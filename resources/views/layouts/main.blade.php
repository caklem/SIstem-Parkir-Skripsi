<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Parkir')</title>

    <!-- AdminLTE and Dependencies -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.1.0/styles/overlayscrollbars.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">

    <style>
        .main-sidebar {
            background: linear-gradient(135deg, #2b4162 0%, #12100e 100%) !important;
            position: fixed;
        }
        
        .nav-sidebar .nav-item {
            margin: 5px 15px;
        }

        .nav-sidebar .nav-link {
            border-radius: 8px;
            margin: 5px 0;
            transition: all 0.3s;
            color: #fff;
        }

        .nav-sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .nav-sidebar .nav-link.active {
            background-color: #ffc107;
            color: #000 !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .brand-link {
            border-bottom: 1px solid rgba(255,255,255,0.1) !important;
            padding: 15px !important;
        }

        .brand-text {
            color: #fff !important;
            font-weight: 500 !important;
            font-size: 1.2rem;
        }

        .nav-header {
            color: #ffc107 !important;
            padding: 1rem 1rem 0.5rem;
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .os-theme-light {
            --os-handle-bg: rgba(255,255,255,0.2);
        }

        /* Tambahkan CSS untuk responsivitas */
        @media (max-width: 991.98px) {
            .sidebar-mini .main-sidebar {
                transform: translateX(-250px);
                transition: transform 0.3s ease-in-out;
            }

            .sidebar-mini.sidebar-open .main-sidebar {
                transform: translateX(0);
            }

            .content-wrapper {
                margin-left: 0 !important;
            }

            .sidebar-open .main-overlay {
                display: block;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1031;
            }
        }

        /* Perbaikan tampilan navbar */
        .navbar {
            padding: 0.5rem 1rem;
        }

        .navbar-toggler {
            border: none;
            padding: 0.5rem;
        }

        /* Animasi sidebar */
        .main-sidebar {
            transition: transform 0.3s ease-in-out, width 0.3s ease-in-out;
        }

        /* Perbaikan tampilan konten */
        @media (max-width: 767.98px) {
            .content-wrapper {
                padding: 15px;
            }

            .main-footer {
                text-align: center;
            }

            .main-footer .float-right {
                float: none !important;
                display: block;
                margin-bottom: 0.5rem;
            }
        }

        /* Perbaikan sidebar behavior */
        @media (min-width: 992px) {
            .sidebar-mini.sidebar-collapse .main-sidebar {
                width: 4.6rem;
            }
            
            .sidebar-mini.sidebar-collapse .content-wrapper {
                margin-left: 4.6rem;
            }
        }

        @media (max-width: 991.98px) {
            .main-sidebar {
                width: 250px;
                margin-left: -250px;
            }

            .sidebar-open .main-sidebar {
                margin-left: 0;
            }

            .content-wrapper {
                margin-left: 0 !important;
            }
        }

        /* Improve transition */
        .main-sidebar, 
        .content-wrapper {
            transition: margin-left 0.3s ease-in-out, width 0.3s ease-in-out;
        }
    </style>
</head>
<body class="sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        {{-- @include('layouts.navbar') --}}

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-warning elevation-4">
            <!-- Brand Logo -->
            <a href="/" class="brand-link">
                <img src="{{ asset('img/logo.png') }}" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">GOLDEN HILL</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <li class="nav-item">
                            <a href="{{ route('parkir.dashboard') }}" class="nav-link {{ Request::routeIs('parkir.dashboard') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-speedometer2"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        
                        <li class="nav-header">MANAJEMEN PARKIR</li>
                        
                        <li class="nav-item">
                            <a href="{{ route('parkir.index') }}" class="nav-link {{ Request::routeIs('parkir.index') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-box-arrow-in-right"></i>
                                <p>Data Masuk</p>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="{{ route('parkir.keluar') }}" class="nav-link {{ Request::routeIs('parkir.keluar') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-box-arrow-right"></i>
                                <p>Data Keluar</p>
                            </a>
                        </li>

                        <li class="nav-header">PENGATURAN</li>
                        
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-person-circle"></i>
                                <p>
                                    Akun
                                    <i class="bi bi-chevron-right ms-auto"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="./profile" class="nav-link">
                                        <i class="nav-icon bi bi-person"></i>
                                        <p>Profil</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link text-danger">
                                        <i class="nav-icon bi bi-box-arrow-left"></i>
                                        <p>Keluar</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline">
                Sistem Parkir
            </div>
            <strong>Copyright &copy; 2024</strong>
        </footer>
    </div>
    <div class="main-overlay"></div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.1.0/browser/overlayscrollbars.browser.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize AdminLTE
            if (typeof $.fn.AdminLTE !== 'undefined') {
                $.AdminLTE.init();
            }

            // Initialize OverlayScrollbars if sidebar exists
            if ($('.main-sidebar').length > 0) {
                const options = {
                    scrollbars: {
                        autoHide: 'leave',
                        clickScrolling: true
                    }
                };
                
                $('.main-sidebar').each(function() {
                    if (typeof OverlayScrollbars !== 'undefined') {
                        OverlayScrollbars(this, options);
                    }
                });
            }

            // Sidebar toggle handler
            $('[data-widget="pushmenu"]').on('click', function(e) {
                e.preventDefault();
                if ($(window).width() >= 992) {
                    $('body').toggleClass('sidebar-collapse');
                } else {
                    $('body').toggleClass('sidebar-open');
                }
            });

            // Add active class to current menu item
            const currentPath = window.location.pathname;
            $(`.nav-sidebar a[href="${currentPath}"]`).addClass('active');

            // Tambahkan handler untuk overlay
            $('.main-overlay').on('click', function() {
                $('body').removeClass('sidebar-open');
            });

            // Perbaikan toggle sidebar untuk mobile
            $('[data-widget="pushmenu"], [data-lte-toggle="sidebar"]').on('click', function(e) {
                e.preventDefault();
                if ($(window).width() <= 991.98) {
                    $('body').toggleClass('sidebar-open');
                } else {
                    $('body').toggleClass('sidebar-collapse');
                }
            });

            // Handle window resize
            $(window).on('resize', function() {
                if ($(window).width() > 991.98) {
                    $('body').removeClass('sidebar-open');
                }
            });

            // Close sidebar when clicking outside on mobile
            $(document).on('click', function(e) {
                if ($(window).width() <= 991.98) {
                    if (!$(e.target).closest('.main-sidebar').length && 
                        !$(e.target).closest('[data-widget="pushmenu"]').length &&
                        !$(e.target).closest('[data-lte-toggle="sidebar"]').length) {
                        $('body').removeClass('sidebar-open');
                    }
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>