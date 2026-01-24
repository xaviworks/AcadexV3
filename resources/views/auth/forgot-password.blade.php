@extends('layouts.guest')

@section('contents')
    <div class="mb-4 text-sm text-white">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4 text-white" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="text-white" id="forgot-password-form">
        @csrf
        <input type="hidden" name="email" id="email-full">

        <!-- Email Username -->
        <div class="mb-4">
            <x-input-label for="email" :value="__('Email Username')" class="text-white" />
            <div class="relative flex rounded-md shadow-sm">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-white">
                    <i class="fas fa-user"></i>
                </span>
                <x-text-input 
                    id="email" 
                    class="pl-10 mt-1 w-full rounded-r-none border border-gray-300 shadow-sm bg-transparent text-white placeholder-white focus:ring-green-500 focus:border-green-500"
                    type="text" 
                    name="email_username" 
                    :value="old('email') ? str_replace('@brokenshire.edu.ph', '', old('email')) : ''" 
                    required 
                    autofocus 
                    placeholder="Enter your username" 
                    pattern="^[^@]+$" 
                    title="Do not include '@' or domain — just the username."
                />
                <span class="inline-flex items-center px-3 rounded-r-md bg-white/20 border border-l-0 border-gray-300 mt-1 text-sm text-white">
                    @brokenshire.edu.ph
                </span>
            </div>

            <!-- Live warning -->
            <p id="email-warning" class="text-sm text-red-400 mt-1 hidden">
                Please enter only your username — do not include '@' or email domain.
            </p>

            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('login') }}" class="inline-flex items-center text-sm text-green-300 hover:text-green-200 transition-colors duration-150">
                <i class="fas fa-arrow-left"></i>{{ __('Back to Login') }}
            </a>
            <button type="submit" style="background-color: #198754; border-color: #198754;" class="inline-flex items-center px-4 py-2 text-white rounded-md font-semibold text-xs uppercase tracking-widest shadow-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Send Reset Link') }}
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const emailField = document.getElementById('email');
            const emailFullField = document.getElementById('email-full');
            const warning = document.getElementById('email-warning');
            const form = document.getElementById('forgot-password-form');

            // Show warning if @ is included
            emailField.addEventListener('input', () => {
                if (emailField.value.includes('@')) {
                    warning.classList.remove('hidden');
                    // Auto-remove the @ and anything after it
                    emailField.value = emailField.value.split('@')[0];
                } else {
                    warning.classList.add('hidden');
                }
            });

            // Set the hidden field value with full email on form submit
            form.addEventListener('submit', function(e) {
                const username = emailField.value.trim();
                
                if (username) {
                    // Set the full email in the hidden field
                    emailFullField.value = username + '@brokenshire.edu.ph';
                }
                // The visible field remains unchanged (username only)
            });
        });
    </script>
@endsection
