@extends('layouts.guest')

@section('contents')
    <div class="mb-4 text-sm text-white">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4 text-white" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="text-white">
        @csrf

        <!-- Email Address -->
        <div class="mb-4">
            <x-input-label for="email" :value="__('Email')" class="text-white" />
            <x-text-input 
                id="email" 
                class="block mt-1 w-full bg-transparent text-white placeholder-white border border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500" 
                type="email" 
                name="email" 
                :value="old('email')" 
                required 
                autofocus 
                placeholder="Enter your email address"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
        </div>

        <div class="flex items-center justify-end">
            <x-primary-button class="bg-green-700 hover:bg-green-800 text-black dark:text-white">
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
@endsection
