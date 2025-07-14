<!-- FullCalendar CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.js"></script>



<!-- Full Calendar View -->
<div class="container-fluid px-4 mt-4">
  <div class="card shadow-lg">
    <div class="card-header bg-gradient-warning text-white">
      <h5 class="text-center text-uppercase font-weight-bold mb-0">Maintenance Schedule - Calendar View</h5>
    </div>
    <div class="card-body">
      <div id="calendar"></div>
    </div>
  </div>
</div>


<!-- Initialize FullCalendar -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    initialDate: '2025-02-01',
    height: 600,
    headerToolbar: {
      left: '',
      center: 'title',
      right: ''
    },
    events: [
      {
        title: 'Community Clean-up',
        start: '2025-02-05',
        color: '#28a745'
      },
      {
        title: 'Waste Collection Awareness',
        start: '2025-02-12',
        color: '#17a2b8'
      },
      {
        title: 'Bago City Fiesta',
        start: '2025-02-19',
        color: '#ffc107'
      },
      {
        title: 'Environmental Seminar',
        start: '2025-02-25',
        color: '#dc3545'
      }
    ]
  });

  calendar.render();
});


// Toggle Button Label Update
const toggleBtn = document.getElementById('toggleEventBtn');
const collapseElement = document.getElementById('calendarContainer');

if (collapseElement) {
  if (collapseElement.classList.contains('show')) {
    toggleBtn.textContent = "📅 Unview Maintenance Schedule";
  } else {
    toggleBtn.textContent = "📅 View Maintenance Schedule";
  }

  collapseElement.addEventListener('hidden.bs.collapse', () => {
    toggleBtn.textContent = "📅 View Maintenance Schedule";
  });

  collapseElement.addEventListener('shown.bs.collapse', () => {
    toggleBtn.textContent = "📅 Unview Maintenance Schedule";
  });
}
</script>
