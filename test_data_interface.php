<?php
// Test Data Interface for CEMO System
header('Content-Type: application/json');

// Database connection
$host = 'localhost'; 
$dbname = 'u520834156_DBWasteTracker'; 
$username = 'u520834156_userWT2025'; 
$password = '^Lx|Aii1'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? 'status';

switch ($action) {
    case 'status':
        // Get database status
        $status = [];
        
        $tables = ['barangays_table', 'sensor', 'gps_location', 'client_table', 'driver_table', 'monthly_waste_table'];
        
        foreach ($tables as $table) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $status[$table] = $result['count'];
            } catch (Exception $e) {
                $status[$table] = 'Error: ' . $e->getMessage();
            }
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Database status retrieved',
            'data' => $status
        ]);
        break;
        
    case 'insert_sample':
        // Insert sample data
        try {
            // Sample barangay data
            $barangays = [
                [1, 'Abuanan', 10.5466, 122.9907, 'Bago City', 'https://facebook.com/abuanan', 'Abuanan FB Page', 1],
                [2, 'Alianza', 10.5389, 122.9856, 'Bago City', 'https://facebook.com/alianza', 'Alianza FB Page', 1],
                [3, 'Atipuluan', 10.5321, 122.9789, 'Bago City', 'https://facebook.com/atipuluan', 'Atipuluan FB Page', 2]
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO barangays_table (brgy_id, barangay, latitude, longitude, city, facebook_link, link_text, schedule_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                barangay = VALUES(barangay),
                latitude = VALUES(latitude),
                longitude = VALUES(longitude),
                city = VALUES(city),
                facebook_link = VALUES(facebook_link),
                link_text = VALUES(link_text),
                schedule_id = VALUES(schedule_id)
            ");
            
            foreach ($barangays as $barangay) {
                $stmt->execute($barangay);
            }
            
            // Sample sensor data
            $sensor_data = [
                [1, 45, 1, 1734567890, date('Y-m-d H:i:s'), 85.5, 'Collecting'],
                [1, 52, 2, 1734567891, date('Y-m-d H:i:s'), 92.3, 'Collecting'],
                [1, 38, 3, 1734567892, date('Y-m-d H:i:s'), 78.1, 'Idle']
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO sensor (sensor_id, count, brgy_id, location_id, timestamp, distance, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($sensor_data as $data) {
                $stmt->execute($data);
            }
            
            // Sample GPS data
            $gps_data = [
                [1734567890, 10.5466, 122.9907, date('Y-m-d H:i:s')],
                [1734567891, 10.5389, 122.9856, date('Y-m-d H:i:s')],
                [1734567892, 10.5321, 122.9789, date('Y-m-d H:i:s')]
            ];
            
            $stmt = $pdo->prepare("
                INSERT INTO gps_location (location_id, latitude, longitude, timestamp)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                latitude = VALUES(latitude),
                longitude = VALUES(longitude),
                timestamp = VALUES(timestamp)
            ");
            
            foreach ($gps_data as $data) {
                $stmt->execute($data);
            }
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Sample data inserted successfully',
                'inserted' => [
                    'barangays' => count($barangays),
                    'sensor_readings' => count($sensor_data),
                    'gps_locations' => count($gps_data)
                ]
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to insert sample data: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'clear_data':
        // Clear test data (be careful with this!)
        $confirm = $_GET['confirm'] ?? false;
        
        if (!$confirm) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Please add ?confirm=true to confirm data clearing'
            ]);
            break;
        }
        
        try {
            $tables = ['sensor', 'gps_location', 'sensor_agg_daily'];
            
            foreach ($tables as $table) {
                $pdo->exec("TRUNCATE TABLE $table");
            }
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Test data cleared successfully'
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to clear data: ' . $e->getMessage()
            ]);
        }
        break;
        
    case 'recent_data':
        // Get recent sensor data
        try {
            $stmt = $pdo->query("
                SELECT s.*, b.barangay, g.latitude, g.longitude
                FROM sensor s
                LEFT JOIN barangays_table b ON s.brgy_id = b.brgy_id
                LEFT JOIN gps_location g ON s.location_id = g.location_id
                ORDER BY s.timestamp DESC
                LIMIT 10
            ");
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Recent sensor data retrieved',
                'data' => $data
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to retrieve data: ' . $e->getMessage()
            ]);
        }
        break;
        
    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid action. Available actions: status, insert_sample, clear_data, recent_data'
        ]);
}
?>
