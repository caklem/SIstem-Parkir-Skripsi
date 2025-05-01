/**
 * Deteksi Plat Nomor Sederhana di Browser
 * Versi minimal untuk keadaan darurat
 */

function detectPlateSimple() {
    console.log("Fungsi deteksi sederhana dipanggil");
    
    try {
        // Get elements
        const video = document.getElementById('camera-preview');
        if (!video) {
            throw new Error("Elemen video tidak ditemukan");
        }
        
        // Create canvas if not exists
        let canvas = document.getElementById('canvas-preview');
        if (!canvas) {
            canvas = document.createElement('canvas');
            canvas.id = 'canvas-preview';
            canvas.style.display = 'none';
            document.querySelector('.camera-container').appendChild(canvas);
        }
        
        // Set canvas size
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        
        // Draw video to canvas
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Display captured image
        let capturedImage = document.getElementById('captured-image');
        if (!capturedImage) {
            const capturedDiv = document.createElement('div');
            capturedDiv.className = 'captured-image';
            capturedDiv.style.display = 'none';
            capturedDiv.style.textAlign = 'center';
            
            capturedImage = document.createElement('img');
            capturedImage.id = 'captured-image';
            capturedImage.className = 'img-fluid';
            capturedImage.alt = 'Captured Image';
            
            capturedDiv.appendChild(capturedImage);
            document.querySelector('.camera-container').appendChild(capturedDiv);
        }
        
        capturedImage.src = canvas.toDataURL('image/jpeg', 0.9);
        $('.captured-image').show();
        
        // Set a dummy plate number (since this is the emergency version)
        $('#plat_nomor').val('B 1234 XYZ');
        $('#plat_nomor').addClass('highlight-for-edit');
        setTimeout(() => {
            $('#plat_nomor').removeClass('highlight-for-edit');
        }, 2000);
        
        // Hide camera modal
        $('#cameraModal').modal('hide');
        
        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Deteksi Berhasil',
            text: 'Plat nomor contoh telah ditetapkan. Anda dapat mengeditnya jika perlu.',
            confirmButtonColor: '#28a745'
        });
        
    } catch (error) {
        console.error("Error dalam deteksi sederhana:", error);
        
        // Show error message
        Swal.fire({
            icon: 'error',
            title: 'Error Deteksi',
            text: 'Terjadi kesalahan: ' + error.message,
            confirmButtonColor: '#dc3545'
        });
    }
}

// Register event handler
$(document).ready(function() {
    console.log("Simple detection script loaded");
    
    $(document).on('click', '#takePictureBrowser', function() {
        console.log("Simple detection button clicked");
        detectPlateSimple();
    });
});