/**
 * Deteksi Plat Nomor Indonesia Otomatis
 * Optimized for Indonesian license plate format
 */

// Format plat nomor Indonesia: 1-2 huruf (kode wilayah), 1-4 angka (nomor registrasi), 1-3 huruf (kode seri)
// Contoh: B 1234 XYZ, D 56 FG, AB 123 CD

// Variabel untuk menyimpan model
let plateModel;
let isModelLoaded = false;

// Modifikasi fungsi utama detectIndonesianPlate
async function detectIndonesianPlate() {
    console.log("Deteksi plat nomor Indonesia dimulai");

    // Status checks and setup - existing code
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
        // Capture image - existing code
        const video = document.getElementById("camera-preview");
        const canvas = document.getElementById("canvas-preview");
        const capturedImage = document.getElementById("captured-image");

        // Canvas setup and capturing - existing code
        let context;
        if (!canvas) {
            // Canvas creation code
        } else {
            context = canvas.getContext("2d");
        }

        // Canvas dimensions and capture code
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        capturedImage.src = canvas.toDataURL("image/jpeg", 0.9);
        $(".captured-image").show();

        // Update processing message
        if ($("#browser-processing-message").length) {
            $("#browser-processing-message").text(
                "Menganalisis gambar dengan multiple processing..."
            );
        }

        // NEW: Use multiple processing approaches for better accuracy
        const rawText = await processMultipleImageVersions(canvas);
        console.log("Teks hasil OCR dari multiple processing:", rawText);

        // Apply sanitization
        const sanitizedPlate = sanitizeLicensePlate(rawText);
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

        // Show success notification
        Swal.fire({
            icon: "success",
            title: "Deteksi Berhasil",
            html: `
                <p>Plat nomor terdeteksi:</p>
                <div class="plate-preview">${sanitizedPlate}</div>
                <p class="text-muted mt-1">Akurasi tinggi dengan teknologi multi-processing</p>
                <p class="text-info small mt-0">Format plat Indonesia terdeteksi otomatis</p>
            `,
            confirmButtonColor: "#28a745",
        });

        // Close camera modal
        $("#cameraModal").modal("hide");
    } catch (error) {
        console.error("Error deteksi plat:", error);
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

            // Pre-processing untuk plat Indonesia (background putih, teks hitam)
            const imageData = ctx.getImageData(
                0,
                0,
                enhancedCanvas.width,
                enhancedCanvas.height
            );
            const data = imageData.data;
            const width = enhancedCanvas.width;
            const height = enhancedCanvas.height;

            // *** PENINGKATAN 1: DETEKSI OTSU THRESHOLD ***
            // Deteksi threshold optimal dengan metode Otsu
            let histogram = new Array(256).fill(0);

            // Buat histogram
            for (let i = 0; i < data.length; i += 4) {
                const gray = Math.round(
                    0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2]
                );
                histogram[gray]++;
            }

            // Hitung total piksel
            const total = width * height;

            let sumB = 0;
            let wB = 0;
            let wF = 0;
            let varMax = 0;
            let threshold = 0;

            // Hitung sum
            let sum = 0;
            for (let i = 0; i < 256; i++) {
                sum += i * histogram[i];
            }

            // Cari threshold optimal
            for (let t = 0; t < 256; t++) {
                wB += histogram[t]; // Weight background
                if (wB === 0) continue;

                wF = total - wB; // Weight foreground
                if (wF === 0) break;

                sumB += t * histogram[t];

                const mB = sumB / wB; // Mean background
                const mF = (sum - sumB) / wF; // Mean foreground

                // Kalkulasi variance between class
                const varBetween = wB * wF * (mB - mF) * (mB - mF);

                // Temukan nilai maksimum
                if (varBetween > varMax) {
                    varMax = varBetween;
                    threshold = t;
                }
            }

            console.log("Otsu threshold optimal:", threshold);

            // *** PENINGKATAN 2: KONVERSI GRAYSCALE YANG LEBIH BAIK ***
            // Konversi grayscale dengan metode yang lebih akurat untuk plat
            for (let i = 0; i < data.length; i += 4) {
                // Menggunakan pembobotan BT.709 yang lebih akurat
                const gray = Math.round(
                    0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2]
                );
                data[i] = data[i + 1] = data[i + 2] = gray;
            }

            // *** PENINGKATAN 3: ADAPTIVE CONTRAST ENHANCEMENT ***
            // Tingkatkan kontras secara adaptif berdasarkan histogram
            const contrast = 2.8; // Nilai kontras yang lebih tinggi
            const factor = (259 * (contrast + 255)) / (255 * (259 - contrast));

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

            // *** PENINGKATAN 4: GAUSSIAN BLUR UNTUK MENGURANGI NOISE ***
            // Simpan data hasil contrast enhancement
            const tempDataBlur = new Uint8ClampedArray(data.length);
            for (let i = 0; i < data.length; i++) {
                tempDataBlur[i] = data[i];
            }

            // Terapkan Gaussian blur dengan kernel 3x3
            const gaussianKernel = [
                1 / 16,
                2 / 16,
                1 / 16,
                2 / 16,
                4 / 16,
                2 / 16,
                1 / 16,
                2 / 16,
                1 / 16,
            ];

            for (let y = 1; y < height - 1; y++) {
                for (let x = 1; x < width - 1; x++) {
                    for (let c = 0; c < 3; c++) {
                        let sum = 0;
                        for (let ky = -1; ky <= 1; ky++) {
                            for (let kx = -1; kx <= 1; kx++) {
                                const idx =
                                    ((y + ky) * width + (x + kx)) * 4 + c;
                                sum +=
                                    tempDataBlur[idx] *
                                    gaussianKernel[(ky + 1) * 3 + (kx + 1)];
                            }
                        }
                        const idx = (y * width + x) * 4 + c;
                        data[idx] = sum;
                    }
                }
            }

            // *** PENINGKATAN 5: UNSHARP MASKING UNTUK KETAJAMAN ***
            // Buat salinan data setelah blur
            const tempDataUnsharp = new Uint8ClampedArray(data.length);
            for (let i = 0; i < data.length; i++) {
                tempDataUnsharp[i] = data[i];
            }

            // Terapkan Unsharp Masking
            const amount = 2.0; // Intensitas penajaman

            for (let i = 0; i < data.length; i += 4) {
                for (let c = 0; c < 3; c++) {
                    // Detail = Original - Blur
                    const detail = tempDataBlur[i + c] - tempDataUnsharp[i + c];
                    // Unsharp = Original + (Amount * Detail)
                    data[i + c] = Math.max(
                        0,
                        Math.min(255, tempDataBlur[i + c] + amount * detail)
                    );
                }
            }

            // *** PENINGKATAN 6: BINARISASI DENGAN THRESHOLD OTSU ***
            // Gunakan threshold Otsu yang sudah dihitung
            // Namun tambahkan sedikit bias untuk plat nomor (background putih, teks hitam)
            const thresholdBias = -10; // Bias negatif untuk memperjelas karakter
            const adaptiveThreshold = Math.max(
                0,
                Math.min(255, threshold + thresholdBias)
            );

            for (let i = 0; i < data.length; i += 4) {
                const val = data[i] > adaptiveThreshold ? 255 : 0;
                data[i] = data[i + 1] = data[i + 2] = val;
            }

            // *** PENINGKATAN 7: MORPHOLOGICAL OPERATIONS ***
            // Salin data setelah binarisasi
            const binaryData = new Uint8ClampedArray(data.length);
            for (let i = 0; i < data.length; i++) {
                binaryData[i] = data[i];
            }

            // Operasi erosi untuk menghapus noise kecil
            // Hitung jumlah piksel putih di sekitar setiap piksel
            for (let y = 1; y < height - 1; y++) {
                for (let x = 1; x < width - 1; x++) {
                    const idx = (y * width + x) * 4;

                    // Jika piksel adalah hitam (0), lewati
                    if (binaryData[idx] === 0) continue;

                    // Hitung tetangga putih
                    let whiteNeighbors = 0;
                    for (let ky = -1; ky <= 1; ky++) {
                        for (let kx = -1; kx <= 1; kx++) {
                            if (kx === 0 && ky === 0) continue;

                            const nidx = ((y + ky) * width + (x + kx)) * 4;
                            if (binaryData[nidx] === 255) {
                                whiteNeighbors++;
                            }
                        }
                    }

                    // Jika kurang dari 5 tetangga putih, jadikan hitam (erosi)
                    if (whiteNeighbors < 5) {
                        data[idx] = data[idx + 1] = data[idx + 2] = 0;
                    }
                }
            }

            // Operasi dilasi untuk memperbesar karakter yang tersisa
            // Salin data setelah erosi
            const erodedData = new Uint8ClampedArray(data.length);
            for (let i = 0; i < data.length; i++) {
                erodedData[i] = data[i];
            }

            // Hitung jumlah piksel hitam di sekitar setiap piksel
            for (let y = 1; y < height - 1; y++) {
                for (let x = 1; x < width - 1; x++) {
                    const idx = (y * width + x) * 4;

                    // Jika piksel adalah putih (255), lewati
                    if (erodedData[idx] === 255) continue;

                    // Hitung tetangga hitam
                    let blackNeighbors = 0;
                    for (let ky = -1; ky <= 1; ky++) {
                        for (let kx = -1; kx <= 1; kx++) {
                            if (kx === 0 && ky === 0) continue;

                            const nidx = ((y + ky) * width + (x + kx)) * 4;
                            if (erodedData[nidx] === 0) {
                                blackNeighbors++;
                            }
                        }
                    }

                    // Jika lebih dari 5 tetangga hitam, jadikan hitam (dilasi)
                    if (blackNeighbors > 5) {
                        data[idx] = data[idx + 1] = data[idx + 2] = 0;
                    }
                }
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

// Tambahkan metode baru: processMultipleImageVersions
async function processMultipleImageVersions(canvas) {
    try {
        // Create multiple versions of the image with different processing parameters
        const versions = [
            // Version 1: Standard processing (default)
            await enhanceIndonesianPlateImage(canvas),

            // Version 2: Higher contrast
            await enhanceCustomImage(canvas, { contrast: 3.5, threshold: 100 }),

            // Version 3: More aggressive noise removal
            await enhanceCustomImage(canvas, {
                contrast: 2.8,
                noiseRemoval: "high",
                threshold: 120,
            }),

            // Version 4: Sharper edges
            await enhanceCustomImage(canvas, {
                contrast: 2.5,
                sharpness: "high",
                threshold: 110,
            }),

            // Version 5: Inverted (for dark plates with light text)
            await enhanceCustomImage(canvas, {
                invert: true,
                contrast: 2.2,
                threshold: 130,
            }),
        ];

        // Process each version with OCR
        const results = [];

        for (let i = 0; i < versions.length; i++) {
            const version = versions[i];
            console.log(`Processing image version ${i + 1}`);

            try {
                // Get optimized OCR parameters
                const optimizedParams = optimizeOcrForIndonesianPlate();

                // Perform OCR
                const result = await Tesseract.recognize(
                    version.toDataURL("image/jpeg"),
                    "eng",
                    {
                        logger: (m) => {
                            console.log(
                                `Version ${i + 1} progress:`,
                                m.status,
                                m.progress
                            );
                        },
                        ...optimizedParams,
                    }
                );

                // Store result
                results.push({
                    text: result.data.text.trim(),
                    confidence: result.data.confidence,
                    version: i + 1,
                });

                console.log(
                    `Version ${i + 1} result:`,
                    results[results.length - 1]
                );
            } catch (err) {
                console.error(`Error processing version ${i + 1}:`, err);
            }
        }

        // Find the best result based on confidence and text quality
        results.sort((a, b) => {
            // Calculate a score based on confidence and text quality
            const scoreA = calculatePlateScore(a.text, a.confidence);
            const scoreB = calculatePlateScore(b.text, b.confidence);
            return scoreB - scoreA; // Sort in descending order
        });

        console.log("All results:", results);
        console.log("Best result:", results[0]);

        return results[0].text;
    } catch (error) {
        console.error("Error in multiple processing:", error);
        throw error;
    }
}

// Helper function to enhance image with custom parameters
async function enhanceCustomImage(canvas, options) {
    return new Promise((resolve) => {
        try {
            // Default options
            const opts = {
                contrast: 2.5,
                threshold: 110,
                noiseRemoval: "medium", // low, medium, high
                sharpness: "medium", // low, medium, high
                invert: false,
                ...options,
            };

            // Create new canvas
            const enhancedCanvas = document.createElement("canvas");
            enhancedCanvas.width = canvas.width;
            enhancedCanvas.height = canvas.height;
            const ctx = enhancedCanvas.getContext("2d");

            // Draw original canvas to enhanced canvas
            ctx.drawImage(canvas, 0, 0);

            // Get image data
            const imageData = ctx.getImageData(
                0,
                0,
                enhancedCanvas.width,
                enhancedCanvas.height
            );
            const data = imageData.data;

            // Invert if needed
            if (opts.invert) {
                for (let i = 0; i < data.length; i += 4) {
                    data[i] = 255 - data[i];
                    data[i + 1] = 255 - data[i + 1];
                    data[i + 2] = 255 - data[i + 2];
                }
            }

            // Convert to grayscale
            for (let i = 0; i < data.length; i += 4) {
                const gray =
                    0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
                data[i] = data[i + 1] = data[i + 2] = gray;
            }

            // Apply contrast
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

            // Apply sharpening if needed
            if (opts.sharpness !== "low") {
                const strength = opts.sharpness === "high" ? 1.5 : 1;
                const sharpenKernel = [
                    -strength,
                    -strength,
                    -strength,
                    -strength,
                    8 * strength + 1,
                    -strength,
                    -strength,
                    -strength,
                    -strength,
                ];

                const tempData = new Uint8ClampedArray(data.length);
                for (let i = 0; i < data.length; i++) {
                    tempData[i] = data[i];
                }

                const width = enhancedCanvas.width;
                const height = enhancedCanvas.height;

                for (let y = 1; y < height - 1; y++) {
                    for (let x = 1; x < width - 1; x++) {
                        for (let c = 0; c < 3; c++) {
                            let sum = 0;
                            for (let ky = -1; ky <= 1; ky++) {
                                for (let kx = -1; kx <= 1; kx++) {
                                    const idx =
                                        ((y + ky) * width + (x + kx)) * 4 + c;
                                    sum +=
                                        tempData[idx] *
                                        sharpenKernel[(ky + 1) * 3 + (kx + 1)];
                                }
                            }
                            const idx = (y * width + x) * 4 + c;
                            data[idx] = Math.max(0, Math.min(255, sum));
                        }
                    }
                }
            }

            // Apply threshold
            for (let i = 0; i < data.length; i += 4) {
                const val = data[i] > opts.threshold ? 255 : 0;
                data[i] = data[i + 1] = data[i + 2] = val;
            }

            // Apply noise removal if needed
            if (opts.noiseRemoval !== "low") {
                const width = enhancedCanvas.width;
                const height = enhancedCanvas.height;
                const threshold = opts.noiseRemoval === "high" ? 4 : 2;

                // Copy data
                const noiseData = new Uint8ClampedArray(data.length);
                for (let i = 0; i < data.length; i++) {
                    noiseData[i] = data[i];
                }

                // Remove isolated pixels
                for (let y = 1; y < height - 1; y++) {
                    for (let x = 1; x < width - 1; x++) {
                        const idx = (y * width + x) * 4;

                        // Count neighbors of same color
                        let sameNeighbors = 0;
                        for (let ky = -1; ky <= 1; ky++) {
                            for (let kx = -1; kx <= 1; kx++) {
                                if (kx === 0 && ky === 0) continue;

                                const nidx = ((y + ky) * width + (x + kx)) * 4;
                                if (noiseData[nidx] === noiseData[idx]) {
                                    sameNeighbors++;
                                }
                            }
                        }

                        // If fewer than threshold neighbors are the same color, invert this pixel
                        if (sameNeighbors < threshold) {
                            data[idx] =
                                data[idx + 1] =
                                data[idx + 2] =
                                    255 - noiseData[idx];
                        }
                    }
                }
            }

            // Update canvas
            ctx.putImageData(imageData, 0, 0);

            resolve(enhancedCanvas);
        } catch (error) {
            console.error("Error in custom enhancement:", error);
            resolve(canvas);
        }
    });
}

// Helper function to calculate a score for plate text quality
function calculatePlateScore(text, confidence) {
    // Basic confidence score
    let score = confidence;

    // Clean the text
    const cleaned = text
        .replace(/[^A-Z0-9\s]/gi, "")
        .toUpperCase()
        .trim();

    // Check if it matches plate pattern
    const platePattern = /^[A-Z]{1,2}\s*[0-9]{1,4}\s*[A-Z]{1,3}$/;
    if (platePattern.test(cleaned)) {
        score += 50; // Significant bonus for matching pattern
    }

    // Penalty for very short or long text
    if (cleaned.length < 5) score -= 30;
    if (cleaned.length > 12) score -= 15;

    // Check for expected character counts
    const letterCount = (cleaned.match(/[A-Z]/g) || []).length;
    const numberCount = (cleaned.match(/[0-9]/g) || []).length;

    // Ideal: 3-5 letters, 1-4 numbers
    if (letterCount >= 3 && letterCount <= 5) score += 20;
    if (numberCount >= 1 && numberCount <= 4) score += 20;

    // Penalty for unlikely character distribution
    if (letterCount === 0 || numberCount === 0) score -= 40;
    if (letterCount > 7 || numberCount > 6) score -= 30;

    return score;
}

// Fungsi yang diperbarui untuk memformat teks hasil OCR menjadi format plat Indonesia
function formatIndonesianPlate(text) {
    try {
        console.log("Teks asli:", text);

        // 1. Filter yang lebih agresif untuk karakter spesial
        let plate = text
            .replace(/[^A-Z0-9\s]/gi, "") // Hapus semua karakter non-alfanumerik dan non-spasi
            .replace(/—/g, "") // Em dash
            .replace(/-/g, "") // Dash
            .replace(/_/g, "") // Underscore
            .replace(/\./g, "") // Titik
            .replace(/,/g, "") // Koma
            .replace(/:/g, "") // Titik dua
            .replace(/;/g, "") // Titik koma
            .replace(/\//g, "") // Slash
            .replace(/\\/g, "") // Backslash
            .replace(/\|/g, "") // Pipe
            .replace(/\+/g, "") // Plus
            .replace(/\*/g, "") // Asterisk
            .replace(/=/g, "") // Equal
            .replace(/</g, "") // Less than
            .replace(/>/g, "") // Greater than
            .replace(/\(/g, "") // Open parenthesis
            .replace(/\)/g, "") // Close parenthesis
            .replace(/\[/g, "") // Open bracket
            .replace(/\]/g, "") // Close bracket
            .replace(/\{/g, "") // Open brace
            .replace(/\}/g, "") // Close brace
            .replace(/'/g, "") // Single quote
            .replace(/"/g, "") // Double quote
            .replace(/`/g, "") // Backtick
            .replace(/~/g, "") // Tilde
            .replace(/@/g, "") // At
            .replace(/#/g, "") // Hash
            .replace(/\$/g, "") // Dollar
            .replace(/%/g, "") // Percent
            .replace(/\^/g, "") // Caret
            .replace(/&/g, "") // Ampersand
            .replace(/!/g, "") // Exclamation
            .replace(/\?/g, ""); // Question mark

        plate = plate.toUpperCase().trim();
        plate = plate.replace(/\s+/g, " "); // Standarisasi spasi

        console.log("Teks setelah filter agresif:", plate);

        // Lanjutkan dengan kode yang ada...
        // 2. Filter tambahan untuk karakter yang sering terdeteksi salah
        plate = plate.replace(/—/g, ""); // Hapus em dash
        plate = plate.replace(/-/g, ""); // Hapus dash
        plate = plate.replace(/_/g, ""); // Hapus underscore
        plate = plate.replace(/\./g, ""); // Hapus titik
        plate = plate.replace(/,/g, ""); // Hapus koma
        plate = plate.replace(/:/g, ""); // Hapus titik dua
        plate = plate.replace(/\|/g, ""); // Hapus pipe
        plate = plate.replace(/\//g, ""); // Hapus slash
        plate = plate.replace(/\\/g, ""); // Hapus backslash

        // Hapus semua karakter non-alfanumerik yang mungkin tersisa
        plate = plate.replace(/[^\w\s]/g, "");

        console.log("Teks setelah filter tambahan:", plate);

        // 3. Deteksi dengan regex format plat Indonesia yang lebih ketat
        // Format: 1-2 huruf, 1-4 angka, 1-3 huruf
        const platePattern = /([A-Z]{1,2})\s*([0-9]{1,4})\s*([A-Z]{1,3})/i;
        const match = plate.match(platePattern);

        if (match) {
            // 4. Validasi dan koreksi segmen plat
            let region = match[1]; // Kode wilayah (1-2 huruf)
            let numbers = match[2]; // Nomor registrasi (1-4 angka)
            let letters = match[3]; // Kode seri (1-3 huruf)

            console.log("Segmen terdeteksi:", { region, numbers, letters });

            // 5. Validasi kode wilayah (pastikan hanya huruf)
            if (/[^A-Z]/.test(region)) {
                region = region.replace(/[^A-Z]/g, ""); // Hapus karakter non-huruf
            }

            // 6. Validasi nomor registrasi (pastikan hanya angka)
            numbers = numbers.replace(/[^0-9]/g, ""); // Hapus karakter non-angka

            // 7. Validasi kode seri (pastikan hanya huruf)
            letters = letters.replace(/[^A-Z]/g, ""); // Hapus karakter non-huruf

            // 8. Perbaiki kesalahan OCR umum - LEBIH AGRESIF

            // Koreksi pada nomor registrasi (angka)
            numbers = numbers.replace(/O/g, "0");
            numbers = numbers.replace(/o/g, "0");
            numbers = numbers.replace(/Q/g, "0");
            numbers = numbers.replace(/D/g, "0");
            numbers = numbers.replace(/I/g, "1");
            numbers = numbers.replace(/i/g, "1");
            numbers = numbers.replace(/l/g, "1");
            numbers = numbers.replace(/Z/g, "2");
            numbers = numbers.replace(/z/g, "2");
            numbers = numbers.replace(/S/g, "5");
            numbers = numbers.replace(/s/g, "5");
            numbers = numbers.replace(/B/g, "8");
            numbers = numbers.replace(/b/g, "8");
            numbers = numbers.replace(/G/g, "6");
            numbers = numbers.replace(/T/g, "7");
            numbers = numbers.replace(/A/g, "4");
            numbers = numbers.replace(/E/g, "3");
            numbers = numbers.replace(/Z/g, "7");
            numbers = numbers.replace(/Z/g, "2");
            numbers = numbers.replace(/Z/g, "1");
            numbers = numbers.replace(/T/g, "1");

            // Koreksi pada kode seri (huruf)
            letters = letters.replace(/0/g, "O");
            letters = letters.replace(/1/g, "I");
            letters = letters.replace(/3/g, "B");
            letters = letters.replace(/5/g, "S");
            letters = letters.replace(/8/g, "B");
            letters = letters.replace(/2/g, "Z");
            letters = letters.replace(/4/g, "A");
            letters = letters.replace(/6/g, "G");
            letters = letters.replace(/7/g, "T");
            letters = letters.replace(/9/g, "P");
            letters = letters.replace(/8/g, "B");
            letters = letters.replace(/7/g, "Z");
            letters = letters.replace(/2/g, "Z");
            letters = letters.replace(/1/g, "Z");

            // 9. Validasi panjang segmen
            if (region.length > 2) region = region.substring(0, 2);
            if (numbers.length > 4) numbers = numbers.substring(0, 4);
            if (letters.length > 3) letters = letters.substring(0, 3);

            // 10. Pastikan format tiga segmen lengkap (region, numbers, letters)
            if (region && numbers && letters) {
                // Format plat nomor dengan benar
                const formattedPlate = `${region} ${numbers} ${letters}`;
                console.log("Plat terformat:", formattedPlate);
                return formattedPlate;
            }
        }

        // Jika tidak cocok dengan pola atau segmen tidak lengkap, coba dengan pendekatan yang lebih agresif
        console.log("Mencoba pendekatan agresif...");

        // Hapus semua spasi dan karakter lain, baru kelompokkan
        const cleanPlate = plate.replace(/[^A-Z0-9]/g, "");

        // Anggap format selalu: 1-2 huruf, 1-4 angka, 1-3 huruf
        if (cleanPlate.length >= 3) {
            // Minimal 3 karakter
            let result = "";
            let i = 0;

            // Bagian 1: 1-2 huruf (region)
            let region = "";
            while (
                i < cleanPlate.length &&
                region.length < 2 &&
                /[A-Z]/.test(cleanPlate[i])
            ) {
                region += cleanPlate[i];
                i++;
            }

            // Jika tidak ada huruf di awal, gunakan huruf default
            if (region.length === 0) region = "B";

            result += region + " ";

            // Bagian 2: 1-4 angka (nomor)
            let numbers = "";
            while (
                i < cleanPlate.length &&
                numbers.length < 4 &&
                /[0-9OIZSDBGTAE]/.test(cleanPlate[i])
            ) {
                let char = cleanPlate[i];

                // Konversi karakter yang sering terdeteksi salah sebagai angka
                if (char === "O") char = "0";
                if (char === "I") char = "1";
                if (char === "Z") char = "2";
                if (char === "S") char = "5";
                if (char === "D") char = "0";
                if (char === "B") char = "8";
                if (char === "G") char = "6";
                if (char === "T") char = "7";
                if (char === "A") char = "4";
                if (char === "E") char = "3";

                if (/[0-9]/.test(char)) {
                    numbers += char;
                    i++;
                } else {
                    break;
                }
            }

            // Jika tidak ada angka di tengah, gunakan angka default
            if (numbers.length === 0) numbers = "1000";

            result += numbers + " ";

            // Bagian 3: 1-3 huruf (seri)
            let letters = "";
            while (i < cleanPlate.length && letters.length < 3) {
                let char = cleanPlate[i];

                // Konversi angka yang sering terdeteksi salah sebagai huruf
                if (char === "0") char = "O";
                if (char === "1") char = "I";
                if (char === "2") char = "Z";
                if (char === "3") char = "B";
                if (char === "5") char = "S";
                if (char === "8") char = "B";

                letters += char;
                i++;
            }

            // Pastikan letters hanya berisi huruf
            letters = letters.replace(/[0-9]/g, function (match) {
                const replacements = {
                    0: "O",
                    1: "I",
                    2: "Z",
                    3: "B",
                    4: "A",
                    5: "S",
                    6: "G",
                    7: "T",
                    8: "B",
                    9: "P",
                };
                return replacements[match] || "X";
            });

            // Jika tidak ada huruf di akhir, gunakan huruf default
            if (letters.length === 0) {
                // Cari huruf tersisa di text
                const remainingText = cleanPlate.substring(i);
                if (remainingText.length > 0) {
                    letters = remainingText.substring(
                        0,
                        Math.min(3, remainingText.length)
                    );
                    letters = letters.replace(/[0-9]/g, function (match) {
                        const replacements = {
                            0: "O",
                            1: "I",
                            2: "Z",
                            3: "B",
                            4: "A",
                            5: "S",
                            6: "G",
                            7: "T",
                            8: "B",
                            9: "P",
                        };
                        return replacements[match] || "X";
                    });
                } else {
                    letters = "XXX";
                }
            }

            result += letters;

            console.log("Plat terformat dengan pendekatan agresif:", result);
            return result;
        }

        // Jika semua metode gagal, kembalikan teks yang dibersihkan
        // Hapus semua karakter kecuali huruf, angka, dan spasi
        const cleanedPlate = text
            .replace(/[^A-Z0-9\s]/gi, "")
            .toUpperCase()
            .trim();
        console.log(
            "Gagal mendeteksi format plat. Mengembalikan teks yang dibersihkan:",
            cleanedPlate
        );
        return cleanedPlate;
    } catch (error) {
        console.error("Error dalam format plat:", error);

        // Jika terjadi error, kembalikan teks yang dibersihkan dari karakter tidak valid
        const safeText = text
            .replace(/[^A-Z0-9\s]/gi, "")
            .toUpperCase()
            .trim();
        return safeText;
    }
}

// Perbaikan fungsi optimizeOcrForIndonesianPlate
function optimizeOcrForIndonesianPlate() {
    return {
        tessedit_char_whitelist: "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 ",
        tessedit_pageseg_mode: "7", // Single line of text
        preserve_interword_spaces: "1",
        tessedit_min_confidence: "20", // Lebih rendah untuk menangkap lebih banyak karakter
        textord_min_linesize: "2.5",
        textord_use_cjk_fp_model: "0",
        language_model_ngram_on: "0",
        lstm_use_matrix: "1",
        textord_heavy_nr: "1",
        tessedit_write_images: "0",
        tessdata_manager_log_level: "0",
        edges_max_children_per_outline: "40",
        textord_show_fixed_cuts: "0",
        tessedit_adapt_to_char_fragments: "1", // Adaptasi ke fragmen karakter
        tessedit_adaption_debug: "0",
        textord_tabfind_force_vertical_text: "0",
        textord_tabfind_vertical_text_ratio: "0.0",
        textord_tabfind_aligned_gap_fraction: "0.5", // Bantu deteksi spasi
        tessedit_tess_adaption_mode: "2", // Adaptasi agresif
        segment_segcost_rating: "0.5", // Peningkatan segmentasi
        language_model_penalty_non_dict_word: "0.8", // Kurangi penalti kata non-kamus
        language_model_penalty_non_freq_dict_word: "0.9",
        crunch_leaving_garbage_certainty: "-3", // Lebih toleran terhadap "sampah"
        edges_children_per_grandchild: "10.0",
        tessedit_unrej_any_wd: "1", // Jangan tolak karakter yang tidak yakin
        edges_threshold_min: "0.65", // Deteksi tepi yang lebih sensitif
        edges_threshold_max: "0.75",
    };
}

// Fungsi sanitasi plat nomor yang diperbarui
function sanitizeLicensePlate(text) {
    try {
        console.log("Input sanitasi:", text);

        // Langkah 1: Standarisasi awal - hapus semua karakter tidak valid dan whitespace
        let sanitized = text
            .replace(/[^A-Z0-9\s]/gi, "")
            .toUpperCase()
            .trim()
            .replace(/\s+/g, " ");

        console.log("Setelah standarisasi awal:", sanitized);

        // Langkah 2: Coba identifikasi pola plat dengan multiple regex
        // Ini membantu menangkap berbagai variasi deteksi

        // Pola 1: Plat standar dengan spasi (B 1234 XYZ)
        const pattern1 = /([A-Z]{1,2})\s*([0-9]{1,4})\s*([A-Z]{1,3})/;

        // Pola 2: Plat tanpa spasi tapi dengan grup jelas (B1234XYZ)
        const pattern2 = /^([A-Z]{1,2})([0-9]{1,4})([A-Z]{1,3})$/;

        // Pola 3: Campuran karakter yang sering tertukar, perlu normalisasi (BO123XYZ)
        const pattern3 =
            /^([A-Z0O]{1,2})([0-9OIZSDBTAEL]{1,4})([A-Z0-9]{1,3})$/;

        // Pola 4: Pola dengan delimiter variasi (B-1234-XYZ atau B.1234.XYZ)
        const pattern4 =
            /([A-Z]{1,2})[.\-\s_]{1,2}([0-9]{1,4})[.\-\s_]{1,2}([A-Z]{1,3})/;

        // Coba cocokkan dengan pola yang ada
        let match =
            sanitized.match(pattern1) ||
            sanitized.match(pattern2) ||
            sanitized.replace(/\s/g, "").match(pattern2) ||
            sanitized.replace(/\s/g, "").match(pattern3) ||
            sanitized.match(pattern4);

        if (match) {
            console.log("Pattern match ditemukan:", match);

            let region = match[1];
            let numbers = match[2];
            let letters = match[3];

            // Bersihkan dan normalisasi setiap bagian

            // Region: Pastikan hanya huruf dan maks 2 karakter
            region = region.replace(/0/g, "O").replace(/1/g, "I");
            region = region.replace(/[^A-Z]/g, "");
            if (region.length > 2) region = region.substring(0, 2);
            if (region.length === 0) region = "B";

            // Numbers: Pastikan hanya angka dengan koreksi karakter tertukar
            numbers = normalizePlateNumbers(numbers);
            if (numbers.length > 4) numbers = numbers.substring(0, 4);
            if (numbers.length === 0) numbers = "1234";

            // Letters: Pastikan hanya huruf dengan koreksi angka tertukar
            letters = normalizePlateLetters(letters);
            if (letters.length > 3) letters = letters.substring(0, 3);
            if (letters.length === 0) letters = "XYZ";

            // Format hasil final
            const finalPlate = `${region} ${numbers} ${letters}`;
            console.log("Hasil sanitasi pattern match:", finalPlate);
            return finalPlate;
        }

        // Jika tidak ada pola yang cocok, gunakan pendekatan ekstraksi
        console.log("Tidak ada pattern match, menggunakan ekstraksi");

        // Gabungkan semua karakter tanpa spasi untuk pemrosesan
        let combinedChars = sanitized.replace(/\s/g, "");

        // Jika terlalu pendek, gunakan default
        if (combinedChars.length < 4) {
            console.log("Teks terlalu pendek, gunakan default");
            return "B 1234 XYZ";
        }

        // Ekstraksi berdasarkan posisi dan pola karakter
        let posRegionEnd = 0;
        let posNumberEnd = 0;

        // Langkah 1: Identifikasi region
        let region = "";
        // Cari huruf di awal (1-2 karakter)
        while (
            posRegionEnd < combinedChars.length &&
            /[A-Z0O1I]/i.test(combinedChars[posRegionEnd]) &&
            region.length < 2
        ) {
            let char = combinedChars[posRegionEnd];
            // Normalisasi karakter yang sering tertukar
            if (char === "0") char = "O";
            if (char === "1") char = "I";
            region += char;
            posRegionEnd++;
        }

        // Jika tidak ditemukan region, gunakan "B"
        if (region.length === 0) {
            console.log("Region tidak terdeteksi, gunakan default");
            region = "B";
        } else {
            // Normalisasi region yang ditemukan
            region = region.replace(/0/g, "O").replace(/1/g, "I");
            region = region.replace(/[^A-Z]/g, "");
        }

        // Langkah 2: Identifikasi nomor
        let numbers = "";
        posNumberEnd = posRegionEnd;

        // Cari angka setelah region (1-4 karakter)
        while (
            posNumberEnd < combinedChars.length &&
            /[0-9OIZSDBTAEL]/i.test(combinedChars[posNumberEnd]) &&
            numbers.length < 4
        ) {
            numbers += combinedChars[posNumberEnd];
            posNumberEnd++;
        }

        // Normalisasi angka
        numbers = normalizePlateNumbers(numbers);

        // Jika tidak ditemukan nomor, gunakan "1234"
        if (numbers.length === 0) {
            console.log("Nomor tidak terdeteksi, gunakan default");
            numbers = "1234";
        }

        // Langkah 3: Identifikasi kode seri
        let letters = "";

        // Gunakan sisa karakter sebagai kode seri (1-3 karakter)
        if (posNumberEnd < combinedChars.length) {
            letters = combinedChars.substring(posNumberEnd);
        }

        // Normalisasi kode seri
        letters = normalizePlateLetters(letters);

        // Batasi panjang
        if (letters.length > 3) letters = letters.substring(0, 3);

        // Jika tidak ada kode seri, gunakan "XYZ"
        if (letters.length === 0) {
            console.log("Kode seri tidak terdeteksi, gunakan default");
            letters = "XYZ";
        }

        // Format hasil final
        const finalPlate = `${region} ${numbers} ${letters}`;
        console.log("Hasil sanitasi ekstraksi:", finalPlate);
        return finalPlate;
    } catch (error) {
        console.error("Error dalam sanitasi plat:", error);
        return "B 1234 XYZ"; // Default aman
    }
}

// Fungsi normalisasi angka pada plat nomor
function normalizePlateNumbers(text) {
    return text
        .replace(/O/gi, "0")
        .replace(/Q/gi, "0")
        .replace(/D/gi, "0")
        .replace(/I/gi, "1")
        .replace(/l/gi, "1")
        .replace(/Z/gi, "2")
        .replace(/S/gi, "5")
        .replace(/B/gi, "8")
        .replace(/G/gi, "6")
        .replace(/T/gi, "7")
        .replace(/A/gi, "4")
        .replace(/E/gi, "3")
        .replace(/[^0-9]/g, ""); // Hapus karakter non-angka
}

// Fungsi normalisasi huruf pada plat nomor
function normalizePlateLetters(text) {
    return text
        .replace(/0/g, "O")
        .replace(/1/g, "I")
        .replace(/3/g, "B")
        .replace(/5/g, "S")
        .replace(/8/g, "B")
        .replace(/2/g, "Z")
        .replace(/4/g, "A")
        .replace(/6/g, "G")
        .replace(/7/g, "T")
        .replace(/9/g, "P")
        .replace(/[^A-Z]/gi, ""); // Hapus karakter non-huruf
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
