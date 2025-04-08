@extends('layouts.main')

@section('title', 'Generate QR Code')

@section('content')
<!-- Tombol Sidebar -->
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
                    <h1>Generate QR Code</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('qrcode.generate') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="nomorKartu">Nomor Kartu Parkir</label>
                            <input type="text" 
                                   class="form-control @error('nomorKartu') is-invalid @enderror" 
                                   id="nomorKartu" 
                                   name="nomorKartu" 
                                   value="{{ old('nomorKartu') }}"
                                   placeholder="Masukkan nomor kartu (huruf dan angka)">
                        </div>
                        <button type="submit" class="btn btn-primary">Generate QR Code</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection