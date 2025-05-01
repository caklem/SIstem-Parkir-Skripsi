/**
 * Script untuk membuat elemen kamera secara dinamis jika tidak ditemukan
 */

function ensureCameraElements() {
    console.log("Checking camera elements...");
    
    // Periksa apakah modal kamera sudah ada
    if (!document.getElementById('cameraModal')) {
        console.log("Creating camera modal...");
        
        // Buat modal kamera
        const modalHTML = `
        <div class="modal fade" id="cameraModal" tabindex="-1" aria-labelledby="cameraModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cameraModalLabel">Deteksi Plat Nomor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="camera-container">
                            <video id="camera-preview" autoplay playsinline></video>
                            <canvas id="canvas-preview" style="display: none;"></canvas>
                            <div class="captured-image" style="display: none; text-align: center;">
                                <img id="captured-image" class="img-fluid" alt="Captured Image">
                            </div>
                        </div>
                        
                        <div id="loading-indicator" style="display: none; text-align: center; margin-top: 10px;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p id="opencv-processing-message" class="mt-2 d-none">Sedang memproses di server...</p>
                            <p id="browser-processing-message" class="mt-2 d-none">Sedang memproses di browser...</p>
                        </div>
                        
                        <div class="mt-3 d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-primary btn-sm" id="takePictureStandard">
                                <i class="fas fa-camera"></i> Ambil Foto (Server)
                            </button>
                            
                            <button type="button" class="btn btn-success btn-sm" id="takePictureBrowser">
                                <i class="fas fa-camera"></i> Deteksi di Browser
                            </button>
                            
                            <button type="button" class="btn btn-danger btn-sm" id="cancelCamera">
                                <i class="fas fa-times"></i> Tutup Kamera
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        `;
        
        // Tambahkan modal ke body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    } else {
        console.log("Camera modal already exists");
        
        // Periksa dan buat elemen di dalam modal
        const modalBody = document.querySelector('#cameraModal .modal-body');
        
        if (!modalBody) {
            console.error("Modal body not found");
            return;
        }
        
        // Periksa container kamera
        if (!document.querySelector('.camera-container')) {
            const containerDiv = document.createElement('div');
            containerDiv.className = 'camera-container';
            modalBody.prepend(containerDiv);
        }
        
        // Periksa video
        if (!document.getElementById('camera-preview')) {
            const video = document.createElement('video');
            video.id = 'camera-preview';
            video.autoplay = true;
            video.playsinline = true;
            document.querySelector('.camera-container').appendChild(video);
        }
        
        // Periksa canvas
        if (!document.getElementById('canvas-preview')) {
            const canvas = document.createElement('canvas');
            canvas.id = 'canvas-preview';
            canvas.style.display = 'none';
            document.querySelector('.camera-container').appendChild(canvas);
        }
        
        // Periksa captured-image
        if (!document.querySelector('.captured-image')) {
            const capturedDiv = document.createElement('div');
            capturedDiv.className = 'captured-image';
            capturedDiv.style.display = 'none';
            capturedDiv.style.textAlign = 'center';
            
            const capturedImg = document.createElement('img');
            capturedImg.id = 'captured-image';
            capturedImg.className = 'img-fluid';
            capturedImg.alt = 'Captured Image';
            
            capturedDiv.appendChild(capturedImg);
            document.querySelector('.camera-container').appendChild(capturedDiv);
        }
        
        // Periksa loading indicator
        if (!document.getElementById('loading-indicator')) {
            const loadingHTML = `
            <div id="loading-indicator" style="display: none; text-align: center; margin-top: 10px;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p id="opencv-processing-message" class="mt-2 d-none">Sedang memproses di server...</p>
                <p id="browser-processing-message" class="mt-2 d-none">Sedang memproses di browser...</p>
            </div>
            `;
            document.querySelector('.camera-container').insertAdjacentHTML('afterend', loadingHTML);
        }
    }
    
    console.log("Camera elements check complete");
}

// Panggil fungsi saat dokumen dimuat
$(document).ready(function() {
    ensureCameraElements();
    
    // Cek elemen setiap kali modal ditampilkan
    $('#cameraModal').on('show.bs.modal', function() {
        ensureCameraElements();
    });
});