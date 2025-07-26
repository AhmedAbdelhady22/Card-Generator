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
//use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;




class CardController extends Controller
{
public function index(){
    $cards = Card::where('user_id', Auth::id())
                ->where('is_active', true)
                ->select(['id', 'name', 'company', 'position', 'email', 'phone', 'mobile', 'address', 'company_address', 'logo', 'qr_code'])
                ->orderBy('created_at', 'desc')
                    ->get();

    
                    
    $this->logActivity(
    'viewed_cards', 
    null, 
    null,
    $cards,
    'User viewed their cards'

);
    return $this->successResponse($cards, 'Cards retrieved successfully');   
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
    ]);

    if ($validator->fails()) {
        return $this->errorResponse('Validation failed', 422, $validator->errors());
    }

    $validated = $validator->validated();

    $logoPath = null;
    if ($request->hasFile('logo')) {
        $logoPath = $request->file('logo')->store('logos', 'public');
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

        return $this->successResponse(
        $card->fresh(),
        'Business card created successfully', 
        201
    );
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


public function update(Request $request, Card $card){
    if ($card->user_id !== Auth::id()){
        return $this->errorResponse('Unauthorized', 403);
    }

    $oldData = $card->toArray();

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
        return $this->errorResponse('Validation failed', 422, $validator->errors());
    }

    $validated = $validator->validated();

    if ($request->hasFile('logo')){
        if ($card->logo && Storage::disk('public')->exists($card->logo)){
            Storage::disk('public')->delete($card->logo);
        }
        $validated['logo'] = $request->file('logo')->store('logos', 'public');
    }

    $card->update($validated);


    $this->generateQrCode($card);



        $this->logActivity(
        'update_card', 
        $card, 
        $oldData, 
        $card->fresh()->toArray(), 
        "Updated card: {$card->name}"
    );

    return $this->successResponse($card->fresh(), 'Card updated successfully');
}


public function destroy(Card $card){
    if ($card->user_id !== Auth::id()) {
        return $this->errorResponse('Unauthorized', 403);
    }

    $oldData = $card->toArray();
    $card->delete();


        $this->logActivity(
        'delete_card', 
        $card, 
        $oldData, 
        null, 
        "Deleted business card: {$card->name}"
    );

    return $this->successResponse(null, 'Card deleted successfully');
}

private function generateQrCode(Card $card){

        $cardUrl = url("/card/{$card->slug}");

    try {

        $qrCode = QrCode::create($cardUrl)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->setSize(300)
            ->setMargin(10)
            ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin());

        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        $filename = "qr-codes/card-{$card->id}.png";
        
        Storage::disk('public')->put($filename, $result->getString());
        
        $card->update(['qr_code' => $filename]);
        
    }  

    catch (\Exception $e) {
        Log::error("QR code generation failed for card {$card->id}: " . $e->getMessage());
    }
}

}