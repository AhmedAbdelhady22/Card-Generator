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
            return $this->errorResponse('Validation failed', 422, $validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        if(!$user){
            return $this->errorResponse('User not found', 401, );
        }

        if(!Hash::check($request->password, $user->password)){
            $this->logActivity(
                'failed_login_attempt',
                $user,
                null,
                null,
                'User attempted to login with incorrect password'
            );
            return $this->errorResponse('Wrong password', 401);
        }

        if(!$user->is_active){
            $this->logActivity(
                'blocked_login_attempt',
                $user,
                null,
                ['reason' => 'account_inactive'],
                'login attempt while account is inactive'
            );
            return $this->errorResponse('Account is inactive', 403);
        }

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip()
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $this->logActivity(
        'login', 
        $user, 
        null, 
        ['login_time' => now(), 'ip' => $request->ip()], 
        'User logged in successfully'
        );

        $userData = $user->load(['role.permissions']);

        return $this->successResponse([
        'user' => $userData,
        'token' => $token,
        'token_type' => 'Bearer'
    ], 'Login successful');

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
            return $this->errorResponse('Registration failde', 422, $validator->errors());
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

            return $this->successResponse([
            'user' => $userData,
            'token' => $token,
            'token_type' => 'Bearer'
        ], 'Registration successful', 201);

    } catch(Exception $e){

        Log::error('User registration failed: ' . $e->getMessage());

        return $this->errorResponse('User registration failed', 500);
        }


        }



    public function logout(Request $request){
        $user = Auth::user();

        if(!$user) {
            return $this->errorResponse('User not authenticated', 401);
        }

        $request->user()->currentAccessToken()->destroy();

        $this->logActivity(
            'logout',
            $user,
            null,
            ['logout_time'=>now(), 'ip'=>$request->ip()],
            'User logout'
        );

        return $this->successResponse(null, 'Logged out successfully');


    }



}


