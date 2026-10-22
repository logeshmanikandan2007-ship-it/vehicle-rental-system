
<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['name'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Share Vehicle Location</title>
<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #101010;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}
.card {
    width: 90%;
    max-width: 430px;
    padding: 28px;
    background: #1d1d1d;
    border-radius: 18px;
    box-sizing: border-box;
    text-align: center;
    border: 1px solid #333;
}
h1 { color: #ff3434; }
input, button {
    width: 100%;
    box-sizing: border-box;
    padding: 13px;
    margin-top: 12px;
    border-radius: 8px;
    font-size: 16px;
}
input {
    background: #111;
    color: white;
    border: 1px solid #444;
}
button {
    border: none;
    cursor: pointer;
    font-weight: bold;
}
#start { background: #ff3434; color: white; }
#stop { background: #444; color: white; }
#status { color: #ccc; line-height: 1.5; }
</style>
</head>
<body>
<div class="card">
    <h1>📍 Vehicle GPS</h1>
    <p>Enter your active booking ID and allow location access.</p>

    <input type="number" id="booking_id"
           placeholder="Enter Booking ID" min="1">

    <button id="start" onclick="startTracking()">
        Start Location Sharing
    </button>

    <button id="stop" onclick="stopTracking()" disabled>
        Stop Sharing
    </button>

    <p id="status">Location sharing is stopped.</p>
</div>

<script>
let watchId = null;
let sending = false;

function startTracking() {
    const bookingId = document.getElementById("booking_id").value;
    const status = document.getElementById("status");

    if (!bookingId || Number(bookingId) < 1) {
        status.textContent = "Please enter a valid Booking ID.";
        return;
    }

    if (!navigator.geolocation) {
        status.textContent = "This browser does not support GPS.";
        return;
    }

    if (!window.isSecureContext) {
        status.textContent =
            "GPS requires HTTPS or localhost. Open the secure website.";
        return;
    }

    if (watchId !== null) return;

    status.textContent = "Waiting for GPS permission...";

    watchId = navigator.geolocation.watchPosition(
        async function(position) {
            if (sending) return;
            sending = true;

            const data = new URLSearchParams({
                booking_id: bookingId,
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            });

            try {
                const response = await fetch("update_location.php", {
                    method: "POST",
                    headers: {
                        "Content-Type":
                            "application/x-www-form-urlencoded"
                    },
                    body: data
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    status.textContent =
                        result.message || "Unable to update location.";
                    stopTracking();
                    return;
                }

                status.textContent =
                    "GPS sharing is active 📍\n" +
                    "Latitude: " + position.coords.latitude.toFixed(6) +
                    "\nLongitude: " + position.coords.longitude.toFixed(6) +
                    "\nLast updated: " + new Date().toLocaleTimeString();
            } catch (error) {
                status.textContent =
                    "Could not contact website. Check your connection.";
            } finally {
                sending = false;
            }
        },
        function(error) {
            status.textContent =
                "GPS error: " + error.message +
                ". Check location permission.";
            stopTracking();
        },
        {
            enableHighAccuracy: true,
            maximumAge: 5000,
            timeout: 15000
        }
    );

    document.getElementById("start").disabled = true;
    document.getElementById("stop").disabled = false;
}

function stopTracking() {
    if (watchId !== null) {
        navigator.geolocation.clearWatch(watchId);
        watchId = null;
    }

    document.getElementById("start").disabled = false;
    document.getElementById("stop").disabled = false;
    document.getElementById("status").textContent =
        "Location sharing stopped.";
}
</script>
</body>
</html>
