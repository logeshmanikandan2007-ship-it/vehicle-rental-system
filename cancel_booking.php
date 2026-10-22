<?php
include 'db.php';

$id = $_GET['id'];

$sql = "DELETE FROM bookings WHERE id=$id";

if(mysqli_query($conn,$sql))
{
    echo "<script>alert('Booking Cancelled Successfully'); window.location='booking_history.php';</script>";
}
else
{
    echo "Error";
}
?>