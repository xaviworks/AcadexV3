@extends('layouts.guest')

@section('contents')
    <form method="POST" action="{{ route('password.store') }}" class="text-white">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="mb-4">
            <x-input-label for="email" :value="__('Email')" class="text-white" />
            <x-text-input 
                id="email" 
                class="block mt-1 w-full bg-transparent text-white placeholder-white border border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500" 
                type="email" 
                name="email" 
                :value="old('email', $request->email)" 
                required 
                autofocus 
                autocomplete="username" 
                placeholder="Enter your email"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
        </div>

        <!-- Password -->
        <div class="mb-4">
            <x-input-label for="password" :value="__('Password')" class="text-white" />
            <x-text-input 
                id="password" 
                class="block mt-1 w-full bg-transparent text-white placeholder-white border border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500" 
                type="password" 
                name="password" 
                required 
                autocomplete="new-password" 
                placeholder="New password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-white" />
            <x-text-input 
                id="password_confirmation" 
                class="block mt-1 w-full bg-transparent text-white placeholder-white border border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500"
                type="password" 
                name="password_confirmation" 
                required 
                autocomplete="new-password" 
                placeholder="Confirm password"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-400" />
        </div>

        <div class="flex items-center justify-end">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white border border-green-700 rounded-md font-semibold text-xs uppercase tracking-widest shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Reset Password') }}
            </button>
        </div>
    </form>
@endsection
