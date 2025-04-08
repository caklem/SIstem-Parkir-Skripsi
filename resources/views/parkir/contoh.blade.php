@push('scripts')
<script>
let html5QrcodeScanner = null;

$(document).ready(function() {
    // Start Scanner
    $('#startButton').click(function() {
        if (html5QrcodeScanner === null) {
            $('#stopButton').show();
            $(this).hide();
            
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", 
                { 
                    fps: 10, 
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                }
            );
            
            html5QrcodeScanner.render((decodedText) => {
                // Cek status kartu melalui AJAX
                $.ajax({
                    url: '/parkir/check-card',
                    type: 'POST',
                    data: {
                        nomor_kartu: decodedText,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.is_used) {
                            // Kartu sedang digunakan
                            Swal.fire({
                                icon: 'error',
                                title: 'Kartu Sedang Digunakan',
                                text: 'Kartu ini masih digunakan dan belum keluar parkir',
                                confirmButtonColor: '#dc3545'
                            });
                        } else {
                            // Kartu valid dan bisa digunakan
                            $('#nomor_kartu').val(decodedText);
                            stopScanner();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Nomor kartu berhasil di-scan',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan saat memverifikasi kartu',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            });
        }
    });

    // Stop Scanner
    $('#stopButton').click(function() {
        stopScanner();
    });

    // Function untuk stop scanner
    function stopScanner() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear();
            html5QrcodeScanner = null;
            $('#stopButton').hide();
            $('#startButton').show();
        }
    }

    // Reset scanner saat modal ditutup
    $('#parkirModal').on('hidden.bs.modal', function() {
        stopScanner();
    });

    // Validasi input manual
    $('#nomor_kartu').on('input', function() {
        let value = this.value;
        // Validasi saat input manual
        if (value.length > 0) {
            $.ajax({
                url: '/parkir/check-card',
                type: 'POST',
                data: {
                    nomor_kartu: value,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.is_used) {
                        $('#nomor_kartu').addClass('is-invalid');
                        $('#nomor_kartu').next('.invalid-feedback').remove();
                        $('#nomor_kartu').after('<div class="invalid-feedback">Kartu ini masih digunakan dan belum keluar parkir</div>');
                    } else {
                        $('#nomor_kartu').removeClass('is-invalid');
                        $('#nomor_kartu').next('.invalid-feedback').remove();
                    }
                }
            });
        }
    });
});
</script>
@endpush