<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mobile Access Blocked - {{ config('app.name', 'ACADEX') }}</title>

    <!-- Preload Background Image -->
    <link rel="preload" as="image" href="{{ asset('images/bg.jpg') }}">
    
    <!-- Bootstrap CSS (Local) -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    
    <!-- Bootstrap Icons (Local) -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}">

    <!-- Local Fonts (Poppins, Inter, Feeling Passionate, Instrument Sans) -->
    <link rel="stylesheet" href="{{ asset('css/local-fonts.css') }}">

    <style>
        :root {
            --guest-background: url('{{ asset('images/bg.jpg') }}');
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-image: var(--guest-background);
            background-size: cover;
            background-attachment: fixed;
            background-position: center center;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 0;
        }

        .branding-container {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            animation: fadeInDown 0.6s ease-out;
        }

        .branding-container img {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .branding-text h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 48px;
            font-weight: 700;
            color: white;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
            margin: 0;
            line-height: 1;
        }

        .branding-text p {
            font-family: 'Feeling Passionate', cursive;
            font-size: 18px;
            color: rgba(255, 255, 255, 0.95);
            margin: 5px 0 0 0;
            font-style: italic;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.5);
        }

        .glass-card {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            color: white;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            font-size: 28px;
            color: #ffffff;
            margin-bottom: 22px;
            margin-top: 0;
            font-weight: 700;
            text-align: center;
            text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.5);
        }

        .message {
            font-size: 16px;
            color: #ffffff;
            line-height: 1.8;
            margin-bottom: 25px;
            text-align: center;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.4);
        }

        .requirements {
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 22px;
            margin: 25px 0;
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .requirements h3 {
            font-size: 14px;
            color: #ffffff;
            font-weight: 700;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            text-align: center;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.4);
        }

        .requirements ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .requirements li {
            padding: 12px 0;
            color: #ffffff;
            font-size: 15px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        .requirements li:last-child {
            border-bottom: none;
        }

        .requirements li i {
            color: #a7f3d0;
            margin-right: 14px;
            font-size: 18px;
            flex-shrink: 0;
            filter: drop-shadow(1px 1px 2px rgba(0, 0, 0, 0.4));
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            text-align: center;
        }

        .footer-text {
            font-size: 14px;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4);
            line-height: 1.4;
            flex-wrap: wrap;
        }

        .footer-text i {
            font-size: 15px;
            flex-shrink: 0;
        }

        .alert-box {
            background: rgba(239, 68, 68, 0.25);
            border: 2px solid rgba(239, 68, 68, 0.5);
            border-radius: 10px;
            padding: 16px;
            margin-top: 22px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .alert-box strong {
            color: #fecaca;
            font-size: 15px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
        }

        @media (max-width: 768px) {
            .branding-container {
                gap: 15px;
                margin-bottom: 25px;
            }

            .branding-container img {
                width: 65px;
                height: 65px;
            }

            .branding-text h1 {
                font-size: 38px;
            }

            .branding-text p {
                font-size: 15px;
            }

            .glass-card {
                padding: 35px 25px;
            }
            
            h1 {
                font-size: 25px;
                margin-bottom: 18px;
            }
            
            .message {
                font-size: 15px;
            }

            .requirements h3 {
                font-size: 13px;
            }

            .requirements li {
                font-size: 14px;
                padding: 10px 0;
            }

            .footer-text {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <!-- Branding Section -->
    <div class="branding-container">
        <img src="{{ asset('logo.jpg') }}" alt="ACADEX Logo">
        <div class="branding-text">
            <h1>ACADEX</h1>
            <p>Fides et Servitium</p>
        </div>
    </div>

    <!-- Glass Card -->
    <div class="glass-card">
        <h1>Mobile Device Access Blocked</h1>
        
        <p class="message">
            This application is designed exclusively for desktop computers and laptops. All mobile device access (phones and tablets) is blocked to ensure optimal functionality and user experience.
        </p>

        <div class="requirements">
            <h3><i class="bi bi-check-circle-fill"></i> Supported Devices Only</h3>
            <ul>
                <li><i class="bi bi-pc-display-horizontal"></i> Desktop Computers (Windows, Mac, Linux)</li>
                <li><i class="bi bi-laptop"></i> Laptop Computers</li>
            </ul>
        </div>

        <div class="alert-box">
            <strong><i class="bi bi-exclamation-triangle-fill"></i> Please access this system from a desktop or laptop computer only.</strong>
        </div>

        <div class="footer">
            <div class="footer-text">
                <i class="bi bi-info-circle"></i><span>Need help? Contact your system administrator or IT support.</span>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (Local) -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
