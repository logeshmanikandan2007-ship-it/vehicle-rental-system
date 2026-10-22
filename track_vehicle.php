
<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

$userName = $_SESSION['name'];
$booking = null;
$message = '';

if (isset($_GET['booking_id'])) {
    $bookingId = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);

    if ($bookingId && $bookingId > 0) {
        $stmt = $conn->prepare(
            "SELECT id, vehicle_name, booking_date, start_km,
                    end_km, total_km, total_fare, status
             FROM bookings
             WHERE id = ? AND user_name = ?"
        );
        $stmt->bind_param("is", $bookingId, $userName);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    if (!$booking) {
        $message = "Booking not found. Please check the booking ID.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Track Vehicle | VehicleRental</title>

<link rel="stylesheet"
 href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<style>
* { box-sizing: border-box; }
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #101010;
    color: white;
}
nav {
    background: #1b1b1b;
    padding: 18px;
    border-bottom: 2px solid #e50914;
}
nav a, .back { color: white; text-decoration: none; }
.container { width: 92%; max-width: 760px; margin: 30px auto; }
.card {
    background: #202020;
    padding: 22px;
    border-radius: 12px;
    margin-bottom: 20px;
}
h1, h2 { color: #ff3030; }
input, button {
    width: 100%;
    padding: 13px;
    margin-top: 10px;
    border-radius: 6px;
    font-size: 16px;
}
input {
    background: #111;
    border: 1px solid #555;
    color: white;
}
button {
    background: #e50914;
    color: white;
    border: 0;
    font-weight: bold;
    cursor: pointer;
}
.details { line-height: 1.8; overflow-wrap: anywhere; }
.status { color: #ff6464; font-weight: bold; }
.message { color: #ff7777; }
#map {
    width: 100%;
    height: 350px;
    border-radius: 10px;
    background: #333;
}
.small { color: #ccc; font-size: 14px; }
</style>
</head>
<body>
<nav><a href="index.php">🚘 VehicleRental</a></nav>

<div class="container">
    <div class="card">
        <h1>📍 Track Your Vehicle</h1>
        <p>Enter your booking ID to view booking details and GPS location.</p>

        <form method="GET" action="track_vehicle.php">
            <label for="booking_id">Booking ID</label>
            <input type="number" id="booking_id" name="booking_id"
                   min="1" required placeholder="Example: 17"
                   value="<?php echo isset($_GET['booking_id'])
                       ? htmlspecialchars((string)$_GET['booking_id']) : ''; ?>">
            <button type="submit">Track Booking</button>
        </form>
    </div>

    <?php if ($message !== ''): ?>
        <div class="card"><p class="message"><?php echo htmlspecialchars($message); ?></p></div>
    <?php endif; ?>

    <?php if ($booking): ?>
        <div class="card">
            <h2>Booking Details</h2>
            <div class="details">
                <p><strong>Booking ID:</strong> <?php echo (int)$booking['id']; ?></p>
                <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($booking['vehicle_name']); ?></p>
                <p><strong>Booking Date:</strong> <?php echo htmlspecialchars((string)$booking['booking_date']); ?></p>
                <p><strong>Start KM:</strong> <?php echo htmlspecialchars((string)$booking['start_km']); ?></p>
                <p><strong>End KM:</strong> <?php echo htmlspecialchars((string)$booking['end_km']); ?></p>
                <p><strong>Total KM:</strong> <?php echo htmlspecialchars((string)$booking['total_km']); ?> km</p>
                <p><strong>Total Fare:</strong> ₹<?php echo number_format((float)$booking['total_fare'], 2); ?></p>
                <p><strong>Status:</strong> <span class="status"><?php echo htmlspecialchars($booking['status']); ?></span></p>
            </div>
        </div>

        <div class="card">
            <h2>🗺️ Vehicle GPS Location</h2>
            <p id="gps-status" class="small">Loading saved GPS location...</p>
            <div id="map"></div>
            <p class="small">The map shows the latest location received from the sharing device. It is not the vehicle's location unless that device is travelling with the vehicle.</p>
        </div>

        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
        const bookingId = <?php echo (int)$booking['id']; ?>;
        const map = L.map('map').setView([11.0, 78.0], 7);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let marker = null;

        async function refreshLocation() {
            const status = document.getElementById('gps-status');

            try {
                const response = await fetch(
                    'get_location.php?booking_id=' + encodeURIComponent(bookingId),
                    { cache: 'no-store' }
                );
                const data = await response.json();

                if (!response.ok || !data.success) {
                    status.textContent = data.message || 'GPS location unavailable.';
                    return;
                }

                const lat = Number(data.latitude);
                const lng = Number(data.longitude);

                if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                    status.textContent = 'Invalid GPS coordinates received.';
                    return;
                }

                const point = [lat, lng];

                if (!marker) {
                    marker = L.marker(point).addTo(map)
                        .bindPopup('Vehicle GPS Location');
                    map.setView(point, 16);
                } else {
                    marker.setLatLng(point);
                }

                status.textContent =
                    'Last GPS update: ' + data.updated_at +
                    ' | ' + lat.toFixed(6) + ', ' + lng.toFixed(6);
            } catch (error) {
                status.textContent =
                    'Could not load GPS. Check internet and try again.';
            }
        }

        refreshLocation();
        setInterval(refreshLocation, 5000);
        </script>
    <?php endif; ?>

    <p><a class="back" href="booking_history.php">← Back to Booking History</a></p>
</div>
</body>
</html>
