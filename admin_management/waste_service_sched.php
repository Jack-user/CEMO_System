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
$sql = "SELECT s.schedule_id, w.vehicle_name, s.day, s.status 
        FROM schedule_table s 
        LEFT JOIN waste_service_table w ON w.waste_service_id = s.waste_service_id 
        ORDER BY s.day";

$result = $conn->query($sql);

// if ($result && $result->num_rows > 0) {
//   while ($row = $result->fetch_assoc()) {
//     $calendarEvents[] = [
//       'id'    => $row['schedule_id'],
//       'title' => $row['vehicle_name'] . ' - ' . $row['status'],
//       'start' => $row['day'],
//       'color' => ($row['status'] === 'Scheduled') ? '#198754' : '#6c757d',
//     ];
//   }
// }

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $icon = ($row['status'] === 'Scheduled') ? '🟢' : '✅';
    $calendarEvents[] = [
      'id'    => $row['schedule_id'],
      'title' => $icon . ' ' . $row['vehicle_name'] . ' - ' . $row['status'],
      'start' => $row['day'],
      'color' => ($row['status'] === 'Scheduled') ? '#198754' : '#6c757d',
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
    min-width: 200px;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 8px rgba(102,192,94,0.06);
    padding: 20px 16px;
    margin-right: 0;
    height: fit-content;
    border: 1px solid #e3e6ea;
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
  .calendar-card {
    width: 100%;
    background: transparent;
    border-radius: 16px;
    box-shadow: none;
    overflow: visible;
    padding: 0;
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
    border-radius: 8px !important;
    background: #49755c !important;
    color: #fff !important;
    border: none !important;
    font-weight: 500;
    padding: 7px 18px !important;
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
  </style>

</head>
<body>
<?php include '../sidebar/admin_sidebar.php'; ?>
<main class="main-content position-relative h-100 border-radius-lg">
  <?php include '../includes/navbar.php'; ?>

  <div class="dropdown d-flex justify-content-end mt-2 mb-2 me-3">
    <!-- <button class="btn btn-success fw-bold dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
      📅 View 
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
      <li><a class="dropdown-item" href="vehicle_assignment.php">Vehicle Assignment</a></li>
      <li><a class="dropdown-item" href="waste_service_sched.php">Waste Collection Schedule</a></li>
    </ul> -->
  </div>
    <div class="container-fluid px-4">
    <div class="row">
      <div class="col-12 mb-2">  
        <div class="card shadow-lg">
          <div class="card-header p-0 position-relative mt-n4 mx-4 z-index-2">
            <div class="calendar-header shadow-dark border-radius-lg pt-4 pb-3"> 
              <h5 class="text-white text-center text-uppercase font-weight-bold mb-0">WASTE CALENDAR</h5>
            </div>
            <div class="card-body px-4 pt-4">
              <div class="calendar-wrapper">
                <aside class="calendar-sidebar">
                  <h6>Filter by Status</h6>
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" value="Scheduled" id="filterScheduled" checked>
                    <label class="form-check-label" for="filterScheduled">Scheduled</label>
                  </div>
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" value="Completed" id="filterCompleted" checked>
                    <label class="form-check-label" for="filterCompleted">Completed</label>
                  </div>
                  <!-- Add more filters as needed -->
                </aside>
                <div class="calendar-card">
                  <div id="calendar"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <button class="floating-btn" title="Add Schedule" data-bs-toggle="modal" data-bs-target="#addScheduleModal">+</button>

  <!-- Add Schedule Modal -->
  <div class="modal fade" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form id="addScheduleForm" method="POST" action="add_schedule.php">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addScheduleModalLabel">Add Schedule</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="vehicle_name" class="form-label">Vehicle Name</label>
              <select class="form-select" name="vehicle_name" id="vehicle_name" required>
                <?php
                $vehicles = $conn->query("SELECT vehicle_name FROM waste_service_table");
                while ($v = $vehicles->fetch_assoc()) {
                  echo '<option value="' . htmlspecialchars($v['vehicle_name']) . '">' . htmlspecialchars($v['vehicle_name']) . '</option>';
                }
                ?>
              </select>
            </div>
            <div class="mb-3">
              <label for="day" class="form-label">Date</label>
              <input type="date" class="form-control" name="day" id="day" required>
            </div>
            <div class="mb-3">
              <label for="status" class="form-label">Status</label>
              <select class="form-select" name="status" id="status" required>
                <option value="Scheduled">Scheduled</option>
                <option value="Completed">Completed</option>
              </select>
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
              <label for="edit_vehicle_name" class="form-label">Vehicle Name</label>
              <select class="form-select" name="vehicle_name" id="edit_vehicle_name" required>
                <?php
                $vehicles = $conn->query("SELECT vehicle_name FROM waste_service_table");
                while ($v = $vehicles->fetch_assoc()) {
                  echo '<option value="' . htmlspecialchars($v['vehicle_name']) . '">' . htmlspecialchars($v['vehicle_name']) . '</option>';
                }
                ?>
              </select>
            </div>
            <div class="mb-3">
              <label for="edit_day" class="form-label">Date</label>
              <input type="date" class="form-control" name="day" id="edit_day" required>
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

  const calendar = new FullCalendar.Calendar(calendarEl, {
    timeZone: 'UTC',
    themeSystem: 'bootstrap5',
    initialView: 'dayGridMonth',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
    },
    weekNumbers: true,
    dayMaxEvents: true,
    events: allEvents,
    eventClick: function(info) {
      alert(`Vehicle: ${info.event.title}\nStatus: ${info.event.extendedProps.status}\nDate: ${info.event.startStr}`);
    },
    datesSet: function() {
      // Remove any existing 'Today' tags to prevent duplicates
      document.querySelectorAll('.fc-day-today .today-label').forEach(el => el.remove());

      // Add 'Today' label inside the .fc-day-today cell
      const todayCell = document.querySelector('.fc-day-today');
      if (todayCell) {
        const label = document.createElement('div');
        label.className = 'today-label';
        label.textContent = '';
        label.style.fontSize = '0.7rem';
        label.style.marginTop = '2px';
        label.style.fontWeight = 'bold';
        // label.style.color = '#e12626'; // match your theme if needed
        todayCell.appendChild(label);
      }
    }
  });

  calendar.render();
});

  document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    let allEvents = <?php echo json_encode($calendarEvents); ?>;
    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
      },
      events: allEvents,
      eventClick: function(info) {
        // Populate modal fields
        const event = info.event;
        const eventData = allEvents.find(ev => ev.id == event.id);
        document.getElementById('edit_schedule_id').value = event.id;
        document.getElementById('edit_vehicle_name').value = event.title.split(' - ')[0];
        document.getElementById('edit_day').value = event.startStr;
        document.getElementById('edit_status').value = event.title.split(' - ')[1];
        var editModal = new bootstrap.Modal(document.getElementById('editScheduleModal'));
        editModal.show();
      }
    });
    calendar.render();

    // Filter logic
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

    // Edit Schedule AJAX
    document.getElementById('editScheduleForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      fetch('edit_schedule.php', {
        method: 'POST',
        body: formData
      }).then(res => res.json())
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          alert('Failed to update schedule.');
        }
      });
    });

    // Delete Schedule AJAX
    document.getElementById('deleteScheduleBtn').addEventListener('click', function() {
      const scheduleId = document.getElementById('edit_schedule_id').value;
      if (confirm('Are you sure you want to delete this schedule?')) {
        fetch('edit_schedule.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: 'delete=1&schedule_id=' + encodeURIComponent(scheduleId)
        }).then(res => res.json())
        .then(data => {
          if (data.success) {
            location.reload();
          } else {
            alert('Failed to delete schedule.');
          }
        });
      }
    });
  });

  
</script>

</body>
</html>
<?php

