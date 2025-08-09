@extends('layouts.app')

@section('title', 'Manage User Permissions')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="display-4">Manage Permissions: {{ $user->name }}</h1>
                <div class="btn-group" role="group">
                    <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Users
                    </a>
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit"></i> Edit User
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

    <div class="row">
        <!-- User Information -->
        <div class="col-md-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-user-circle fa-4x text-gray-300"></i>
                    </div>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <td><strong>Role:</strong></td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $user->role ? ucfirst($user->role->name) : 'No Role' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($user->role && $rolePermissions->count() > 0)
                <div class="card shadow mt-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">Role Permissions</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Permissions inherited from the {{ ucfirst($user->role->name) }} role:</p>
                        @foreach($rolePermissions as $permission)
                            <span class="badge bg-light text-dark mb-1">
                                <i class="fas fa-shield-alt"></i> {{ $permission->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Permission Management -->
        <div class="col-md-8">
            <form action="{{ route('admin.users.permissions.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Individual User Permissions</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">
                            <i class="fas fa-info-circle"></i>
                            Individual permissions override role permissions. Denied permissions take precedence over granted permissions.
                        </p>

                        @if($allPermissions->count() > 0)
                            <div class="row">
                                @foreach($allPermissions as $permission)
                                    @php
                                        $hasFromRole = $user->role && $rolePermissions->contains($permission);
                                        $hasGranted = $user->userPermissions->contains($permission);
                                        $hasDenied = $user->deniedPermissions->contains($permission);
                                        $effectiveStatus = '';
                                        
                                        if ($hasDenied) {
                                            $effectiveStatus = 'denied';
                                        } elseif ($hasGranted) {
                                            $effectiveStatus = 'granted';
                                        } elseif ($hasFromRole) {
                                            $effectiveStatus = 'role';
                                        } else {
                                            $effectiveStatus = 'none';
                                        }
                                    @endphp

                                    <div class="col-md-6 mb-3">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <h6 class="mb-1">{{ $permission->name }}</h6>
                                                        @if($permission->description)
                                                            <small class="text-muted">{{ $permission->description }}</small>
                                                        @endif
                                                    </div>
                                                    <div class="text-end">
                                                        @if($effectiveStatus === 'denied')
                                                            <span class="badge bg-danger">Denied</span>
                                                        @elseif($effectiveStatus === 'granted')
                                                            <span class="badge bg-success">Granted</span>
                                                        @elseif($effectiveStatus === 'role')
                                                            <span class="badge bg-info">From Role</span>
                                                        @else
                                                            <span class="badge bg-secondary">Not Granted</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   id="granted_{{ $permission->id }}" 
                                                                   name="granted_permissions[]" 
                                                                   value="{{ $permission->id }}"
                                                                   {{ $hasGranted ? 'checked' : '' }}
                                                                   onchange="handlePermissionChange({{ $permission->id }}, 'granted')">
                                                            <label class="form-check-label text-success" for="granted_{{ $permission->id }}">
                                                                <i class="fas fa-check"></i> Grant
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   id="denied_{{ $permission->id }}" 
                                                                   name="denied_permissions[]" 
                                                                   value="{{ $permission->id }}"
                                                                   {{ $hasDenied ? 'checked' : '' }}
                                                                   onchange="handlePermissionChange({{ $permission->id }}, 'denied')">
                                                            <label class="form-check-label text-danger" for="denied_{{ $permission->id }}">
                                                                <i class="fas fa-times"></i> Deny
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Update User Permissions
                                </button>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-shield-alt fa-3x text-gray-300 mb-3"></i>
                                <h5 class="text-muted">No Permissions Available</h5>
                                <p class="text-muted">There are no permissions configured in the system.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function handlePermissionChange(permissionId, type) {
    const grantedCheckbox = document.getElementById(`granted_${permissionId}`);
    const deniedCheckbox = document.getElementById(`denied_${permissionId}`);
    
    if (type === 'granted' && grantedCheckbox.checked) {
        // If granting, uncheck denied
        deniedCheckbox.checked = false;
    } else if (type === 'denied' && deniedCheckbox.checked) {
        // If denying, uncheck granted
        grantedCheckbox.checked = false;
    }
}

// Bulk actions
function grantAllPermissions() {
    const grantedCheckboxes = document.querySelectorAll('input[name="granted_permissions[]"]');
    const deniedCheckboxes = document.querySelectorAll('input[name="denied_permissions[]"]');
    
    grantedCheckboxes.forEach(checkbox => checkbox.checked = true);
    deniedCheckboxes.forEach(checkbox => checkbox.checked = false);
}

function denyAllPermissions() {
    const grantedCheckboxes = document.querySelectorAll('input[name="granted_permissions[]"]');
    const deniedCheckboxes = document.querySelectorAll('input[name="denied_permissions[]"]');
    
    grantedCheckboxes.forEach(checkbox => checkbox.checked = false);
    deniedCheckboxes.forEach(checkbox => checkbox.checked = true);
}

function clearAllPermissions() {
    const grantedCheckboxes = document.querySelectorAll('input[name="granted_permissions[]"]');
    const deniedCheckboxes = document.querySelectorAll('input[name="denied_permissions[]"]');
    
    grantedCheckboxes.forEach(checkbox => checkbox.checked = false);
    deniedCheckboxes.forEach(checkbox => checkbox.checked = false);
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
</style>
@endsection
