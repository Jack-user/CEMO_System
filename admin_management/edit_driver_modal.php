<!-- Edit Driver Modal -->
<div class="modal fade" id="editDriverModal" tabindex="-1" aria-labelledby="editDriverModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="update_driver.php" id="editDriverForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Driver</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="driver_id" id="editDriverId">
          <div class="row g-3">
            <!-- Example fields -->
            <div class="col-md-6">
              <label>First Name</label>
              <input type="text" name="first_name" id="editDriverFirstName" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>Last Name</label>
              <input type="text" name="last_name" id="editDriverLastName" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>Gender</label>
              <select name="gender" id="editDriverGender" class="form-select" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
            <div class="col-md-6">
              <label>Age</label>
              <input type="number" name="age" id="editDriverAge" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>Contact</label>
              <input type="text" name="contact" id="editDriverContact" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>Address</label>
              <input type="text" name="address" id="editDriverAddress" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>License No.</label>
              <input type="text" name="license_no" id="editDriverLicenseNo" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>Password</label>
              <div class="input-group">
                <input type="password" name="password" id="editDriverPassword" class="form-control">
                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="editDriverPassword">👁️‍🗨️</button>
              </div>
              <div class="form-text">Leave blank if unchanged.</div>
            </div>
          </div>
        </div>
        <div class="modal-footer mt-2">
          <button type="submit" class="btn btn-primary">Update Driver</button>
        </div>
      </div>
    </form>
  </div>
</div>
