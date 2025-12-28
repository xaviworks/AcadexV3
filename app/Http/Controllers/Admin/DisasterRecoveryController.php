<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Backup;
use App\Models\Setting;
use App\Services\BackupService;
use App\Services\RestoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DisasterRecoveryController extends Controller
{
    public function __construct(
        protected BackupService $backupService,
        protected RestoreService $restoreService
    ) {
        $this->middleware('auth');
    }

    /**
     * Main dashboard - simple and clean.
     */
    public function index()
    {
        Gate::authorize('admin');

        $backups = Backup::completed()
            ->latest()
            ->take(10)
            ->get();

        $latestBackup = $backups->first();
        $storageInfo = $this->backupService->getStorageInfo();
        
        // Recent activity
        $recentActivity = AuditLog::with('user')
            ->latest()
            ->take(5)
            ->get();

        // Stats
        $stats = [
            'total_backups' => Backup::completed()->count(),
            'storage_used' => $storageInfo['total_size_formatted'] ?? '0 MB',
            'storage_bytes' => $storageInfo['total_size'] ?? 0,
            'last_backup' => $latestBackup?->created_at?->diffForHumans() ?? 'Never',
            'changes_today' => AuditLog::whereDate('created_at', today())->count(),
        ];

        $schedule = json_decode(Setting::where('key', 'backup_schedule')->value('value') ?? '{}', true);

        return view('admin.disaster-recovery.index', compact(
            'backups',
            'latestBackup',
            'recentActivity',
            'stats',
            'storageInfo',
            'schedule'
        ));
    }

    /**
     * Create backup (one-click).
     */
    public function createBackup(Request $request)
    {
        Gate::authorize('admin');

        $request->validate([
            'password' => 'required|string',
            'type' => 'nullable|in:full,config',
            'notes' => 'nullable|string|max:255',
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->with('error', 'Incorrect password. Please try again.');
        }

        try {
            $type = $request->input('type', 'full');
            $user = Auth::user();

            $backup = match ($type) {
                'config' => $this->backupService->createConfigBackup($user, $request->notes),
                default => $this->backupService->createFullBackup($user, $request->notes),
            };

            return back()->with('success', "Backup created successfully! ({$backup->size_formatted})");

        } catch (\Throwable $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Download backup file.
     */
    public function download(Backup $backup): StreamedResponse
    {
        Gate::authorize('admin');

        if (!$backup->fileExists()) {
            abort(404, 'Backup file not found');
        }

        return response()->streamDownload(function () use ($backup) {
            readfile($backup->getFullPath());
        }, $backup->filename, [
            'Content-Type' => 'application/zip',
        ]);
    }

    /**
     * Delete a backup.
     */
    public function delete(Request $request, Backup $backup)
    {
        Gate::authorize('admin');

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->with('error', 'Incorrect password. Please try again.');
        }

        $this->backupService->deleteBackup($backup);

        return back()->with('success', 'Backup deleted successfully.');
    }

    /**
     * Restore from backup.
     */
    public function restore(Request $request, Backup $backup)
    {
        Gate::authorize('admin');

        $request->validate([
            'password' => 'required|string',
            'confirm_restore' => 'required|accepted',
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->with('error', 'Incorrect password. Please try again.');
        }

        try {
            // Create safety backup if requested
            if ($request->boolean('create_safety_backup')) {
                $this->backupService->createFullBackup(
                    Auth::user(), 
                    'Safety backup before restoring ' . $backup->created_at->format('Y-m-d H:i')
                );
            }

            $results = $this->restoreService->restoreFromBackup($backup, [
                'clear_existing' => true,
            ]);

            $tablesRestored = count($results['tables_restored'] ?? []);
            $totalRows = array_sum($results['tables_restored'] ?? []);

            return back()->with('success', "Restore completed! {$tablesRestored} tables, {$totalRows} records restored.");

        } catch (\Throwable $e) {
            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    /**
     * Preview backup contents (AJAX).
     */
    public function preview(Backup $backup)
    {
        Gate::authorize('admin');

        try {
            $preview = $this->backupService->previewBackup($backup);
            return response()->json(['success' => true, 'preview' => $preview]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Activity log page.
     */
    public function activity(Request $request)
    {
        Gate::authorize('admin');

        $query = AuditLog::with('user')->latest();

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('auditable_type', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(25);

        return view('admin.disaster-recovery.activity', compact('logs'));
    }

    /**
     * View single audit log.
     */
    public function showActivity(AuditLog $auditLog)
    {
        Gate::authorize('admin');
        $auditLog->load('user');
        return view('admin.disaster-recovery.activity-show', compact('auditLog'));
    }

    /**
     * Rollback a change.
     */
    public function rollback(Request $request, AuditLog $auditLog)
    {
        Gate::authorize('admin');

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->with('error', 'Incorrect password. Please try again.');
        }

        if (!$auditLog->old_values) {
            return back()->with('error', 'Cannot rollback: no previous data available.');
        }

        try {
            $this->restoreService->rollbackToAuditLog($auditLog);
            return back()->with('success', 'Change rolled back successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Rollback failed: ' . $e->getMessage());
        }
    }



    /**
     * Update backup schedule.
     */
    public function schedule(Request $request)
    {
        Gate::authorize('admin');

        $request->validate([
            'frequency' => 'required|in:daily,weekly,monthly,never',
            'time' => 'required_unless:frequency,never',
        ]);

        Setting::updateOrCreate(
            ['key' => 'backup_schedule'],
            ['value' => json_encode([
                'frequency' => $request->frequency,
                'time' => $request->time,
            ])]
        );

        return back()->with('success', 'Backup schedule updated successfully.');
    }

    /**
     * Run manual backup now (for testing schedule).
     */
    public function runNow()
    {
        Gate::authorize('admin');

        try {
            $backup = $this->backupService->createFullBackup(
                Auth::user(),
                'Manual trigger - testing automatic backup'
            );

            return back()->with('success', "Manual backup completed! ({$backup->size_formatted})");

        } catch (\Throwable $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }
}
