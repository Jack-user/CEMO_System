<?php
include '../includes/conn.php';
include '../includes/header.php';
// ✅ 1. HANDLE THE UPDATE REQUEST FIRST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $route_id = $_POST['route_id'];
    $new_end = $_POST['end_point'];

    $stmt = $conn->prepare("UPDATE route_table SET end_point = ? WHERE route_id = ?");
    $stmt->bind_param("si", $new_end, $route_id);

    if ($stmt->execute()) {
        $_SESSION['msg'] = "Route successfully updated!";
    } else {
        $_SESSION['msg'] = "Update failed.";
    }

    header("Location: ../admin_management/admin_map.php");
    exit();
}

// ✅ 2. FETCH DATA FOR DISPLAY
$sql = "SELECT r.*, w.vehicle_name 
        FROM route_table r
        LEFT JOIN waste_service_table w ON r.route_id = w.route_id";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
?>
<tr>
    <td>
        <div class="d-flex px-2 py-1">
            <div><img src="../assets/img/logo.png" class="avatar avatar-sm me-3 border-radius-lg" alt="route"></div>
            <div class="d-flex flex-column justify-content-center">
                <h6 class="mb-0 text-sm"><?= htmlspecialchars($row['vehicle_name']); ?></h6>
            </div>
        </div>
    </td>
    <td>
        <p class="text-xs font-weight-bold mb-0 text-center">
            Start Point: <?= htmlspecialchars($row['start_point']); ?> → End Point: <?= htmlspecialchars($row['end_point']); ?>
        </p>
    </td>
    <td class="align-middle text-center">
        <a href="#" class="badge badge-sm bg-gradient-warning view-route"
           data-barangay="<?= htmlspecialchars($row['end_point']); ?>">
            View
        </a>
    </td>
    <td class="text-center">
        <a href="#" class="badge badge-sm bg-gradient-success edit-route-btn"
           data-route-id="<?= $row['route_id']; ?>"
           data-end-point="<?= htmlspecialchars($row['end_point']); ?>"
           data-start-point="<?= htmlspecialchars($row['start_point']); ?>">
           Edit
        </a>
    </td>
</tr>
<?php endwhile; else: ?>
<tr><td colspan="4" class="text-center text-secondary">No routes found.</td></tr>
<?php endif; ?>
