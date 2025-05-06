@extends('layouts.auth')

@section('content')
<div class="login-box">
    <div class="login-logo">
        <a href="{{ url('/') }}"><b>Sipark</b>Golden Hill</a>
    </div>
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Login Sistem Parkir</p>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="post">
                @csrf
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                           placeholder="Email" value="{{ old('email') }}" required autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Password" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-warning btn-block">Masuk</button>
                    </div>
                </div>
            </form>

            <p class="mb-0 mt-3 text-center">
                <a href="{{ route('register') }}" class="text-center text-warning"><b>Daftar Akun</b>, Jika Belum Punya Akun</a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-warning {
        color: #fff;
        background-color: #ffc107;
        border-color: #ffc107;
        box-shadow: none;
        font-weight: 600;
    }
    
    .btn-warning:hover {
        color: #fff;
        background-color: #e0a800;
        border-color: #d39e00;
    }
    
    .text-warning {
        color: #ffc107 !important;
    }
    
    .text-warning:hover {
        color: #e0a800 !important;
        text-decoration: underline;
    }
</style>
@endpush