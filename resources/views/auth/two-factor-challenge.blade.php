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
            <div class="bg-white p-4 rounded-lg mb-3">
                <img 
                    src="{!! (new PragmaRX\Google2FAQRCode\Google2FA())->getQRCodeInline(
                        config('app.name'),
                        $user->email,
                        $user->two_factor_secret
                    ) !!}" 
                    alt="QR Code" 
                    class="w-48 h-48">
            </div>
            <p class="text-xs text-gray-300 text-center max-w-sm">
                {{ __('Scan this QR code with your authenticator app, then enter the code below.') }}
            </p>
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.login.store') }}" class="w-full max-w-sm mx-auto text-white" id="2fa-form">
        @csrf
        <input type="hidden" name="device_fingerprint" id="device_fingerprint">

        <div class="mt-4">
            <x-input-label for="code" :value="__('Code')" class="text-white" />

            <x-text-input id="code" class="block mt-1 w-full bg-transparent text-white border-gray-300 focus:border-green-500 focus:ring-green-500"
                            type="text"
                            inputmode="numeric"
                            name="code"
                            autofocus
                            autocomplete="one-time-code" />

            <x-input-error :messages="$errors->get('code')" class="mt-2 text-red-400" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <button type="button" 
                    onclick="document.getElementById('cancel-form').submit()"
                    class="text-sm text-gray-300 hover:text-white underline decoration-solid">
                {{ __('Cancel') }}
            </button>

            <x-primary-button class="ms-3">
                {{ __('Login') }}
            </x-primary-button>
        </div>
    </form>

    <form id="cancel-form" method="POST" action="{{ route('two-factor.login.cancel') }}" class="hidden">
        @csrf
    </form>

    <script>
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
