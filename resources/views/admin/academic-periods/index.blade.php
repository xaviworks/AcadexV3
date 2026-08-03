@extends('layouts.app')

@section('content')
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.notify?.success(@json(session('success')));
        });
    </script>
@endif

@if(session('warning'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.notify?.warning(@json(session('warning')));
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.notify?.error(@json(session('error')));
        });
    </script>
@endif

<div class="container py-4">
    <x-admin.page-header
        title="Academic Periods"
        subtitle="Manage school years and semester records"
        icon="bi bi-calendar-event-fill"
    >
        <button class="btn btn-success" onclick="showGenerateModal()">+ Generate New</button>
    </x-admin.page-header>

    {{-- Periods Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-success">
                    <tr>
                        <th>Academic Year</th>
                        <th>Semester</th>
                        <th class="text-center">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periods as $index => $period)
                        <tr>
                            <td>{{ $period->academic_year }}</td>
                            <td>{{ ucfirst($period->semester) }}</td>
                            <td class="text-center">{{ $period->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted fst-italic py-3">No academic periods found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<x-modal.confirmation
    id="confirmModal"
    title="Confirm Action"
    variant="success"
>
    <p class="mb-0">Are you sure you want to generate a new academic period based on the latest one?</p>

    <x-slot:footer>
        <form method="POST" action="{{ route('admin.academicPeriods.generate') }}">
            @csrf
            <x-modal.actions primary-text="Yes, Generate" primary-variant="success" />
        </form>
    </x-slot:footer>
</x-modal.confirmation>

{{-- JS --}}
<script>
    function showGenerateModal() {
        const modalEl = document.getElementById('confirmModal');
        if (modalEl) {
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
        }
    }
    window.showGenerateModal = showGenerateModal;
</script>
@endsection
