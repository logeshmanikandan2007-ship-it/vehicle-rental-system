
<?php
session_start();
include 'db.php';

// Check login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

$userId = (int) $_SESSION['user_id'];
$userName = trim($_SESSION['name']);

$error = "";
$vehicle = null;

// Get selected vehicle
$vehicleId = isset($_GET['vehicle_id'])
    ? (int) $_GET['vehicle_id']
    : (int) ($_POST['vehicle_id'] ?? 0);

if ($vehicleId <= 0) {
    die("Invalid vehicle ID. Please select a vehicle.");
}

// Fetch vehicle details
$sql = "SELECT * FROM vehicles WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $vehicleId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$vehicle = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$vehicle) {
    die("Vehicle not found.");
}

$rate = 0;

if (isset($vehicle['rate_per_km'])) {
    $rate = (float) $vehicle['rate_per_km'];
} elseif (isset($vehicle['price_per_km'])) {
    $rate = (float) $vehicle['price_per_km'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $bookingDate = trim($_POST['booking_date'] ?? '');
    $startKm = filter_var(
        $_POST['start_km'] ?? null,
        FILTER_VALIDATE_FLOAT
    );

    // Validate date
    $dateObject = DateTime::createFromFormat('Y-m-d', $bookingDate);
    $validDate = $dateObject &&
        $dateObject->format('Y-m-d') === $bookingDate;

    // Validate KM
    if (!$validDate) {
        $error = "Please select a valid booking date.";
    } elseif ($bookingDate < date('Y-m-d')) {
        $error = "Booking date cannot be in the past.";
    } elseif ($startKm === false || $startKm < 0) {
        $error = "Please enter a valid starting kilometre.";
    } elseif ($rate <= 0) {
        $error = "Vehicle kilometre rate is not configured correctly.";
    } else {

        // Save the exact logged-in session name.
        // End KM and fare are entered/calculated when returning the vehicle.
        $status = "Booked";

        $insertSql = "
            INSERT INTO bookings
                (user_name, vehicle_id, vehicle_name,
                 booking_date, start_km, end_km,
                 total_km, total_fare, status)
            VALUES (?, ?, ?, ?, ?, 0, 0, 0, ?)
        ";

        $insertStmt = mysqli_prepare($conn, $insertSql);

        mysqli_stmt_bind_param(
            $insertStmt,
            "sissds",
            $userName,
            $vehicleId,
            $vehicle['vehicle_name'],
            $bookingDate,
            $startKm,
            $status
        );

        if (mysqli_stmt_execute($insertStmt)) {
            $newBookingId = mysqli_insert_id($conn);
            mysqli_stmt_close($insertStmt);

            header(
                "Location: return_vehicle.php?booking_id=" .
                $newBookingId
            );
            exit();
        } else {
            $error = "Booking failed. Please try again.";
            mysqli_stmt_close($insertStmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VehicleRental | Book Vehicle</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #101010;
            color: #fff;
            min-height: 100vh;
        }

        header {
            background: #181818;
            padding: 20px 7%;
            border-bottom: 2px solid #e50914;
        }

        .logo {
            font-size: 25px;
            font-weight: bold;
        }

        .logo span {
            color: #e50914;
        }

        .container {
            max-width: 620px;
            margin: 45px auto;
            padding: 30px;
            background: #1b1b1b;
            border: 1px solid #333;
            border-radius: 14px;
        }

        h1 {
            margin-bottom: 10px;
        }

        .subtitle {
            color: #aaa;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .vehicle {
            padding: 20px;
            background: #252525;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .vehicle h2 {
            margin-bottom: 10px;
            font-size: 21px;
        }

        .rate {
            color: #ff343d;
            font-size: 22px;
            font-weight: bold;
            margin-top: 10px;
        }

        label {
            display: block;
            margin: 17px 0 8px;
            color: #ddd;
        }

        input {
            width: 100%;
            padding: 13px;
            background: #252525;
            color: white;
            border: 1px solid #444;
            border-radius: 7px;
            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #e50914;
        }

        button {
            width: 100%;
            padding: 15px;
            margin-top: 24px;
            background: #e50914;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #bd000a;
        }

        .error {
            padding: 13px;
            background: #461b1b;
            border: 1px solid #a33;
            color: #ffbaba;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .note {
            margin-top: 16px;
            color: #aaa;
            font-size: 13px;
            line-height: 1.6;
        }

        .back {
            display: inline-block;
            margin-top: 20px;
            color: #ff454d;
            text-decoration: none;
        }

        @media (max-width: 650px) {
            .container {
                margin: 25px 15px;
                padding: 22px;
            }
        }
    </style>
</head>

<body>

<header>
    <div class="logo">Vehicle<span>Rental</span></div>
</header>

<div class="container">

    <h1>Book Your Vehicle</h1>

    <p class="subtitle">
        Welcome, <?php echo htmlspecialchars($userName); ?>!
        Enter your booking details below.
    </p>

    <?php if ($error !== ''): ?>
        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="vehicle">
        <h2>
            <?php echo htmlspecialchars($vehicle['vehicle_name']); ?>
        </h2>

        <p>
            Vehicle type:
            <?php echo htmlspecialchars($vehicle['vehicle_type']); ?>
        </p>

        <p class="rate">
            ₹<?php echo number_format($rate, 2); ?> / km
        </p>
    </div>

    <form method="POST"
          action="booking.php?vehicle_id=<?php echo $vehicleId; ?>">

        <input type="hidden"
               name="vehicle_id"
               value="<?php echo $vehicleId; ?>">

        <label for="booking_date">Booking Date</label>
        <input
            type="date"
            id="booking_date"
            name="booking_date"
            min="<?php echo date('Y-m-d'); ?>"
            value="<?php echo htmlspecialchars($_POST['booking_date'] ?? date('Y-m-d')); ?>"
            required
        >

        <label for="start_km">Starting Kilometre (KM)</label>
        <input
            type="number"
            id="start_km"
            name="start_km"
            min="0"
            step="0.01"
            value="<?php echo htmlspecialchars($_POST['start_km'] ?? ''); ?>"
            placeholder="Example: 12000"
            required
        >

        <button type="submit">Confirm Booking</button>

    </form>

    <p class="note">
        Final distance and fare will be calculated when the vehicle is returned.
    </p>

    <a class="back" href="vehicles.php">← Back to Vehicles</a>

</div>

</body>
</html>
