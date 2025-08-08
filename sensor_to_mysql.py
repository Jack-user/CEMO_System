import serial
import mysql.connector
import time

location_id = 1

try:
    arduino = serial.Serial('COM5', 9600)
    time.sleep(2)

    db = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="cemo_db"
    )
    cursor = db.cursor()

    while True:
        try:
            line = arduino.readline().decode(errors='ignore').strip()
            print("Raw line:", line)

            if line and "," in line:
                parts = line.split(",")
                if len(parts) == 2:
                    distance_str, count_str = parts
                    count = int(count_str)

                    cursor.execute(
                        "INSERT INTO sensor (location_id, count) VALUES (%s, %s)",
                        (location_id, count)
                    )
                    db.commit()

                    print("Inserted:", cursor.statement)
                else:
                    print("Invalid split format:", parts)
            else:
                print("No comma or empty line.")

        except Exception as e:
            print("Insert error:", e)
            break

    arduino.close()
    db.close()

except Exception as conn_error:
    print("Connection error:", conn_error)