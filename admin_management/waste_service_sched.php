<?php
session_start();
include '../includes/header.php';
include '../includes/conn.php';

if (!isset($_SESSION['admin_id'])) {
  header("Location: ../index.php");
  exit();
}

// Fetch events from DB
$calendarEvents = [];

// Add approved client requests with their request_date and request_time, including extendedProps
$sqlRequests = "SELECT request_id, request_details, request_date, request_time, status 
                FROM client_requests 
                WHERE status = 'approved' 
                ORDER BY request_date, request_time";

$resultRequests = $conn->query($sqlRequests);

if ($resultRequests && $resultRequests->num_rows > 0) {
    while ($row = $resultRequests->fetch_assoc()) {
        if (empty($row['request_date']) || empty($row['request_time'])) continue;

        // Normalize time to HH:MM:SS if needed
        $time = strlen($row['request_time']) === 5 ? $row['request_time'] . ':00' : $row['request_time'];
        $startDateTime = $row['request_date'] . 'T' . $time;

        $calendarEvents[] = [
            'id' => 'request_' . $row['request_id'],
            'title' => '✅ ' . $row['request_details'] . ' - Approved',
            'start' => $startDateTime,
            'color' => '#28a745',
            'extendedProps' => [
                'request_time' => $row['request_time'] // Pass raw HH:MM for JS
            ]
        ];
    }
}

// 1. Fetch Events from schedule_table
$sqlEvent = "SELECT schedule_id, event_name, day, time, status FROM schedule_table ORDER BY day, time";
$resultEvent = $conn->query($sqlEvent);

if ($resultEvent && $resultEvent->num_rows > 0) {
  while ($row = $resultEvent->fetch_assoc()) {
    if ($row['status'] === 'Completed') continue; // Skip completed
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
$sqlMaint = "SELECT maintenance_id, m_name, m_date, m_time, status FROM maintenance_table ORDER BY m_date, m_time";
$resultMaint = $conn->query($sqlMaint);

if ($resultMaint && $resultMaint->num_rows > 0) {
  while ($row = $resultMaint->fetch_assoc()) {
    if ($row['status'] === 'Completed') continue; // Skip completed
    $icon = ($row['status'] === 'Scheduled') ? '🚛' : '✅';
    $time = strlen($row['m_time']) === 5 ? $row['m_time'] . ':00' : $row['m_time'];
    $startDateTime = $row['m_date'] . 'T' . $time;

    $calendarEvents[] = [
      'id' => 'maint_' . $row['maintenance_id'],
      'title' => $icon . ' ' . $row['m_name'] . ' - ' . $row['status'],
      'start' => $startDateTime,
      'color' => ($row['status'] === 'Scheduled') ? '#007bff' : '#6c757d', // example colors
    ];
  }
}

// Auto-complete past scheduled events and log them
$now = new DateTime();
$currentTime = $now->format('Y-m-d H:i:s'); // Define once

// 1. Handle Events from schedule_table
$stmt = $conn->prepare("
    UPDATE schedule_table 
    SET status = 'Completed' 
    WHERE status = 'Scheduled' 
      AND CONCAT(day, ' ', time) < ?
");
$stmt->bind_param("s", $currentTime);
$stmt->execute();
$stmt->close();

// 2. Handle Maintenance from maintenance_table
$stmt = $conn->prepare("
    UPDATE maintenance_table 
    SET status = 'Completed' 
    WHERE status = 'Scheduled' 
      AND CONCAT(m_date, ' ', m_time) < ?
");
$stmt->bind_param("s", $currentTime);
$stmt->execute();
$stmt->close();

// 3. Handle Client Requests (Approved only)
$stmt = $conn->prepare("
    UPDATE client_requests 
    SET status = 'Completed' 
    WHERE status = 'approved' 
      AND CONCAT(request_date, ' ', request_time) < ?
");
$stmt->bind_param("s", $currentTime);
$stmt->execute();
$stmt->close();

// Log Events
$stmt = $conn->prepare("
    INSERT IGNORE INTO past_events_log (original_id, type, name, date, time)
    SELECT CONCAT('event_', schedule_id), 'Event', event_name, day, time
    FROM schedule_table
    WHERE status = 'Completed' 
      AND CONCAT(day, ' ', time) < ?
      AND NOT EXISTS (
        SELECT 1 FROM past_events_log WHERE original_id = CONCAT('event_', schedule_id)
      )
");
$stmt->bind_param("s", $currentTime);
$stmt->execute();
$stmt->close();

// Log Maintenance
$stmt = $conn->prepare("
    INSERT IGNORE INTO past_events_log (original_id, type, name, date, time)
    SELECT CONCAT('maint_', maintenance_id), 'Maintenance', m_name, m_date, m_time
    FROM maintenance_table
    WHERE status = 'Completed' 
      AND CONCAT(m_date, ' ', m_time) < ?
      AND NOT EXISTS (
        SELECT 1 FROM past_events_log WHERE original_id = CONCAT('maint_', maintenance_id)
      )
");
$stmt->bind_param("s", $currentTime);
$stmt->execute();
$stmt->close();

// Log Client Requests
$stmt = $conn->prepare("
    INSERT IGNORE INTO past_events_log (original_id, type, name, date, time)
    SELECT CONCAT('event_', schedule_id), 'Event', event_name, day, time
    FROM schedule_table
    WHERE status = 'Completed' 
      AND CONCAT(day, ' ', time) < ?
      AND NOT EXISTS (
        SELECT 1 FROM past_events_log 
        WHERE original_id = CONCAT('event_', schedule_table.schedule_id)
          COLLATE utf8mb4_unicode_ci
      )
");
$stmt->bind_param("s", $currentTime);
$stmt->execute();
$stmt->close();

 $page_title = "Waste Calendar"; // Set the page title dynamically

?>
<body class="g-sidenav-show bg-gray-100">
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
      <!-- Removed event table, only calendar below -->
      <div id="calendar"></div>
    </div>
  </div>

  <button class="floating-btn" title="Add Schedule" data-bs-toggle="modal" data-bs-target="#addScheduleModal">+</button>

<!-- Recently Completed Events (Dynamic) -->
<div class="card mt-4">
  <div class="card-header bg-light">
    <h6 class="mb-0">Recently Completed Events</h6>
  </div>
  <div class="card-body p-0">
    <ul id="recentLogsList" class="list-group list-group-flush">
      <li class="list-group-item text-muted">Loading...</li>
    </ul>
  </div>
</div>

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
              <select class="form-select" name="time" id="time" required>
                <option value="" disabled selected>Select Time</option>
                <option value="08:00">8:00 AM</option>
                <option value="09:00">9:00 AM</option>
                <option value="10:00">10:00 AM</option>
                <option value="10:00">11:00 AM</option>
                <option value="10:00">01:00 PM</option>
                <option value="10:00">02:00 PM</option>
                <!-- Add more if needed -->
              </select>
            </div>
            <div class="mb-3">
              <label for="status" class="form-label">Status</label>
              <select class="form-select" name="status" id="status">
                <option value="Scheduled">Scheduled</option>
\              </select>
            </div>
          </div>

          <!-- Maintenance Schedule Section -->
          <div id="maintenanceSection" style="display: none;">
            <div class="mb-3">
              <label for="maintenance_name" class="form-label">Select Vehicle</label>
                <select class="form-select" name="maintenance_id" id="maintenance_name" required>
                <option value="" disabled selected>Select Vehicle</option>
                <?php
                $vehicleQuery = $conn->query("
                  SELECT waste_service_id, vehicle_name 
                  FROM waste_service_table 
                  ORDER BY vehicle_name ASC
                ");
                while ($v = $vehicleQuery->fetch_assoc()) {
                  echo '<option value="' . (int)$v['waste_service_id'] . '">' . htmlspecialchars($v['vehicle_name']) . '</option>';
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
              <select class="form-select" name="m_time" id="m_time" required>
                <option value="" disabled selected>Select Time</option>
                <option value="08:00">8:00 AM</option>
                <option value="09:00">9:00 AM</option>
                <option value="10:00">10:00 AM</option>
                <!-- Add more if needed -->
              </select>
            </div>
            <div class="mb-3">
              <label for="maintenance_status" class="form-label">Status</label>
              <select class="form-select" name="maintenance_status" id="maintenance_status" readonly>
                <option value="Scheduled">Scheduled</option>
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
            <h5 class="modal-title" id="editScheduleModalLabel">Edit Schedule & Maintenance</h5>
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
                <select class="form-select" name="time" id="edit_time" required>
                  <option value="" disabled>Select Time</option>
                  <option value="08:00">8:00 AM</option>
                  <option value="09:00">9:00 AM</option>
                  <option value="10:00">10:00 AM</option>
                  <option value="11:00">11:00 AM</option>
                  <option value="13:00">1:00 PM</option>
                  <option value="14:00">2:00 PM</option>
                </select>            </div>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');
  let allEvents = <?php echo json_encode($calendarEvents); ?>;

  const now = new Date();
  const inOneMinute = new Date(now.getTime() + 1 * 60 * 1000);

// Find and highlight next upcoming scheduled event (including client requests)
let upcomingEvents = allEvents
  .filter(e => {
    const eventDate = new Date(e.start);
    return eventDate > now && 
           (e.title.includes("Scheduled") || e.title.includes("Approved"));
  })
  .sort((a, b) => new Date(a.start) - new Date(b.start));

// Add type info to events for display
upcomingEvents = upcomingEvents.map(ev => {
  let type = 'Event';
  if (ev.id.startsWith('maint_')) type = 'Maintenance';
  if (ev.id.startsWith('request_')) type = 'Client Request';
  
  return { ...ev, type };
});

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
      let rawTitle = event.title.split(' - ')[0];
      let cleanTitle = rawTitle.replace(/^[🟢✅🚛]/, '').trim();
      document.getElementById('edit_event_name').value = cleanTitle;
      const iso = event.start.toISOString();
      document.getElementById('edit_day').value = iso.split("T")[0];

      // *** UPDATED: Use request_time for client requests ***
      const timeValue = event.id.startsWith('request_') && event.extendedProps.request_time
        ? event.extendedProps.request_time.substring(0,5)
        : iso.split("T")[1].substring(0, 5);

      document.getElementById('edit_time').value = timeValue;
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
upcomingBox.innerHTML = `<h6>Upcoming Events & Requests</h6><ul class="list-group list-group-flush"></ul>`;

// Update list content
function updateUpcomingList() {
  const list = upcomingBox.querySelector('ul');
  list.innerHTML = '';

  const now = new Date();
  const upcomingEvents = allEvents
    .filter(e => {
      const eventDate = new Date(e.start);
      return eventDate > now && 
             (e.title.includes("Scheduled") || e.title.includes("Approved"));
    })
    .map(ev => {
      let type = 'Event';
      if (ev.id.startsWith('maint_')) type = 'Maintenance';
      if (ev.id.startsWith('request_')) type = 'Client Request';
      return { ...ev, type };
    })
    .sort((a, b) => new Date(a.start) - new Date(b.start));

  if (upcomingEvents.length === 0) {
    const li = document.createElement('li');
    li.className = 'list-group-item text-muted';
    li.textContent = 'No upcoming events.';
    list.appendChild(li);
  } else {
    upcomingEvents.slice(0, 5).forEach(ev => {
      const date = new Date(ev.start);
      const status = ev.title.split(' - ')[1]?.trim() || 'Scheduled';
      const title = ev.title.split(' - ')[0].replace(/^✅|🟢|🚛/, '').trim();
      const li = document.createElement('li');
      li.className = 'list-group-item small';
      li.innerHTML = `
        <strong>${ev.type}</strong> 
        <span class="badge bg-light text-dark">${status}</span><br>
        <small>${title}</small><br>
        ${date.toLocaleDateString()} ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
      `;
      list.appendChild(li);
    });
  }
}

toggleBtn.addEventListener('click', () => {
  const box = document.getElementById('upcomingEventsBox');
  const isVisible = box.style.display === 'block';
  box.style.display = isVisible ? 'none' : 'block';
  toggleBtn.textContent = isVisible ? 'Show Upcoming Events' : 'Hide Upcoming Events';

  if (box.style.display === 'block') {
    updateUpcomingList(); // Refresh list on open
  }
});

// Initial render
updateUpcomingList();

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
        loadRecentLogs(); // refresh logs without reload
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


// Event hover popover for calendar events
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
      const event = info.event;
      document.getElementById('edit_schedule_id').value = event.id;
      document.getElementById('edit_event_name').value = event.title.split(' - ')[0].replace(/^🟢|✅/, '').trim();
      const iso = event.start.toISOString();
      document.getElementById('edit_day').value = iso.split("T")[0];
      // *** UPDATED here too ***
      const timeValue = event.id.startsWith('request_') && event.extendedProps.request_time
        ? event.extendedProps.request_time.substring(0,5)
        : iso.split("T")[1].substring(0, 5);
      document.getElementById('edit_time').value = timeValue;
      document.getElementById('edit_status').value = event.title.split(' - ')[1];
      var editModal = new bootstrap.Modal(document.getElementById('editScheduleModal'));
      editModal.show();
    },
    eventMouseEnter: function(info) {
      const event = info.event;
      const eventName = event.title.split(' - ')[0].replace(/^🟢|✅/, '').trim();
      const eventDate = event.start.toLocaleDateString();

      // *** UPDATED: show request_time for client requests on hover ***
      const eventTime = (event.id.startsWith('request_') && event.extendedProps.request_time)
        ? event.extendedProps.request_time.substring(0, 5)
        : event.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

      const eventStatus = event.title.split(' - ')[1];
      const popover = document.createElement('div');
      popover.className = 'popover bs-popover-top show';
      popover.style.position = 'absolute';
      popover.style.zIndex = '9999';
      popover.style.background = '#fff';
      popover.style.border = '1px solid #e3e6ea';
      popover.style.borderRadius = '8px';
      popover.style.boxShadow = '0 2px 8px rgba(102,192,94,0.10)';
      popover.style.padding = '12px 18px';
      popover.innerHTML = `<strong>${eventName}</strong><br>Date: ${eventDate}<br>Time: ${eventTime}<br>Status: ${eventStatus}`;
      document.body.appendChild(popover);
      // Position popover near mouse
      const mouseX = info.jsEvent.clientX;
      const mouseY = info.jsEvent.clientY;
      popover.style.top = (mouseY + window.scrollY - popover.offsetHeight - 10) + 'px';
      popover.style.left = (mouseX + window.scrollX - popover.offsetWidth/2) + 'px';
      info.el._popover = popover;
    },
    eventMouseLeave: function(info) {
      if (info.el._popover) {
        info.el._popover.remove();
        info.el._popover = null;
      }
    }
  });
  calendar.render();
});

// Function to load recent logs via AJAX
function loadRecentLogs() {
  fetch('get_recent_logs.php')
    .then(res => res.json())
    .then(logs => {
      const container = document.getElementById('recentLogsList');
      container.innerHTML = '';

      if (logs.length === 0) {
        container.innerHTML = '<li class="list-group-item text-muted">No completed events logged yet.</li>';
        return;
      }

      logs.forEach(log => {
        const time = new Date(`2000-01-01T${log.time}`).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const li = document.createElement('li');
        li.className = 'list-group-item small';
        li.innerHTML = `
          <strong>${htmlspecialchars(log.name)}</strong>
          <span class="badge bg-secondary float-end">${htmlspecialchars(log.type)}</span><br>
          <small>${htmlspecialchars(log.date)} at ${time}</small>
        `;
        container.appendChild(li);
      });
    })
    .catch(err => {
      console.error('Failed to load logs:', err);
      document.getElementById('recentLogsList').innerHTML =
        '<li class="list-group-item text-danger">Error loading logs.</li>';
    });
}

// Helper to escape HTML
function htmlspecialchars(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

// Load once on page load
document.addEventListener('DOMContentLoaded', loadRecentLogs);

// Optional: Auto-refresh every 30 seconds
setInterval(loadRecentLogs, 30000);
</script>

</body>
</html>

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

/* Ensure navbar z-index is proper */
        .navbar-main {
            z-index: 1030;
            backdrop-filter: saturate(200%) blur(30px);
            background-color: rgba(255, 255, 255, 0.8) !important;
        }
        
        /* Fix dropdown positioning */
        .dropdown-menu {
            z-index: 1040;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border-radius: 0.75rem;
            margin-top: 0.5rem;
        }
        
        .dropdown-item {
            padding: 0.75rem 1.25rem;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            margin: 0 0.5rem;
            transform: translateX(5px);
        }
        
        /* Toast positioning */
        .toast {
            min-width: 350px;
        }
        
        /* Mobile sidebar toggle styling */
        .sidenav-toggler-inner {
            cursor: pointer;
        }
        
        /* Ensure Font Awesome icons are visible */
        .fa-solid, .fa-regular {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900;
        }
        
        .fa-regular {
            font-weight: 400 !important;
        }
        
        /* Fix badge positioning */
        .nav-item .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            z-index: 1;
        }
        
        /* Breadcrumb styling */
        .breadcrumb-item + .breadcrumb-item::before {
            content: "/";
            color: #adb5bd;
        }
        
        /* User info styling */
        .nav-link.dropdown-toggle::after {
            display: none;
        }
        
        /* Success indicator dot */
        .bg-success {
            background-color: #28a745 !important;
        }

  </style>
