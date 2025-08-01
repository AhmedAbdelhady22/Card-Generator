@extends('layouts.app')

@section('title', 'Manage Permissions')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="display-4">Permission Management</h1>
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

    <div class="row">
        @foreach($roles as $role)
            <div class="col-md-6 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            {{ ucfirst($role->name) }} Role
                        </h6>
                        <span class="badge bg-info">{{ $role->permissions->count() }} permissions</span>
                    </div>
                    <div class="card-body">
                        @if($role->description)
                            <p class="text-muted mb-3">{{ $role->description }}</p>
                        @endif

                        <form action="{{ route('admin.roles.permissions.update', $role) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Assign Permissions:</label>
                                <div class="row">
                                    @if($permissions->count() > 0)
                                        @foreach($permissions as $permission)
                                            <div class="col-12 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           id="permission_{{ $role->id }}_{{ $permission->id }}" 
                                                           name="permissions[]" value="{{ $permission->id }}"
                                                           {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="permission_{{ $role->id }}_{{ $permission->id }}">
                                                        <strong>{{ $permission->name }}</strong>
                                                        @if($permission->description)
                                                            <small class="text-muted d-block">{{ $permission->description }}</small>
                                                        @endif
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="col-12">
                                            <div class="text-center py-3">
                                                <i class="fas fa-shield-alt fa-2x text-gray-300 mb-2"></i>
                                                <p class="text-muted">No permissions available.</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if($permissions->count() > 0)
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="selectAllPermissions({{ $role->id }})">
                                            Select All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="deselectAllPermissions({{ $role->id }})">
                                            Deselect All
                                        </button>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Permissions
                                    </button>
                                </div>
                            @endif
                        </form>

                        <!-- Current Users with this Role -->
                        @php
                            $roleUsers = \App\Models\User::where('role_id', $role->id)->limit(5)->get();
                            $userCount = \App\Models\User::where('role_id', $role->id)->count();
                        @endphp

                        @if($userCount > 0)
                            <hr>
                            <h6 class="fw-bold text-muted mb-2">Users with this role ({{ $userCount }} total):</h6>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($roleUsers as $user)
                                    <span class="badge bg-light text-dark">{{ $user->name }}</span>
                                @endforeach
                                @if($userCount > 5)
                                    <span class="badge bg-secondary">+{{ $userCount - 5 }} more</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($roles->count() === 0)
        <div class="card shadow">
            <div class="card-body text-center py-5">
                <i class="fas fa-user-shield fa-3x text-gray-300 mb-3"></i>
                <h5 class="text-muted">No Roles Available</h5>
                <p class="text-muted">There are no roles configured in the system.</p>
            </div>
        </div>
    @endif
</div>

<script>
function selectAllPermissions(roleId) {
    const checkboxes = document.querySelectorAll(`input[name="permissions[]"][id*="permission_${roleId}_"]`);
    checkboxes.forEach(checkbox => checkbox.checked = true);
}

function deselectAllPermissions(roleId) {
    const checkboxes = document.querySelectorAll(`input[name="permissions[]"][id*="permission_${roleId}_"]`);
    checkboxes.forEach(checkbox => checkbox.checked = false);
}
</script>

<style>
.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

.form-check-input:checked {
    background-color: #4e73df;
    border-color: #4e73df;
}

.form-check-input:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

.badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.btn-primary {
    background-color: #4e73df;
    border-color: #4e73df;
}

.btn-primary:hover {
    background-color: #2e59d9;
    border-color: #2653d4;
}
</style>
@endsection
