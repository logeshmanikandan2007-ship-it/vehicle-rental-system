<?php
include 'db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (
        empty($name) ||
        empty($email) ||
        empty($phone) ||
        empty($password) ||
        empty($confirm_password)
    ) {

        $error = "Please fill in all the fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {

        $error = "Please enter a valid 10-digit phone number.";

    } elseif (strlen($password) < 4) {

        $error = "Password must contain at least 4 characters.";

    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } else {

        /* Check existing email */

        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);

        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);

        $check_result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($check_result) > 0) {

            $error = "This email is already registered.";

        } else {

            /* Insert user */

            $sql = "INSERT INTO users
                    (name, email, phone, password)
                    VALUES (?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "ssss",
                $name,
                $email,
                $phone,
                $password
            );

            if (mysqli_stmt_execute($stmt)) {

                $success = "Registration successful! You can login now.";

            } else {

                $error = "Registration failed. Please try again.";
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($check_stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Register | VehicleRental</title>


<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{

    font-family:Arial, sans-serif;

    min-height:100vh;

    background:
        linear-gradient(
            rgba(3,15,35,0.90),
            rgba(3,15,35,0.94)
        ),
        url("images/swift.jpg");

    background-size:cover;

    background-position:center;

    display:flex;

    justify-content:center;

    align-items:center;

    padding:25px;
}


/* REGISTER CARD */

.register-card{

    width:100%;

    max-width:900px;

    background:white;

    border-radius:24px;

    overflow:hidden;

    display:grid;

    grid-template-columns:0.9fr 1.1fr;

    box-shadow:
        0 25px 70px rgba(0,0,0,0.40);
}


/* LEFT SIDE */

.register-left{

    background:
        linear-gradient(
            rgba(7,21,47,0.82),
            rgba(7,21,47,0.94)
        ),
        url("images/swift.jpg");

    background-size:cover;

    background-position:center;

    color:white;

    padding:50px 40px;

    display:flex;

    flex-direction:column;

    justify-content:center;
}


.logo{

    font-size:27px;

    font-weight:bold;

    margin-bottom:45px;
}


.logo span{

    color:#20df8f;
}


.register-left h1{

    font-size:40px;

    line-height:1.15;

    margin-bottom:18px;
}


.register-left h1 span{

    color:#20df8f;
}


.register-left p{

    color:#d5dcea;

    line-height:1.7;

    font-size:15px;
}


.benefits{

    margin-top:30px;
}


.benefit{

    display:flex;

    align-items:center;

    gap:12px;

    margin-bottom:15px;

    color:#e5eaf2;

    font-size:14px;
}


.check{

    width:32px;

    height:32px;

    border-radius:50%;

    background:#20df8f;

    color:#07152f;

    display:flex;

    align-items:center;

    justify-content:center;

    font-weight:bold;
}


/* RIGHT SIDE */

.register-right{

    padding:45px;

}


.register-title{

    margin-bottom:25px;
}


.register-title h2{

    color:#07152f;

    font-size:30px;

    margin-bottom:7px;
}


.register-title p{

    color:#667085;

    font-size:14px;
}


/* MESSAGE */

.error{

    background:#ffe8e8;

    color:#c62828;

    padding:12px 15px;

    border-radius:9px;

    margin-bottom:18px;

    font-size:14px;

    border-left:4px solid #ff5252;
}


.success{

    background:#e7fff4;

    color:#08784d;

    padding:12px 15px;

    border-radius:9px;

    margin-bottom:18px;

    font-size:14px;

    border-left:4px solid #20df8f;
}


/* FORM */

.form-group{

    margin-bottom:17px;
}


.form-group label{

    display:block;

    font-size:14px;

    font-weight:bold;

    color:#344054;

    margin-bottom:7px;
}


.form-group input{

    width:100%;

    padding:13px 15px;

    border:1px solid #d0d5dd;

    border-radius:9px;

    outline:none;

    font-size:14px;

    transition:0.3s;
}


.form-group input:focus{

    border-color:#20df8f;

    box-shadow:
        0 0 0 3px rgba(32,223,143,0.12);
}


/* TWO COLUMN */

.form-row{

    display:grid;

    grid-template-columns:1fr 1fr;

    gap:15px;
}


/* BUTTON */

.register-button{

    width:100%;

    padding:14px;

    border:none;

    border-radius:9px;

    background:
        linear-gradient(
            135deg,
            #20df8f,
            #16b875
        );

    color:white;

    font-size:16px;

    font-weight:bold;

    cursor:pointer;

    transition:0.3s;

    margin-top:5px;
}


.register-button:hover{

    transform:translateY(-2px);

    box-shadow:
        0 10px 25px rgba(32,217,138,0.30);
}


/* LOGIN */

.login-text{

    text-align:center;

    margin-top:20px;

    color:#667085;

    font-size:14px;
}


.login-text a{

    color:#16b875;

    font-weight:bold;

    text-decoration:none;
}


.login-text a:hover{

    text-decoration:underline;
}


/* HOME */

.home{

    text-align:center;

    margin-top:15px;
}


.home a{

    color:#667085;

    text-decoration:none;

    font-size:13px;
}


.home a:hover{

    color:#16b875;
}


/* MOBILE */

@media(max-width:800px){

    .register-card{

        grid-template-columns:1fr;

        max-width:520px;
    }

    .register-left{

        padding:35px;

    }

    .register-left h1{

        font-size:32px;

    }

    .benefits{

        display:none;

    }

    .register-right{

        padding:35px 28px;

    }
}


@media(max-width:500px){

    .form-row{

        grid-template-columns:1fr;

        gap:0;
    }

    .register-left{

        padding:30px 25px;

    }

    .register-right{

        padding:30px 22px;

    }

}

</style>

</head>


<body>


<div class="register-card">


    <!-- LEFT -->

    <div class="register-left">


        <div class="logo">

            🚗 Vehicle<span>Rental</span>

        </div>


        <h1>

            Start Your<br>

            <span>Journey Today!</span>

        </h1>


        <p>

            Create your VehicleRental account and
            discover an easier way to rent cars and bikes.

        </p>


        <div class="benefits">


            <div class="benefit">

                <div class="check">✓</div>

                Easy Online Booking

            </div>


            <div class="benefit">

                <div class="check">✓</div>

                Affordable Prices

            </div>


            <div class="benefit">

                <div class="check">✓</div>

                Reliable Vehicles

            </div>


        </div>


    </div>


    <!-- RIGHT -->

    <div class="register-right">


        <div class="register-title">

            <h2>Create Account</h2>

            <p>

                Register now and start renting your vehicle.

            </p>

        </div>


        <?php if(!empty($error)){ ?>

            <div class="error">

                ⚠️
                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php } ?>


        <?php if(!empty($success)){ ?>

            <div class="success">

                ✅
                <?php echo htmlspecialchars($success); ?>

            </div>

        <?php } ?>


        <form method="POST">


            <!-- NAME -->

            <div class="form-group">

                <label>Full Name</label>

                <input
                    type="text"
                    name="name"
                    placeholder="Enter your full name"
                    required
                >

            </div>


            <!-- EMAIL -->

            <div class="form-group">

                <label>Email Address</label>

                <input
                    type="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                >

            </div>


            <!-- PHONE -->

            <div class="form-group">

                <label>Phone Number</label>

                <input
                    type="tel"
                    name="phone"
                    placeholder="10-digit mobile number"
                    maxlength="10"
                    required
                >

            </div>


            <!-- PASSWORDS -->

            <div class="form-row">


                <div class="form-group">

                    <label>Password</label>

                    <input
                        type="password"
                        name="password"
                        placeholder="Create password"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Confirm Password</label>

                    <input
                        type="password"
                        name="confirm_password"
                        placeholder="Confirm password"
                        required
                    >

                </div>


            </div>


            <!-- BUTTON -->

            <button
                type="submit"
                class="register-button"
            >

                Create My Account →

            </button>


        </form>


        <div class="login-text">

            Already have an account?

            <a href="login.php">
                Login
            </a>

        </div>


        <div class="home">

            <a href="index.php">

                ← Back to Home

            </a>

        </div>


    </div>


</div>


</body>

</html>