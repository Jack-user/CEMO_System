import serial
import re
import requests

# Adjust COM port and baud rate
ser = serial.Serial('COM8', 9600)

# Pattern to extract lat/lng like 10.607216,122.921310
gps_pattern = re.compile(r'(-?\d+\.\d+),\s*(-?\d+\.\d+)')

# Your correct HTTPS URL local/online
# POST_URL = "http://localhost/phpmyadmin/CEMO_System/final/api/save_gps.php" #local
POST_URL = "https://bagowastetracker.bccbsis.com/api/save_gps.php" #online
while True:
    if ser.in_waiting:
        # Use more robust decoding to skip invalid characters
        line = ser.readline().decode('utf-8', errors='ignore').strip()
        print("📡 Raw line:", line)

        match = gps_pattern.search(line)
        if match:
            lat, lng = match.groups()
            print(f"📍 Extracted Coordinates -> Latitude: {lat}, Longitude: {lng}")

            try:
                # Send POST to PHP backend
                response = requests.post(
                    POST_URL,
                    data={'latitude': lat.strip(), 'longitude': lng.strip()},
                    headers={'Content-Type': 'application/x-www-form-urlencoded'},
                    allow_redirects=True
                )
                
                # Print response result
                if response.status_code == 200:
                    print(f"✅ Server Response: {response.text}")
                else:
                    print(f"❌ Server error {response.status_code}: {response.text}")
            except Exception as e:
                print("❌ Request failed:", e)
        else:
            print("⚠️ Skipped: Line did not contain GPS coordinates.")
