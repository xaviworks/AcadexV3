<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ACADEX') }}</title>

    <!-- Preload Background Image -->
    <link rel="preload" as="image" href="{{ asset('images/bg.jpg') }}">
    <!-- Set the CSS variable early so the preloaded image is used across the page -->
    <style>
        :root {
            --guest-background: url('{{ asset('images/bg.jpg') }}');
        }
    </style>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700&display=swap" rel="stylesheet">

    <!-- CDNFonts: Feeling Passionate -->
    <link href="https://fonts.cdnfonts.com/css/feeling-passionate" rel="stylesheet">

    <!-- Tailwind & App Assets -->
    @vite(['resources/css/app.css', 'resources/css/guest-entry.css', 'resources/js/app.js'])

    {{-- Styles: resources/css/layout/guest.css (loaded via guest-entry.css) --}}
</head>
    <body class="text-white" style="--guest-background: url('{{ asset('images/bg.jpg') }}'); background-image: var(--guest-background); background-size: cover; background-attachment: fixed; background-position: center center;">

    <!-- Branding Section -->
    <div class="branding-container">
        <img src="{{ asset('logo.jpg') }}" alt="ACADEX Logo">
        <div class="branding-text">
            <h1>ACADEX</h1>
            <p>Fides et Servitium</p>
        </div>
    </div>

    <!-- Login Card -->
    <div class="overlay">
        <div class="container login-container">
            <div class="col-md-4 col-lg-4 glass-card text-white p-4">
                <!-- Dynamic Content -->
                @yield('contents')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Alpine.js for interactivity (optional) -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('passwordToggle', () => ({
                showPassword: false,
                togglePassword() {
                    this.showPassword = !this.showPassword;
                }
            }));
        });
    </script>

</body>
</html>
