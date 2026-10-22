
<?php
session_start();
require_once 'db.php';

// Admin dashboard summary
$bookingCount = 0;
$vehicleCount = 0;
$userCount = 0;
$totalRevenue = 0;

// Count bookings
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings");
if ($result) {
    $bookingCount = (int) mysqli_fetch_assoc($result)['total'];
}

// Count vehicles
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM vehicles");
if ($result) {
    $vehicleCount = (int) mysqli_fetch_assoc($result)['total'];
}

// Count users
$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
if ($result) {
    $userCount = (int) mysqli_fetch_assoc($result)['total'];
}

// Revenue from completed bookings
$result = mysqli_query(
    $conn,
    "SELECT COALESCE(SUM(total_fare), 0) AS revenue
     FROM bookings
     WHERE status = 'Completed'"
);
if ($result) {
    $totalRevenue = (float) mysqli_fetch_assoc($result)['revenue'];
}

// Fetch all bookings
$result = mysqli_query($conn, "SELECT * FROM bookings ORDER BY id DESC");

if (!$result) {
    die("Unable to load bookings. Please check your database table.");
}

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VehicleRental | Admin Dashboard</title>

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
}

body {
    background: #101010;
    color: #f5f5f5;
    min-height: 100vh;
}

.navbar {
    background: #181818;
    border-bottom: 2px solid #e50914;
    padding: 19px 5%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    flex-wrap: wrap;
}

.logo {
    color: white;
    font-size: 25px;
    font-weight: bold;
}

.logo span {
    color: #e50914;
}

.nav-link {
    color: #ddd;
    text-decoration: none;
    margin-left: 18px;
    font-size: 14px;
}

.nav-link:hover {
    color: #ff454d;
}

.container {
    width: 92%;
    max-width: 1400px;
    margin: 35px auto;
}

.heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 28px;
}

.heading h1 {
    font-size: 30px;
    margin-bottom: 9px;
}

.heading p {
    color: #aaa;
    font-size: 14px;
    line-height: 1.6;
}

.admin-label {
    border: 1px solid #663039;
    background: #291518;
    color: #ff656d;
    padding: 12px 17px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: bold;
}

.stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 30px;
}

.stat-card {
    background: #1b1b1b;
    border: 1px solid #333;
    border-left: 4px solid #e50914;
    border-radius: 12px;
    padding: 23px;
}

.stat-card p {
    color: #aaa;
    font-size: 13px;
    margin-bottom: 13px;
}

.stat-card h2 {
    font-size: 27px;
    overflow-wrap: anywhere;
}

.stat-card small {
    display: block;
    color: #777;
    margin-top: 10px;
}

.panel {
    background: #191919;
    border: 1px solid #303030;
    border-radius: 13px;
    overflow: hidden;
}

.panel-header {
    padding: 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    flex-wrap: wrap;
    border-bottom: 1px solid #303030;
}

.panel-header h2 {
    font-size: 19px;
}

.search {
    background: #242424;
    color: white;
    border: 1px solid #444;
    border-radius: 8px;
    padding: 12px 14px;
    width: 300px;
    max-width: 100%;
    outline: none;
}

.search:focus {
    border-color: #e50914;
}

.table-wrap {
    width: 100%;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 1050px;
}

th {
    background: #222;
    color: #ff656d;
    text-transform: uppercase;
    letter-spacing: .4px;
    font-size: 11px;
    text-align: left;
    padding: 16px 14px;
    white-space: nowrap;
}

td {
    padding: 16px 14px;
    border-top: 1px solid #303030;
    color: #ddd;
    font-size: 13px;
    white-space: nowrap;
}

tbody tr:hover {
    background: #222;
}

.booking-id {
    color: #ff656d;
    font-weight: bold;
}

.fare {
    color: #fff;
    font-weight: bold;
}

.status {
    display: inline-block;
    border-radius: 20px;
    padding: 7px 11px;
    font-size: 11px;
    font-weight: bold;
    background: #303030;
    color: #ddd;
}

.status-booked {
    background: #302719;
    color: #f5c542;
}

.status-completed {
    background: #153323;
    color: #65e49a;
}

.status-cancelled {
    background: #391b1b;
    color: #ff8585;
}

.status-pending-payment {
    background: #352318;
    color: #ffb16d;
}

.cancel-btn {
    display: inline-block;
    background: #36191c;
    border: 1px solid #713038;
    color: #ff858b;
    padding: 8px 12px;
    text-decoration: none;
    border-radius: 7px;
    font-size: 12px;
    font-weight: bold;
}

.cancel-btn:hover {
    background: #e50914;
    color: white;
}

.no-action {
    color: #777;
    font-size: 12px;
}

.empty {
    padding: 35px;
    text-align: center;
    color: #aaa;
}

.back-btn {
    display: inline-block;
    margin-top: 25px;
    background: #e50914;
    color: white;
    padding: 12px 19px;
    text-decoration: none;
    border-radius: 8px;
    font-weight: bold;
    font-size: 14px;
}

.back-btn:hover {
    background: #bd000a;
}

footer {
    text-align: center;
    padding: 25px 15px;
    color: #777;
    font-size: 12px;
}

@media (max-width: 950px) {
    .stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 550px) {
    .container {
        width: 94%;
        margin-top: 24px;
    }

    .stats {
        grid-template-columns: 1fr;
    }

    .heading h1 {
        font-size: 25px;
    }

    .navbar {
        padding: 17px 4%;
    }

    .nav-link {
        margin-left: 0;
        margin-right: 12px;
        display: inline-block;
        margin-top: 10px;
    }

    .stat-card {
        padding: 19px;
    }
}
</style>
</head>

<body>

<nav class="navbar">
    <div class="logo">Vehicle<span>Rental</span></div>

    <div>
        <a class="nav-link" href="index.php">Home</a>
        <a class="nav-link" href="vehicles.php">Vehicles</a>
        <a class="nav-link" href="booking_history.php">My Bookings</a>
    </div>
</nav>

<main class="container">

    <section class="heading">
        <div>
            <h1>Admin Dashboard</h1>
            <p>Monitor bookings, vehicles, customers and completed rental revenue.</p>
        </div>

        <div class="admin-label">ADMIN PANEL</div>
    </section>

    <section class="stats">

        <div class="stat-card">
            <p>Total Bookings</p>
            <h2><?php echo number_format($bookingCount); ?></h2>
            <small>All booking records</small>
        </div>

        <div class="stat-card">
            <p>Total Vehicles</p>
            <h2><?php echo number_format($vehicleCount); ?></h2>
            <small>Registered vehicles</small>
        </div>

        <div class="stat-card">
            <p>Total Users</p>
            <h2><?php echo number_format($userCount); ?></h2>
            <small>Registered customers</small>
        </div>

        <div class="stat-card">
            <p>Completed Revenue</p>
            <h2>₹<?php echo number_format($totalRevenue, 2); ?></h2>
            <small>Sum of completed booking fares</small>
        </div>

    </section>

    <section class="panel">

        <div class="panel-header">
            <h2>All Bookings</h2>

            <input
                type="search"
                id="bookingSearch"
                class="search"
                placeholder="Search ID, customer, vehicle or status..."
                aria-label="Search bookings"
            >
        </div>

        <div class="table-wrap">
            <table id="bookingsTable">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Booking Date</th>
                        <th>Start KM</th>
                        <th>End KM</th>
                        <th>Total KM</th>
                        <th>Total Fare</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (mysqli_num_rows($result) > 0): ?>

                    <?php while ($row = mysqli_fetch_assoc($result)): ?>

                        <?php
                        $status = strtolower(trim($row['status'] ?? ''));
                        $statusClass = 'status';

                        if ($status === 'booked') {
                            $statusClass .= ' status-booked';
                        } elseif ($status === 'completed') {
                            $statusClass .= ' status-completed';
                        } elseif ($status === 'cancelled' || $status === 'cancel') {
                            $statusClass .= ' status-cancelled';
                        } elseif ($status === 'pending payment') {
                            $statusClass .= ' status-pending-payment';
                        }
                        ?>

                        <tr>
                            <td class="booking-id">
                                #<?php echo (int) $row['id']; ?>
                            </td>

                            <td><?php echo e($row['user_name']); ?></td>

                            <td><?php echo e($row['vehicle_name']); ?></td>

                            <td><?php echo e($row['booking_date']); ?></td>

                            <td><?php echo number_format((float) $row['start_km'], 2); ?></td>

                            <td><?php echo number_format((float) $row['end_km'], 2); ?></td>

                            <td><?php echo number_format((float) $row['total_km'], 2); ?> km</td>

                            <td class="fare">
                                ₹<?php echo number_format((float) $row['total_fare'], 2); ?>
                            </td>

                            <td>
                                <span class="<?php echo e($statusClass); ?>">
                                    <?php echo e(ucwords($status)); ?>
                                </span>
                            </td>

                            <td>
                                <?php if ($status === 'booked'): ?>
                                    <a
                                        class="cancel-btn"
                                        href="cancel_booking.php?id=<?php echo (int) $row['id']; ?>"
                                        onclick="return confirm('Are you sure you want to cancel booking #<?php echo (int) $row['id']; ?>?');"
                                    >Cancel</a>
                                <?php else: ?>
                                    <span class="no-action">No action</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="10" class="empty">No bookings found.</td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>
        </div>

        <div id="noResults" class="empty" style="display:none;">
            No matching bookings found.
        </div>

    </section>

    <a href="index.php" class="back-btn">← Back to Home</a>

</main>

<footer>
    © <?php echo date('Y'); ?> VehicleRental. All rights reserved.
</footer>

<script>
const searchInput = document.getElementById('bookingSearch');
const tableRows = document.querySelectorAll('#bookingsTable tbody tr');
const noResults = document.getElementById('noResults');

searchInput.addEventListener('input', function () {
    const query = this.value.toLowerCase().trim();
    let visibleCount = 0;

    tableRows.forEach(function (row) {
        const matches = row.textContent.toLowerCase().includes(query);
        row.style.display = matches ? '' : 'none';

        if (matches) {
            visibleCount++;
        }
    });

    noResults.style.display = visibleCount === 0 ? 'block' : 'none';
});
</script>

</body>
</html>
