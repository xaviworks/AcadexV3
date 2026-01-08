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
    <meta name="color-scheme" content="light">

    <title>{{ config('app.name', 'Laravel') }}</title>
    
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

    {{-- Styles: resources/css/layout/app.css --}}

    <!-- Tutorial System Styles (Admin, VPAA, and Dean users) -->
    @auth
        @if(Auth::user()->role === 3 || Auth::user()->role === 5 || Auth::user()->role === 2)
            <link rel="stylesheet" href="{{ asset('css/admin-tutorial.css') }}">
        @endif
    @endauth

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

        // Smooth page transitions - show loader on navigation
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            
            if (link && 
                link.href && 
                link.href.indexOf(window.location.origin) === 0 &&
                !link.hasAttribute('target') &&
                !link.hasAttribute('download') &&
                !link.classList.contains('dropdown-toggle') &&
                !link.getAttribute('href').startsWith('#') &&
                !link.closest('.dropdown-menu')) {
                
                // Show loading screen for internal navigation
                document.body.classList.remove('loaded');
            }
        });

        // Handle browser back/forward
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                setTimeout(function() {
                    document.body.classList.add('loaded');
                }, 100);
            }
        });

        // Session validity check on page visibility change (handles browser back button)
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                // Quick session check via lightweight endpoint
                fetch('{{ route("session.check") }}', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (response.status === 401 || response.status === 419) {
                        // Session expired or CSRF mismatch - redirect to login
                        window.location.href = '{{ route("login") }}';
                    }
                })
                .catch(() => {
                    // Network error - may indicate session issue, reload page
                    window.location.reload();
                });
            }
        });

        // Also check on pageshow for bfcache restoration
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                // Page was restored from bfcache - verify session
                fetch('{{ route("session.check") }}', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (response.status === 401 || response.status === 419) {
                        window.location.href = '{{ route("login") }}';
                    }
                })
                .catch(() => {
                    window.location.reload();
                });
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

    {{-- Sign Out Confirmation Modal - At body level for proper z-index stacking --}}
    <div class="modal fade" id="signOutModal" tabindex="-1" aria-labelledby="signOutModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 1.25rem; overflow: hidden;">
                {{-- Header with gradient background --}}
                <div class="modal-header border-0 text-white position-relative" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); padding: 2rem 2rem 1.5rem;">
                    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: url('data:image/svg+xml,%3Csvg width=\"20\" height=\"20\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cpath d=\"M0 0h20v20H0z\" fill=\"none\"/%3E%3Cpath d=\"M0 0l10 10M10 0L0 10M10 10l10 10M20 0L10 10\" stroke=\"%23ffffff\" stroke-width=\"0.5\" opacity=\"0.1\"/%3E%3C/svg%3E'); opacity: 0.3;"></div>
                    <div class="w-100 position-relative">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="p-3 rounded-circle me-3" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px);">
                                    <i class="bi bi-box-arrow-right" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title fw-bold mb-0" id="signOutModalLabel">Sign Out</h5>
                                    <small style="opacity: 0.9;">End your current session</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
                
                {{-- Body with icon and message --}}
                <div class="modal-body text-center" style="padding: 2.5rem 2rem;">
                    <div class="mb-3">
                        <div class="mx-auto d-inline-flex align-items-center justify-content-center rounded-circle" 
                             style="width: 80px; height: 80px; background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%);">
                            <i class="bi bi-question-circle-fill text-danger" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-3" style="color: #2c3e50;">Are you sure you want to sign out?</h5>
                    <p class="text-muted mb-0">You'll need to sign in again to access your account.</p>
                </div>
                
                {{-- Footer with action buttons --}}
                <div class="modal-footer border-0 bg-light" style="padding: 1.5rem 2rem;">
                    <button type="button" class="btn btn-light px-4 py-2 rounded-pill" data-bs-dismiss="modal" style="font-weight: 600;">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <form method="POST" action="{{ route('logout') }}" id="logoutForm" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger px-4 py-2 rounded-pill" style="font-weight: 600; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);">
                            <i class="bi bi-box-arrow-right me-2"></i>Yes, Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Styles: resources/css/layout/app.css --}}

    {{-- Note: Modal backdrop handling is done via data-bs-backdrop attributes on individual modals --}}

    {{-- Toast Notifications --}}
    @include('components.toast-notifications')

    {{-- Confirmation Dialog (Alpine.js) --}}
    @include('components.confirmation-dialog')

    <script>
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
            console.log('%cOutcome-based education intelligence platform', subtitleStyle);
        })();
    </script>

    @stack('scripts')
</body>
</html>
