@extends('layouts.main')

@section('title', 'Print QR Code')

@section('content')
<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card mt-4">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                {!! $qrcode !!}
                            </div>
                            <h4>{{ $nomorKartu }}</h4>
                            <button onclick="window.print()" class="btn btn-primary mt-3 no-print">
                                <i class="fas fa-print"></i> Cetak QR Code
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    @media print {
        .no-print { display: none !important; }
        .content-wrapper { 
            margin-left: 0 !important;
            padding: 0 !important;
        }
        .main-sidebar, .main-header { display: none !important; }
        svg { 
            max-width: 200px;
            height: auto;
            margin: 0 auto;
        }
    }
</style>
@endsection