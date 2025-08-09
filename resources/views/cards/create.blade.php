@extends('layouts.app')

@section('title', 'Create Card')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center mb-4">
                    <h4>Create a New Card</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('cards.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="name" class="form-label required">Full Name</label>
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

                        <div class="form-group mb-3">
                            <label for="company" class="form-label required">Company</label>
                            <input type="text" 
                                   class="form-control @error('company') is-invalid @enderror" 
                                   id="company" 
                                   name="company" 
                                   value="{{ old('company') }}" 
                                   required>
                            @error('company')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="position" class="form-label">Position/Job Title</label>
                            <input type="text" 
                                   class="form-control @error('position') is-invalid @enderror" 
                                   id="position" 
                                   name="position" 
                                   value="{{ old('position') }}">
                            @error('position')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="mobile" class="form-label">Mobile</label>
                                    <input type="tel" 
                                           class="form-control @error('mobile') is-invalid @enderror" 
                                           id="mobile" 
                                           name="mobile" 
                                           value="{{ old('mobile') }}">
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
                                      rows="2">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="company_address" class="form-label">Company Address</label>
                            <textarea class="form-control @error('company_address') is-invalid @enderror" 
                                      id="company_address" 
                                      name="company_address" 
                                      rows="2">{{ old('company_address') }}</textarea>
                            @error('company_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="logo" class="form-label">Company Logo</label>
                            <input type="file" 
                                   class="form-control @error('logo') is-invalid @enderror" 
                                   id="logo" 
                                   name="logo" 
                                   accept="image/*"
                                   onchange="previewLogo(this)">
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="mt-2">
                                <img id="logo-preview" src="#" alt="Logo Preview" 
                                     style="display: none; max-width: 150px; max-height: 150px; border: 1px solid #ddd; border-radius: 5px;">
                            </div>
                        </div>
                        
                        <div class="form-group mb-4">
                            <button type="submit" class="btn btn-primary w-100">Create Card</button>    
                        </div>
                    </form> 
                </div>
            </div>

            <!-- Live Preview Card -->
            <div class="card mt-4">
                <div class="card-header text-center">
                    <h5>Live Preview</h5>
                </div>
                <div class="card-body">
                    <div id="card-preview" class="business-card-preview">
                        <div class="preview-content">
                            <div class="preview-header d-flex align-items-center">
                                <img id="preview-logo" src="" alt="Logo" style="display: none; width: 50px; height: 50px; margin-right: 15px; border-radius: 5px;">
                                <div>
                                    <h5 id="preview-name" class="mb-1 text-primary">Your Name</h5>
                                    <h6 id="preview-position" class="mb-0 text-muted">Your Position</h6>
                                </div>
                            </div>
                            <hr>
                            <div class="preview-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6 id="preview-company" class="text-success">Your Company</h6>
                                        <p class="mb-1"><strong>Email:</strong> <span id="preview-email">your@email.com</span></p>
                                        <p class="mb-1"><strong>Phone:</strong> <span id="preview-phone">+1234567890</span></p>
                                        <p class="mb-1"><strong>Mobile:</strong> <span id="preview-mobile">+1234567890</span></p>
                                        <p class="mb-0"><strong>Address:</strong> <span id="preview-address">Your Address</span></p>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <div id="qr-code-preview">
                                            <div class="qr-placeholder">
                                                <i class="fas fa-qrcode fa-3x text-muted"></i>
                                                <p class="small text-muted mt-2">QR Code will be generated after creation</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Logo Preview Function
function previewLogo(input) {
    const preview = document.getElementById('logo-preview');
    const previewLogo = document.getElementById('preview-logo');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // File selected for upload
        const file = files[0];
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            
            previewLogo.src = e.target.result;
            previewLogo.style.display = 'block';
        };
        
        reader.readAsDataURL(file);
    }
}

// Live Preview Updates
document.addEventListener('DOMContentLoaded', function() {
    // Get all form inputs
    const nameInput = document.getElementById('name');
    const companyInput = document.getElementById('company');
    const positionInput = document.getElementById('position');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const mobileInput = document.getElementById('mobile');
    const addressInput = document.getElementById('address');
    
    // Get all preview elements
    const previewName = document.getElementById('preview-name');
    const previewCompany = document.getElementById('preview-company');
    const previewPosition = document.getElementById('preview-position');
    const previewEmail = document.getElementById('preview-email');
    const previewPhone = document.getElementById('preview-phone');
    const previewMobile = document.getElementById('preview-mobile');
    const previewAddress = document.getElementById('preview-address');
    
    // Update preview in real-time
    nameInput.addEventListener('input', function() {
        previewName.textContent = this.value || 'Your Name';
    });
    
    companyInput.addEventListener('input', function() {
        previewCompany.textContent = this.value || 'Your Company';
    });
    
    positionInput.addEventListener('input', function() {
        previewPosition.textContent = this.value || 'Your Position';
    });
    
    emailInput.addEventListener('input', function() {
        previewEmail.textContent = this.value || 'your@email.com';
    });
    
    phoneInput.addEventListener('input', function() {
        previewPhone.textContent = this.value || '+1234567890';
    });
    
    mobileInput.addEventListener('input', function() {
        previewMobile.textContent = this.value || '+1234567890';
    });
    
    addressInput.addEventListener('input', function() {
        previewAddress.textContent = this.value || 'Your Address';
    });
});
</script>
@endsection