import serial
import re
import requests

# Adjust COM port and baud rate
ser = serial.Serial('COM4', 9600)

# Pattern to extract lat/lng like 10.607216,122.921310
gps_pattern = re.compile(r'(-?\d+\.\d+),\s*(-?\d+\.\d+)')

# Your correct HTTPS URL
POST_URL = "https://bagowastetracker.bccbsis.com/api/save_gps.php"  # <-- change if hosted elsewhere

while True:
    if ser.in_waiting:
        line = ser.readline().decode('utf-8').strip()
        print("Raw line:", line)

        match = gps_pattern.search(line)
        if match:
            lat, lng = match.groups()
            try:
                response = requests.post(
                    POST_URL,
                    data={'latitude': lat.strip(), 'longitude': lng.strip()},
                    headers={'Content-Type': 'application/x-www-form-urlencoded'},
                    allow_redirects=True  # ✅ handle 307 or 302 redirects
                )
                if response.status_code == 200:
                    print(f"✔ Server Response: {response.text}")
                else:
                    print(f"❌ Server error {response.status_code}: {response.text}")
            except Exception as e:
                print("❌ Request failed:", e)
        else:
            print("↪ Skipped non-GPS line.")
