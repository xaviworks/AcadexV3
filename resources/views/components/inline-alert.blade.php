{{--
    Inline Alert Message Component
    ==============================
    A lightweight, user-friendly inline message for simple status/info notices.
    Use this for short contextual messages (e.g., "No pending applications").
    For larger empty states with icons and CTAs, use <x-empty-state> instead.

    Usage:
        <x-inline-alert message="No pending instructor applications." />
        <x-inline-alert type="info" message="Select a subject to continue." />
        <x-inline-alert type="warning" icon="bi-exclamation-triangle" message="Action required." />

    Props:
        type       (string)  'success' | 'info' | 'warning' | 'muted'  (default: 'muted')
        icon       (string)  Bootstrap icon class (default: auto-detected by type)
        message    (string)  The message text
--}}

@props([
    'type' => 'muted',
    'icon' => null,
    'message' => '',
])

@php
    $defaultIcons = [
        'success' => 'bi-check-circle',
        'info'    => 'bi-info-circle',
        'warning' => 'bi-exclamation-triangle',
        'muted'   => 'bi-info-circle',
    ];

    $resolvedIcon = $icon ?? ($defaultIcons[$type] ?? 'bi-info-circle');

    $colorMap = [
        'success' => 'text-success',
        'info'    => 'text-primary',
        'warning' => 'text-warning',
        'muted'   => 'text-muted',
    ];

    $bgMap = [
        'success' => 'rgba(25, 135, 84, 0.06)',
        'info'    => 'rgba(13, 110, 253, 0.06)',
        'warning' => 'rgba(255, 193, 7, 0.08)',
        'muted'   => 'rgba(108, 117, 125, 0.06)',
    ];

    $borderMap = [
        'success' => 'rgba(25, 135, 84, 0.2)',
        'info'    => 'rgba(13, 110, 253, 0.2)',
        'warning' => 'rgba(255, 193, 7, 0.3)',
        'muted'   => 'rgba(108, 117, 125, 0.15)',
    ];

    $textColor = $colorMap[$type] ?? 'text-muted';
    $bgColor   = $bgMap[$type] ?? $bgMap['muted'];
    $borderColor = $borderMap[$type] ?? $borderMap['muted'];
@endphp

<div class="d-flex align-items-center gap-3 px-4 py-3 rounded-4"
     style="background: {{ $bgColor }}; border: 1.5px solid {{ $borderColor }};">
    <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
         style="width: 42px; height: 42px; background: {{ $borderColor }};">
        <i class="bi {{ $resolvedIcon }} {{ $textColor }}" style="font-size: 1.25rem;"></i>
    </div>
    <span class="{{ $textColor }}" style="font-size: 1.05rem; font-weight: 500;">{!! $message !!}</span>
</div>
