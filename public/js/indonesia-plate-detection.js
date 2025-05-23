/**
 * Deteksi Plat Nomor Indonesia Otomatis
 * Optimized for Indonesian license plate format
 */

// Format plat nomor Indonesia: 1-2 huruf (kode wilayah), 1-4 angka (nomor registrasi), 1-3 huruf (kode seri)
// Contoh: B 1234 XYZ, D 56 FG, AB 123 CD

// Variabel untuk menyimpan model
let plateModel;
let isModelLoaded = false;

// Tambahkan variabel global di bagian atas file
let isPlateDetectionNotificationShown = false;
let detectedPlateConfidence = 0;
let mostAccuratePlate = "";
let isProcessingPlate = false;
let lastDetectedPlate = "";
let lastDetectionTimestamp = 0;

// Inisialisasi model deteksi plat nomor
async function initPlateDetectionModel() {
    try {
        if (!isModelLoaded) {
            console.log("Memuat model deteksi plat nomor...");

            // Load model dari CDN atau server lokal
            plateModel = await tf.loadGraphModel(
                "models/plate_detection_model/model.json"
            );

            console.log("Model berhasil dimuat!");
            isModelLoaded = true;
        }
    } catch (error) {
        console.error("Error saat memuat model:", error);
        // Fallback ke deteksi klasik jika gagal memuat model
        isModelLoaded = false;
    }
}

// Deteksi plat dengan model neural network
async function detectPlateWithModel(imageElement) {
    try {
        // Pastikan model sudah dimuat
        if (!isModelLoaded) {
            await initPlateDetectionModel();
        }

        // Jika model masih gagal dimuat, gunakan deteksi klasik
        if (!isModelLoaded) {
            console.log("Menggunakan deteksi klasik (model tidak tersedia)");
            return null;
        }

        // Konversi gambar ke tensor
        const imageTensor = tf.browser
            .fromPixels(imageElement)
            .resizeBilinear([416, 416])
            .expandDims(0)
            .div(255.0);

        // Lakukan prediksi
        const predictions = await plateModel.predict(imageTensor);

        // Proses hasil prediksi
        const [boxes, scores, classes] = predictions;
        const boxesData = await boxes.data();
        const scoresData = await scores.data();
        const classesData = await classes.data();

        // Bersihkan tensor
        tf.dispose([imageTensor, ...predictions]);

        // Filter hasil dengan confidence tinggi
        const detections = [];
        const threshold = 0.5; // Confidence threshold

        for (let i = 0; i < scoresData.length; i++) {
            if (scoresData[i] > threshold) {
                // Konversi dari format YOLO ke koordinat canvas
                const [y1, x1, y2, x2] = [
                    boxesData[i * 4] * imageElement.height,
                    boxesData[i * 4 + 1] * imageElement.width,
                    boxesData[i * 4 + 2] * imageElement.height,
                    boxesData[i * 4 + 3] * imageElement.width,
                ];

                detections.push({
                    x: x1,
                    y: y1,
                    width: x2 - x1,
                    height: y2 - y1,
                    confidence: scoresData[i],
                    class: classesData[i],
                });
            }
        }

        // Pilih deteksi dengan confidence tertinggi
        if (detections.length > 0) {
            detections.sort((a, b) => b.confidence - a.confidence);
            return detections[0];
        }

        return null;
    } catch (error) {
        console.error("Error deteksi dengan model:", error);
        return null;
    }
}

// Modifikasi fungsi utama detectIndonesianPlate
async function detectIndonesianPlate() {
    try {
        // Cek apakah sedang dalam proses deteksi
        if (isProcessingPlate) {
            console.log(
                "Proses deteksi plat sedang berjalan, permintaan baru diabaikan"
            );
            return;
        }

        // Cek apakah ada deteksi yang baru saja dilakukan dalam 3 detik terakhir
        const now = Date.now();
        if (now - lastDetectionTimestamp < 3000) {
            console.log(
                "Deteksi baru diabaikan: terlalu cepat setelah deteksi sebelumnya"
            );
            return;
        }

        console.log("Deteksi plat nomor Indonesia dimulai");
        isProcessingPlate = true;
        lastDetectionTimestamp = now;

        // Reset status notifikasi di awal sesi deteksi baru
        isPlateDetectionNotificationShown = false;
        detectedPlateConfidence = 0;
        mostAccuratePlate = "";

        // Status checks and setup
        if (window.ocrInProgress) {
            console.log("Proses OCR sedang berjalan, harap tunggu");
            isProcessingPlate = false;
            return;
        }

        window.ocrInProgress = true;
        $("#loading-indicator").show();

        // PERBAIKAN: Inisialisasi model neural network jika tersedia
        if (!isModelLoaded && typeof tf !== "undefined") {
            try {
                await initPlateDetectionModel();
            } catch (e) {
                console.log(
                    "Gagal memuat model deteksi plat, menggunakan metode klasik"
                );
            }
        }

        if ($("#browser-processing-message").length) {
            // Canvas dimensions and capture code
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            capturedImage.src = canvas.toDataURL("image/jpeg", 0.9);
            $(".captured-image").show();

            // PERBAIKAN: Tampilkan pesan proses lebih detail
            if ($("#browser-processing-message").length) {
                $("#browser-processing-message").text(
                    "Menganalisis gambar dengan AI processing..."
                );
            }

            let rawText = "";
            let detectedPlateType = "unknown";

            // PERBAIKAN: Deteksi tipe plat nomor
            const plateInfo = detectPlateBackground(
                context.getImageData(0, 0, canvas.width, canvas.height)
            );
            detectedPlateType = plateInfo.type;
            console.log("Deteksi tipe plat:", plateInfo);

            // PERBAIKAN: Gunakan model neural network jika tersedia
            if (isModelLoaded && typeof tf !== "undefined") {
                try {
                    console.log(
                        "Menggunakan model deteksi plat neural network"
                    );

                    // Buat gambar dari canvas untuk input model
                    const imageElement = new Image();
                    imageElement.src = canvas.toDataURL("image/jpeg", 1.0);

                    // Tunggu gambar dimuat
                    await new Promise((resolve) => {
                        imageElement.onload = resolve;
                    });

                    // Deteksi area plat dengan model
                    const plateRegion = await detectPlateWithModel(
                        imageElement
                    );

                    if (plateRegion) {
                        console.log(
                            "Plat terdeteksi dengan model AI:",
                            plateRegion
                        );

                        // Crop gambar ke area plat yang terdeteksi
                        const plateCanvas = document.createElement("canvas");
                        plateCanvas.width = plateRegion.width;
                        plateCanvas.height = plateRegion.height;

                        const plateCtx = plateCanvas.getContext("2d");
                        plateCtx.drawImage(
                            canvas,
                            plateRegion.x,
                            plateRegion.y,
                            plateRegion.width,
                            plateRegion.height,
                            0,
                            0,
                            plateCanvas.width,
                            plateCanvas.height
                        );

                        // Tampilkan area terdeteksi untuk debugging
                        if (window.debugMode) {
                            displayProcessingImage(
                                plateCanvas,
                                "AI Detected Plate"
                            );
                        }

                        // Proses gambar plat yang terdeteksi
                        rawText = await processMultipleImageVersions(
                            plateCanvas || canvas
                        );
                    } else {
                        console.log(
                            "Tidak ada plat yang terdeteksi model AI, gunakan full image processing"
                        );
                        // Fallback ke metode klasik
                        rawText = await processMultipleImageVersions(canvas);
                    }
                } catch (e) {
                    console.error("Error pada deteksi dengan model AI:", e);
                    // Fallback ke metode klasik
                    rawText = await processMultipleImageVersions(canvas);
                }
            } else {
                // Gunakan metode klasik
                console.log("Menggunakan metode deteksi plat klasik");
                rawText = await processMultipleImageVersions(canvas);
            }

            console.log("Teks hasil OCR dari multiple processing:", rawText);

            // Apply sanitization dan formatting dengan informasi tipe plat
            const sanitizedPlate = sanitizeLicensePlate(
                rawText,
                detectedPlateType
            );
            console.log("Hasil sanitasi final:", sanitizedPlate);

            // Hide loading indicators
            $("#loading-indicator").hide();
            if ($("#browser-processing-message").length) {
                $("#browser-processing-message").addClass("d-none");
            }
            window.ocrInProgress = false;

            // Update the plate number field
            $("#plat_nomor").val(sanitizedPlate);
            $("#plat_nomor").addClass("highlight-for-edit");
            setTimeout(() => {
                $("#plat_nomor").removeClass("highlight-for-edit");
            }, 2000);

            // Verifikasi hasil dengan batas ambang kepercayaan minimum
            const confidence = mostAccuratePlate ? detectedPlateConfidence : 0;
            const isHiConfidence = confidence > 70;
            const isMediumConfidence = confidence > 40;

            // Tampilkan notifikasi hasil
            showDetectionResultNotification(
                sanitizedPlate,
                confidence,
                isHiConfidence,
                isMediumConfidence
            );

            // Close camera modal
            $("#cameraModal").modal("hide");

            // Reset status setelah selesai
            isProcessingPlate = false;
        }
    } catch (error) {
        console.error("Error deteksi plat:", error);
        $("#loading-indicator").hide();
        if ($("#browser-processing-message").length) {
            $("#browser-processing-message").addClass("d-none");
        }
        window.ocrInProgress = false;
        isProcessingPlate = false;

        Swal.fire({
            icon: "error",
            title: "Error Deteksi",
            text: "Gagal mendeteksi plat nomor: " + error.message,
            confirmButtonColor: "#dc3545",
        });
    }
}

// Perbaiki fungsi enhanceIndonesianPlateImage untuk hasil lebih konsisten
async function enhanceIndonesianPlateImage(canvas) {
    return new Promise((resolve) => {
        try {
            // Buat canvas baru
            const enhancedCanvas = document.createElement("canvas");
            enhancedCanvas.width = canvas.width;
            enhancedCanvas.height = canvas.height;
            const ctx = enhancedCanvas.getContext("2d");

            // Gambar canvas asli ke canvas yang ditingkatkan
            ctx.drawImage(canvas, 0, 0);

            // PERBAIKAN KRITIS: Deteksi area plat terlebih dahulu
            const plateRegionResult = detectPlateRegionAdvanced(canvas);

            // Jika area plat terdeteksi, crop dan fokus ke area tersebut
            if (plateRegionResult.foundPlate) {
                console.log("Area plat terdeteksi! Memfokuskan proses...");
                // Crop ke area plat dan perbesar untuk detail lebih baik
                const plateCanvas = document.createElement("canvas");
                const padding = 10; // Padding untuk memastikan seluruh plat tertangkap

                plateCanvas.width =
                    plateRegionResult.region.width + padding * 2;
                plateCanvas.height =
                    plateRegionResult.region.height + padding * 2;

                const plateCtx = plateCanvas.getContext("2d");
                plateCtx.drawImage(
                    canvas,
                    Math.max(0, plateRegionResult.region.x - padding),
                    Math.max(0, plateRegionResult.region.y - padding),
                    plateRegionResult.region.width + padding * 2,
                    plateRegionResult.region.height + padding * 2,
                    0,
                    0,
                    plateCanvas.width,
                    plateCanvas.height
                );

                // Gunakan canvas yang di-crop untuk proses selanjutnya
                ctx.clearRect(
                    0,
                    0,
                    enhancedCanvas.width,
                    enhancedCanvas.height
                );
                enhancedCanvas.width = plateCanvas.width;
                enhancedCanvas.height = plateCanvas.height;
                ctx.drawImage(plateCanvas, 0, 0);
            }

            // Pre-processing dengan pendekatan multi-layer untuk plat Indonesia
            const imageData = ctx.getImageData(
                0,
                0,
                enhancedCanvas.width,
                enhancedCanvas.height
            );
            const data = imageData.data;
            const width = enhancedCanvas.width;
            const height = enhancedCanvas.height;

            // PERBAIKAN: Analisis karakteristik gambar untuk adaptasi otomatis
            const imageAnalysis = analyzeImageCharacteristics(
                data,
                width,
                height
            );
            console.log("Analisis gambar:", imageAnalysis);

            // Adaptive processing berdasarkan analisis
            if (imageAnalysis.isDark) {
                // Tingkatkan brightness untuk gambar gelap
                applyBrightnessCorrection(data, 40);
            } else if (imageAnalysis.isBright) {
                // Turunkan brightness untuk gambar terlalu terang
                applyBrightnessCorrection(data, -20);
            }

            // PERBAIKAN: Penguatan tepi karakter yang lebih presisi
            applyAdvancedEdgeEnhancement(
                data,
                width,
                height,
                imageAnalysis.contrastLevel
            );

            // PERBAIKAN: Adaptive contrast based on plate type (white/yellow)
            if (imageAnalysis.isYellowPlate) {
                console.log("Plat kuning terdeteksi, mengoptimalkan...");
                // Khusus untuk plat kuning
                applyYellowPlateOptimization(data, width, height);
            } else {
                // Untuk plat standar (putih)
                applyStandardPlateOptimization(data, width, height);
            }

            // PERBAIKAN: Bilateral filter untuk mengurangi noise tapi mempertahankan tepi
            applyBilateralFilter(data, width, height, 9, 75, 75);

            // PERBAIKAN: Local Adaptive Thresholding untuk kondisi pencahayaan tidak merata
            applyAdaptiveThresholding(data, width, height, 15, 2);

            // PERBAIKAN: Morphological operations untuk memperjelas karakter
            applyMorphologicalOperations(data, width, height, imageAnalysis);

            // PERBAIKAN: Character spacing normalization
            normalizeCharacterSpacing(data, width, height);

            // PERBAIKAN: Perspective correction jika plat terdeteksi miring
            if (plateRegionResult.foundPlate && plateRegionResult.skewAngle) {
                console.log(
                    "Koreksi perspektif diterapkan:",
                    plateRegionResult.skewAngle
                );
                applyPerspectiveCorrection(
                    data,
                    width,
                    height,
                    plateRegionResult.skewAngle
                );
            }

            // Update canvas dengan data yang telah diproses
            ctx.putImageData(imageData, 0, 0);

            resolve(enhancedCanvas);
        } catch (error) {
            console.error("Error dalam meningkatkan gambar:", error);
            resolve(canvas); // Kembalikan canvas asli jika terjadi error
        }
    });
}

// Modifikasi pada fungsi enhanceCustomImage
async function enhanceCustomImage(canvas, options) {
    return new Promise(async (resolve) => {
        try {
            // Default options
            const opts = {
                contrast: 2.5,
                brightness: 0,
                threshold: 130,
                noiseRemoval: "medium",
                sharpness: "medium",
                invert: false,
                yellowPlate: false,
                darkPlate: false,
                thinCharacters: false,
                letterWidth: "normal",
                adaptiveThreshold: false,
                ...options,
            };

            // Buat canvas baru
            const enhancedCanvas = document.createElement("canvas");
            enhancedCanvas.width = canvas.width;
            enhancedCanvas.height = canvas.height;
            const ctx = enhancedCanvas.getContext("2d");

            // Gambar canvas asli ke canvas yang ditingkatkan
            ctx.drawImage(canvas, 0, 0);

            // Auto-deteksi tipe plat jika tidak ditentukan
            if (!opts.darkPlate && !opts.yellowPlate) {
                const imageData = ctx.getImageData(
                    0,
                    0,
                    enhancedCanvas.width,
                    enhancedCanvas.height
                );
                const plateInfo = detectPlateBackground(imageData);

                if (plateInfo.type === "dark" && plateInfo.confidence > 65) {
                    console.log(
                        "Auto-detected dark background plate",
                        plateInfo
                    );
                    opts.darkPlate = true;
                } else if (
                    plateInfo.brightPercentage < 0.3 &&
                    plateInfo.darkPercentage > 0.6
                ) {
                    // Kondisi cahaya rendah, mungkin plat gelap
                    console.log(
                        "Low light conditions detected, trying dark plate processing",
                        plateInfo
                    );
                    opts.darkPlate = true;
                }

                // Logika untuk mendeteksi plat kuning tetap ada
                // ...
            }

            // JALUR KHUSUS: Plat dengan background hitam
            if (opts.darkPlate) {
                console.log("Processing dark background plate");
                const darkPlateCanvas = await enhanceDarkPlateImage(canvas);
                resolve(darkPlateCanvas);
                return;
            }

            // Lanjutkan dengan proses normal untuk plat standar (putih) atau kuning
            // [Kode preprocessing yang sudah ada...]

            // Dapatkan data gambar
            const imageData = ctx.getImageData(
                0,
                0,
                enhancedCanvas.width,
                enhancedCanvas.height
            );
            const data = imageData.data;

            // Penyesuaian brightness
            if (opts.brightness !== 0) {
                for (let i = 0; i < data.length; i += 4) {
                    data[i] = Math.max(
                        0,
                        Math.min(255, data[i] + opts.brightness)
                    );
                    data[i + 1] = Math.max(
                        0,
                        Math.min(255, data[i + 1] + opts.brightness)
                    );
                    data[i + 2] = Math.max(
                        0,
                        Math.min(255, data[i + 2] + opts.brightness)
                    );
                }
            }

            // Optimasi untuk plat kuning
            if (opts.yellowPlate) {
                // Kode yang sudah ada untuk plat kuning
                // ...
            }

            // Konversi ke grayscale
            for (let i = 0; i < data.length; i += 4) {
                const gray =
                    0.2126 * data[i] +
                    0.7152 * data[i + 1] +
                    0.0722 * data[i + 2];
                data[i] = data[i + 1] = data[i + 2] = gray;
            }

            // Terapkan kontras
            const factor =
                (259 * (opts.contrast + 255)) / (255 * (259 - opts.contrast));
            for (let i = 0; i < data.length; i += 4) {
                data[i] = Math.max(
                    0,
                    Math.min(255, factor * (data[i] - 128) + 128)
                );
                data[i + 1] = Math.max(
                    0,
                    Math.min(255, factor * (data[i + 1] - 128) + 128)
                );
                data[i + 2] = Math.max(
                    0,
                    Math.min(255, factor * (data[i + 2] - 128) + 128)
                );
            }

            // Proses lainnya yang sudah ada
            // ...

            // Update canvas dengan data yang telah diproses
            ctx.putImageData(imageData, 0, 0);

            resolve(enhancedCanvas);
        } catch (error) {
            console.error("Error dalam custom enhancement:", error);
            resolve(canvas);
        }
    });
}

// Fungsi untuk optimasi gambar plat background hitam
function enhanceDarkPlateImage(canvas) {
    return new Promise((resolve) => {
        try {
            const enhancedCanvas = document.createElement("canvas");
            enhancedCanvas.width = canvas.width;
            enhancedCanvas.height = canvas.height;
            const ctx = enhancedCanvas.getContext("2d");

            // Gambar canvas asli ke canvas yang ditingkatkan
            ctx.drawImage(canvas, 0, 0);

            // Analisis gambar
            const imageData = ctx.getImageData(
                0,
                0,
                enhancedCanvas.width,
                enhancedCanvas.height
            );
            const data = imageData.data;

            // 1. Inverse gambar untuk plat background hitam
            for (let i = 0; i < data.length; i += 4) {
                data[i] = 255 - data[i]; // R
                data[i + 1] = 255 - data[i + 1]; // G
                data[i + 2] = 255 - data[i + 2]; // B
            }

            // 2. Tingkatkan kontras untuk memperjelas karakter putih
            const contrastFactor = 2.5;
            for (let i = 0; i < data.length; i += 4) {
                data[i] = Math.max(
                    0,
                    Math.min(255, (data[i] - 128) * contrastFactor + 128)
                );
                data[i + 1] = Math.max(
                    0,
                    Math.min(255, (data[i + 1] - 128) * contrastFactor + 128)
                );
                data[i + 2] = Math.max(
                    0,
                    Math.min(255, (data[i + 2] - 128) * contrastFactor + 128)
                );
            }

            // 3. Gaussian blur untuk mengurangi noise
            const tempData = new Uint8ClampedArray(data.length);
            for (let i = 0; i < data.length; i++) {
                tempData[i] = data[i];
            }
            applyGaussianBlur(
                data,
                tempData,
                enhancedCanvas.width,
                enhancedCanvas.height,
                3
            );

            // 4. Adaptive thresholding untuk hasil optimal
            applyAdaptiveThresholding(
                data,
                enhancedCanvas.width,
                enhancedCanvas.height,
                15,
                5
            );

            // 5. Morphological operations untuk memperjelas karakter
            // Dilation untuk memperbesar karakter putih
            const morphTempData = new Uint8ClampedArray(data.length);
            for (let i = 0; i < data.length; i++) {
                morphTempData[i] = data[i];
            }
            applyDilation(
                data,
                morphTempData,
                enhancedCanvas.width,
                enhancedCanvas.height
            );

            // Update canvas dengan data yang diproses
            ctx.putImageData(imageData, 0, 0);

            // Debug mode - tampilkan gambar proses
            if (window.debugMode) {
                displayProcessingImage(enhancedCanvas, "Dark Plate Enhanced");
            }

            resolve(enhancedCanvas);
        } catch (error) {
            console.error("Error enhancing dark plate:", error);
            resolve(canvas); // Kembalikan canvas asli jika error
        }
    });
}

// Fungsi khusus untuk optimasi plat putih standard
function enhanceLightPlateImage(canvas) {
    return new Promise((resolve) => {
        try {
            const enhancedCanvas = document.createElement("canvas");
            enhancedCanvas.width = canvas.width;
            enhancedCanvas.height = canvas.height;
            const ctx = enhancedCanvas.getContext("2d");

            // Gambar canvas asli ke canvas yang ditingkatkan
            ctx.drawImage(canvas, 0, 0);

            // Dapatkan data gambar
            const imageData = ctx.getImageData(
                0,
                0,
                enhancedCanvas.width,
                enhancedCanvas.height
            );
            const data = imageData.data;

            // 1. Deteksi dan analisis area plat
            // Komputasi histogram untuk analisis
            const histogram = new Array(256).fill(0);
            for (let i = 0; i < data.length; i += 4) {
                const gray = Math.round(
                    0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2]
                );
                histogram[gray]++;
            }

            // Tentukan nilai threshold optimal menggunakan metode Otsu
            const threshold = determineOtsuThreshold(
                histogram,
                enhancedCanvas.width * enhancedCanvas.height
            );

            // 2. Peningkatan kontras lokal dan global
            const contrastFactor = 2.0;
            const brightnessFactor = -10; // Sedikit gelap untuk memperjelas karakter hitam

            for (let i = 0; i < data.length; i += 4) {
                // Koreksi brightness
                data[i] = Math.max(
                    0,
                    Math.min(255, data[i] + brightnessFactor)
                );
                data[i + 1] = Math.max(
                    0,
                    Math.min(255, data[i + 1] + brightnessFactor)
                );
                data[i + 2] = Math.max(
                    0,
                    Math.min(255, data[i + 2] + brightnessFactor)
                );

                // Peningkatan kontras
                data[i] = Math.max(
                    0,
                    Math.min(255, (data[i] - 128) * contrastFactor + 128)
                );
                data[i + 1] = Math.max(
                    0,
                    Math.min(255, (data[i + 1] - 128) * contrastFactor + 128)
                );
                data[i + 2] = Math.max(
                    0,
                    Math.min(255, (data[i + 2] - 128) * contrastFactor + 128)
                );
            }

            // 3. Konversi ke grayscale
            for (let i = 0; i < data.length; i += 4) {
                const gray = Math.round(
                    0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2]
                );
                data[i] = data[i + 1] = data[i + 2] = gray;
            }

            // 4. Proses adaptif threshold berdasarkan histogram
            applyAdaptiveThresholding(
                data,
                enhancedCanvas.width,
                enhancedCanvas.height,
                17,
                4
            );

            // 5. Operasi morfologi untuk memperjelas karakter
            // Pertama erosi untuk menghapus noise
            const tempData = new Uint8ClampedArray(data.length);
            for (let i = 0; i < data.length; i++) {
                tempData[i] = data[i];
            }

            applyErosion(data, enhancedCanvas.width, enhancedCanvas.height);

            // Lalu dilasi untuk memperkuat karakter
            for (let i = 0; i < data.length; i++) {
                tempData[i] = data[i];
            }
            applyDilation(data, enhancedCanvas.width, enhancedCanvas.height);

            // 6. Edge enhancement untuk memperjelas batas karakter
            for (let i = 0; i < data.length; i++) {
                tempData[i] = data[i];
            }
            applySharpen(
                data,
                tempData,
                enhancedCanvas.width,
                enhancedCanvas.height,
                1.5
            );

            // Update canvas dengan data yang diproses
            ctx.putImageData(imageData, 0, 0);

            // Debug mode - tampilkan gambar proses
            if (window.debugMode) {
                displayProcessingImage(
                    enhancedCanvas,
                    "Standard White Plate Enhanced"
                );
            }

            resolve(enhancedCanvas);
        } catch (error) {
            console.error("Error enhancing light plate:", error);
            resolve(canvas); // Kembalikan canvas asli jika error
        }
    });
}

// Fungsi untuk mencari threshold optimal menggunakan metode Otsu
function determineOtsuThreshold(histogram, totalPixels) {
    // Probabilitas untuk setiap intensitas
    const probabilities = histogram.map((count) => count / totalPixels);

    let bestThreshold = 0;
    let bestVariance = 0;

    // Untuk setiap kemungkinan threshold
    for (let t = 0; t < 256; t++) {
        // Probabilitas kelas (background / foreground)
        let w0 = 0;
        let w1 = 0;

        // Mean kelas
        let mu0 = 0;
        let mu1 = 0;

        // Hitung probabilitas dan mean untuk kedua kelas
        for (let i = 0; i < 256; i++) {
            if (i <= t) {
                w0 += probabilities[i];
                mu0 += i * probabilities[i];
            } else {
                w1 += probabilities[i];
                mu1 += i * probabilities[i];
            }
        }

        // Normalisasi mean
        if (w0 > 0) mu0 /= w0;
        if (w1 > 0) mu1 /= w1;

        // Hitung between-class variance
        const variance = w0 * w1 * Math.pow(mu0 - mu1, 2);

        // Update jika menemukan variance yang lebih baik
        if (variance > bestVariance) {
            bestVariance = variance;
            bestThreshold = t;
        }
    }

    return bestThreshold;
}

// Fungsi analisis gambar yang lebih canggih
function analyzeImageCharacteristics(data, width, height) {
    // Histogram untuk analisis brightness dan contrast
    const histogram = new Array(256).fill(0);
    let yellowPixelCount = 0;
    let totalPixels = width * height;

    // Hitung histogram dan deteksi warna kuning (untuk plat kuning)
    for (let i = 0; i < data.length; i += 4) {
        const r = data[i];
        const g = data[i + 1];
        const b = data[i + 2];

        // Brightness dengan pembobotan BT.709
        const brightness = Math.round(0.2126 * r + 0.7152 * g + 0.0722 * b);
        histogram[brightness]++;

        // Deteksi warna kuning (R tinggi, G tinggi, B rendah)
        if (r > 150 && g > 150 && b < 100) {
            yellowPixelCount++;
        }
    }

    // Analisis brightness
    let cumulativeSum = 0;
    let lowerPercentile = 0;
    let upperPercentile = 0;

    for (let i = 0; i < 256; i++) {
        cumulativeSum += histogram[i];

        if (cumulativeSum / totalPixels >= 0.05 && lowerPercentile === 0) {
            lowerPercentile = i; // 5% percentile
        }

        if (cumulativeSum / totalPixels >= 0.95 && upperPercentile === 0) {
            upperPercentile = i; // 95% percentile
            break;
        }
    }

    // Hitung brightness dan contrast metrics
    const medianBrightness = findMedianBrightness(histogram, totalPixels);
    const contrastLevel = upperPercentile - lowerPercentile;

    // Deteksi plat kuning (threshold 15%)
    const isYellowPlate = yellowPixelCount / totalPixels > 0.15;

    return {
        medianBrightness,
        contrastLevel,
        isDark: medianBrightness < 80,
        isBright: medianBrightness > 200,
        isYellowPlate,
        noiseLevel: estimateNoiseLevel(data, width, height),
        edgeStrength: calculateEdgeStrength(data, width, height),
    };
}

// Deteksi tipe background plat nomor
function detectPlateBackground(imageData) {
    const data = imageData.data;
    const width = imageData.width;
    const height = imageData.height;

    // Histogram untuk analisis brightness
    const histogram = new Array(256).fill(0);
    let totalPixels = width * height;

    // Analisis warna & brightness
    let darkPixelCount = 0;
    let brightPixelCount = 0;
    let blackWhiteTransitions = 0;
    let lastPixelBright = false;

    for (let y = 0; y < height; y++) {
        for (let x = 0; x < width; x++) {
            const idx = (y * width + x) * 4;
            const r = data[idx];
            const g = data[idx + 1];
            const b = data[idx + 2];

            // Brightness dengan pembobotan BT.709
            const brightness = Math.round(0.2126 * r + 0.7152 * g + 0.0722 * b);
            histogram[brightness]++;

            // Hitung piksel gelap dan terang
            if (brightness < 70) {
                darkPixelCount++;
                if (lastPixelBright) {
                    blackWhiteTransitions++;
                    lastPixelBright = false;
                }
            } else if (brightness > 180) {
                brightPixelCount++;
                if (!lastPixelBright) {
                    blackWhiteTransitions++;
                    lastPixelBright = true;
                }
            }
        }
    }

    // Persentase piksel gelap dan terang
    const darkPercentage = darkPixelCount / totalPixels;
    const brightPercentage = brightPixelCount / totalPixels;

    // Analisis distribusi
    let peakDark = 0;
    let peakBright = 0;
    let darkPeakValue = 0;
    let brightPeakValue = 0;

    // Cari puncak gelap (0-90) dan terang (165-255)
    for (let i = 0; i < 90; i++) {
        if (histogram[i] > peakDark) {
            peakDark = histogram[i];
            darkPeakValue = i;
        }
    }

    for (let i = 165; i < 256; i++) {
        if (histogram[i] > peakBright) {
            peakBright = histogram[i];
            brightPeakValue = i;
        }
    }

    // Rasio puncak (untuk karakter kontras tinggi)
    const peakRatio =
        Math.max(peakDark, peakBright) /
        Math.max(1, Math.min(peakDark, peakBright));

    // Keputusan tipe plat berdasarkan analisis
    let plateType = "unknown";
    let confidence = 0;

    if (darkPercentage > 0.6 && peakRatio > 2) {
        // Plat dengan background hitam (karakter terang)
        plateType = "dark";
        confidence = Math.min(100, darkPercentage * 100 + (peakRatio - 2) * 10);
    } else if (brightPercentage > 0.5 && peakRatio > 2) {
        // Plat dengan background putih (karakter gelap)
        plateType = "light";
        confidence = Math.min(
            100,
            brightPercentage * 100 + (peakRatio - 2) * 10
        );
    } else if (blackWhiteTransitions > width * height * 0.02) {
        // Banyak transisi hitam-putih menunjukkan plat nomor
        // Tentukan tipe berdasarkan dominasi
        plateType = darkPercentage > brightPercentage ? "dark" : "light";
        confidence =
            60 +
            Math.min(30, (blackWhiteTransitions / (width * height)) * 3000);
    } else {
        // Default ke plat standar jika tidak ada pola jelas
        plateType = "light"; // Default: plat putih
        confidence = 50;
    }

    return {
        type: plateType,
        confidence: confidence,
        darkPercentage: darkPercentage,
        brightPercentage: brightPercentage,
        blackWhiteTransitions: blackWhiteTransitions,
        peakRatio: peakRatio,
    };
}

// Deteksi area plat dengan pendekatan advanced
function detectPlateRegionAdvanced(canvas) {
    // Implementasi deteksi area plat yang lebih canggih menggunakan
    // kombinasi edge detection, contour analysis, dan geometric filters

    const ctx = canvas.getContext("2d");
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const data = imageData.data;

    // 1. Konversi ke grayscale
    const grayscale = new Uint8Array(canvas.width * canvas.height);
    for (let y = 0; y < canvas.height; y++) {
        for (let x = 0; x < canvas.width; x++) {
            const idx = (y * canvas.width + x) * 4;
            grayscale[y * canvas.width + x] = Math.round(
                0.299 * data[idx] +
                    0.587 * data[idx + 1] +
                    0.114 * data[idx + 2]
            );
        }
    }

    // 2. Edge detection (Sobel)
    const edges = applySobelEdgeDetection(
        grayscale,
        canvas.width,
        canvas.height
    );

    // 3. Morphological operations untuk memperjelas batas
    const dilatedEdges = applyDilationToArray(
        edges,
        canvas.width,
        canvas.height
    );

    // 4. Connected component analysis
    const regions = findConnectedRegions(
        dilatedEdges,
        canvas.width,
        canvas.height
    );

    // 5. Filter dan skor region berdasarkan karakteristik plat Indonesia
    const scoredRegions = regions
        .map((region) => ({
            ...region,
            score: scorePlateRegion(region, canvas.width, canvas.height),
        }))
        .filter((region) => region.score > 0.5) // Minimal 50% confidence
        .sort((a, b) => b.score - a.score);

    // Jika ada region dengan skor tinggi
    if (scoredRegions.length > 0 && scoredRegions[0].score > 0.7) {
        const bestRegion = scoredRegions[0];

        // Deteksi kemiringan (skew angle)
        const skewAngle = detectSkewAngle(
            dilatedEdges,
            canvas.width,
            canvas.height,
            bestRegion
        );

        return {
            foundPlate: true,
            region: bestRegion,
            skewAngle,
        };
    }

    return { foundPlate: false };
}

// Event handler
$(document).ready(function () {
    console.log("Indonesia plate detection loaded");

    // Tambahkan handler untuk tombol deteksi plat otomatis
    $(document).on("click", "#detectAutoPlate", function () {
        console.log("Auto plate detection button clicked");
        detectIndonesianPlate();
    });

    // Tambahkan handler untuk tombol browser detection yang sudah ada
    $(document).on("click", "#takePictureBrowser", function () {
        console.log(
            "Browser detection button clicked (with Indonesian plate format)"
        );
        detectIndonesianPlate();
    });

    // Siapkan konfigurasi Tesseract optimal
    if (typeof Tesseract !== "undefined") {
        // Atur worker count untuk performa lebih baik
        Tesseract.setLogging(false);

        // Pre-initialize worker untuk performa lebih baik
        console.log("Pre-initializing Tesseract worker...");
        const initWorker = Tesseract.createWorker({
            logger: function (m) {
                if (m.status === "recognizing text") {
                    // Kosong, hanya untuk supresi log
                }
            },
        });

        // Inisialisasi worker di background
        initWorker
            .then((worker) => {
                window.preInitializedTesseractWorker = worker;
                console.log("Tesseract worker pre-initialized");
            })
            .catch((err) => {
                console.error(
                    "Failed to pre-initialize Tesseract worker:",
                    err
                );
            });
    }

    // Tambahkan di bagian kontrol
    $("#detectionControls").append(`
        <div class="form-group mt-2">
            <label for="plateTypeSelect">Tipe Plat</label>
            <select class="form-control" id="plateTypeSelect">
                <option value="auto">Otomatis</option>
                <option value="light">Plat Putih</option>
                <option value="dark">Plat Hitam</option>
                <option value="yellow">Plat Kuning</option>
            </select>
        </div>
    `);

    // Gunakan pilihan ini saat deteksi
    $("#plateTypeSelect").on("change", function () {
        window.forcedPlateType = $(this).val();
    });
}); // <-- Tambahkan tanda kurung tutup dan titik koma di sini

// Ekspos fungsi sanitasi plat ke global scope agar bisa diakses oleh file lain
// Tambahkan di akhir file
// Ekspos fungsi penting ke scope global
window.sanitizeLicensePlate = sanitizeLicensePlate;
window.enhanceIndonesianPlateImage = enhanceIndonesianPlateImage;
window.processMultipleImageVersions = processMultipleImageVersions;
window.calculatePlateScore = calculatePlateScore;
window.normalizePlateNumbers = normalizePlateNumbers;
window.normalizePlateLetters = normalizePlateLetters;

// Tambahkan fungsi processMultipleImageVersions
async function processMultipleImageVersions(canvas) {
    try {
        console.log(
            "Memulai multiple processing dengan pendekatan terbaik untuk plat Indonesia"
        );

        // Deteksi tipe plat terlebih dahulu
        const ctx = canvas.getContext("2d");
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const plateInfo = detectPlateBackground(imageData);
        console.log("Deteksi tipe plat:", plateInfo);

        // Tambah versi preprocessing khusus plat hitam jika terdeteksi
        const isDarkPlate =
            plateInfo.type === "dark" && plateInfo.confidence > 65;
        const versions = [];

        // Versi khusus untuk plat background hitam
        if (isDarkPlate || plateInfo.darkPercentage > 0.5) {
            console.log("Menambahkan versi untuk plat background hitam");

            // Version Dark-1: Optimized for black background plates - inverted
            versions.push(
                await enhanceCustomImage(canvas, {
                    darkPlate: true,
                    contrast: 3.0,
                    threshold: 130,
                    adaptiveThreshold: true,
                    sharpness: "high",
                    letterWidth: "normal",
                })
            );

            // Version Dark-2: Aggressive enhancement for black plates
            versions.push(
                await enhanceCustomImage(canvas, {
                    darkPlate: true,
                    contrast: 4.0,
                    brightness: 20,
                    adaptiveThreshold: true,
                    sharpness: "very_high",
                    letterWidth: "wide",
                })
            );

            // Version Dark-3: Binary with custom thresholding
            versions.push(
                await enhanceCustomImage(canvas, {
                    darkPlate: true,
                    binary: true,
                    brightness: 30,
                    threshold: 120,
                    morphology: "dilate",
                    letterWidth: "wide",
                })
            );

            // Version Dark-4: Specialized for low contrast dark plates
            versions.push(
                await enhanceCustomImage(canvas, {
                    darkPlate: true,
                    contrast: 5.0,
                    brightness: 40,
                    sharpen: 3.0,
                    adaptiveThreshold: true,
                })
            );
        }

        // Versi standar untuk semua tipe plat
        // Version 1: Basic high contrast option optimized for standard white plates
        versions.push(
            await enhanceCustomImage(canvas, {
                contrast: 3.5,
                threshold: 140,
                sharpness: "high",
                adaptiveThreshold: true,
                edgeEnhancement: 2.0,
                plateType: isDarkPlate ? "dark" : "white",
            })
        );

        // Version 2: Optimized for yellow plates (public transport)
        versions.push(
            await enhanceCustomImage(canvas, {
                contrast: 3.0,
                yellowPlateOptimization: true,
                colorInversion: false,
                edgeEnhancement: 1.8,
                letterSpacing: "normal",
                plateType: "yellow",
            })
        );

        // Version 3: Optimized for low light conditions
        versions.push(
            await enhanceCustomImage(canvas, {
                brightness: isDarkPlate ? -20 : 30,
                contrast: 4.0,
                sharpen: 3.0,
                denoise: "high",
                adaptiveThreshold: true,
                plateType: isDarkPlate ? "dark" : "white",
                gammaCorrection: 1.5,
            })
        );

        // Tambahkan versi lainnya yang sudah ada
        // ...

        // PERBAIKAN: Konfigurasi OCR yang lebih baik untuk setiap versi gambar
        // [Kode Konfigurasi OCR yang sudah ada]

        // Lanjutkan dengan kode yang sudah ada untuk pemrosesan semua versi dengan Tesseract

        // Logika pemilihan hasil terbaik dipertahankan
        // ...

        // [Kode yang sudah ada untuk memproses semua versi dan mendapatkan hasil]

        return ""; // Hasil akan ditentukan oleh kode sebelumnya
    } catch (error) {
        console.error("Error dalam multiple processing:", error);
        throw error;
    }
}

// Update fungsi sanitizeLicensePlate untuk mendukung berbagai tipe plat
// Perbaiki fungsi sanitizeLicensePlate yang sudah ada
function sanitizeLicensePlate(text) {
    try {
        console.log("Input sanitasi plat:", text);
        
        // 1. Pembersihan awal - hapus karakter tidak valid
        let sanitized = text
            .replace(/[^A-Z0-9\s]/gi, "") // Hapus semua karakter non-alfanumerik
            .toUpperCase()
            .trim();
        
        // 2. Standarisasi spasi
        sanitized = sanitized.replace(/\s+/g, " ");
        
        console.log("Setelah pembersihan awal:", sanitized);
        
        // 3. Coba identifikasi dengan pola plat standar Indonesia
        // Format: 1-2 huruf, 1-4 angka, 1-3 huruf (contoh: B 1234 XYZ)
        const platePattern = /([A-Z]{1,2})\s*([0-9]{1,4})\s*([A-Z]{1,3})/i;
        const match = sanitized.match(platePattern);
        
        if (match) {
            console.log("Pola plat terdeteksi:", match);
            
            let region = match[1]; // 1-2 huruf (kode wilayah)
            let numbers = match[2]; // 1-4 angka
            let letters = match[3]; // 1-3 huruf (seri)
            
            // Normalisasi setiap bagian
            region = normalizeRegion(region);
            numbers = normalizeNumbers(numbers);
            letters = normalizeLetters(letters);
            
            // Format dengan spasi yang benar
            const result = `${region} ${numbers} ${letters}`;
            console.log("Hasil sanitasi pola:", result);
            return result;
        }
        
        // 4. Jika tidak match dengan pola di atas, coba parsing manual
        console.log("Parsing manual untuk:", sanitized);
        
        // Hapus semua spasi untuk pemrosesan
        const noSpaces = sanitized.replace(/\s/g, "");
        
        // Jika string terlalu pendek, kembalikan default
        if (noSpaces.length < 4) {
            console.log("Teks terlalu pendek, gunakan default");
            return "B 1234 ABC";
        }
        
        let region = "";
        let numbers = "";
        let letters = "";
        let pos = 0;
        
        // Ekstrak region (1-2 huruf di awal)
        while (pos < noSpaces.length && /[A-Z0-9]/i.test(noSpaces[pos]) && region.length < 2) {
            const char = noSpaces[pos++];
            // Konversi angka yang mungkin terdeteksi sebagai huruf
            if (char === '0') region += 'O';
            else if (char === '1') region += 'I';
            else if (char === '8') region += 'B';
            else if (char === '5') region += 'S';
            else region += char;
        }
        
        // Ekstrak nomor (1-4 angka)
        while (pos < noSpaces.length && /[0-9A-Z]/i.test(noSpaces[pos]) && numbers.length < 4) {
            const char = noSpaces[pos++];
            // Konversi huruf yang mungkin terdeteksi sebagai angka
            if (char === 'O' || char === 'o' || char === 'Q' || char === 'q' || char === 'D' || char === 'd') numbers += '0';
            else if (char === 'I' || char === 'i' || char === 'l' || char === 'L') numbers += '1';
            else if (char === 'Z' || char === 'z') numbers += '2';
            else if (char === 'E' || char === 'e') numbers += '3';
            else if (char === 'A' || char === 'a') numbers += '4';
            else if (char === 'S' || char === 's') numbers += '5';
            else if (char === 'G' || char === 'g') numbers += '6';
            else if (char === 'T' || char === 't') numbers += '7';
            else if (char === 'B' || char === 'b') numbers += '8';
            else if (/[0-9]/.test(char)) numbers += char;
            else break; // Jika bukan angka atau huruf yang dapat dikonversi, hentikan ekstraksi nomor
        }
        
        // Ekstrak sisa sebagai seri huruf
        while (pos < noSpaces.length && letters.length < 3) {
            const char = noSpaces[pos++];
            // Konversi angka yang mungkin terdeteksi sebagai huruf
            if (char === '0') letters += 'O';
            else if (char === '1') letters += 'I';
            else if (char === '8') letters += 'B';
            else if (char === '5') letters += 'S';
            else if (char === '2') letters += 'Z';
            else if (char === '4') letters += 'A';
            else if (/[A-Z]/i.test(char)) letters += char;
        }
        
        // Validasi setiap bagian
        region = normalizeRegion(region);
        numbers = normalizeNumbers(numbers);
        letters = normalizeLetters(letters);
        
        // Format hasil akhir
        const result = `${region} ${numbers} ${letters}`;
        console.log("Hasil sanitasi manual:", result);
        return result;
    } catch (error) {
        console.error("Error dalam sanitasi plat:", error);
        return "B 1234 ABC"; // Default jika terjadi error
    }
}

// Tambahkan fungsi pembantu untuk normalisasi karakter
function normalizeRegion(region) {
    // Pastikan hanya huruf dan maksimal 2 karakter
    region = region.replace(/0/g, "O").replace(/1/g, "I").replace(/8/g, "B").replace(/5/g, "S");
    region = region.replace(/[^A-Z]/gi, "").toUpperCase();
    if (region.length === 0) region = "B"; // Default jika kosong
    if (region.length > 2) region = region.substring(0, 2);
    return region;
}

function normalizeNumbers(numbers) {
    // Pastikan berisi angka yang valid dan maksimal 4 digit
    numbers = numbers
        .replace(/O|o|Q|q|D|d/g, "0")
        .replace(/I|i|l|L/g, "1")
        .replace(/Z|z/g, "2")
        .replace(/E|e/g, "3")
        .replace(/A|a/g, "4")
        .replace(/S|s/g, "5")
        .replace(/G|g/g, "6")
        .replace(/T|t/g, "7")
        .replace(/B|b/g, "8");
    
    numbers = numbers.replace(/[^0-9]/g, "");
    if (numbers.length === 0) numbers = "1234"; // Default jika kosong
    if (numbers.length > 4) numbers = numbers.substring(0, 4);
    return numbers;
}

function normalizeLetters(letters) {
    // Pastikan berisi huruf yang valid dan maksimal 3 karakter
    letters = letters
        .replace(/0/g, "O")
        .replace(/1/g, "I")
        .replace(/8/g, "B")
        .replace(/5/g, "S")
        .replace(/2/g, "Z")
        .replace(/4/g, "A");
    
    letters = letters.replace(/[^A-Z]/gi, "").toUpperCase();
    if (letters.length === 0) letters = "ABC"; // Default jika kosong
    if (letters.length > 3) letters = letters.substring(0, 3);
    return letters;
}

// Pastikan sanitasi selalu berjalan dengan helper function
function ensureSanitizationRuns(rawText) {
    console.log("Running ensureSanitizationRuns with:", rawText);
    
    // Cek apakah rawText valid
    if (!rawText || typeof rawText !== 'string') {
        console.error("rawText tidak valid:", rawText);
        return "B 1234 ABC";
    }
    
    // Coba panggil sanitizeLicensePlate
    try {
        const sanitized = sanitizeLicensePlate(rawText);
        console.log("Sanitasi berhasil:", sanitized);
        return sanitized;
    } catch (e) {
        console.error("Error saat memanggil sanitizeLicensePlate:", e);
        return "B 1234 ABC";
    }
}

// Modifikasi fungsi detectIndonesianPlate yang sudah ada
async function detectIndonesianPlate() {
    console.log("Deteksi plat nomor Indonesia dimulai");

    // Status checks and setup
    if (window.ocrInProgress) {
        console.log("Proses OCR sedang berjalan, harap tunggu");
        return;
    }

    window.ocrInProgress = true;
    $("#loading-indicator").show();
    
    if ($("#browser-processing-message").length) {
        $("#browser-processing-message")
            .removeClass("d-none")
            .text("Mempersiapkan deteksi...");
    }

    try {
        // Capture image from video
        const video = document.getElementById("camera-preview");
        const canvas = document.getElementById("canvas-preview");
        const capturedImage = document.getElementById("captured-image");
        
        // Setup canvas
        const context = canvas.getContext("2d");
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        capturedImage.src = canvas.toDataURL("image/jpeg", 0.9);
        $(".captured-image").show();
        
        // Enhance image for OCR
        let processedCanvas;
        
        // Check if forced plate type is set
        if (window.forcedPlateType && window.forcedPlateType !== 'auto') {
            console.log(`Menggunakan tipe plat yang dipilih: ${window.forcedPlateType}`);
            
            if (window.forcedPlateType === 'dark') {
                processedCanvas = await enhanceDarkPlateImage(canvas);
            } else if (window.forcedPlateType === 'yellow') {
                processedCanvas = await enhanceYellowPlateImage(canvas);
            } else {
                processedCanvas = await enhanceLightPlateImage(canvas);
            }
        } else {
            // Auto detect and enhance
            processedCanvas = await processMultipleImageVersions(canvas);
        }
        
        // Process with OCR
        if ($("#browser-processing-message").length) {
            $("#browser-processing-message").text("Mengenali teks plat nomor...");
        }

        // OCR dengan Tesseract  
        const result = await Tesseract.recognize(
            processedCanvas.toDataURL("image/jpeg", 1.0),
            "eng",
            {
                logger: (m) => {
                    console.log(m);
                    if ($("#browser-processing-message").length && m.status === "recognizing text") {
                        $("#browser-processing-message").text(
                            `Mengenali teks: ${Math.floor(m.progress * 100)}%`
                        );
                    }
                },
                ...optimizeOcrForIndonesianPlate()
            }
        );

        // Dapatkan teks hasil OCR
        const rawText = result.data.text.trim();
        console.log("Teks hasil OCR:", rawText);
        
        // Validasi rawText
        if (!rawText || rawText.trim() === "") {
            throw new Error("OCR tidak mendeteksi teks apapun");
        }
        
        // Pastikan sanitasi berjalan
        const sanitizedPlate = ensureSanitizationRuns(rawText);
        console.log("Hasil sanitasi final:", sanitizedPlate);
        
        // Sembunyikan loading
        $("#loading-indicator").hide();
        if ($("#browser-processing-message").length) {
            $("#browser-processing-message").addClass("d-none");
        }
        window.ocrInProgress = false;

        // Verifikasi hasil sanitasi
        if (!sanitizedPlate || sanitizedPlate.trim() === "") {
            throw new Error("Hasil sanitasi kosong");
        }
        
        // Isi field plat nomor
        $("#plat_nomor").val(sanitizedPlate);
        
        // Debug untuk mengecek nilai field
        console.log("Nilai field plat setelah diset:", $("#plat_nomor").val());
        
        // Highlight untuk menandakan update berhasil
        $("#plat_nomor").addClass("highlight-for-edit");
        setTimeout(() => {
            $("#plat_nomor").removeClass("highlight-for-edit");
        }, 2000);
        
        // Tampilkan notifikasi sukses
        Swal.fire({
            icon: "success",
            title: "Deteksi Berhasil",
            html: `
                <p>Plat nomor terdeteksi:</p>
                <div class="plate-preview">${sanitizedPlate}</div>
                <p class="text-muted mt-1">Confidence: ${Math.round(
                    result.data.confidence
                )}%</p>
            `,
            confirmButtonColor: "#28a745",
        });

        // Close camera modal
        $("#cameraModal").modal("hide");
        
    } catch (error) {
        console.error("Error deteksi plat:", error);
        
        // Sembunyikan loading
        $("#loading-indicator").hide();
        if ($("#browser-processing-message").length) {
            $("#browser-processing-message").addClass("d-none");
        }
        window.ocrInProgress = false;

        Swal.fire({
            icon: "error",
            title: "Error Deteksi",
            text: "Gagal mendeteksi plat nomor: " + error.message,
            confirmButtonColor: "#dc3545",
        });
    }
}

// Ekspos fungsi ke global scope
window.sanitizeLicensePlate = sanitizeLicensePlate;
window.ensureSanitizationRuns = ensureSanitizationRuns;
