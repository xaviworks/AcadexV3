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
    </script>
</section>
