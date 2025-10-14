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
$sql = "SELECT b.barangay, SUM(a.total_count) AS total_count
        FROM sensor_agg_daily a
        JOIN barangays_table b ON a.brgy_id = b.brgy_id
        WHERE a.brgy_id = ? AND a.date BETWEEN DATE(?) AND DATE(?)
        GROUP BY a.brgy_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param('iss', $brgy_id, $start, $end);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

$barangay = $row['barangay'] ?? '';
$totalCount = (int)($row['total_count'] ?? 0);
$tons = round($totalCount * 0.001, 2);

// Fallback to raw `sensor` for monthly total if aggregate has no data
if ($totalCount === 0) {
    $sqlRaw = "SELECT b.barangay, SUM(s.count) AS total_count
               FROM sensor s
               JOIN barangays_table b ON s.brgy_id = b.brgy_id
               WHERE s.brgy_id = ? AND s.timestamp BETWEEN ? AND ?
               GROUP BY s.brgy_id";
    $stmtR = $conn->prepare($sqlRaw);
    $stmtR->bind_param('iss', $brgy_id, $start, $end);
    $stmtR->execute();
    $resR = $stmtR->get_result();
    $rowR = $resR->fetch_assoc();
    if ($rowR) {
        $barangay = $rowR['barangay'] ?? $barangay;
        $totalCount = (int)($rowR['total_count'] ?? 0);
        $tons = round($totalCount * 0.001, 2);
    }
    $stmtR->close();
}

// Yearly monthly totals for the selected barangay
$yearSql = "SELECT MONTH(date) AS month_num, SUM(total_count) AS month_total
            FROM sensor_agg_daily
            WHERE brgy_id = ? AND YEAR(date) = ?
            GROUP BY MONTH(date)";
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

// Fallback yearly series if aggregate yields all zeros
$allZero = true; foreach ($monthlyCounts as $c) { if ($c > 0) { $allZero = false; break; } }
if ($allZero) {
    $yearSqlRaw = "SELECT MONTH(timestamp) AS month_num, SUM(count) AS month_total
                   FROM sensor
                   WHERE brgy_id = ? AND YEAR(timestamp) = ?
                   GROUP BY MONTH(timestamp)";
    $stmtYR = $conn->prepare($yearSqlRaw);
    $stmtYR->bind_param('ii', $brgy_id, $year);
    $stmtYR->execute();
    $yearResR = $stmtYR->get_result();
    $monthlyCounts = array_fill(0, 12, 0);
    while ($yr = $yearResR->fetch_assoc()) {
        $idx = max(1, min(12, (int)$yr['month_num'])) - 1;
        $monthlyCounts[$idx] = (int)$yr['month_total'];
    }
    $monthlyTons = array_map(function($c){ return round($c * 0.001, 3); }, $monthlyCounts);
    $stmtYR->close();
}

// Optionally compute daily breakdown for the month to show richer chart later
$dailySql = "SELECT DAYOFMONTH(date) AS day_number, SUM(total_count) AS daily_count
             FROM sensor_agg_daily
             WHERE brgy_id = ? AND date BETWEEN DATE(?) AND DATE(?)
             GROUP BY date
             ORDER BY date";
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

// Fallback daily series if aggregate has no data
if (count($dailyData) === 0) {
    $dailySqlRaw = "SELECT DAYOFMONTH(timestamp) AS day_number, SUM(count) AS daily_count
                    FROM sensor
                    WHERE brgy_id = ? AND timestamp BETWEEN ? AND ?
                    GROUP BY DATE(timestamp)
                    ORDER BY DATE(timestamp)";
    $stmt2R = $conn->prepare($dailySqlRaw);
    $stmt2R->bind_param('iss', $brgy_id, $start, $end);
    $stmt2R->execute();
    $dailyResR = $stmt2R->get_result();
    while ($r = $dailyResR->fetch_assoc()) {
        $dailyData[] = [
            'day_number' => (int)$r['day_number'],
            'daily_count' => (int)$r['daily_count'],
            'daily_tons' => round(((int)$r['daily_count']) * 0.001, 3),
        ];
    }
    $stmt2R->close();
}

// Progress heuristic (cap at 98%)
$progress = 0;
if ($tons > 0) {
    $progress = (int)min(98, round($tons * 100));
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

