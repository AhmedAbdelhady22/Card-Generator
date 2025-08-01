@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid mt-4">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-header bg-gradient-primary text-white rounded-4 p-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="display-6 fw-bold mb-2">
                            <i class="fas fa-tachometer-alt me-3"></i>Welcome back, {{ Auth::user()->name }}!
                        </h1>
                        <p class="lead mb-0 opacity-90">Here's an overview of your business cards and activity</p>
                    </div>
                    <div class="text-end">
                        <div class="badge bg-white text-primary fs-6 px-3 py-2">
                            <i class="fas fa-calendar me-1"></i>{{ date('M d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card bg-primary text-white rounded-4 p-4 h-100 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-icon mb-3">
                            <i class="fas fa-id-card fa-2x opacity-75"></i>
                        </div>
                        <h3 class="fw-bold mb-1">{{ $stats['total_cards'] }}</h3>
                        <p class="mb-0 opacity-90">Total Cards</p>
                    </div>
                    <div class="stat-trend">
                        <div class="trend-indicator bg-white bg-opacity-20 rounded-circle p-2">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card bg-success text-white rounded-4 p-4 h-100 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-icon mb-3">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                        <h3 class="fw-bold mb-1">{{ $stats['active_cards'] }}</h3>
                        <p class="mb-0 opacity-90">Active Cards</p>
                    </div>
                    <div class="stat-trend">
                        <div class="trend-indicator bg-white bg-opacity-20 rounded-circle p-2">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card bg-warning text-white rounded-4 p-4 h-100 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-icon mb-3">
                            <i class="fas fa-pause-circle fa-2x opacity-75"></i>
                        </div>
                        <h3 class="fw-bold mb-1">{{ $stats['inactive_cards'] }}</h3>
                        <p class="mb-0 opacity-90">Inactive Cards</p>
                    </div>
                    <div class="stat-trend">
                        <div class="trend-indicator bg-white bg-opacity-20 rounded-circle p-2">
                            <i class="fas fa-eye-slash"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card bg-info text-white rounded-4 p-4 h-100 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-icon mb-3">
                            <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                        </div>
                        <h3 class="fw-bold mb-1">{{ $stats['cards_this_month'] }}</h3>
                        <p class="mb-0 opacity-90">This Month</p>
                    </div>
                    <div class="stat-trend">
                        <div class="trend-indicator bg-white bg-opacity-20 rounded-circle p-2">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row g-4">
        <!-- Recent Cards -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-clock text-primary me-2"></i>Recent Cards
                        </h5>
                        <a href="{{ route('cards.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($recentCards->count() > 0)
                        <div class="row g-3">
                            @foreach($recentCards as $card)
                                <div class="col-md-6 col-lg-4">
                                    <div class="mini-card border rounded-3 p-3 h-100 hover-lift">
                                        <div class="d-flex align-items-center mb-2">
                                            @if($card->logo)
                                                <img src="{{ asset('storage/' . $card->logo) }}" alt="Logo" 
                                                     class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            @endif
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0 fw-semibold">{{ $card->name }}</h6>
                                                <small class="text-muted">{{ $card->company }}</small>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge {{ $card->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $card->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                            <a href="{{ route('cards.show', $card->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-id-card text-muted fa-3x mb-3"></i>
                            <h6 class="text-muted">No cards created yet</h6>
                            <a href="{{ route('cards.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Create Your First Card
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions & Activity -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-bolt text-warning me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('cards.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create New Card
                        </a>
                        <a href="{{ route('cards.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i>View All Cards
                        </a>
                        {{-- <button class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Print Dashboard
                        </button> --}}
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-history text-info me-2"></i>Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentActivity && $recentActivity->count() > 0)
                        <div class="activity-timeline">
                            @foreach($recentActivity as $activity)
                                <div class="activity-item d-flex align-items-start mb-3">
                                    <div class="activity-icon me-3">
                                        <div class="bg-primary rounded-circle p-2">
                                            <i class="fas fa-{{ $activity->action === 'created' ? 'plus' : ($activity->action === 'updated' ? 'edit' : 'trash') }} text-white small"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1 small">
                                            <strong>{{ ucfirst($activity->action) }}</strong> card
                                            @if($activity->card_name)
                                                "{{ $activity->card_name }}"
                                            @endif
                                        </p>
                                        <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-history text-muted fa-2x mb-2"></i>
                            <p class="text-muted mb-0">No recent activity</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Chart -->
    @if($stats['total_cards'] > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-chart-line text-success me-2"></i>Card Creation Trend
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="cardTrendChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Chart.js Script -->
@if($stats['total_cards'] > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('cardTrendChart').getContext('2d');
    const cardTrend = @json($cardTrend);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: cardTrend.map(item => item.month),
            datasets: [{
                label: 'Cards Created',
                data: cardTrend.map(item => item.count),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
@endif
@endsection
