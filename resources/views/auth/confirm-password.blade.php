@extends('layouts.guest')

@section('contents')
    <div class="mb-4 text-sm text-white">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="text-white">
        @csrf

        <!-- Password -->
        <div class="mb-4">
            <x-input-label for="password" :value="__('Password')" class="text-white" />

            <x-text-input 
                id="password" 
                class="block mt-1 w-full bg-transparent text-white placeholder-white border border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500" 
                type="password"
                name="password"
                required 
                autocomplete="current-password" 
                placeholder="Enter your password"
            />

            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
        </div>

        <div class="flex justify-end">
            <button type="submit" style="background-color: #198754; border-color: #198754;" class="inline-flex items-center px-4 py-2 text-white rounded-md font-semibold text-xs uppercase tracking-widest shadow-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Confirm') }}
            </button>
        </div>
    </form>
@endsection
