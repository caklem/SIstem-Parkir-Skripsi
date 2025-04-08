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
    <!-- Judul -->
    {{-- <h5 class="text-warning font-weight-bold mb-0">Dashboard Parkir Keluar</h5>     --}}
</div>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Daftar QR Code</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor Kartu</th>
                                <th>Tanggal Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($qrcodes as $index => $qr)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $qr->nomor_kartu }}</td>
                                    <td>{{ $qr->created_at->format('d-m-Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('qrcode.print', ['nomorKartu' => $qr->nomor_kartu])}}"
                                            class="btn btn-sm btn-info">
                                            <i class="fas fa-print"></i> Cetak Ulang
                                        </a>
                                        <form action="{{ route('qrcode.delete', $qr->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus QR Code ini?')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada data QR Code</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    {{ $qrcodes->links() }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection