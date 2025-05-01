/**
 * Validasi Input Plat Nomor
 * Memastikan input plat nomor selalu dalam format yang benar
 */

$(document).ready(function() {
    // Setup awal field plat nomor
    setupPlateField();
    makePlateFieldLikeVehicleType();
    
    // Panggil lagi jika modal dibuka
    $(document).on('shown.bs.modal', '.modal', function() {
        setupPlateField();
        setTimeout(makePlateFieldLikeVehicleType, 100);
    });
    
    // Dan setelah AJAX selesai
    $(document).ajaxComplete(function() {
        makePlateFieldLikeVehicleType();
    });
    
    function setupPlateField() {
        // Tambahkan maksimal panjang input pada field plat nomor
        $("#plat_nomor").attr("maxlength", 12); // 9 karakter + 2 spasi + buffer
        
        // Tambahkan placeholder dan class styling
        $("#plat_nomor").attr("placeholder", "B 1234 ABC");
        $("#plat_nomor").addClass("plate-input");
        
        // Tambahkan tooltip bantuan
        // $("#plat_nomor").attr("title", "Format: 1-2 huruf, 1-4 angka, 1-3 huruf");
        
        // Hapus panduan jika sudah ada (untuk mencegah duplikasi)
        $(".plate-format-guide").remove();
        
        // Tambahkan panduan format jika belum ada
        if ($(".plate-format-guide").length === 0) {
            // $("#plat_nomor").after('<div class="plate-format-guide text-muted small mt-1">Format: 1-2 huruf, 1-4 angka, 1-3 huruf</div>');
        }
    }
    
    // Tambahkan validasi saat input berubah
    $(document).on("input", "#plat_nomor", function() {
        // Ambil nilai input saat ini
        let input = $(this).val();
        
        // Filter karakter: hanya izinkan huruf, angka, dan spasi
        input = input.replace(/[^A-Za-z0-9\s]/g, "");
        
        // Konversi ke huruf besar
        input = input.toUpperCase();
        
        // Standarisasi spasi
        input = input.replace(/\s+/g, " ").trim();
        
        // Update nilai input
        $(this).val(input);
    });
    
    // Validasi format saat fokus hilang dari field
    $(document).on("blur", "#plat_nomor", function() {
        const input = $(this).val();
        
        // Jika input tidak kosong, format dengan sanitizeLicensePlate
        if (input.trim() !== "") {
            try {
                const sanitized = window.sanitizeLicensePlate ? 
                    window.sanitizeLicensePlate(input) : input.toUpperCase().trim();
                $(this).val(sanitized);
            } catch (e) {
                console.error("Error saat sanitasi input:", e);
            }
        }
    });
    
    // Tambahkan validasi pada form submit
    $(document).on("submit", "form", function() {
        const plateInput = $("#plat_nomor").val();
        
        // Validasi format plat nomor saat submit
        if (plateInput.trim() !== "") {
            try {
                const sanitized = window.sanitizeLicensePlate ? 
                    window.sanitizeLicensePlate(plateInput) : plateInput.toUpperCase().trim();
                $("#plat_nomor").val(sanitized);
            } catch (e) {
                console.error("Error saat sanitasi input pada submit:", e);
            }
        }
    });
});

// Tambahkan fungsi untuk menyesuaikan field plat nomor
function makePlateFieldLikeVehicleType() {
    // Pastikan kita tidak menduplikasi perubahan
    if ($('#plat_nomor').parent().hasClass('input-group')) {
        return;
    }
    
    // Ambil field plat nomor
    const plateField = $('#plat_nomor');
    if (plateField.length === 0) return;
    
    // Bungkus dengan input-group seperti jenis kendaraan
    plateField.wrap('<div class="input-group"></div>');
    
    // Tambahkan prepend ikon
    plateField.before(
        '<div class="input-group-prepend">' +
        '  <span class="input-group-text">' +
        '    <i class="fas fa-car-alt"></i>' + // Ikon mobil, sesuaikan dengan kebutuhan
        '  </span>' +
        '</div>'
    );
    
    // Tambahkan class untuk styling
    plateField.addClass('form-control plate-input');
    
    // Hapus background-image jika menggunakan input-group
    plateField.css('background-image', 'none');
    plateField.css('padding-left', '0.75rem');
}