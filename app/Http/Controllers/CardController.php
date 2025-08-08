<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Str;

class CardController extends Controller
{
    public function index(Request $request)
    {
        $cards = Card::byUser(Auth::id())
            ->select(['id', 'name', 'company', 'position', 'email', 'phone', 'mobile', 'address', 'company_address', 'logo', 'qr_code', 'slug', 'is_active'])
            ->orderBy('created_at', 'desc')
            ->get();

        $this->logActivity('viewed_cards', null, null, $cards, 'User viewed their cards');

        if ($request->expectsJson()) {
            return $this->successResponse($cards, 'Cards retrieved successfully');   
        }
        
        return view('cards.index', compact('cards'));
    }

    public function store(Request $request)
    {
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
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return $this->errorResponse('Validation failed', 422, $validator->errors());
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $cardData = array_merge($validator->validated(), [
                'user_id' => Auth::id(),
                'is_active' => true,
            ]);

            $card = Card::createWithQrCode($cardData);

            if ($request->hasFile('logo')) {
                $card->uploadLogo($request->file('logo'));
            }

            $this->logActivity(
                'created_card',
                $card,
                null,
                $cardData,
                "User created a new card: {$card->name}"
            );

            if ($request->expectsJson()) {
                return $this->successResponse($card->fresh(), 'Business card created successfully', 201);
            }
            
            return redirect()->route('cards.index')->with('success', 'Card created successfully!');

        } catch (\Exception $e) {
            Log::error('Card creation failed: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return $this->errorResponse('Failed to create card', 500);
            }
            
            return back()->with('error', 'Failed to create card. Please try again.');
        }
    }

    public function update(Request $request, $cardId)
    {
        $card = Card::find($cardId);
        
        if (!$card || $card->user_id !== Auth::id()) {
            return $this->unauthorizedResponse($request);
        }

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
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($request, $validator);
        }

        try {
            $oldData = $card->toArray();

            $card->update($validator->validated());
            
            if ($request->hasFile('logo')) {
                $card->uploadLogo($request->file('logo'));
            }
            
            $card->generateQrCode();

            $this->logActivity('update_card', $card, $oldData, $card->fresh()->toArray(), "Updated card: {$card->name}");

            if ($request->expectsJson()) {
                return $this->successResponse($card->fresh(), 'Card updated successfully');
            }
            
            return redirect()->route('cards.index')->with('success', 'Card updated successfully');

        } catch (\Exception $e) {
            Log::error('Card update failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to update card', 500);
        }
    }

    public function destroy(Request $request, $cardId)
    {
        $card = Card::find($cardId);
        
        if (!$card || $card->user_id !== Auth::id()) {
            return $this->unauthorizedResponse($request);
        }

        try {
            $oldData = $card->toArray();
            $cardName = $card->name;
            
            $card->deleteFiles();
            $card->delete();

            $this->logActivity('delete_card', $card, $oldData, null, "Deleted business card: {$cardName}");

            if ($request->expectsJson()) {
                return $this->successResponse(null, 'Card deleted successfully');
            }
            
            return redirect()->route('cards.index')->with('success', 'Card deleted successfully');

        } catch (\Exception $e) {
            Log::error('Card deletion failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete card', 500);
        }
    }

    public function toggleStatus($cardId)
    {
        // Find card by ID or slug
        $card = Card::where('id', $cardId)
                    ->orWhere('slug', $cardId)
                    ->where('user_id', Auth::id())
                    ->first();

        if (!$card) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Card not found'
                ], 404);
            }
            return redirect()->back()->with('error', 'Card not found');
        }

        try {
            $card->is_active = !$card->is_active;
            $card->save();

            $status = $card->is_active ? 'activated' : 'deactivated';
            $this->logActivity('card_status_changed', $card, null, ['status' => $status], "Card {$status}: {$card->name}");

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Card {$status} successfully!",
                    'is_active' => $card->is_active
                ]);
            }

            return redirect()->back()->with('success', "Card {$status} successfully!");

        } catch (\Exception $e) {
            Log::error("Failed to toggle card status for card {$card->id}: " . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update card status'
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to update card status');
        }


    }

    public function downloadPdf($id)
    {
        $card = Card::findOrFail($id);

        if ($card->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized access', 403);
        }

        try {
            $this->logActivity('pdf_generated', $card, null, ['card_name' => $card->name], 'User generated PDF for card: ' . $card->name);

            $pdfData = $card->getPdfData();
            $html = view('cards.pdf', $pdfData)->render();

            $pdf = Browsershot::html($html)
                ->format('A4')
                ->margins(10, 10, 10, 10)
                ->showBackground()
                ->waitUntilNetworkIdle()
                ->timeout(60)
                ->setOption('--disable-web-security', true)
                ->pdf();

            $filename = Str::slug($card->name . '-' . $card->company) . '-business-card.pdf';

            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                
        } catch (\Exception $e) {
            Log::error("PDF generation failed for card {$card->id}: " . $e->getMessage());
            return $this->errorResponse('Failed to generate PDF', 500);
        }
    }

    public function previewPdf($id)
    {
        $card = Card::findOrFail($id);

        if ($card->user_id !== Auth::id()) {
            return $this->errorResponse('Unauthorized access', 403);
        }

        try {
            $pdf = $card->generatePdf();
            $filename = $card->getPdfFilename();

            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
                
        } catch (\Exception $e) {
            Log::error("PDF preview failed for card {$card->id}: " . $e->getMessage());
            return $this->errorResponse('Failed to preview PDF', 500);
        }
    }

    // HELPER METHODS (Keep controller thin)
    private function unauthorizedResponse(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->errorResponse('Unauthorized', 403);
        }
        return redirect()->route('cards.index')->with('error', 'Unauthorized');
    }

    private function validationErrorResponse(Request $request, $validator)
    {
        if ($request->expectsJson()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }
        return redirect()->back()->withErrors($validator)->withInput();
    }
}