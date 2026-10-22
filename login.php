<?php
session_start();
include 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {

        $error = "Please enter your email and password.";

    } else {

        $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) == 1) {

            $user = mysqli_fetch_assoc($result);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];

            header("Location: index.php");
            exit();

        } else {

            $error = "Invalid Email or Password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | VehicleRental</title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:Arial, Helvetica, sans-serif;
        }

        body{
            min-height:100vh;
            background:
            linear-gradient(rgba(7,21,47,0.90),rgba(7,21,47,0.96)),
            url('images/swift.jpg');
            background-size:cover;
            background-position:center;

            display:flex;
            align-items:center;
            justify-content:center;

            padding:30px;
        }

        /* LOGIN CONTAINER */

        .login-container{
            width:100%;
            max-width:950px;

            display:grid;
            grid-template-columns:1fr 1fr;

            background:white;

            border-radius:24px;

            overflow:hidden;

            box-shadow:0 25px 70px rgba(0,0,0,0.35);
        }


        /* LEFT SIDE */

        .login-left{
            background:
            linear-gradient(rgba(7,21,47,0.80),rgba(7,21,47,0.90)),
            url('images/swift.jpg');

            background-size:cover;
            background-position:center;

            padding:55px 45px;

            color:white;

            display:flex;
            flex-direction:column;
            justify-content:center;
        }

        .logo{
            font-size:27px;
            font-weight:bold;
            margin-bottom:55px;
        }

        .logo span{
            color:#20df8f;
        }

        .login-left h1{
            font-size:42px;
            line-height:1.15;
            margin-bottom:20px;
        }

        .login-left h1 span{
            color:#20df8f;
        }

        .login-left p{
            color:#d3dbea;
            line-height:1.7;
            font-size:16px;
            max-width:380px;
        }

        .benefits{
            margin-top:35px;
        }

        .benefit{
            display:flex;
            align-items:center;
            gap:12px;
            margin-bottom:15px;
            color:#e3e8f0;
        }

        .benefit-icon{
            width:34px;
            height:34px;

            display:flex;
            align-items:center;
            justify-content:center;

            background:#20df8f;
            color:#07152f;

            border-radius:50%;
        }


        /* RIGHT SIDE */

        .login-right{
            padding:55px 45px;

            display:flex;
            flex-direction:column;
            justify-content:center;
        }

        .login-title{
            margin-bottom:30px;
        }

        .login-title h2{
            color:#07152f;
            font-size:32px;
            margin-bottom:8px;
        }

        .login-title p{
            color:#788398;
            font-size:14px;
        }


        /* ERROR */

        .error{
            background:#fff0f0;
            color:#d93636;

            padding:13px 15px;

            border-radius:8px;

            margin-bottom:20px;

            font-size:14px;

            border-left:4px solid #ff5555;
        }


        /* FORM */

        .form-group{
            margin-bottom:20px;
        }

        .form-group label{
            display:block;

            color:#26344d;

            font-size:14px;

            font-weight:bold;

            margin-bottom:8px;
        }

        .input-box{
            position:relative;
        }

        .input-box span{
            position:absolute;

            left:15px;
            top:50%;

            transform:translateY(-50%);

            font-size:18px;
        }

        .input-box input{
            width:100%;

            padding:14px 15px 14px 45px;

            border:1px solid #dce2eb;

            border-radius:9px;

            outline:none;

            font-size:15px;

            transition:0.3s;
        }

        .input-box input:focus{
            border-color:#20df8f;

            box-shadow:0 0 0 3px rgba(32,223,143,0.12);
        }


        /* LOGIN BUTTON */

        .login-button{
            width:100%;

            border:none;

            background:#20df8f;

            color:#07152f;

            padding:15px;

            border-radius:9px;

            font-size:16px;

            font-weight:bold;

            cursor:pointer;

            transition:0.3s;

            margin-top:5px;
        }

        .login-button:hover{
            background:#16c97e;

            transform:translateY(-2px);

            box-shadow:0 8px 20px rgba(32,223,143,0.25);
        }


        /* REGISTER */

        .register-text{
            text-align:center;

            margin-top:25px;

            color:#7b8597;

            font-size:14px;
        }

        .register-text a{
            color:#0b9c68;

            text-decoration:none;

            font-weight:bold;
        }

        .register-text a:hover{
            text-decoration:underline;
        }


        /* HOME */

        .home-link{
            text-align:center;

            margin-top:18px;
        }

        .home-link a{
            color:#687386;

            text-decoration:none;

            font-size:14px;
        }

        .home-link a:hover{
            color:#20b878;
        }


        /* MOBILE */

        @media(max-width:800px){

            .login-container{
                grid-template-columns:1fr;
                max-width:520px;
            }

            .login-left{
                padding:40px 35px;
            }

            .login-left h1{
                font-size:34px;
            }

            .logo{
                margin-bottom:30px;
            }

            .benefits{
                display:none;
            }

            .login-right{
                padding:40px 30px;
            }
        }

    </style>

</head>


<body>


<div class="login-container">


    <!-- LEFT -->

    <div class="login-left">

        <div class="logo">
            🚗 Vehicle<span>Rental</span>
        </div>

        <h1>
            Welcome<br>
            <span>Back!</span>
        </h1>

        <p>
            Login to your VehicleRental account and
            continue your journey with a reliable vehicle.
        </p>


        <div class="benefits">

            <div class="benefit">

                <div class="benefit-icon">✓</div>

                Easy Online Booking

            </div>


            <div class="benefit">

                <div class="benefit-icon">✓</div>

                Affordable Rental Prices

            </div>


            <div class="benefit">

                <div class="benefit-icon">✓</div>

                Reliable Vehicles

            </div>

        </div>

    </div>


    <!-- RIGHT -->

    <div class="login-right">


        <div class="login-title">

            <h2>Login to Account</h2>

            <p>
                Enter your details to continue
            </p>

        </div>


        <?php if(!empty($error)): ?>

            <div class="error">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <form method="POST">


            <!-- EMAIL -->

            <div class="form-group">

                <label>Email Address</label>

                <div class="input-box">

                    <span>📧</span>

                    <input
                        type="email"
                        name="email"
                        placeholder="Enter your email"
                        required
                    >

                </div>

            </div>


            <!-- PASSWORD -->

            <div class="form-group">

                <label>Password</label>

                <div class="input-box">

                    <span>🔒</span>

                    <input
                        type="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >

                </div>

            </div>


            <!-- BUTTON -->

            <button
                type="submit"
                class="login-button"
            >
                🔐 Login
            </button>


        </form>


        <div class="register-text">

            Don't have an account?

            <a href="register.php">
                Create Account
            </a>

        </div>


        <div class="home-link">

            <a href="index.php">
                ← Back to Home
            </a>

        </div>


    </div>

</div>


</body>
</html>