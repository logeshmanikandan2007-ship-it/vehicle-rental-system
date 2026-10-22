
<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'], $_SESSION['name'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Please login first"]);
    exit;
}

$bookingId = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);

if (!$bookingId || $bookingId < 1) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid booking ID"]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT vl.latitude, vl.longitude, vl.updated_at
     FROM bookings b
     LEFT JOIN vehicle_locations vl ON vl.booking_id = b.id
     WHERE b.id = ? AND b.user_name = ?"
);

$stmt->bind_param("is", $bookingId, $_SESSION['name']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Booking not found"]);
    exit;
}

if ($row['latitude'] === null || $row['longitude'] === null) {
    echo json_encode([
        "success" => false,
        "message" => "No GPS location received yet"
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "latitude" => (float) $row['latitude'],
    "longitude" => (float) $row['longitude'],
    "updated_at" => $row['updated_at']
]);
?>
