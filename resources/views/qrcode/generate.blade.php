<!-- filepath: c:\xampp3\htdocs\sistem-parkir\resources\views\qrcode\generate.blade.php -->
@extends('layouts.main')

@section('title', 'Generate QR Code')

@section('content')
<!-- Simple Navbar -->
<div class="navbar bg-white py-2 px-4 border-bottom d-flex align-items-center">
    <!-- Tombol Sidebar -->
    <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
        <i class="bi bi-list"></i>
    </a>
    <!-- Spasi Kecil -->
    <div class="ms-2"></div>
    <!-- Judul -->
    {{-- <h5 class="text-warning font-weight-bold mb-0">Dashboard Parkir Keluar</h5>     --}}
</div>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">Generate QR Code</h1>
                <p class="text-muted m-0">Buat QR Code untuk kartu parkir</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-house"></i></a></li>
                    <li class="breadcrumb-item active">Generate QR Code</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Form Card -->
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">Generate QR Code Kartu Parkir</h3>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <span>Terdapat kesalahan pada input form:</span>
                                </div>
                                <ul class="mb-0 mt-2 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Generator Form -->
                        <div class="row justify-content-center">
                            <div class="col-md-8 col-lg-6">
                                <form action="{{ route('qrcode.generate') }}" method="POST" id="generateQrForm">
                                    @csrf
                                    <div class="form-group mb-4">
                                        <label for="nomorKartu" class="form-label">Nomor Kartu Parkir</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-credit-card-2-front"></i>
                                            </span>
                                            <input type="text" 
                                                class="form-control @error('nomorKartu') is-invalid @enderror" 
                                                id="nomorKartu" 
                                                name="nomorKartu" 
                                                value="{{ old('nomorKartu') }}"
                                                placeholder="Masukkan nomor kartu (huruf dan angka)">
                                            <div class="invalid-feedback">
                                                Nomor kartu parkir tidak boleh kosong
                                            </div>
                                        </div>
                                        <small class="form-text text-muted mt-1">
                                            <i class="bi bi-info-circle-fill me-1"></i> Contoh: A001, B002, dll.
                                        </small>
                                    </div>
                                    <div class="d-grid gap-2 col-md-6 mx-auto">
                                        <button type="submit" class="btn btn-primary" id="generateButton">
                                            <i class="bi bi-qr-code me-2"></i>Generate QR Code
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- QR Code Result (jika ada) -->
            @if(isset($qrCode))
            <div class="col-12 mt-4">
                <div class="card card-outline card-success shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">QR Code Berhasil Dibuat</h3>
                    </div>
                    <div class="card-body text-center">
                        <div class="row justify-content-center">
                            <div class="col-md-6 col-lg-4">
                                <div class="qr-container p-3 bg-white rounded shadow-sm mx-auto mb-3" style="max-width: 250px">
                                    {!! $qrCode !!}
                                </div>
                                <h4 class="mb-3">{{ $nomorKartu ?? '' }}</h4>
                                <div class="d-grid gap-2 col-md-8 mx-auto">
                                    <a href="{{ route('qrcode.download', ['nomorKartu' => $nomorKartu ?? '']) }}" class="btn btn-success mb-2">
                                        <i class="bi bi-download me-2"></i>Download QR Code
                                    </a>
                                    <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                                        <i class="bi bi-printer me-2"></i>Cetak QR Code
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* QR Code print styles */
    @media print {
        body * {
            visibility: hidden;
        }
        .qr-container, .qr-container * {
            visibility: visible;
        }
        .qr-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Front-end validation untuk form generate QR code
        $('#generateQrForm').on('submit', function(e) {
            const nomorKartu = $('#nomorKartu').val().trim();
            
            // Reset validasi terlebih dahulu
            $('#nomorKartu').removeClass('is-invalid');
            
            // Validasi: Nomor kartu tidak boleh kosong
            if (!nomorKartu) {
                e.preventDefault(); // Mencegah form submit
                $('#nomorKartu').addClass('is-invalid');
                
                // Tampilkan SweetAlert
                Swal.fire({
                    icon: 'warning',
                    title: 'Input Kosong',
                    text: 'Silahkan masukkan nomor kartu parkir terlebih dahulu',
                    confirmButtonColor: '#3085d6'
                }).then((result) => {
                    $('#nomorKartu').focus();
                });
                
                return false;
            }
            
            // Format validasi (opsional)
            // Contoh: Hanya huruf dan angka (alfanumerik)
            const validFormat = /^[a-zA-Z0-9]+$/.test(nomorKartu);
            if (!validFormat) {
                e.preventDefault(); // Mencegah form submit
                
                Swal.fire({
                    icon: 'error',
                    title: 'Format Salah',
                    text: 'Nomor kartu hanya boleh berisi huruf dan angka',
                    confirmButtonColor: '#3085d6'
                }).then((result) => {
                    $('#nomorKartu').focus();
                });
                
                return false;
            }
            
            // Tampilkan loading indicator saat generate (opsional)
            $('#generateButton').html('<i class="spinner-border spinner-border-sm me-2"></i>Memproses...');
            $('#generateButton').prop('disabled', true);
            
            return true; // Lanjutkan submit form
        });
        
        // Hapus validasi saat input berubah
        $('#nomorKartu').on('input', function() {
            if ($(this).val().trim() !== '') {
                $(this).removeClass('is-invalid');
            }
        });
    });
</script>
@endpush