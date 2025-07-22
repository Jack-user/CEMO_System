<!-- Add Driver Modal -->
<div class="modal fade" id="addDriverModal" tabindex="-1" aria-labelledby="addDriverModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="add_driver.php" id="addDriverForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Driver</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label>First Name</label>
              <input type="text" name="first_name" id="addDriverFirstName" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>Last Name</label>
              <input type="text" name="last_name" id="addDriverLastName" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>Gender</label>
              <select name="gender" id="addDriverGender" class="form-select" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
            <div class="col-md-6">
              <label>Age</label>
              <input type="number" name="age" id="addDriverAge" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>Contact</label>
              <input type="text" name="contact" id="addDriverContact" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>Address</label>
              <input type="text" name="address" id="addDriverAddress" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>License No.</label>
              <input type="text" name="license_no" id="addDriverLicenseNo" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>Password</label>
              <div class="input-group">
                <input type="password" name="password" id="addDriverPassword" class="form-control" required>
                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="addDriverPassword">👁️‍🗨️</button>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer mt-2">
          <button type="submit" class="btn btn-primary">Save Driver</button>
        </div>
      </div>
    </form>
  </div>
</div>
