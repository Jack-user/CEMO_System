<?php
session_start();
include '../includes/header.php';
include '../includes/conn.php';

if (!isset($_SESSION['admin_id'])) {
  header("Location: ../login_page/sign-in.php");
  exit();
}

// Fetch events from DB
$calendarEvents = [];

// 1. Fetch Events from schedule_table
$sqlEvent = "SELECT schedule_id, event_name, day, time, status FROM schedule_table ORDER BY day, time";
$resultEvent = $conn->query($sqlEvent);

if ($resultEvent && $resultEvent->num_rows > 0) {
  while ($row = $resultEvent->fetch_assoc()) {
    if ($row['status'] === 'Completed') continue; // ✅ Skip completed
    $icon = ($row['status'] === 'Scheduled') ? '🟢' : '✅';
    $time = strlen($row['time']) === 5 ? $row['time'] . ':00' : $row['time'];
    $startDateTime = $row['day'] . 'T' . $time;

    $calendarEvents[] = [
      'id'    => 'event_' . $row['schedule_id'],
      'title' => $icon . ' ' . $row['event_name'] . ' - ' . $row['status'],
      'start' => $startDateTime,
      'color' => ($row['status'] === 'Scheduled') ? '#0a0b0bff' : '#6c757d',
    ];
  }
}

// 2. Fetch Maintenance from maintenance_table
$sqlMaint = "SELECT maintenance_id, m_name, m_date, m_time, m_status FROM maintenance_table ORDER BY m_date, m_time";
$resultMaint = $conn->query($sqlMaint);

if ($resultMaint && $resultMaint->num_rows > 0) {
  while ($row = $resultMaint->fetch_assoc()) {
    if ($row['m_status'] === 'Completed') continue; // ✅ Skip completed
    $icon = ($row['m_status'] === 'Scheduled') ? '🚛' : '✅';
    $time = strlen($row['m_time']) === 5 ? $row['m_time'] . ':00' : $row['m_time'];
    $startDateTime = $row['m_date'] . 'T' . $time;

    $calendarEvents[] = [
      'id'    => 'maint_' . $row['maintenance_id'],
      'title' => $icon . ' ' . $row['m_name'] . ' - ' . $row['m_status'],
      'start' => $startDateTime,
      'color' => ($row['m_status'] === 'Scheduled') ? '#b8860b' : '#6c757d',
    ];
  }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Waste Collection Schedule</title>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css" rel="stylesheet">
  <link href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
  body {
    background: #f6f8fa;
    font-family: 'Segoe UI', Arial, sans-serif;
  }
  .calendar-wrapper {
    display: flex;
    gap: 32px;
    align-items: flex-start;
  }
  .calendar-sidebar {
  background: transparent;
  border: none;
  padding: 0;
  }
  .calendar-sidebar h6 {
    font-weight: 600;
    color: #49755c;
    margin-bottom: 16px;
    font-size: 1rem;
    letter-spacing: 0.5px;
  }
  .calendar-sidebar .form-check-label {
    font-weight: 400;
    color: #49755c;
    margin-left: 6px;
    font-size: 0.97rem;
  }
  #calendar {
    width: 100%;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(102,192,94,0.06);
    padding: 18px;
    border: 1px solid #e3e6ea;
    min-height: 600px;
  }
  .calendar-box {
  background: #ffffff;
  border-radius: 20px;
  padding: 20px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
  border: 1px solid #e3e6ea;
  margin-bottom: 24px;
}

.calendar-card {
  width: 100%;
  background: #fff;
  border-radius: 16px;
  padding: 12px;
  border: 1px solid #e3e6ea;
}

  .calendar-header {
    background: #49755c;
    color: #fff;
    padding: 22px 0 12px 0;
    border-bottom: 1px solid #e3e6ea;
    text-align: center;
    border-radius: 16px 16px 0 0;
    margin-bottom: 18px;
  }
  .calendar-header h5 {
    font-size: 1.35rem;
    font-weight: 600;
    margin: 0;
    letter-spacing: 0.5px;
  }
  .fc-toolbar-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #49755c;
    letter-spacing: 0.5px;
  }
  .fc-button {
    border-radius: 20px !important;
    background: #49755c !important;
    color: #fff !important;
    border: none !important;
    font-weight: 500;
    padding: 5px 10px !important;
    box-shadow: none !important;
    transition: background 0.2s;
    font-size: 0.97rem !important;
  }
  .fc-button:hover, .fc-button-active, .fc-button-primary:not(:disabled):active {
    background: #66c05e !important;
  }
  .fc-event {
    border-radius: 10px;
    box-shadow: none;
    font-size: 0.98rem;
    padding: 6px 12px;
    border: none;
    background: #eaf7ee !important;
    color: #49755c !important;
    margin-bottom: 3px;
    font-weight: 500;
    letter-spacing: 0.3px;
  }
  .fc-event:hover {
    background: #d4f5e9 !important;
    color: #2d4c3c !important;
  }
  .fc-daygrid-event-dot {
    display: none;
  }
  .fc-daygrid-day-number {
    font-weight: 600;
    color: #49755c;
    background: #eaf7ee;
    border-radius: 50%;
    padding: 2px 7px;
    margin-right: 2px;
    font-size: 0.97rem;
  }
  .today-label {
    font-size: 0.7rem;
    font-weight: bold;
    position: start;
    top: 2px;
    right: 4px;
    z-index: 10;
  }
  .fc-day-today {
  position: relative; /* Ensure the cell is the positioning context */
}

.fc-day-today::after {
  content: 'Today';
  font-size: 0.65rem;
  color: #e12626;
  font-weight: bold;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%); /* Center horizontally and vertically */
  background: #fff;
  padding: 2px 4px;
  border-radius: 4px;
  z-index: 10;
}

  .fc-col-header-cell-cushion {
    font-weight: 600;
    color: #49755c;
    font-size: 0.98rem;
  }
  .fc-scrollgrid {
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid #e3e6ea;
  }
  .floating-btn {
    position: fixed;
    bottom: 32px;
    right: 32px;
    z-index: 999;
    background: #49755c;
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 54px;
    height: 54px;
    font-size: 1.7rem;
    box-shadow: 0 2px 8px rgba(102,192,94,0.10);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
  }
  .floating-btn:hover {
    background: #66c05e;
  }
  .next-event-highlight {
  background-color: #e9f7ff !important;
  border-left: 5px solid #007bff !important;
}

.swal2-popup.blinking-alert {
  animation: blink 1s infinite;
}
@keyframes blink {
  0% { box-shadow: 0 0 5px red; }
  50% { box-shadow: 0 0 15px orange; }
  100% { box-shadow: 0 0 5px red; }
}

#upcomingEventsBox h6 {
  font-weight: 600;
  margin-bottom: 10px;
  color: #49755c;
}

#upcomingEventsBox .list-group-item {
  font-size: 0.9rem;
  border: none;
  padding: 6px 8px;
}

.btn-outline-success {
  border-color: #66c05e;
  color: #49755c;
}
.btn-outline-success:hover {
  background-color: #66c05e;
  color: white;
}
  </style>

</head>
<body>
<?php include '../sidebar/admin_sidebar.php'; ?>
<main class="main-content position-relative h-100 border-radius-lg">
  <?php include '../includes/navbar.php'; ?>
  </div>
  <!-- Calendar Content -->
  <div class="card-body px-4 pt-4">
    <div class="calendar-box">
      <!-- Filter Panel -->
      <div class="calendar-sidebar d-flex justify-content-start mb-3" style="gap: 24px; flex-wrap: wrap;">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="Scheduled" id="filterScheduled" checked>
          <label class="form-check-label" for="filterScheduled">Scheduled</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="Completed" id="filterCompleted" checked>
          <label class="form-check-label" for="filterCompleted">Completed</label>
        </div>
      </div>
      <!-- Calendar Box -->
      <div id="calendar"></div>
    </div>
  </div>

  <button class="floating-btn" title="Add Schedule" data-bs-toggle="modal" data-bs-target="#addScheduleModal">+</button>

  <!-- Updated Add Schedule Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="addScheduleForm" method="POST" action="add_schedule.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addScheduleModalLabel">Add Schedule</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">

          <!-- Schedule Type Selector -->
          <div class="mb-3">
            <label for="schedule_type" class="form-label">Schedule Type</label>
            <select class="form-select" id="schedule_type" name="schedule_type" required>
              <option value="Event" selected>Event</option>
              <option value="Maintenance">Maintenance</option>
            </select>
          </div>

          <!-- Event Schedule Section -->
          <div id="eventSection">
            <div class="mb-3">
              <label for="event_name" class="form-label">Event Name</label>
              <input type="text" class="form-control" name="event_name" id="event_name">
            </div>
            <div class="mb-3">
              <label for="day" class="form-label">Date</label>
              <input type="date" class="form-control" name="day" id="day">
            </div>
            <div class="mb-3">
              <label for="time" class="form-label">Time</label>
              <input type="time" class="form-control" name="time" id="time">
            </div>
            <div class="mb-3">
              <label for="status" class="form-label">Status</label>
              <select class="form-select" name="status" id="status">
                <option value="Scheduled">Scheduled</option>
                <option value="Completed">Completed</option>
              </select>
            </div>
          </div>

          <!-- Maintenance Schedule Section -->
          <div id="maintenanceSection" style="display: none;">
            <div class="mb-3">
              <label for="maintenance_name" class="form-label">Select Vehicle</label>
                <select class="form-select" name="maintenance_name" id="maintenance_name" required>
                <option value="" disabled selected>Select Vehicle</option>
                <?php
                $vehicleQuery = $conn->query("SELECT vehicle_name FROM waste_service_table ORDER BY vehicle_name ASC");
                while ($v = $vehicleQuery->fetch_assoc()) {
                  echo '<option value="' . htmlspecialchars($v['vehicle_name']) . '">' . htmlspecialchars($v['vehicle_name']) . '</option>';
                }
                ?>
              </select>
            </div>
            <div class="mb-3">
              <label for="m_date" class="form-label">Date</label>
              <input type="date" class="form-control" name="m_date" id="m_date">
            </div>
            <div class="mb-3">
              <label for="m_time" class="form-label">Time</label>
              <input type="time" class="form-control" name="m_time" id="m_time">
            </div>
            <div class="mb-3">
              <label for="maintenance_status" class="form-label">Status</label>
              <select class="form-select" name="maintenance_status" id="maintenance_status">
                <option value="Scheduled">Scheduled</option>
                <option value="Completed">Completed</option>
              </select>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Add</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

  <!-- Edit/Delete Modal -->
  <div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form id="editScheduleForm">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editScheduleModalLabel">Edit Schedule</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="schedule_id" id="edit_schedule_id">
            <div class="mb-3">
              <label for="edit_event_name" class="form-label">Event Name</label>
              <input type="text" class="form-control" name="event_name" id="edit_event_name" required>
            </div>
            <div class="mb-3">
              <label for="edit_day" class="form-label">Date</label>
              <input type="date" class="form-control" name="day" id="edit_day" required>
            </div>
            <div class="mb-3">
              <label for="edit_time" class="form-label">Time</label>
              <input type="time" class="form-control" name="time" id="edit_time" required>
            </div>
            <div class="mb-3">
              <label for="edit_status" class="form-label">Status</label>
              <select class="form-select" name="status" id="edit_status" required>
                <option value="Scheduled">Scheduled</option>
                <option value="Completed">Completed</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Save Changes</button>
            <button type="button" class="btn btn-danger" id="deleteScheduleBtn">Delete</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  <?php include '../includes/footer.php'; ?>
</main>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>

document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');
  let allEvents = <?php echo json_encode($calendarEvents); ?>;

  const now = new Date();
  const inOneMinute = new Date(now.getTime() + 1 * 60 * 1000);

  // Find and highlight next upcoming scheduled event
  let upcomingEvents = allEvents
    .filter(e => new Date(e.start) > now && e.title.includes("Scheduled"))
    .sort((a, b) => new Date(a.start) - new Date(b.start));

  if (upcomingEvents.length > 0) {
    upcomingEvents[0].className = 'next-event-highlight';
  }

  // Alert for event within 1 minute
  allEvents.forEach(event => {
    const eventDate = new Date(event.start);
    if (
      event.title.includes('Scheduled') &&
      eventDate > now &&
      eventDate <= inOneMinute
    ) {
      Swal.fire({
        icon: 'info',
        title: 'Upcoming Schedule',
        html: `<strong>${event.title.replace(/^🟢/, '').split(' - ')[0]}</strong> is scheduled soon.`,
        timer: 5000,
        showConfirmButton: false,
        customClass: {
          popup: 'blinking-alert'
        }
      });
    }
  });

  // Calendar setup
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    events: allEvents,
    eventClick: function(info) {
      const event = info.event;
      document.getElementById('edit_schedule_id').value = event.id;
      document.getElementById('edit_event_name').value = event.title.split(' - ')[0].replace(/^🟢|✅/, '').trim();
      const iso = event.start.toISOString();
      document.getElementById('edit_day').value = iso.split("T")[0];
      document.getElementById('edit_time').value = iso.split("T")[1].substring(0, 5);
      document.getElementById('edit_status').value = event.title.split(' - ')[1];
      var editModal = new bootstrap.Modal(document.getElementById('editScheduleModal'));
      editModal.show();
    }
  });

  calendar.render();

  // Toggleable Upcoming Events Card
  const toggleBtn = document.createElement('button');
  toggleBtn.className = 'btn btn-sm btn-outline-success mb-2';
  toggleBtn.innerHTML = 'Show Upcoming Events';
  toggleBtn.style.marginBottom = '10px';

  const upcomingBox = document.createElement('div');
  upcomingBox.className = 'calendar-card mt-2';
  upcomingBox.style.display = 'none';
  upcomingBox.id = 'upcomingEventsBox';
  upcomingBox.innerHTML = `<h6>Upcoming Events & Maintenance</h6><ul class="list-group list-group-flush">
  ${upcomingEvents.slice(0, 4).map(ev => {
    const date = new Date(ev.start);
    const type = ev.id.startsWith('maint_') ? 'Maintenance' : 'Event';
    const status = ev.title.split(' - ')[1].trim();
    return `<li class="list-group-item small">
      <strong>${type}</strong> <span class="badge bg-light text-dark">${status}</span><br>
      ${date.toLocaleDateString()} ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
    </li>`;
  }).join('')}
</ul>`;



  toggleBtn.addEventListener('click', () => {
    const box = document.getElementById('upcomingEventsBox');
    const visible = box.style.display === 'block';
    box.style.display = visible ? 'none' : 'block';
    toggleBtn.textContent = visible ? 'Show Upcoming Events' : 'Hide Upcoming Events';
  });

  const calendarBox = document.querySelector('.calendar-box');
  calendarBox.prepend(upcomingBox);
  calendarBox.prepend(toggleBtn);
});

// Filters
function filterEvents() {
  const showScheduled = document.getElementById('filterScheduled').checked;
  const showCompleted = document.getElementById('filterCompleted').checked;
  const filtered = allEvents.filter(ev => {
    if (ev.title.includes('Scheduled') && showScheduled) return true;
    if (ev.title.includes('Completed') && showCompleted) return true;
    return false;
  });
  calendar.removeAllEvents();
  filtered.forEach(ev => calendar.addEvent(ev));
}
document.getElementById('filterScheduled').addEventListener('change', filterEvents);
document.getElementById('filterCompleted').addEventListener('change', filterEvents);

// Add Schedule Submit
document.getElementById('addScheduleForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const formData = new FormData(this);
  const selectedDate = new Date(document.getElementById('day').value + 'T00:00:00');
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  if (selectedDate < today) {
    Swal.fire({
      icon: 'warning',
      title: 'Invalid Date',
      text: "You can't schedule an event in the past.",
    });
    return;
  }

  fetch('add_schedule.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Added!',
        text: 'Schedule added successfully.',
        timer: 1500,
        showConfirmButton: false
      }).then(() => {
        location.reload();
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Failed to add schedule.'
      });
    }
  });
});

// Edit Schedule
document.getElementById('editScheduleForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  fetch('edit_schedule.php', {
    method: 'POST',
    body: formData
  }).then(res => res.json())
  .then(data => {
    if (data.success) {
      Swal.fire({ icon: 'success', title: 'Updated!', text: 'Schedule updated.' })
        .then(() => location.reload());
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to update schedule.' });
    }
  });
});

// Delete Schedule
document.getElementById('deleteScheduleBtn').addEventListener('click', function() {
  const scheduleId = document.getElementById('edit_schedule_id').value;
  Swal.fire({
    title: 'Are you sure?',
    text: 'This schedule will be deleted permanently.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!',
  }).then(result => {
    if (result.isConfirmed) {
      fetch('edit_schedule.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'delete=1&schedule_id=' + encodeURIComponent(scheduleId)
      }).then(res => res.json())
      .then(data => {
        if (data.success) {
          Swal.fire({ icon: 'success', title: 'Deleted!', text: 'Schedule removed.' })
            .then(() => location.reload());
        } else {
          Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to delete schedule.' });
        }
      });
    }
  });
});
// Toggle between Event and Maintenance form
document.getElementById('schedule_type').addEventListener('change', function () {
  const type = this.value;
  const eventSection = document.getElementById('eventSection');
  const maintenanceSection = document.getElementById('maintenanceSection');

  const eventInputs = eventSection.querySelectorAll('input, select');
  const maintenanceInputs = maintenanceSection.querySelectorAll('input, select');

  if (type === 'Event') {
    eventSection.style.display = 'block';
    maintenanceSection.style.display = 'none';

    // Enable required on Event fields
    eventInputs.forEach(input => input.setAttribute('required', 'required'));
    // Disable required on Maintenance fields
    maintenanceInputs.forEach(input => input.removeAttribute('required'));

  } else {
    eventSection.style.display = 'none';
    maintenanceSection.style.display = 'block';

    // Enable required on Maintenance fields
    maintenanceInputs.forEach(input => input.setAttribute('required', 'required'));
    // Disable required on Event fields
    eventInputs.forEach(input => input.removeAttribute('required'));
  }
});

// ✅ Trigger it immediately on load (in case Event is selected by default)
document.getElementById('schedule_type').dispatchEvent(new Event('change'));

</script>
</body>
</html>

