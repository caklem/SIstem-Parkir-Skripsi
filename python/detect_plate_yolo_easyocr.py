import os
import sys
import json
import torch
import cv2
import easyocr
import numpy as np
from datetime import datetime

# Konfigurasi
CONFIDENCE_THRESHOLD = 0.3
MODEL_PATH = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), 'python', 'models', 'yolov5s.pt')
DEBUG_MODE = True

# Fungsi logging
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
    
    # Simpan gambar original untuk pemrosesan EasyOCR
    original_img = img.copy()
    
    # --- TAHAP 1: DETEKSI LOKASI PLAT DENGAN YOLOV5 ---
    log("Loading YOLOv5 model...")
    model = torch.hub.load('ultralytics/yolov5', 'custom', path=MODEL_PATH)
    
    # Konfigurasi model
    model.conf = CONFIDENCE_THRESHOLD  # Confidence threshold
    model.iou = 0.45  # NMS IoU threshold
    model.classes = [2]  # Kelas 2 adalah 'car' di COCO
    
    # Deteksi objek
    log("Running YOLOv5 detection...")
    results = model(img)
    
    # Dapatkan hasil deteksi
    detections = results.pandas().xyxy[0]
    
    # Debug - simpan gambar dengan bounding box
    if DEBUG_MODE:
        debug_img = img.copy()
        for _, row in detections.iterrows():
            x1, y1, x2, y2 = int(row['xmin']), int(row['ymin']), int(row['xmax']), int(row['ymax'])
            cv2.rectangle(debug_img, (x1, y1), (x2, y2), (0, 255, 0), 2)
            
        debug_path = image_path.replace('.jpg', '_yolo_detection.jpg')
        cv2.imwrite(debug_path, debug_img)
        log(f"Saved debug image to {debug_path}")
    
    # --- TAHAP 2: CROP DAN PREPROCESSING AREA PLAT ---
    log("Processing detected regions...")
    
    plate_candidates = []
    
    # Jika tidak ada deteksi, gunakan gambar lengkap
    if detections.empty:
        log("No car detected, using full image")
        h, w = img.shape[:2]
        plate_candidates.append({
            'img': img,
            'box': [0, 0, w, h],
            'confidence': 1.0
        })
    else:
        # Untuk setiap deteksi (mobil), cari plat nomor potensialnya
        for _, row in detections.iterrows():
            x1, y1, x2, y2 = int(row['xmin']), int(row['ymin']), int(row['xmax']), int(row['ymax'])
            cropped = img[y1:y2, x1:x2]
            
            # Convert to grayscale
            gray = cv2.cvtColor(cropped, cv2.COLOR_BGR2GRAY)
            
            # Noise reduction
            blur = cv2.GaussianBlur(gray, (5, 5), 0)
            
            # Edge detection
            edges = cv2.Canny(blur, 100, 200)
            
            # Find contours
            contours, _ = cv2.findContours(edges.copy(), cv2.RETR_LIST, cv2.CHAIN_APPROX_SIMPLE)
            
            # Sort by area, largest first
            contours = sorted(contours, key=cv2.contourArea, reverse=True)[:5]
            
            for cnt in contours:
                x, y, w, h = cv2.boundingRect(cnt)
                
                # Filter based on aspect ratio (license plates typically have 2:1 to 5:1)
                aspect_ratio = float(w) / h
                area = w * h
                
                if 2.0 <= aspect_ratio <= 5.0 and area > 1000:  # Min area to filter noise
                    plate_img = cropped[y:y+h, x:x+w]
                    
                    # Add candidate
                    plate_candidates.append({
                        'img': plate_img,
                        'box': [x1 + x, y1 + y, x1 + x + w, y1 + y + h],
                        'confidence': row['confidence'] * 0.9  # Slightly lower than car detection confidence
                    })
                    
                    # Debug: save plate candidate
                    if DEBUG_MODE:
                        debug_plate_path = image_path.replace('.jpg', f'_plate_candidate_{len(plate_candidates)}.jpg')
                        cv2.imwrite(debug_plate_path, plate_img)
    
    # Fallback: If no plate candidates found, use the original car crops
    if not plate_candidates:
        log("No plate candidates found, fallback to car regions")
        for _, row in detections.iterrows():
            x1, y1, x2, y2 = int(row['xmin']), int(row['ymin']), int(row['xmax']), int(row['ymax'])
            plate_candidates.append({
                'img': img[y1:y2, x1:x2],
                'box': [x1, y1, x2, y2],
                'confidence': row['confidence']
            })
    
    # Fallback: If still no candidates, use the entire image
    if not plate_candidates:
        log("Using entire image as fallback")
        h, w = img.shape[:2]
        plate_candidates.append({
            'img': img,
            'box': [0, 0, w, h],
            'confidence': 0.5
        })
    
    # --- TAHAP 3: OCR DENGAN EASYOCR ---
    log("Initializing EasyOCR...")
    reader = easyocr.Reader(['en'])
    
    best_result = None
    highest_confidence = -1
    
    for idx, candidate in enumerate(plate_candidates):
        plate_img = candidate['img']
        
        # Image enhancement for OCR
        log(f"Processing candidate {idx+1}/{len(plate_candidates)}...")
        
        # Convert to grayscale if not already
        if len(plate_img.shape) == 3:
            gray = cv2.cvtColor(plate_img, cv2.COLOR_BGR2GRAY)
        else:
            gray = plate_img
            
        # Resize (double the size for better OCR)
        gray = cv2.resize(gray, None, fx=2, fy=2, interpolation=cv2.INTER_CUBIC)
        
        # Improve contrast
        gray = cv2.equalizeHist(gray)
        
        # Save enhanced image for debugging
        if DEBUG_MODE:
            cv2.imwrite(image_path.replace('.jpg', f'_enhanced_{idx+1}.jpg'), gray)
            
        # OCR with EasyOCR
        log("Running EasyOCR...")
        results = reader.readtext(gray)
        
        # Process results
        if results:
            log(f"OCR returned {len(results)} text regions")
            
            full_text = []
            confidence_sum = 0
            
            for (bbox, text, conf) in results:
                # Clean text (remove spaces, keep alphanumeric)
                cleaned_text = ''.join(c for c in text if c.isalnum() or c.isspace()).strip().upper()
                if cleaned_text:
                    full_text.append(cleaned_text)
                    confidence_sum += conf
                    log(f"Text detected: '{cleaned_text}' (confidence: {conf:.2f})")
            
            # Join detected text parts
            if full_text:
                plate_text = ' '.join(full_text)
                avg_confidence = confidence_sum / len(full_text)
                
                # If better than previous result, update
                if avg_confidence > highest_confidence:
                    highest_confidence = avg_confidence
                    best_result = {
                        'text': plate_text,
                        'confidence': avg_confidence,
                        'box': candidate['box']
                    }
    
    # --- TAHAP 4: FORMAT HASIL UNTUK OUTPUT ---
    if best_result:
        # Format plat nomor Indonesia (contoh: B 1234 XYZ)
        raw_text = best_result['text']
        
        # Try to clean and format as Indonesian license plate
        plate_pattern = r'([A-Z]{1,2})\s*(\d{1,4})\s*([A-Z]{1,3})'
        import re
        match = re.search(plate_pattern, raw_text)
        
        formatted_text = raw_text
        if match:
            region, numbers, letters = match.groups()
            formatted_text = f"{region} {numbers} {letters}"
        
        log(f"Final result: {formatted_text} (confidence: {best_result['confidence']:.2f})")
        
        # Return result
        result = {
            "success": True,
            "plate_number": formatted_text,
            "raw_text": raw_text,
            "confidence": float(best_result['confidence']),
            "box": best_result['box'],
            "message": "Plate detection successful"
        }
    else:
        log("No text detected")
        result = {
            "success": False,
            "plate_number": "",
            "raw_text": "",
            "confidence": 0.0,
            "message": "Could not recognize any text"
        }
    
    # Output result as JSON
    print(json.dumps(result))
    
except Exception as e:
    import traceback
    trace = traceback.format_exc()
    log(f"Error: {str(e)}\n{trace}")
    print(json.dumps({"success": False, "message": str(e), "trace": trace}))
    sys.exit(1)