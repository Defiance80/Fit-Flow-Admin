<!DOCTYPE html>
<html lang="{{ $currentLanguage->code ?? 'en' }}" dir="{{ $isRTL ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') &mdash; {{ config('app.name') }}</title>
    @include('includes.css')
    @stack('style')
</head>

<body>
    <div id="app">
        <div class="main-wrapper">
            <!-- Header -->
            <div class="navbar-bg"></div>
            <nav class="navbar navbar-expand-lg main-navbar">
                <form class="form-inline mr-auto">
                    <ul class="navbar-nav mr-3">
                        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars" style="color: #94A3B8;"></i></a></li>
                    </ul>
                </form>
                <ul class="navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <div style="background: #0D9488; color: white; width: 34px; height: 34px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; margin-right: 8px;">
                                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                            </div>
                            <div class="d-sm-none d-lg-inline-block" style="color: #E2E8F0;">{{ auth()->user()->name ?? 'Admin' }}</div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div class="dropdown-title">{{ auth()->user()->email ?? '' }}</div>
                            <div class="dropdown-divider"></div>
                            <a href="{{ route('logout') }}" class="dropdown-item has-icon text-danger"
                               href="{{ route('admin.logout') }}">
                                <i class="fas fa-sign-out-alt"></i> {{ __('Logout') }}
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>
            

            <!-- Sidebar -->
            @include('components.sidebar')
            <!-- Main Content -->
            <div class="main-content">
                <!-- Page Title Section -->
                @hasSection('page-title')
                    <section class="section">
                        <div class="section-header">
                            @yield('page-title')
                        </div>
                    </section>
                @endif

                @yield('main')
            </div>

            <!-- Footer -->
            <footer class="main-footer">
                <div class="footer-left">
                    Copyright &copy; {{ date('Y') }} <div class="bullet"></div> {{ config('app.name') }}
                </div>
                <div class="footer-right"> {{ __('1.0.0') }} </div>
            </footer>
        </div>
    </div>
    
    @include('includes.js')
    @stack('scripts')
    @yield('script')
    @yield('js')
    
    <!-- RTL Sidebar Toggle Script -->
    @if($isRTL)
    <script>
        $(document).ready(function() {
            console.log('RTL Mode Active - Forcing sidebar to right side');
            
            // Check if mobile/tablet
            function isMobile() {
                return window.innerWidth <= 1024;
            }
            
            // Force RTL sidebar positioning
            function forceRTLSidebar() {
                console.log('Applying RTL sidebar positioning...');
                
                // Prevent horizontal scrolling
                $('html, body').css({
                    'overflow-x': 'hidden',
                    'max-width': '100vw'
                });
                
                if (isMobile()) {
                    // Mobile/Tablet behavior - hidden by default
                    $('.main-sidebar').css({
                        'position': 'fixed',
                        'top': '0',
                        'right': '-260px',
                        'left': 'auto',
                        'width': '260px',
                        'height': '100vh',
                        'z-index': '9999',
                        'background': '#fff',
                        'box-shadow': '-2px 0 5px rgba(0,0,0,0.1)',
                        'transform': 'translateX(0)',
                        'transition': 'right 0.3s ease'
                    });
                    
                    $('.main-content').css({
                        'margin-right': '0',
                        'margin-left': '0',
                        'max-width': '100vw',
                        'overflow-x': 'hidden'
                    });
                } else {
                    // Desktop behavior - always visible
                    $('.main-sidebar').css({
                        'position': 'fixed',
                        'top': '0',
                        'right': '0',
                        'left': 'auto',
                        'width': '260px',
                        'height': '100vh',
                        'z-index': '9999',
                        'background': '#fff',
                        'box-shadow': '-2px 0 5px rgba(0,0,0,0.1)',
                        'transform': 'translateX(0)',
                        'transition': 'right 0.3s ease'
                    });
                    
                    $('.main-content').css({
                        'margin-right': '260px',
                        'margin-left': '0',
                        'max-width': 'calc(100vw - 260px)',
                        'overflow-x': 'hidden'
                    });
                }
                
                // Ensure all containers respect viewport width
                $('.container, .container-fluid, .row, .card, .section').css({
                    'max-width': '100%',
                    'overflow-x': 'hidden'
                });
                
                console.log('RTL sidebar positioning applied - Mobile:', isMobile());
            }
            
            // Apply RTL positioning immediately
            forceRTLSidebar();
            
            // Reapply after a short delay to ensure DOM is ready
            setTimeout(forceRTLSidebar, 100);
            setTimeout(forceRTLSidebar, 500);
            
            // Reapply on window resize
            $(window).on('resize', function() {
                forceRTLSidebar();
            });
            
            // Handle sidebar toggle for RTL
            $('[data-toggle="sidebar"]').on('click', function(e) {
                e.preventDefault();
                
                if (isMobile()) {
                    // Mobile behavior - toggle show/hide
                    $('.main-sidebar').toggleClass('show hide');
                    
                    if ($('.main-sidebar').hasClass('show')) {
                        $('.main-sidebar').css('right', '0');
                    } else {
                        $('.main-sidebar').css('right', '-260px');
                    }
                } else {
                    // Desktop behavior - always visible
                    forceRTLSidebar();
                }
            });
            
            // Close sidebar when clicking outside (mobile only)
            $(document).on('click', function(e) {
                if (isMobile() && !$(e.target).closest('.main-sidebar, [data-toggle="sidebar"]').length) {
                    $('.main-sidebar').removeClass('show').addClass('hide').css('right', '-260px');
                }
            });
            
            // Close sidebar on escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && isMobile()) {
                    $('.main-sidebar').removeClass('show').addClass('hide').css('right', '-260px');
                }
            });
        });
    </script>
    @endif
</body>

</html>

