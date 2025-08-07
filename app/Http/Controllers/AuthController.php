<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',            
            'password' => ['required', 'string', 'min:8', 'max:255']
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return $this->errorResponse('Validation failed', 422, $validator->errors());
            } else {
                return back()->withErrors($validator)->withInput();
            }
        }

        // ✅ GOOD: Delegate authentication to model
        $loginResult = User::attemptLogin(
            $request->email, 
            $request->password, 
            $request->ip()
        );

        if (!$loginResult['success']) {
            // Handle different failure scenarios
            if (isset($loginResult['user']) && $loginResult['reason'] === 'wrong_password') {
                $this->logActivity(
                    'failed_login_attempt',
                    $loginResult['user'],
                    null,
                    null,
                    'User attempted to login with incorrect password'
                );
            }

            if (isset($loginResult['user']) && $loginResult['reason'] === 'account_inactive') {
                $this->logActivity(
                    'blocked_login_attempt',
                    $loginResult['user'],
                    null,
                    ['reason' => 'account_inactive'],
                    'Login attempt while account is inactive'
                );
            }

            if ($request->expectsJson()) {
                return $this->errorResponse($loginResult['message'], $loginResult['code']);
            } else {
                return back()->withErrors(['email' => 'These credentials do not match our records.'])->withInput();
            }
        }

        $user = $loginResult['user'];

        // ✅ GOOD: Login user for web sessions
        Auth::login($user);

        // ✅ GOOD: Get auth data from model
        $authData = $user->getAuthData();

        $this->logActivity(
            'login', 
            $user, 
            null, 
            ['login_time' => now(), 'ip' => $request->ip()], 
            'User logged in successfully'
        );

        if ($request->expectsJson()) {
            return $this->successResponse($authData, 'Login successful');
        } else {
            return redirect()->route('dashboard');
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|min:2',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validator->setCustomMessages([
            'email.unique' => 'This email address is already registered.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters long.'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return $this->errorResponse('Registration failed', 422, $validator->errors());
            } else {
                return back()->withErrors($validator)->withInput();
            }
        }

        // ✅ GOOD: Delegate registration to model
        $registrationResult = User::registerUser(
            $validator->validated(), 
            $request->ip()
        );

        if (!$registrationResult['success']) {
            if ($request->expectsJson()) {
                return $this->errorResponse($registrationResult['message'], $registrationResult['code']);
            } else {
                return back()->withErrors(['error' => 'Registration failed. Please try again.'])->withInput();
            }
        }

        $user = $registrationResult['user'];

        // ✅ GOOD: Auto-login the user for web sessions
        Auth::login($user);

        // ✅ GOOD: Get auth data from model
        $authData = $user->getAuthData();

        $this->logActivity(
            'register',
            $user,
            null,
            [
                'name' => $user->name,
                'email' => $user->email,
                'ip' => $request->ip(),
            ],
            'New user registered'
        );

        if ($request->expectsJson()) {
            return $this->successResponse($authData, 'Registration successful', 201);
        } else {
            return redirect()->route('dashboard')->with('success', 'Registration successful! Welcome!');
        }
    }

    public function logout(Request $request)
    {
        $user = User::find(Auth::id());

        if (!$user) {
            if ($request->expectsJson()) {
                return $this->errorResponse('User not authenticated', 401);
            } else {
                return redirect()->route('login');
            }
        }

        // Log activity before logout
        $this->logActivity(
            'logout',
            $user,
            null,
            ['logout_time' => now(), 'ip' => $request->ip()],
            'User logout'
        );

        if ($request->expectsJson()) {
            $success = $user->logoutUser();
            
            if ($success) {
                return $this->successResponse(null, 'Logged out successfully');
            } else {
                return $this->errorResponse('Logout failed', 500);
            }
        } else {
            // For web requests, logout from session
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('home')->with('success', 'You have been logged out successfully.');
        }
    }

    public function logoutUser(Request $request)
    {
        try {
            $user = User::find(Auth::id());
            
            if ($user) {
                // For API: Delete current access token
                if ($user->currentAccessToken()) {
                    $user->currentAccessToken()->delete();
                }
                
                // For web: Logout and invalidate session
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return response()->json(['message' => 'Logged out successfully']);
            
        } catch (\Exception $e) {
            Log::error('Logout failed: ' . $e->getMessage());
            return response()->json(['error' => 'Logout failed'], 500);
        }
    }
}


