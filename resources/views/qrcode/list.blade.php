<!-- filepath: c:\xampp3\htdocs\sistem-parkir\resources\views\qrcode\list.blade.php -->
@extends('layouts.main')

@section('title', 'Daftar QR Code')

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
                <h1 class="m-0 text-dark">Daftar QR Code</h1>
                <p class="text-muted m-0">Manajemen kartu parkir yang terdaftar</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-house"></i></a></li>
                    <li class="breadcrumb-item active">Daftar QR Code</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Data Card -->
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <h3 class="card-title mb-0">Data Kartu Parkir</h3>
                            
                            <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                                <div class="input-group input-group-sm position-relative search-container">
                                    <input type="text" id="searchQrCode" class="form-control" placeholder="Cari nomor kartu...">
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-search"></i>
                                        </span>
                                    </div>
                                    <div class="search-clear" id="clearSearch" style="display: none">
                                        <i class="bi bi-x-circle-fill"></i>
                                    </div>
                                </div>
                                
                                <a href="{{ route('qrcode.generate') }}" class="btn btn-primary btn-sm d-flex align-items-center">
                                    <i class="bi bi-plus-lg me-1"></i>
                                    <span>Buat QR Code</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    {{ session('success') }}
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="qrCodeTable">
                                <thead>
                                    <tr class="bg-light">
                                        <th style="width: 5%; text-align: center;" class="align-middle">No</th>
                                        <th style="width: 35%;" class="align-middle">Nomor Kartu</th>
                                        <th style="width: 30%;" class="align-middle">Tanggal Dibuat</th>
                                        <th style="width: 30%; text-align: center;" class="align-middle">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($qrcodes as $index => $qr)
                                        <tr>
                                            <td class="text-center align-middle">{{ $index + $qrcodes->firstItem() }}</td>
                                            <td class="align-middle">{{ $qr->nomor_kartu }}</td>
                                            <td class="align-middle">{{ $qr->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('qrcode.print', ['nomorKartu' => $qr->nomor_kartu])}}"
                                                        class="btn btn-info">
                                                        <i class="fas fa-print me-1"></i> Cetak
                                                    </a>
                                                    <button type="button" class="btn btn-danger delete-qr" 
                                                            data-id="{{ $qr->id }}"
                                                            data-nomor="{{ $qr->nomor_kartu }}">
                                                        <i class="fas fa-trash me-1"></i> Hapus
                                                    </button>
                                                </div>
                                                
                                                <form id="delete-form-{{ $qr->id }}" action="{{ route('qrcode.delete', $qr->id) }}" method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="bi bi-qr-code text-muted mb-3" style="font-size: 3rem;"></i>
                                                    <p class="text-muted mb-3">Belum ada data QR Code yang terdaftar</p>
                                                    <a href="{{ route('qrcode.generate') }}" class="btn btn-primary">
                                                        <i class="bi bi-plus-lg me-1"></i> Buat QR Code Baru
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Custom Pagination -->
                        @if($qrcodes->hasPages())
                            <div class="pagination-container mt-4">
                                <nav>
                                    <ul class="pagination justify-content-center justify-content-md-end">
                                        {{-- Previous Page Link --}}
                                        @if($qrcodes->onFirstPage())
                                            <li class="page-item disabled">
                                                <span class="page-link" aria-hidden="true">&laquo;</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $qrcodes->previousPageUrl() }}" rel="prev" aria-label="Previous">&laquo;</a>
                                            </li>
                                        @endif

                                        {{-- Pagination Elements --}}
                                        @for($i = 1; $i <= $qrcodes->lastPage(); $i++)
                                            @if($i == $qrcodes->currentPage())
                                                <li class="page-item active">
                                                    <span class="page-link">{{ $i }}</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $qrcodes->url($i) }}">{{ $i }}</a>
                                                </li>
                                            @endif
                                        @endfor

                                        {{-- Next Page Link --}}
                                        @if($qrcodes->hasMorePages())
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $qrcodes->nextPageUrl() }}" rel="next" aria-label="Next">&raquo;</a>
                                            </li>
                                        @else
                                            <li class="page-item disabled">
                                                <span class="page-link" aria-hidden="true">&raquo;</span>
                                            </li>
                                        @endif
                                    </ul>
                                </nav>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Live search functionality
        $("#searchQrCode").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#qrCodeTable tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
            
            // Show no results message if needed
            const visibleRows = $("#qrCodeTable tbody tr:visible").length;
            if (visibleRows === 0 && value !== '') {
                if ($("#no-results-row").length === 0) {
                    $("#qrCodeTable tbody").append(
                        '<tr id="no-results-row"><td colspan="4" class="text-center py-4">' +
                        '<div class="d-flex flex-column align-items-center">' +
                        '<i class="bi bi-search mb-2" style="font-size: 2rem;"></i>' +
                        '<p class="text-muted">Tidak ada hasil yang cocok dengan pencarian Anda</p>' +
                        '</div></td></tr>'
                    );
                }
            } else {
                $("#no-results-row").remove();
            }
        });

        // Menambahkan fungsionalitas clear button untuk search
        $("#searchQrCode").on("input", function() {
            if ($(this).val().length > 0) {
                $("#clearSearch").show();
            } else {
                $("#clearSearch").hide();
            }
        });

        $("#clearSearch").on("click", function() {
            $("#searchQrCode").val('');
            $("#searchQrCode").trigger("keyup");  // Trigger search event
            $(this).hide();
        });
        
        // Sweet Alert for delete confirmation
        $('.delete-qr').on('click', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const nomor = $(this).data('nomor');
            
            Swal.fire({
                title: 'Konfirmasi Hapus',
                html: `Anda yakin ingin menghapus QR Code <strong>${nomor}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit the delete form
                    document.getElementById(`delete-form-${id}`).submit();
                }
            });
        });
    });
</script>
@endpush

@push('styles')
<style>
    /* Style untuk tabel */
    .table {
        margin-bottom: 0;
    }
    
    .table thead tr th {
        font-weight: 600;
        border-bottom-width: 2px;
    }
    
    .align-middle {
        vertical-align: middle !important;
    }
    
    /* Style untuk pagination */
    .pagination-container {
        margin-top: 1.5rem;
    }
    
    .pagination {
        margin-bottom: 0;
    }
    
    .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    
    .page-link {
        color: #0d6efd;
        padding: 0.375rem 0.75rem;
    }
    
    .page-link:hover {
        color: #0a58ca;
        background-color: #e9ecef;
        border-color: #dee2e6;
    }
    
    .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }
    
    /* Animasi hover baris tabel */
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }
    
    /* Responsivitas tombol pada layar kecil */
    @media (max-width: 576px) {
        .card-header {
            flex-direction: column;
            align-items: start !important;
        }
        
        .card-tools {
            margin-top: 1rem;
            width: 100%;
        }
        
        .card-tools .input-group {
            width: 100% !important;
        }
        
        /* Fix untuk tombol pada layar kecil */
        .btn-group {
            display: flex;
            width: 100%;
        }
        
        .btn-group .btn {
            flex: 1;
        }
    }
    
    /* Penyesuaian tampilan button group pada layar kecil */
    @media (max-width: 480px) {
        .btn-group {
            flex-direction: column;
        }
        
        .btn-group .btn {
            width: 100%;
            margin-top: 0.25rem;
            margin-bottom: 0.25rem;
            border-radius: 0.25rem !important;
        }
        
        .btn-group .btn:first-child {
            margin-top: 0;
        }
        
        .btn-group .btn:last-child {
            margin-bottom: 0;
        }
    }

    /* Style untuk search bar dan tombol */
    .search-container {
        position: relative;
        min-width: 200px;
        max-width: 250px;
    }

    .search-container .input-group-text {
        background-color: transparent;
        border-left: none;
    }

    .search-container .form-control {
        border-right: none;
        padding-right: 30px;
    }

    .search-container .form-control:focus {
        box-shadow: none;
        border-color: #ced4da;
    }

    .search-container .form-control:focus + .input-group-append .input-group-text {
        border-color: #ced4da;
    }

    .search-clear {
        position: absolute;
        right: 40px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        cursor: pointer;
        z-index: 10;
        display: none;
    }

    .search-clear:hover {
        color: #dc3545;
    }

    /* Button styling */
    .btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
        white-space: nowrap;
    }

    @media (max-width: 576px) {
        .search-container {
            min-width: 100%;
            max-width: 100%;
            margin-bottom: 0.5rem;
        }
        
        .d-flex.align-items-center.gap-2 {
            flex-direction: column-reverse;
            width: 100%;
        }
        
        .d-flex.align-items-center.gap-2 .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }
</style>
@endpush