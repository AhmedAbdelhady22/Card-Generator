@extends('layouts.app')

@section('title', 'Activity Logs')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="display-4">Activity Logs</h1>
                <div class="btn-group" role="group">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <a href="{{ route('admin.logs.export', request()->all()) }}" class="btn btn-success">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.logs') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="from_date" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="from_date" name="from_date" 
                                   value="{{ request('from_date') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="to_date" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="to_date" name="to_date" 
                                   value="{{ request('to_date') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="action" class="form-label">Action</label>
                            <select class="form-select" id="action" name="action">
                                <option value="">All Actions</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                                        {{ ucfirst($action) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">User</label>
                            <select class="form-select" id="user_id" name="user_id">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="ip_address" class="form-label">IP Address</label>
                            <input type="text" class="form-control" id="ip_address" name="ip_address" 
                                   value="{{ request('ip_address') }}" placeholder="e.g., 192.168">
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <a href="{{ route('admin.logs') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Activity Logs</h6>
            <span class="badge bg-info">{{ $logs->total() }} logs total</span>
        </div>
        <div class="card-body">
            @if($logs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Model</th>
                                <th>Description</th>
                                <th>IP Address</th>
                                <th>MAC Address</th>
                                <th>Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td><code>{{ $log->id }}</code></td>
                                    <td>
                                        @if($log->user)
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <div class="avatar-title bg-primary rounded-circle text-white">
                                                        {{ substr($log->user->name, 0, 1) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $log->user->name }}</div>
                                                    <small class="text-muted">{{ $log->user->email }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">Unknown User</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $log->action === 'created' ? 'success' : 
                                            ($log->action === 'deleted' ? 'danger' : 
                                            ($log->action === 'updated' ? 'warning' : 
                                            ($log->action === 'viewed' ? 'info' : 'secondary'))) 
                                        }}">
                                            {{ ucfirst($log->action) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($log->model_type)
                                            <div>
                                                <strong>{{ class_basename($log->model_type) }}</strong>
                                                @if($log->model_id)
                                                    <small class="text-muted d-block">ID: {{ $log->model_id }}</small>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;" title="{{ $log->description }}">
                                            {{ $log->description }}
                                        </div>
                                    </td>
                                    <td><code>{{ $log->ip_address }}</code></td>
                                    <td>
                                        @if($log->mac_address && $log->mac_address !== 'Unknown')
                                            <code>{{ $log->mac_address }}</code>
                                        @else
                                            <span class="text-muted">Unknown</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div title="{{ $log->created_at->format('Y-m-d H:i:s') }}">
                                            <strong>{{ $log->created_at->format('M d, Y') }}</strong>
                                            <small class="text-muted d-block">{{ $log->created_at->format('H:i:s') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.logs.show', $log) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $logs->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-clipboard-list fa-3x text-gray-300 mb-3"></i>
                    <p class="text-muted">No activity logs found.</p>
                    @if(request()->anyFilled(['from_date', 'to_date', 'action', 'user_id', 'ip_address']))
                        <a href="{{ route('admin.logs') }}" class="btn btn-primary">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    @endif
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

.badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.table th {
    border-top: none;
    font-weight: 600;
}

.text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
@endsection
