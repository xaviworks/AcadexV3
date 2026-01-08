@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <h1 class="h3 text-dark fw-bold mb-2">
                <i class="bi bi-heart-pulse text-success"></i> System Health
            </h1>
            <p class="text-muted mb-0">Monitor your application's performance in real-time</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <form action="{{ route('admin.system-health.clear-cache') }}" method="POST" class="d-inline" 
                  onsubmit="return confirm('This will clear all cached data. Continue?');">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary" 
                        data-bs-toggle="tooltip" title="Clear application, config, route, and view caches">
                    <i class="bi bi-arrow-clockwise"></i> Clear Cache
                </button>
            </form>
            <form action="{{ route('admin.system-health.optimize-database') }}" method="POST" class="d-inline"
                  onsubmit="return confirm('This will optimize all database tables. Continue?');">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary"
                        data-bs-toggle="tooltip" title="Optimize and defragment database tables">
                    <i class="bi bi-database-gear"></i> Optimize DB
                </button>
            </form>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Main Metrics --}}
    <div class="row g-4 mb-4">
        {{-- CPU Load --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="bi bi-cpu text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="text-muted mb-1">CPU Load</h6>
                    <h4 class="mb-2">{{ number_format($serverMetrics['cpu_load'], 1) }}%</h4>
                    <div class="progress mx-auto" style="height: 6px; width: 80%;">
                        <div class="progress-bar {{ $serverMetrics['cpu_load'] > 80 ? 'bg-danger' : ($serverMetrics['cpu_load'] > 60 ? 'bg-warning' : 'bg-success') }}" 
                             style="width: {{ min($serverMetrics['cpu_load'], 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Memory Usage --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="bi bi-memory text-success" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="text-muted mb-1">Memory</h6>
                    <h4 class="mb-2">{{ number_format($serverMetrics['memory_usage'], 1) }}%</h4>
                    <div class="progress mx-auto" style="height: 6px; width: 80%;">
                        <div class="progress-bar {{ $serverMetrics['memory_usage'] > 80 ? 'bg-danger' : ($serverMetrics['memory_usage'] > 60 ? 'bg-warning' : 'bg-success') }}" 
                             style="width: {{ $serverMetrics['memory_usage'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Disk Space --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="bi bi-hdd text-warning" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="text-muted mb-1">Disk Space</h6>
                    <h4 class="mb-2">{{ number_format($serverMetrics['disk_usage'], 1) }}%</h4>
                    <div class="progress mx-auto" style="height: 6px; width: 80%;">
                        <div class="progress-bar {{ $serverMetrics['disk_usage'] > 80 ? 'bg-danger' : ($serverMetrics['disk_usage'] > 60 ? 'bg-warning' : 'bg-success') }}" 
                             style="width: {{ $serverMetrics['disk_usage'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Database Size --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="bi bi-database text-info" style="font-size: 2rem;"></i>
                    </div>
                    <h6 class="text-muted mb-1">Database</h6>
                    <h4 class="mb-2">{{ $databaseMetrics['database_size'] }}</h4>
                    <small class="text-muted">
                        <i class="bi bi-table"></i> {{ $databaseMetrics['total_tables'] }} tables
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Details Section --}}
    <div class="row g-4">
        {{-- System Details --}}
        <div class="col-md-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle text-primary me-2"></i>
                        <h6 class="mb-0">System Information</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block mb-2">PHP Version</small>
                                <strong class="d-block">{{ PHP_VERSION }}</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block mb-2">Laravel</small>
                                <strong class="d-block">{{ app()->version() }}</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block mb-2">Environment</small>
                                <span class="badge bg-{{ config('app.env') === 'production' ? 'danger' : 'warning' }}">
                                    {{ strtoupper(config('app.env')) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block mb-2">Debug Mode</small>
                                <span class="badge bg-{{ config('app.debug') ? 'warning' : 'success' }}">
                                    {{ config('app.debug') ? 'ENABLED' : 'DISABLED' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Errors --}}
    @if(count($recentErrors) > 0)
    <div class="row g-4 mt-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm border-start border-danger border-3">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                            <h6 class="mb-0 text-danger">Recent Errors</h6>
                        </div>
                        <span class="badge bg-danger">{{ count($recentErrors) }} error(s)</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-3" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        <small>Review these errors and take necessary action. Check full logs for details.</small>
                    </div>
                    <div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                        @foreach($recentErrors as $index => $error)
                        <div class="list-group-item border-0 {{ $index > 0 ? 'border-top' : '' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-1">
                                        <span class="badge bg-danger me-2">{{ $loop->iteration }}</span>
                                        <small class="text-muted">
                                            <i class="bi bi-clock"></i> {{ $error['time'] }}
                                        </small>
                                    </div>
                                    <p class="mb-0 text-dark">{{ Str::limit($error['message'], 150) }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row g-3 mt-0">
        <div class="col-12">
            <div class="card border-0 shadow-sm border-start border-success border-3">
                <div class="card-body text-center py-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 mb-2">All Clear! 🎉</h5>
                    <p class="text-muted mb-0">No errors detected in recent logs</p>
                    <small class="text-muted">Your application is running smoothly</small>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
