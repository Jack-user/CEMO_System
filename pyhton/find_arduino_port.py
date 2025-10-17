import serial.tools.list_ports
import serial
import time

def find_arduino_port():
    print("Scanning for Arduino devices...")
    
    # Get all available ports
    ports = serial.tools.list_ports.comports()
    
    if not ports:
        print("No COM ports found!")
        return None
    
    print(f"Found {len(ports)} COM port(s):")
    for i, port in enumerate(ports):
        print(f"  {i+1}. {port.device} - {port.description}")
    
    # Try to connect to each port and test for Arduino
    for port in ports:
        port_name = port.device
        print(f"\nTesting {port_name}...")
        
        try:
            # Try common Arduino baud rates
            for baud in [9600, 115200, 57600, 38400]:
                try:
                    ser = serial.Serial(port_name, baud, timeout=2)
                    print(f"  Connected at {baud} baud")
                    
                    # Wait a bit for any data
                    time.sleep(1)
                    
                    # Check if there's any data (Arduino might be sending data)
                    if ser.in_waiting:
                        data = ser.read(ser.in_waiting).decode('utf-8', errors='ignore')
                        print(f"  Data received: {data.strip()}")
                        if "LoRa" in data or "init" in data or "GPS" in data:
                            print(f"  *** This looks like your Arduino! ***")
                            ser.close()
                            return port_name, baud
                    
                    ser.close()
                    print(f"  No data received at {baud} baud")
                    
                except serial.SerialException as e:
                    print(f"  Failed at {baud} baud: {e}")
                    continue
                    
        except Exception as e:
            print(f"  Error testing {port_name}: {e}")
            continue
    
    print("\nNo Arduino detected. Make sure:")
    print("1. Arduino is connected via USB")
    print("2. Arduino IDE is closed")
    print("3. Try unplugging and reconnecting the Arduino")
    return None

if __name__ == "__main__":
    result = find_arduino_port()
    if result:
        port, baud = result
        print(f"\nRecommended settings:")
        print(f"PORT = \"{port}\"")
        print(f"BAUD = {baud}")
    else:
        print("\nCould not find Arduino. Try the troubleshooting steps above.")
