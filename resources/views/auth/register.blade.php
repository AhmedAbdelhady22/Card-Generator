@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="form-container">
                <h2 class="text-center mb-4">Create Account</h2>
                
                <form id="register-form" method="POST" action="{{ route('register') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="name" class="form-label required">Name</label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label required">Email</label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label required">Password</label>
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label required">Confirm Password</label>
                        <input type="password" 
                               class="form-control" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               required>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" class="text-primary-custom">Terms of Service</a> and <a href="#" class="text-primary-custom">Privacy Policy</a>
                            </label>
                        </div>
                    </div>

                    <div class="form-group mb-4 btn btn-primary w-100 w-md-75 w-lg-50">
                        <button type="submit" class="btn btn-primary w-100">
                            Create Account
                        </button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <p>Already have an account? <a href="{{ route('login') }}" class="text-primary-custom">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
