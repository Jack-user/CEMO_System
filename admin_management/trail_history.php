<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_page/sign-in.php");
    exit();
}

header('Content-Type: application/json');

// Handle different actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'save_history':
        saveTrailHistory();
        break;
    case 'get_history':
        getTrailHistory();
        break;
    case 'delete_history':
        deleteTrailHistory();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}

function saveTrailHistory() {
    $trailData = $_POST['trail_data'] ?? '';
    $trailName = $_POST['trail_name'] ?? 'Trail_' . date('Y-m-d_H-i-s');
    
    if (empty($trailData)) {
        echo json_encode(['error' => 'No trail data provided']);
        return;
    }
    
    $historyFile = 'trail_history.json';
    $history = [];
    
    // Load existing history
    if (file_exists($historyFile)) {
        $history = json_decode(file_get_contents($historyFile), true) ?? [];
    }
    
    // Add new trail to history
    $history[] = [
        'id' => uniqid(),
        'name' => $trailName,
        'date_created' => date('Y-m-d H:i:s'),
        'trail_data' => json_decode($trailData, true),
        'point_count' => count(json_decode($trailData, true))
    ];
    
    // Keep only last 50 trails
    if (count($history) > 50) {
        $history = array_slice($history, -50);
    }
    
    // Save to file
    if (file_put_contents($historyFile, json_encode($history, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true, 'message' => 'Trail saved to history']);
    } else {
        echo json_encode(['error' => 'Failed to save trail history']);
    }
}

function getTrailHistory() {
    $historyFile = 'trail_history.json';
    
    if (!file_exists($historyFile)) {
        echo json_encode(['history' => []]);
        return;
    }
    
    $history = json_decode(file_get_contents($historyFile), true) ?? [];
    echo json_encode(['history' => $history]);
}

function deleteTrailHistory() {
    $trailId = $_POST['trail_id'] ?? '';
    
    if (empty($trailId)) {
        echo json_encode(['error' => 'No trail ID provided']);
        return;
    }
    
    $historyFile = 'trail_history.json';
    
    if (!file_exists($historyFile)) {
        echo json_encode(['error' => 'No history file found']);
        return;
    }
    
    $history = json_decode(file_get_contents($historyFile), true) ?? [];
    
    // Remove the specified trail
    $history = array_filter($history, function($trail) use ($trailId) {
        return $trail['id'] !== $trailId;
    });
    
    // Save updated history
    if (file_put_contents($historyFile, json_encode(array_values($history), JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true, 'message' => 'Trail removed from history']);
    } else {
        echo json_encode(['error' => 'Failed to update history']);
    }
}
?> 