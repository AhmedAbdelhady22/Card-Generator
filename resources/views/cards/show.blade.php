@extends('layouts.app')
@section('title', 'Card Details')
@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-lg border-0" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-primary text-black text-center py-4">
                    <h3 class="mb-0"><i class="fas fa-id-card me-2"></i>Business Card Details</h3>
                </div>
                <div class="card-body p-5">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row mb-4">
                                <div class="col-auto">
                                    @if($card->logo)
                                        <img src="{{ asset('storage/' . $card->logo) }}" alt="Company Logo" 
                                             class="rounded-circle border border-3 border-light shadow"
                                             style="width: 100px; height: 100px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-light border border-3 d-flex align-items-center justify-content-center" 
                                             style="width: 100px; height: 100px;">
                                            <i class="fas fa-building text-muted fa-2x"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="col">
                                    <h2 class="text-primary fw-bold mb-1">{{ $card->name }}</h2>
                                    <h5 class="text-secondary mb-0">{{ $card->company }}</h5>
                                    @if($card->position)
                                        <p class="text-muted mb-0"><em>{{ $card->position }}</em></p>
                                    @endif
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="text-primary border-bottom pb-2 mb-3">
                                        <i class="fas fa-address-book me-2"></i>Contact Information
                                    </h5>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center p-3 bg-light rounded contact-item">
                                                <div class="icon-wrapper me-3">
                                                    <i class="fas fa-envelope text-primary"></i>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block">Email</small>
                                                    <strong>{{ $card->email }}</strong>
                                                </div>
                                            </div>
                                        </div>

                                        @if($card->phone)
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center p-3 bg-light rounded contact-item">
                                                    <div class="icon-wrapper me-3">
                                                        <i class="fas fa-phone text-success"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">Phone</small>
                                                        <strong>{{ $card->phone }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($card->mobile)
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center p-3 bg-light rounded contact-item">
                                                    <div class="icon-wrapper me-3">
                                                        <i class="fas fa-mobile-alt text-info"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">Mobile</small>
                                                        <strong>{{ $card->mobile }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($card->address)
                                            <div class="col-12">
                                                <div class="d-flex align-items-start p-3 bg-light rounded contact-item">
                                                    <div class="icon-wrapper me-3 mt-1">
                                                        <i class="fas fa-map-marker-alt text-danger"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">Address</small>
                                                        <strong>{{ $card->address }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($card->company_address)
                                            <div class="col-12">
                                                <div class="d-flex align-items-start p-3 bg-light rounded contact-item">
                                                    <div class="icon-wrapper me-3 mt-1">
                                                        <i class="fas fa-building text-secondary"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">Company Address</small>
                                                        <strong>{{ $card->company_address }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 d-flex flex-column align-items-center justify-content-center">
                            @if($card->qr_code)
                                <div class="text-center">
                                    <div class="bg-gradient bg-primary text-white p-3 rounded-top">
                                        <h6 class="mb-0 fw-bold">
                                            <i class="fas fa-qrcode me-2"></i>QR Code
                                        </h6>
                                    </div>
                                    <div class="bg-white p-4 rounded-bottom shadow-sm border">
                                        <img src="{{ asset('storage/' . $card->qr_code) }}" alt="QR Code" 
                                             class="img-fluid rounded shadow-sm" 
                                             style="width: 180px; height: 180px;">
                                        <small class="d-block text-muted mt-3">
                                            <i class="fas fa-mobile-alt me-1"></i>
                                            Scan to view this card
                                        </small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    
                    <div class="row mt-5">
                        <div class="col-12 text-center">
                            <div class="btn-group shadow-sm" role="group">
                                <a href="{{ route('cards.index') }}" class="btn btn-outline-secondary ">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Cards
                                </a>
                                <a href="{{ route('cards.edit', $card->id) }}" class="btn btn-primary ">
                                    <i class="fas fa-edit me-2"></i>Edit Card
                                </a>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-success  dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-file-pdf me-2"></i>Export PDF
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('cards.pdf.preview', $card->id) }}" target="_blank">
                                            <i class="fas fa-eye me-2"></i>Preview PDF
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('cards.pdf', $card->id) }}">
                                            <i class="fas fa-download me-2"></i>Download PDF
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection