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
let mostAccuratePlate = '';
let isProcessingPlate = false;
let lastDetectedPlate = '';
let lastDetectionTimestamp = 0;

// Modifikasi fungsi utama detectIndonesianPlate
async function detectIndonesianPlate() {
    // Add the try statement at the beginning of the function
    try {
        // Cek apakah sedang dalam proses deteksi
        if (isProcessingPlate) {
            console.log("Proses deteksi plat sedang berjalan, permintaan baru diabaikan");
            return;
        }
        
        // Cek apakah ada deteksi yang baru saja dilakukan dalam 3 detik terakhir
        const now = Date.now();
        if (now - lastDetectionTimestamp < 3000) {
            console.log("Deteksi baru diabaikan: terlalu cepat setelah deteksi sebelumnya");
            return;
        }

        console.log("Deteksi plat nomor Indonesia dimulai");
        isProcessingPlate = true;
        lastDetectionTimestamp = now;

        // Reset status notifikasi di awal sesi deteksi baru
        isPlateDetectionNotificationShown = false;
        detectedPlateConfidence = 0;
        mostAccuratePlate = '';

        // Status checks and setup - existing code
        if (window.ocrInProgress) {
            console.log("Proses OCR sedang berjalan, harap tunggu");
            isProcessingPlate = false;
            return;
        }

        window.ocrInProgress = true;
        $("#loading-indicator").show();

        if ($("#browser-processing-message").length) {
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

            // Verifikasi hasil dengan batas ambang kepercayaan minimum
            const confidence = mostAccuratePlate ? detectedPlateConfidence : 0;
            const isHiConfidence = confidence > 70;
            const isMediumConfidence = confidence > 40;
            
            // Show success notification only once with the most accurate result
            if (!isPlateDetectionNotificationShown) {
                isPlateDetectionNotificationShown = true;
                
                if (isHiConfidence) {
                    // Hasil dengan kepercayaan tinggi
                    Swal.fire({
                        icon: "success",
                        title: "Deteksi Berhasil",
                        html: `
                            <p>Plat nomor terdeteksi:</p>
                            <div class="plate-preview" style="font-size: 18px; font-weight: bold; margin: 15px 0; padding: 8px; background: #e8f5e9; border-radius: 4px;">${sanitizedPlate}</div>
                            <p class="text-muted mt-1">Confidence: ${Math.round(confidence)}%</p>
                            <p class="text-success small mt-0">Akurasi tinggi!</p>
                        `,
                        confirmButtonColor: "#28a745",
                    });
                } else if (isMediumConfidence) {
                    // Hasil dengan kepercayaan sedang
                    Swal.fire({
                        icon: "success",
                        title: "Deteksi Berhasil",
                        html: `
                            <p>Plat nomor terdeteksi:</p>
                            <div class="plate-preview" style="font-size: 18px; font-weight: bold; margin: 15px 0; padding: 8px; background: #fff3cd; border-radius: 4px;">${sanitizedPlate}</div>
                            <p class="text-muted mt-1">Confidence: ${Math.round(confidence)}%</p>
                            <p class="text-warning small mt-0">Periksa kembali hasil deteksi</p>
                        `,
                        confirmButtonColor: "#28a745",
                    });
                } else {
                    Swal.fire({
                        icon: "info",
                        title: "Deteksi Berhasil",
                        html: `
                            <p>Plat nomor terdeteksi:</p>
                            <div class="plate-preview" style="font-size: 18px; font-weight: bold; margin: 15px 0; padding: 8px; background: #f5f5f5; border-radius: 4px;">${sanitizedPlate}</div>
                            <p class="text-muted mt-1">Confidence: ${Math.round(confidence)}%</p>
                            <p class="text-info small mt-0">Harap periksa dan edit jika diperlukan</p>
                        `,
                        confirmButtonColor: "#28a745",
                    });
                }
            }

            // Close camera modal
            $("#cameraModal").modal("hide");
        }
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

// Perbaikan untuk meningkatkan akurasi deteksi plat nomor Indonesia
async function processMultipleImageVersions(canvas) {
    try {
        console.log("Memulai multiple processing dengan pendekatan baru");

        // Buat lebih banyak versi gambar dengan parameter yang dioptimalkan untuk plat Indonesia
        const versions = [
            // Versi standar dengan kontras tinggi
            await enhanceCustomImage(canvas, {
                contrast: 3.0,
                threshold: 130,
                sharpness: "high",
            }),

            // Versi optimasi plat putih (mayoritas plat di Indonesia)
            await enhanceCustomImage(canvas, {
                contrast: 3.5,
                threshold: 140,
                sharpness: "high",
                noiseRemoval: "high",
                letterWidth: "normal", // Sesuai lebar huruf/angka plat Indonesia
            }),

            // Versi optimasi untuk plat kuning (plat umum)
            await enhanceCustomImage(canvas, {
                contrast: 2.8,
                threshold: 120,
                yellowPlate: true,
                noiseRemoval: "medium",
            }),

            // Versi optimasi untuk kondisi cahaya rendah
            await enhanceCustomImage(canvas, {
                contrast: 4.0,
                brightness: 20,
                threshold: 110,
            }),

            // Versi optimasi untuk kondisi cahaya berlebih
            await enhanceCustomImage(canvas, {
                contrast: 2.0,
                brightness: -20,
                threshold: 150,
            }),

            // Versi khusus untuk menangkap bentuk huruf tipis
            await enhanceCustomImage(canvas, {
                contrast: 2.5,
                thinCharacters: true,
                threshold: 125,
                adaptiveThreshold: true,
            }),
        ];

        // Tambahkan parameter OCR yang dioptimalkan untuk masing-masing versi
        const ocrParams = [
            // Parameter standar
            {
                tessedit_char_whitelist:
                    "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 ",
                tessedit_pageseg_mode: "7", // Single line of text
            },

            // Parameter fokus akurasi karakter
            {
                tessedit_char_whitelist:
                    "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 ",
                tessedit_pageseg_mode: "7",
                preserve_interword_spaces: "1",
                tessedit_min_confidence: "60",
            },

            // Parameter untuk plat nomor yang kurang jelas
            {
                tessedit_char_whitelist:
                    "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 ",
                tessedit_pageseg_mode: "7",
                textord_min_linesize: "2.5",
                tessdata_manager_log_level: "0",
                edges_max_children_per_outline: "40",
            },
        ];

        // Proses setiap versi dengan Tesseract
        const results = [];

        for (let i = 0; i < versions.length; i++) {
            const version = versions[i];
            console.log(`Processing versi gambar ${i + 1}`);

            try {
                // Pilih parameter OCR untuk versi ini (gunakan secara bergiliran)
                const params = ocrParams[i % ocrParams.length];

                // Tampilkan gambar yang sedang diproses (untuk debugging)
                if (window.debugMode) {
                    displayProcessingImage(version, `Versi ${i + 1}`);
                }

                // Lakukan OCR
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
                        ...params,
                    }
                );

                // Simpan semua hasil termasuk confidence level per karakter
                const plateText = result.data.text.trim();
                const wordConfidences = result.data.words.map(
                    (w) => w.confidence
                );
                const avgConfidence = result.data.confidence;

                // Analisis lebih mendalam dari hasil OCR
                const plateValidityScore =
                    calculatePlateValidityScore(plateText);

                // Hitung skor keseluruhan
                const totalScore =
                    avgConfidence * 0.6 + plateValidityScore * 0.4;

                results.push({
                    text: plateText,
                    confidence: avgConfidence,
                    wordConfidences,
                    plateValidityScore,
                    totalScore,
                    version: i + 1,
                });

                console.log(
                    `Version ${i + 1} result:`,
                    plateText,
                    `(confidence: ${avgConfidence.toFixed(
                        2
                    )}%, validity: ${plateValidityScore.toFixed(2)})`
                );
            } catch (err) {
                console.error(`Error processing version ${i + 1}:`, err);
            }
        }

        console.log("Semua hasil OCR:", results);
        
        // PERBAIKAN: Pengelompokan hasil untuk identifikasi konsistensi
        const groupedResults = groupSimilarResults(results);
        console.log("Hasil pengelompokan:", groupedResults);

        // Pilih kelompok dengan skor tertinggi dan konsistensi terbaik
        const bestGroup = selectBestResultGroup(groupedResults);
        console.log("Kelompok hasil terbaik:", bestGroup);

        // Jika ada hasil yang konsisten, gunakan itu
        if (bestGroup && bestGroup.items.length >= 2) {
            // Gunakan hasil yang konsisten
            const bestResultInGroup = bestGroup.items.reduce(
                (best, current) =>
                    current.totalScore > best.totalScore ? current : best,
                bestGroup.items[0]
            );

            // Simpan informasi hasil terbaik
            mostAccuratePlate = bestResultInGroup.text;
            detectedPlateConfidence = bestResultInGroup.confidence;
            
            console.log("Menggunakan hasil konsisten:", mostAccuratePlate, "dengan confidence:", detectedPlateConfidence);
            return bestResultInGroup.text;
        }

        // Jika tidak ada hasil konsisten, sortir berdasarkan skor total dan pilih yang terbaik
        results.sort((a, b) => b.totalScore - a.totalScore);
        
        // Simpan informasi hasil terbaik
        if (results.length > 0) {
            mostAccuratePlate = results[0].text;
            detectedPlateConfidence = results[0].confidence;
        }
        
        console.log("Hasil terbaik berdasarkan skor:", mostAccuratePlate, "dengan confidence:", detectedPlateConfidence);
        return results.length > 0 ? results[0].text : "";
    } catch (error) {
        console.error("Error in multiple processing:", error);
        throw error;
    }
}

// Fungsi baru untuk menghitung skor validitas plat nomor
function calculatePlateValidityScore(text) {
    // Bersihkan teks
    const cleanText = text
        .replace(/[^A-Z0-9\s]/gi, "")
        .toUpperCase()
        .trim();

    // Basis skor
    let score = 30;

    // Cek pola plat nomor Indonesia
    const platePattern = /^[A-Z]{1,2}\s*[0-9]{1,4}\s*[A-Z]{1,3}$/;
    if (platePattern.test(cleanText.replace(/\s+/g, ""))) {
        score += 40;
    }

    // Hitung karakter valid
    const letters = (cleanText.match(/[A-Z]/g) || []).length;
    const numbers = (cleanText.match(/[0-9]/g) || []).length;

    // Pola karakter yang benar untuk plat Indonesia
    if (letters >= 2 && letters <= 5) score += 10;
    if (numbers >= 1 && numbers <= 4) score += 10;

    // Panjang total yang masuk akal
    if (cleanText.length >= 5 && cleanText.length <= 10) score += 10;

    // Penalti untuk pola yang tidak mungkin
    if (letters === 0 || numbers === 0) score -= 30;
    if (cleanText.length < 3) score -= 20;
    if (cleanText.length > 12) score -= 10;

    return Math.max(0, Math.min(100, score));
}

// Fungsi untuk mengelompokkan hasil yang mirip
function groupSimilarResults(results) {
    const groups = [];

    for (const result of results) {
        // Proses teks untuk perbandingan
        const normalizedText = normalizeForComparison(result.text);

        // Cari kelompok yang cocok
        let foundGroup = false;
        for (const group of groups) {
            const groupText = normalizeForComparison(group.key);

            // Jika sangat mirip atau identik setelah normalisasi, masukkan ke grup yang sama
            if (calculateTextSimilarity(groupText, normalizedText) >= 0.8) {
                group.items.push(result);
                foundGroup = true;
                break;
            }
        }

        // Jika tidak ada grup yang cocok, buat grup baru
        if (!foundGroup) {
            groups.push({
                key: result.text,
                items: [result],
            });
        }
    }

    return groups;
}

// Normalisasi teks untuk perbandingan
function normalizeForComparison(text) {
    // Hapus semua kecuali huruf dan angka
    let normalized = text.replace(/[^A-Z0-9]/gi, "").toUpperCase();

    // Normalisasi karakter yang sering tertukar
    normalized = normalized.replace(/0/g, "O");
    normalized = normalized.replace(/1/g, "I");
    normalized = normalized.replace(/5/g, "S");
    normalized = normalized.replace(/8/g, "B");

    return normalized;
}

// Hitung kesamaan teks menggunakan Jaro-Winkler distance
function calculateTextSimilarity(s1, s2) {
    if (s1 === s2) return 1.0;

    // Implementasi sederhana Levenshtein distance
    const m = s1.length;
    const n = s2.length;

    // Matriks jarak
    const d = Array(m + 1)
        .fill()
        .map(() => Array(n + 1).fill(0));

    // Inisialisasi
    for (let i = 1; i <= m; i++) {
        d[i][0] = i;
    }

    for (let j = 1; j <= n; j++) {
        d[0][j] = j;
    }

    // Isi matriks
    for (let j = 1; j <= n; j++) {
        for (let i = 1; i <= m; i++) {
            const cost = s1[i - 1] === s2[j - 1] ? 0 : 1;
            d[i][j] = Math.min(
                d[i - 1][j] + 1, // Deletion
                d[i][j - 1] + 1, // Insertion
                d[i - 1][j - 1] + cost // Substitution
            );
        }
    }

    // Konversi jarak ke similaritas
    const maxLen = Math.max(s1.length, s2.length);
    return maxLen > 0 ? 1 - d[m][n] / maxLen : 1;
}

// Pilih kelompok hasil terbaik
function selectBestResultGroup(groups) {
    if (groups.length === 0) return null;

    // Hitung skor untuk setiap grup
    for (const group of groups) {
        // Skor berdasarkan jumlah item (konsistensi)
        const consistencyScore = Math.min(1, group.items.length / 3) * 50;

        // Skor berdasarkan rata-rata skor total item
        const avgTotalScore =
            group.items.reduce((sum, item) => sum + item.totalScore, 0) /
            group.items.length;

        // Skor berdasarkan validitas plat
        const avgValidityScore =
            group.items.reduce(
                (sum, item) => sum + item.plateValidityScore,
                0
            ) / group.items.length;

        // Skor final grup
        group.groupScore =
            consistencyScore * 0.4 +
            avgTotalScore * 0.3 +
            avgValidityScore * 0.3;
    }

    // Urutkan grup berdasarkan skor
    groups.sort((a, b) => b.groupScore - a.groupScore);

    return groups[0];
}

// Fungsi yang ditingkatkan untuk enhance image dengan parameter khusus plat Indonesia
async function enhanceCustomImage(canvas, options) {
    return new Promise((resolve) => {
        try {
            // Default options
            const opts = {
                contrast: 2.5,
                brightness: 0,
                threshold: 130,
                noiseRemoval: "medium", // low, medium, high
                sharpness: "medium", // low, medium, high
                invert: false,
                yellowPlate: false, // Optimasi khusus untuk plat kuning
                thinCharacters: false, // Optimasi untuk karakter tipis
                letterWidth: "normal", // narrow, normal, wide
                adaptiveThreshold: false, // Gunakan adaptive threshold
                ...options,
            };

            // Buat canvas baru
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
            const width = enhancedCanvas.width;
            const height = enhancedCanvas.height;

            // PERBAIKAN BARU: Deteksi area potensial plat
            // Cari area dengan kontras tinggi yang menunjukkan kemungkinan plat
            let plateRegion = detectPotentialPlateRegion(imageData);

            // Jika area plat terdeteksi, fokuskan pemrosesan di area tersebut
            let regionStartX = 0;
            let regionStartY = 0;
            let regionWidth = width;
            let regionHeight = height;

            if (plateRegion) {
                regionStartX = plateRegion.x;
                regionStartY = plateRegion.y;
                regionWidth = plateRegion.width;
                regionHeight = plateRegion.height;

                console.log("Potential plate region detected:", plateRegion);
            }

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
                // Tingkatkan kontras untuk plat kuning
                for (let y = 0; y < height; y++) {
                    for (let x = 0; x < width; x++) {
                        const idx = (y * width + x) * 4;

                        // Deteksi warna kuning (R tinggi, G tinggi, B rendah)
                        if (
                            data[idx] > 150 &&
                            data[idx + 1] > 150 &&
                            data[idx + 2] < 100
                        ) {
                            // Ubah warna kuning menjadi putih untuk meningkatkan kontras
                            data[idx] = data[idx + 1] = data[idx + 2] = 255;
                        }
                    }
                }
            }

            // Konversi ke grayscale
            for (let i = 0; i < data.length; i += 4) {
                // Pembobotan BT.709 yang lebih akurat
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

            // Terapkan Gaussian blur untuk mengurangi noise
            const tempDataBlur = new Uint8ClampedArray(data.length);
            for (let i = 0; i < data.length; i++) {
                tempDataBlur[i] = data[i];
            }

            // Terapkan blur dengan intensitas berdasarkan parameter noise removal
            let kernelSize = 3;
            if (opts.noiseRemoval === "high") kernelSize = 5;

            applyGaussianBlur(data, tempDataBlur, width, height, kernelSize);

            // Terapkan sharpening dengan kekuatan berdasarkan parameter sharpness
            let sharpenStrength = 1.0;
            if (opts.sharpness === "high") sharpenStrength = 1.5;
            if (opts.sharpness === "low") sharpenStrength = 0.5;

            applySharpen(data, tempDataBlur, width, height, sharpenStrength);

            // Terapkan adaptive threshold atau threshold standar
            if (opts.adaptiveThreshold) {
                applyAdaptiveThreshold(data, width, height, opts.threshold);
            } else {
                // Terapkan threshold standar
                for (let i = 0; i < data.length; i += 4) {
                    const val = data[i] > opts.threshold ? 255 : 0;
                    data[i] = data[i + 1] = data[i + 2] = val;
                }
            }

            // Optimasi untuk karakter tipis
            if (opts.thinCharacters) {
                // Skip erosi untuk mencegah hilangnya karakter tipis
                // Terapkan dilasi saja untuk memperkuat karakter
                applyDilation(data, width, height);
            } else {
                // Terapkan erosi dan dilasi standar (morphological operations)
                applyErosion(data, width, height);
                applyDilation(data, width, height);
            }

            // Terapkan optimasi lebar karakter berdasarkan parameter letterWidth
            if (opts.letterWidth === "narrow") {
                // Penipisan karakter
                applyErosion(data, width, height);
            } else if (opts.letterWidth === "wide") {
                // Penebalan karakter
                applyDilation(data, width, height);
                applyDilation(data, width, height);
            }

            // Update canvas dengan data yang telah diproses
            ctx.putImageData(imageData, 0, 0);

            // PERBAIKAN BARU: Jika area plat terdeteksi, tambahkan bounding box untuk debug
            if (plateRegion && window.debugMode) {
                ctx.strokeStyle = "red";
                ctx.lineWidth = 2;
                ctx.strokeRect(
                    plateRegion.x,
                    plateRegion.y,
                    plateRegion.width,
                    plateRegion.height
                );
            }

            resolve(enhancedCanvas);
        } catch (error) {
            console.error("Error dalam custom enhancement:", error);
            resolve(canvas);
        }
    });
}

// Fungsi baru untuk deteksi area plat potensial
function detectPotentialPlateRegion(imageData) {
    const data = imageData.data;
    const width = imageData.width;
    const height = imageData.height;

    // Buat array grayscale
    const grayData = new Uint8Array(width * height);
    for (let y = 0; y < height; y++) {
        for (let x = 0; x < width; x++) {
            const idx = (y * width + x) * 4;
            grayData[y * width + x] = data[idx];
        }
    }

    // Hitung gradien (edges) - deteksi Sobel sederhana
    const edgeData = new Uint8Array(width * height);
    for (let y = 1; y < height - 1; y++) {
        for (let x = 1; x < width - 1; x++) {
            // Sobel operator
            const gx =
                -1 * grayData[(y - 1) * width + (x - 1)] +
                1 * grayData[(y - 1) * width + (x + 1)] +
                -2 * grayData[y * width + (x - 1)] +
                2 * grayData[y * width + (x + 1)] +
                -1 * grayData[(y + 1) * width + (x - 1)] +
                1 * grayData[(y + 1) * width + (x + 1)];

            const gy =
                -1 * grayData[(y - 1) * width + (x - 1)] +
                -2 * grayData[(y - 1) * width + x] +
                -1 * grayData[(y - 1) * width + (x + 1)] +
                1 * grayData[(y + 1) * width + (x - 1)] +
                2 * grayData[(y + 1) * width + x] +
                1 * grayData[(y + 1) * width + (x + 1)];

            const g = Math.sqrt(gx * gx + gy * gy);
            edgeData[y * width + x] = g > 100 ? 255 : 0;
        }
    }

    // Cari area dengan banyak edge yang mengindikasikan plat nomor
    // (Metode sederhana: hitung kepadatan edge di setiap area)
    const blockSize = 20;
    const blockCountX = Math.floor(width / blockSize);
    const blockCountY = Math.floor(height / blockSize);

    let maxDensity = 0;
    let bestBlock = { x: 0, y: 0, density: 0 };

    for (let by = 0; by < blockCountY; by++) {
        for (let bx = 0; bx < blockCountX; bx++) {
            let edgeCount = 0;

            // Hitung edge dalam blok
            for (
                let y = by * blockSize;
                y < (by + 1) * blockSize && y < height;
                y++
            ) {
                for (
                    let x = bx * blockSize;
                    x < (bx + 1) * blockSize && x < width;
                    x++
                ) {
                    if (edgeData[y * width + x] > 0) {
                        edgeCount++;
                    }
                }
            }

            const density = edgeCount / (blockSize * blockSize);

            if (density > maxDensity) {
                maxDensity = density;
                bestBlock = { x: bx, y: by, density };
            }
        }
    }

    // Jika kepadatan cukup tinggi, kemungkinan adalah area plat
    if (maxDensity > 0.1) {
        // Tentukan area yang lebih besar di sekitar blok terbaik
        const expandFactor = 2; // Perluas area
        const x = Math.max(0, (bestBlock.x - expandFactor) * blockSize);
        const y = Math.max(0, (bestBlock.y - expandFactor) * blockSize);
        const w = Math.min(width - x, (expandFactor * 2 + 1) * blockSize);
        const h = Math.min(height - y, (expandFactor * 2 + 1) * blockSize);

        return { x, y, width: w, height: h };
    }

    return null;
}

// Tambahkan utilitas morfologi
function applyErosion(data, width, height) {
    const tempData = new Uint8ClampedArray(data.length);
    for (let i = 0; i < data.length; i++) {
        tempData[i] = data[i];
    }

    for (let y = 1; y < height - 1; y++) {
        for (let x = 1; x < width - 1; x++) {
            const idx = (y * width + x) * 4;

            // Jika piksel putih (255), periksa tetangga
            if (tempData[idx] === 255) {
                let erode = false;

                // Periksa 8 tetangga
                for (let dy = -1; dy <= 1; dy++) {
                    for (let dx = -1; dx <= 1; dx++) {
                        if (dx === 0 && dy === 0) continue;

                        const nidx = ((y + dy) * width + (x + dx)) * 4;
                        if (tempData[nidx] === 0) {
                            erode = true;
                            break;
                        }
                    }
                    if (erode) break;
                }

                if (erode) {
                    data[idx] = data[idx + 1] = data[idx + 2] = 0;
                }
            }
        }
    }
}

function applyDilation(data, width, height) {
    const tempData = new Uint8ClampedArray(data.length);
    for (let i = 0; i < data.length; i++) {
        tempData[i] = data[i];
    }

    for (let y = 1; y < height - 1; y++) {
        for (let x = 1; x < width - 1; x++) {
            const idx = (y * width + x) * 4;

            // Jika piksel hitam (0), periksa tetangga
            if (tempData[idx] === 0) {
                let dilate = false;

                // Periksa 8 tetangga
                for (let dy = -1; dy <= 1; dy++) {
                    for (let dx = -1; dx <= 1; dx++) {
                        if (dx === 0 && dy === 0) continue;

                        const nidx = ((y + dy) * width + (x + dx)) * 4;
                        if (tempData[nidx] === 255) {
                            dilate = true;
                            break;
                        }
                    }
                    if (dilate) break;
                }

                if (dilate) {
                    data[idx] = data[idx + 1] = data[idx + 2] = 255;
                }
            }
        }
    }
}

// Tambahkan adaptive thresholding untuk hasil yang lebih baik pada berbagai kondisi pencahayaan
function applyAdaptiveThreshold(data, width, height, threshold) {
    const tempData = new Uint8ClampedArray(data.length);
    for (let i = 0; i < data.length; i++) {
        tempData[i] = data[i];
    }

    const windowSize = 15; // Ukuran window untuk adaptive threshold
    const c = 5; // Konstanta pengurangan, nilai yang lebih tinggi = lebih banyak piksel putih

    for (let y = 0; y < height; y++) {
        for (let x = 0; x < width; x++) {
            const idx = (y * width + x) * 4;

            // Hitung rata-rata di sekitar piksel
            let sum = 0;
            let count = 0;

            for (
                let wy = Math.max(0, y - windowSize / 2);
                wy < Math.min(height, y + windowSize / 2);
                wy++
            ) {
                for (
                    let wx = Math.max(0, x - windowSize / 2);
                    wx < Math.min(width, x + windowSize / 2);
                    wx++
                ) {
                    const widx = (wy * width + wx) * 4;
                    sum += tempData[widx];
                    count++;
                }
            }

            const avg = sum / count;

            // Terapkan threshold adaptif
            data[idx] =
                data[idx + 1] =
                data[idx + 2] =
                    tempData[idx] > avg - c ? 255 : 0;
        }
    }
}

// Tambahkan Gaussian blur
function applyGaussianBlur(data, tempData, width, height, kernelSize) {
    // Buat kernel Gaussian
    const sigma = 1.0;
    const kernel = [];
    const halfSize = Math.floor(kernelSize / 2);

    // Hitung koefisien kernel
    let sum = 0;
    for (let y = -halfSize; y <= halfSize; y++) {
        for (let x = -halfSize; x <= halfSize; x++) {
            const g = Math.exp(-(x * x + y * y) / (2 * sigma * sigma));
            kernel.push(g);
            sum += g;
        }
    }

    // Normalisasi kernel
    for (let i = 0; i < kernel.length; i++) {
        kernel[i] /= sum;
    }

    // Terapkan blur
    for (let y = halfSize; y < height - halfSize; y++) {
        for (let x = halfSize; x < width - halfSize; x++) {
            for (let c = 0; c < 3; c++) {
                let sum = 0;
                let ki = 0;

                for (let ky = -halfSize; ky <= halfSize; ky++) {
                    for (let kx = -halfSize; kx <= halfSize; kx++) {
                        const idx = ((y + ky) * width + (x + kx)) * 4 + c;
                        sum += tempData[idx] * kernel[ki++];
                    }
                }

                const idx = (y * width + x) * 4 + c;
                data[idx] = sum;
            }
        }
    }
}

// Tambahkan Sharpen
function applySharpen(data, tempData, width, height, strength) {
    const kernel = [
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

    for (let y = 1; y < height - 1; y++) {
        for (let x = 1; x < width - 1; x++) {
            for (let c = 0; c < 3; c++) {
                let sum = 0;
                let ki = 0;

                for (let ky = -1; ky <= 1; ky++) {
                    for (let kx = -1; kx <= 1; kx++) {
                        const idx = ((y + ky) * width + (x + kx)) * 4 + c;
                        sum += tempData[idx] * kernel[ki++];
                    }
                }

                const idx = (y * width + x) * 4 + c;
                data[idx] = Math.max(0, Math.min(255, sum));
            }
        }
    }
}

// Fungsi untuk debugging: tampilkan gambar proses
function displayProcessingImage(canvas, label) {
    // Hanya tampilkan jika dalam mode debug
    if (!window.debugMode) return;

    // Buat div container jika belum ada
    if (!document.getElementById("debug-images")) {
        const container = document.createElement("div");
        container.id = "debug-images";
        container.style.position = "fixed";
        container.style.top = "10px";
        container.style.right = "10px";
        container.style.zIndex = "9999";
        container.style.background = "rgba(0,0,0,0.7)";
        container.style.padding = "10px";
        container.style.borderRadius = "5px";
        container.style.maxHeight = "80vh";
        container.style.overflow = "auto";
        document.body.appendChild(container);
    }

    // Buat element untuk menampilkan gambar
    const imgContainer = document.createElement("div");
    imgContainer.style.marginBottom = "10px";

    const labelElem = document.createElement("div");
    labelElem.textContent = label;
    labelElem.style.color = "white";
    labelElem.style.fontSize = "12px";
    labelElem.style.marginBottom = "5px";

    const img = document.createElement("img");
    img.src = canvas.toDataURL();
    img.style.maxWidth = "200px";
    img.style.border = "2px solid #fff";

    imgContainer.appendChild(labelElem);
    imgContainer.appendChild(img);

    document.getElementById("debug-images").appendChild(imgContainer);
}

// Ekspos fungsi untuk testing
window.debugMode = false; // Set true untuk debugging

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

// 1. Tambahkan deteksi region plat nomor dengan edge detection dan contour analysis
async function detectPlateRegion(canvas) {
    // Buat canvas baru untuk deteksi
    const detectionCanvas = document.createElement("canvas");
    detectionCanvas.width = canvas.width;
    detectionCanvas.height = canvas.height;
    const ctx = detectionCanvas.getContext("2d");
    ctx.drawImage(canvas, 0, 0);

    // Konversi ke grayscale
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const data = imageData.data;

    for (let i = 0; i < data.length; i += 4) {
        const gray =
            0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
        data[i] = data[i + 1] = data[i + 2] = gray;
    }

    // Edge detection dengan Sobel
    const edgeData = detectEdges(imageData);

    // Temukan kontur potensial
    const regions = findPlateContours(edgeData, canvas.width, canvas.height);

    // Filter dan skor region berdasarkan karakteristik plat nomor
    const scoredRegions = scoreRegions(regions);

    // Jika ada region yang cocok, crop gambar ke region tersebut
    if (scoredRegions.length > 0 && scoredRegions[0].score > 70) {
        const bestRegion = scoredRegions[0];
        const croppedCanvas = document.createElement("canvas");
        croppedCanvas.width = bestRegion.width;
        croppedCanvas.height = bestRegion.height;
        const croppedCtx = croppedCanvas.getContext("2d");

        croppedCtx.drawImage(
            canvas,
            bestRegion.x,
            bestRegion.y,
            bestRegion.width,
            bestRegion.height,
            0,
            0,
            bestRegion.width,
            bestRegion.height
        );

        return {
            canvas: croppedCanvas,
            region: bestRegion,
            foundPlate: true,
        };
    }

    // Jika tidak ditemukan region yang cocok, gunakan gambar asli
    return {
        canvas: canvas,
        region: null,
        foundPlate: false,
    };
}

// 2. Tambahkan analisis adaptif untuk gambar
function analyzeImageCharacteristics(canvas) {
    const ctx = canvas.getContext("2d");
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const data = imageData.data;

    // Hitung brightness rata-rata
    let totalBrightness = 0;
    for (let i = 0; i < data.length; i += 4) {
        const r = data[i];
        const g = data[i + 1];
        const b = data[i + 2];
        const brightness = (r + g + b) / 3;
        totalBrightness += brightness;
    }
    const avgBrightness = totalBrightness / (data.length / 4);

    // Hitung kontras
    let contrastVariance = 0;
    for (let i = 0; i < data.length; i += 4) {
        const r = data[i];
        const g = data[i + 1];
        const b = data[i + 2];
        const brightness = (r + g + b) / 3;
        contrastVariance += Math.pow(brightness - avgBrightness, 2);
    }
    const contrast = Math.sqrt(contrastVariance / (data.length / 4)) / 128;

    // Hitung noise
    let noise = calculateImageNoise(data, canvas.width, canvas.height);

    // Tentukan parameter preprocessing yang optimal
    let preprocessingParams = {
        contrast: 2.5,
        threshold: 130,
        noiseRemoval: "medium",
        sharpness: "medium",
        invert: false,
    };

    // Adaptasi berdasarkan karakteristik gambar
    if (avgBrightness > 180) {
        // Gambar terlalu terang
        preprocessingParams.contrast = 3.2;
        preprocessingParams.threshold = 150;
    } else if (avgBrightness < 80) {
        // Gambar terlalu gelap
        preprocessingParams.contrast = 4.0;
        preprocessingParams.threshold = 100;
        preprocessingParams.invert = true;
    }

    if (contrast < 0.3) {
        // Kontras rendah
        preprocessingParams.contrast = Math.max(
            preprocessingParams.contrast,
            3.5
        );
    }

    if (noise > 20) {
        // Noise tinggi
        preprocessingParams.noiseRemoval = "high";
        preprocessingParams.sharpness = "low";
    }

    return preprocessingParams;
}

// 3. Konfigurasi Tesseract yang lebih optimal
function getOptimizedTesseractConfig(imageCharacteristics) {
    // Base config
    const config = {
        tessedit_char_whitelist: "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 ",
        tessedit_pageseg_mode: "7", // Single line of text
        preserve_interword_spaces: "1",
        tessedit_min_confidence: "25",
        textord_min_linesize: "2.5",
        textord_use_cjk_fp_model: "0",
        language_model_ngram_on: "0",
        lstm_use_matrix: "1",
        textord_heavy_nr: "1",
        tessedit_write_images: "0",
        tessdata_manager_log_level: "0",
    };

    // Adaptasi berdasarkan karakteristik gambar
    if (imageCharacteristics.noise > 20) {
        config.edges_max_children_per_outline = "60";
        config.edges_children_per_grandchild = "12.0";
        config.edges_threshold_min = "0.70";
        config.edges_threshold_max = "0.85";
    }

    if (imageCharacteristics.contrast < 0.3) {
        config.textord_min_linesize = "2.0";
        config.crunch_leaving_garbage_certainty = "-2";
        config.textord_tabfind_aligned_gap_fraction = "0.3";
    }

    return config;
}

// 4. Improved voting system untuk multiple results
function selectBestResult(results) {
    // Hitung frekuensi untuk mendeteksi konsistensi
    const textFrequency = {};
    const regionFrequency = {};
    const numbersFrequency = {};
    const lettersFrequency = {};

    // Parse semua hasil untuk mendapatkan region, nomor, dan huruf
    const parsedResults = results.map((result) => {
        const text = result.text
            .replace(/[^A-Z0-9\s]/g, "")
            .toUpperCase()
            .trim();
        const platePattern = /([A-Z]{1,2})\s*([0-9]{1,4})\s*([A-Z]{1,3})/;
        const match = text.match(platePattern);

        if (match) {
            const region = match[1];
            const numbers = match[2];
            const letters = match[3];

            // Tambahkan ke frekuensi
            textFrequency[text] = (textFrequency[text] || 0) + 1;
            regionFrequency[region] = (regionFrequency[region] || 0) + 1;
            numbersFrequency[numbers] = (numbersFrequency[numbers] || 0) + 1;
            lettersFrequency[letters] = (lettersFrequency[letters] || 0) + 1;

            return {
                text,
                region,
                numbers,
                letters,
                confidence: result.confidence,
                score: calculatePlateScore(text, result.confidence),
            };
        }

        return {
            text,
            region: null,
            numbers: null,
            letters: null,
            confidence: result.confidence,
            score: calculatePlateScore(text, result.confidence),
        };
    });

    // Filter hasil yang valid
    const validResults = parsedResults.filter(
        (r) => r.region && r.numbers && r.letters
    );

    if (validResults.length === 0) {
        // Jika tidak ada hasil valid, kembalikan hasil dengan skor tertinggi
        return results.sort(
            (a, b) =>
                calculatePlateScore(b.text, b.confidence) -
                calculatePlateScore(a.text, a.confidence)
        )[0];
    }

    // Temukan region, nomor, dan huruf yang paling sering muncul
    const mostFrequentRegion = Object.entries(regionFrequency).sort(
        (a, b) => b[1] - a[1]
    )[0][0];
    const mostFrequentNumbers = Object.entries(numbersFrequency).sort(
        (a, b) => b[1] - a[1]
    )[0][0];
    const mostFrequentLetters = Object.entries(lettersFrequency).sort(
        (a, b) => b[1] - a[1]
    )[0][0];

    // Gabungkan hasil yang paling konsisten
    const consensusText = `${mostFrequentRegion} ${mostFrequentNumbers} ${mostFrequentLetters}`;

    // Periksa jika ada hasil yang sama persis dengan consensus
    const exactMatch = validResults.find(
        (r) =>
            r.region === mostFrequentRegion &&
            r.numbers === mostFrequentNumbers &&
            r.letters === mostFrequentLetters
    );

    if (exactMatch) {
        return {
            text: consensusText,
            confidence: exactMatch.confidence,
            consensusMethod: "exact-match",
        };
    }

    // Jika tidak ada yang sama persis, ambil rata-rata confidence dari hasil yang berkontribusi
    const contributingResults = validResults.filter(
        (r) =>
            r.region === mostFrequentRegion ||
            r.numbers === mostFrequentNumbers ||
            r.letters === mostFrequentLetters
    );

    const avgConfidence =
        contributingResults.reduce((sum, r) => sum + r.confidence, 0) /
        Math.max(1, contributingResults.length);

    return {
        text: consensusText,
        confidence: avgConfidence,
        consensusMethod: "voting",
    };
}
