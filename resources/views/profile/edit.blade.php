@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('Profile Settings') }}
    </h2>
@endsection

@section('content')
<style>
    .page-wrapper {
        background-color: #EAF8E7;
        min-height: 100vh;
        padding: 0;
        margin: 0;
    }

    .page-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1.5rem 1rem;
    }

    .page-title {
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
    }

    .content-wrapper {
        background-color: white;
        border-radius: 0.75rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        padding: 1.5rem;
        margin-top: 1.5rem;
        font-size: 1rem;
    }

    .content-wrapper label {
        font-size: 1.1rem;
        font-weight: 500;
    }

    .content-wrapper input,
    .content-wrapper select {
        font-size: 1rem;
    }
</style>

<div class="page-wrapper">
    <div class="page-container">
        <!-- Page Title -->
        <h1 class="text-3xl font-bold mb-2 text-gray-800 d-flex align-items-center">
            <i class="bi bi-person-circle text-success me-3 fs-2"></i>
            Profile Account Management
        </h1>
        <p class="text-muted mb-4" style="font-size: 1rem;">Manage your profile information and password securely</p>

        <!-- Profile Information Section -->
        <div class="content-wrapper">
            <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="hidden">
                @csrf
            </form>

            <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
                @csrf
                @method('patch')
                @include('profile.partials.update-profile-information-form')
                <div class="flex justify-end mt-6">
                    <button type="submit" class="btn btn-success px-4 py-2">
                        <i class="bi bi-check-circle me-2"></i>Update Profile
                    </button>
                </div>
            </form>
        </div>

        <!-- Password Update Section -->
        <div class="content-wrapper">
            <header class="mb-4 bg-transparent">
                <h2 class="h4 fw-semibold text-dark mb-2">
                    {{ __('Update Password') }}
                </h2>
                <p class="text-muted" style="font-size: 1rem;">
                    {{ __('Ensure your account is using a long, random password to stay secure.') }}
                </p>
            </header>

            <form method="post" action="{{ route('profile.password.update') }}" class="space-y-6">
                @csrf
                @method('put')
                <div class="space-y-4">
                    <div>
                        <x-input-label for="update_password_current_password" :value="__('Current Password')" />
                        <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                        <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="update_password_password" :value="__('New Password')" />
                        <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                        <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
                        <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                    </div>
                </div>
                <div class="flex justify-end mt-6">
                    <button type="submit" class="btn btn-success px-4 py-2">
                        <i class="bi bi-shield-lock me-2"></i>Update Password
                    </button>
                </div>
            </form>
        </div>

        <!-- Two Factor Authentication -->
        <div class="content-wrapper">
            @include('profile.partials.two-factor-authentication-form')
        </div>
    </div>
</div>

@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof notify !== 'undefined') {
                notify.success(@json(session('success')));
            }
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof notify !== 'undefined') {
                notify.error(@json(session('error')));
            }
        });
    </script>
@endif
@endsection
