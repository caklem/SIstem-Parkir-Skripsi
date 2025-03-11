@extends('layouts.main')

@section('title', 'Edit Data Parkir')

@section('content')
    <h1>Edit Data Parkir</h1>
    <form action="{{ route('parkir.update', $parkir->id) }}" method="POST">
        @csrf
        @method('PUT')
        <label>Plat Nomor:</label>
        <input type="text" name="plat_nomor" value="{{ $parkir->plat_nomor }}" required>
        <label>Jenis Kendaraan:</label>
        <input type="text" name="jenis_kendaraan" value="{{ $parkir->jenis_kendaraan }}" required>
        <button type="submit" class="btn btn-primary">Perbarui</button>
    </form>
@endsection
