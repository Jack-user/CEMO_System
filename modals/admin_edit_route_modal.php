<?php
// Fetch all barangays for dropdown
$sql = "SELECT barangay FROM barangays_table WHERE city = 'Bago City'";
$result = $conn->query($sql);
$allBarangays = ($result && $result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<div class="modal fade" id="editRouteModal" tabindex="-1" aria-labelledby="editRouteLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="../backend/admin_fetch_routes.php" id="editRouteForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editRouteLabel">Edit Route End Point</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="route_id" id="editRouteId">
          <div class="mb-3">
              <label>Current Start Point</label>
              <input type="text" id="currentStartPoint" class="form-control" disabled>
          </div>
          <div class="mb-3">
              <label>Current End Point</label>
              <input type="text" id="currentEndPoint" class="form-control" disabled>
          </div>
          <div class="mb-3">
              <label>New End Point</label>
              <select name="end_point" id="editEndPoint" class="form-select" required>
                  <?php foreach ($allBarangays as $barangay): ?>
                      <option value="<?= htmlspecialchars($barangay['barangay']); ?>">
                          <?= htmlspecialchars($barangay['barangay']); ?>
                      </option>
                  <?php endforeach; ?>
              </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Update Route</button>
        </div>
      </div>
    </form>
  </div>
</div>
