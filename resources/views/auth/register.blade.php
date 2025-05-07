@extends('layouts.auth')
@section('title', 'Sipark | Register Sistem Parkir')

@section('content')
<div class="register-box">
    <div class="register-logo">
        <a href="{{ url('/') }}"><b>Sipark</b>Golden Hill</a>
    </div>
    <div class="card">
        <div class="card-body register-card-body">
            <p class="login-box-msg">Daftar Akun Sistem Parkir</p>

            <form action="{{ route('register') }}" method="post">
                @csrf
                <div class="input-group mb-3">
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                           placeholder="Full name" value="{{ old('name') }}" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                           placeholder="Email" value="{{ old('email') }}" required>
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
                <div class="input-group mb-3">
                    <input type="password" name="password_confirmation" class="form-control" 
                           placeholder="Confirm password" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-warning btn-block">Daftar</button>
                    </div>
                </div>
            </form>

            <p class="mb-0 mt-3 text-center">
                <a href="{{ route('login') }}" class="text-center text-warning"><b>Login</b>, Jika Sudah Punya Akun</a>
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
    
    .register-box {
        width: 360px;
        margin: 0 auto;
    }
    
    @media (max-width: 576px) {
        .register-box {
            width: 90%;
        }
    }
    
    .register-card-body {
        background-color: #fff;
        padding: 20px;
        border-radius: 0.25rem;
    }
    
    .register-logo a {
        color: #495057;
    }
</style>
@endpush