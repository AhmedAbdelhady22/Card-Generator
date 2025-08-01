@extends('layouts.app')

@section('content')
<div   class=" d-flex justify-content-center align-items-center min-vh-100">
    <div id="hero" class="container-sm">
        <div class="text-center">
            <h1 class="display-4 mb-4">Welcome to the Card Generator</h1>
            <p class="lead mb-4">Create and customize your own cards easily.</p>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                
                @auth
                    <a href="{{ route('dashboard') }}" class="btn hero-btn btn-primary ">View My Cards</a>
                @else
                    <a href="{{ route('login') }}" class="btn hero-btn ">Get Started</a>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection