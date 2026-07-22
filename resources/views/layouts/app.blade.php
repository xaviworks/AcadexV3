<!DOCTYPE html>
<!--
    
     █████╗  ██████╗ █████╗ ██████╗ ███████╗██╗  ██╗
    ██╔══██╗██╔════╝██╔══██╗██╔══██╗██╔════╝╚██╗██╔╝
    ███████║██║     ███████║██║  ██║█████╗   ╚███╔╝ 
    ██╔══██║██║     ██╔══██║██║  ██║██╔══╝   ██╔██╗ 
    ██║  ██║╚██████╗██║  ██║██████╔╝███████╗██╔╝ ██╗
    ╚═╝  ╚═╝ ╚═════╝╚═╝  ╚═╝╚═════╝ ╚══════╝╚═╝  ╚═╝
                                                      
    An Outcomes-Based Automated Grading System
    
-->
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="csrf-refresh-url" content="{{ route('csrf.refresh') }}">
    <meta name="color-scheme" content="light">

    <title>{{ config('app.name', 'Laravel') }}</title>
    @include('layouts.partials.favicon')
    
    <!-- DNS Prefetch & Preconnect for CDN resources -->
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    {{-- Styles: resources/css/layout/app.css --}}

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons via CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- DataTables CSS with Bootstrap 5 Integration -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('css/datatables-custom.css') }}">
    
    <!-- Google Fonts - Inter (with display=swap to prevent FOIT) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- App CSS & JS (with cache busting) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>

    <!-- Additional Page Styles -->
    @stack('styles')

    <!-- Preload critical resources -->
    <link rel="preload" as="image" href="{{ asset('logo.jpg') }}">
    <link rel="preload" as="script" href="{{ asset('js/page-transition.js') }}">
    
    <!-- Page transition handler (load early) -->
    <script src="{{ asset('js/page-transition.js') }}" defer></script>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="page-loader">
        <div class="loader-spinner"></div>
        <div class="loader-text">Loading...</div>
    </div>
    <!-- Sidebar -->
    <aside class="sidebar-wrapper">
        @include('layouts.sidebar')
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        @include('layouts.navigation')

        <!-- Page Content -->
        <main class="p-4">
            <div class="container-fluid px-4">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Scripts Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Alpine.js is loaded via Vite with stores configured in resources/js/app.js -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootbox.js (requires jQuery and Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/bootbox@6.0.0/dist/bootbox.all.min.js"></script>
    
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Remove loading class when page is ready -->
    <script>
        // Show content when page is fully loaded
        window.addEventListener('load', function() {
            // Small delay for smoother transition
            setTimeout(function() {
                document.body.classList.add('loaded');
            }, 150);
        });

        // Fallback if load event already fired
        if (document.readyState === 'complete') {
            setTimeout(function() {
                document.body.classList.add('loaded');
            }, 150);
        } else if (document.readyState === 'interactive') {
            // If DOM is ready but resources are still loading
            setTimeout(function() {
                document.body.classList.add('loaded');
            }, 200);
        }

        // Smooth page transitions - show loader only when navigation will actually continue.
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');

            if (!link ||
                !link.href ||
                link.href.indexOf(window.location.origin) !== 0 ||
                link.hasAttribute('target') ||
                link.hasAttribute('download') ||
                link.hasAttribute('data-no-page-loader') ||
                link.classList.contains('dropdown-toggle') ||
                link.getAttribute('href').startsWith('#') ||
                link.closest('.dropdown-menu')) {
                return;
            }

            // Let page-level handlers run first. If they cancel navigation
            // (for example, to show an unsaved-changes modal), keep the loader hidden.
            setTimeout(function() {
                if (e.defaultPrevented) {
                    document.body.classList.add('loaded');
                    return;
                }

                document.body.classList.remove('loaded');
            }, 0);
        });

        // Handle browser back/forward
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                setTimeout(function() {
                    document.body.classList.add('loaded');
                }, 100);
            }
        });

        // -----------------------------------------------------------------------
        // Session validity checking
        // The /session/check endpoint has no auth middleware so it always returns
        // a real 401 (instead of a 302→200 redirect) when the session is revoked.
        // -----------------------------------------------------------------------
        var SESSION_CHECK_URL  = '{{ route("session.check") }}';
        var LOGIN_URL          = '{{ route("login") }}';
        var SESSION_POLL_MS    = 30000; // check every 30 seconds

        function checkSessionValid() {
            fetch(SESSION_CHECK_URL, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(function(response) {
                // 401 = session revoked/expired, 419 = CSRF token mismatch
                // response.redirected means the auth middleware redirected us to
                // the login page (should not happen now, but kept as a safety net)
                if (response.status === 401 || response.status === 419 || response.redirected) {
                    clearInterval(window._sessionPollInterval);
                    window.location.href = LOGIN_URL;
                }
            })
            .catch(function() {
                // Network error – don't redirect, just wait for next poll
            });
        }

        window.addEventListener('auth:session-expired', function() {
            clearInterval(window._sessionPollInterval);
            window.location.href = LOGIN_URL;
        });

        // Periodic polling: kicks users out within SESSION_POLL_MS after revocation
        window._sessionPollInterval = setInterval(checkSessionValid, SESSION_POLL_MS);

        // Immediate check on tab focus restore (handles browser back button)
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                checkSessionValid();
            }
        });

        // Immediate check on bfcache page restore
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                checkSessionValid();
            }
        });
    </script>

    <!-- Course Outcome Submenu Handler -->
    <script>
        // Helper to close all submenus (optionally exclude one)
        function closeAllSubmenus(exceptId = null) {
            const map = {
                'courseOutcomeSubmenu': '.course-outcome-chevron',
                'studentsSubmenu': '.students-chevron',
                'gradesSubmenu': '.grades-chevron',
                'academicRecordsSubmenu': '.academic-records-chevron',
                'chairpersonReportsSubmenu': '.chairperson-reports-chevron',
                'manageCOSubmenu': '.manage-co-chevron'
            };
            Object.keys(map).forEach(id => {
                if (id === exceptId) return;
                const submenu = document.getElementById(id);
                const chevron = document.querySelector(map[id]);
                if (submenu && submenu.classList.contains('show')) {
                    submenu.classList.remove('show');
                }
                if (chevron && chevron.classList.contains('rotated')) {
                    chevron.classList.remove('rotated');
                }
            });
        }

        function toggleCourseOutcomeMenu() {
            const submenu = document.getElementById('courseOutcomeSubmenu');
            const chevron = document.querySelector('.course-outcome-chevron');
            
            if (submenu && chevron) {
                // Close other submenus
                closeAllSubmenus('courseOutcomeSubmenu');
                if (submenu.classList.contains('show')) {
                    submenu.classList.remove('show');
                    chevron.classList.remove('rotated');
                } else {
                    submenu.classList.add('show');
                    chevron.classList.add('rotated');
                }
            }
        }
        
        // Auto-expand if on Course Outcome pages
        document.addEventListener('DOMContentLoaded', function() {
            const isCourseOutcomePage = window.location.pathname.includes('/course_outcomes') || 
                                      window.location.pathname.includes('/course-outcome-attainments');
            
            if (isCourseOutcomePage) {
                const submenu = document.getElementById('courseOutcomeSubmenu');
                const chevron = document.querySelector('.course-outcome-chevron');
                
                if (submenu && chevron) {
                    closeAllSubmenus('courseOutcomeSubmenu');
                    submenu.classList.add('show');
                    chevron.classList.add('rotated');
                }
            }
        });
        
        // Students Submenu Handler
        function toggleStudentsMenu() {
            const submenu = document.getElementById('studentsSubmenu');
            const chevron = document.querySelector('.students-chevron');
            if (submenu && chevron) {
                // Close other submenus
                closeAllSubmenus('studentsSubmenu');
                if (submenu.classList.contains('show')) {
                    submenu.classList.remove('show');
                    chevron.classList.remove('rotated');
                } else {
                    submenu.classList.add('show');
                    chevron.classList.add('rotated');
                }
            }
        }
        
        // Manage Course Outcome Submenu Handler
        function toggleManageCOMenu() {
            const submenu = document.getElementById('manageCOSubmenu');
            const chevron = document.querySelector('.manage-co-chevron');
            if (submenu && chevron) {
                // Close other submenus
                closeAllSubmenus('manageCOSubmenu');
                if (submenu.classList.contains('show')) {
                    submenu.classList.remove('show');
                    chevron.classList.remove('rotated');
                } else {
                    submenu.classList.add('show');
                    chevron.classList.add('rotated');
                }
            }
        }
        
        // Manage Academic Records Submenu Handler
        function toggleAcademicRecordsMenu() {
            const submenu = document.getElementById('academicRecordsSubmenu');
            const chevron = document.querySelector('.academic-records-chevron');
            if (submenu && chevron) {
                // Close other submenus
                closeAllSubmenus('academicRecordsSubmenu');
                if (submenu.classList.contains('show')) {
                    submenu.classList.remove('show');
                    chevron.classList.remove('rotated');
                } else {
                    submenu.classList.add('show');
                    chevron.classList.add('rotated');
                }
            }
        }
        
        // Auto-expand if on Course Outcome pages (GE Coordinator)
        document.addEventListener('DOMContentLoaded', function() {
            const isCOPage = window.location.pathname.includes('/gecoordinator/reports/co-');
            if (isCOPage) {
                const submenu = document.getElementById('manageCOSubmenu');
                const chevron = document.querySelector('.manage-co-chevron');
                if (submenu && chevron) {
                    closeAllSubmenus('manageCOSubmenu');
                    submenu.classList.add('show');
                    chevron.classList.add('rotated');
                }
            }
        });
        
        // Auto-expand if on Students pages
        document.addEventListener('DOMContentLoaded', function() {
            const isStudentsPage = window.location.pathname.includes('/instructor/students');
            if (isStudentsPage) {
                const submenu = document.getElementById('studentsSubmenu');
                const chevron = document.querySelector('.students-chevron');
                if (submenu && chevron) {
                    closeAllSubmenus('studentsSubmenu');
                    submenu.classList.add('show');
                    chevron.classList.add('rotated');
                }
            }
        });
        
        // Chairperson Reports Submenu Handler
        function toggleChairpersonReportsMenu() {
            const submenu = document.getElementById('chairpersonReportsSubmenu');
            const chevron = document.querySelector('.chairperson-reports-chevron');
            if (submenu && chevron) {
                // Close other submenus
                closeAllSubmenus('chairpersonReportsSubmenu');
                if (submenu.classList.contains('show')) {
                    submenu.classList.remove('show');
                    chevron.classList.remove('rotated');
                } else {
                    submenu.classList.add('show');
                    chevron.classList.add('rotated');
                }
            }
        }
        // Auto-expand if on Chairperson Reports or Course Outcome pages
        document.addEventListener('DOMContentLoaded', function() {
            const isChairpersonReports = window.location.pathname.includes('/chairperson/reports') || window.location.pathname.includes('/chairperson/course_outcomes');
            if (isChairpersonReports) {
                const submenu = document.getElementById('chairpersonReportsSubmenu');
                const chevron = document.querySelector('.chairperson-reports-chevron');
                if (submenu && chevron) {
                    closeAllSubmenus('chairpersonReportsSubmenu');
                    submenu.classList.add('show');
                    chevron.classList.add('rotated');
                }
            }
        });
    </script>

    <!-- Grades Submenu Handler -->
    <script>
        function toggleGradesMenu() {
            const submenu = document.getElementById('gradesSubmenu');
            const chevron = document.querySelector('.grades-chevron');
            
            if (submenu && chevron) {
                // Close other submenus
                closeAllSubmenus('gradesSubmenu');
                if (submenu.classList.contains('show')) {
                    submenu.classList.remove('show');
                    chevron.classList.remove('rotated');
                } else {
                    submenu.classList.add('show');
                    chevron.classList.add('rotated');
                }
            }
        }
        
        // Auto-expand if on Grades pages
        document.addEventListener('DOMContentLoaded', function() {
            const isGradesPage = window.location.pathname.includes('/grades') || 
                                window.location.pathname.includes('/final-grades');
            
            if (isGradesPage) {
                const submenu = document.getElementById('gradesSubmenu');
                const chevron = document.querySelector('.grades-chevron');
                
                if (submenu && chevron) {
                    closeAllSubmenus('gradesSubmenu');
                    submenu.classList.add('show');
                    chevron.classList.add('rotated');
                }
            }
        });
    </script>

    <x-modal.destructive
        id="signOutModal"
        title="Sign Out"
        description="End your current session"
        :backdrop="false"
        :keyboard="false"
    >
        <x-slot:icon><i class="bi bi-box-arrow-right me-1"></i></x-slot:icon>
        <div class="text-center py-3">
            <div class="mb-3">
                <i class="bi bi-question-circle-fill text-danger" style="font-size: 2.5rem;"></i>
            </div>
            <h5 class="fw-bold mb-3">Are you sure you want to sign out?</h5>
            <p class="text-muted mb-0">You'll need to sign in again to access your account.</p>
        </div>

        <x-slot:footer>
            <form method="POST" action="{{ route('logout') }}" id="logoutForm" class="d-inline" onsubmit="clearAnnouncementSession()">
                @csrf
                <x-modal.actions destructive-text="Yes, Sign Out" />
            </form>
        </x-slot:footer>
    </x-modal.destructive>
    
    {{-- Styles: resources/css/layout/app.css --}}

    {{-- Note: Modal backdrop handling is done via data-bs-backdrop attributes on individual modals --}}

    {{-- Toast Notifications --}}
    @include('components.toast-notifications')

    {{-- Confirmation Dialog (Alpine.js) --}}
    @include('components.confirmation-dialog')

    {{-- System Announcements Popup --}}
    @include('components.announcement-popup')

    <script>
        // Clear announcement session storage on logout
        function clearAnnouncementSession() {
            sessionStorage.removeItem('dismissedAnnouncements');
        }

        (function () {
            // Display branded message to anyone inspecting the console
            if (!window.console) {
                return;
            }

            const titleStyle = [
                'font-weight: 700',
                'font-size: 32px',
                'color: #023336',
                'letter-spacing: 0.2rem',
                'font-family: "Inter", sans-serif',
            ].join(';');

            const subtitleStyle = [
                'font-size: 14px',
                'color: #1bce8f',
                'font-family: "Inter", sans-serif',
            ].join(';');

            console.log('%cACADEX', titleStyle);
            console.log('%cAn Outcome-based automated grading system', subtitleStyle);
        })();
    </script>

    @stack('scripts')
</body>
</html>
