@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="display-4">Admin Dashboard</h1>
                <div class="btn-group" role="group">
                    <a href="{{ route('admin.users') }}" class="btn btn-outline-primary">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                    <a href="{{ route('admin.cards') }}" class="btn btn-outline-info">
                        <i class="fas fa-id-card"></i> Manage Cards
                    </a>
                    <a href="{{ route('admin.logs') }}" class="btn btn-outline-warning">
                        <i class="fas fa-clipboard-list"></i> Activity Logs
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Users
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_users'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Cards
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_cards'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-id-card fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Admins
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_admins'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Users This Month
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['users_this_month'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activities</h6>
                    <a href="{{ route('admin.logs') }}" class="btn btn-sm btn-primary">View All Logs</a>
                </div>
                <div class="card-body">
                    @if($stats['recent_activities']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Description</th>
                                        <th>IP Address</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stats['recent_activities'] as $activity)
                                        <tr>
                                            <td>
                                                @if($activity->user)
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm me-2">
                                                            <div class="avatar-title bg-primary rounded-circle text-white">
                                                                {{ substr($activity->user->name, 0, 1) }}
                                                            </div>
                                                        </div>
                                                        {{ $activity->user->name }}
                                                    </div>
                                                @else
                                                    <span class="text-muted">Unknown User</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $activity->action === 'created' ? 'success' : ($activity->action === 'deleted' ? 'danger' : 'info') }}">
                                                    {{ ucfirst($activity->action) }}
                                                </span>
                                            </td>
                                            <td>{{ $activity->description }}</td>
                                            <td><code>{{ $activity->ip_address }}</code></td>
                                            <td>
                                                <small class="text-muted" title="{{ $activity->created_at->format('Y-m-d H:i:s') }}">
                                                    {{ $activity->created_at->diffForHumans() }}
                                                </small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-clipboard-list fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">No recent activities found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

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

.badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.badge-success {
    background-color: #1cc88a;
}

.badge-danger {
    background-color: #e74a3b;
}

.badge-info {
    background-color: #36b9cc;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}
</style>
@endsection