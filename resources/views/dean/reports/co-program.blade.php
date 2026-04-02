@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-5">
    @include('chairperson.partials.toast-notifications')

    @include('chairperson.partials.reports-header', [
        'title' => 'Program Outcomes Summary',
        'subtitle' => 'Track Program Learning Outcome attainment for ' . ($program->course_code ?? 'selected program'),
        'icon' => 'bi-diagram-3',
        'academicYear' => $academicYear,
        'semester' => $semester
    ])

    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Program Outcomes Reports']
    ]" />

    <div class="card border-0 shadow-sm rounded-4 mt-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div>
                    <div class="text-uppercase text-muted small fw-semibold mb-1">Assigned Program</div>
                    <div class="fw-semibold text-dark">{{ $program->course_code ?? 'N/A' }}</div>
                    <div class="text-muted small">{{ $program->course_description ?? 'No program description available.' }}</div>
                </div>

                <div class="badge text-bg-light px-3 py-2 rounded-pill">
                    {{ collect($activePloDefinitions ?? [])->count() }} active PLO{{ collect($activePloDefinitions ?? [])->count() === 1 ? '' : 's' }}
                </div>
            </div>

            @if (collect($activePloDefinitions ?? [])->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-diagram-3 text-muted fs-1 d-block mb-3"></i>
                    <h5 class="fw-semibold">No active PLOs configured yet</h5>
                    <p class="text-muted mb-0">This program has no active Program Learning Outcomes to display for the selected period.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start align-middle" style="min-width: 280px;">
                                    <i class="bi bi-mortarboard text-primary me-2"></i>Program
                                </th>
                                @foreach($activePloDefinitions as $plo)
                                    <th class="text-center align-middle" style="min-width: 170px;" title="{{ $plo->title }}">
                                        <div class="fw-semibold">{{ $plo->plo_code }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($byProgram as $programId => $row)
                                <tr>
                                    <td class="text-start">
                                        <div class="fw-semibold">{{ $row['program']->course_code ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $row['program']->course_description ?? '' }}</small>
                                    </td>
                                    @foreach($activePloDefinitions as $plo)
                                        @php($value = $row['plos'][$plo->id] ?? null)
                                        <td class="text-center">
                                            @if($value)
                                                @php($threshold = (float) ($value['target_percentage'] ?? 0))
                                                @php($level = $value['level'] ?? ['label' => '', 'tone' => 'success'])
                                                @php($toneClass = match($level['tone'] ?? 'success') {
                                                    'danger' => 'bg-danger-subtle text-danger-emphasis',
                                                    'warning' => 'bg-warning-subtle text-warning-emphasis',
                                                    default => 'bg-success-subtle text-success-emphasis',
                                                })
                                                @php($levelBannerClass = match($level['tone'] ?? 'success') {
                                                    'danger' => 'plo-level-banner-danger',
                                                    'warning' => 'plo-level-banner-warning',
                                                    default => 'plo-level-banner-success',
                                                })
                                                <span class="badge {{ $toneClass }} px-3 py-2 rounded-pill">
                                                    {{ number_format((float) $value['percent'], 2) }}%
                                                </span>
                                                <div class="mt-2 plo-result-meta">
                                                    <div class="plo-result-chips">
                                                        @foreach($value['co_codes'] as $coCode)
                                                            <span class="plo-result-chip">{{ $coCode }}</span>
                                                        @endforeach
                                                    </div>
                                                    <div class="plo-target-text">Target {{ number_format($threshold, 2) }}%</div>
                                                    @if(!empty($level['label']))
                                                        <div class="plo-level-banner {{ $levelBannerClass }}">{{ $level['label'] }}</div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted fs-5">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ collect($activePloDefinitions)->count() + 1 }}" class="text-center py-5">
                                        <i class="bi bi-inbox text-muted fs-1 d-block mb-2"></i>
                                        <p class="text-muted mb-0">No assessed program outcomes found for this academic period.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

