import os
import sys
import numpy as np
import cv2
import tensorflow as tf
from tensorflow.keras.preprocessing.image import ImageDataGenerator
from tensorflow.keras.applications import MobileNetV2
from tensorflow.keras.layers import Dense, GlobalAveragePooling2D, Dropout
from tensorflow.keras.models import Model
from tensorflow.keras.optimizers import Adam
from tensorflow.keras.callbacks import ModelCheckpoint, EarlyStopping, ReduceLROnPlateau

# Lokasi dataset
DATASET_PATH = os.path.join(os.path.dirname(os.path.abspath(__file__)), "dataset", "plates")
MODEL_SAVE_PATH = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), "public", "models", "plate-detection")

# Membuat direktori output jika belum ada
os.makedirs(MODEL_SAVE_PATH, exist_ok=True)
if not os.path.exists(DATASET_PATH):
    os.makedirs(DATASET_PATH)
    
def check_dataset():
    """Check if dataset exists and has proper structure"""
    if not os.path.exists(DATASET_PATH):
        print(f"Dataset directory not found: {DATASET_PATH}")
        print("Please create the directory and add training images.")
        return False
        
    subdirs = [d for d in os.listdir(DATASET_PATH) if os.path.isdir(os.path.join(DATASET_PATH, d))]
    if not subdirs:
        print(f"No class subdirectories found in {DATASET_PATH}")
        print("Please create subdirectories for each class/character.")
        return False
        
    total_images = 0
    for subdir in subdirs:
        class_path = os.path.join(DATASET_PATH, subdir)
        images = [f for f in os.listdir(class_path) if f.lower().endswith(('.png', '.jpg', '.jpeg'))]
        total_images += len(images)
        print(f"Found {len(images)} images in class '{subdir}'")
    
    print(f"Total images: {total_images}")
    if total_images < 100:
        print("WARNING: Very few training images. Model accuracy may be poor.")
        
    return total_images > 0

def train_model():
    """Train the plate detection model"""
    print("Starting model training...")
    
    # Data augmentation for training
    train_datagen = ImageDataGenerator(
        rescale=1./255,
        rotation_range=10,
        width_shift_range=0.1,
        height_shift_range=0.1,
        shear_range=0.1,
        zoom_range=0.1,
        horizontal_flip=False,
        fill_mode='nearest',
        validation_split=0.2
    )

    # Load training data
    train_generator = train_datagen.flow_from_directory(
        DATASET_PATH,
        target_size=(224, 224),
        batch_size=32,
        class_mode='categorical',
        subset='training'
    )

    # Load validation data
    validation_generator = train_datagen.flow_from_directory(
        DATASET_PATH,
        target_size=(224, 224),
        batch_size=32,
        class_mode='categorical',
        subset='validation'
    )

    # Base model (MobileNetV2 is efficient for mobile/web)
    base_model = MobileNetV2(weights='imagenet', include_top=False, input_shape=(224, 224, 3))

    # Freeze base model layers
    for layer in base_model.layers:
        layer.trainable = False

    # Add custom classification layers
    x = base_model.output
    x = GlobalAveragePooling2D()(x)
    x = Dense(1024, activation='relu')(x)
    x = Dropout(0.5)(x)
    predictions = Dense(train_generator.num_classes, activation='softmax')(x)

    # Create the model
    model = Model(inputs=base_model.input, outputs=predictions)

    # Compile
    model.compile(
        optimizer=Adam(learning_rate=0.001),
        loss='categorical_crossentropy',
        metrics=['accuracy']
    )

    # Callbacks
    checkpoint = ModelCheckpoint(
        os.path.join(MODEL_SAVE_PATH, "plate_model_best.h5"),
        monitor='val_accuracy',
        save_best_only=True,
        verbose=1
    )
    
    early_stopping = EarlyStopping(
        monitor='val_loss',
        patience=10,
        restore_best_weights=True,
        verbose=1
    )
    
    reduce_lr = ReduceLROnPlateau(
        monitor='val_loss',
        factor=0.2,
        patience=5,
        min_lr=0.00001,
        verbose=1
    )

    # Train the model
    history = model.fit(
        train_generator,
        steps_per_epoch=train_generator.samples // train_generator.batch_size,
        validation_data=validation_generator,
        validation_steps=validation_generator.samples // validation_generator.batch_size,
        epochs=30,
        callbacks=[checkpoint, early_stopping, reduce_lr]
    )

    # Save the final model
    model.save(os.path.join(MODEL_SAVE_PATH, "plate_detection_model.h5"))
    
    # Save class indices
    import json
    with open(os.path.join(MODEL_SAVE_PATH, "class_indices.json"), 'w') as f:
        # Invert the dictionary to get class name from index
        class_indices = {v: k for k, v in train_generator.class_indices.items()}
        json.dump(class_indices, f)

    # Try to convert to TensorFlow.js format
    try:
        import tensorflowjs as tfjs
        tfjs_target = os.path.join(MODEL_SAVE_PATH, "tfjs_model")
        tfjs.converters.save_keras_model(model, tfjs_target)
        print(f"Model successfully converted to TensorFlow.js format and saved to {tfjs_target}")
    except ImportError:
        print("TensorFlow.js not available. Skipping conversion.")
        print("To convert the model, install tensorflowjs: pip install tensorflowjs")

    print("Model training completed!")
    
if __name__ == "__main__":
    if not check_dataset():
        print("\nDataset preparation instructions:")
        print("1. Create subdirectories in 'python/dataset/plates' for each character class")
        print("2. For plate numbers, create folders like 'A', 'B', '1', '2', etc.")
        print("3. Add training images for each character in their respective folders")
        print("4. Run this script again after preparing the dataset")
        sys.exit(1)
    
    train_model()