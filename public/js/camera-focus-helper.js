/**
 * Helper untuk mengoptimalkan fokus kamera pada plat nomor
 */

// Fungsi untuk mengatur setting kamera optimal untuk plat nomor
function optimizeCameraForPlate() {
    const video = document.getElementById('camera-preview');
    
    if (!video) return;
    
    // Cek apakah ada akses ke pengaturan lebih detail
    if (video.srcObject && video.srcObject.getVideoTracks().length > 0) {
        const track = video.srcObject.getVideoTracks()[0];
        
        try {
            const capabilities = track.getCapabilities();
            const settings = track.getSettings();
            
            // Atur constraints berdasarkan kemampuan kamera
            const constraints = {};
            
            // Fokus manual jika didukung
            if (capabilities.focusMode && capabilities.focusMode.includes('manual')) {
                constraints.focusMode = 'manual';
                
                if (capabilities.focusDistance) {
                    // Fokus ke jarak 50cm
                    constraints.focusDistance = Math.min(
                        Math.max(0.5, capabilities.focusDistance.min),
                        capabilities.focusDistance.max
                    );
                }
            } else if (capabilities.focusMode && capabilities.focusMode.includes('continuous')) {
                constraints.focusMode = 'continuous';
            }
            
            // Set brightness jika didukung
            if (capabilities.brightness) {
                constraints.brightness = Math.min(
                    Math.max(capabilities.brightness.min + 
                            (capabilities.brightness.max - capabilities.brightness.min) * 0.6,
                            capabilities.brightness.min),
                    capabilities.brightness.max
                );
            }
            
            // Set contrast jika didukung
            if (capabilities.contrast) {
                constraints.contrast = Math.min(
                    Math.max(capabilities.contrast.min + 
                            (capabilities.contrast.max - capabilities.contrast.min) * 0.7,
                            capabilities.contrast.min),
                    capabilities.contrast.max
                );
            }
            
            // Terapkan pengaturan
            if (Object.keys(constraints).length > 0) {
                track.applyConstraints(constraints)
                    .then(() => console.log('Kamera dioptimalkan untuk plat nomor'))
                    .catch(err => console.log('Gagal mengoptimalkan kamera:', err));
            }
        } catch (error) {
            console.log('Error saat mengoptimalkan kamera:', error);
        }
    }
}

// Panggil saat kamera diinisialisasi
$(document).ready(function() {
    $('#cameraModal').on('shown.bs.modal', function() {
        // Tunggu sebentar agar kamera siap
        setTimeout(optimizeCameraForPlate, 1000);
    });
});