@extends('layouts.app')

@section('content')
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    .health-header {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        border-radius: 12px;
        color: white;
        padding: 1.5rem;
        margin-bottom: 1.25rem;
    }
    .health-header h1 { font-size: 1.25rem; font-weight: 600; margin-bottom: 0.25rem; }
    .health-header p { opacity: 0.9; margin-bottom: 1rem; font-size: 0.875rem; }
    
    .metric-card {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e9ecef;
        height: 100%;
    }
    .metric-card h6 { 
        font-size: 0.75rem; 
        color: #6c757d; 
        text-transform: uppercase; 
        letter-spacing: 0.5px; 
        margin-bottom: 0.75rem;
        font-weight: 600;
    }
    .metric-value {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .metric-label {
        font-size: 0.75rem;
        color: #6c757d;
    }
    .metric-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        margin-bottom: 0.75rem;
    }
    
    .progress-bar-wrapper {
        background: #f0f0f0;
        border-radius: 10px;
        height: 8px;
        overflow: hidden;
        margin: 0.5rem 0;
    }
    .progress-bar-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 0.3s ease;
    }
    
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    .status-healthy { background: #d1fae5; color: #065f46; }
    .status-warning { background: #fed7aa; color: #92400e; }
    .status-error { background: #fecaca; color: #991b1b; }
    
    .content-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e9ecef;
        margin-bottom: 1rem;
    }
    .content-card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .content-card-header h6 { font-weight: 600; margin: 0; font-size: 0.9rem; }
    .content-card-body { padding: 1rem 1.25rem; }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f5f5f5;
        font-size: 0.85rem;
    }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #6c757d; }
    .info-value { font-weight: 600; }
    
    .error-item {
        padding: 0.75rem;
        background: #fef2f2;
        border-left: 3px solid #dc2626;
        border-radius: 6px;
        margin-bottom: 0.5rem;
    }
    .error-item:last-child { margin-bottom: 0; }
    .error-time { font-size: 0.7rem; color: #6c757d; }
    .error-message { font-size: 0.8rem; margin: 0.25rem 0 0 0; }
    
    .empty-state {
        text-align: center;
        padding: 2rem 1rem;
        color: #6c757d;
    }
    .empty-state i { font-size: 2rem; margin-bottom: 0.75rem; opacity: 0.4; }
</style>
@endpush

<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="health-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1><i class="fas fa-heart-pulse me-2"></i>System Health Monitor</h1>
                <p class="mb-2">Real-time monitoring of your application's health and performance</p>
                <div class="d-flex gap-2 flex-wrap">
                    <form action="{{ route('admin.system-health.clear-cache') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-light btn-sm">
                            <i class="fas fa-broom me-1"></i>Clear Cache
                        </button>
                    </form>
                    <form action="{{ route('admin.system-health.optimize-database') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-database me-1"></i>Optimize DB
                        </button>
                    </form>
                </div>
            </div>
            <div class="col-md-4 text-end d-none d-md-block">
                <i class="fas fa-heartbeat" style="font-size: 4rem; opacity: 0.2;"></i>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Server Metrics --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3 col-6">
            <div class="metric-card">
                <div class="metric-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-microchip"></i>
                </div>
                <div class="metric-value text-primary">{{ $serverMetrics['cpu_load'] }}</div>
                <div class="metric-label">CPU Load Avg</div>
                <div class="progress-bar-wrapper">
                    <div class="progress-bar-fill bg-primary" style="width: {{ min($serverMetrics['cpu_load'] * 100, 100) }}%"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="metric-card">
                <div class="metric-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-memory"></i>
                </div>
                <div class="metric-value text-success">{{ $serverMetrics['memory_percent'] }}%</div>
                <div class="metric-label">Memory Usage</div>
                <div class="progress-bar-wrapper">
                    <div class="progress-bar-fill {{ $serverMetrics['memory_percent'] > 80 ? 'bg-danger' : 'bg-success' }}" 
                         style="width: {{ $serverMetrics['memory_percent'] }}%"></div>
                </div>
                <small class="text-muted">{{ $serverMetrics['memory_used'] }} / {{ $serverMetrics['memory_total'] }}</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="metric-card">
                <div class="metric-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-hdd"></i>
                </div>
                <div class="metric-value text-warning">{{ $serverMetrics['disk_percent'] }}%</div>
                <div class="metric-label">Disk Usage</div>
                <div class="progress-bar-wrapper">
                    <div class="progress-bar-fill {{ $serverMetrics['disk_percent'] > 80 ? 'bg-danger' : 'bg-warning' }}" 
                         style="width: {{ $serverMetrics['disk_percent'] }}%"></div>
                </div>
                <small class="text-muted">{{ $serverMetrics['disk_free'] }} free</small>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="metric-card">
                <div class="metric-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-database"></i>
                </div>
                <div class="metric-value text-info">{{ $databaseMetrics['size'] }}</div>
                <div class="metric-label">Database Size</div>
                <span class="status-badge status-{{ $databaseMetrics['status'] === 'healthy' ? 'healthy' : 'error' }}">
                    {{ ucfirst($databaseMetrics['status']) }}
                </span>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Left Column --}}
        <div class="col-lg-8">
            {{-- Database Details --}}
            <div class="content-card">
                <div class="content-card-header">
                    <h6><i class="fas fa-database text-info me-2"></i>Database Health</h6>
                </div>
                <div class="content-card-body">
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="status-badge status-{{ $databaseMetrics['status'] === 'healthy' ? 'healthy' : 'error' }}">
                                <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                {{ ucfirst($databaseMetrics['status']) }}
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Total Size</span>
                        <span class="info-value">{{ $databaseMetrics['size'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Table Count</span>
                        <span class="info-value">{{ $databaseMetrics['table_count'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Active Connections</span>
                        <span class="info-value">{{ $databaseMetrics['connections'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Storage Usage --}}
            <div class="content-card">
                <div class="content-card-header">
                    <h6><i class="fas fa-folder text-warning me-2"></i>Storage Breakdown</h6>
                </div>
                <div class="content-card-body">
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-save me-2"></i>Backups</span>
                        <span class="info-value">{{ \App\Services\BackupService::formatBytes($storageMetrics['backups']) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-file-alt me-2"></i>Logs</span>
                        <span class="info-value">{{ \App\Services\BackupService::formatBytes($storageMetrics['logs']) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-upload me-2"></i>Uploads</span>
                        <span class="info-value">{{ \App\Services\BackupService::formatBytes($storageMetrics['uploads']) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-folder-open me-2"></i>Total Storage</span>
                        <span class="info-value">{{ \App\Services\BackupService::formatBytes($storageMetrics['total']) }}</span>
                    </div>
                </div>
            </div>

            {{-- Queue Status --}}
            <div class="content-card">
                <div class="content-card-header">
                    <h6><i class="fas fa-tasks text-primary me-2"></i>Queue Status</h6>
                </div>
                <div class="content-card-body">
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="status-badge status-{{ $queueMetrics['status'] }}">
                                <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                {{ ucfirst($queueMetrics['status']) }}
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Pending Jobs</span>
                        <span class="info-value">{{ $queueMetrics['pending'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Failed Jobs</span>
                        <span class="info-value text-{{ $queueMetrics['failed'] > 0 ? 'danger' : 'success' }}">
                            {{ $queueMetrics['failed'] }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="col-lg-4">
            {{-- Application Stats --}}
            <div class="content-card">
                <div class="content-card-header">
                    <h6><i class="fas fa-chart-line text-success me-2"></i>Application Stats</h6>
                </div>
                <div class="content-card-body">
                    <div class="info-row">
                        <span class="info-label">Active Users</span>
                        <span class="info-value">{{ $applicationMetrics['active_users'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Total Users</span>
                        <span class="info-value">{{ $applicationMetrics['total_users'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Today's Activities</span>
                        <span class="info-value">{{ $applicationMetrics['today_activities'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Active Sessions</span>
                        <span class="info-value">{{ $applicationMetrics['active_sessions'] }}</span>
                    </div>
                </div>
            </div>

            {{-- System Info --}}
            <div class="content-card">
                <div class="content-card-header">
                    <h6><i class="fas fa-info-circle text-secondary me-2"></i>System Info</h6>
                </div>
                <div class="content-card-body">
                    <div class="info-row">
                        <span class="info-label">PHP Version</span>
                        <span class="info-value">{{ $serverMetrics['php_version'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Laravel Version</span>
                        <span class="info-value">{{ $serverMetrics['laravel_version'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Environment</span>
                        <span class="info-value">{{ config('app.env') }}</span>
                    </div>
                </div>
            </div>

            {{-- Recent Errors --}}
            <div class="content-card">
                <div class="content-card-header">
                    <h6><i class="fas fa-exclamation-triangle text-danger me-2"></i>Recent Errors</h6>
                </div>
                <div class="content-card-body">
                    @forelse($recentErrors as $error)
                        <div class="error-item">
                            <div class="error-time">{{ $error['timestamp'] }}</div>
                            <div class="error-message text-danger">
                                <strong>{{ $error['level'] }}:</strong> {{ $error['message'] }}
                            </div>
                        </div>
                    @empty
                        <div class="empty-state py-3">
                            <i class="fas fa-check-circle text-success"></i>
                            <p class="mb-0 small">No recent errors</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
