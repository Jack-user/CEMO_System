import serial
import mysql.connector
import time
import re

# Adjust COM port and baud rate
ser = serial.Serial('COM4', 9600)

# Connect to MySQL
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="cemo_db"
)
cursor = db.cursor()

# Pattern to extract lat/lng like 10.607216,122.921310
gps_pattern = re.compile(r'(-?\d+\.\d+),\s*(-?\d+\.\d+)')

while True:
    if ser.in_waiting:
        line = ser.readline().decode('utf-8').strip()
        print("Raw line:", line)

        match = gps_pattern.search(line)
        if match:
            lat, lng = match.groups()
            try:
                cursor.execute(
                    "INSERT INTO gps_location (latitude, longitude) VALUES (%s, %s)",
                    (lat.strip(), lng.strip())
                )
                db.commit()
                print(f"✔ Saved to DB: {lat}, {lng}")
            except Exception as e:
                print("❌ DB error:", e)
        else:
            print("↪ Skipped non-GPS line.")
