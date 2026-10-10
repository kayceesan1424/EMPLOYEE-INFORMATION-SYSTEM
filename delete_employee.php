<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

include "config.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");

if (!$stmt) {
    die("Delete query failed: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $id);

if (!$stmt->execute()) {
    $error = $stmt->error;
    $stmt->close();

    die("Unable to delete employee: " . htmlspecialchars($error));
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Successful</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f4f7fb;
            font-family: Arial, Helvetica, sans-serif;
            color: #0f172a;
        }

        .success-card {
            width: 460px;
            max-width: calc(100% - 40px);
            padding: 42px 38px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(15, 23, 42, 0.10);
            animation: cardIn .4s ease-out;
        }

        .success-icon {
            width: 78px;
            height: 78px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #dcfce7;
            border: 7px solid #f0fdf4;
            color: #16a34a;
            font-size: 38px;
            font-weight: bold;
        }

        .success-badge {
            display: inline-block;
            padding: 7px 14px;
            margin-bottom: 14px;
            border-radius: 20px;
            background: #ecfdf5;
            color: #15803d;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        h1 {
            margin: 0 0 12px;
            color: #0f2d67;
            font-size: 25px;
        }

        .message {
            margin: 0 auto;
            max-width: 350px;
            color: #64748b;
            font-size: 14px;
            line-height: 1.7;
        }

        .dashboard-btn {
            display: inline-block;
            margin-top: 27px;
            padding: 12px 24px;
            border-radius: 9px;
            background: #2563eb;
            color: #ffffff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            transition: .2s;
        }

        .dashboard-btn:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .redirect-text {
            margin-top: 17px;
            color: #94a3b8;
            font-size: 12px;
        }

        .progress {
            height: 4px;
            margin-top: 15px;
            overflow: hidden;
            border-radius: 10px;
            background: #e2e8f0;
        }

        .progress-bar {
            width: 100%;
            height: 100%;
            background: #2563eb;
            animation: countdown 3s linear forwards;
        }

        @keyframes countdown {
            from { width: 100%; }
            to { width: 0%; }
        }

        @keyframes cardIn {
            from {
                opacity: 0;
                transform: translateY(18px) scale(.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (max-width: 500px) {
            .success-card {
                padding: 35px 24px;
            }

            h1 {
                font-size: 22px;
            }

            .dashboard-btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="success-card">

        <div class="success-icon">✓</div>

        <div class="success-badge">
            Delete Completed
        </div>

        <h1>
            Employee Deleted Successfully
        </h1>

        <p class="message">
            The employee record has been successfully removed
            from the Employee Information System.
        </p>

        <a href="index.php?deleted=success" class="dashboard-btn">
            Continue to Dashboard
        </a>

        <div class="redirect-text">
            Returning to Dashboard automatically...
        </div>

        <div class="progress">
            <div class="progress-bar"></div>
        </div>

    </div>

    <script>
        setTimeout(function () {
            window.location.href = "index.php?deleted=success";
        }, 3000);
    </script>

</body>
</html>
