# Simpan sebagai test_tensorflow.py
import tensorflow as tf
import numpy as np

print(f"TensorFlow version: {tf.__version__}")
print(f"NumPy version: {np.__version__}")

# Buat model sederhana untuk mengkonfirmasi TensorFlow berjalan
model = tf.keras.Sequential([
    tf.keras.layers.Dense(10, input_shape=(5,), activation='relu'),
    tf.keras.layers.Dense(1, activation='sigmoid')
])

model.compile(optimizer='adam', loss='binary_crossentropy')
print("Model kompilasi berhasil!")

# Generate dummy data
x = np.random.random((10, 5))
y = np.random.randint(0, 2, (10, 1))

# Fit untuk memastikan semuanya berjalan
model.fit(x, y, epochs=1, batch_size=2, verbose=1)
print("TensorFlow berjalan dengan baik!")