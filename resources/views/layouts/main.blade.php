<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sipark | Sistem Parkir')</title>

    <!-- AdminLTE and Dependencies -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.1.0/styles/overlayscrollbars.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{asset('css/plate-detection.css')}}">
    <link rel="stylesheet" href="{{ asset('css/plate-validator.css') }}">
   
     <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('img/logo.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">

    <!-- QR Code Scanner -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Library untuk deteksi plat nomor di browser -->
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@3.18.0/dist/tf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/coco-ssd@2.2.2/dist/coco-ssd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@2.1.5/dist/tesseract.min.js"></script>
    <script src="{{ asset('js/indonesia-plate-detection.js') }}"></script>

    @stack('styles')

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

        /* QR Scanner Styles */
        #qr-reader-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }

        #reader {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        #reader video {
            border-radius: 8px;
        }

        .qr-input-group {
            position: relative;
        }

        .qr-input-group .btn-scan {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        /* Sidebar Responsive Styles */
        .main-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1038;
            transition: transform .3s ease-in-out, margin-left .3s ease-in-out, width .3s ease-in-out;
        }

        @media (max-width: 991.98px) {
            .main-sidebar {
                transform: translateX(-250px);
                margin-left: 0;
            }

            .sidebar-open .main-sidebar {
                transform: translateX(0);
            }

            .main-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1037;
            }

            .sidebar-open .main-overlay {
                display: block;
            }

            .content-wrapper {
                margin-left: 0 !important;
            }
        }

        /* Improve transitions */
        .main-sidebar, 
        .content-wrapper {
            transition: transform .3s ease-in-out,
                        margin-left .3s ease-in-out;
        }

        /* Navbar toggle button */
        [data-lte-toggle="sidebar"] {
            cursor: pointer;
            transition: color .3s ease;
        }
    </style>

    @push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@4.0.3/dist/tesseract.min.js"></script>
    <script src="{{ asset('js/plate-detection.js') }}"></script>
    @endpush
</head>
<body class="sidebar-mini layout-fixed">

    <div class="wrapper">
        <!-- Navbar -->
        {{-- @include('layouts.navbar') --}}

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-warning elevation-4">
            <!-- Brand Logo -->
            <a href="/" class="brand-link text-center">
                <img src="{{ asset('img/logo.png') }}" alt="Logo" class="brand-image img-circle elevation-2" 
                     style="opacity: 0.8; max-height: 40px; margin-left: 0.2rem; margin-right: 0.5rem;">
                <span class="brand-text font-weight-light">GOLDEN HILL</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <li class="nav-item">
                            <a href="{{ route('parkir.dashboard') }}" class="nav-link {{ Request::routeIs('parkir.dashboard') ? 'active' : '' }}">
                                <i class="bi bi-house"></i>
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

                        <li class="nav-header">KARTU PARKIR</li>

                        <li class="nav-item">
                            <a href="{{ route('qrcode.generate') }}" class="nav-link {{ Request::routeIs('qrcode.generate') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-qr-code"></i>
                                <p>Generate QR Code</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('qrcode.list') }}" class="nav-link {{ Request::routeIs('qrcode.list') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-card-list"></i>
                                <p>Daftar Kartu</p>
                            </a>
                        </li>

                        <li class="nav-item">
                                <li class="nav-item">
                                    <form action="{{ route('logout') }}" method="POST" id="logout-form">
                                    @csrf
                                    <a href="#" class="nav-link text-danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="nav-icon bi bi-box-arrow-left"></i>
                                        <p>Keluar</p>
                                    </a>
                                </form>
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
    <script src="{{ asset('js/create-camera-elements.js') }}"></script>
    <!-- Tambahkan di bagian bawah sebelum </body> -->
    <script src="{{ asset('js/camera-focus-helper.js') }}"></script>
    <script src="{{ asset('js/preload-tesseract.js') }}"></script>
    <script src="{{ asset('js/indonesia-plate-detection.js') }}"></script>
    <script src="{{ asset('js/plate-input-validator.js') }}"></script>
    

    <!-- Replace the existing QR scanner script with this one -->
    <script>
        let html5QrcodeScanner = null;

        function stopScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear();
                html5QrcodeScanner = null;
                $('#stopButton').show();
                $('#startButton').hide();
            }
        }

        // Simple QR Scanner implementation
        $(document).ready(function() {
            $('#startButton').click(function() {
                if (html5QrcodeScanner === null) {
                    const html5QrCode = new Html5Qrcode("reader");
                    const config = {
                        fps: 10,
                        qrbox: { width: 250, height: 250 }
                    };

                    $('#stopButton').show();
                    $(this).hide();

                    html5QrCode.start(
                        { facingMode: "environment" },
                        config,
                        (decodedText) => {
                            // On successful scan
                            $('#nomor_kartu').val(decodedText);
                            
                            html5QrCode.stop();
                            $('#reader').empty();
                            $('#stopButton').hide();
                            $('#startButton').show();

                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'QR Code berhasil di-scan',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        },
                        (error) => {
                            // Silence errors
                        }
                    ).catch((err) => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Tidak dapat mengakses kamera'
                        });
                        $('#stopButton').hide();
                        $('#startButton').show();
                    });

                    // Stop button handler
                    $('#stopButton').click(function() {
                        if (html5QrCode) {
                            html5QrCode.stop();
                            $('#reader').empty();
                            $('#stopButton').hide();
                            $('#startButton').show();
                        }
                    });
                }
            });

            // Reset scanner when modal is closed
            $('.modal').on('hidden.bs.modal', function() {
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.clear();
                    html5QrcodeScanner = null;
                }
                $('#reader').empty();
                $('#stopButton').hide();
                $('#startButton').show();
            });
        });
    </script>

    <script>
    $(document).ready(function() {
        // Handle sidebar toggle
        $('[data-lte-toggle="sidebar"]').on('click', function(e) {
            e.preventDefault();
            $('body').toggleClass('sidebar-open');
            
            // Add overlay when sidebar is open
            if ($('body').hasClass('sidebar-open')) {
                $('.main-overlay').fadeIn();
            } else {
                $('.main-overlay').fadeOut();
            }
        });

        // Close sidebar when clicking overlay
        $('.main-overlay').on('click', function() {
            $('body').removeClass('sidebar-open');
            $(this).fadeOut();
        });

        // Handle window resize
        $(window).on('resize', function() {
            if ($(window).width() > 991.98) {
                $('body').removeClass('sidebar-open');
                $('.main-overlay').fadeOut();
            }
        });
    });
    </script>

    @stack('scripts')
</body>
</html>