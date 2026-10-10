<?php

include "config.php";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $full_name = trim($_POST["full_name"] ?? "");

    if ($username === "" || $password === "" || $full_name === "") {

        $error = "Please fill in all fields.";

    } elseif (strlen($password) < 6) {

        $error = "Password must be at least 6 characters.";

    } else {

        $check = $conn->prepare(
            "SELECT id FROM users WHERE username = ?"
        );

        $check->bind_param("s", $username);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $error = "Username already exists.";

        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare("
                INSERT INTO users
                (username, password, full_name)
                VALUES (?, ?, ?)
            ");

            $stmt->bind_param(
                "sss",
                $username,
                $hashedPassword,
                $full_name
            );

            if ($stmt->execute()) {

                $message = "Account created successfully! You can now log in.";

            } else {

                $error = "Database error: " . $stmt->error;

            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Create Account | Employee System</title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family:
                "Segoe UI",
                Arial,
                Helvetica,
                sans-serif;

            min-height: 100vh;

            background:
                linear-gradient(
                    135deg,
                    #071f4d 0%,
                    #0b3d91 45%,
                    #2563eb 100%
                );

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 30px;

            color: #0f172a;
        }

        /* Background Decoration */

        body::before {
            content: "";
            position: fixed;

            width: 420px;
            height: 420px;

            border-radius: 50%;

            background: rgba(255,255,255,0.06);

            top: -180px;
            right: -120px;
        }

        body::after {
            content: "";
            position: fixed;

            width: 350px;
            height: 350px;

            border-radius: 50%;

            background: rgba(255,255,255,0.05);

            bottom: -150px;
            left: -120px;
        }

        /* Main Container */

        .register-container {
            width: 100%;
            max-width: 480px;

            position: relative;
            z-index: 2;
        }

        /* Card */

        .register-card {
            background: #ffffff;

            border-radius: 22px;

            padding: 42px 42px 35px;

            box-shadow:
                0 25px 60px rgba(0, 0, 0, 0.25);

            border: 1px solid rgba(255,255,255,0.5);
        }

        /* Logo */

        .register-logo {
            text-align: center;

            font-size: 25px;
            font-weight: 800;

            letter-spacing: 1.5px;

            color: #0b3d91;

            margin-bottom: 8px;
        }

        .register-logo span {
            color: #2563eb;
        }

        .logo-icon {
            width: 58px;
            height: 58px;

            margin: 0 auto 16px;

            border-radius: 16px;

            display: flex;
            align-items: center;
            justify-content: center;

            background:
                linear-gradient(
                    135deg,
                    #0b3d91,
                    #2563eb
                );

            color: white;

            font-size: 25px;

            box-shadow:
                0 8px 20px rgba(37, 99, 235, 0.25);
        }

        /* Heading */

        .register-card h1 {
            text-align: center;

            font-size: 28px;

            font-weight: 800;

            color: #0f172a;

            margin-top: 20px;

            margin-bottom: 7px;
        }

        .register-subtitle {
            text-align: center;

            color: #64748b;

            font-size: 14px;

            margin-bottom: 28px;
        }

        /* Alerts */

        .alert,
        .register-error {
            padding: 13px 15px;

            border-radius: 10px;

            font-size: 13px;

            margin-bottom: 20px;

            display: flex;

            align-items: center;

            gap: 10px;
        }

        .alert {
            background: #ecfdf5;

            color: #047857;

            border: 1px solid #a7f3d0;
        }

        .register-error {
            background: #fef2f2;

            color: #dc2626;

            border: 1px solid #fecaca;
        }

        /* Form */

        .register-group {
            margin-bottom: 18px;
        }

        .register-group label {
            display: block;

            font-size: 13px;

            font-weight: 700;

            color: #334155;

            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper > i {
            position: absolute;

            left: 15px;

            top: 50%;

            transform: translateY(-50%);

            color: #64748b;

            font-size: 15px;

            pointer-events: none;
        }

        .register-group input {
            width: 100%;

            height: 50px;

            border: 1px solid #dbe3ef;

            border-radius: 11px;

            background: #f8fafc;

            padding: 0 45px 0 44px;

            font-size: 14px;

            color: #0f172a;

            outline: none;

            transition: all 0.2s ease;
        }

        .register-group input::placeholder {
            color: #94a3b8;
        }

        .register-group input:hover {
            border-color: #b8c7dc;
        }

        .register-group input:focus {
            background: #ffffff;

            border-color: #2563eb;

            box-shadow:
                0 0 0 4px rgba(37, 99, 235, 0.10);
        }

        .register-group input:focus + .input-focus-icon {
            color: #2563eb;
        }

        /* Password Toggle */

        .password-toggle {
            position: absolute;

            right: 15px;

            top: 50%;

            transform: translateY(-50%);

            border: none;

            background: transparent;

            color: #64748b;

            cursor: pointer;

            font-size: 15px;

            padding: 4px;
        }

        .password-toggle:hover {
            color: #2563eb;
        }

        /* Password Note */

        .password-note {
            margin-top: 7px;

            font-size: 11px;

            color: #94a3b8;
        }

        /* Button */

        .register-button {
            width: 100%;

            height: 51px;

            border: none;

            border-radius: 11px;

            margin-top: 8px;

            background:
                linear-gradient(
                    135deg,
                    #0b3d91,
                    #2563eb
                );

            color: white;

            font-size: 14px;

            font-weight: 700;

            letter-spacing: 0.2px;

            cursor: pointer;

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 9px;

            box-shadow:
                0 8px 18px rgba(37, 99, 235, 0.22);

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }

        .register-button:hover {
            transform: translateY(-1px);

            box-shadow:
                0 12px 25px rgba(37, 99, 235, 0.30);
        }

        .register-button:active {
            transform: translateY(0);
        }

        /* Divider */

        .divider {
            display: flex;

            align-items: center;

            gap: 12px;

            margin: 25px 0 20px;

            color: #94a3b8;

            font-size: 11px;
        }

        .divider::before,
        .divider::after {
            content: "";

            height: 1px;

            flex: 1;

            background: #e2e8f0;
        }

        /* Login Link */

        .register-footer {
            text-align: center;

            font-size: 13px;

            color: #64748b;
        }

        .register-footer a {
            color: #2563eb;

            font-weight: 700;

            text-decoration: none;

            margin-left: 4px;
        }

        .register-footer a:hover {
            color: #0b3d91;

            text-decoration: underline;
        }

        /* Security Notice */

        .security-notice {
            margin-top: 22px;

            padding: 11px 12px;

            border-radius: 9px;

            background: #f8fafc;

            border: 1px solid #edf2f7;

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 7px;

            color: #64748b;

            font-size: 11px;
        }

        .security-notice i {
            color: #2563eb;
        }

        /* Mobile */

        @media (max-width: 600px) {

            body {
                padding: 18px;
            }

            .register-card {
                padding: 32px 24px 28px;

                border-radius: 18px;
            }

            .register-card h1 {
                font-size: 25px;
            }

            .register-logo {
                font-size: 22px;
            }

            .logo-icon {
                width: 52px;
                height: 52px;
            }
        }

    </style>

</head>

<body>

<div class="register-container">

    <div class="register-card">

        <!-- Logo -->

        <div class="logo-icon">
            <i class="fa-solid fa-user-plus"></i>
        </div>

        <div class="register-logo">
            EMPLOYEE <span>SYSTEM</span>
        </div>

        <h1>Create Account</h1>

        <p class="register-subtitle">
            Register a new administrator account
        </p>


        <!-- Success Message -->

        <?php if ($message): ?>

            <div class="alert">
                <i class="fa-solid fa-circle-check"></i>

                <span>
                    <?= htmlspecialchars($message) ?>
                </span>
            </div>

        <?php endif; ?>


        <!-- Error Message -->

        <?php if ($error): ?>

            <div class="register-error">
                <i class="fa-solid fa-circle-exclamation"></i>

                <span>
                    <?= htmlspecialchars($error) ?>
                </span>
            </div>

        <?php endif; ?>


        <!-- Registration Form -->

        <form method="POST">

            <!-- Full Name -->

            <div class="register-group">

                <label for="full_name">
                    Full Name
                </label>

                <div class="input-wrapper">

                    <i class="fa-solid fa-user"></i>

                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        placeholder="System Administrator"
                        value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                        autocomplete="name"
                        required
                    >

                </div>

            </div>


            <!-- Username -->

            <div class="register-group">

                <label for="username">
                    Username
                </label>

                <div class="input-wrapper">

                    <i class="fa-solid fa-user-tag"></i>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter username"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        autocomplete="username"
                        required
                    >

                </div>

            </div>


            <!-- Password -->

            <div class="register-group">

                <label for="password">
                    Password
                </label>

                <div class="input-wrapper">

                    <i class="fa-solid fa-lock"></i>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter password"
                        autocomplete="new-password"
                        required
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        onclick="togglePassword()"
                        aria-label="Show password"
                    >
                        <i
                            class="fa-solid fa-eye"
                            id="passwordIcon"
                        ></i>
                    </button>

                </div>

                <div class="password-note">
                    <i class="fa-solid fa-circle-info"></i>
                    Password must contain at least 6 characters.
                </div>

            </div>


            <!-- Submit -->

            <button
                type="submit"
                class="register-button"
            >

                <i class="fa-solid fa-user-plus"></i>

                Create Administrator Account

            </button>

        </form>


        <!-- Divider -->

        <div class="divider">
            ACCOUNT ACCESS
        </div>


        <!-- Login -->

        <div class="register-footer">

            Already have an account?

            <a href="login.php">
                Back to Login
            </a>

        </div>


        <!-- Security -->

        <div class="security-notice">

            <i class="fa-solid fa-shield-halved"></i>

            Your password is securely encrypted.

        </div>

    </div>

</div>


<script>

function togglePassword() {

    const password =
        document.getElementById("password");

    const icon =
        document.getElementById("passwordIcon");

    if (password.type === "password") {

        password.type = "text";

        icon.classList.remove("fa-eye");

        icon.classList.add("fa-eye-slash");

    } else {

        password.type = "password";

        icon.classList.remove("fa-eye-slash");

        icon.classList.add("fa-eye");
    }
}

</script>

</body>

</html>


