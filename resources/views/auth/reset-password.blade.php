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
            <div class="relative">
                <!-- Password input (hidden) -->
                <input
                    id="password"
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm pr-12 mt-1 w-full border border-gray-300 shadow-sm bg-transparent text-white placeholder-white focus:ring-green-500 focus:border-green-500"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    placeholder="New password"
                />
                
                <!-- Text input (visible) - hidden by default -->
                <input
                    id="password-visible"
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm pr-12 mt-1 w-full border border-gray-300 shadow-sm bg-transparent text-white placeholder-white focus:ring-green-500 focus:border-green-500"
                    type="text"
                    style="display: none; position: absolute; top: 0; left: 0; width: 100%;"
                    placeholder="New password"
                    tabindex="-1"
                />
                
                <button
                    type="button"
                    id="togglePassword"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 mt-1 text-white/80 hover:text-white focus:outline-none transition-colors duration-200 z-10 hidden"
                >
                    <i class="fas fa-eye-slash" id="toggleIcon"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-white" />
            <div class="relative">
                <!-- Password confirmation input (hidden) -->
                <input
                    id="password_confirmation"
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm pr-12 mt-1 w-full border border-gray-300 shadow-sm bg-transparent text-white placeholder-white focus:ring-green-500 focus:border-green-500"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Confirm password"
                />
                
                <!-- Text input (visible) - hidden by default -->
                <input
                    id="password_confirmation-visible"
                    class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm pr-12 mt-1 w-full border border-gray-300 shadow-sm bg-transparent text-white placeholder-white focus:ring-green-500 focus:border-green-500"
                    type="text"
                    style="display: none; position: absolute; top: 0; left: 0; width: 100%;"
                    placeholder="Confirm password"
                    tabindex="-1"
                />
                
                <button
                    type="button"
                    id="togglePasswordConfirmation"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 mt-1 text-white/80 hover:text-white focus:outline-none transition-colors duration-200 z-10 hidden"
                >
                    <i class="fas fa-eye-slash" id="toggleIconConfirmation"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-400" />
        </div>

        <div class="flex items-center justify-end">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white border border-green-700 rounded-md font-semibold text-xs uppercase tracking-widest shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Reset Password') }}
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Password visibility toggle
            const toggleBtn = document.getElementById('togglePassword');
            const toggleIcon = document.getElementById('toggleIcon');
            const passwordInput = document.getElementById('password');
            const passwordVisible = document.getElementById('password-visible');
            
            if (toggleBtn && toggleIcon && passwordInput && passwordVisible) {
                let isVisible = false;
                
                // Show/hide toggle button based on input
                const updateToggleVisibility = () => {
                    if (passwordInput.value.length > 0 || passwordVisible.value.length > 0) {
                        toggleBtn.classList.remove('hidden');
                    } else {
                        toggleBtn.classList.add('hidden');
                        if (isVisible) {
                            isVisible = false;
                            passwordVisible.style.display = 'none';
                            passwordInput.style.display = 'block';
                            toggleIcon.classList.remove('fa-eye');
                            toggleIcon.classList.add('fa-eye-slash');
                        }
                    }
                };
                
                passwordInput.addEventListener('input', function() {
                    passwordVisible.value = this.value;
                    updateToggleVisibility();
                });
                
                passwordVisible.addEventListener('input', function() {
                    passwordInput.value = this.value;
                    updateToggleVisibility();
                });
                
                toggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    isVisible = !isVisible;
                    
                    if (isVisible) {
                        passwordInput.style.display = 'none';
                        passwordVisible.style.display = 'block';
                        passwordVisible.style.position = 'relative';
                        passwordVisible.value = passwordInput.value;
                        passwordVisible.focus();
                        toggleIcon.classList.remove('fa-eye-slash');
                        toggleIcon.classList.add('fa-eye');
                    } else {
                        passwordVisible.style.display = 'none';
                        passwordInput.style.display = 'block';
                        passwordInput.value = passwordVisible.value;
                        passwordInput.focus();
                        toggleIcon.classList.remove('fa-eye');
                        toggleIcon.classList.add('fa-eye-slash');
                    }
                });
            }

            // Password confirmation visibility toggle
            const toggleBtnConfirm = document.getElementById('togglePasswordConfirmation');
            const toggleIconConfirm = document.getElementById('toggleIconConfirmation');
            const passwordConfirmInput = document.getElementById('password_confirmation');
            const passwordConfirmVisible = document.getElementById('password_confirmation-visible');
            
            if (toggleBtnConfirm && toggleIconConfirm && passwordConfirmInput && passwordConfirmVisible) {
                let isVisibleConfirm = false;
                
                const updateToggleVisibilityConfirm = () => {
                    if (passwordConfirmInput.value.length > 0 || passwordConfirmVisible.value.length > 0) {
                        toggleBtnConfirm.classList.remove('hidden');
                    } else {
                        toggleBtnConfirm.classList.add('hidden');
                        if (isVisibleConfirm) {
                            isVisibleConfirm = false;
                            passwordConfirmVisible.style.display = 'none';
                            passwordConfirmInput.style.display = 'block';
                            toggleIconConfirm.classList.remove('fa-eye');
                            toggleIconConfirm.classList.add('fa-eye-slash');
                        }
                    }
                };
                
                passwordConfirmInput.addEventListener('input', function() {
                    passwordConfirmVisible.value = this.value;
                    updateToggleVisibilityConfirm();
                });
                
                passwordConfirmVisible.addEventListener('input', function() {
                    passwordConfirmInput.value = this.value;
                    updateToggleVisibilityConfirm();
                });
                
                toggleBtnConfirm.addEventListener('click', function(e) {
                    e.preventDefault();
                    isVisibleConfirm = !isVisibleConfirm;
                    
                    if (isVisibleConfirm) {
                        passwordConfirmInput.style.display = 'none';
                        passwordConfirmVisible.style.display = 'block';
                        passwordConfirmVisible.style.position = 'relative';
                        passwordConfirmVisible.value = passwordConfirmInput.value;
                        passwordConfirmVisible.focus();
                        toggleIconConfirm.classList.remove('fa-eye-slash');
                        toggleIconConfirm.classList.add('fa-eye');
                    } else {
                        passwordConfirmVisible.style.display = 'none';
                        passwordConfirmInput.style.display = 'block';
                        passwordConfirmInput.value = passwordConfirmVisible.value;
                        passwordConfirmInput.focus();
                        toggleIconConfirm.classList.remove('fa-eye');
                        toggleIconConfirm.classList.add('fa-eye-slash');
                    }
                });
            }
        });
    </script>
@endsection
