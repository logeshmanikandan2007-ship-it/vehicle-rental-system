
<?php
session_start();
include 'db.php';

$isLoggedIn = isset($_SESSION['user_id']);

$sql = "SELECT * FROM vehicles WHERE status = 'Available' ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Unable to load vehicles. Please check your database columns.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Our Fleet | VehicleRental</title>

<style>
:root {
    --bg: #080808;
    --card: #121212;
    --red: #ff3030;
    --text: #ffffff;
    --muted: #a1a1aa;
    --border: #292929;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: var(--bg);
    color: var(--text);
    font-family: Arial, Helvetica, sans-serif;
    line-height: 1.6;
}

a {
    color: inherit;
    text-decoration: none;
}

.navbar {
    position: sticky;
    top: 0;
    z-index: 10;
    padding: 17px 7%;
    background: rgba(10,10,10,.96);
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
    flex-wrap: wrap;
}

.logo {
    font-size: 25px;
    font-weight: 900;
    letter-spacing: -.8px;
}

.logo span,
.red {
    color: var(--red);
}

.nav-links {
    display: flex;
    gap: 22px;
    align-items: center;
    flex-wrap: wrap;
}

.nav-links a {
    color: #d4d4d4;
    font-size: 13px;
    transition: .25s;
}

.nav-links a:hover,
.nav-links a.active {
    color: var(--red);
}

.container {
    width: 86%;
    max-width: 1250px;
    margin: auto;
}

.hero {
    position: relative;
    overflow: hidden;
    padding: 65px 0 40px;
}

.hero::before {
    content: "";
    position: absolute;
    width: 340px;
    height: 340px;
    right: -100px;
    top: 0;
    background: var(--red);
    opacity: .09;
    filter: blur(100px);
    border-radius: 50%;
    pointer-events: none;
}

.eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    color: #ff7777;
    font-size: 11px;
    font-weight: bold;
    letter-spacing: 3px;
    text-transform: uppercase;
}

.eyebrow::before {
    content: "";
    width: 25px;
    height: 2px;
    background: var(--red);
}

.hero h1 {
    font-size: clamp(36px, 5vw, 62px);
    line-height: 1.08;
    letter-spacing: -2px;
    margin: 17px 0;
    max-width: 700px;
}

.hero p {
    max-width: 590px;
    color: var(--muted);
    font-size: 15px;
}

.hero-bottom {
    margin-top: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.pill {
    border: 1px solid #333;
    background: #111;
    padding: 8px 13px;
    border-radius: 30px;
    font-size: 12px;
    color: #ddd;
}

.pill strong {
    color: var(--red);
}

.fleet-heading {
    display: flex;
    justify-content: space-between;
    align-items: end;
    gap: 15px;
    margin: 20px 0 25px;
}

.fleet-heading h2 {
    font-size: 25px;
}

.fleet-heading p {
    color: var(--muted);
    font-size: 13px;
}

.vehicle-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 24px;
    padding-bottom: 75px;
}

.vehicle-card {
    min-width: 0;
    background: linear-gradient(145deg, #171717, #0e0e0e);
    border: 1px solid var(--border);
    border-radius: 13px;
    overflow: hidden;
    transition: transform .3s, border-color .3s, box-shadow .3s;
}

.vehicle-card:hover {
    transform: translateY(-7px);
    border-color: rgba(255,48,48,.8);
    box-shadow: 0 18px 40px rgba(255,48,48,.09);
}

.vehicle-image {
    height: 225px;
    position: relative;
    overflow: hidden;
    background: #202020;
}

.vehicle-image::after {
    content: "";
    position: absolute;
    inset: 45% 0 0;
    background: linear-gradient(transparent, rgba(0,0,0,.45));
    pointer-events: none;
}

.vehicle-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .45s;
}

.vehicle-card:hover .vehicle-image img {
    transform: scale(1.06);
}

.type-badge {
    position: absolute;
    top: 14px;
    left: 14px;
    z-index: 1;
    padding: 6px 11px;
    background: var(--red);
    color: white;
    font-size: 10px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    border-radius: 4px;
}

.vehicle-info {
    padding: 21px;
}

.vehicle-info h2 {
    font-size: 21px;
    line-height: 1.3;
    margin-bottom: 7px;
}

.vehicle-meta {
    color: var(--muted);
    font-size: 12px;
}

.price-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    border-top: 1px solid #292929;
    margin-top: 18px;
    padding-top: 17px;
}

.price {
    color: var(--red);
    font-size: 27px;
    font-weight: 900;
    line-height: 1.2;
}

.price small {
    color: #a1a1aa;
    font-size: 12px;
    font-weight: normal;
}

.available {
    color: #75e49a;
    font-size: 11px;
    white-space: nowrap;
}

.available span {
    display: inline-block;
    width: 7px;
    height: 7px;
    background: #50d879;
    border-radius: 50%;
    margin-right: 4px;
}

.book-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    margin-top: 20px;
    padding: 13px;
    border: 1px solid var(--red);
    border-radius: 6px;
    background: var(--red);
    color: white;
    font-size: 13px;
    font-weight: bold;
    transition: .25s;
}

.book-btn:hover {
    background: #d91f1f;
    border-color: #d91f1f;
    box-shadow: 0 7px 20px rgba(255,48,48,.18);
}

.login-note {
    text-align: center;
    color: var(--muted);
    font-size: 11px;
    margin-top: 10px;
}

.empty {
    grid-column: 1 / -1;
    padding: 65px 20px;
    text-align: center;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
}

.empty h2 {
    margin-bottom: 8px;
}

.empty p {
    color: var(--muted);
}

footer {
    border-top: 1px solid var(--border);
    padding: 25px 7%;
    text-align: center;
    color: #858585;
    font-size: 12px;
}

@media (max-width: 950px) {
    .vehicle-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .container {
        width: 90%;
    }
}

@media (max-width: 600px) {
    .navbar {
        padding: 15px 5%;
    }

    .nav-links {
        gap: 13px;
    }

    .nav-links a {
        font-size: 12px;
    }

    .logo {
        font-size: 23px;
    }

    .hero {
        padding-top: 45px;
    }

    .hero h1 {
        letter-spacing: -1px;
    }

    .fleet-heading {
        align-items: start;
        flex-direction: column;
    }

    .vehicle-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .vehicle-image {
        height: 240px;
    }

    .vehicle-info {
        padding: 19px;
    }
}
</style>
</head>

<body>

<header class="navbar">
    <a href="index.php" class="logo">Vehicle<span>Rental</span></a>

    <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="vehicles.php" class="active">Vehicles</a>

        <?php if ($isLoggedIn): ?>
            <a href="track_vehicle.php">Track Booking</a>
            <a href="booking_history.php">My Bookings</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

<main class="container">

    <section class="hero">
        <div class="eyebrow">Find Your Perfect Ride</div>

        <h1>Choose Your Ride.<br>
            <span class="red">Own The Journey.</span>
        </h1>

        <p>
            Explore our collection of cars and bikes. Compare kilometre rates,
            choose your favourite vehicle, and get ready for your next journey.
        </p>

        <div class="hero-bottom">
            <div class="pill"><strong>●</strong> Available Vehicles</div>
            <div class="pill">Transparent <strong>₹ / KM</strong> Pricing</div>
        </div>
    </section>

    <section class="fleet-heading">
        <div>
            <h2>Explore Our <span class="red">Fleet</span></h2>
            <p>Find the right vehicle for your next trip.</p>
        </div>
    </section>

    <section class="vehicle-grid">

    <?php if (mysqli_num_rows($result) > 0): ?>

        <?php while ($vehicle = mysqli_fetch_assoc($result)): ?>

            <?php
            $vehicleName = $vehicle['vehicle_name'] ?? 'Vehicle';
            $vehicleType = $vehicle['vehicle_type'] ?? 'Other';

            $rate = $vehicle['rate_per_km']
                ?? $vehicle['price_per_km']
                ?? 0;

            $image = trim($vehicle['image'] ?? '');

            if ($image !== '') {
                // Supports either "Yamaha-R15-New.jpg"
                // or "images/Yamaha-R15-New.jpg" in the database.
                $imagePath = 'images/' . basename($image);
            } else {
                $imagePath = '';
            }
            ?>

            <article class="vehicle-card">

                <div class="vehicle-image">
                    <?php if ($imagePath !== ''): ?>
                        <img
                            src="<?php echo htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8'); ?>"
                            alt="<?php echo htmlspecialchars($vehicleName, ENT_QUOTES, 'UTF-8'); ?>"
                            loading="lazy"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';"
                        >
                        <div style="display:none;height:100%;place-items:center;color:#aaa;font-size:13px;">
                            Image unavailable
                        </div>
                    <?php else: ?>
                        <div style="height:100%;display:grid;place-items:center;color:#aaa;font-size:13px;">
                            Vehicle image unavailable
                        </div>
                    <?php endif; ?>

                    <span class="type-badge">
                        <?php echo htmlspecialchars($vehicleType, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>

                <div class="vehicle-info">
                    <h2><?php echo htmlspecialchars($vehicleName, ENT_QUOTES, 'UTF-8'); ?></h2>

                    <div class="vehicle-meta">
                        Distance-based rental · Flexible booking
                    </div>

                    <div class="price-row">
                        <div class="price">
                            &#8377;<?php echo number_format((float)$rate, 2); ?>
                            <small>/ km</small>
                        </div>

                        <div class="available"><span></span>Available</div>
                    </div>

                    <?php if ($isLoggedIn): ?>
                        <a
                            class="book-btn"
                            href="booking.php?vehicle_id=<?php echo (int)$vehicle['id']; ?>"
                        >
                            Book This Vehicle <span aria-hidden="true">→</span>
                        </a>
                    <?php else: ?>
                        <a class="book-btn" href="login.php">
                            Login to Book <span aria-hidden="true">→</span>
                        </a>
                        <div class="login-note">
                            Please log in to reserve this vehicle.
                        </div>
                    <?php endif; ?>
                </div>

            </article>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="empty">
            <h2>No Vehicles Available</h2>
            <p>Please check back later or contact the administrator.</p>
        </div>

    <?php endif; ?>

    </section>
</main>

<footer>
    &copy; <?php echo date('Y'); ?> VehicleRental. All rights reserved.
</footer>

</body>
</html>
