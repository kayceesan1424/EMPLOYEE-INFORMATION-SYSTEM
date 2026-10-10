<?php
session_start();
include "config.php";

$error = "";

// If already logged in, go directly to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Please enter your username and password.";
    } else {

        // Find user by username
        $stmt = $conn->prepare("
            SELECT id, username, password
            FROM users
            WHERE username = ?
            LIMIT 1
        ");

        if ($stmt) {

            $stmt->bind_param("s", $username);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {

                $user = $result->fetch_assoc();

                /*
                 * Passwords should be stored using password_hash().
                 */
                if (password_verify($password, $user['password'])) {

                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];

                    header("Location: index.php");
                    exit;

                } else {
                    $error = "Incorrect username or password.";
                }

            } else {
                $error = "Incorrect username or password.";
            }

            $stmt->close();

        } else {
            $error = "Unable to process login. Please check your database.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Employee Information System</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", Arial, sans-serif;
            min-height: 100vh;

            background:
                radial-gradient(circle at 10% 20%,
                rgba(37, 99, 235, 0.15),
                transparent 25%),

                radial-gradient(circle at 90% 80%,
                rgba(96, 165, 250, 0.18),
                transparent 25%),

                #eef5ff;

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 30px;
        }

        /* Main Login Container */

        .login-container {
            width: 100%;
            max-width: 1150px;
            min-height: 650px;

            display: grid;
            grid-template-columns: 1fr 1fr;

            background: #ffffff;

            border-radius: 22px;

            overflow: hidden;

            box-shadow:
                0 25px 60px rgba(15, 23, 42, 0.15);

            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        /* =========================
           LEFT SIDE
        ========================== */

        .left-panel {
            position: relative;

            padding: 55px 55px 40px;

            color: white;

            background:
                linear-gradient(
                    145deg,
                    #0b3d91 0%,
                    #1559c7 50%,
                    #2563eb 100%
                );

            overflow: hidden;
        }

        /* Decorative circles */

        .left-panel::before {
            content: "";

            position: absolute;

            width: 350px;
            height: 350px;

            border-radius: 50%;

            background: rgba(255, 255, 255, 0.07);

            top: -150px;
            right: -100px;
        }

        .left-panel::after {
            content: "";

            position: absolute;

            width: 300px;
            height: 300px;

            border-radius: 50%;

            background: rgba(96, 165, 250, 0.15);

            bottom: -170px;
            left: -100px;
        }

        .brand {
            position: relative;
            z-index: 2;

            display: flex;
            align-items: center;

            gap: 18px;

            margin-bottom: 35px;
        }

        .brand-icon {
            width: 65px;
            height: 65px;

            background: rgba(255, 255, 255, 0.15);

            border: 1px solid rgba(255, 255, 255, 0.25);

            border-radius: 17px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 32px;

            box-shadow:
                0 10px 25px rgba(0, 0, 0, 0.12);
        }

        .brand-text h1 {
            font-size: 25px;
            line-height: 1.2;

            font-weight: 700;
        }

        .brand-text span {
            color: #93c5fd;
        }

        .brand-text p {
            font-size: 13px;
            margin-top: 5px;

            color: #dbeafe;
        }

        .left-content {
            position: relative;
            z-index: 2;
        }

        .left-content h2 {
            font-size: 35px;
            line-height: 1.2;

            margin-bottom: 18px;

            font-weight: 700;
        }

        .left-content > p {
            color: #dbeafe;

            font-size: 16px;

            line-height: 1.7;

            max-width: 430px;

            margin-bottom: 38px;
        }

        /* Illustration */

        .illustration {
            width: 100%;
            max-width: 430px;

            margin: 0 auto 35px;

            position: relative;

            z-index: 2;
        }

        .computer {
            height: 190px;

            background: #ffffff;

            border: 8px solid #bfdbfe;

            border-radius: 16px;

            box-shadow:
                0 15px 35px rgba(0, 0, 0, 0.20);

            padding: 20px;

            position: relative;
        }

        .screen-header {
            height: 14px;

            width: 75%;

            background: #bfdbfe;

            border-radius: 10px;

            margin-bottom: 22px;
        }

        .screen-row {
            display: flex;

            align-items: center;

            gap: 15px;

            margin-bottom: 15px;
        }

        .screen-avatar {
            width: 45px;
            height: 45px;

            border-radius: 50%;

            background: #2563eb;

            display: flex;
            align-items: center;
            justify-content: center;

            color: white;

            font-size: 21px;
        }

        .screen-lines {
            flex: 1;
        }

        .line {
            height: 9px;

            background: #dbeafe;

            border-radius: 10px;

            margin-bottom: 8px;
        }

        .line.short {
            width: 55%;
        }

        .line.medium {
            width: 75%;
        }

        .computer-base {
            width: 120px;
            height: 12px;

            background: #bfdbfe;

            margin: 10px auto 0;

            border-radius: 0 0 10px 10px;
        }

        /* Features */

        .features {
            position: relative;
            z-index: 2;

            display: grid;
            grid-template-columns: repeat(3, 1fr);

            gap: 12px;

            margin-top: 20px;
        }

        .feature {
            text-align: center;

            padding: 14px 8px;

            border-right: 1px solid rgba(255,255,255,0.15);
        }

        .feature:last-child {
            border-right: none;
        }

        .feature-icon {
            font-size: 25px;

            margin-bottom: 7px;
        }

        .feature span {
            display: block;

            font-size: 12px;

            color: #dbeafe;

            line-height: 1.4;
        }

        /* =========================
           RIGHT SIDE
        ========================== */

        .right-panel {
            padding: 60px 75px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #ffffff;
        }

        .login-box {
            width: 100%;
            max-width: 430px;
        }

        .user-icon {
            width: 76px;
            height: 76px;

            margin: 0 auto 22px;

            border-radius: 50%;

            background: #eff6ff;

            border: 1px solid #dbeafe;

            display: flex;

            align-items: center;
            justify-content: center;

            color: #2563eb;

            font-size: 35px;

            box-shadow:
                0 8px 20px rgba(37, 99, 235, 0.10);
        }

        .login-title {
            text-align: center;

            color: #0b3d91;

            font-size: 36px;

            font-weight: 750;

            margin-bottom: 10px;
        }

        .login-subtitle {
            text-align: center;

            color: #64748b;

            font-size: 15px;

            margin-bottom: 38px;

            line-height: 1.5;
        }

        /* Error Message */

        .error-message {
            background: #fef2f2;

            border: 1px solid #fecaca;

            color: #b91c1c;

            padding: 13px 15px;

            border-radius: 10px;

            margin-bottom: 22px;

            font-size: 14px;

            text-align: center;
        }

        /* Form */

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;

            color: #334155;

            font-size: 14px;

            font-weight: 600;

            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;

            left: 17px;

            top: 50%;

            transform: translateY(-50%);

            font-size: 19px;

            color: #64748b;

            pointer-events: none;
        }

        .form-group input {
            width: 100%;

            height: 54px;

            padding: 0 18px 0 50px;

            border: 1px solid #dbe3ef;

            border-radius: 12px;

            background: #f8fbff;

            color: #1e293b;

            font-size: 15px;

            outline: none;

            transition: all 0.2s ease;
        }

        .form-group input::placeholder {
            color: #94a3b8;
        }

        .form-group input:focus {
            border-color: #2563eb;

            background: #ffffff;

            box-shadow:
                0 0 0 4px rgba(37, 99, 235, 0.10);
        }

        /* Sign In Button */

        .login-button {
            width: 100%;

            height: 55px;

            margin-top: 8px;

            border: none;

            border-radius: 12px;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #0b3d91
                );

            color: white;

            font-size: 16px;

            font-weight: 700;

            cursor: pointer;

            box-shadow:
                0 10px 22px rgba(37, 99, 235, 0.25);

            transition: all 0.2s ease;
        }

        .login-button:hover {
            transform: translateY(-2px);

            box-shadow:
                0 14px 28px rgba(37, 99, 235, 0.32);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .button-arrow {
            margin-left: 8px;

            font-size: 18px;
        }

        /* Footer */

        .login-footer {
            text-align: center;

            margin-top: 30px;

            color: #94a3b8;

            font-size: 13px;
        }

        .login-footer strong {
            color: #2563eb;
        }

        /* =========================
           RESPONSIVE
        ========================== */

        @media (max-width: 900px) {

            body {
                padding: 20px;
            }

            .login-container {
                grid-template-columns: 1fr;

                max-width: 600px;
            }

            .left-panel {
                display: none;
            }

            .right-panel {
                padding: 55px 35px;
            }
        }

        @media (max-width: 480px) {

            body {
                padding: 12px;
            }

            .login-container {
                border-radius: 16px;
            }

            .right-panel {
                padding: 45px 25px;
            }

            .login-title {
                font-size: 30px;
            }

            .login-subtitle {
                font-size: 14px;
            }
        }

    </style>
</head>

<body>

<div class="login-container">

    <!-- =========================
         LEFT BLUE PANEL
    ========================== -->

    <div class="left-panel">

        <div class="brand">

            <div class="brand-icon">
                👥
            </div>

            <div class="brand-text">

                <h1>
                    Employee Information
                    <span>System</span>
                </h1>

                <p>
                    Employee Management Platform
                </p>

            </div>

        </div>


        <div class="left-content">

            <h2>
                Manage Your Workforce<br>
                With Confidence.
            </h2>

            <p>
                Easily manage employee information,
                monitor employee records, and keep
                your organization's workforce organized
                in one secure system.
            </p>


            <!-- Simple CSS Illustration -->

            <div class="illustration">

                <div class="computer">

                    <div class="screen-header"></div>

                    <div class="screen-row">

                        <div class="screen-avatar">
                            👤
                        </div>

                        <div class="screen-lines">

                            <div class="line medium"></div>

                            <div class="line short"></div>

                        </div>

                    </div>

                    <div class="screen-row">

                        <div class="screen-avatar">
                            ✓
                        </div>

                        <div class="screen-lines">

                            <div class="line medium"></div>

                            <div class="line short"></div>

                        </div>

                    </div>

                </div>

                <div class="computer-base"></div>

            </div>


            <!-- Features -->

            <div class="features">

                <div class="feature">

                    <div class="feature-icon">
                        👥
                    </div>

                    <span>
                        Manage<br>
                        Employees
                    </span>

                </div>


                <div class="feature">

                    <div class="feature-icon">
                        🔒
                    </div>

                    <span>
                        Secure<br>
                        Data
                    </span>

                </div>


                <div class="feature">

                    <div class="feature-icon">
                        📊
                    </div>

                    <span>
                        Organized<br>
                        Records
                    </span>

                </div>

            </div>

        </div>

    </div>


    <!-- =========================
         RIGHT LOGIN PANEL
    ========================== -->

    <div class="right-panel">

        <div class="login-box">

            <div class="user-icon">
                👤
            </div>

            <h2 class="login-title">
                Welcome Back
            </h2>

            <p class="login-subtitle">
                Sign in to access the Employee Information System
            </p>


            <?php if ($error !== ""): ?>

                <div class="error-message">
                    ⚠ <?php echo htmlspecialchars($error); ?>
                </div>

            <?php endif; ?>


            <form method="POST" action="login.php">

                <!-- Username -->

                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            👤
                        </span>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Enter username"
                            autocomplete="username"
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                            required
                        >

                    </div>

                </div>


                <!-- Password -->

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            🔒
                        </span>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter password"
                            autocomplete="current-password"
                            required
                        >

                    </div>

                </div>


                <!-- Sign In -->

                <button
                    type="submit"
                    class="login-button"
                >

                    Sign In

                    <span class="button-arrow">
                        →
                    </span>

                </button>

            </form>


            <div class="login-footer">

                <strong>Employee Information System</strong>

                <br>

                Secure Employee Management

            </div>

        </div>

    </div>

</div>

</body>
</html>