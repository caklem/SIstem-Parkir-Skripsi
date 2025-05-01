/**
 * Preload Tesseract worker untuk mempercepat deteksi saat dibutuhkan
 */

// Variabel untuk menyimpan worker
let preloadedWorker = null;

// Preload Tesseract worker
async function preloadTesseractWorker() {
    try {
        console.log('Memulai preload Tesseract worker...');
        
        if (typeof Tesseract === 'undefined') {
            console.log('Tesseract belum dimuat, menunggu...');
            setTimeout(preloadTesseractWorker, 1000);
            return;
        }
        
        console.log('Inisialisasi Tesseract worker...');
        preloadedWorker = await Tesseract.createWorker('eng');
        
        console.log('Loading language data...');
        await preloadedWorker.load();
        await preloadedWorker.loadLanguage('eng');
        await preloadedWorker.initialize('eng');
        
        console.log('Mengatur parameter optimal untuk plat...');
        await preloadedWorker.setParameters({
            tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 ',
            tessedit_pageseg_mode: '7' // Single text line
        });
        
        console.log('Tesseract worker berhasil di-preload');
        
        // Tambahkan ke window untuk bisa diakses dari mana saja
        window.preloadedTesseractWorker = preloadedWorker;
        
    } catch (error) {
        console.error('Error saat preload Tesseract worker:', error);
    }
}

// Mulai preload model saat halaman dimuat
$(document).ready(function() {
    // Tunggu semua resource dimuat
    $(window).on('load', function() {
        setTimeout(preloadTesseractWorker, 2000);
    });
});