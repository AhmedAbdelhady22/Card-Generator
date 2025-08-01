@extends('layouts.app')
@section('title', 'Edit Card')
@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center mb-4">
                    <h4>Edit Business Card</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('cards.update', $card->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $card->name) }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="company" class="form-label">Company *</label>
                                    <input type="text" 
                                           class="form-control @error('company') is-invalid @enderror" 
                                           id="company" 
                                           name="company" 
                                           value="{{ old('company', $card->company) }}" 
                                           required>
                                    @error('company')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="position" class="form-label">Position/Job Title</label>
                                    <input type="text" 
                                           class="form-control @error('position') is-invalid @enderror" 
                                           id="position" 
                                           name="position" 
                                           value="{{ old('position', $card->position) }}">
                                    @error('position')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $card->email) }}" 
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone', $card->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="mobile" class="form-label">Mobile</label>
                                    <input type="text" 
                                           class="form-control @error('mobile') is-invalid @enderror" 
                                           id="mobile" 
                                           name="mobile" 
                                           value="{{ old('mobile', $card->mobile) }}">
                                    @error('mobile')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" 
                                      name="address" 
                                      rows="3">{{ old('address', $card->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                                                <div class="form-group mb-3">
                            <label for="company_address" class="form-label">Company Address</label>
                            <textarea class="form-control @error('company_address') is-invalid @enderror" 
                                      id="company_address" 
                                      name="company_address" 
                                      rows="2">{{ old('company_address', $card->company_address) }}</textarea>
                            @error('company_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="logo" class="form-label">Company Logo</label>
                            @if($card->logo)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $card->logo) }}" alt="Current Logo" 
                                         style="width: 100px; height: 100px; object-fit: cover; border-radius: 5px;">
                                    <small class="d-block text-muted">Current logo</small>
                                </div>
                            @endif
                            <input type="file" 
                                   class="form-control @error('logo') is-invalid @enderror" 
                                   id="logo" 
                                   name="logo" 
                                   accept="image/*">
                            <small class="text-muted">Leave empty to keep current logo</small>
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Card</button>
                            <a href="{{ route('cards.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form> 
                </div>
            </div>
        </div>
    </div>
</div>

@endsection