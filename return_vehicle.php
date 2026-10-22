
<?php
session_start();
require_once 'db.php';

// 1. Customer login check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

$userName = $_SESSION['name'];
$message = "";

// 2. Get booking ID
$bookingId = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
} else {
    $bookingId = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
}

if (!$bookingId || $bookingId < 1) {
    die("Invalid booking ID. Please open your booking again.");
}

// 3. Get only the logged-in customer's booking
function getBooking($conn, $bookingId, $userName) {
    $stmt = $conn->prepare(
        "SELECT b.id, b.user_name, b.vehicle_id,
                b.vehicle_name, b.booking_date,
                b.start_km, b.end_km, b.total_km,
                b.total_fare, b.status,
                v.rate_per_km
         FROM bookings b
         LEFT JOIN vehicles v ON b.vehicle_id = v.id
         WHERE b.id = ? AND b.user_name = ?
         LIMIT 1"
    );

    $stmt->bind_param("is", $bookingId, $userName);
    $stmt->execute();

    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();

    $stmt->close();

    return $booking;
}

$booking = getBooking($conn, $bookingId, $userName);

if (!$booking) {
    http_response_code(404);
    die("Booking not found or you do not have permission to access it.");
}

// 4. Process return and calculate fare
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($booking['status'] !== 'Booked') {
        $message = "This booking cannot be returned again.";
    } else {
        $endKmInput = trim($_POST['end_km'] ?? '');

        if ($endKmInput === '' || !is_numeric($endKmInput)) {
            $message = "Please enter a valid End KM.";
        } else {
            $startKm = (float) $booking['start_km'];
            $endKm = (float) $endKmInput;

            if (!is_finite($endKm) || $endKm < 0) {
                $message = "Please enter a valid End KM.";
            } elseif ($endKm < $startKm) {
                $message = "End KM cannot be less than Start KM.";
            } elseif (
                $booking['rate_per_km'] === null ||
                !is_numeric($booking['rate_per_km']) ||
                (float) $booking['rate_per_km'] < 0
            ) {
                $message = "Vehicle rate not found. Please contact admin.";
            } else {
                $totalKm = round($endKm - $startKm, 2);
                $rate = (float) $booking['rate_per_km'];
                $totalFare = round($totalKm * $rate, 2);
                $newStatus = "Pending Payment";

                // Update only the owner's booking that is still Booked
                $update = $conn->prepare(
                    "UPDATE bookings
                     SET end_km = ?,
                         total_km = ?,
                         total_fare = ?,
                         status = ?
                     WHERE id = ?
                       AND user_name = ?
                       AND status = 'Booked'"
                );

                $update->bind_param(
                    "dddsis",
                    $endKm,
                    $totalKm,
                    $totalFare,
                    $newStatus,
                    $bookingId,
                    $userName
                );

                if ($update->execute() && $update->affected_rows === 1) {
                    $update->close();

                    header(
                        "Location: payment.php?booking_id=" . $bookingId
                    );
                    exit();
                } else {
                    $message = "Booking update failed. Refresh and try again.";
                    $update->close();
                }
            }
        }
    }

    // Refresh booking details
    $booking = getBooking($conn, $bookingId, $userName);

    if (!$booking) {
        die("Unable to load booking details.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Return Vehicle | VehicleRental</title>

<style>
* {
    box-sizing: border-box;
}

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

nav a {
    color: white;
    text-decoration: none;
    font-weight: bold;
}

.container {
    width: 90%;
    max-width: 650px;
    margin: 35px auto;
}

.card {
    background: #202020;
    padding: 25px;
    margin-bottom: 20px;
    border-radius: 12px;
}

h1, h2 {
    color: #ff3030;
}

.details p {
    line-height: 1.5;
    border-bottom: 1px solid #333;
    padding-bottom: 8px;
}

input {
    width: 100%;
    padding: 13px;
    margin: 10px 0 16px;
    border: 1px solid #555;
    border-radius: 6px;
    background: #111;
    color: white;
}

button {
    width: 100%;
    padding: 14px;
    background: #e50914;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
}

button:hover {
    background: #bd0811;
}

.message {
    background: #391b1b;
    border: 1px solid #a33;
    padding: 12px;
    border-radius: 6px;
    color: #ffaaaa;
}

.status {
    color: #ff6868;
    font-weight: bold;
}

.back {
    display: inline-block;
    margin-top: 12px;
    color: white;
}
</style>
</head>

<body>

<nav>
    <a href="index.php">🚘 VehicleRental</a>
</nav>

<div class="container">

    <div class="card">
        <h1>Return Your Vehicle</h1>

        <p>Enter the final odometer reading to calculate your fare.</p>

        <?php if ($message !== ''): ?>
            <p class="message">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <div class="details">
            <p>
                <strong>Booking ID:</strong>
                <?php echo (int) $booking['id']; ?>
            </p>

            <p>
                <strong>Vehicle:</strong>
                <?php echo htmlspecialchars($booking['vehicle_name']); ?>
            </p>

            <p>
                <strong>Booking Date:</strong>
                <?php echo htmlspecialchars($booking['booking_date']); ?>
            </p>

            <p>
                <strong>Start KM:</strong>
                <?php echo number_format((float) $booking['start_km'], 2); ?>
            </p>

            <p>
                <strong>Status:</strong>
                <span class="status">
                    <?php echo htmlspecialchars($booking['status']); ?>
                </span>
            </p>
        </div>

        <?php if ($booking['status'] === 'Booked'): ?>

            <form method="POST"
                  action="return_vehicle.php?booking_id=<?php echo (int) $bookingId; ?>">

                <input type="hidden"
                       name="booking_id"
                       value="<?php echo (int) $bookingId; ?>">

                <label for="end_km">
                    End KM (final odometer reading)
                </label>

                <input
                    type="number"
                    name="end_km"
                    id="end_km"
                    min="<?php echo htmlspecialchars((string) $booking['start_km']); ?>"
                    step="0.01"
                    placeholder="Example: 12500"
                    required
                >

                <button type="submit">
                    Calculate Fare & Continue to Payment
                </button>
            </form>

        <?php else: ?>

            <p>
                <strong>End KM:</strong>
                <?php echo number_format((float) $booking['end_km'], 2); ?>
            </p>

            <p>
                <strong>Total Distance:</strong>
                <?php echo number_format((float) $booking['total_km'], 2); ?> km
            </p>

            <p>
                <strong>Total Fare:</strong>
                ₹<?php echo number_format((float) $booking['total_fare'], 2); ?>
            </p>

        <?php endif; ?>

        <a class="back" href="booking_history.php">
            ← Back to Booking History
        </a>
