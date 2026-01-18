@extends('layouts.guest')

@section('contents')
    <div class="mb-4 text-sm text-white">
        {{ __('For security, please enter your two-factor authentication code to proceed with password reset.') }}
    </div>

    <form method="POST" action="{{ route('password.2fa.verify') }}" class="text-white">
        @csrf

        <!-- 2FA Code -->
        <div class="mb-4">
            <x-input-label for="code" :value="__('Authentication Code')" class="text-white" />
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-white">
                    <i class="fas fa-shield-alt"></i>
                </span>
                <x-text-input 
                    id="code" 
                    class="pl-10 mt-1 w-full bg-transparent text-white placeholder-white border border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500"
                    type="text" 
                    name="code" 
                    required 
                    autofocus 
                    autocomplete="one-time-code"
                    placeholder="000000"
                    maxlength="6"
                    pattern="[0-9]{6}"
                />
            </div>
            <p class="text-xs text-gray-300 mt-1">
                {{ __('Enter the 6-digit code from your authenticator app') }}
            </p>
            <x-input-error :messages="$errors->get('code')" class="mt-2 text-red-400" />
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('password.request') }}" class="inline-flex items-center text-sm text-green-300 hover:text-green-200 transition-colors duration-150">
                <i class="fas fa-arrow-left"></i>{{ __('Back') }}
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white border border-green-700 rounded-md font-semibold text-xs uppercase tracking-widest shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Verify & Send Reset Link') }}
            </button>
        </div>
    </form>
@endsection
