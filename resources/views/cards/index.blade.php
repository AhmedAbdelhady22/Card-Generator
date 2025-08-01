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
                <div class="card custom-card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            @if($card->logo)
                                <div class="logo-container  me-3">
                                    <img src="{{ asset('storage/' . $card->logo) }}" alt="Logo" 
                                         class=" rounded-circle shadow-sm" 
                                         style="width: 100px; height:100px; object-fit: cover;">
                                </div>
                            @else
                                <div class="logo-placeholder me-3">
                                    <div class="rounded-circle bg-gradient bg-primary d-flex align-items-center justify-content-center" 
                                         style="width: 100px; height: 100px;">
                                        <i class="fas fa-user text-white fs-4"></i>
                                    </div>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-1 fw-bold">{{ $card->name ?? 'Sample Card' }}</h5>
                                <h6 class="card-subtitle text-primary fw-semibold">{{ $card->company ?? 'Company Name' }}</h6>
                            </div>
                        </div>
                        
                        <div class="card-info mb-4">
                            @if($card->position)
                                <div class="position-badge mb-3">
                                    <span class="badge bg-light text-dark fs-6 px-3 py-2">
                                        <i class="fas fa-briefcase me-1"></i>{{ $card->position }}
                                    </span>
                                </div>
                            @endif
                            
                            <div class="contact-info">
                                <div class="contact-item-mini mb-2">
                                    <i class="fas fa-envelope text-primary me-2"></i>
                                    <span class="text-muted">{{ $card->email ?? 'email@example.com' }}</span>
                                </div>
                                
                                @if($card->phone)
                                    <div class="contact-item-mini mb-2">
                                        <i class="fas fa-phone text-success me-2"></i>
                                        <span class="text-muted">{{ $card->phone }}</span>
                                    </div>
                                @endif
                                
                                @if($card->mobile)
                                    <div class="contact-item-mini mb-2">
                                        <i class="fas fa-mobile-alt text-info me-2"></i>
                                        <span class="text-muted">{{ $card->mobile }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <div class="btn-group shadow-sm" role="group">
                                <a href="{{ route('cards.show', $card->id) }}" class="btn btn-outline-primary btn-sm ">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                                <a href="{{ route('cards.edit', $card->id) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </a>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-success btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-file-pdf me-1"></i>PDF
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item mr-2" href="{{ route('cards.pdf.preview', $card->id) }}" target="_blank">
                                            <i class="fas fa-eye me-2"></i>Preview PDF
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('cards.pdf', $card->id) }}">
                                            <i class="fas fa-download me-2"></i>Download PDF
                                        </a></li>
                                    </ul>
                                </div>
                                <form action="{{ route('cards.destroy', $card->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" 
                                            onclick="console.log('Form action:', this.form.action); return confirm('Are you sure you want to delete this card?')">
                                        <i class="fas fa-trash me-1"></i>Delete
                                    </button>
                                </form>
                            </div>
                            @if($card->qr_code)
                                <div class="qr-code-mini">
                                    <div class="qr-container" data-bs-toggle="tooltip" title="Scan QR Code">
                                        <img src="{{ asset('storage/' . $card->qr_code) }}" alt="QR Code" 
                                             class="qr-mini rounded shadow-sm" style="width: 45px; height: 45px;">
                                        <div class="qr-overlay">
                                            <i class="fas fa-qrcode text-white"></i>
                                        </div>
                                    </div>
                                </div>
                            @endif
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
