<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    /**
     * Show public card by slug
     */
    public function showCard($slug)
    {
        $card = Card::where('slug', $slug)
            ->where('is_active', true)
            ->with('user:id,name')
            ->first();

        if (!$card) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Card not found'
                ], 404);
            }
            abort(404, 'Card not found');
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $card->id,
                    'name' => $card->name,
                    'company' => $card->company,
                    'position' => $card->position,
                    'email' => $card->email,
                    'phone' => $card->phone,
                    'mobile' => $card->mobile,
                    'address' => $card->address,
                    'company_address' => $card->company_address,
                    'logo_url' => $card->logo_url,
                    'qr_code_url' => $card->qr_code_url,
                    'owner' => $card->user->name ?? 'Unknown'
                ]
            ]);
        }

        // Return a view for web browsers
        return view('cards.public', compact('card'));
    }
}
