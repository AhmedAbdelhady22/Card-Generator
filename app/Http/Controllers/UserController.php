<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Card;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;

class UserController extends Controller
{
    public function userProfile(){
        $user_id = Auth::id();
        $user = User::find($user_id)
        ->load(['role:id,name'])->loadCount('cards');
        if (!$user) {
            return $this->errorResponse('User not authenticated', 401);
        }
        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'cards_count' => $user->cards_count,
            'status' => $user->is_active ? 'active' : 'inactive',
            'last_login_at' => $user->last_login_at,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
        ], 'User profile retrieved successfully');
    }
    public function updateProfile(Request $request){
        $user_id = Auth::id();
        $user = User::find($user_id);
        if (!$user) {
            return $this->errorResponse('User not authenticated', 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|min:3',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        $emailChanged = $user->email !== $request->email;


        if ($emailChanged) {
            $updateData['email_verified_at'] = null;
        }


        $user->update($updateData);

        $this->logActivity(
            'profile_updated',
            $user,
            null,
            [
                'updated_fields' => ['name', 'email'],
                'email_changed' => $emailChanged,
                'ip' => $request->ip(),
            ],
                'User updated profile'
        );

        $user->refresh();

        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
        ], 'Profile updated successfully');

    }

    public function changePassword(Request $request){
        $user_id = Auth::id();
        $user = User::find($user_id);
        if (!$user) {
            return $this->errorResponse('User not authenticated', 401);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string|min:8|max:255',
            'new_password' => 'required|string|min:8|max:255|confirmed|different:current_password',
            'new_password_confirmation' => 'required|string|min:8|max:255'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse('Current password is incorrect', 401);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();


        $this->logActivity(
            'password_changed',
            $user,
            null,
            ['ip' => $request->ip()],
            'User changed password'
        );

        return $this->successResponse(null, 'Password changed successfully');
    }

    public function getMyCards(Request $request){
        $user = User::find(  Auth::id());
        $cards = $user->cards()
            ->select(['id', 'name', 'company', 'position', 'status', 'slug', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return $this->successResponse($cards, 'User cards retrieved successfully');
    }

    public function deleteAccount(Request $request){
        $user_id = Auth::id();
        $user = User::find($user_id);

        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
            'confirmation' => 'required|string|in:DELETE_MY_ACCOUNT'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        if (!Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Password is incorrect', 401);
        }

        $this->logActivity(
            'account_deletion_requested',
            $user,
            null,
            ['ip' => $request->ip()],
            'User requested account deletion'
        );

        $user->cards()->delete();
        $user->tokens()->delete();
        $user->delete();

        return $this->successResponse(null, 'Account deleted successfully');

    }

    public function dashboard()
    {
        $user = Auth::user();
        
        // Get user's cards
        $cards = Card::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Dashboard statistics
        $stats = [
            'total_cards' => $cards->count(),
            'active_cards' => $cards->where('is_active', true)->count(),
            'inactive_cards' => $cards->where('is_active', false)->count(),
            'cards_this_month' => $cards->where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
        ];
        
        // Recent cards (last 3)
        $recentCards = $cards->take(3);
        
        // Recent activity logs for this user
        $recentActivity = ActivityLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        // Card creation trend (last 6 months)
        $cardTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $count = $cards->where('created_at', '>=', $month->startOfMonth())
                          ->where('created_at', '<=', $month->endOfMonth())
                          ->count();
            $cardTrend[] = [
                'month' => $month->format('M Y'),
                'count' => $count
            ];
        }

        // Log dashboard view activity
        $this->logActivity(
            'dashboard_viewed',
            $user,
            null,
            ['stats' => $stats],
            'User viewed dashboard'
        );
        
        return view('auth.dashboard', compact('user', 'cards', 'stats', 'recentCards', 'recentActivity', 'cardTrend'));
    }

    public function getDashboardData()
    {
        $user = Auth::user();
        
        $cards = Card::where('user_id', $user->id)->get();
        
        $stats = [
            'total_cards' => $cards->count(),
            'active_cards' => $cards->where('is_active', true)->count(),
            'inactive_cards' => $cards->where('is_active', false)->count(),
            'cards_this_month' => $cards->where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
        ];

        $this->logActivity(
            'dashboard_api_accessed',
            $user,
            null,
            ['stats' => $stats],
            'User accessed dashboard data via API'
        );
        
        return $this->successResponse([
            'stats' => $stats,
            'cards' => $cards
        ], 'Dashboard data retrieved successfully');
    }



}
