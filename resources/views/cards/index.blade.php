@extends('layouts.app')

@section('title', 'My Cards')

@section('content')
<div class="container mt-4 cards-index">
    <div class="row">
        <div class="col-12">
            <div class="hero-header text-center mb-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="display-5 fw-bold text-primary mb-2">
                            <i class="fas fa-id-card me-3"></i>My Business Cards
                        </h1>
                        <p class="lead text-muted">Manage your professional digital business cards</p>
                    </div>
                    <a href="{{ route('cards.create') }}" class="btn btn-primary btn-lg shadow-sm">
                        <i class="fas fa-plus me-2"></i>Create New Card
                    </a>
                </div>
            </div>
            
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>
    </div>

    <div class="row g-4">
        @forelse($cards ?? [] as $card)
            <div class="col-lg-6 col-lg-4 mb-4">
                <div class="card custom-card h-100 border-0 shadow-sm {{ !$card->is_active ? 'opacity-75' : '' }}">
                    <!-- Add inactive overlay if needed -->
                    @if(!$card->is_active)
                        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
                             style="background: rgba(0,0,0,0.1); z-index: 1; border-radius: 0.375rem;">
                            <span class="badge bg-secondary fs-6 px-3 py-2">
                                <i class="fas fa-pause me-1"></i>Inactive
                            </span>
                        </div>
                    @endif
                    
                    <div class="card-body p-4 d-flex flex-column">
                        <!-- Card Content - This will expand to fill available space -->
                        <div class="flex-grow-1">
                            <!-- Header section with logo and text -->
                            <div class="card-header">
                                @if($card->logo)
                                    <div class="logo-container">
                                        <img src="{{ asset('storage/' . $card->logo) }}" alt="Logo">
                                    </div>
                                @else
                                    <div class="logo-placeholder">
                                        <div class="rounded-circle">
                                            <i class="fas fa-user text-white fs-4"></i>
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="text-content">
                                    <h5 class="card-title">{{ $card->name ?? 'Sample Card' }}</h5>
                                    <h6 class="card-subtitle">{{ $card->company ?? 'Company Name' }}</h6>
                                </div>
                            </div>
                            
                            <!-- Position Badge -->
                            @if($card->position)
                                <div class="position-badge">
                                    <span class="badge">
                                        <i class="fas fa-briefcase me-1"></i>{{ $card->position }}
                                    </span>
                                </div>
                            @endif
                            
                            <!-- Contact Info Section -->
                            <div class="contact-info">
                                <div class="contact-item-mini">
                                    <i class="fas fa-envelope text-primary"></i>
                                    <span class="text-muted">{{ $card->email ?? 'email@example.com' }}</span>
                                </div>
                                
                                @if($card->phone)
                                    <div class="contact-item-mini">
                                        <i class="fas fa-phone text-success"></i>
                                        <span class="text-muted">{{ $card->phone }}</span>
                                    </div>
                                @endif
                                
                                @if($card->mobile)
                                    <div class="contact-item-mini">
                                        <i class="fas fa-mobile-alt text-info"></i>
                                        <span class="text-muted">{{ $card->mobile }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Fixed Position Buttons Section - Always at bottom -->
                        <div class="card-actions-fixed mt-auto pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <!-- Action Buttons -->
                                <div class="btn-group shadow-sm mb-2 mb-lg-0" role="group">
                                    <a href="{{ route('cards.show', $card->id) }}" 
                                       class="btn btn-outline-primary btn-sm {{ !$card->is_active ? 'disabled' : '' }}">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                    <a href="{{ route('cards.edit', $card->id) }}" 
                                       class="btn btn-outline-secondary btn-sm {{ !$card->is_active ? 'disabled' : '' }}">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </a>
                                    
                                    <!-- Status Toggle Button (Always available) -->
                                    <form action="{{ route('cards.toggle-status', $card->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-{{ $card->is_active ? 'warning' : 'success' }} btn-sm" 
                                                onclick="return confirm('Are you sure you want to {{ $card->is_active ? 'deactivate' : 'activate' }} this card?')">
                                            @if($card->is_active)
                                                <i class="fas fa-pause me-1"></i>Deactivate
                                            @else
                                                <i class="fas fa-play me-1"></i>Activate
                                            @endif
                                        </button>
                                    </form>
                                    
                                    <!-- PDF actions - show always but disabled for inactive cards -->
                                    <div class="btn-group" role="group">
                                        <button type="button" 
                                                class="btn btn-outline-success btn-sm dropdown-toggle {{ !$card->is_active ? 'disabled' : '' }}" 
                                                data-bs-toggle="dropdown" 
                                                aria-expanded="false"
                                                {{ !$card->is_active ? 'disabled' : '' }}>
                                            <i class="fas fa-file-pdf me-1"></i>PDF
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item {{ !$card->is_active ? 'disabled' : '' }}" 
                                                   href="{{ $card->is_active ? route('cards.pdf.preview', $card->id) : '#' }}" 
                                                   {{ $card->is_active ? 'target="_blank"' : '' }}>
                                                    <i class="fas fa-eye me-2"></i>Preview PDF
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item {{ !$card->is_active ? 'disabled' : '' }}" 
                                                   href="{{ $card->is_active ? route('cards.pdf', $card->id) : '#' }}">
                                                    <i class="fas fa-download me-2"></i>Download PDF
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Delete button - show always but disabled for inactive cards -->
                                    <form action="{{ route('cards.destroy', $card->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-outline-danger btn-sm {{ !$card->is_active ? 'disabled' : '' }}" 
                                                onclick="return {{ $card->is_active ? 'confirm(\'Are you sure you want to delete this card?\')' : 'false' }}"
                                                {{ !$card->is_active ? 'disabled' : '' }}>
                                            <i class="fas fa-trash me-1"></i>Delete
                                        </button>
                                    </form>
                                </div>
                                
                                <!-- Status Badge and QR Code - Always show -->
                                <div class="status-badge-container d-flex align-items-center">
                                    <!-- Always show QR code if it exists -->
                                    @if($card->qr_code)
                                        <div class="qr-code-mini">
                                            <div class="qr-container" data-bs-toggle="tooltip" title="QR Code{{ !$card->is_active ? ' (Inactive)' : '' }}">
                                                <img src="{{ asset('storage/' . $card->qr_code) }}" alt="QR Code" 
                                                     class="qr-mini rounded shadow-sm {{ !$card->is_active ? 'opacity-50' : '' }}" 
                                                     style="width: 45px; height: 45px; object-fit: cover;">
                                                <div class="qr-overlay">
                                                    <i class="fas fa-qrcode text-white"></i>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <!-- Show placeholder if no QR code -->
                                        <div class="qr-code-mini">
                                            <div class="qr-container" data-bs-toggle="tooltip" title="No QR Code">
                                                <div class="bg-light border rounded d-flex align-items-center justify-content-center"
                                                     style="width: 45px; height: 45px;">
                                                    <i class="fas fa-qrcode text-muted"></i>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="empty-state text-center py-5">
                    <div class="empty-icon mb-4">
                        <i class="fas fa-id-card-alt text-muted" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="text-muted mb-3">No Business Cards Yet</h3>
                    <p class="lead text-muted mb-4">Create your first professional business card to get started</p>
                    <a href="{{ route('cards.create') }}" class="btn btn-primary btn-lg shadow-sm">
                        <i class="fas fa-plus me-2"></i>Create Your First Card
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
