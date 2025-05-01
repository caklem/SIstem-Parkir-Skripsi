/**
 * Deteksi Plat Nomor di Browser
 * Menggunakan Tesseract.js
 */

// Fungsi untuk mendeteksi plat nomor di browser
async function detectPlateInBrowser() {
    console.log("Fungsi detectPlateInBrowser dipanggil");

    // Periksa apakah Tesseract tersedia
    if (typeof Tesseract === "undefined") {
        console.error("Tesseract.js tidak tersedia");
        Swal.fire({
            icon: "error",
            title: "Library Tidak Tersedia",
            text: "Library Tesseract.js tidak dimuat dengan benar. Coba refresh halaman.",
            confirmButtonColor: "#dc3545",
        });
        return;
    }

    // Dapatkan elemen yang diperlukan
    const video = document.getElementById("camera-preview");
    const canvas = document.getElementById("canvas-preview");
    const capturedImage = document.getElementById("captured-image");

    if (!video) {
        console.error("Elemen video tidak ditemukan");
        Swal.fire({
            icon: "error",
            title: "Elemen Tidak Ditemukan",
            text: "Elemen video tidak ditemukan. Coba refresh halaman dan gunakan browser Chrome terbaru.",
            confirmButtonColor: "#dc3545",
        });
        return;
    }

    if (!canvas) {
        console.error("Elemen canvas tidak ditemukan");
        // Buat canvas jika tidak ada
        const newCanvas = document.createElement("canvas");
        newCanvas.id = "canvas-preview";
        newCanvas.style.display = "none";
        video.parentNode.insertBefore(newCanvas, video.nextSibling);
        canvas = newCanvas;
    }

    if (!capturedImage) {
        console.error("Elemen captured-image tidak ditemukan");
        // Buat element image jika tidak ada
        const capturedDiv = document.createElement("div");
        capturedDiv.className = "captured-image";
        capturedDiv.style.display = "none";
        capturedDiv.style.textAlign = "center";

        const newImg = document.createElement("img");
        newImg.id = "captured-image";
        newImg.className = "img-fluid";
        newImg.alt = "Captured Image";

        capturedDiv.appendChild(newImg);
        video.parentNode.insertBefore(capturedDiv, video.nextSibling);
        capturedImage = newImg;
    }

    // Tampilkan loading
    $("#loading-indicator").show();
    if ($("#browser-processing-message").length) {
        $("#browser-processing-message")
            .removeClass("d-none")
            .text("Memproses gambar...");
    }

    try {
        // Set variabel global
        window.ocrInProgress = true;

        // Set dimensi canvas
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;

        // Gambar video ke canvas
        const context = canvas.getContext("2d");
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Tampilkan gambar yang ditangkap
        capturedImage.src = canvas.toDataURL("image/jpeg", 0.9);
        $(".captured-image").show();

        // Pre-process gambar untuk hasil OCR yang lebih baik
        const enhancedCanvas = enhancePlateImage(canvas);

        console.log("Mulai proses OCR dengan Tesseract.js");
        if ($("#browser-processing-message").length) {
            $("#browser-processing-message").text("Mengenali plat nomor...");
        }

        // Proses dengan Tesseract.js
        const result = await Tesseract.recognize(
            enhancedCanvas.toDataURL("image/jpeg"),
            "eng",
            {
                logger: (m) => {
                    if (
                        m.status === "recognizing text" &&
                        $("#browser-processing-message").length
                    ) {
                        $("#browser-processing-message").text(
                            `Mengenali teks: ${Math.floor(m.progress * 100)}%`
                        );
                    }
                    console.log("OCR progress:", m.status || m);
                },
                tessedit_char_whitelist:
                    "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 ",
                tessedit_pageseg_mode: "7", // Treat as single text line
            }
        );

        console.log("Hasil OCR:", result);

        // Ambil teks hasil OCR
        const rawText = result.data.text.trim().toUpperCase();
        console.log("Raw text result:", rawText);

        // Terapkan perbaikan hasil OCR
        const plateText = improveOCRResult(rawText);
        console.log("Improved plate text:", plateText);

        // Isi form dengan hasil OCR
        $("#plat_nomor").val(plateText);
        $("#plat_nomor").addClass("highlight-for-edit");
        setTimeout(() => {
            $("#plat_nomor").removeClass("highlight-for-edit");
        }, 2000);

        // Sembunyikan loading indicator
        $("#loading-indicator").hide();
        if ($("#browser-processing-message").length) {
            $("#browser-processing-message").addClass("d-none");
        }
        window.ocrInProgress = false;

        // Tampilkan pesan sukses
        Swal.fire({
            icon: "success",
            title: "Deteksi Berhasil",
            html: `
                <p>Plat nomor terdeteksi:</p>
                <h4 class="mb-0">${plateText}</h4>
                <p class="text-muted mt-1">Confidence: ${Math.round(
                    result.data.confidence
                )}%</p>
                <p class="text-info small">Metode: Browser (Tesseract.js)</p>
            `,
            confirmButtonColor: "#28a745",
        });

        // Tutup modal kamera
        $("#cameraModal").modal("hide");
    } catch (error) {
        console.error("Error dalam deteksi browser:", error);
        $("#loading-indicator").hide();
        if ($("#browser-processing-message").length) {
            $("#browser-processing-message").addClass("d-none");
        }
        window.ocrInProgress = false;

        // Tampilkan pesan error
        Swal.fire({
            icon: "error",
            title: "Error Deteksi",
            text: "Terjadi kesalahan saat deteksi: " + error.message,
            confirmButtonColor: "#dc3545",
        });
    }
}

// Fungsi untuk meningkatkan kualitas gambar
function enhancePlateImage(canvas) {
    try {
        // Buat canvas baru untuk hasil yang diproses
        const processedCanvas = document.createElement("canvas");
        processedCanvas.width = canvas.width;
        processedCanvas.height = canvas.height;
        const ctx = processedCanvas.getContext("2d");

        // Copy gambar dari canvas sumber
        ctx.drawImage(canvas, 0, 0);

        // Ambil data gambar
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const data = imageData.data;

        // Konversi ke grayscale
        for (let i = 0; i < data.length; i += 4) {
            const gray =
                0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
            data[i] = data[i + 1] = data[i + 2] = gray;
        }

        // Tingkatkan kontras
        const contrast = 1.5;
        const factor = (259 * (contrast + 255)) / (255 * (259 - contrast));

        for (let i = 0; i < data.length; i += 4) {
            data[i] = factor * (data[i] - 128) + 128;
            data[i + 1] = factor * (data[i + 1] - 128) + 128;
            data[i + 2] = factor * (data[i + 2] - 128) + 128;
        }

        // Binarisasi (threshold)
        for (let i = 0; i < data.length; i += 4) {
            const val = data[i] > 140 ? 255 : 0;
            data[i] = data[i + 1] = data[i + 2] = val;
        }

        // Update gambar
        ctx.putImageData(imageData, 0, 0);

        return processedCanvas;
    } catch (error) {
        console.error("Error dalam enhancePlateImage:", error);
        return canvas; // Return original canvas if error
    }
}

// Fungsi untuk memproses dan memperbaiki hasil OCR
function improveOCRResult(text) {
    try {
        // Bersihkan teks
        let plate = text.toUpperCase().trim();
        plate = plate.replace(/\s+/g, " ");

        // Deteksi jika ini adalah pola plat nomor (huruf-angka-huruf)
        const platePattern = /([A-Z]{1,2})\s*([0-9]{1,4})\s*([A-Z]{1,3})/;
        const match = plate.match(platePattern);

        if (match) {
            // Jika sesuai pola plat, koreksi setiap bagian dengan tepat
            let region = match[1]; // Huruf daerah (contoh: B, D, AB)
            let numbers = match[2]; // Bagian angka (contoh: 1234)
            let letters = match[3]; // Bagian huruf (contoh: ABC)

            // Pada bagian angka, ganti huruf yang mungkin salah baca
            numbers = numbers.replace(/O/g, "0");
            numbers = numbers.replace(/I/g, "1");
            numbers = numbers.replace(/S/g, "5");
            numbers = numbers.replace(/Z/g, "2");
            numbers = numbers.replace(/E/g, "3");
            numbers = numbers.replace(/A/g, "4");
            numbers = numbers.replace(/G/g, "6");
            numbers = numbers.replace(/T/g, "7");
            numbers = numbers.replace(/B/g, "8");
            numbers = numbers.replace(/P/g, "9");
            

            // Pada bagian huruf, ganti angka yang mungkin salah baca
            letters = letters.replace(/0/g, "O");
            letters = letters.replace(/1/g, "I");
            letters = letters.replace(/5/g, "S");
            letters = letters.replace(/2/g, "Z");
            letters = letters.replace(/3/g, "E");
            letters = letters.replace(/4/g, "A");
            letters = letters.replace(/6/g, "G");
            letters = letters.replace(/7/g, "T");
            letters = letters.replace(/8/g, "B");
            letters = letters.replace(/9/g, "P");



            // Buat hasil yang terkoreksi dengan format standar
            return `${region} ${numbers} ${letters}`;
        }

        // Jika tidak sesuai pola, kembalikan teks asli yang sudah dibersihkan
        return plate;
    } catch (error) {
        console.error("Error dalam improveOCRResult:", error);
        return text; // Return original text if error
    }
}

// Inisialisasi saat dokumen siap
$(document).ready(function () {
    console.log("plate-detection-browser.js loaded");

    // Event listener untuk tombol deteksi browser
    $(document).on("click", "#takePictureBrowser", function () {
        console.log("Browser detection button clicked via document.on");
        detectPlateInBrowser();
    });
});
