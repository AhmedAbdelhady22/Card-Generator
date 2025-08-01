<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use App\Models\Card;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

use SimpleSoftwareIO\QrCode\Facades\QrCode;






class CardController extends Controller
{
public function index(Request $request){
    $cards = Card::where('user_id', Auth::id())
                ->where('is_active', true)
                ->select(['id', 'name', 'company', 'position', 'email', 'phone', 'mobile', 'address', 'company_address', 'logo', 'qr_code','slug'])
                ->orderBy('created_at', 'desc')
                    ->get();

 
                    
    $cards->each(function ($card) {
        $card->public_url = url("/api/public/card/{$card->slug}");
    });
                    
    $this->logActivity(
    'viewed_cards', 
    null, 
    null,
    $cards,
    'User viewed their cards'

);

    if ($request->expectsJson()) {
        return $this->successResponse($cards, 'Cards retrieved successfully');   
    } else {
        return view('cards.index', compact('cards'));
    }
}


public function store(Request $request){
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'company' => 'required|string|max:255',
        'position' => 'nullable|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:20',
        'mobile' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:500',
        'company_address' => 'nullable|string|max:500',
        'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ], [
        'logo.image' => 'The logo must be an image file.',
        'logo.mimes' => 'The logo must be a file of type: jpeg, png, jpg, gif, svg.',
        'logo.max' => 'The logo may not be greater than 2MB.',
    ]);

    if ($validator->fails()) {
        if ($request->expectsJson()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        } else {
            return back()->withErrors($validator)->withInput();
        }
    }

    $validated = $validator->validated();

    $logoPath = null;
    if ($request->hasFile('logo')) {
        $logoFile = $request->file('logo');
        
        // Debug information
        Log::info('Logo upload attempt:', [
            'original_name' => $logoFile->getClientOriginalName(),
            'mime_type' => $logoFile->getClientMimeType(),
            'size' => $logoFile->getSize(),
            'is_valid' => $logoFile->isValid()
        ]);
        
        if ($logoFile->isValid()) {
            $logoPath = $logoFile->store('logos', 'public');
            Log::info('Logo stored successfully:', ['path' => $logoPath]);
        } else {
            Log::error('Logo file is not valid');
        }
    }    

    $cardData = array_merge($validated, [
        'user_id' => Auth::id(),
        'logo' => $logoPath,
        'is_active' => true,
    ]);

    $card = Card::create($cardData);

    $this->generateQrCode($card);

    $this->logActivity(
        'created_card',
        $card,
        null,
        $cardData,
        "User created a new card: {$card->name}"
    );

    if ($request->expectsJson()) {
        return $this->successResponse(
            $card->fresh(),
            'Business card created successfully', 
            201
        );
    } else {
        return redirect()->route('cards.index')->with('success', 'Card created successfully!');
    }
}



public function show(Card $card){
    if ($card->user_id !== Auth::id()){
        return $this->errorResponse('Unauthorized', 403);
    }

    $this->logActivity(
        'viewed_card',
        $card,
        null,
        null,
        "User viewed card: {$card->name}"
    );

    return $this->successResponse($card, 'Card retrieved successfully');
}


public function update(Request $request, $cardId){
    // Find the card manually
    $card = Card::find($cardId);
    
    if (!$card) {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Card not found'], 404);
        }
        return redirect()->route('cards.index')->with('error', 'Card not found');
    }
    
    if ($card->user_id !== Auth::id()) {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return redirect()->route('cards.index')->with('error', 'Unauthorized');
    }

    $oldData = $card->toArray();

    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'company' => 'required|string|max:255',
        'position' => 'nullable|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:20',
        'mobile' => 'nullable|string|max:20',
        'website' => 'nullable|url|max:255',
        'address' => 'nullable|string|max:500',
        'company_address' => 'nullable|string|max:500',
        'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    if ($validator->fails()) {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }
        return redirect()->back()->withErrors($validator)->withInput();
    }

    $validated = $validator->validated();

    // Handle logo upload
    if ($request->hasFile('logo')) {
        // Delete old logo if exists
        if ($card->logo && Storage::disk('public')->exists($card->logo)) {
            Storage::disk('public')->delete($card->logo);
        }
        
        // Upload new logo
        $logoPath = $request->file('logo')->store('logos', 'public');
        $validated['logo'] = $logoPath;
    }

    // Update card with all validated data
    $card->update($validated);
    
    // Regenerate QR code
    $this->generateQrCode($card);

    $this->logActivity(
        'update_card', 
        $card, 
        $oldData, 
        $card->fresh()->toArray(), 
        "Updated card: {$card->name}"
    );

    if ($request->expectsJson()) {
        return response()->json(['message' => 'Card updated successfully', 'card' => $card->fresh()]);
    }
    
    return redirect()->route('cards.index')->with('success', 'Card updated successfully');
}


public function destroy(Request $request, $cardId){
    // Find the card manually
    $card = Card::find($cardId);
    
    if (!$card) {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Card not found'], 404);
        }
        return redirect()->route('cards.index')->with('error', 'Card not found');
    }
    
    if ($card->user_id !== Auth::id()) {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return redirect()->route('cards.index')->with('error', 'Unauthorized');
    }

    $oldData = $card->toArray();
    $cardName = $card->name;
    
    // Delete associated files
    if ($card->logo) {
        Storage::disk('public')->delete($card->logo);
    }
    if ($card->qr_code) {
        Storage::disk('public')->delete($card->qr_code);
    }
    
    $card->delete();

    $this->logActivity(
        'delete_card', 
        $card, 
        $oldData, 
        null, 
        "Deleted business card: {$cardName}"
    );

    if ($request->expectsJson()) {
        return response()->json(['message' => 'Card deleted successfully']);
    }
    
    return redirect()->route('cards.index')->with('success', 'Card deleted successfully');
}

private function generateQrCode(Card $card){
    try {
        $cardUrl = url("/api/public/card/{$card->slug}");
        Log::info("🔄 Generating QR code for card {$card->id} with URL: {$cardUrl}");
        
        // ✅ Generate QR code content
        $qrCodeContent = QrCode::format('svg')
            ->size(300)
            ->margin(2)
            ->generate($cardUrl);
        
        Log::info("✅ QR code generated: " . strlen($qrCodeContent) . " bytes");
        
        // ✅ Create filename
        $filename = "qr-codes/card-{$card->id}.svg";
        
        // ✅ Use Laravel Storage for proper path handling
        Storage::disk('public')->makeDirectory('qr-codes');
        
        // ✅ Save QR code
        $saved = Storage::disk('public')->put($filename, $qrCodeContent);
        
        if ($saved) {
            // ✅ Update card record
            $card->update(['qr_code' => $filename]);
            Log::info("✅ QR code saved successfully: {$filename}");
        } else {
            Log::error("❌ Failed to save QR code file");
        }
        
    } catch (\Exception $e) {
        Log::error("❌ QR code generation failed for card {$card->id}: " . $e->getMessage());
        Log::error("❌ Stack trace: " . $e->getTraceAsString());
    }
}

/**
 * Generate and download PDF for a business card
 */
public function downloadPdf($cardId)
{
    // Find the card and ensure it belongs to the authenticated user
    $card = Card::where('id', $cardId)
                ->where('user_id', Auth::id())
                ->first();

    if (!$card) {
        if (request()->expectsJson()) {
            return $this->errorResponse('Card not found or unauthorized access', 404);
        }
        return redirect()->route('cards.index')->with('error', 'Card not found or unauthorized access.');
    }

    try {
        // Log the PDF generation activity
        $this->logActivity(
            'pdf_generated',
            $card,
            null,
            ['card_name' => $card->name],
            'User generated PDF for card: ' . $card->name
        );

        // Set longer timeout for PDF generation
        ini_set('max_execution_time', 120);

        // Generate PDF from the card data
        $pdf = Pdf::loadView('cards.pdf', compact('card'))
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled' => false, // Disable remote content for security
                      'defaultFont' => 'DejaVu Sans', // Use a font that supports Unicode
                      'dpi' => 150,
                      'debugPng' => false,
                      'debugKeepTemp' => false,
                      'debugCss' => false,
                      'enable_php' => true
                  ]);

        // Generate filename
        $filename = Str::slug($card->name . '-' . $card->company) . '-business-card.pdf';

        return $pdf->download($filename);

    } catch (\Exception $e) {
        Log::error("PDF generation failed for card {$card->id}: " . $e->getMessage());
        Log::error("Stack trace: " . $e->getTraceAsString());
        
        if (request()->expectsJson()) {
            return $this->errorResponse('Failed to generate PDF: ' . $e->getMessage(), 500);
        }
        
        return redirect()->back()->with('error', 'Failed to generate PDF. Please try again.');
    }
}

/**
 * Preview PDF for a business card
 */
public function previewPdf($cardId)
{
    // Find the card and ensure it belongs to the authenticated user
    $card = Card::where('id', $cardId)
                ->where('user_id', Auth::id())
                ->first();

    if (!$card) {
        if (request()->expectsJson()) {
            return $this->errorResponse('Card not found or unauthorized access', 404);
        }
        return redirect()->route('cards.index')->with('error', 'Card not found or unauthorized access.');
    }

    try {
        // Log the PDF preview activity
        $this->logActivity(
            'pdf_previewed',
            $card,
            null,
            ['card_name' => $card->name],
            'User previewed PDF for card: ' . $card->name
        );

        // Set longer timeout for PDF generation
        ini_set('max_execution_time', 120);

        // Generate PDF and stream it to browser
        $pdf = Pdf::loadView('cards.pdf', compact('card'))
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled' => false, // Disable remote content for security
                      'defaultFont' => 'DejaVu Sans', // Use a font that supports Unicode
                      'dpi' => 150,
                      'debugPng' => false,
                      'debugKeepTemp' => false,
                      'debugCss' => false,
                      'enable_php' => true
                  ]);

        return $pdf->stream('business-card-preview.pdf');

    } catch (\Exception $e) {
        Log::error("PDF preview failed for card {$card->id}: " . $e->getMessage());
        Log::error("Stack trace: " . $e->getTraceAsString());
        
        if (request()->expectsJson()) {
            return $this->errorResponse('Failed to preview PDF: ' . $e->getMessage(), 500);
        }
        
        return redirect()->back()->with('error', 'Failed to preview PDF. Please try again.');
    }
}

/**
 * Helper method to safely convert image to base64
 */
private function getImageAsBase64($imagePath)
{
    try {
        if (!$imagePath || !file_exists($imagePath)) {
            return null;
        }

        $imageData = file_get_contents($imagePath);
        if ($imageData === false) {
            return null;
        }

        $mimeType = mime_content_type($imagePath);
        if (!$mimeType || !in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            return null;
        }

        return [
            'data' => base64_encode($imageData),
            'mime' => $mimeType
        ];
    } catch (\Exception $e) {
        Log::error("Image conversion failed for: {$imagePath} - " . $e->getMessage());
        return null;
    }
}
}