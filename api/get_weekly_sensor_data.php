<?php
header('Content-Type: application/json');
include '../includes/conn.php';

try {
    // -----------------------------
    // Input Validation & Defaults
    // -----------------------------
    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
    $month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
    $week = isset($_GET['week']) ? (int)$_GET['week'] : ceil(date('j') / 7); // Current week of month

    if ($month < 1 || $month > 12) {
        throw new Exception('Invalid month. Must be 1–12.');
    }
    if ($year < 2000 || $year > 2100) {
        throw new Exception('Invalid year.');
    }

    // -----------------------------
    // Create DateTime objects safely
    // -----------------------------
    $firstDayOfMonth = new DateTime("$year-$month-01");
    $lastDayOfMonth = clone $firstDayOfMonth;
    $lastDayOfMonth->modify('last day of this month'); // Safe way to get last day

    // -----------------------------
    // Calculate First Monday of the Month
    // -----------------------------
    $firstMonday = clone $firstDayOfMonth;
    $dow = (int)$firstMonday->format('N'); // 1=Mon, 7=Sun
    if ($dow != 1) {
        $firstMonday->modify('last Monday');
    }

    // -----------------------------
    // Determine Selected Week: Monday of week #N
    // -----------------------------
    $weekMonday = clone $firstMonday;
    $daysToAdd = 7 * ($week - 1);
    $weekMonday->add(new DateInterval("P{$daysToAdd}D"));

    $weekSunday = clone $weekMonday;
    $weekSunday->add(new DateInterval('P6D'));

    $weekStart = $weekMonday->format('Y-m-d 00:00:00');
    $weekEnd = $weekSunday->format('Y-m-d 23:59:59');

    // -----------------------------
    // Fetch Daily Data for sensor_id = 1 (raw sensor table only)
    // -----------------------------
    $dailyQuery = "
        SELECT 
            DATE(timestamp) as date_only,
            DATE_FORMAT(timestamp, '%a') as day_name,
            DAYOFMONTH(timestamp) as day_number,
            SUM(count) as daily_count
        FROM 
            sensor
        WHERE 
            sensor_id = 1 AND
            timestamp >= ? AND timestamp <= ?
        GROUP BY 
            DATE(timestamp)
        ORDER BY 
            DATE(timestamp)
    ";

    $stmt = $conn->prepare($dailyQuery);
    $stmt->bind_param("ss", $weekStart, $weekEnd);
    $stmt->execute();
    $result = $stmt->get_result();
    $dailyData = [];
    while ($row = $result->fetch_assoc()) {
        $dailyData[] = [
            'date_only' => $row['date_only'],
            'day_name' => $row['day_name'],
            'day_number' => (int)$row['day_number'],
            'daily_count' => (int)$row['daily_count']
        ];
    }

    // -----------------------------
    // Build Full Week: Mon to Sun (fill missing days)
    // -----------------------------
    $fullWeekData = [];
    $current = clone $weekMonday;

    for ($i = 0; $i < 7; $i++) {
        $dateStr = $current->format('Y-m-d');
        $match = null;
        foreach ($dailyData as $day) {
            if ($day['date_only'] === $dateStr) {
                $match = $day;
                break;
            }
        }

        $fullWeekData[] = $match ? $match : [
            'day_name' => $current->format('D'),
            'day_number' => (int)$current->format('j'),
            'daily_count' => 0,
            'date_only' => $dateStr
        ];

        $current->add(new DateInterval('P1D'));
    }

    // -----------------------------
    // Get All Weekly Totals for This Month
    // -----------------------------
    $weeklyData = [];
    $currentWeekStart = clone $firstMonday;

    while ($currentWeekStart <= $lastDayOfMonth) {
        $weekEndCheck = clone $currentWeekStart;
        $weekEndCheck->add(new DateInterval('P6D')); // Sunday

        // Only include weeks that overlap the current month
        $startStr = $currentWeekStart->format('Y-m-d');
        $endStr = $weekEndCheck->format('Y-m-d');

        if ($weekEndCheck >= $firstDayOfMonth && $currentWeekStart <= $lastDayOfMonth) {
            $query = "SELECT SUM(count) as total_count FROM sensor WHERE sensor_id = 1 AND timestamp >= ? AND timestamp <= ?";
            $stmt2 = $conn->prepare($query);
            $stmt2->bind_param("ss", $startStr, $endStr);
            $stmt2->execute();
            $res = $stmt2->get_result();
            $row = $res->fetch_assoc();
            $totalCount = (int)($row['total_count'] ?? 0);

            // Format date range: "Aug 4–10" or "Jul 29 – Aug 5"
            $startDay = (int)$currentWeekStart->format('j');
            $endDay = (int)$weekEndCheck->format('j');
            $startMonth = $currentWeekStart->format('M');
            $endMonth = $weekEndCheck->format('M');
            $dateRange = ($startMonth === $endMonth)
                ? "$startMonth $startDay-$endDay"
                : "$startMonth $startDay - $endMonth $endDay";

            $weeklyData[] = [
                'week_of_month' => count($weeklyData) + 1,
                'date_range' => $dateRange,
                'total_count' => $totalCount
            ];
        }

        $currentWeekStart->add(new DateInterval('P7D'));
    }

    // -----------------------------
    // Last Week's Total (Previous Mon–Sun)
    // -----------------------------
    $lastWeekStart = date('Y-m-d', strtotime('last week monday'));
    $lastWeekEnd = date('Y-m-d', strtotime('last week sunday'));

    $lastWeekQuery = "SELECT SUM(count) as total_count FROM sensor WHERE sensor_id = 1 AND DATE(timestamp) BETWEEN ? AND ?";
    $stmtLast = $conn->prepare($lastWeekQuery);
    $stmtLast->bind_param("ss", $lastWeekStart, $lastWeekEnd);
    $stmtLast->execute();
    $lastRes = $stmtLast->get_result();
    $lastRow = $lastRes->fetch_assoc();
    $lastWeekTons = $lastRow && $lastRow['total_count'] ? number_format($lastRow['total_count'] * 0.001, 2) : "0.00";
    $stmtLast->close();

    // -----------------------------
    // Final Output
    // -----------------------------
    echo json_encode([
        'success' => true,
        'month' => date('F Y', mktime(0, 0, 0, $month, 1, $year)),
        'weeklyData' => $weeklyData,
        'dailyData' => $fullWeekData,
        'selectedWeek' => $week,
        'weekRange' => $weekMonday->format('j') . ' - ' . $weekSunday->format('j'),
        'lastWeekWaste' => $lastWeekTons
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>