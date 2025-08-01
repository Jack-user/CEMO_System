<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_page/sign-in.php");
    exit();
}
$page_title = "Trail History";
include '../includes/header.php';
?>

<body class="g-sidenav-show bg-gray-200">
    <?php include '../sidebar/admin_sidebar.php'; ?>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <?php include '../includes/navbar.php'; ?>

        <div class="container mt-3">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Trail History</h5>
                        </div>
                        <div class="card-body">
                            <div id="trailHistoryList">
                                <!-- Trail history items will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Trail Map</h5>
                        </div>
                        <div class="card-body">
                            <div id="historyMap" style="height: 500px; border-radius: 8px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include '../includes/footer.php'; ?>
    </main>

    <!-- Scripts -->
    <script src="../assets/js/core/popper.min.js"></script>
    <script src="../assets/js/core/bootstrap.min.js"></script>
    <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>

    <script>
        var historyMap;
        var currentTrailLayer = null;

        // Initialize map
        window.onload = function() {
            // Define bounding box around Bago City
            var bagoBounds = L.latLngBounds(
                L.latLng(10.4300, 122.7800), // Southwest corner
                L.latLng(10.6500, 123.1000)  // Northeast corner
            );

            // Initialize the map
            historyMap = L.map('historyMap', {
                center: [10.5379, 122.8333],
                zoom: 13,
                maxBounds: bagoBounds,
                maxBoundsViscosity: 1.0,
                minZoom: 12,
                maxZoom: 18
            });

            // Add OpenStreetMap tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(historyMap);

            // Add start point marker (Bago City Hall)
            L.marker([10.538274, 122.835230]).addTo(historyMap)
                .bindPopup("<b>Bago City Hall</b>")
                .openPopup();

            // Load trail history
            loadTrailHistory();
        };

        function loadTrailHistory() {
            fetch('trail_history.php?action=get_history')
                .then(response => response.json())
                .then(data => {
                    displayTrailHistory(data.history);
                })
                .catch(error => {
                    console.error('Error loading trail history:', error);
                    document.getElementById('trailHistoryList').innerHTML = '<p class="text-muted">No trail history found.</p>';
                });
        }

        function displayTrailHistory(history) {
            const container = document.getElementById('trailHistoryList');
            
            if (history.length === 0) {
                container.innerHTML = '<p class="text-muted">No trail history found.</p>';
                return;
            }

            let html = '';
            history.reverse().forEach(trail => {
                html += `
                    <div class="trail-item mb-3 p-3 border rounded" style="cursor: pointer;" onclick="showTrailOnMap('${trail.id}')">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">${trail.name}</h6>
                                <small class="text-muted">${trail.date_created}</small>
                                <br>
                                <small class="text-info">${trail.point_count} GPS points</small>
                            </div>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteTrail('${trail.id}', event)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }

        function showTrailOnMap(trailId) {
            // Remove current trail layer
            if (currentTrailLayer) {
                historyMap.removeLayer(currentTrailLayer);
            }

            // Get trail data
            fetch('trail_history.php?action=get_history')
                .then(response => response.json())
                .then(data => {
                    const trail = data.history.find(t => t.id === trailId);
                    if (trail && trail.trail_data.length > 0) {
                        // Create polyline
                        const polyline = L.polyline(trail.trail_data, {
                            color: '#ff6b35',
                            weight: 4,
                            opacity: 0.8
                        }).addTo(historyMap);

                        // Add markers for start and end points
                        const startMarker = L.marker(trail.trail_data[0], {
                            icon: L.divIcon({
                                className: 'start-marker',
                                html: '<div style="background-color: #28a745; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white;"></div>',
                                iconSize: [12, 12],
                                iconAnchor: [6, 6]
                            })
                        }).addTo(historyMap).bindPopup('Start Point');

                        const endMarker = L.marker(trail.trail_data[trail.trail_data.length - 1], {
                            icon: L.divIcon({
                                className: 'end-marker',
                                html: '<div style="background-color: #dc3545; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white;"></div>',
                                iconSize: [12, 12],
                                iconAnchor: [6, 6]
                            })
                        }).addTo(historyMap).bindPopup('End Point');

                        // Fit map to trail bounds
                        historyMap.fitBounds(polyline.getBounds());

                        // Store current layer for removal
                        currentTrailLayer = L.layerGroup([polyline, startMarker, endMarker]);

                        // Highlight selected trail item
                        document.querySelectorAll('.trail-item').forEach(item => {
                            item.style.backgroundColor = '';
                        });
                        event.target.closest('.trail-item').style.backgroundColor = '#f8f9fa';
                    }
                })
                .catch(error => console.error('Error showing trail:', error));
        }

        function deleteTrail(trailId, event) {
            event.stopPropagation();
            
            if (confirm('Are you sure you want to delete this trail from history?')) {
                const formData = new FormData();
                formData.append('action', 'delete_history');
                formData.append('trail_id', trailId);

                fetch('trail_history.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload trail history
                        loadTrailHistory();
                        
                        // Remove from map if it was the currently displayed trail
                        if (currentTrailLayer) {
                            historyMap.removeLayer(currentTrailLayer);
                            currentTrailLayer = null;
                        }
                    } else {
                        alert('Error deleting trail: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error deleting trail:', error);
                    alert('Error deleting trail');
                });
            }
        }
    </script>
</body>
</html> 