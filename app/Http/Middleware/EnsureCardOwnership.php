<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Card;

class EnsureCardOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        // Get the card ID from the route
        $cardId = $request->route('card');
        
        if ($cardId) {
            // Check if the card exists and belongs to the authenticated user
            $card = Card::where('id', $cardId)
                       ->where('user_id', Auth::id())
                       ->first();
            
            if (!$card) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Card not found or unauthorized access.'
                    ], 403);
                }
                
                return redirect()->route('cards.index')
                    ->with('error', 'Card not found or you do not have permission to access it.');
            }
        }
        
        return $next($request);
    }
}
