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
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Predicted Waste Today</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">0.22 tons</div>
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
                <div class="col-xl-3 col-md-6">
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
                <div class="col-lg-4 col-md-6 mt-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-0">Waste Collections</h6>
                            <p class="text-sm">Weekly Waste Collection Records</p>
                            <div class="pe-2">
                                <div class="chart">
                                    <canvas id="chart-bars" class="chart-canvas" height="170"></canvas>
                                </div>
                            </div>
                            <hr class="dark horizontal">
                            <div class="d-flex">
                                <i class="material-symbols-rounded text-sm my-auto me-1">schedule</i>
                                <p class="mb-0 text-sm">Campaign sent 2 days ago</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mt-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-0">Brgy Waste Volume</h6>
                            <p class="text-sm">Yearly Waste Volume Prediction for Each Barangay</p>
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
                <div class="col-lg-4 mt-4 mb-3">
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
                </div>
            <div class="col-xl-3 col-md-6 mb-4">
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
            </div>
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
</script>
</html>
</body>
