<section>
    <header class="mb-4 bg-transparent">
        <h2 class="h4 fw-semibold text-dark mb-2">
            {{ __('Two Factor Authentication') }}
        </h2>

        <p class="text-muted" style="font-size: 1rem;">
            {{ __('Add additional security to your account using two factor authentication.') }}
        </p>
    </header>

    <div class="mt-4">
        @if(! auth()->user()->two_factor_secret)
            {{-- Enable 2FA --}}
            <x-primary-button
                x-data=""
                x-on:click.prevent="$dispatch('open-modal', 'confirm-2fa-enable')"
            >{{ __('Enable') }}</x-primary-button>
        @else
            {{-- 2FA is Enabled --}}
            <div class="flex flex-col gap-4">
                <div class="p-4 bg-white border border-gray-200 rounded-lg">
                    @if(! auth()->user()->two_factor_confirmed_at)
                        <p class="font-semibold text-gray-900 mb-4">
                            {{ __('Two factor authentication is enabled.') }}
                        </p>
                        <p class="text-sm text-gray-600 mb-4">
                            {{ __('Scan the QR code below with your authenticator app (Google Authenticator, Authy, etc.). On your next login, you will be asked to enter a code from your authenticator app to complete the setup.') }}
                        </p>
                    @else
                        <p class="font-semibold text-green-600 mb-4">
                            {{ __('Two factor authentication is confirmed and active.') }}
                        </p>
                        <p class="text-sm text-gray-600 mb-4">
                            {{ __('Your account is protected with two factor authentication. You can view your QR code below if you need to set up a new device.') }}
                        </p>
                    @endif
                    
                    <div class="mb-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">{{ __('QR Code') }}</p>
                        <div class="relative inline-block" id="qr-container">
                            <!-- Single QR Code with blur toggle -->
                            <img 
                                id="qr-image" 
                                src="{!! (new PragmaRX\Google2FAQRCode\Google2FA())->getQRCodeInline(
                                    config('app.name'),
                                    auth()->user()->email,
                                    auth()->user()->two_factor_secret
                                ) !!}" 
                                alt="QR Code" 
                                class="border border-gray-300 rounded p-2 transition-all duration-300" 
                                style="filter: blur(10px);">
                            
                            <!-- Overlay with reveal button -->
                            <div id="qr-overlay" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30 rounded">
                                <button 
                                    type="button"
                                    x-data=""
                                    x-on:click.prevent="$dispatch('open-modal', 'reveal-qr-code')"
                                    class="px-4 py-2 bg-white text-gray-700 rounded-md shadow-md hover:bg-gray-100 transition-colors flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    {{ __('Click to Reveal QR Code') }}
                                </button>
                            </div>
                            
                            <!-- Hide button (shown after reveal) -->
                            <button 
                                id="hide-qr-btn"
                                type="button"
                                onclick="hideQRCode()"
                                class="hidden mt-2 text-sm text-gray-600 hover:text-gray-800 underline">
                                {{ __('Hide QR Code') }}
                            </button>
                        </div>
                    </div>
                </div>
                
                @if(auth()->user()->role === 3)
                    {{-- Recovery Codes Section (Admin Only - shown when 2FA is enabled) --}}
                    <div class="p-5 bg-gradient-to-br from-amber-50 to-orange-50 border-2 border-orange-200 rounded-lg mt-4 shadow-sm">
                        <div class="flex items-start gap-3 mb-4">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="w-7 h-7 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h3 class="text-lg font-bold text-gray-900">{{ __('Recovery Codes') }}</h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 border border-orange-300">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
                                        </svg>
                                        Admin Only
                                    </span>
                                </div>
                                <p class="text-sm text-gray-700 leading-relaxed">
                                    {{ __('Recovery codes are your backup access method. If you lose your authenticator device, these codes will allow you to regain access to your account. These codes are available immediately after enabling 2FA.') }}
                                </p>
                            </div>
                        </div>
                        
                        {{-- Important Notice --}}
                        <div class="bg-white border-l-4 border-red-500 p-4 mb-4 rounded-r-md">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-red-800 mb-1">{{ __('Important Security Notice') }}</h4>
                                    <ul class="text-xs text-red-700 space-y-1 list-disc list-inside">
                                        <li>{{ __('Store these codes in a secure location (password manager, safe, etc.)') }}</li>
                                        <li>{{ __('Each code can only be used once') }}</li>
                                        <li>{{ __('Regenerating codes will invalidate all previous codes') }}</li>
                                        <li>{{ __('Never share these codes with anyone') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Recovery Codes Display Area --}}
                        <div id="recovery-codes-container" class="hidden mb-4">
                            <div class="bg-white border-2 border-dashed border-gray-300 rounded-lg p-5 mb-3">
                                <div class="flex items-center justify-between mb-3">
                                    <p class="text-sm font-semibold text-gray-700">
                                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M7 9a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2V9z"></path>
                                            <path d="M5 3a2 2 0 00-2 2v6a2 2 0 002 2V5h8a2 2 0 00-2-2H5z"></path>
                                        </svg>
                                        {{ __('Your 8 Recovery Codes:') }}
                                    </p>
                                    <button 
                                        type="button"
                                        onclick="copyRecoveryCodes()"
                                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ __('Copy All') }}
                                    </button>
                                </div>
                                <div class="grid grid-cols-2 gap-3" id="recovery-codes-list">
                                    {{-- Codes will be populated here --}}
                                </div>
                                <p class="text-xs text-gray-500 mt-3 italic">
                                    <svg class="w-3 h-3 inline" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ __('Tip: Print or save these codes securely. You\'ll need them if you lose access to your authenticator app.') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <button 
                                id="toggle-recovery-codes-btn"
                                type="button"
                                onclick="toggleRecoveryCodes()"
                                class="inline-flex items-center px-5 py-2.5 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white shadow-md hover:bg-blue-700 hover:shadow-lg active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-150">
                                <svg id="show-icon" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg id="hide-icon" class="hidden w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                                <span id="toggle-btn-text">{{ __('Show Recovery Codes') }}</span>
                            </button>
                            
                            <button 
                                type="button"
                                x-data=""
                                x-on:click.prevent="$dispatch('open-modal', 'regenerate-recovery-codes')"
                                class="inline-flex items-center px-5 py-2.5 bg-orange-600 border border-transparent rounded-lg font-semibold text-sm text-white shadow-md hover:bg-orange-700 hover:shadow-lg active:bg-orange-800 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-all duration-150"
                                title="{{ __('Generate new codes and invalidate old ones') }}">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                {{ __('Regenerate Codes') }}
                            </button>
                        </div>
                        
                        <div class="mt-4 flex items-start gap-2 bg-blue-50 border border-blue-200 rounded-md p-3">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="text-xs text-blue-800">
                                <p class="font-semibold mb-1">{{ __('How to use recovery codes:') }}</p>
                                <ol class="list-decimal list-inside space-y-0.5 ml-1">
                                    <li>{{ __('Click "Show Recovery Codes" and enter your password') }}</li>
                                    <li>{{ __('Save the codes in a secure location (password manager recommended)') }}</li>
                                    <li>{{ __('Use a code instead of your authenticator app code if you lose access') }}</li>
                                    <li>{{ __('Each code works only once - regenerate after using several codes') }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                @endif
                
                <div class="mt-2">
                    <x-danger-button
                        x-data=""
                        x-on:click.prevent="$dispatch('open-modal', 'confirm-2fa-disable')"
                    >{{ __('Disable') }}</x-danger-button>
                </div>

                {{-- Reveal QR Code Modal --}}
                <x-modal name="reveal-qr-code" maxWidth="md" focusable>
                    <div class="p-6 bg-white rounded-lg">
                        <div class="mb-4">
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Confirm Your Password') }}
                            </h2>
                        </div>

                        <div class="mb-6">
                            <p class="text-sm text-gray-600">
                                {{ __('Please enter your password to view the QR code for security purposes.') }}
                            </p>
                        </div>

                        <div>
                            <div class="mb-6">
                                <label for="reveal_password" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Password') }}
                                </label>

                                <input
                                    id="reveal_password"
                                    name="password"
                                    type="password"
                                    class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    placeholder="{{ __('Enter your password') }}"
                                    required
                                    autofocus
                                />

                                <div id="reveal-error" class="text-sm text-red-600 mt-2 hidden"></div>
                            </div>

                            <div class="flex items-center justify-end gap-3">
                                <button type="button" x-on:click="show = false" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                    {{ __('Cancel') }}
                                </button>

                                <button 
                                    id="reveal-submit-btn"
                                    type="button"
                                    onclick="handleRevealQR()"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span id="reveal-btn-text">{{ __('Reveal QR Code') }}</span>
                                    <svg id="reveal-spinner" class="hidden animate-spin ml-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </x-modal>

                {{-- Disable Modal --}}
                <x-modal name="confirm-2fa-disable" maxWidth="md" :show="$errors->userDeletion->isNotEmpty()" focusable>
                    <div class="p-6 bg-white rounded-lg">
                        <div class="mb-4">
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Disable Two Factor Authentication?') }}
                            </h2>
                        </div>

                        <div class="mb-6">
                            <p class="text-sm text-gray-600">
                                {{ __('This will remove the extra layer of security from your account. Please enter your password to confirm.') }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('two-factor.disable') }}">
                            @csrf
                            @method('DELETE')

                            <div class="mb-6">
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('Password') }}
                                </label>

                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    placeholder="{{ __('Enter your password') }}"
                                    required
                                    autofocus
                                />

                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end gap-3">
                                <button type="button" x-on:click="show = false" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                    {{ __('Cancel') }}
                                </button>

                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Disable 2FA') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </x-modal>
            </div>
        @endif
    </div>

    {{-- Enable 2FA Confirmation Modal (outside conditional so always available) --}}
    <x-modal name="confirm-2fa-enable" maxWidth="md" focusable>
        <div class="p-6 bg-white rounded-lg">
            <div class="mb-4">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ __('Enable Two Factor Authentication?') }}
                </h2>
            </div>

            <div class="mb-6">
                <p class="text-sm text-gray-600">
                    {{ __('Two factor authentication adds an additional layer of security to your account by requiring a code from your authenticator app in addition to your password when signing in.') }}
                </p>
                <p class="text-sm text-gray-600 mt-2">
                    {{ __('Once enabled, you will need to scan a QR code with your authenticator app (such as Google Authenticator or Authy).') }}
                </p>
            </div>

            <form method="POST" action="{{ route('two-factor.enable') }}">
                @csrf

                <div class="flex items-center justify-end gap-3">
                    <button type="button" x-on:click="show = false" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        {{ __('Cancel') }}
                    </button>

                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Enable 2FA') }}
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Show Recovery Codes Modal (Admin Only) --}}
    <x-modal name="show-recovery-codes" maxWidth="md" focusable>
        <div class="p-6 bg-white rounded-lg">
            <div class="mb-4">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ __('Recovery Codes') }}
                </h2>
            </div>

            <div class="mb-6">
                <p class="text-sm text-gray-600">
                    {{ __('Please enter your password to view your recovery codes.') }}
                </p>
            </div>

            <div>
                <div class="mb-6">
                    <label for="show_recovery_password" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('Password') }}
                    </label>

                    <input
                        id="show_recovery_password"
                        type="password"
                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        placeholder="{{ __('Enter your password') }}"
                        required
                        autofocus
                    />

                    <div id="show-recovery-error" class="text-sm text-red-600 mt-2 hidden"></div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" x-on:click="show = false" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        {{ __('Cancel') }}
                    </button>

                    <button 
                        id="show-recovery-submit-btn"
                        type="button"
                        onclick="handleShowRecoveryCodes()"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="show-recovery-btn-text">{{ __('Show Codes') }}</span>
                        <svg id="show-recovery-spinner" class="hidden animate-spin ml-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </x-modal>

    {{-- Regenerate Recovery Codes Modal (Admin Only) --}}
    <x-modal name="regenerate-recovery-codes" maxWidth="md" focusable>
        <div class="p-6 bg-white rounded-lg">
            <div class="mb-4">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ __('Regenerate Recovery Codes') }}
                </h2>
            </div>

            <div class="mb-6">
                <p class="text-sm text-gray-600">
                    {{ __('This will invalidate all your current recovery codes. Please enter your password to confirm.') }}
                </p>
            </div>

            <div>
                <div class="mb-6">
                    <label for="regenerate_password" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('Password') }}
                    </label>

                    <input
                        id="regenerate_password"
                        type="password"
                        class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        placeholder="{{ __('Enter your password') }}"
                        required
                        autofocus
                    />

                    <div id="regenerate-error" class="text-sm text-red-600 mt-2 hidden"></div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" x-on:click="show = false" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                        {{ __('Cancel') }}
                    </button>

                    <button 
                        id="regenerate-submit-btn"
                        type="button"
                        onclick="handleRegenerateRecoveryCodes()"
                        class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-500 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="regenerate-btn-text">{{ __('Regenerate Codes') }}</span>
                        <svg id="regenerate-spinner" class="hidden animate-spin ml-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </x-modal>

    <script>
        function handleRevealQR() {
            const password = document.getElementById('reveal_password').value;
            const errorDiv = document.getElementById('reveal-error');
            const submitBtn = document.getElementById('reveal-submit-btn');
            const btnText = document.getElementById('reveal-btn-text');
            const spinner = document.getElementById('reveal-spinner');
            
            // Validate password field
            if (!password) {
                errorDiv.textContent = 'Please enter your password';
                errorDiv.classList.remove('hidden');
                return;
            }
            
            // Show loading state
            submitBtn.disabled = true;
            btnText.textContent = 'Verifying...';
            spinner.classList.remove('hidden');
            errorDiv.classList.add('hidden');
            
            // Send AJAX request to verify password
            fetch('{{ route('two-factor.reveal-qr') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ password: password })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove blur from QR code
                    const qrImage = document.getElementById('qr-image');
                    qrImage.style.filter = 'blur(0px)';
                    
                    // Hide overlay, show hide button
                    document.getElementById('qr-overlay').classList.add('hidden');
                    document.getElementById('hide-qr-btn').classList.remove('hidden');
                    
                    // Close modal
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'reveal-qr-code' }));
                    
                    // Clear password field
                    document.getElementById('reveal_password').value = '';
                    errorDiv.classList.add('hidden');
                    
                    // Show success notification
                    if (typeof notify !== 'undefined') {
                        notify.success('QR code revealed successfully. Keep it secure!');
                    }
                } else {
                    errorDiv.textContent = data.message || 'Invalid password';
                    errorDiv.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorDiv.textContent = 'An error occurred. Please try again.';
                errorDiv.classList.remove('hidden');
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                btnText.textContent = 'Reveal QR Code';
                spinner.classList.add('hidden');
            });
        }
        
        function hideQRCode() {
            const qrImage = document.getElementById('qr-image');
            qrImage.style.filter = 'blur(10px)';
            document.getElementById('qr-overlay').classList.remove('hidden');
            document.getElementById('hide-qr-btn').classList.add('hidden');
        }
        
        // Toggle Recovery Codes Display
        function toggleRecoveryCodes() {
            const container = document.getElementById('recovery-codes-container');
            const btn = document.getElementById('toggle-recovery-codes-btn');
            const btnText = document.getElementById('toggle-btn-text');
            const showIcon = document.getElementById('show-icon');
            const hideIcon = document.getElementById('hide-icon');
            
            if (container.classList.contains('hidden')) {
                // Show codes - open password modal
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'show-recovery-codes' }));
            } else {
                // Hide codes
                container.classList.add('hidden');
                btnText.textContent = '{{ __('Show Recovery Codes') }}';
                showIcon.classList.remove('hidden');
                hideIcon.classList.add('hidden');
                btn.classList.remove('bg-gray-600', 'hover:bg-gray-700', 'active:bg-gray-800');
                btn.classList.add('bg-blue-600', 'hover:bg-blue-700', 'active:bg-blue-800');
            }
        }
        
        // Show Recovery Codes Function (Admin Only)
        function handleShowRecoveryCodes() {
            const password = document.getElementById('show_recovery_password').value;
            const errorDiv = document.getElementById('show-recovery-error');
            const submitBtn = document.getElementById('show-recovery-submit-btn');
            const btnText = document.getElementById('show-recovery-btn-text');
            const spinner = document.getElementById('show-recovery-spinner');
            
            if (!password) {
                errorDiv.textContent = 'Please enter your password';
                errorDiv.classList.remove('hidden');
                return;
            }
            
            submitBtn.disabled = true;
            btnText.textContent = 'Loading...';
            spinner.classList.remove('hidden');
            errorDiv.classList.add('hidden');
            
            fetch('{{ route('two-factor.recovery-codes') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ password: password })
            })
            .then(response => {
                // Check HTTP status first
                if (!response.ok) {
                    throw new Error('Invalid password');
                }
                return response.json();
            })
            .then(data => {
                // Only proceed if we have recovery codes AND no error
                if (data.recovery_codes && data.recovery_codes.length > 0) {
                    displayRecoveryCodes(data.recovery_codes);
                    
                    // Update toggle button to "Hide" state
                    const btn = document.getElementById('toggle-recovery-codes-btn');
                    const btnText = document.getElementById('toggle-btn-text');
                    const showIcon = document.getElementById('show-icon');
                    const hideIcon = document.getElementById('hide-icon');
                    
                    btnText.textContent = '{{ __('Hide Recovery Codes') }}';
                    showIcon.classList.add('hidden');
                    hideIcon.classList.remove('hidden');
                    btn.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'active:bg-blue-800');
                    btn.classList.add('bg-gray-600', 'hover:bg-gray-700', 'active:bg-gray-800');
                    
                    // Clear password field
                    document.getElementById('show_recovery_password').value = '';
                    
                    // Close modal
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'show-recovery-codes' }));
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Recovery Codes Retrieved',
                        text: 'Please save these codes in a secure location.',
                        confirmButtonColor: '#3085d6'
                    });
                } else {
                    // No codes returned or error in response
                    errorDiv.textContent = data.message || 'Failed to retrieve recovery codes';
                    errorDiv.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Recovery codes error:', error);
                errorDiv.textContent = 'Invalid password. Please try again.';
                errorDiv.classList.remove('hidden');
            })
            .finally(() => {
                submitBtn.disabled = false;
                btnText.textContent = 'Show Codes';
                spinner.classList.add('hidden');
            });
        }
        
        // Regenerate Recovery Codes Function (Admin Only)
        function handleRegenerateRecoveryCodes() {
            const password = document.getElementById('regenerate_password').value;
            const errorDiv = document.getElementById('regenerate-error');
            const submitBtn = document.getElementById('regenerate-submit-btn');
            const btnText = document.getElementById('regenerate-btn-text');
            const spinner = document.getElementById('regenerate-spinner');
            
            if (!password) {
                errorDiv.textContent = 'Please enter your password';
                errorDiv.classList.remove('hidden');
                return;
            }
            
            submitBtn.disabled = true;
            btnText.textContent = 'Regenerating...';
            spinner.classList.remove('hidden');
            errorDiv.classList.add('hidden');
            
            fetch('{{ route('two-factor.recovery-codes.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ password: password })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Invalid password');
                }
                return response.json();
            })
            .then(data => {
                if (data.recovery_codes && data.recovery_codes.length > 0) {
                    // Display the new codes
                    displayRecoveryCodes(data.recovery_codes);
                    
                    // Update toggle button to "Hide" state
                    const btn = document.getElementById('toggle-recovery-codes-btn');
                    const btnText = document.getElementById('toggle-btn-text');
                    const showIcon = document.getElementById('show-icon');
                    const hideIcon = document.getElementById('hide-icon');
                    
                    btnText.textContent = '{{ __('Hide Recovery Codes') }}';
                    showIcon.classList.add('hidden');
                    hideIcon.classList.remove('hidden');
                    btn.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'active:bg-blue-800');
                    btn.classList.add('bg-gray-600', 'hover:bg-gray-700', 'active:bg-gray-800');
                    
                    // Clear password field
                    document.getElementById('regenerate_password').value = '';
                    
                    // Close modal
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'regenerate-recovery-codes' }));
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'New Recovery Codes Generated!',
                        html: '<p class="text-sm text-gray-600 mt-2">Your old codes have been invalidated.</p><p class="text-sm font-semibold text-orange-600 mt-2">⚠️ Please save these new codes immediately!</p>',
                        confirmButtonColor: '#ea580c',
                        confirmButtonText: 'Got it!'
                    });
                } else {
                    errorDiv.textContent = data.message || 'Failed to regenerate codes';
                    errorDiv.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Regenerate error:', error);
                errorDiv.textContent = 'Invalid password. Please try again.';
                errorDiv.classList.remove('hidden');
            })
            .finally(() => {
                submitBtn.disabled = false;
                btnText.textContent = 'Regenerate Codes';
                spinner.classList.add('hidden');
            });
        }
        
        function displayRecoveryCodes(codes) {
            const container = document.getElementById('recovery-codes-container');
            const codesList = document.getElementById('recovery-codes-list');
            
            codesList.innerHTML = '';
            codes.forEach((code, index) => {
                const codeDiv = document.createElement('div');
                codeDiv.className = 'relative group';
                codeDiv.innerHTML = `
                    <div class="flex items-center justify-between bg-gradient-to-r from-gray-50 to-gray-100 p-3 rounded-lg border border-gray-300 hover:border-blue-400 hover:shadow-md transition-all duration-200">
                        <span class="text-xs font-medium text-gray-400 mr-2">${index + 1}.</span>
                        <span class="font-mono text-base font-bold text-gray-800 tracking-wider flex-1">${code}</span>
                        <svg class="w-4 h-4 text-green-600 opacity-0 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                `;
                codesList.appendChild(codeDiv);
            });
            
            container.classList.remove('hidden');
            
            // Smooth scroll to codes
            setTimeout(() => {
                container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 100);
        }
        
        function copyRecoveryCodes() {
            const codes = Array.from(document.querySelectorAll('#recovery-codes-list div'))
                .map(div => {
                    const codeText = div.querySelector('.font-mono');
                    return codeText ? codeText.textContent.trim() : '';
                })
                .filter(code => code !== '')
                .join('\n');
            
            navigator.clipboard.writeText(codes).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Copied to Clipboard!',
                    html: `
                        <p class="text-sm text-gray-600 mt-2">All <strong>${codes.split('\n').length} recovery codes</strong> have been copied.</p>
                        <p class="text-xs text-gray-500 mt-2">💡 Paste them into a secure password manager or save to a file in a safe location.</p>
                    `,
                    timer: 3500,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'animated fadeIn'
                    }
                });
            }).catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Copy Failed',
                    text: 'Please select and copy the codes manually.',
                    confirmButtonColor: '#3085d6'
                });
            });
        }
    </script>
</section>
