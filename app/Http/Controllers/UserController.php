<?php

namespace App\Http\Controllers;


use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\ActivityLog;


class UserController extends Controller
{
    public function userProfile()
    {
        try {
            $user = User::find(Auth::user()->id);
            
            if (!$user) {
                return $this->errorResponse('User not authenticated', 401);
            }

            // Alternative approach - load relationships separately
            $user->load('role:id,name');
            $user->loadCount('cards');

            return $this->successResponse([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'cards_count' => $user->cards_count,
                'status' => $user->status,
                'last_login' => $user->formatted_last_login,
                'created_at' => $user->created_at,
            ], 'User profile retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Error getting user profile: ' . $e->getMessage());
            return $this->errorResponse('Error retrieving profile', 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = User::find(Auth::user()->id);

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

            $emailChanged = $user->email !== $request->email;
            
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if ($emailChanged) {
                $updateData['email_verified_at'] = null;
            }

            $user->update($updateData);

            // Log activity
            $this->logActivity(
                'profile_updated',
                $user,
                null,
                ['updated_fields' => ['name', 'email']],
                'User updated profile'
            );

            return $this->successResponse([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
            ], 'Profile updated successfully');

        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage());
            return $this->errorResponse('Error updating profile', 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $user = User::find(Auth::user()->id);

            if (!$user) {
                return $this->errorResponse('User not authenticated', 401);
            }

            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string|min:8',
                'new_password' => 'required|string|min:8|confirmed|different:current_password',
                'new_password_confirmation' => 'required|string|min:8'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', 422, $validator->errors());
            }

            if (!$user->validatePassword($request->current_password)) {
                return $this->errorResponse('Current password is incorrect', 401);
            }

            $user->changePassword($request->new_password);

            $this->logActivity('password_changed', $user, null, [], 'User changed password');

            return $this->successResponse(null, 'Password changed successfully');

        } catch (\Exception $e) {
            Log::error('Error changing password: ' . $e->getMessage());
            return $this->errorResponse('Error changing password', 500);
        }
    }

    public function dashboard()
    {
        try {
            $user = User::find(Auth::user()->id);

            if (!$user) {
                return redirect()->route('login');
            }
            
            $stats = $user->getDashboardStats();
            $cardTrend = $user->getCardTrend();
            
            $recentCards = $user->cards()
                ->latest()
                ->take(3)
                ->get();
                
            $recentActivity = $user->activityLogs()
                ->latest()
                ->take(5)
                ->get();

            $this->logActivity('dashboard_viewed', $user, null, ['stats' => $stats], 'User viewed dashboard');

            return view('auth.dashboard', compact('user', 'stats', 'cardTrend', 'recentCards', 'recentActivity'));

        } catch (\Exception $e) {
            Log::error('Error loading dashboard: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Error loading dashboard');
        }
    }

    public function deleteAccount(Request $request)
    {
        try {
            $user = User::find(Auth::user()->id);
            
            if (!$user) {
                return $this->errorResponse('User not authenticated', 401);
            }
            
            // Optional: Require password confirmation
            $validator = Validator::make($request->all(), [
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Password required for account deletion', 422, $validator->errors());
            }

            if (!$user->validatePassword($request->password)) {
                return $this->errorResponse('Incorrect password', 401);
            }
            
            $success = $user->deleteAccount();
            
            if (!$success) {
                return $this->errorResponse('Failed to delete account', 500);
            }

            // Logout user
            Auth::logout();

            return $this->successResponse(null, 'Account deleted successfully');

        } catch (\Exception $e) {
            Log::error('Error deleting account: ' . $e->getMessage());
            return $this->errorResponse('Error deleting account', 500);
        }
    }
}
