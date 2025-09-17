<?php
header('Content-Type: application/json');
include '../includes/conn.php';

// Params: brgy_id (required), year (default current), month (default current)
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$brgy_id = isset($_GET['brgy_id']) ? (int)$_GET['brgy_id'] : null;

if (!$brgy_id || $month < 1 || $month > 12 || $year < 2000 || $year > 2100) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit;
}

// Compute month range
$start = sprintf('%04d-%02d-01 00:00:00', $year, $month);
$endDate = new DateTime($start);
$endDate->modify('last day of this month')->setTime(23, 59, 59);
$end = $endDate->format('Y-m-d H:i:s');

// Total for this month for the selected barangay
$sql = "SELECT b.barangay, SUM(s.count) AS total_count
        FROM sensor s
        JOIN barangays_table b ON s.brgy_id = b.brgy_id
        WHERE s.brgy_id = ? AND s.timestamp BETWEEN ? AND ?
        GROUP BY s.brgy_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param('iss', $brgy_id, $start, $end);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

$barangay = $row['barangay'] ?? '';
$totalCount = (int)($row['total_count'] ?? 0);
$tons = round($totalCount * 0.001, 2);

// Yearly monthly totals for the selected barangay
$yearSql = "SELECT MONTH(timestamp) AS month_num, SUM(count) AS month_total
            FROM sensor
            WHERE brgy_id = ? AND YEAR(timestamp) = ?
            GROUP BY MONTH(timestamp)";
$stmtY = $conn->prepare($yearSql);
$stmtY->bind_param('ii', $brgy_id, $year);
$stmtY->execute();
$yearRes = $stmtY->get_result();
$monthlyCounts = array_fill(0, 12, 0);
while ($yr = $yearRes->fetch_assoc()) {
	$idx = max(1, min(12, (int)$yr['month_num'])) - 1;
	$monthlyCounts[$idx] = (int)$yr['month_total'];
}
$monthlyTons = array_map(function($c){ return round($c * 0.001, 3); }, $monthlyCounts);

// Optionally compute daily breakdown for the month to show richer chart later
$dailySql = "SELECT DAYOFMONTH(timestamp) AS day_number, SUM(count) AS daily_count
             FROM sensor
             WHERE brgy_id = ? AND timestamp BETWEEN ? AND ?
             GROUP BY DATE(timestamp)
             ORDER BY DATE(timestamp)";
$stmt2 = $conn->prepare($dailySql);
$stmt2->bind_param('iss', $brgy_id, $start, $end);
$stmt2->execute();
$dailyRes = $stmt2->get_result();
$dailyData = [];
while ($r = $dailyRes->fetch_assoc()) {
    $dailyData[] = [
        'day_number' => (int)$r['day_number'],
        'daily_count' => (int)$r['daily_count'],
        'daily_tons' => round(((int)$r['daily_count']) * 0.001, 3),
    ];
}

// Progress heuristic (cap at 100)
$progress = 0;
if ($tons > 0) {
    $progress = (int)min(100, round($tons * 100));
}

echo json_encode([
    'success' => true,
    'barangay' => $barangay,
    'brgy_id' => $brgy_id,
    'year' => $year,
    'month' => $month,
    'total_count' => $totalCount,
    'tons' => $tons,
    'progress' => $progress,
    'dailyData' => $dailyData,
    'monthlyCounts' => $monthlyCounts,
    'monthlyTons' => $monthlyTons,
]);
?>

