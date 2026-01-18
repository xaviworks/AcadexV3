@extends('layouts.guest')

@section('contents')
    <div class="mb-4 text-sm text-white">
        @if(!$user->two_factor_confirmed_at)
            {{ __('First time setup: Please scan the QR code below with your authenticator app (Google Authenticator, Authy, etc.), then enter the 6-digit code.') }}
        @else
            {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
        @endif
    </div>

    @if(!$user->two_factor_confirmed_at)
        <div class="mb-6 flex flex-col items-center">
            <div class="bg-white p-4 rounded-lg mb-3 flex items-center justify-center">
                {!! \App\Support\QRCodeHelper::generate(
                    config('app.name'),
                    $user->email,
                    $user->two_factor_secret,
                    200
                ) !!}
            </div>
            <p class="text-xs text-gray-300 text-center max-w-sm">
                {{ __('Scan this QR code with your authenticator app, then enter the code below.') }}
            </p>
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.login.store') }}" class="w-full max-w-sm mx-auto text-white" id="2fa-form">
        @csrf
        <input type="hidden" name="device_fingerprint" id="device_fingerprint">
        <input type="hidden" name="recovery" id="recovery" value="0">

        <div class="mt-4">
            <x-input-label for="code" :value="__('Code')" class="text-white" />

            <x-text-input id="code" class="block mt-1 w-full bg-transparent text-white border-gray-300 focus:border-green-500 focus:ring-green-500"
                            type="text"
                            inputmode="numeric"
                            name="code"
                            autofocus
                            autocomplete="one-time-code"
                            placeholder="{{ __('Enter 6-digit code') }}" />

            <x-input-error :messages="$errors->get('code')" class="mt-2 text-red-400" />
        </div>

        {{-- Recovery Code Option (Admin Only) or Help Text (Other Users) --}}
        <div class="mt-4 text-center">
            @if($user->role === 3)
                {{-- Admin: Show recovery code toggle --}}
                <button type="button" 
                        id="toggle-recovery-btn"
                        onclick="toggleRecoveryMode()"
                        class="text-sm text-blue-300 hover:text-blue-100 underline decoration-dotted inline-flex items-center gap-1 transition-colors">
                    <svg id="recovery-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span id="recovery-text">{{ __('Use a recovery code instead') }}</span>
                </button>
                
                {{-- Recovery Code Help Text for Admin --}}
                <div id="recovery-help" class="hidden mt-3 bg-blue-900/30 border border-blue-400/50 rounded-lg p-3 text-left">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-blue-300 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="text-xs text-blue-200">
                            <p class="font-semibold mb-1">{{ __('Lost your authenticator device?') }}</p>
                            <p>{{ __('Enter one of your recovery codes (format: xxxxx-xxxxx). Each code can only be used once.') }}</p>
                            <p class="mt-1 text-blue-300">{{ __('You can view your codes in Profile → Two Factor Authentication.') }}</p>
                        </div>
                    </div>
                </div>
            @else
                {{-- Non-Admin: Show contact admin message --}}
                <div class="mt-3 bg-amber-900/30 border border-amber-400/50 rounded-lg p-3 text-left">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-amber-300 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="text-xs text-amber-200">
                            <p class="font-semibold mb-1">{{ __('Lost access to your authenticator?') }}</p>
                            <p>{{ __('Please contact your system administrator to reset your two-factor authentication. They can help you regain access to your account.') }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="flex items-center justify-between mt-6">
            <button type="button" 
                    onclick="document.getElementById('cancel-form').submit()"
                    class="text-sm text-gray-300 hover:text-white underline decoration-solid">
                {{ __('Cancel') }}
            </button>

            <button type="submit" style="background-color: #198754; border-color: #198754;" class="inline-flex items-center px-4 py-2 text-white rounded-md font-semibold text-xs uppercase tracking-widest shadow-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 ms-3">
                {{ __('Login') }}
            </button>
        </div>
    </form>

    <form id="cancel-form" method="POST" action="{{ route('two-factor.login.cancel') }}" class="hidden">
        @csrf
    </form>

    <script>
        // Toggle between authenticator code and recovery code mode
        function toggleRecoveryMode() {
            const codeInput = document.getElementById('code');
            const codeLabel = document.querySelector('label[for="code"]');
            const recoveryInput = document.getElementById('recovery');
            const recoveryBtn = document.getElementById('toggle-recovery-btn');
            const recoveryText = document.getElementById('recovery-text');
            const recoveryIcon = document.getElementById('recovery-icon');
            const recoveryHelp = document.getElementById('recovery-help');
            
            if (recoveryInput.value === '0') {
                // Switch to recovery code mode
                recoveryInput.value = '1';
                codeInput.placeholder = '{{ __("xxxxx-xxxxx") }}';
                codeInput.inputMode = 'text';
                codeLabel.textContent = '{{ __("Recovery Code") }}';
                recoveryText.textContent = '{{ __("Use authenticator code instead") }}';
                recoveryIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                `;
                recoveryHelp.classList.remove('hidden');
                codeInput.value = '';
                codeInput.focus();
            } else {
                // Switch back to authenticator code mode
                recoveryInput.value = '0';
                codeInput.placeholder = '{{ __("Enter 6-digit code") }}';
                codeInput.inputMode = 'numeric';
                codeLabel.textContent = '{{ __("Code") }}';
                recoveryText.textContent = '{{ __("Use a recovery code instead") }}';
                recoveryIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                `;
                recoveryHelp.classList.add('hidden');
                codeInput.value = '';
                codeInput.focus();
            }
        }
        
        document.addEventListener('DOMContentLoaded', async function () {
            // Generate device fingerprint and hash it
            const generateFingerprint = async () => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                ctx.textBaseline = 'top';
                ctx.font = '14px Arial';
                ctx.fillText('browser fingerprint', 2, 2);
                
                const fingerprintData = JSON.stringify({
                    canvas: canvas.toDataURL(),
                    userAgent: navigator.userAgent,
                    language: navigator.language,
                    platform: navigator.platform,
                    screen: `${screen.width}x${screen.height}x${screen.colorDepth}`,
                    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                    plugins: Array.from(navigator.plugins || []).map(p => p.name).join(','),
                });

                // Hash the fingerprint using SHA-256
                const encoder = new TextEncoder();
                const data = encoder.encode(fingerprintData);
                const hashBuffer = await crypto.subtle.digest('SHA-256', data);
                const hashArray = Array.from(new Uint8Array(hashBuffer));
                const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
                
                return hashHex;
            };

            // Set the hashed fingerprint
            const hash = await generateFingerprint();
            document.getElementById('device_fingerprint').value = hash;
        });
    </script>
@endsection
