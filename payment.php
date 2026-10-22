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
$bookingId = (int)($_GET['booking_id'] ?? $_POST['booking_id'] ?? 0);

if ($bookingId <= 0) {
    header("Location: booking_history.php");
    exit();
}

// Fetch and verify booking ownership
$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM bookings WHERE id = ?"
);

if (!$stmt) {
    die("Unable to load booking.");
}

mysqli_stmt_bind_param($stmt, "i", $bookingId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$booking = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$booking) {
    die("Booking not found.");
}

if (strcasecmp(trim($booking['user_name']), $userName) !== 0) {
    http_response_code(403);
    die("You are not authorized to access this booking.");
}

$error = '';
$selectedMethod = $_POST['payment_method'] ?? '';

// Process demo payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $allowedMethods = ['UPI', 'Card', 'Cash'];

    if (!in_array($selectedMethod, $allowedMethods, true)) {
        $error = "Please select a payment method.";
    } else {

        // Check the current booking status again
        $check = mysqli_prepare(
            $conn,
            "SELECT status FROM bookings
             WHERE id = ? AND user_name = ?"
        );

        if (!$check) {
            die("Unable to verify booking status.");
        }

        mysqli_stmt_bind_param($check, "is", $bookingId, $userName);
        mysqli_stmt_execute($check);
        $checkResult = mysqli_stmt_get_result($check);
        $current = mysqli_fetch_assoc($checkResult);
        mysqli_stmt_close($check);

        if (!$current) {
            http_response_code(403);
            die("Booking access denied.");
        }

        if ($current['status'] !== 'Pending Payment') {
            $error = "This booking is not awaiting payment.";
        } elseif ($selectedMethod === 'UPI') {
            $error = "UPI is currently a demo option. No money was transferred. Connect a real payment gateway before accepting UPI payments.";
        } else {
            /*
             * DEMO ONLY:
             * This updates the booking status for a college project.
             * It does not verify a real card transaction or cash receipt.
             */
            $update = mysqli_prepare(
                $conn,
                "UPDATE bookings
                 SET status = 'Completed'
                 WHERE id = ?
                   AND user_name = ?
                   AND status = 'Pending Payment'"
            );

            if (!$update) {
                die("Unable to process the demo payment.");
            }

            mysqli_stmt_bind_param($update, "is", $bookingId, $userName);
            mysqli_stmt_execute($update);
            $updated = mysqli_stmt_affected_rows($update);
            mysqli_stmt_close($update);

            if ($updated === 1) {
                header("Location: booking_history.php?payment=success");
                exit();
            } else {
                $error = "Unable to update booking. Please refresh and try again.";
            }
        }
    }
}

// Refresh booking details
$refresh = mysqli_prepare(
    $conn,
    "SELECT * FROM bookings WHERE id = ?"
);

if (!$refresh) {
    die("Unable to refresh booking details.");
}

mysqli_stmt_bind_param($refresh, "i", $bookingId);
mysqli_stmt_execute($refresh);
$refreshResult = mysqli_stmt_get_result($refresh);
$booking = mysqli_fetch_assoc($refreshResult);
mysqli_stmt_close($refresh);

if (
    !$booking ||
    strcasecmp(trim($booking['user_name']), $userName) !== 0
) {
    http_response_code(403);
    die("Booking access denied.");
}

$fare = (float)$booking['total_fare'];
$status = $booking['status'];
$canPay = ($status === 'Pending Payment');

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>VehicleRental | Checkout</title>

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
        radial-gradient(ellipse at 90% 0%, rgba(237,28,36,.16), transparent 38%),
        #090909;
}

a {
    color: inherit;
    text-decoration: none;
}

.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    padding: 20px 7%;
    background: #111;
    border-bottom: 1px solid #292929;
}

.logo {
    font-size: 25px;
    font-weight: 900;
    letter-spacing: -.8px;
}

.logo span {
    color: var(--red);
}

.navlinks {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.navlinks a {
    color: #ccc;
    font-size: 14px;
}

.navlinks a:hover {
    color: #ff5a60;
}

.page {
    max-width: 1100px;
    width: 92%;
    margin: 42px auto;
}

.topline {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.eyebrow {
    display: inline-block;
    padding: 9px 13px;
    color: #ff7b80;
    background: #251315;
    border: 1px solid #522226;
    border-radius: 30px;
    font-size: 11px;
    font-weight: bold;
    letter-spacing: 1.3px;
}

.secure {
    color: var(--green);
    font-size: 12px;
}

h1 {
    margin-top: 20px;
    font-size: clamp(30px, 5vw, 44px);
    letter-spacing: -1px;
}

.subtitle {
    color: var(--muted);
    margin-top: 10px;
    line-height: 1.7;
}

.grid {
    display: grid;
    grid-template-columns: 1fr .95fr;
    gap: 24px;
    margin-top: 30px;
    align-items: start;
}

.card {
    background: linear-gradient(145deg, #191919, #101010);
    border: 1px solid var(--line);
    border-radius: 18px;
    padding: 25px;
    box-shadow: 0 18px 40px rgba(0,0,0,.25);
}

.card h2 {
    font-size: 19px;
    margin-bottom: 20px;
}

.vehicle {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 19px;
    background: linear-gradient(110deg, #351518, #202020);
    border: 1px solid #512326;
    border-radius: 13px;
    margin-bottom: 17px;
}

.vehicle-icon {
    width: 58px;
    height: 58px;
    flex-shrink: 0;
    display: grid;
    place-items: center;
    font-size: 29px;
    background: #4a1a1e;
    border-radius: 13px;
}

.vehicle-name {
    font-size: 20px;
    font-weight: 800;
    overflow-wrap: anywhere;
}

.ref {
    margin-top: 7px;
    font-size: 12px;
    color: #bdbdbd;
}

.row {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 14px 0;
    border-bottom: 1px solid #2b2b2b;
    line-height: 1.5;
}

.row span {
    color: var(--muted);
    font-size: 14px;
}

.row strong {
    text-align: right;
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

.total {
    margin-top: 20px;
    padding: 21px;
    border-radius: 14px;
    background: linear-gradient(120deg, #351417, #211315);
    border: 1px solid #592328;
}

.total-label {
    color: #d7b9bb;
    font-size: 12px;
    letter-spacing: 1.3px;
}

.total-price {
    margin-top: 9px;
    color: #ff545c;
    font-size: 36px;
    font-weight: 900;
    overflow-wrap: anywhere;
}

.total-note {
    margin-top: 8px;
    color: #c3aeb0;
    font-size: 12px;
    line-height: 1.6;
}

.method {
    display: flex;
    align-items: flex-start;
    gap: 13px;
    padding: 16px;
    border: 1px solid #363636;
    border-radius: 12px;
    background: #191919;
    margin-bottom: 12px;
    cursor: pointer;
    transition: .2s;
}

.method:hover,
.method:has(input:checked) {
    border-color: var(--red);
    background: #261719;
}

.method input {
    width: 17px;
    height: 17px;
    margin-top: 3px;
    flex-shrink: 0;
    accent-color: var(--red);
}

.method-title {
    font-size: 15px;
    font-weight: bold;
}

.method small {
    display: block;
    margin-top: 6px;
    color: var(--muted);
    font-size: 12px;
    line-height: 1.6;
}

.qr-panel {
    display: none;
    margin: 12px 0 16px;
    padding: 17px;
    background: #202020;
    border: 1px dashed #7b373b;
    border-radius: 12px;
    text-align: center;
}

.qr-panel.show {
    display: block;
}

.qr-placeholder {
    width: 150px;
    height: 150px;
    margin: 12px auto;
    display: grid;
    place-items: center;
    padding: 15px;
    color: #ccc;
    background: #111;
    border: 1px solid #454545;
    border-radius: 8px;
    line-height: 1.5;
    font-size: 12px;
}

.qr-panel p {
    color: #bbb;
    font-size: 12px;
    line-height: 1.6;
}

.notice,
.error {
    padding: 14px 16px;
    margin: 18px 0;
    border-radius: 11px;
    line-height: 1.7;
    font-size: 13px;
}

.notice {
    color: #d5d5d5;
    background: #202020;
    border: 1px solid #393939;
}

.error {
    color: #ffc4c7;
    background: #351719;
    border: 1px solid #713037;
}

.btn {
    width: 100%;
    padding: 16px;
    color: #fff;
    background: linear-gradient(120deg, var(--red), var(--red-dark));
    border: 0;
    border-radius: 11px;
    font-size: 15px;
    font-weight: 800;
    cursor: pointer;
    transition: .2s;
}

.btn:hover {
    filter: brightness(1.12);
    transform: translateY(-2px);
}

.btn:disabled {
    opacity: .6;
    cursor: not-allowed;
    transform: none;
}

.links {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
    margin-top: 23px;
}

.links a {
    color: #ff666d;
    font-size: 13px;
}

.links a:hover {
    text-decoration: underline;
}

.footer {
    padding: 25px 12px 32px;
    color: #777;
    text-align: center;
    font-size: 12px;
    line-height: 1.8;
}

@media(max-width: 760px) {
    .navbar {
        padding: 18px 5%;
    }

    .navlinks {
        gap: 13px;
    }

    .navlinks a {
        font-size: 12px;
    }

    .page {
        margin: 28px auto;
    }

    .grid {
        grid-template-columns: 1fr;
    }

    .card {
        padding: 20px;
    }
}

@media(max-width: 380px) {
    .row {
        gap: 8px;
    }

    .total-price {
        font-size: 30px;
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

    <div class="topline">
        <span class="eyebrow">PREMIUM CHECKOUT</span>
        <span class="secure">● Booking ownership verified</span>
    </div>

    <h1>Complete Your Payment</h1>

    <p class="subtitle">
        Review your booking details and choose a demo payment option.
    </p>

    <?php if ($error !== ''): ?>
        <div class="error">
            <?php echo e($error); ?>
        </div>
    <?php endif; ?>

    <div class="grid">

        <section class="card">
            <h2>Your Booking</h2>

            <div class="vehicle">
                <div class="vehicle-icon">🏍️</div>

                <div>
                    <div class="vehicle-name">
                        <?php echo e($booking['vehicle_name']); ?>
                    </div>

                    <div class="ref">
                        Booking #<?php echo (int)$booking['id']; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <span>Customer</span>
                <strong><?php echo e($booking['user_name']); ?></strong>
            </div>

            <div class="row">
                <span>Booking date</span>
                <strong><?php echo e($booking['booking_date']); ?></strong>
            </div>

            <div class="row">
                <span>Start KM</span>
                <strong>
                    <?php echo number_format((float)$booking['start_km'], 1); ?> km
                </strong>
            </div>

            <div class="row">
                <span>End KM</span>
                <strong>
                    <?php echo number_format((float)$booking['end_km'], 1); ?> km
                </strong>
            </div>

            <div class="row">
                <span>Total distance</span>
                <strong>
                    <?php echo number_format((float)$booking['total_km'], 1); ?> km
                </strong>
            </div>

            <div class="row">
                <span>Booking status</span>
                <strong>
                    <span class="status"><?php echo e($status); ?></span>
                </strong>
            </div>

            <div class="total">
                <div class="total-label">TOTAL FARE</div>

                <div class="total-price">
                    ₹<?php echo number_format($fare, 2); ?>
                </div>

                <p class="total-note">
                    Fare displayed from the booking record. Confirm the
                    distance and fare before proceeding.
                </p>
            </div>

        </section>

        <section class="card">
            <h2>Choose Payment Method</h2>

            <?php if ($canPay): ?>

                <div class="notice">
                    <strong>Demo checkout:</strong>
                    This college project does not currently process real
                    payments. Card and Cash below are simulated options.
                    UPI will not mark a booking as paid.
                </div>

                <form method="POST"
                      action="payment.php?booking_id=<?php echo (int)$bookingId; ?>"
                      id="paymentForm">

                    <input type="hidden"
                           name="booking_id"
                           value="<?php echo (int)$bookingId; ?>">

                    <label class="method">
                        <input type="radio"
                               name="payment_method"
                               value="UPI"
                               <?php echo $selectedMethod === 'UPI' ? 'checked' : ''; ?>
                               required>

                        <span>
                            <span class="method-title">📱 UPI Payment</span>
                            <small>
                                Demo only. No genuine UPI transaction or
                                merchant QR is configured.
                            </small>
                        </span>
                    </label>

                    <div class="qr-panel" id="upiPanel">
                        <strong>UPI demo option</strong>

                        <div class="qr-placeholder">
                            DEMO ONLY<br>
                            No payment QR configured
                        </div>

                        <p>
                            Do not scan this placeholder. Connect a verified
                            merchant QR or payment gateway for real payments.
                        </p>
                    </div>

                    <label class="method">
                        <input type="radio"
                               name="payment_method"
                               value="Card"
                               <?php echo $selectedMethod === 'Card' ? 'checked' : ''; ?>
                               required>

                        <span>
                            <span class="method-title">💳 Debit / Credit Card</span>
                            <small>
                                Simulated college-project option. Do not enter
                                real card details.
                            </small>
                        </span>
                    </label>

                    <label class="method">
                        <input type="radio"
                               name="payment_method"
                               value="Cash"
                               <?php echo $selectedMethod === 'Cash' ? 'checked' : ''; ?>
                               required>

                        <span>
                            <span class="method-title">💵 Cash Payment</span>
                            <small>
                                Demo confirmation only. No cash receipt is
                                verified by this page.
                            </small>
                        </span>
                    </label>

                    <button type="submit" class="btn" id="payButton">
                        Continue Demo Checkout
                    </button>

                </form>

            <?php else: ?>

                <div class="notice">
                    <strong>Payment is not available.</strong><br>
                    Current booking status:
                    <?php echo e($status); ?>.
                    This booking is not awaiting payment.
                </div>

                <a href="booking_history.php" class="btn"
                   style="display:block;text-align:center;">
                    View My Bookings
                </a>

            <?php endif; ?>

            <div class="links">
                <a href="booking_history.php">← My Bookings</a>
                <a href="vehicles.php">Browse Vehicles</a>
            </div>

        </section>

    </div>

</main>

<footer class="footer">
    VehicleRental — College Project Demo<br>
    Secure booking ownership checks enabled.
    Real payment processing is not configured.
</footer>

<script>
const paymentForm = document.getElementById('paymentForm');
const upiPanel = document.getElementById('upiPanel');
const payButton = document.getElementById('payButton');

function updatePaymentUI() {
    if (!paymentForm || !upiPanel || !payButton) {
        return;
    }

    const selected = paymentForm.querySelector(
        'input[name="payment_method"]:checked'
    );

    upiPanel.classList.toggle(
        'show',
        selected !== null && selected.value === 'UPI'
    );

    if (selected && selected.value === 'UPI') {
        payButton.textContent = 'UPI Demo — No Real Payment';
    } else {
        payButton.textContent = 'Continue Demo Checkout';
    }
}

if (paymentForm) {
    paymentForm.querySelectorAll(
        'input[name="payment_method"]'
    ).forEach(function (radio) {
        radio.addEventListener('change', updatePaymentUI);
    });

    paymentForm.addEventListener('submit', function () {
        if (payButton) {
            payButton.disabled = true;
            payButton.textContent = 'Processing...';
        }
    });

    updatePaymentUI();
}
</script>

</body>
</html>
```