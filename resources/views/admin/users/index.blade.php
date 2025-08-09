@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="display-4">User Management</h1>
                <div class="btn-group" role="group">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New User
                    </a>
                </div>
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
            <h6 class="m-0 font-weight-bold text-primary">All Users</h6>
            <span class="badge bg-info">{{ $users->total() }} users total</span>
        </div>
        <div class="card-body">
            @if($users->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Cards</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                <div class="avatar-title bg-primary rounded-circle text-white">
                                                    {{ substr($user->name, 0, 1) }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $user->name }}</div>
                                                @if($user->id === auth()->id())
                                                    <small class="text-muted">(You)</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->role)
                                            <span class="badge bg-{{ $user->role->name === 'admin' ? 'danger' : 'info' }}">
                                                {{ ucfirst($user->role->name) }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">No Role</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $user->is_active ? 'success' : 'warning' }}">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $user->cards->count() }} cards</span>
                                    </td>
                                    <td>
                                        <small class="text-muted" title="{{ $user->created_at->format('Y-m-d H:i:s') }}">
                                            {{ $user->created_at->format('M d, Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary" title="Edit User">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <a href="{{ route('admin.users.permissions', $user) }}" class="btn btn-outline-info" title="Manage Permissions">
                                                <i class="fas fa-shield-alt"></i>
                                            </a>
                                            
                                            @if($user->id !== auth()->id())
                                                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-outline-{{ $user->is_active ? 'warning' : 'success' }}" 
                                                            title="{{ $user->is_active ? 'Deactivate' : 'Activate' }} User">
                                                        <i class="fas fa-{{ $user->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                                
                                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal{{ $user->id }}" title="Delete User">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-outline-secondary" disabled title="Cannot modify your own account">
                                                    <i class="fas fa-lock"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                <!-- Delete Confirmation Modal -->
                                @if($user->id !== auth()->id())
                                    <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to delete user <strong>{{ $user->name }}</strong>?</p>
                                                    <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('admin.users.delete', $user) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Delete User</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $users->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-users fa-3x text-gray-300 mb-3"></i>
                    <p class="text-muted">No users found.</p>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add First User
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
</style>
@endsection
