import os
import sys
import cv2
import numpy as np
import easyocr
from PIL import Image
import json
from datetime import datetime

# Fungsi untuk logging
def log(message):
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    print(f"[{timestamp}] {message}")

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
    
    # Deteksi plat nomor (crop area)
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    blur = cv2.GaussianBlur(gray, (5, 5), 0)
    thresh = cv2.adaptiveThreshold(blur, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY_INV, 11, 2)
    
    # Cari kontur dan filter untuk menemukan plat nomor
    contours, _ = cv2.findContours(thresh, cv2.RETR_TREE, cv2.CHAIN_APPROX_SIMPLE)
    
    # Filter kontur berdasarkan area dan rasio aspek (plat nomor biasanya persegi panjang)
    possible_plates = []
    for cnt in contours:
        area = cv2.contourArea(cnt)
        if area > 1000:
            x, y, w, h = cv2.boundingRect(cnt)
            aspect_ratio = w / float(h)
            if 2.0 < aspect_ratio < 6.0:
                possible_plates.append((x, y, w, h, area))
    
    # Gunakan area terbesar sebagai plat nomor (asumsi)
    plate_img = img.copy()
    if possible_plates:
        possible_plates.sort(key=lambda x: x[4], reverse=True)
        x, y, w, h, _ = possible_plates[0]
        plate_img = img[y:y+h, x:x+w]
        
    # Preprocessing untuk OCR
    plate_img_gray = cv2.cvtColor(plate_img, cv2.COLOR_BGR2GRAY)
    _, plate_binary = cv2.threshold(plate_img_gray, 0, 255, cv2.THRESH_BINARY + cv2.THRESH_OTSU)
    
    # EasyOCR untuk deteksi plat nomor
    log("Menjalankan EasyOCR untuk deteksi teks...")
    reader = easyocr.Reader(['en'])  # Inisialisasi untuk bahasa Inggris (karakter latin)
    results = reader.readtext(plate_binary)
    
    # Extract hasil OCR
    plate_text = ""
    confidence = 0
    
    if results:
        for (bbox, text, conf) in results:
            plate_text += text + " "
            confidence += conf
        
        confidence /= len(results)  # Average confidence
        plate_text = plate_text.strip().upper()  # Standarkan output
        
        log(f"Text terdeteksi: '{plate_text}' dengan confidence {confidence:.2f}")
    else:
        log("Tidak ada teks terdeteksi")
        plate_text = ""
        confidence = 0
    
    # Format plat nomor Indonesia: Letter Number Letter/Number
    import re
    pattern = r'([A-Z]{1,2})[\s]*(\d{1,4})[\s]*([A-Z]{1,3})'
    match = re.search(pattern, plate_text)
    
    formatted_plate = plate_text
    if match:
        formatted_plate = f"{match.group(1)} {match.group(2)} {match.group(3)}"
    
    # Return hasil
    result = {
        "success": bool(plate_text),
        "plate_number": formatted_plate or "Tidak terdeteksi",
        "confidence": float(confidence),
        "raw_text": plate_text,
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