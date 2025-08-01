<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Exception;


class AuthController extends Controller
{
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',            
            'password'=>['required','string','min:8','max:255']
        ]);

        if ($validator->fails()){
            if ($request->expectsJson()) {
                return $this->errorResponse('Validation failed', 422, $validator->errors());
            } else {
                return back()->withErrors($validator)->withInput();
            }
        }

        $user = User::where('email', $request->email)->first();

        if(!$user){
            if ($request->expectsJson()) {
                return $this->errorResponse('User not found', 401);
            } else {
                return back()->withErrors(['email' => 'These credentials do not match our records.'])->withInput();
            }
        }

        if(!Hash::check($request->password, $user->password)){
            $this->logActivity(
                'failed_login_attempt',
                $user,
                null,
                null,
                'User attempted to login with incorrect password'
            );
            if ($request->expectsJson()) {
                return $this->errorResponse('Wrong password', 401);
            } else {
                return back()->withErrors(['email' => 'These credentials do not match our records.'])->withInput();
            }
        }

        if(!$user->is_active){
            $this->logActivity(
                'blocked_login_attempt',
                $user,
                null,
                ['reason' => 'account_inactive'],
                'login attempt while account is inactive'
            );
            if ($request->expectsJson()) {
                return $this->errorResponse('Account is inactive', 403);
            } else {
                return back()->withErrors(['email' => 'Your account is inactive. Please contact support.'])->withInput();
            }
        }

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip()
        ]);

        // Log the user in for web sessions
        Auth::login($user);

        $token = $user->createToken('auth_token')->plainTextToken;

        $this->logActivity(
        'login', 
        $user, 
        null, 
        ['login_time' => now(), 'ip' => $request->ip()], 
        'User logged in successfully'
        );

        $userData = $user->load(['role.permissions']);

        if ($request->expectsJson()) {
            return $this->successResponse([
                'user' => $userData,
                'token' => $token,
                'token_type' => 'Bearer'
            ], 'Login successful');
        } else {
            return redirect()->route('dashboard');
        }

    }   
    
    
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|min:2',
            'email' =>  'required|email|max:255|unique:users,email',
            'password'=>  'required|string|min:8|confirmed',
        ]);

        $validator->setCustomMessages([
            'email.unique' => 'This email address is already registered.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters long.'
        ]);

        if($validator->fails()){
            if ($request->expectsJson()) {
                return $this->errorResponse('Registration failed', 422, $validator->errors());
            } else {
                return back()->withErrors($validator)->withInput();
            }
        }
        $validated = $validator->validate();

        $userRole = Role::where('name', 'user')->first();

        if(!$userRole){
            Log::error('Default user role not found during registration');

            $userRole = Role::create([
                'name'=> 'user',
                'description' => 'Default user role'
            ]);
        }

        try {
            $user = User::create([
                'name'=> $validated['name'],
                'email'=> $validated['email'],
                'password'=>Hash::make($validated['password']),
                'role_id'=>$userRole->id,
                'email_verified_at' => now()
            ]);

            // Auto-login the user for web sessions
            Auth::login($user);

            $token = $user->createToken('auth-token')->plainTextToken;
            $userData = $user->load(['role.permissions']);

            $this->logActivity(
                'register',
                $user,
                null,
                [
                    'name' => $user->name,
                    'email'=> $user->email,
                    'ip' => $request->ip(),
                ],
                'New user registered'
            );

            if ($request->expectsJson()) {
                return $this->successResponse([
                    'user' => $userData,
                    'token' => $token,
                    'token_type' => 'Bearer'
                ], 'Registration successful', 201);
            } else {
                return redirect()->route('dashboard')->with('success', 'Registration successful! Welcome!');
            }

    } catch(Exception $e){

        Log::error('User registration failed: ' . $e->getMessage());

        if ($request->expectsJson()) {
            return $this->errorResponse('User registration failed', 500);
        } else {
            return back()->withErrors(['error' => 'Registration failed. Please try again.'])->withInput();
        }
        }


        }



    public function logout(Request $request){
        $user = Auth::user();

        if(!$user) {
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
            ['logout_time'=>now(), 'ip'=>$request->ip()],
            'User logout'
        );

        if ($request->expectsJson()) {
            // For API requests, delete the token
            $request->user()->currentAccessToken()->delete();
            return $this->successResponse(null, 'Logged out successfully');
        } else {
            // For web requests, logout from session
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('home')->with('success', 'You have been logged out successfully.');
        }
    }



}


