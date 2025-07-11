<div class="d-flex justify-content-end mt-4 mb-3">
  <button id="toggleEventBtn" class="btn btn-warning btn-lg fw-bold shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#eventsSchedule" aria-expanded="false" aria-controls="eventsSchedule">
    📅 View Maintenance Schedule
  </button>
</div>

<!-- Upcoming Events Schedule - February -->
<div class="row mt-2">
  <div class="col-12">
    <div class="collapse" id="eventsSchedule">
      <div class="card shadow-lg">
        <div class="card-header bg-gradient-warning text-white">
          <h5 class="text-center text-uppercase font-weight-bold mb-0">View Maintenance Schedule</h5>
        </div>
        <div class="card-body px-0 pb-2">
          <div class="table-responsive p-0">
            <table class="table table-bordered align-items-center mb-0 text-center calendar-table">
              <thead class="bg-light">
                <tr>
                  <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Event</th>
                  <th colspan="29" class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">February</th>
                </tr>
                <tr>
                  <th></th>
                  <?php for ($i = 1; $i <= 29; $i++): ?>
                    <th><?= $i ?></th>
                  <?php endfor; ?>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><strong>Community Clean-up</strong></td>
                  <?php for ($i = 1; $i <= 29; $i++): ?>
                    <td><?= ($i == 5) ? 'Scheduled' : '' ?></td>
                  <?php endfor; ?>
                </tr>
                <tr>
                  <td><strong>Waste Collection Awareness</strong></td>
                  <?php for ($i = 1; $i <= 29; $i++): ?>
                    <td><?= ($i == 12) ? 'Scheduled' : '' ?></td>
                  <?php endfor; ?>
                </tr>
                <tr>
                  <td><strong>Bago City Fiesta</strong></td>
                  <?php for ($i = 1; $i <= 29; $i++): ?>
                    <td><?= ($i == 19) ? 'Scheduled' : '' ?></td>
                  <?php endfor; ?>
                </tr>
                <tr>
                  <td><strong>Environmental Seminar</strong></td>
                  <?php for ($i = 1; $i <= 29; $i++): ?>
                    <td><?= ($i == 25) ? 'Scheduled' : '' ?></td>
                  <?php endfor; ?>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Calendar-specific styles -->
<style>
.calendar-table th, .calendar-table td {
    font-size: 0.85rem;
    padding: 0.25rem 0.4rem;
}
.calendar-table th {
    background: #f8f9fa;
}
.calendar-table td {
    background: #fff;
}
.calendar-table td:empty {
    background: #f5f5f5;
}
</style>

<!-- Calendar-specific script -->
<script>
const toggleBtn = document.getElementById('toggleEventBtn');
const collapseElement = document.getElementById('eventsSchedule');

if (collapseElement) {
  // Set button text based on the initial state
  if (collapseElement.classList.contains('show')) {
      toggleBtn.textContent = "📅 Unview Maintenance Schedule";
  } else {
      toggleBtn.textContent = "📅 View Maintenance Schedule";
  }

  collapseElement.addEventListener('hidden.bs.collapse', function () {
      toggleBtn.textContent = "📅 View Maintenance Schedule";
  });

  collapseElement.addEventListener('shown.bs.collapse', function () {
      toggleBtn.textContent = "📅 Unview Maintenance Schedule";
  });
}
</script>
