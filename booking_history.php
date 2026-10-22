```php
<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'], $_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

$userName = trim($_SESSION['name']);

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM bookings
     WHERE LOWER(TRIM(user_name)) = LOWER(TRIM(?))
     ORDER BY id DESC"
);

if (!$stmt) {
    die("Unable to load booking history.");
}

mysqli_stmt_bind_param($stmt, "s", $userName);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$bookings = [];

while ($row = mysqli_fetch_assoc($result)) {
    $bookings[] = $row;
}

mysqli_stmt_close($stmt);

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$totalBookings = count($bookings);
$completedCount = 0;
$totalFare = 0;

foreach ($bookings as $booking) {
    if (($booking['status'] ?? '') === 'Completed') {
        $completedCount++;
        $totalFare += (float)($booking['total_fare'] ?? 0);
    }
}

$paymentSuccess = isset($_GET['payment'])
    && $_GET['payment'] === 'success';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VehicleRental | My Bookings</title>

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #090909;
    color: #fff;
    min-height: 100vh;
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
    border-bottom: 1px solid #303030;
}

.logo {
    font-size: 25px;
    font-weight: 900;
}

.logo span {
    color: #ed1c24;
}

.navlinks {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}

.navlinks a {
    color: #ddd;
    font-size: 13px;
}

.navlinks a:hover {
    color: #ff555d;
}

.page {
    width: 92%;
    max-width: 1150px;
    margin: 40px auto;
}

.heading {
    margin-bottom: 25px;
}

.badge {
    display: inline-block;
    padding: 8px 13px;
    border-radius: 30px;
    color: #ff9297;
    background: #301416;
    border: 1px solid #572429;
    font-size: 11px;
    letter-spacing: 1px;
}

h1 {
    font-size: clamp(30px, 5vw, 42px);
    margin-top: 17px;
}

.subtitle {
    color: #aaa;
    margin-top: 10px;
    line-height: 1.7;
}

.success {
    background: #11291e;
    color: #a4f2c9;
    border: 1px solid #285b42;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 22px;
    line-height: 1.6;
}

.summary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 25px;
}

.summary-card {
    padding: 22px;
    background: #151515;
    border: 1px solid #303030;
    border-radius: 13px;
}

.summary-card span {
    display: block;
    color: #aaa;
    font-size: 12px;
    margin-bottom: 12px;
}

.summary-card strong {
    font-size: 25px;
    overflow-wrap: anywhere;
}

.red-text {
    color: #ff555d;
}

.booking-list {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

.booking-card {
    min-width: 0;
    overflow: hidden;
    background: #141414;
    border: 1px solid #333;
    border-radius: 15px;
}

.card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 12px;
    padding: 20px;
    background: #281315;
    border-bottom: 1px solid #512328;
}

.vehicle-name {
    font-size: 20px;
    font-weight: 800;
    overflow-wrap: anywhere;
}

.booking-number {
    color: #c7b3b4;
    margin-top: 7px;
    font-size: 12px;
}

.status {
    padding: 7px 10px;
    border-radius: 30px;
    font-size: 11px;
    border: 1px solid #5b4825;
    color: #ffd777;
    background: #332916;
}

.status.completed {
    color: #8af0bd;
    background: #112b20;
    border-color: #285b42;
}

.status.booked {
    color: #9fd4ff;
    background: #142637;
    border-color: #28506c;
}

.card-body {
    padding: 20px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #2b2b2b;
    line-height: 1.5;
}

.detail-row span {
    color: #aaa;
    font-size: 13px;
}

.detail-row strong {
    text-align: right;
    font-size: 13px;
    overflow-wrap: anywhere;
}

.fare {
    margin-top: 18px;
    padding: 17px;
    background: #271416;
    border: 1px solid #512328;
    border-radius: 10px;
}

.fare-label {
    color: #d4b7b9;
    font-size: 11px;
    letter-spacing: 1px;
}

.fare-amount {
    margin-top: 8px;
    color: #ff555d;
    font-size: 28px;
    font-weight: 900;
}

.actions {
    display: flex;
    flex-wrap: wrap;
    gap: 9px;
    margin-top: 18px;
}

.btn {
    display: inline-block;
    padding: 11px 13px;
    background: #222;
    color: #fff;
    border: 1px solid #444;
    border-radius: 8px;
    font-size: 12px;
    font-weight: bold;
    text-align: center;
}

.btn.red {
    background: #ed1c24;
    border-color: #ed1c24;
}

.btn.green {
    background: #11291e;
    color: #a4f2c9;
    border-color: #285b42;
}

.empty {
    padding: 45px 20px;
    text-align: center;
    background: #151515;
    border: 1px solid #303030;
    border-radius: 15px;
}

.empty-icon {
    font-size: 42px;
    margin-bottom: 15px;
}

.empty p {
    color: #aaa;
    line-height: 1.7;
    margin-top: 12px;
}

.empty .btn {
    margin-top: 20px;
}

.footer {
    text-align: center;
    color: #777;
    font-size: 12px;
    line-height: 1.8;
    padding: 25px 15px 32px;
}

@media (max-width: 800px) {
    .booking-list,
    .summary {
        grid-template-columns: 1fr;
    }

    .navbar {
        padding: 18px 5%;
    }

    .page {
        margin: 28px auto;
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
        <a href="booking_history.php">My Bookings</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="page">

    <section class="heading">
        <span class="badge">YOUR RENTAL ACTIVITY</span>
        <h1>My Bookings</h1>
        <p class="subtitle">
            View your bookings, rental distance, fare and receipts.
        </p>
    </section>

    <?php if ($paymentSuccess): ?>
        <div class="success">
            <strong>Demo checkout completed.</strong>
            The project booking status was updated. This message does not
            independently verify a real payment transaction.
        </div>
    <?php endif; ?>

    <section class="summary">
        <div class="summary-card">
            <span>Total Bookings</span>
            <strong><?php echo $totalBookings; ?></strong>
        </div>

        <div class="summary-card">
            <span>Completed Bookings</span>
            <strong><?php echo $completedCount; ?></strong>
        </div>

        <div class="summary-card">
            <span>Total Recorded Fare</span>
            <strong class="red-text">
                ₹<?php echo number_format($totalFare, 2); ?>
            </strong>
        </div>
    </section>

    <?php if (empty($bookings)): ?>

        <section class="empty">
            <div class="empty-icon">🚗</div>
            <h2>No bookings yet</h2>
            <p>
                You haven't booked a vehicle yet.
                Browse vehicles to create your first booking.
            </p>
            <a class="btn red" href="vehicles.php">Browse Vehicles</a>
        </section>

    <?php else: ?>

        <section class="booking-list">

            <?php foreach ($bookings as $row): ?>

                <?php
                $bookingId = (int)$row['id'];
                $status = (string)($row['status'] ?? 'Booked');

                $statusClass = '';

                if ($status === 'Completed') {
                    $statusClass = 'completed';
                } elseif ($status === 'Booked') {
                    $statusClass = 'booked';
                }
                ?>

                <article class="booking-card">

                    <div class="card-top">
                        <div>
                            <div class="vehicle-name">
                                <?php echo e($row['vehicle_name'] ?? 'Vehicle'); ?>
                            </div>

                            <div class="booking-number">
                                Booking #<?php echo $bookingId; ?>
                            </div>
                        </div>

                        <span class="status <?php echo e($statusClass); ?>">
                            <?php echo e($status); ?>
                        </span>
                    </div>

                    <div class="card-body">

                        <div class="detail-row">
                            <span>Customer</span>
                            <strong>
                                <?php echo e($row['user_name'] ?? $userName); ?>
                            </strong>
                        </div>

                        <div class="detail-row">
                            <span>Booking Date</span>
                            <strong>
                                <?php echo e($row['booking_date'] ?? '-'); ?>
                            </strong>
                        </div>

                        <div class="detail-row">
                            <span>Start KM</span>
                            <strong>
                                <?php echo number_format((float)($row['start_km'] ?? 0)); ?> km
                            </strong>
                        </div>

                        <div class="detail-row">
                            <span>End KM</span>
                            <strong>
                                <?php echo number_format((float)($row['end_km'] ?? 0)); ?> km
                            </strong>
                        </div>

                        <div class="detail-row">
                            <span>Total Distance</span>
                            <strong>
                                <?php echo number_format((float)($row['total_km'] ?? 0)); ?> km
                            </strong>
                        </div>

                        <div class="fare">
                            <div class="fare-label">RECORDED RENTAL FARE</div>
                            <div class="fare-amount">
                                ₹<?php echo number_format((float)($row['total_fare'] ?? 0), 2); ?>
                            </div>
                        </div>

                        <div class="actions">

                            <a class="btn red"
                               href="receipt.php?booking_id=<?php echo $bookingId; ?>">
                                View / Print Receipt
                            </a>

                            <?php if ($status === 'Pending Payment'): ?>
                                <a class="btn green"
                                   href="payment.php?booking_id=<?php echo $bookingId; ?>">
                                    Continue to Payment
                                </a>
                            <?php endif; ?>

                            <?php if ($status === 'Booked'): ?>
                                <a class="btn red"
                                   href="return_vehicle.php?booking_id=<?php echo $bookingId; ?>">
                                    Return Vehicle
                                </a>
                            <?php endif; ?>

                            <a class="btn"
                               href="track_vehicle.php?booking_id=<?php echo $bookingId; ?>">
                                Track Vehicle
                            </a>

                        </div>
                    </div>
                </article>

            <?php endforeach; ?>

        </section>

    <?php endif; ?>

</main>

<footer class="footer">
    VehicleRental — My Bookings<br>
    Recorded fare is a project value and does not independently verify real payment.
</footer>

</body>
</html>
```