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
// Limit progress to 98% maximum
if ($progress > 98) $progress = 98;

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
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="forecastedWasteNextWeek">-- tons</div>
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
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="wasteCollectionProgress">
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
                                <button class="btn btn-sm btn-outline-primary" onclick="showBrgyDetails()">
                                    <i class="material-symbols-rounded me-1">visibility</i>
                                    View Barangay Details
                                </button>
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
                <div class="col-lg-12 col-md-8 mt-4 mb-4">
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

                <!-- <div class="col-lg-6 col-md-6 mt-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-0">Dumpsite Area</h6>
                            <p class="text-sm">Yearly Waste Forecast for Dumpsite Capacity</p>
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
                </div> -->
        </div>
    </div>
</div>

     <!-- Barangay Details Modal -->
    <div class="modal fade" id="brgyDetailsModal" tabindex="-1" aria-labelledby="brgyDetailsModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="brgyDetailsModalLabel">
                        <i class="material-symbols-rounded me-2">location_on</i>
                        Barangay Collection Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="material-symbols-rounded text-primary me-2">calendar_today</i>
                                <span id="selectedDateInfo" class="fw-semibold">Select a day to view details</span>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="d-flex align-items-center justify-content-end">
                                <i class="material-symbols-rounded text-success me-2">recycling</i>
                                <span id="totalWasteInfo" class="text-muted">Total: -- tons</span>
                            </div>
                        </div>
                    </div>
                    
                    
                    <!-- Day Selection -->
                    <div class="mb-4">
                        <div class="row row-cols-7 g-2" id="daySelectionGrid">
                            <!-- Days will be populated here -->
                        </div>
                    </div>
                    
                    <!-- Barangay Data Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                                <thead class="table-white">
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Barangay</th>
                                        <th class="text-center">Collection Count</th>
                                        <th class="text-center">Tons</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                            <tbody id="brgyDetailsTable">
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="material-symbols-rounded me-2">info</i>
                                        Select a day to view barangay collection details
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="exportBrgyData()">
                        <i class="material-symbols-rounded me-1">download</i>
                        Export Data
                    </button>
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

// --- Waste Forecast Loading ---
async function loadWasteForecast() {
    try {
        const res = await fetch('../api/get_waste_forecast.php?lookback_days=35');
        const data = await res.json();
        console.log('Waste forecast API response:', data); // Debug log

        if (data.success && data.forecasts && data.forecasts.length > 0) {
            // Calculate total forecasted waste for next week
            const totalForecast = data.forecasts.reduce((sum, forecast) => {
                return sum + (forecast.forecasted_tons || 0);
            }, 0);
            
            // Update the forecast display
            document.getElementById('forecastedWasteNextWeek').textContent = 
                totalForecast.toFixed(2) + ' tons';
            
            // Add model info as tooltip or console log
            console.log('Forecast model:', data.model_info);
            console.log('Total forecasted waste:', totalForecast.toFixed(2), 'tons');
            console.log('Number of barangays with forecasts:', data.forecasts.length);
            
        } else {
            throw new Error(data.error || 'No forecast data available');
        }
    } catch (error) {
        console.error('Error loading waste forecast:', error);
        document.getElementById('forecastedWasteNextWeek').textContent = 'Error';
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
let currentBrgyData = null;
let selectedDay = null;

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
            onClick: (event, elements) => {
                if (elements.length > 0) {
                    const dayIndex = elements[0].index;
                    showBrgyDetailsForDay(dayIndex);
                }
            },
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
    loadWasteForecast();
    loadWeeklyWasteData();
    setInterval(() => { 
        loadDashboardSummary(); 
        loadWasteForecast();
        loadWeeklyWasteData(); 
        loadBrgyWasteVolume(); 
    }, 30000);

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

    // Handle window resize for modal positioning
    window.addEventListener('resize', () => {
        const modal = document.getElementById('brgyDetailsModal');
        if (modal && modal.classList.contains('show')) {
            adjustModalPosition();
        }
    });

    // Listen for sidebar toggle events
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            setTimeout(() => {
                const modal = document.getElementById('brgyDetailsModal');
                if (modal && modal.classList.contains('show')) {
                    adjustModalPosition();
                }
            }, 300); // Wait for sidebar animation to complete
        });
    }
});

// ✅ Merged function: Weekly chart + Average Daily Collection + Collection Efficiency
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

        // --- Update bar chart ---
        if (barChart && Array.isArray(data.dailyData)) {
            const dailyTons = data.dailyData.map(d => (d.daily_count || 0) * 0.001);
            barChart.data.labels = data.dailyData.map(d => d.day_name);
            barChart.data.datasets[0].data = dailyTons;
            barChart.update();

            // --- Average Daily Collection ---
            const totalWeekTons = dailyTons.reduce((a, b) => a + b, 0);
            const avgDaily = totalWeekTons / (dailyTons.length || 1);
            document.getElementById('avgDailyCollection').textContent =
                avgDaily.toFixed(2) + ' tons';

            // --- Collection Efficiency ---
            // Example: expected = 2 tons/day (adjust if you have a real target)
            const expectedPerDay = 2;
            const expectedTotal = expectedPerDay * (dailyTons.length || 1);
            const efficiency = expectedTotal > 0 ? (totalWeekTons / expectedTotal) * 100 : 0;
            document.getElementById('collectionEfficiency').textContent =
                efficiency.toFixed(0) + '%';

            // --- Waste Collection Progress (Current Week Only) ---
            const progressEl = document.getElementById('wasteCollectionProgress');
            if (progressEl) {
                // Prefer API-provided weekly utilization if available
                const weeklyUtil = (data.weeklyUtilization !== undefined && data.weeklyUtilization !== null)
                    ? Number(data.weeklyUtilization)
                    : efficiency; // fallback to computed efficiency
                progressEl.textContent = (isFinite(weeklyUtil) ? weeklyUtil.toFixed(1) : '0.0') + '%';
            }
        }

        // --- Update week cards ---
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
                 const sortedWeeks = [...data.weeklyData].sort((a, b) => a.week_of_month - b.week_of_month);
                    const last3Weeks = sortedWeeks.slice(-3);
                    console.log('Last 3 weeks:', last3Weeks);
                        const totalTons = last3Weeks.reduce((sum, w) => sum + (w.total_count * 0.001), 0);
                        // Calculate average
                        const avgTons = last3Weeks.length > 0 ? totalTons / last3Weeks.length : 0;
                        document.getElementById('forecastedWasteNextWeek').textContent = avgTons.toFixed(2) + ' tons';
                
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

        // --- Update month-year label ---
        const monthYearEl = document.querySelector('.month-year');
        if (monthYearEl) {
            monthYearEl.textContent = data.month;
        }

        // --- Update daily stats labels and values ---
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

        // --- Set selectedWeek after first load ---
        if (selectedWeek === null) {
            selectedWeek = data.selectedWeek;
        }

    } catch (e) {
        document.getElementById('avgDailyCollection').textContent = 'Err';
        document.getElementById('collectionEfficiency').textContent = 'Err';
    }
}

// --- Barangay Details Modal Functions ---
let selectedDate = new Date();
let currentWeekStart = new Date();

async function showBrgyDetails() {
    try {
        const modal = new bootstrap.Modal(document.getElementById('brgyDetailsModal'));
        
        // Adjust modal positioning based on sidebar state
        adjustModalPosition();
        
        modal.show();
        
        // Load weekly barangay data
        await loadWeeklyBrgyData();
    } catch (e) {
        console.error('Error showing barangay details:', e);
    }
}

function adjustModalPosition() {
    const modalDialog = document.querySelector('#brgyDetailsModal .modal-dialog');
    const sidebar = document.getElementById('sidenav-main');
    
    // Remove any existing positioning classes
    modalDialog.classList.remove('sidebar-hidden');
    
    if (sidebar && window.innerWidth >= 1200) {
        // Check if sidebar is visible on large screens
        const sidebarRect = sidebar.getBoundingClientRect();
        if (sidebarRect.width <= 0 || sidebarRect.left < 0) {
            // Sidebar is hidden or collapsed
            modalDialog.classList.add('sidebar-hidden');
        }
    } else if (window.innerWidth < 1200) {
        // Medium screens and below - use default responsive behavior
        modalDialog.classList.add('sidebar-hidden');
    }
}

async function loadWeeklyBrgyData() {
    try {
        const params = new URLSearchParams({ 
            year: currentYear, 
            month: currentMonth,
            week: selectedWeek || 1
        });
        
        const res = await fetch(`../api/get_daily_brgy_waste_details.php?${params.toString()}`);
        const data = await res.json();
        
        if (data.success) {
            currentBrgyData = data.daily_data;
            populateDaySelection();
        }
    } catch (e) {
        console.error('Error loading weekly barangay data:', e);
    }
}

function populateDaySelection() {
    const dayGrid = document.getElementById('daySelectionGrid');
    dayGrid.innerHTML = '';
    
    if (!currentBrgyData) return;
    
    currentBrgyData.forEach((day, index) => {
        const dayCard = document.createElement('div');
        dayCard.className = 'col';
        dayCard.innerHTML = `
            <div class="day-card p-3 rounded border text-center cursor-pointer ${selectedDay === index ? 'bg-primary text-white' : 'bg-light'}" 
                 onclick="selectDay(${index})" 
                 style="cursor: pointer; transition: all 0.2s;">
                <div class="fw-semibold">${day.day_name}</div>
                <div class="small">${day.day_number}</div>
                <div class="mt-1">
                    <span class="badge ${selectedDay === index ? 'bg-white text-primary' : 'bg-success'}">
                        ${day.total_tons.toFixed(2)}T
                    </span>
                </div>
            </div>
        `;
        dayGrid.appendChild(dayCard);
    });
}

function selectDay(dayIndex) {
    selectedDay = dayIndex;
    populateDaySelection();
    showDayBrgyDetails(dayIndex);
}

async function showDayBrgyDetails(dayIndex) {
    if (!currentBrgyData || !currentBrgyData[dayIndex]) return;
    
    const dayData = currentBrgyData[dayIndex];
    
    // Update header info
    document.getElementById('selectedDateInfo').textContent = 
        `${dayData.day_name}, ${dayData.date} (${dayData.day_number})`;
    document.getElementById('totalWasteInfo').textContent = 
        `Total: ${dayData.total_tons.toFixed(2)} tons`;
    
    // Populate table
    const tableBody = document.getElementById('brgyDetailsTable');
    tableBody.innerHTML = '';
    
    if (dayData.barangay_data.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    <i class="material-symbols-rounded me-2">info</i>
                    No collection data for this day
                </td>
            </tr>
        `;
        return;
    }
    
    // Calculate total for percentage calculation
    const totalCount = dayData.total_count;
    
    dayData.barangay_data.forEach((brgy, index) => {
        const progressWidth = totalCount > 0 ? Math.min(98, (brgy.daily_count / totalCount) * 100) : 0;
        const percentage = totalCount > 0 ? Math.min(98, (brgy.daily_count / totalCount) * 100) : 0;
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="text-center">
                <span class="badge ${index < 3 ? 'bg-warning text-dark' : 'bg-secondary'} fs-6">${index + 1}</span>
            </td>
            <td class="fw-semibold text-dark">${brgy.barangay}</td>
            <td class="text-center fw-bold text-primary">${brgy.daily_count.toLocaleString()}</td>
            <td class="text-center fw-bold text-success">${brgy.tons.toFixed(3)}</td>
            <td class="text-center">
                <span class="badge ${brgy.daily_count > 0 ? 'bg-success' : 'bg-secondary'}">
                    ${brgy.daily_count > 0 ? 'Collected' : 'Done'}
                </span>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

function showBrgyDetailsForDay(dayIndex) {
    showBrgyDetails();
    setTimeout(() => {
        selectDay(dayIndex);
    }, 300);
}

function exportBrgyData() {
    if (!currentBrgyData || selectedDay === null) {
        alert('Please select a day to export data.');
        return;
    }
    
    const dayData = currentBrgyData[selectedDay];
    const csvContent = [
        ['Barangay', 'Count', 'Tons', 'Vehicle', 'Driver', 'Plate Number'],
        ...dayData.barangay_data.map(brgy => [
            brgy.barangay,
            brgy.daily_count,
            brgy.tons.toFixed(3),
            brgy.vehicles,
            brgy.drivers,
            brgy.plate_numbers
        ])
    ].map(row => row.join(',')).join('\n');
    
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `barangay_collection_${dayData.date}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
}

// --- Simple Date Selection ---
async function loadDataForSelectedDate() {
    const dateInput = document.getElementById('datePicker');
    if (!dateInput.value) return;
    
    selectedDate = new Date(dateInput.value);
    
    try {
        const year = selectedDate.getFullYear();
        const month = selectedDate.getMonth() + 1;
        const day = selectedDate.getDate();
        
        // Calculate which week of the month this date falls into
        const firstDayOfMonth = new Date(year, month - 1, 1);
        const firstMonday = new Date(firstDayOfMonth);
        const dayOfWeek = firstMonday.getDay();
        const daysToMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
        firstMonday.setDate(firstMonday.getDate() - daysToMonday);
        
        const weekOfMonth = Math.ceil((day - firstMonday.getDate() + 1) / 7);
        
        const params = new URLSearchParams({ 
            year, 
            month,
            week: weekOfMonth,
            day: day
        });
        
        const res = await fetch(`../api/get_daily_brgy_waste_details.php?${params.toString()}`);
        const data = await res.json();
        
        if (data.success) {
            // Update the display with the selected date's data
            const dateStr = selectedDate.toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            document.getElementById('selectedDateInfo').textContent = dateStr;
            updateDateDisplay();
            updateSelectedDateInfo();
            
            // Clear the day selection grid
            const dayGrid = document.getElementById('daySelectionGrid');
            dayGrid.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="material-symbols-rounded me-2">info</i>
                        Showing data for ${data.date || 'selected date'}
                    </div>
                </div>
            `;
            
            // Update total waste info
            const totalTons = data.total_tons || 0;
            document.getElementById('totalWasteInfo').textContent = `Total: ${totalTons.toFixed(2)} tons`;
            
            // Display the barangay data
            showDayBrgyDetailsFromData(data);
        }
    } catch (e) {
        console.error('Error loading data for selected date:', e);
    }
}

function showDayBrgyDetailsFromData(data) {
    const tableBody = document.getElementById('brgyDetailsTable');
    tableBody.innerHTML = '';
    
    if (!data.barangay_data || data.barangay_data.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    <i class="material-symbols-rounded me-2">info</i>
                    No collection data for this date
                </td>
            </tr>
        `;
        return;
    }
    
    const totalCount = data.total_count || 0;
    
    data.barangay_data.forEach((brgy, index) => {
        const progressWidth = totalCount > 0 ? Math.min(98, (brgy.daily_count / totalCount) * 100) : 0;
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="text-center">
                <span class="badge ${index < 3 ? 'bg-warning text-dark' : 'bg-secondary'} fs-6">${index + 1}</span>
            </td>
            <td class="fw-semibold text-dark">${brgy.barangay}</td>
            <td class="text-center fw-bold text-primary">${brgy.daily_count.toLocaleString()}</td>
            <td class="text-center fw-bold text-success">${brgy.tons.toFixed(3)}</td>
            <td class="text-center">
                <span class="badge ${brgy.daily_count > 0 ? 'bg-success' : 'bg-secondary'}">
                    ${brgy.daily_count > 0 ? 'Collected' : 'Done'}
                </span>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// --- Weekly Calendar Functions ---
function getWeekStart(date) {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day;
    const weekStart = new Date(d);
    weekStart.setDate(diff);
    return weekStart;
}

function initializeWeekCalendar() {
    // Set up event listeners for navigation
    document.querySelector('.prev-week').addEventListener('click', () => {
        console.log('Previous week clicked');
        navigateWeek(-1);
    });
    document.querySelector('.next-week').addEventListener('click', () => {
        console.log('Next week clicked');
        navigateWeek(1);
    });
    
    // Load the current week
    loadWeekCalendar();
}

function navigateWeek(direction) {
    console.log('Navigating week:', direction, 'Current week start:', currentWeekStart.toDateString());
    
    // Create a new date to avoid mutating the original
    const newWeekStart = new Date(currentWeekStart);
    newWeekStart.setDate(newWeekStart.getDate() + (direction * 7));
    currentWeekStart = newWeekStart;
    
    console.log('New week start:', currentWeekStart.toDateString());
    
    loadWeekCalendar();
    
    // Auto-select the first day of the new week if no date is selected
    if (!selectedDate) {
        selectedDate = new Date(currentWeekStart);
        updateDateDisplay();
        updateSelectedDateInfo();
        loadDataForSelectedDate();
    }
}

function loadWeekCalendar() {
    const weekYear = document.querySelector('.week-year');
    const weekGrid = document.getElementById('weekCalendarGrid');
    
    // Update week/year display
    const weekEnd = new Date(currentWeekStart);
    weekEnd.setDate(weekEnd.getDate() + 6);
    
    const startMonth = currentWeekStart.toLocaleDateString('en-US', { month: 'short' });
    const endMonth = weekEnd.toLocaleDateString('en-US', { month: 'short' });
    const year = currentWeekStart.getFullYear();
    
    if (startMonth === endMonth) {
        weekYear.textContent = `${startMonth} ${year}`;
    } else {
        weekYear.textContent = `${startMonth} - ${endMonth} ${year}`;
    }
    
    // Generate week days
    weekGrid.innerHTML = '';
    for (let i = 0; i < 7; i++) {
        const dayDate = new Date(currentWeekStart);
        dayDate.setDate(dayDate.getDate() + i);
        
        const dayElement = document.createElement('div');
        dayElement.className = 'col';
        
        const isToday = isSameDay(dayDate, new Date());
        const isSelected = selectedDate && isSameDay(dayDate, selectedDate);
        
        dayElement.innerHTML = `
            <button class="btn btn-sm w-100 ${isSelected ? 'btn-primary' : isToday ? 'btn-outline-primary' : 'btn-outline-secondary'}" 
                    onclick="selectDateFromCalendar('${dayDate.toISOString().split('T')[0]}')">
                ${dayDate.getDate()}
            </button>
        `;
        
        weekGrid.appendChild(dayElement);
    }
    
    console.log('Week loaded:', currentWeekStart.toDateString(), 'to', new Date(currentWeekStart.getTime() + 6 * 24 * 60 * 60 * 1000).toDateString());
}

function isSameDay(date1, date2) {
    return date1.getDate() === date2.getDate() &&
           date1.getMonth() === date2.getMonth() &&
           date1.getFullYear() === date2.getFullYear();
}

function selectDateFromCalendar(dateString) {
    selectedDate = new Date(dateString);
    updateDateDisplay();
    loadDataForSelectedDate();
    loadWeekCalendar(); // Refresh to show selection
    updateSelectedDateInfo();
}

// --- Date Display Functions ---
function updateDateDisplay() {
    const dateDisplay = document.getElementById('selectedDateDisplay');
    if (selectedDate) {
        const dateStr = selectedDate.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric',
            year: 'numeric'
        });
        dateDisplay.textContent = dateStr;
    } else {
        dateDisplay.textContent = 'Choose Date';
    }
}

function goToToday() {
    selectedDate = new Date();
    currentWeekStart = getWeekStart(selectedDate);
    updateDateDisplay();
    loadWeekCalendar();
    loadDataForSelectedDate();
}

function updateSelectedDateInfo() {
    const selectedDateInfo = document.getElementById('selectedDateInfo');
    if (selectedDate) {
        const dateStr = selectedDate.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        selectedDateInfo.innerHTML = `
            <div class="col text-center">
                <div class="fw-semibold text-primary">${dateStr}</div>
                <div class="small text-muted">Click to view collection data</div>
            </div>
        `;
    } else {
        selectedDateInfo.innerHTML = `
            <div class="col text-muted small">No date selected</div>
        `;
    }
}

function clearDateSelection() {
    selectedDate = null;
    document.getElementById('selectedDateDisplay').textContent = 'Choose Date';
    updateSelectedDateInfo();
    
    // Clear the table
    const tableBody = document.getElementById('brgyDetailsTable');
    tableBody.innerHTML = `
        <tr>
            <td colspan="6" class="text-center text-muted py-4">
                <i class="material-symbols-rounded me-2">info</i>
                Select a date to view collection details
            </td>
        </tr>
    `;
    
    // Reset day selection grid
    loadWeeklyBrgyData();
}


var ctx3 = document.getElementById("chart-line-tasks").getContext("2d");
new Chart(ctx3, {
    type: "line",
    data: {
        labels: ["2025", "2026", "2027", "2028", "2029", "2030", "2031", "2032", "2033"],
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

        /* Barangay Details Modal Styles */
        .day-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .day-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-color: #007bff;
        }

        .day-card.bg-primary {
            border-color: #007bff;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
        }

        .progress {
            border-radius: 4px;
        }

        .badge {
            font-size: 0.75rem;
        }

        .modal-xl {
            max-width: 1200px;
        }

        /* Vehicle and Driver Info Styling */
        .vehicle-driver-info {
            font-size: 0.875rem;
        }

        .vehicle-info {
            color: #007bff;
            font-weight: 600;
        }

        .driver-info {
            color: #6c757d;
            font-size: 0.8rem;
        }

        .plate-info {
            color: #17a2b8;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .material-symbols-rounded {
            vertical-align: middle;
        }

        /* Simple Date Picker Styles */
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }

        /* Date Dropdown Styling */
        .dropdown-menu {
            border: 1px solid #dee2e6;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .form-control {
            border-radius: 0.375rem;
            border: 1px solid #ced4da;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .btn-sm {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }

        /* Weekly Calendar Styling */
        .week-grid .btn {
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .week-grid .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .week-grid .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .week-grid .btn-outline-primary {
            color: #007bff;
            border-color: #007bff;
        }

        .week-grid .btn-outline-secondary {
            color: #6c757d;
            border-color: #dee2e6;
        }

        .selected-date-info {
            background-color: #f8f9fa;
            border-radius: 0.375rem;
            padding: 0.5rem;
        }

        /* Clean Table Styling */
        .table {
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .table-dark th {
            background-color: #343a40;
            border-color: #454d55;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
            transform: translateY(-1px);
            transition: all 0.2s ease;
        }

        .table tbody tr {
            border-bottom: 1px solid #e9ecef;
        }

        .table tbody tr:last-child {
            border-bottom: none;
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
        }

        .progress {
            border-radius: 0.375rem;
            background-color: #e9ecef;
        }

        .progress-bar {
            border-radius: 0.375rem;
            transition: width 0.3s ease;
        }

        /* Modal positioning to avoid sidebar overlap */
        .modal {
            z-index: 1055;
        }

        .modal-backdrop {
            z-index: 1050;
        }

        /* Ensure modal content fits within viewport */
        .modal-dialog {
            margin: 1rem auto;
            max-height: calc(100vh - 2rem);
        }

        .modal-content {
            max-height: calc(100vh - 2rem);
            display: flex;
            flex-direction: column;
        }

        .modal-body {
            overflow-y: auto;
            flex: 1;
            max-height: calc(100vh - 200px);
        }

        /* Responsive modal sizing */
        @media (max-width: 991.98px) {
            .modal-dialog {
                margin: 0.5rem;
                max-height: calc(100vh - 1rem);
            }
            
            .modal-content {
                max-height: calc(100vh - 1rem);
            }
            
            .modal-body {
                max-height: calc(100vh - 150px);
            }
        }

        /* Modal positioning - responsive and sidebar-aware */
        .modal-dialog {
            transition: all 0.3s ease;
        }

        /* Default positioning for large screens */
        @media (min-width: 1200px) {
            .modal-dialog {
                margin-left: 280px;
                margin-right: 1rem;
                max-width: calc(100vw - 300px);
            }
        }

        /* Medium screens - check if sidebar is collapsed */
        @media (min-width: 992px) and (max-width: 1199.98px) {
            .modal-dialog {
                margin-left: auto;
                margin-right: auto;
                max-width: calc(100vw - 2rem);
            }
        }

        /* Small screens and mobile */
        @media (max-width: 991.98px) {
            .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100vw - 1rem);
            }
        }

        /* Override for when sidebar is hidden */
        .modal-dialog.sidebar-hidden {
            margin-left: auto !important;
            margin-right: auto !important;
            max-width: calc(100vw - 2rem) !important;
        }
    </style>
</body>
</html>