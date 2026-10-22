
<?php
session_start();
require_once 'db.php';

$error = '';

if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header('Location: admin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    /*
      Demo credentials.
      Change these before deploying the project online.
    */
    $adminUsername = 'admin';
    $adminPassword = 'Admin@12345';

    if (
        hash_equals($adminUsername, $username) &&
        hash_equals($adminPassword, $password)
    ) {
        session_regenerate_id(true);
        $_SESSION['is_admin'] = true;
        $_SESSION['admin_username'] = $adminUsername;

        header('Location: admin.php');
        exit();
    } else {
        $error = 'Invalid admin username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VehicleRental | Admin Login</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:Arial,sans-serif}
body{background:#101010;color:#fff;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.card{width:100%;max-width:420px;background:#1b1b1b;border:1px solid #333;border-radius:16px;padding:32px;box-shadow:0 15px 40px #0008}
.logo{text-align:center;font-size:27px;font-weight:bold;margin-bottom:8px}
.logo span{color:#e50914}
.subtitle{text-align:center;color:#aaa;font-size:14px;margin-bottom:28px}
h1{font-size:22px;margin-bottom:22px;text-align:center}
label{display:block;margin:16px 0 8px;color:#ddd;font-size:14px}
input{width:100%;padding:13px;background:#252525;color:#fff;border:1px solid #444;border-radius:8px;font-size:15px}
input:focus{outline:none;border-color:#e50914}
button{width:100%;padding:14px;margin-top:22px;background:#e50914;color:#fff;border:0;border-radius:8px;font-size:16px;font-weight:bold;cursor:pointer}
button:hover{background:#bd000a}
.error{background:#431b1b;border:1px solid #773030;color:#ffb6b6;border-radius:8px;padding:12px;margin-bottom:15px;font-size:14px}
.back{display:block;text-align:center;margin-top:22px;color:#ff555d;text-decoration:none;font-size:14px}
.demo{margin-top:20px;background:#252525;border-radius:8px;padding:12px;color:#bbb;font-size:13px;line-height:1.8}
.demo strong{color:#fff}
</style>
</head>
<body>
<div class="card">
    <div class="logo">Vehicle<span>Rental</span></div>
    <p class="subtitle">Secure Administration Portal</p>
    <h1>Admin Login</h1>

    <?php if ($error !== ''): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="username">Admin Username</label>
        <input id="username" name="username" type="text"
               autocomplete="username" required>

        <label for="password">Admin Password</label>
        <input id="password" name="password" type="password"
               autocomplete="current-password" required>

        <button type="submit">Login to Dashboard</button>
    </form>

    <div class="demo">
        <strong>Demo username:</strong> admin<br>
        <strong>Demo password:</strong> Admin@12345
    </div>

    <a class="back" href="index.php">← Back to Home</a>
</div>
</body>
</html>
