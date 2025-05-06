<!-- filepath: c:\xampp3\htdocs\sistem-parkir\resources\views\qrcode\print-card.blade.php -->
@extends('layouts.main')

@section('title', 'Print QR Code')

@section('content')
<!-- Simple Navbar -->
<div class="navbar bg-white py-2 px-4 border-bottom d-flex align-items-center">
    <!-- Tombol Sidebar -->
    <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
        <i class="bi bi-list"></i>
    </a>
    <!-- Spasi Kecil -->
    <div class="ms-2"></div>
</div>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Print QR Code</h1>
                <p class="text-muted m-0">Cetak kartu QR Code untuk penggunaan di lapangan</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-house"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('qrcode.list') }}">Daftar QR Code</a></li>
                    <li class="breadcrumb-item active">Print QR Code</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 col-sm-10">
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">QR Code Kartu Parkir</h3>
                        <div class="card-tools no-print">
                            <a href="{{ route('qrcode.list') }}" class="btn btn-tool">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <div class="qr-container py-4">
                            <div class="mb-4 mx-auto" style="max-width: 250px;">
                                {!! $qrcode !!}
                            </div>
                            <h3 class="mb-0">{{ $nomorKartu }}</h3>
                            <p class="text-muted mb-4">Kartu Parkir Golden Hill</p>
                        </div>
                        
                        <div class="action-buttons no-print">
                            <button onclick="window.print()" class="btn btn-primary btn-lg">
                                <i class="fas fa-print me-2"></i> Cetak QR Code
                            </button>
                            {{-- <a href="{{ route('qrcode.download', ['nomorKartu' => $nomorKartu]) }}" class="btn btn-success btn-lg ms-2">
                                <i class="fas fa-download me-2"></i> Download
                            </a> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Styling untuk halaman normal (non-print) */
    .qr-container {
        background-color: white;
        border-radius: 0.5rem;
    }
    
    .qr-container svg {
        width: 100%;
        height: auto;
    }
    
    /* Print Styles */
    @media print {
        body {
            margin: 0;
            padding: 0;
            background: white;
        }
        
        .no-print, .main-footer, .navbar, .content-header, .breadcrumb { 
            display: none !important; 
        }
        
        .content-wrapper { 
            margin-left: 0 !important;
            padding: 0 !important;
            background-color: white !important;
        }
        
        .main-sidebar, .main-header { 
            display: none !important; 
        }
        
        .card {
            box-shadow: none !important;
            border: none !important;
        }
        
        .card-header {
            display: none !important;
        }
        
        .card-body {
            padding: 0 !important;
        }
        
        .container-fluid, .content, section {
            padding: 0 !important;
            margin: 0 !important;
        }
        
        .qr-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1cm !important;
        }
        
        .qr-container svg { 
            max-width: 250px !important;
            height: auto;
            margin: 0 auto;
        }
        
        .qr-container h3 {
            margin-top: 20px;
            font-size: 24px;
        }
    }
    
    /* Responsivitas */
    @media (max-width: 576px) {
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .action-buttons .btn {
            margin: 0 !important;
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Document ready handler sebelum print
        window.addEventListener('beforeprint', function() {
            // Persiapan sebelum print (jika diperlukan)
            console.log('Preparing to print...');
        });
        
        // Handler setelah print
        window.addEventListener('afterprint', function() {
            console.log('Print completed or cancelled.');
        });
    });
</script>
@endpush