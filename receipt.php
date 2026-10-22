```php
<?php
session_start();
require_once 'db.php';

// Login protection
if (!isset($_SESSION['user_id'], $_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

$userName = trim($_SESSION['name']);
$bookingId = (int)($_GET['booking_id'] ?? 0);

if ($bookingId <= 0) {
    header("Location: booking_history.php");
    exit();
}

// Fetch booking
$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM bookings WHERE id = ?"
);

if (!$stmt) {
    die("Unable to prepare receipt.");
}

mysqli_stmt_bind_param($stmt, "i", $bookingId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$booking = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// Check booking exists
if (!$booking) {
    http_response_code(404);
    die("Booking not found.");
}

// Verify that the logged-in customer owns this booking
if (strcasecmp(trim($booking['user_name']), $userName) !== 0) {
    http_response_code(403);
    die("You are not authorized to view this receipt.");
}

// This project does not verify real payments.
// Completed is only the current booking status.
$status = $booking['status'];
$isCompleted = ($status === 'Completed');

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$distance = (float)$booking['total_km'];
$fare = (float)$booking['total_fare'];

$receiptNumber = 'VR-' . str_pad((string)$booking['id'], 6, '0', STR_PAD_LEFT);
$receiptDate = date('d M Y, h:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>VehicleRental | Booking Receipt</title>

<style>
:root {
    --red: #ed1c24;
    --red-dark: #a9000a;
    --bg: #090909;
    --card: #151515;
    --line: #303030;
    --muted: #aaa;
    --green: #48d597;
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    color: #fff;
    min-height: 100vh;
    background:
        radial-gradient(ellipse at 90% 0%, rgba(237,28,36,.14), transparent 38%),
        var(--bg);
}

a {
    text-decoration: none;
    color: inherit;
}

.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    padding: 20px 7%;
    background: #111;
    border-bottom: 1px solid #292929;
}

.logo {
    font-size: 25px;
    font-weight: 900;
}

.logo span {
    color: var(--red);
}

.navlinks {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
}

.navlinks a {
    color: #ccc;
    font-size: 14px;
}

.navlinks a:hover {
    color: #ff6268;
}

.page {
    width: 92%;
    max-width: 850px;
    margin: 38px auto;
}

.heading {
    text-align: center;
    margin-bottom: 25px;
}

.badge {
    display: inline-block;
    padding: 8px 13px;
    color: #ff858a;
    background: #291315;
    border: 1px solid #572429;
    border-radius: 30px;
    font-size: 11px;
    font-weight: bold;
    letter-spacing: 1.5px;
}

h1 {
    margin-top: 16px;
    font-size: clamp(28px, 5vw, 40px);
}

.heading p {
    color: var(--muted);
    margin-top: 9px;
    line-height: 1.6;
}

.receipt {
    overflow: hidden;
    background: var(--card);
    border: 1px solid #353535;
    border-radius: 18px;
    box-shadow: 0 20px 60px rgba(0,0,0,.3);
}

.receipt-header {
    padding: 30px;
    background: linear-gradient(120deg, #401619, #171010);
    border-bottom: 1px solid #5b282c;
}

.header-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 20px;
}

.brand {
    font-size: 27px;
    font-weight: 900;
}

.brand span {
    color: #ff5158;
}

.tagline {
    color: #d0b9ba;
    font-size: 12px;
    margin-top: 7px;
}

.receipt-number {
    text-align: right;
    overflow-wrap: anywhere;
}

.receipt-number small {
    display: block;
    color: #c9aeb0;
    font-size: 11px;
    margin-bottom: 6px;
}

.receipt-number strong {
    font-size: 18px;
}

.receipt-body {
    padding: 30px;
}

.section-title {
    font-size: 16px;
    margin-bottom: 17px;
}

.vehicle-box {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    background: #202020;
    border: 1px solid #373737;
    border-radius: 13px;
    margin-bottom: 25px;
}

.vehicle-icon {
    width: 60px;
    height: 60px;
    flex-shrink: 0;
    display: grid;
    place-items: center;
    border-radius: 12px;
    background: #3b171a;
    font-size: 30px;
}

.vehicle-name {
    font-size: 20px;
    font-weight: 800;
    overflow-wrap: anywhere;
}

.vehicle-ref {
    color: var(--muted);
    font-size: 12px;
    margin-top: 7px;
}

.details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-bottom: 25px;
}

.detail {
    padding: 15px;
    background: #1b1b1b;
    border: 1px solid #303030;
    border-radius: 10px;
    min-width: 0;
}

.detail span {
    display: block;
    color: var(--muted);
    font-size: 12px;
    margin-bottom: 8px;
}

.detail strong {
    display: block;
    font-size: 14px;
    overflow-wrap: anywhere;
}

.status {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 30px;
    color: #ffd777;
    background: #332916;
    border: 1px solid #5b4825;
    font-size: 11px;
}

.status.done {
    color: #8af0bd;
    background: #112b20;
    border-color: #285b42;
}

.fare-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
}

.fare-table th,
.fare-table td {
    text-align: left;
    padding: 15px 10px;
    border-bottom: 1px solid #303030;
    font-size: 13px;
}

.fare-table th {
    color: var(--muted);
    font-weight: normal;
}

.fare-table td:last-child,
.fare-table th:last-child {
    text-align: right;
}

.total-box {
    margin-top: 20px;
    padding: 23px;
    background: linear-gradient(120deg, #381417, #201214);
    border: 1px solid #5b282d;
    border-radius: 13px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.total-row span {
    color: #dfc4c6;
    font-size: 13px;
}

.total-amount {
    color: #ff575e;
    font-size: clamp(27px, 5vw, 36px);
    font-weight: 900;
    overflow-wrap: anywhere;
}

.payment-note {
    margin-top: 14px;
    padding: 14px;
    border: 1px solid #514326;
    background: #292316;
    color: #f0d99b;
    border-radius: 10px;
    font-size: 12px;
    line-height: 1.7;
}

.demo-warning {
    margin-top: 15px;
    padding: 13px;
    color: #ffc4c7;
    background: #301719;
    border: 1px solid #613034;
    border-radius: 10px;
    font-size: 12px;
    line-height: 1.7;
}

.receipt-footer {
    padding: 23px 30px;
    text-align: center;
    color: #aaa;
    background: #111;
    border-top: 1px dashed #454545;
    font-size: 12px;
    line-height: 1.8;
}

.actions {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 25px;
}

.btn {
    display: inline-block;
    padding: 14px 20px;
    color: #fff;
    background: linear-gradient(120deg, var(--red), var(--red-dark));
    border: 0;
    border-radius: 10px;
    font-size: 13px;
    font-weight: bold;
    cursor: pointer;
    text-align: center;
}

.btn:hover {
    filter: brightness(1.12);
}

.btn.secondary {
    background: #202020;
    border: 1px solid #414141;
}

.page-footer {
    padding: 25px 10px;
    color: #777;
    text-align: center;
    font-size: 12px;
}

@media(max-width: 600px) {
    .navbar {
        padding: 18px 5%;
    }

    .navlinks {
        gap: 12px;
    }

    .navlinks a {
        font-size: 12px;
    }

    .page {
        margin: 25px auto;
    }

    .receipt-header,
    .receipt-body {
        padding: 20px;
    }

    .receipt-footer {
        padding: 20px;
    }

    .details {
        grid-template-columns: 1fr;
        gap: 11px;
    }

    .receipt-number {
        text-align: left;
    }

    .fare-table th,
    .fare-table td {
        padding: 13px 5px;
        font-size: 12px;
    }

    .btn {
        width: 100%;
    }
}

@media print {
    @page {
        size: A4;
        margin: 12mm;
    }

    body {
        background: #fff !important;
        color: #111 !important;
        min-height: auto;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .navbar,
    .heading,
    .actions,
    .page-footer {
        display: none !important;
    }

    .page {
        width: 100%;
        max-width: none;
        margin: 0;
    }

    .receipt {
        box-shadow: none;
        border: 1px solid #bbb;
        break-inside: avoid;
    }

    .receipt-body {
        padding: 20px;
    }

    .detail,
    .vehicle-box {
        break-inside: avoid;
    }
}
</style>
</head>

<body>

<header class="navbar">
    <a class="logo" href="index.php">Vehicle<span>Rental</span></a>

    <nav class="navlinks">
        <a href="index.php">Home</a>
        <a href="vehicles.php">Vehicles</a>
        <a href="track_vehicle.php">Track Booking</a>
        <a href="booking_history.php">My Bookings</a>
    </nav>
</header>

<main class="page">

    <div class="heading">
        <span class="badge">BOOKING RECEIPT</span>
        <h1>Your Booking Summary</h1>
        <p>Review your vehicle rental details and print this receipt.</p>
    </div>

    <article class="receipt">

        <div class="receipt-header">
            <div class="header-row">
                <div>
                    <div class="brand">Vehicle<span>Rental</span></div>
                    <div class="tagline">
                        YOUR JOURNEY. YOUR WAY.
                    </div>
                </div>

                <div class="receipt-number">
                    <small>RECEIPT REFERENCE</small>
                    <strong><?php echo e($receiptNumber); ?></strong>

                    <div class="tagline">
                        Issued: <?php echo e($receiptDate); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="receipt-body">

            <h2 class="section-title">Vehicle Details</h2>

            <div class="vehicle-box">
                <div class="vehicle-icon">🚘</div>

                <div>
                    <div class="vehicle-name">
                        <?php echo e($booking['vehicle_name']); ?>
                    </div>

                    <div class="vehicle-ref">
                        Booking ID: #<?php echo (int)$booking['id']; ?>
                    </div>
                </div>
            </div>

            <h2 class="section-title">Booking Information</h2>

            <div class="details">

                <div class="detail">
                    <span>Customer Name</span>
                    <strong><?php echo e($booking['user_name']); ?></strong>
                </div>

                <div class="detail">
                    <span>Booking Date</span>
                    <strong><?php echo e($booking['booking_date']); ?></strong>
                </div>

                <div class="detail">
                    <span>Start Odometer</span>
                    <strong>
                        <?php echo number_format((float)$booking['start_km'], 2); ?> KM
                    </strong>
                </div>

                <div class="detail">
                    <span>End Odometer</span>
                    <strong>
                        <?php echo number_format((float)$booking['end_km'], 2); ?> KM
                    </strong>
                </div>

                <div class="detail">
                    <span>Total Distance</span>
                    <strong>
                        <?php echo number_format($distance, 2); ?> KM
                    </strong>
                </div>

                <div class="detail">
                    <span>Booking Status</span>
                    <strong>
                        <span class="status <?php echo $isCompleted ? 'done' : ''; ?>">
                            <?php echo e($status); ?>
                        </span>
                    </strong>
                </div>

            </div>

            <h2 class="section-title">Fare Summary</h2>

            <table class="fare-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>Vehicle rental — <?php echo e($booking['vehicle_name']); ?></td>
                        <td>₹<?php echo number_format($fare, 2); ?></td>
                    </tr>

                    <tr>
                        <td>Distance travelled</td>
                        <td><?php echo number_format($distance, 2); ?> KM</td>
                    </tr>
                </tbody>
            </table>

            <div class="total-box">
                <div class="total-row">
                    <span>TOTAL RENTAL FARE</span>

                    <strong class="total-amount">
                        ₹<?php echo number_format($fare, 2); ?>
                    </strong>
                </div>
            </div>

            <div class="payment-note">
                <strong>Payment information</strong><br>

                <?php if ($isCompleted): ?>
                    The booking status is Completed in the project database.
                    This demo receipt does not independently verify a real
                    payment transaction.
                <?php else: ?>
                    This booking is not marked Completed. This page is a
                    booking summary, not a payment confirmation.
                <?php endif; ?>
            </div>

            <div class="demo-warning">
                <strong>College project demo receipt</strong><br>
                This document is generated by the VehicleRental demo system.
                It is not a tax invoice or proof of a verified real-money
                transaction.
            </div>

        </div>

        <div class="receipt-footer">
            Thank you for choosing VehicleRental!<br>
            Please keep this receipt for your booking records.
        </div>

    </article>

    <div class="actions">
        <button class="btn" type="button" onclick="window.print()">
            🖨️ Print / Save as PDF
        </button>

        <a class="btn secondary" href="booking_history.php">
            ← Booking History
        </a>

        <a class="btn secondary" href="index.php">
            Home
        </a>
    </div>

</main>

<footer class="page-footer">
    VehicleRental — College Project
</footer>

</body>
</html>
```