import os
import sys
import cv2
import numpy as np
import json
from datetime import datetime
import easyocr
import pytesseract
from PIL import Image
import re

# Konfigurasi
CONFIDENCE_THRESHOLD = 0.5

# Fungsi untuk logging
def log(message):
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    print(f"[{timestamp}] {message}")

# Tambahkan fungsi pre-processing ini
def enhance_plate_image(img):
    # Convert to grayscale
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    
    # Increase contrast using CLAHE (Contrast Limited Adaptive Histogram Equalization)
    clahe = cv2.createCLAHE(clipLimit=3.0, tileGridSize=(8,8))
    clahe_img = clahe.apply(gray)
    
    # Bilateral filtering (reduces noise while preserving edges)
    bilateral = cv2.bilateralFilter(clahe_img, 11, 17, 17)
    
    # Adaptive thresholding
    thresh = cv2.adaptiveThreshold(bilateral, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
                                  cv2.THRESH_BINARY_INV, 19, 9)
    
    # Morphological operations to remove noise and fill gaps
    kernel = cv2.getStructuringElement(cv2.MORPH_RECT, (3, 3))
    morph = cv2.morphologyEx(thresh, cv2.MORPH_CLOSE, kernel, iterations=1)
    
    # Dilate to make characters thicker
    dilated = cv2.dilate(morph, kernel, iterations=1)
    
    # Invert back to black text on white background for OCR
    result = cv2.bitwise_not(dilated)
    
    return result

# Cek argumen
if len(sys.argv) < 2:
    print(json.dumps({"success": False, "message": "No image provided"}))
    sys.exit(1)

try:
    # Path gambar input
    image_path = sys.argv[1]
    log(f"Processing image: {image_path}")
    
    # Baca gambar
    img = cv2.imread(image_path)
    if img is None:
        log("Tidak dapat membaca file gambar.")
        print(json.dumps({"success": False, "message": "Unable to read image file"}))
        sys.exit(1)
    
    original_img = img.copy()
    
    # Preprocessing
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    blur = cv2.GaussianBlur(gray, (5, 5), 0)
    
    # Coba deteksi plat nomor
    plate_detected = False
    
    # Metode 1: Deteksi menggunakan threshold dan kontur
    thresh = cv2.adaptiveThreshold(blur, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, 
                                  cv2.THRESH_BINARY_INV, 19, 9)
    
    # Find contours
    contours, _ = cv2.findContours(thresh, cv2.RETR_LIST, cv2.CHAIN_APPROX_SIMPLE)
    
    # Filter untuk plat nomor potensial
    possible_plates = []
    img_h, img_w = img.shape[:2]
    min_area = img_w * img_h * 0.01  # Minimal 1% dari luas gambar
    
    for cnt in contours:
        area = cv2.contourArea(cnt)
        if area > min_area:
            x, y, w, h = cv2.boundingRect(cnt)
            aspect_ratio = w / h
            
            # Plat nomor biasanya memiliki aspect ratio antara 2-5
            if 1.5 < aspect_ratio < 6.0:
                possible_plates.append((x, y, w, h, area))
    
    # Pilih plat berdasarkan area terbesar
    plate_img = img.copy()
    if possible_plates:
        possible_plates.sort(key=lambda x: x[4], reverse=True)
        x, y, w, h, _ = possible_plates[0]
        
        # Expand sedikit untuk memastikan plat terambil sempurna
        y_expand = int(h * 0.1)
        x_expand = int(w * 0.05)
        
        y1 = max(0, y - y_expand)
        y2 = min(img.shape[0], y + h + y_expand)
        x1 = max(0, x - x_expand)
        x2 = min(img.shape[1], x + w + x_expand)
        
        plate_img = img[y1:y2, x1:x2]
        plate_detected = True
        log(f"Plat terdeteksi di koordinat: x={x1}, y={y1}, w={x2-x1}, h={y2-y1}")
    
    # Jika tidak berhasil mendeteksi plat, gunakan gambar asli
    if not plate_detected or plate_img.size == 0:
        log("Tidak dapat mengisolasi plat, menggunakan gambar lengkap")
        plate_img = original_img
    
    # Save the cropped plate (for debugging)
    debug_path = image_path.replace('.jpg', '_plate.jpg')
    cv2.imwrite(debug_path, plate_img)
    
    # Initialize EasyOCR
    log("Initializing EasyOCR...")
    reader = easyocr.Reader(['en'])
    
    # Enhance image for OCR
    plate_img_enhanced = enhance_plate_image(plate_img)

    # Apply additional enhancements for OCR
    _, plate_binary = cv2.threshold(plate_img_enhanced, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    
    # Save enhanced image (for debugging)
    debug_enhanced_path = image_path.replace('.jpg', '_enhanced.jpg')
    cv2.imwrite(debug_enhanced_path, plate_binary)
    
    # OCR with EasyOCR
    log("Running EasyOCR...")
    easyocr_results = reader.readtext(plate_binary)
    easyocr_text = ""
    easyocr_confidence = 0

    if easyocr_results:
        for (bbox, text, conf) in easyocr_results:
            if conf > CONFIDENCE_THRESHOLD:
                easyocr_text += text + " "
                easyocr_confidence += conf
        
        easyocr_text = easyocr_text.strip().upper()
        easyocr_confidence = easyocr_confidence / len(easyocr_results) if easyocr_results else 0

    # OCR with Tesseract
    log("Running Tesseract OCR...")
    try:
        # Convert the OpenCV image to PIL format for Tesseract
        pil_img = Image.fromarray(plate_binary)
        tesseract_text = pytesseract.image_to_string(
            pil_img, 
            config='--psm 7 --oem 1 -c tessedit_char_whitelist="ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 "'
        ).strip().upper()
        log(f"Tesseract result: {tesseract_text}")
    except Exception as e:
        log(f"Tesseract error: {str(e)}")
        tesseract_text = ""

    # Combine results using a simple voting system
    def vote_on_results(results_dict):
        if not results_dict:
            return "", 0
        
        # Simple case: if one engine fails, use the other
        non_empty_results = {k: v for k, v in results_dict.items() if v[0]}
        if len(non_empty_results) == 1:
            engine, (text, conf) = list(non_empty_results.items())[0]
            return text, conf
        
        # Both engines detected text, try to find a common pattern
        texts = [text for text, _ in results_dict.values() if text]
        
        # Check if any text matches license plate pattern
        plate_pattern = re.compile(r'([A-Z]{1,2})\s*(\d{1,4})\s*([A-Z]{1,3})')
        for text in texts:
            if plate_pattern.search(text):
                # Prioritize text that matches plate pattern
                return text, 0.85
        
        # Default: use the longer result as it likely contains more information
        if texts:
            longest_text = max(texts, key=len)
            return longest_text, 0.7
        
        return "", 0

    results_dict = {
        "easyocr": (easyocr_text, easyocr_confidence),
        "tesseract": (tesseract_text, 0.7 if tesseract_text else 0)
    }

    plate_text, confidence = vote_on_results(results_dict)

    # Log all results
    log(f"EasyOCR: {easyocr_text} (conf: {easyocr_confidence:.2f})")
    log(f"Tesseract: {tesseract_text}")
    log(f"Final voted result: {plate_text} (conf: {confidence:.2f})")

    # Return hasil
    result = {
        "success": bool(plate_text),
        "plate_number": plate_text,
        "confidence": float(confidence),
        "raw_text": plate_text,
        "all_results": {
            "easyocr": easyocr_text,
            "tesseract": tesseract_text
        },
        "message": "Plate detection successful" if plate_text else "Could not recognize plate text"
    }
    
    log(f"Hasil akhir: {result}")
    print(json.dumps(result))
    
except Exception as e:
    import traceback
    trace = traceback.format_exc()
    log(f"Error: {str(e)}\n{trace}")
    print(json.dumps({"success": False, "message": str(e), "trace": trace}))
    sys.exit(1)