@extends('layouts.app')

@section('title', 'Manage Cards')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="display-4">Card Management</h1>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

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

    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">All Cards</h6>
            <span class="badge bg-info">{{ $cards->total() }} cards total</span>
        </div>
        <div class="card-body">
            @if($cards->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Card</th>
                                <th>Owner</th>
                                <th>Position</th>
                                <th>Company</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cards as $card)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                <div class="avatar-title bg-info rounded-circle text-white">
                                                    {{ substr($card->name, 0, 1) }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $card->name }}</div>
                                                <small class="text-muted">{{ $card->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($card->user)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <div class="avatar-title bg-primary rounded-circle text-white">
                                                        {{ substr($card->user->name, 0, 1) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <div>{{ $card->user->name }}</div>
                                                    <small class="text-muted">{{ $card->user->email }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">No Owner</span>
                                        @endif
                                    </td>
                                    <td>{{ $card->position ?: 'Not specified' }}</td>
                                    <td>{{ $card->company ?: 'Not specified' }}</td>
                                    <td>
                                        <small class="text-muted" title="{{ $card->created_at->format('Y-m-d H:i:s') }}">
                                            {{ $card->created_at->format('M d, Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('cards.pdf.preview', $card) }}" 
                                               class="btn btn-outline-info" target="_blank" title="Preview PDF">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <a href="{{ route('cards.pdf', $card) }}" 
                                               class="btn btn-outline-success" title="Download PDF">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal{{ $card->id }}" title="Delete Card">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Delete Confirmation Modal -->
                                <div class="modal fade" id="deleteModal{{ $card->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirm Delete</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete the card for <strong>{{ $card->name }}</strong>?</p>
                                                <div class="card border-warning">
                                                    <div class="card-body p-2">
                                                        <small class="text-muted">
                                                            <strong>Card Details:</strong><br>
                                                            Name: {{ $card->name }}<br>
                                                            Email: {{ $card->email }}<br>
                                                            @if($card->position) Position: {{ $card->position }}<br> @endif
                                                            @if($card->company) Company: {{ $card->company }}<br> @endif
                                                            Owner: {{ $card->user ? $card->user->name : 'No Owner' }}
                                                        </small>
                                                    </div>
                                                </div>
                                                <p class="text-danger mt-2"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <form action="{{ route('admin.cards.delete', $card) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Delete Card</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $cards->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-id-card fa-3x text-gray-300 mb-3"></i>
                    <p class="text-muted">No cards found.</p>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create First Card
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 2rem;
    height: 2rem;
}

.avatar-title {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    font-size: 0.875rem;
    font-weight: 600;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

.btn-group-sm > .btn, .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.border-warning {
    border-color: #f6c23e !important;
}
</style>
@endsection
