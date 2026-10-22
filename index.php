```php
<?php
session_start();

$isLoggedIn = isset($_SESSION['user_id'], $_SESSION['name']);
$userName = $isLoggedIn ? $_SESSION['name'] : '';

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>VehicleRental | Drive Your Way</title>

<meta name="description"
content="Explore cars and bikes, book your vehicle, track your rental and view your booking history with VehicleRental.">

<style>
:root {
    --red: #f32932;
    --red-dark: #b90e18;
    --black: #090909;
    --card: #151515;
    --muted: #aaa;
    --line: #303030;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: var(--black);
    color: #fff;
    line-height: 1.6;
}

a {
    color: inherit;
    text-decoration: none;
}

.container {
    width: 90%;
    max-width: 1180px;
    margin: auto;
}

/* NAVIGATION */

.navbar {
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(10, 10, 10, .96);
    border-bottom: 1px solid #292929;
    backdrop-filter: blur(12px);
}

.nav-inner {
    min-height: 76px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.logo {
    font-size: 25px;
    font-weight: 900;
    letter-spacing: -.8px;
}

.logo span {
    color: var(--red);
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 23px;
    flex-wrap: wrap;
}

.nav-links a {
    color: #d0d0d0;
    font-size: 13px;
    transition: color .2s;
}

.nav-links a:hover {
    color: #ff525a;
}

/* BUTTONS */

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 13px 20px;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 700;
    border: 1px solid #424242;
    transition: transform .2s, background .2s;
    cursor: pointer;
}

.btn:hover {
    transform: translateY(-2px);
}

.btn-primary {
    background: var(--red);
    border-color: var(--red);
    color: #fff;
}

.btn-primary:hover {
    background: var(--red-dark);
}

.btn-outline {
    background: transparent;
    color: #fff;
}

/* HERO */

.hero {
    min-height: 570px;
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
    background:
        radial-gradient(ellipse at 85% 45%,
        rgba(243, 41, 50, .19), transparent 38%),
        linear-gradient(115deg, #101010, #090909 72%);
    border-bottom: 1px solid #292929;
}

.hero::after {
    content: "VR";
    position: absolute;
    right: 3%;
    bottom: -110px;
    font-size: clamp(220px, 35vw, 460px);
    font-weight: 900;
    line-height: 1;
    color: rgba(255, 255, 255, .025);
    pointer-events: none;
}

.hero-content {
    position: relative;
    z-index: 1;
    max-width: 720px;
    padding: 80px 0;
}

.eyebrow {
    display: inline-block;
    padding: 7px 12px;
    background: #291315;
    border: 1px solid #572429;
    border-radius: 30px;
    color: #ff999e;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
}

.hero h1 {
    font-size: clamp(42px, 7vw, 76px);
    line-height: 1.08;
    letter-spacing: -2px;
    margin-top: 25px;
}

.hero h1 span {
    color: var(--red);
}

.hero p {
    max-width: 540px;
    margin-top: 23px;
    color: #b4b4b4;
    font-size: 16px;
    line-height: 1.9;
}

.hero-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 13px;
    margin-top: 30px;
}

.hero-note {
    color: #777;
    font-size: 12px;
    margin-top: 25px;
}

/* QUICK FEATURES */

.features {
    padding: 28px 0;
    background: #111;
    border-bottom: 1px solid #292929;
}

.feature-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 22px;
}

.feature {
    display: flex;
    align-items: center;
    gap: 14px;
}

.feature-icon {
    width: 45px;
    height: 45px;
    display: grid;
    place-items: center;
    background: #281315;
    border: 1px solid #512328;
    border-radius: 10px;
    font-size: 21px;
    flex-shrink: 0;
}

.feature strong {
    display: block;
    font-size: 14px;
}

.feature small {
    display: block;
    color: var(--muted);
    font-size: 12px;
    margin-top: 3px;
}

/* SECTION */

.section {
    padding: 82px 0;
}

.section-heading {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 35px;
}

.section-label {
    color: #ff6068;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2px;
}

.section-heading h2 {
    font-size: clamp(28px, 4vw, 42px);
    line-height: 1.2;
    margin-top: 10px;
}

.section-heading p {
    color: var(--muted);
    font-size: 13px;
    margin-top: 10px;
}

/* VEHICLE SHOWCASE */

.vehicle-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 22px;
}

.vehicle-card {
    background: var(--card);
    border: 1px solid #303030;
    border-radius: 13px;
    overflow: hidden;
    transition: transform .25s, border-color .25s;
}

.vehicle-card:hover {
    transform: translateY(-5px);
    border-color: #873037;
}

.vehicle-image {
    height: 205px;
    display: grid;
    place-items: center;
    overflow: hidden;
    background:
        radial-gradient(ellipse at center,
        #302022, #151515 70%);
}

.vehicle-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 12px;
    transition: transform .3s;
}

.vehicle-card:hover .vehicle-image img {
    transform: scale(1.04);
}

.vehicle-info {
    padding: 20px;
}

.vehicle-type {
    color: #ff777e;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.5px;
}

.vehicle-info h3 {
    font-size: 19px;
    margin-top: 7px;
}

.vehicle-info p {
    color: #aaa;
    font-size: 12px;
    margin-top: 8px;
    min-height: 39px;
}

.vehicle-info .btn {
    width: 100%;
    margin-top: 18px;
}

/* HOW IT WORKS */

.steps-section {
    background: #101010;
    border-top: 1px solid #292929;
    border-bottom: 1px solid #292929;
}

.steps-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.step {
    padding: 26px;
    background: #171717;
    border: 1px solid #303030;
    border-radius: 12px;
}

.step-number {
    color: var(--red);
    font-size: 28px;
    font-weight: 900;
}

.step h3 {
    margin-top: 12px;
    font-size: 18px;
}

.step p {
    margin-top: 8px;
    color: #aaa;
    font-size: 13px;
}

/* CTA */

.cta {
    padding: 65px 30px;
    text-align: center;
    background:
        radial-gradient(ellipse at center,
        #321417, #151010 70%);
    border: 1px solid #512328;
    border-radius: 16px;
}

.cta h2 {
    font-size: clamp(28px, 4vw, 42px);
    line-height: 1.2;
}

.cta p {
    max-width: 560px;
    margin: 15px auto 25px;
    color: #c0b4b4;
    font-size: 14px;
}

/* FOOTER */

.footer {
    margin-top: 75px;
    padding: 35px 0;
    background: #101010;
    border-top: 1px solid #292929;
}

.footer-inner {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
}

.footer p {
    color: #888;
    font-size: 12px;
    margin-top: 8px;
}

.footer-links {
    display: flex;
    flex-wrap: wrap;
    gap: 17px;
}

.footer-links a {
    color: #bbb;
    font-size: 12px;
}

/* MOBILE */

@media (max-width: 850px) {
    .nav-inner {
        padding: 16px 0;
        align-items: flex-start;
        flex-direction: column;
    }

    .nav-links {
        gap: 14px;
    }

    .hero {
        min-height: 500px;
    }

    .vehicle-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .steps-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 560px) {
    .container {
        width: 92%;
    }

    .nav-links {
        gap: 12px;
    }

    .nav-links a {
        font-size: 12px;
    }

    .hero h1 {
        letter-spacing: -1px;
    }

    .hero p {
        font-size: 14px;
    }

    .feature-grid {
        grid-template-columns: 1fr;
    }

    .vehicle-grid {
        grid-template-columns: 1fr;
    }

    .vehicle-image {
        height: 230px;
    }

    .section {
        padding: 58px 0;
    }

    .hero-buttons .btn {
        width: 100%;
    }

    .cta {
        padding: 42px 18px;
    }
}
</style>
</head>

<body>

<header class="navbar">
    <div class="container nav-inner">

        <a class="logo" href="index.php">
            Vehicle<span>Rental</span>
        </a>

        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="vehicles.php">Vehicles</a>

            <?php if ($isLoggedIn): ?>
                <a href="booking_history.php">My Bookings</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>

    </div>
</header>

<main>

<section class="hero">
    <div class="container">
        <div class="hero-content">

            <span class="eyebrow">YOUR JOURNEY. YOUR RULES.</span>

            <?php if ($isLoggedIn): ?>
                <h1>Welcome back,<br><span><?php echo h($userName); ?>.</span></h1>
                <p>
                    Your next journey starts here.
                    Explore available vehicles and manage your rental bookings
                    from one place.
                </p>
            <?php else: ?>
                <h1>Drive Your Way.<br><span>Own The Journey.</span></h1>
                <p>
                    Find a ride for your next adventure.
                    Explore cars and bikes, book your preferred vehicle
                    and manage your rental journey with ease.
                </p>
            <?php endif; ?>

            <div class="hero-buttons">
                <a class="btn btn-primary" href="vehicles.php">
                    Explore Vehicles &rarr;
                </a>

                <?php if ($isLoggedIn): ?>
                    <a class="btn btn-outline" href="booking_history.php">
                        My Bookings
                    </a>
                <?php else: ?>
                    <a class="btn btn-outline" href="register.php">
                        Get Started
                    </a>
                <?php endif; ?>
            </div>

            <div class="hero-note">
                SIMPLE BOOKING &nbsp; / &nbsp; KM-BASED FARE &nbsp; / &nbsp; RENTAL HISTORY
            </div>

        </div>
    </div>
</section>

<section class="features">
    <div class="container feature-grid">

        <div class="feature">
            <div class="feature-icon">🚗</div>
            <div>
                <strong>Cars & Bikes</strong>
                <small>Explore the available fleet</small>
            </div>
        </div>

        <div class="feature">
            <div class="feature-icon">📍</div>
            <div>
                <strong>Location Tracking</strong>
                <small>Use the tracking feature when enabled</small>
            </div>
        </div>

        <div class="feature">
            <div class="feature-icon">🧾</div>
            <div>
                <strong>Booking Receipts</strong>
                <small>View and print your rental records</small>
            </div>
        </div>

    </div>
</section>

<section class="section" id="vehicles">
    <div class="container">

        <div class="section-heading">
            <div>
                <span class="section-label">FIND YOUR RIDE</span>
                <h2>Choose Your Machine.</h2>
                <p>Find a vehicle that fits your next journey.</p>
            </div>

            <a class="btn btn-outline" href="vehicles.php">
                View All Vehicles &rarr;
            </a>
        </div>

        <div class="vehicle-grid">

            <article class="vehicle-card">
                <div class="vehicle-image">
                    <img
                        src="images/Swift.jpg"
                        alt="Maruti Swift car"
                        loading="lazy"
                        onerror="this.style.display='none'; this.parentElement.innerHTML='<span style=&quot;font-size:64px&quot;>🚗</span>';">
                </div>

                <div class="vehicle-info">
                    <span class="vehicle-type">CITY DRIVE</span>
                    <h3>Maruti Swift</h3>
                    <p>Ideal for everyday travel and city journeys.</p>
                    <a class="btn btn-primary" href="vehicles.php">
                        Explore Cars
                    </a>
                </div>
            </article>

            <article class="vehicle-card">
                <div class="vehicle-image">
                    <img
                        src="images/I20.jpg"
                        alt="Hyundai i20 car"
                        loading="lazy"
                        onerror="this.style.display='none'; this.parentElement.innerHTML='<span style=&quot;font-size:64px&quot;>🚘</span>';">
                </div>

                <div class="vehicle-info">
                    <span class="vehicle-type">COMFORT DRIVE</span>
                    <h3>Hyundai i20</h3>
                    <p>A stylish choice for comfortable road trips.</p>
                    <a class="btn btn-primary" href="vehicles.php">
                        Explore Cars
                    </a>
                </div>
            </article>

            <article class="vehicle-card">
                <div class="vehicle-image">
                    <img
                        src="images/Bullet.jpg"
                        alt="Royal Enfield motorcycle"
                        loading="lazy"
                        onerror="this.style.display='none'; this.parentElement.innerHTML='<span style=&quot;font-size:64px&quot;>🏍️</span>';">
                </div>

                <div class="vehicle-info">
                    <span class="vehicle-type">ROAD ADVENTURE</span>
                    <h3>Royal Enfield</h3>
                    <p>Enjoy the road with a classic motorcycle ride.</p>
                    <a class="btn btn-primary" href="vehicles.php">
                        Explore Bikes
                    </a>
                </div>
            </article>

        </div>
    </div>
</section>

<section class="section steps-section">
    <div class="container">

        <div class="section-heading">
            <div>
                <span class="section-label">HOW IT WORKS</span>
                <h2>Your Ride In Three Steps.</h2>
                <p>A simple flow to help you manage your rental.</p>
            </div>
        </div>

        <div class="steps-grid">

            <article class="step">
                <div class="step-number">01</div>
                <h3>Choose Your Vehicle</h3>
                <p>
                    Browse the vehicle list and select the ride
                    that matches your needs.
                </p>
            </article>

            <article class="step">
                <div class="step-number">02</div>
                <h3>Book & Enter KM</h3>
                <p>
                    Follow the booking process and enter the
                    odometer readings when required.
                </p>
            </article>

            <article class="step">
                <div class="step-number">03</div>
                <h3>Review Your Fare</h3>
                <p>
                    Review your recorded distance and fare,
                    then use the available checkout options.
                </p>
            </article>

        </div>
    </div>
</section>

<section class="section">
    <div class="container">

        <div class="cta">
            <span class="section-label">READY TO ROLL?</span>
            <h2>Your Next Journey Starts Here.</h2>
            <p>
                Explore the available vehicles and start planning
                your next ride with VehicleRental.
            </p>

            <a class="btn btn-primary" href="vehicles.php">
                Browse Available Vehicles &rarr;
            </a>
        </div>

    </div>
</section>

</main>

<footer class="footer">
    <div class="container footer-inner">

        <div>
            <a class="logo" href="index.php">
                Vehicle<span>Rental</span>
            </a>
            <p>Find your ride. Plan your journey.</p>
            <p>&copy; <?php echo date('Y'); ?> VehicleRental. College project.</p>
        </div>

        <nav class="footer-links">
            <a href="index.php">Home</a>
            <a href="vehicles.php">Vehicles</a>

            <?php if ($isLoggedIn): ?>
                <a href="booking_history.php">My Bookings</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>

    </div>
</footer>

</body>
</html>
```