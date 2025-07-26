<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PublicController extends Controller
{

    public function displayCard(Request $request, $slug){
        $card = Card::where('slug', $slug)
            ->first();

        if(!$card) {
            return $this->errorResponse('Card not found', 404);
        }

        if($card->status === 'disabled') {
            return $this->errorResponse('Card is disabled', 403);
        }

        if(!$card->user->is_active){
            return $this->errorResponse('Sorry, this user account is no longer active.',code: 403);
        }

            
        return $this->successResponse([
        'name' => $card->name,
        'company' => $card->company,
        'position' => $card->position,
        'email' => $card->email,
        'phone' => $card->phone,
        'mobile' => $card->mobile,
        'address' => $card->address,
        'company_address' => $card->company_address,
        'logo_url' => $card->logo ? asset('storage/' . $card->logo) : null,
        ],
        'Card retrieved successfully');

    }




}
