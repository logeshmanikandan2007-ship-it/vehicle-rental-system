
<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['name'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Please login first"
    ]);
    exit;
}

$booking_id = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
$latitude = filter_input(INPUT_POST, 'latitude', FILTER_VALIDATE_FLOAT);
$longitude = filter_input(INPUT_POST, 'longitude', FILTER_VALIDATE_FLOAT);

if (
    !$booking_id ||
    $latitude === false || $latitude === null ||
    $longitude === false || $longitude === null ||
    $latitude < -90 || $latitude > 90 ||
    $longitude < -180 || $longitude > 180
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid location details"
    ]);
    exit;
}

// Check that this booking belongs to the logged-in customer
$stmt = mysqli_prepare(
    $conn,
    "SELECT id FROM bookings
     WHERE id = ? AND user_name = ?
     AND status = 'Booked'"
);

mysqli_stmt_bind_param(
    $stmt,
    "is",
    $booking_id,
    $_SESSION['name']
);

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) !== 1) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Active booking not found"
    ]);
    exit;
}

mysqli_stmt_close($stmt);

// Save or update the latest GPS coordinates
$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO vehicle_locations
        (booking_id, latitude, longitude)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE
        latitude = VALUES(latitude),
        longitude = VALUES(longitude),
        updated_at = CURRENT_TIMESTAMP"
);

mysqli_stmt_bind_param(
    $stmt,
    "idd",
    $booking_id,
    $latitude,
    $longitude
);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "success" => true,
        "message" => "Location updated successfully"
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Unable to save location"
    ]);
}

mysqli_stmt_close($stmt);
?>
