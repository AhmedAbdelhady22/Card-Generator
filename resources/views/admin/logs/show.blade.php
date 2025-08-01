@extends('layouts.app')

@section('title', 'Activity Log Details')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="display-4">Activity Log Details</h1>
                <a href="{{ route('admin.logs') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Logs
                </a>
            </div>

            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Log ID: {{ $log->id }}</h6>
                    <span class="badge bg-{{ 
                        $log->action === 'created' ? 'success' : 
                        ($log->action === 'deleted' ? 'danger' : 
                        ($log->action === 'updated' ? 'warning' : 
                        ($log->action === 'viewed' ? 'info' : 'secondary'))) 
                    }}">
                        {{ ucfirst($log->action) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h6 class="fw-bold text-muted mb-3">Basic Information</h6>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">User:</label>
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
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Action:</label>
                                <div>
                                    <span class="badge bg-{{ 
                                        $log->action === 'created' ? 'success' : 
                                        ($log->action === 'deleted' ? 'danger' : 
                                        ($log->action === 'updated' ? 'warning' : 
                                        ($log->action === 'viewed' ? 'info' : 'secondary'))) 
                                    }}">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Model:</label>
                                <div>
                                    @if($log->model_type)
                                        <div>
                                            <strong>{{ class_basename($log->model_type) }}</strong>
                                            @if($log->model_id)
                                                <small class="text-muted">(ID: {{ $log->model_id }})</small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Description:</label>
                                <div class="p-2 bg-light rounded">
                                    {{ $log->description ?: 'No description provided' }}
                                </div>
                            </div>
                        </div>

                        <!-- Technical Information -->
                        <div class="col-md-6">
                            <h6 class="fw-bold text-muted mb-3">Technical Information</h6>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">IP Address:</label>
                                <div><code>{{ $log->ip_address }}</code></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">MAC Address:</label>
                                <div>
                                    @if($log->mac_address && $log->mac_address !== 'Unknown')
                                        <code>{{ $log->mac_address }}</code>
                                    @else
                                        <span class="text-muted">Unknown</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">User Agent:</label>
                                <div class="p-2 bg-light rounded" style="word-break: break-all; font-size: 0.875rem;">
                                    {{ $log->user_agent ?: 'Unknown' }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Timestamp:</label>
                                <div>
                                    <strong>{{ $log->created_at->format('Y-m-d H:i:s') }}</strong>
                                    <small class="text-muted d-block">{{ $log->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Changes -->
                    @if($log->old_data || $log->new_data)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6 class="fw-bold text-muted mb-3">Data Changes</h6>
                            </div>
                            
                            @if($log->old_data)
                                <div class="col-md-6">
                                    <h6 class="text-danger">Old Data:</h6>
                                    <div class="p-3 bg-light rounded">
                                        <pre class="mb-0" style="font-size: 0.875rem; white-space: pre-wrap;">{{ json_encode($log->old_data, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </div>
                            @endif

                            @if($log->new_data)
                                <div class="col-md-6">
                                    <h6 class="text-success">New Data:</h6>
                                    <div class="p-3 bg-light rounded">
                                        <pre class="mb-0" style="font-size: 0.875rem; white-space: pre-wrap;">{{ json_encode($log->new_data, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Navigation -->
                    <hr>
                    <div class="d-flex justify-content-between">
                        @php
                            $prevLog = \App\Models\ActivityLog::where('id', '<', $log->id)->orderBy('id', 'desc')->first();
                            $nextLog = \App\Models\ActivityLog::where('id', '>', $log->id)->orderBy('id', 'asc')->first();
                        @endphp

                        <div>
                            @if($prevLog)
                                <a href="{{ route('admin.logs.show', $prevLog) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-chevron-left"></i> Previous Log
                                </a>
                            @endif
                        </div>

                        <div>
                            @if($nextLog)
                                <a href="{{ route('admin.logs.show', $nextLog) }}" class="btn btn-outline-secondary">
                                    Next Log <i class="fas fa-chevron-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
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

.form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #6c757d;
}

.badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

pre {
    background-color: transparent !important;
    border: none !important;
    color: #333 !important;
}

.bg-light {
    background-color: #f8f9fa !important;
    border: 1px solid #e3e6f0 !important;
}
</style>
@endsection
