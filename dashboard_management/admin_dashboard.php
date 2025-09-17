<?php
session_start();
include '../includes/conn.php';
// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) { // Change to your session variable
    header("Location: ../login_page/sign-in.php");
    exit();
}

$page_title = "Waste Dashboard"; // Set the page title
include '../includes/header.php'; // Includes the head section and styles

// Query to count users in admin_table
$query = "SELECT COUNT(*) AS total_admins FROM admin_table";
$result = mysqli_query($conn, $query);

$totalAdmins = 0;
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $totalAdmins = $row['total_admins'];
}
// Query to count users in client_table

$queryClient = "SELECT COUNT(*) AS total_clients FROM client_table";
$resultClient = mysqli_query($conn, $queryClient);

$totalClients = 0;
if ($resultClient && mysqli_num_rows($resultClient) > 0) {
    $rowClient = mysqli_fetch_assoc($resultClient);
    $totalClients = $rowClient['total_clients'];
}

// Fetch count from sensor table for sensor_id = 1
$todayStart = date('Y-m-d 00:00:00');
$todayEnd = date('Y-m-d 23:59:59');
$query = "SELECT SUM(count) as today_count FROM sensor WHERE sensor_id = 1 AND timestamp BETWEEN ? AND ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $todayStart, $todayEnd);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$tons = ($row['today_count'] ?? 0) * 0.001;

// Monthly sum of sensor detections (waste count)
$sql = "SELECT SUM(count) AS monthly_count
        FROM sensor
        WHERE MONTH(timestamp) = MONTH(CURDATE())
          AND YEAR(timestamp) = YEAR(CURDATE())";

$result = $conn->query($sql);
$row = $result->fetch_assoc();

$monthlyCount = $row['monthly_count'] ?? 0; // fallback if null

// Convert count to tons (assuming 1 count = 0.05 tons)
$conversionFactor =  0.001; 
$monthlyTons = $monthlyCount * $conversionFactor;

// Calculate progress towards target
$target = 7000; // Example target in tons

$sql = "SELECT SUM(count) AS collected
        FROM sensor
        WHERE MONTH(timestamp) = MONTH(CURDATE())
          AND YEAR(timestamp) = YEAR(CURDATE())";

$result = $conn->query($sql);
$row = $result->fetch_assoc();

$collected = $row['collected'] ?? 0;
$progress = $target > 0 ? round(($collected / $target) * 100, 1) : 0;

?>
<body class="g-sidenav-show bg-gray-100">
    <?php include '../sidebar/admin_sidebar.php'; ?>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800"></h1>
            <div class="row">
                <!-- Cards Section -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Collected Waste Today</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="collectedWasteToday">-- tons</div>
                                </div>
                                <div class="col-auto">
                                    <i class="material-symbols-rounded opacity-10">delete</i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Forecasted Waste Volume (Next Week)</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">1.65 tons</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="material-symbols-rounded opacity-10">schedule</i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Waste Collection Progress
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo $progress . "%"; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="material-symbols-rounded opacity-10">bar_chart</i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Monthly Collected Waste
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo number_format($monthlyTons, 2) . " tons"; ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="material-symbols-rounded opacity-10">calendar_month</i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



            <!-- Charts Section -->
            <div class="row">
                <div class="col-lg-12 col-md-10 mt-4 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 pb-0 d-flex justify-content-between align-items-center px-4">
                            <div class="text-center flex-grow-1">
                                <h5 class="mb-1 fw-semibold text-success">Waste Collected</h5>
                                <p class="text-muted mb-0">Weekly Waste Collection Performance</p>
                                <div id="selectedWeekDisplay" class="small text-primary mt-1"></div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle d-flex align-items-center" 
                                        type="button" 
                                        id="weekDropdown" 
                                        data-bs-toggle="dropdown" 
                                        aria-expanded="false">
                                    <span>View Details</span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="weekDropdown" style="width: 350px;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <button class="btn btn-sm btn-outline-secondary prev-month"><i class="material-symbols-rounded">chevron_left</i></button>
                                    <h6 class="mb-0 fw-semibold month-year">Loading...</h6>
                                    <button class="btn btn-sm btn-outline-secondary next-month"><i class="material-symbols-rounded">chevron_right</i></button>
                                    </div>
                                    <div class="week-grid">
                                        <div class="row row-cols-2 g-2"></div>
                                    </div>
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0 fw-semibold">Selected Week Details</h6>
                                        </div>
                                        <div class="day-stats">
                                            <div class="row row-cols-7 g-1 text-start mb-2" id="dayStatsLabels"></div>
                                            <div class="row row-cols-7 g-1 text-center" id="dayStatsValues"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-3 pb-1">
                            <div class="chart-container mx-auto" style="position: relative; height:350px; width:100%">
                                <canvas id="chart-bars" class="chart-canvas"></canvas>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="material-symbols-rounded text-muted me-1 fs-6">schedule</i>
                                    <span class="text-muted small">Updated Loading....</span>
                                </div>
                            </div>
                        </div>
                         <!-- Statistics Cards Row -->
                    <div class="row">
                        <!-- Weekly Performance -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Weekly Performance</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="weeklyPerformance">85%</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="material-symbols-rounded opacity-10">bar_chart</i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Waste Collected This Week -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Waste Collected This Week</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="wasteLastWeek">12.4 tons</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="material-symbols-rounded opacity-10">recycling</i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Average Daily Collection -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Average Daily Collection</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="avgDailyCollection">1.8 tons</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="material-symbols-rounded opacity-10">trending_up</i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Collection Efficiency -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Collection Efficiency</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="collectionEfficiency">92%</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="material-symbols-rounded opacity-10">speed</i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-lg-6 col-md-6 mt-4 mb-4">
                    <div class="card waste-volume-card">
                        <div class="card-body p-4">
                            <!-- Header Section -->
                            <div class="card-header-section">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="flex-grow-1">
                                        <h5 class="card-title">Brgy Waste Volume</h5>
                                        <p class="card-subtitle">Monthly Waste Volume for Barangay</p>
                                    </div>
                                    <div class="ms-3">
                                        <select id="brgySelect" class="form-select barangay-selector" style="min-width: 180px;"></select>
                                    </div>
                                </div>
                            </div>

                            <!-- Chart Section -->
                            <div class="chart-container">
                                <canvas id="chart-line" class="demo-canvas"></canvas>
                            </div>

                            <!-- Progress Section -->
                            <div class="progress-section">
                                <div class="progress progress-container" style="height: 20px;">
                                    <div id="brgyProgressBar" class="progress-bar-custom" style="width: 0%;">
                                        <span class="progress-text">0%</span>
                                    </div>
                                </div>
                                <div class="waste-info-text" id="brgyWasteInfo">
                                    Select a barangay to view waste volume data
                                </div>
                            </div>

                            <!-- Footer Section -->
                            <div class="card-footer-section">
                                <div class="update-info">
                                    <span class="material-symbols-rounded update-icon">schedule</span>
                                    <span id="brgyWasteUpdated">Updated just now</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-md-6 mt-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-0">Dumpsite Area</h6>
                            <p class="text-sm">Monthly Waste Forecast for Dumpsite Capacity</p>
                            <div class="pe-2">
                                <div class="chart">
                                    <canvas id="chart-line-tasks" class="chart-canvas" height="170"></canvas>
                                </div>
                            </div>
                            <hr class="dark horizontal">
                            <div class="d-flex">
                                <i class="material-symbols-rounded text-sm my-auto me-1">schedule</i>
                                <p class="mb-0 text-sm">Just updated</p>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>

    <!-- Include the Footer -->
<?php include '../includes/footer.php'; ?>
</main>
<script>
// --- Dynamic Dashboard Cards ---
async function loadDashboardSummary() {
    try {
        const res = await fetch('../api/get_dashboard_summary.php');
        const data = await res.json();
        console.log('Dashboard summary API response:', data); // Debug log

        if (data.success) {
            // Waste collected today
            document.getElementById('collectedWasteToday').textContent = 
                (data.todayTons || 0).toFixed(2) + ' tons';

            // Waste last week (default to API value, but can be overridden by week selection)
            if (typeof window.selectedWeekWaste !== 'undefined') {
                document.getElementById('wasteLastWeek').textContent = window.selectedWeekWaste;
            } else {
                document.getElementById('wasteLastWeek').textContent = 
                    (data.lastWeekTons || 0).toFixed(2) + ' tons';
            }

            // Weekly Performance (default to API value, but can be overridden by week selection)
            if (typeof window.selectedWeekPerformance !== 'undefined') {
                document.getElementById('weeklyPerformance').textContent = window.selectedWeekPerformance;
            } else {
                document.getElementById('weeklyPerformance').textContent = 
                    (data.weeklyUtilization || 0).toFixed(1) + '%';
            }
        } else {
            throw new Error(data.error);
        }
    } catch (e) {
        console.error('Error loading dashboard:', e);
        document.getElementById('collectedWasteToday').textContent = 'Err';
        document.getElementById('wasteLastWeek').textContent = 'Err';
        document.getElementById('weeklyPerformance').textContent = 'Err';
    }
}

// --- Brgy Waste Volume Dynamic Section ---
let brgyChart;
let brgyList = [];
let selectedBrgy = '';
let brgyChartWeek = null;

async function loadBarangays() {
    try {
        const res = await fetch('../barangay_api/get_barangays.php');
        const data = await res.json();
        if (Array.isArray(data)) {
            brgyList = data.map(b => ({ brgy_id: b.brgy_id, barangay: b.barangay }));
            const select = document.getElementById('brgySelect');
            select.innerHTML = '<option value="">Select Barangay</option>' + brgyList.map(b => `<option value="${b.brgy_id}">${b.barangay}</option>`).join('');
        }
    } catch {}
}

async function loadBrgyWasteVolume() {
    const brgy_id = document.getElementById('brgySelect').value;
    if (!brgy_id) return;
    // Use current month/year (API also returns full-year monthly aggregation)
    const params = new URLSearchParams({
        brgy_id,
        year: currentYear,
        month: currentMonth
    });
    const res = await fetch('../api/get_brgy_monthly_waste.php?' + params.toString());
    const data = await res.json();
    if (data.success) {
        // Update progress bar
    // Clean Progress Bar Update
    const progressBar = document.getElementById('brgyProgressBar');
    const progressText = progressBar.querySelector('.progress-text');
    progressBar.style.width = `${data.progress}%`;
    // Show total waste summed across all months (tons)
    let totalYearTons = 0;
    if (Array.isArray(data.monthlyTons) && data.monthlyTons.length === 12) {
        totalYearTons = data.monthlyTons.reduce((sum, t) => sum + (t || 0), 0);
    } else {
        totalYearTons = (data.tons || 0);
    }
    progressText.textContent = `${totalYearTons.toFixed(2)} tons`;
    // Info
    document.getElementById('brgyWasteInfo').textContent = `Collected: ${data.tons} tons for ${new Date(currentYear, currentMonth - 1, 1).toLocaleString('default', { month: 'long', year: 'numeric' })}.`;
    document.getElementById('brgyWasteUpdated').textContent = 'Updated just now';
        // Chart: show full-year by month, highlight selected month
        if (!brgyChart) {
            const ctx = document.getElementById('chart-line').getContext('2d');
            // Month labels for the year
            const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const monthData = (Array.isArray(data.monthlyTons) && data.monthlyTons.length === 12) ? data.monthlyTons : Array(12).fill(null).map((v, i) => (i === currentMonth - 1 ? data.tons : null));
            brgyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: 'Waste Collected (tons)',
                        backgroundColor: 'rgba(67,160,71,0.2)',
                        borderColor: '#43A047',
                        pointBackgroundColor: '#43A047',
                        pointBorderColor: '#43A047',
                        data: monthData,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { grid: { color: '#e5e5e5' }, ticks: { color: '#737373' } },
                        x: { grid: { drawBorder: false, display: true }, ticks: { color: '#737373' } }
                    }
                }
            });
        } else {
            // Update with full-year data
            const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const monthData = (Array.isArray(data.monthlyTons) && data.monthlyTons.length === 12) ? data.monthlyTons : Array(12).fill(null).map((v, i) => (i === currentMonth - 1 ? data.tons : null));
            brgyChart.data.labels = monthLabels;
            brgyChart.data.datasets[0].data = monthData;
            brgyChart.update();
        }
    }
}
function updateSelectedWeekDisplay(week, range) {
    const el = document.getElementById('selectedWeekDisplay');
    if (el) {
        if (week && range) {
            el.textContent = `Selected Week: Week ${week} (${range})`;
        } else {
            el.textContent = '';
        }
    }
}
let currentMonth = new Date().getMonth() + 1;
let currentYear = new Date().getFullYear();
let selectedWeek = null;
let barChart;

document.addEventListener('DOMContentLoaded', () => {
    // Chart.js bar chart setup
    const ctx = document.getElementById('chart-bars').getContext('2d');
    barChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Waste Collected (tons)',
                backgroundColor: '#43A047',
                data: Array(7).fill(0),
                borderRadius: 4,
                borderSkipped: false,
                barThickness: 'flex'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            interaction: { intersect: false, mode: 'index' },
            scales: {
                y: { grid: { color: '#e5e5e5' }, ticks: { color: '#737373' } },
                x: { grid: { drawBorder: false, display: false }, ticks: { color: '#737373' } }
            }
        }
    });

    // Week navigation
    document.querySelector('.prev-month').addEventListener('click', () => {
        currentMonth--;
        if (currentMonth < 1) { currentMonth = 12; currentYear--; }
        selectedWeek = 1;
        loadWeeklyWasteData();
        loadBrgyWasteVolume();
    });
    document.querySelector('.next-month').addEventListener('click', () => {
        currentMonth++;
        if (currentMonth > 12) { currentMonth = 1; currentYear++; }
        selectedWeek = 1;
        loadWeeklyWasteData();
        loadBrgyWasteVolume();
    });

    // Initial load
    loadDashboardSummary();
    loadWeeklyWasteData();
    setInterval(() => { loadDashboardSummary(); loadWeeklyWasteData(); loadBrgyWasteVolume(); }, 30000);

    // Brgy Waste Volume
    loadBarangays();
    document.getElementById('brgySelect').addEventListener('change', loadBrgyWasteVolume);
    // Optionally, auto-select first barangay
    setTimeout(() => {
        const select = document.getElementById('brgySelect');
        if (select && select.options.length > 1) {
            select.selectedIndex = 1;
            loadBrgyWasteVolume();
        }
    }, 800);
});

function getWeeklyWasteUrl() {
    const params = new URLSearchParams({ year: currentYear, month: currentMonth });
    if (selectedWeek !== null) params.append('week', selectedWeek);
    return `../api/get_weekly_sensor_data.php?${params.toString()}`;
}

async function loadWeeklyWasteData() {
    try {
        const res = await fetch(getWeeklyWasteUrl());
        const data = await res.json();
        if (!data.success) return;

        // Update bar chart
        if (barChart && Array.isArray(data.dailyData)) {
            const dailyTons = data.dailyData.map(d => (d.daily_count || 0) * 0.001);
            barChart.data.datasets[0].data = dailyTons;
            barChart.update();
        }

        // Update week cards
        const weekGrid = document.querySelector('.week-grid .row');
        if (weekGrid && Array.isArray(data.weeklyData)) {
            weekGrid.innerHTML = '';
            let selectedWeekObj = null;
            data.weeklyData.forEach((w) => {
                const isActive = (selectedWeek || data.selectedWeek) === w.week_of_month;
                if (isActive) selectedWeekObj = w;
                const tons = (w.total_count * 0.001).toFixed(1);
                const badgeClass = isActive ? 'bg-white text-success' : 'bg-success';
                const cardClass = isActive ? 'active' : '';
                const col = document.createElement('div');
                col.className = 'col';
                col.innerHTML = `
                    <div class="week-card p-3 rounded border ${cardClass}" 
                        data-week="${w.week_of_month}"
                        data-tons="${tons}"
                        data-utilization="${(w.utilization || 0).toFixed(1)}%"
                        data-range="${w.date_range}"
                        title="Week ${w.week_of_month} • ${w.date_range} • ${w.total_count} units • ${(w.total_count * 0.001).toFixed(2)} tons">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 fw-semibold">Week ${w.week_of_month}</h6>
                                <p class="text-muted small mb-2">${w.date_range}</p>
                            </div>
                            <span class="badge ${badgeClass}">${tons}T</span>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar ${isActive ? 'bg-white' : 'bg-success'}" 
                                style="width: ${Math.min(100, tons / 3 * 100)}%"></div>
                        </div>
                    </div>
                `;
                weekGrid.appendChild(col);
            });

            // Show selected week display
            if (selectedWeekObj) {
                updateSelectedWeekDisplay(selectedWeekObj.week_of_month, selectedWeekObj.date_range);
            } else {
                updateSelectedWeekDisplay();
            }

            // Add click event for week selection
            weekGrid.querySelectorAll('.week-card').forEach(card => {
                card.addEventListener('click', function() {
                    document.querySelectorAll('.week-card').forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    selectedWeek = parseInt(this.getAttribute('data-week'));
                    // Set global variables for selected week waste and performance
                    window.selectedWeekWaste = this.getAttribute('data-tons') + ' tons';
                    window.selectedWeekPerformance = this.getAttribute('data-utilization');
                    updateSelectedWeekDisplay(this.getAttribute('data-week'), this.getAttribute('data-range'));
                    document.getElementById('wasteLastWeek').textContent = window.selectedWeekWaste;
                    document.getElementById('weeklyPerformance').textContent = window.selectedWeekPerformance;
                    loadWeeklyWasteData();
                });
            });
        }

        // Update month-year label
        const monthYearEl = document.querySelector('.month-year');
        if (monthYearEl) {
            monthYearEl.textContent = data.month;
        }

        // Update daily stats labels and values
        const dayStatsLabels = document.getElementById('dayStatsLabels');
        const dayStatsValues = document.getElementById('dayStatsValues');
        if (dayStatsLabels && dayStatsValues && Array.isArray(data.dailyData)) {
            dayStatsLabels.innerHTML = '';
            dayStatsValues.innerHTML = '';
            data.dailyData.forEach((d) => {
                // Label
                const labelCol = document.createElement('div');
                labelCol.className = 'col';
                labelCol.innerHTML = `<small class="text-muted">${d.day_name}</small>`;
                dayStatsLabels.appendChild(labelCol);
                // Value
                const valueCol = document.createElement('div');
                valueCol.className = 'col';
                let valueHtml = `<div class="py-1 rounded${d.daily_count > 0 ? ' bg-light' : ''}" title="${d.day_name}, ${d.day_number} • Count: ${d.daily_count} • ${(d.daily_count * 0.001).toFixed(2)} tons">${d.day_number}`;
                if (d.daily_count > 0) {
                    valueHtml += ` <small class="d-block text-success">${(d.daily_count * 0.001).toFixed(1)}T</small>`;
                }
                valueHtml += '</div>';
                valueCol.innerHTML = valueHtml;
                dayStatsValues.appendChild(valueCol);
            });
        }

        // Set selectedWeek after first load
        if (selectedWeek === null) {
            selectedWeek = data.selectedWeek;
        }

    } catch (e) {
        // Optionally handle error
    }
}


var ctx3 = document.getElementById("chart-line-tasks").getContext("2d");
new Chart(ctx3, {
    type: "line",
    data: {
        labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        datasets: [{
            label: "Dumpsite Capacity",
            tension: 0,
            borderWidth: 2,
            pointRadius: 3,
            pointBackgroundColor: "#43A047",
            pointBorderColor: "transparent",
            borderColor: "#43A047",
            backgroundColor: "transparent",
            fill: true,
            data: [75, 120, 200, 150, 400, 320, 350, 270, 500],
            maxBarThickness: 6
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
        },
        interaction: { intersect: false, mode: 'index' },
        scales: {
            y: {
                grid: {
                    drawBorder: false,
                    display: true,
                    drawOnChartArea: true,
                    drawTicks: false,
                    borderDash: [4, 4],
                    color: '#e5e5e5'
                },
                ticks: {
                    display: true,
                    padding: 10,
                    color: '#737373',
                    font: { size: 14, lineHeight: 2 },
                }
            },
            x: {
                grid: { drawBorder: false, display: false },
                ticks: {
                    display: true,
                    color: '#737373',
                    padding: 10,
                    font: { size: 14, lineHeight: 2 },
                }
            },
        },
    },
});


</script>
<style>
        .material-symbols-rounded {
            font-family: 'Material Symbols Rounded';
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
        }

        .waste-volume-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 26px -4px hsla(0, 0%, 8%, 0.15), 0 8px 9px -5px hsla(0, 0%, 8%, 0.06);
            transition: all 0.3s ease;
        }

        .waste-volume-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px -4px hsla(0, 0%, 8%, 0.2), 0 8px 12px -5px hsla(0, 0%, 8%, 0.1);
        }

        .card-header-section {
            padding-bottom: 1rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #344767;
            margin: 0;
        }

        .card-subtitle {
            font-size: 0.875rem;
            color: #67748e;
            margin: 0.5rem 0 0 0;
        }

        .barangay-selector {
            border-radius: 8px;
            border: 1px solid #e0e5ed;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .barangay-selector:focus {
            border-color: #5e72e4;
            box-shadow: 0 0 0 0.2rem rgba(94, 114, 228, 0.1);
        }

        .chart-container {
            position: relative;
            margin: 1.5rem 0;
            background: #f8f9ff;
            border-radius: 12px;
            padding: 1rem;
        }

        .progress-section {
            margin-top: 2rem;
        }

        .progress-container {
            background: #e9ecef;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }

        .progress-bar-custom {
            height: 20px;
            background: linear-gradient(90deg, #4CAF50 0%, #66BB6A 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: width 0.8s ease;
            position: relative;
        }

        .progress-text {
            color: white;
            font-size: 0.875rem;
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .waste-info-text {
            font-size: 0.8125rem;
            color: #67748e;
            margin-top: 0.75rem;
        }

        .card-footer-section {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
        }

        .update-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #67748e;
            font-size: 0.8125rem;
        }

        .update-icon {
            font-size: 18px;
        }

        /* Demo styles */
        .demo-container {
            padding: 2rem;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .demo-canvas {
            background: #ffffff;
            border-radius: 8px;
            width: 100%;
            height: 170px;
        }
    </style>
</body>
</html>