<?php
header('Content-Type: application/json');
include '../includes/conn.php';

try {
    // Today's latest reading (not sum)
    $today = date('Y-m-d');

    $stmt = $conn->prepare("SELECT count FROM sensor WHERE sensor_id = 1 AND DATE(timestamp) = ? ORDER BY timestamp DESC LIMIT 1");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $todayRow = $stmt->get_result()->fetch_row();
    $todayLatestRaw = $todayRow ? $todayRow[0] : 0;
    $todayTons = ($todayLatestRaw ?? 0) * 0.001;

    // Last week: Mon–Sun
    $lastMon = date('Y-m-d', strtotime('last week monday'));
    $lastSun = date('Y-m-d', strtotime('last week sunday'));
    $stmt = $conn->prepare("SELECT SUM(count) FROM sensor WHERE sensor_id = 1 AND timestamp BETWEEN ? AND ?");
    $stmt->bind_param("ss", $lastMon, $lastSun);
    $stmt->execute();
    $lastWeekTons = ($stmt->get_result()->fetch_row()[0] ?? 0) * 0.001;

    // Two weeks ago
    $twoMon = date('Y-m-d', strtotime('last week monday -7 days'));
    $twoSun = date('Y-m-d', strtotime('last week sunday -7 days'));
    $stmt = $conn->prepare("SELECT SUM(count) FROM sensor WHERE sensor_id = 1 AND timestamp BETWEEN ? AND ?");
    $stmt->bind_param("ss", $twoMon, $twoSun);
    $stmt->execute();
    $twoWeeksAgoTons = ($stmt->get_result()->fetch_row()[0] ?? 0) * 0.001;

    // === OPTION 1: Week-over-week % change, capped at ±100% ===
    $pctChange = $twoWeeksAgoTons > 0 
        ? (($lastWeekTons - $twoWeeksAgoTons) / $twoWeeksAgoTons) * 100 
        : ($lastWeekTons > 0 ? 100 : 0);

    $pctChange = max(-100, min($pctChange, 100)); // Clamp between -100% and +100%
    $pctChange = round($pctChange, 1);

    // === OPTION 2: Utilization % (Recommended if you want 0–100%) ===
    $maxCapacityPerWeek = 100.0; // ← Adjust this to your real max (e.g., 40 tons?)
    $utilizationPercent = ($lastWeekTons / $maxCapacityPerWeek) * 100;
    $utilizationPercent = min(100, $utilizationPercent); // Can't exceed 100%
    $utilizationPercent = round($utilizationPercent, 1);

    // ✅ Send both (or choose one). We'll send both so you can decide in JS.
    echo json_encode([
        'success' => true,
        'todayTons' => round($todayTons, 2),
        'todayLatestRaw' => $todayLatestRaw ?? 0,
        'lastWeekTons' => round($lastWeekTons, 2),
        'lastWeekPercentageChange' => $pctChange,           // capped growth %
        'weeklyUtilization' => $utilizationPercent         // 0–100% of capacity
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}
?>