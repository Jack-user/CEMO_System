<?php
session_start();
include '../includes/conn.php';
// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) { // Change to your session variable
    header("Location: ../login_page/sign-in.php");
    exit();
}

$page_title = "Dashboard";
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
$query = "SELECT count FROM sensor WHERE sensor_id = 1 LIMIT 1";
$result = $mysqli->query($query);

$tons = 0.0;
if ($result && $row = $result->fetch_assoc()) {
    $count = (int)$row['count'];
    // Convert count to tons; replace 0.01 with your actual conversion factor
    $tons = $count * 0.001;
}
?>
<body>
    <!-- Include the Sidebar -->
    <?php include '../sidebar/admin_sidebar.php'; ?>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <!-- Main Content -->
    <!-- Main Content -->
    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">
            <h1 class="h3 mb-4 text-gray-800">Dashboard</h1>
            <div class="row">
                <!-- Cards Section -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Collected Waste Today</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo number_format($tons, 2); ?> tons
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="material-symbols-rounded opacity-10">delete</i>
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
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Waste Collected Last Week</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">1.23 tons</div>
                                </div>
                                <div class="col-auto">
                                    <i class="material-symbols-rounded opacity-10">recycling</i>
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
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Waste Collection Progress</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">70%</div>
                                </div>
                                <div class="col-auto">
                                    <i class="material-symbols-rounded opacity-10">bar_chart</i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row">
                <div class="col-lg-10 col-md-12 mt-4 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 pb-0 d-flex justify-content-between align-items-center px-4">
                            <div class="text-center flex-grow-1">
                                <h5 class="mb-1 fw-semibold text-success">Waste Collected</h5>
                                <p class="text-muted mb-0">Weekly Waste Collection Performance</p>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle d-flex align-items-center" 
                                        type="button" 
                                        id="weekDropdown" 
                                        data-bs-toggle="dropdown" 
                                        aria-expanded="false">
                                    <span>View Details</span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end p-3" aria-labelledby="weekDropdown" style="width: 350px;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <button class="btn btn-sm btn-outline-secondary prev-month"><i class="material-symbols-rounded">chevron_left</i></button>
                                        <h6 class="mb-0 fw-semibold month-year">October 2023</h6>
                                        <button class="btn btn-sm btn-outline-secondary next-month"><i class="material-symbols-rounded">chevron_right</i></button>
                                    </div>
                                    <div class="week-grid">
                                        <div class="row row-cols-2 g-2">
                                            <div class="col">
                                                <div class="week-card p-3 rounded border">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1 fw-semibold">Week 1</h6>
                                                            <p class="text-muted small mb-2">Oct 1-7</p>
                                                        </div>
                                                        <span class="badge bg-success">2.4T</span>
                                                    </div>
                                                    <div class="progress mt-2" style="height: 6px;">
                                                        <div class="progress-bar bg-success" style="width: 75%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="week-card p-3 rounded border">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1 fw-semibold">Week 2</h6>
                                                            <p class="text-muted small mb-2">Oct 8-14</p>
                                                        </div>
                                                        <span class="badge bg-success">2.1T</span>
                                                    </div>
                                                    <div class="progress mt-2" style="height: 6px;">
                                                        <div class="progress-bar bg-success" style="width: 65%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="week-card p-3 rounded border">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1 fw-semibold">Week 3</h6>
                                                            <p class="text-muted small mb-2">Oct 15-21</p>
                                                        </div>
                                                        <span class="badge bg-success">2.6T</span>
                                                    </div>
                                                    <div class="progress mt-2" style="height: 6px;">
                                                        <div class="progress-bar bg-success" style="width: 85%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="week-card p-3 rounded border active">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1 fw-semibold">Week 4</h6>
                                                            <p class="text-muted small mb-2">Oct 22-28</p>
                                                        </div>
                                                        <span class="badge bg-white text-success">2.3T</span>
                                                    </div>
                                                    <div class="progress mt-2" style="height: 6px;">
                                                        <div class="progress-bar bg-white" style="width: 72%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0 fw-semibold">Selected Week Details</h6>
                                            <span class="text-success small">Oct 22-28</span>
                                        </div>
                                        <div class="day-stats">
                                            <div class="row row-cols-7 g-1 text-start mb-2">
                                                <div class="col"><small class="text-muted">Sun</small></div>
                                                <div class="col"><small class="text-muted">Mon</small></div>
                                                <div class="col"><small class="text-muted">Tue</small></div>
                                                <div class="col"><small class="text-muted">Wed</small></div>
                                                <div class="col"><small class="text-muted">Thu</small></div>
                                                <div class="col"><small class="text-muted">Fri</small></div>
                                                <div class="col"><small class="text-muted">Sat</small></div>
                                            </div>
                                            <div class="row row-cols-7 g-1 text-center">
                                                <div class="col"><div class="py-1 rounded">22</div></div>
                                                <div class="col"><div class="py-1 rounded bg-light">23 <small class="d-block text-success">0.4T</small></div></div>
                                                <div class="col"><div class="py-1 rounded bg-light">24 <small class="d-block text-success">0.5T</small></div></div>
                                                <div class="col"><div class="py-1 rounded bg-light">25 <small class="d-block text-success">0.6T</small></div></div>
                                                <div class="col"><div class="py-1 rounded bg-light">26 <small class="d-block text-success">0.4T</small></div></div>
                                                <div class="col"><div class="py-1 rounded bg-light">27 <small class="d-block text-success">0.4T</small></div></div>
                                                <div class="col"><div class="py-1 rounded">28</div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-3 pb-1">
                            <div class="chart-container mx-auto" style="position: relative; height:300px; width:100%">
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
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-lg-4 col-md-6 mt-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-0">Brgy Waste Volume</h6>
                            <p class="text-sm">Monthly Waste Volume Prediction for Each Barangay</p>
                            <div class="pe-2">
                                <div class="chart">
                                    <canvas id="chart-line" class="chart-canvas" height="170"></canvas>
                                </div>
                            </div>
                            <hr class="dark horizontal">
                            <div class="d-flex">
                                <i class="material-symbols-rounded text-sm my-auto me-1">schedule</i>
                                <p class="mb-0 text-sm">Updated 4 min ago</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row"> 
                <div class="col-lg-4 mt-4 mb-3">
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
            <!-- <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Admin/Staff Users</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalAdmins; ?></div>
                                <div class="mt-1 text-sm">
                                    <span class="text-success font-weight-bold">+3%</span> than last month
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="material-symbols-rounded opacity-10">person</i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Client Users</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalClients; ?></div>
                                <div class="mt-1 text-sm">
                                    <span class="text-success font-weight-bold">+5%</span> than last month
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="material-symbols-rounded opacity-10">person</i>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
</div>
 
    <!-- Include the Footer -->
<?php include '../includes/footer.php'; ?>
</main>
<!-- Chart Scripts -->
<script src="../assets/js/plugins/chartjs.min.js"></script>
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
  <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
  
<script>
    // Chart 1 (Bar Chart - Waste Collections)
    var ctx = document.getElementById("chart-bars").getContext("2d");
    new Chart(ctx, {
        type: "bar",
        data: {
            labels: ["M", "T", "W", "T", "F", "S", "S"],
            datasets: [{
                label: "Waste Collected",
                tension: 0.4,
                borderWidth: 0,
                borderRadius: 4,
                borderSkipped: false,
                backgroundColor: "#43A047",
                data: [80, 55, 35, 60, 75, 45, 90],
                barThickness: 'flex'
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
                        borderDash: [5, 5],
                        color: '#e5e5e5'
                    },
                    ticks: {
                        suggestedMin: 0,
                        suggestedMax: 100,
                        beginAtZero: true,
                        padding: 10,
                        font: { size: 14, lineHeight: 2 },
                        color: "#737373"
                    },
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

    // Chart 2 (Line Chart - Brgy Waste Volume)
    var ctx2 = document.getElementById("chart-line").getContext("2d");
    new Chart(ctx2, {
        type: "line",
        data: {
            labels: ["J", "F", "M", "A", "M", "J", "J", "A", "S", "O", "N", "D"],
            datasets: [{
                label: "Waste Volume",
                tension: 0,
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: "#43A047",
                pointBorderColor: "transparent",
                borderColor: "#43A047",
                backgroundColor: "transparent",
                fill: true,
                data: [500, 250, 400, 120, 600, 900, 700, 550, 250, 800, 1000, 950],
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
                        color: '#737373',
                        padding: 10,
                        font: { size: 12, lineHeight: 2 },
                    }
                },
                x: {
                    grid: { drawBorder: false, display: false },
                    ticks: {
                        display: true,
                        color: '#737373',
                        padding: 10,
                        font: { size: 12, lineHeight: 2 },
                    }
                },
            },
        },
    });

    // Chart 3 (Line Chart - Dumpsite Area)
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
    document.addEventListener('DOMContentLoaded', function() {
    // Month navigation functionality
    const monthYearEl = document.querySelector('.month-year');
    const prevMonthBtn = document.querySelector('.prev-month');
    const nextMonthBtn = document.querySelector('.next-month');
    let currentDate = new Date();
    
    function updateMonthDisplay() {
        monthYearEl.textContent = new Intl.DateTimeFormat('en-US', { 
            month: 'long', 
            year: 'numeric' 
        }).format(currentDate);
        
        // Here you would update the weeks for the new month
        // This is just a placeholder - you'd need to calculate actual weeks
        const weeks = document.querySelectorAll('.week-item');
        weeks.forEach((week, index) => {
            week.textContent = `Week ${index+1} (${getWeekDates(currentDate, index+1)})`;
        });
    }
    
    function getWeekDates(date, weekNum) {
        // Simplified example - implement proper week calculation
        const month = date.getMonth();
        const year = date.getFullYear();
        const startDate = 1 + (weekNum-1)*7;
        let endDate = startDate + 6;
        
        // Adjust for month boundaries
        const daysInMonth = new Date(year, month+1, 0).getDate();
        if (endDate > daysInMonth) endDate = daysInMonth;
        
        const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", 
                          "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        
        return `${monthNames[month]} ${startDate}-${endDate}`;
    }
    
    prevMonthBtn.addEventListener('click', function() {
        currentDate.setMonth(currentDate.getMonth() - 1);
        updateMonthDisplay();
    });
    
    nextMonthBtn.addEventListener('click', function() {
        currentDate.setMonth(currentDate.getMonth() + 1);
        updateMonthDisplay();
    });
    
    // Week selection
    document.querySelectorAll('.week-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.week-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            // Here you would update the chart for the selected week
            const weekNum = this.textContent.match(/Week (\d+)/)[1];
            console.log(`Selected Week ${weekNum}`);
        });
    });
    
    updateMonthDisplay();
});

</script>
<style>
    .week-card {
        transition: all 0.2s ease;
        cursor: pointer;
        background: white;
    }
    .week-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }
    .week-card.active {
        background: #198754;
        color: white;
    }
    .week-card.active .text-muted {
        color: rgba(255,255,255,0.7) !important;
    }
    .day-stats .bg-light {
        background-color: rgba(25, 135, 84, 0.1) !important;
    }
</style>
</html>
</body>