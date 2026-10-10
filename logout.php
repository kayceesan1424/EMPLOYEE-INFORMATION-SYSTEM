<?php
session_start();

/*
    LOGOUT CONFIRMATION PAGE
    The session is destroyed ONLY after
    the user clicks "Yes, Logout".
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["confirm_logout"]) && $_POST["confirm_logout"] === "yes") {

        $_SESSION = [];

        if (ini_get("session.use_cookies")) {

            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        header("Location: login.php?logout=success");
        exit;
    }

    /* CANCEL */
    header("Location: index.php");
    exit;
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

    <title>Confirm Logout - Employee Information System</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background:
                linear-gradient(
                    135deg,
                    #eef4ff 0%,
                    #f8fbff 50%,
                    #e8f0ff 100%
                );

            color: #0f172a;
        }


        /* BACKGROUND DECORATION */

        body::before {
            content: "";
            position: fixed;

            width: 450px;
            height: 450px;

            background: #2563eb;
            opacity: 0.06;

            border-radius: 50%;

            top: -180px;
            left: -180px;
        }

        body::after {
            content: "";
            position: fixed;

            width: 400px;
            height: 400px;

            background: #1d4ed8;
            opacity: 0.05;

            border-radius: 50%;

            bottom: -180px;
            right: -150px;
        }


        /* MODAL */

        .logout-modal {

            position: relative;
            z-index: 10;

            width: 430px;
            max-width: calc(100% - 40px);

            background: #ffffff;

            border-radius: 22px;

            padding: 38px 38px 34px;

            text-align: center;

            box-shadow:
                0 25px 70px rgba(15, 23, 42, 0.16),
                0 8px 25px rgba(37, 99, 235, 0.08);

            border: 1px solid #e2e8f0;

            animation: modalAppear 0.25s ease-out;
        }


        @keyframes modalAppear {

            from {
                opacity: 0;
                transform: translateY(15px) scale(0.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }


        /* LOGOUT ICON */

        .logout-icon {

            width: 76px;
            height: 76px;

            margin: 0 auto 22px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background: #eff6ff;

            color: #2563eb;

            font-size: 34px;

            box-shadow:
                0 8px 20px rgba(37, 99, 235, 0.12);
        }


        /* TITLE */

        h1 {

            font-size: 25px;
            font-weight: 800;

            color: #0f172a;

            margin-bottom: 10px;
        }


        /* MESSAGE */

        .message {

            font-size: 15px;
            line-height: 1.7;

            color: #64748b;

            margin-bottom: 28px;
        }


        .message strong {
            color: #334155;
        }


        /* BUTTONS */

        .buttons {

            display: flex;
            gap: 12px;
        }


        .buttons button {

            flex: 1;

            height: 48px;

            border: none;
            border-radius: 11px;

            font-size: 15px;
            font-weight: 700;

            cursor: pointer;

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;
        }


        /* CANCEL */

        .cancel-btn {

            background: #f1f5f9;
            color: #334155;
        }

        .cancel-btn:hover {

            background: #e2e8f0;

            transform: translateY(-1px);
        }


        /* LOGOUT */

        .confirm-btn {

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #1d4ed8
                );

            color: #ffffff;

            box-shadow:
                0 6px 15px rgba(37, 99, 235, 0.25);
        }

        .confirm-btn:hover {

            background:
                linear-gradient(
                    135deg,
                    #1d4ed8,
                    #1e40af
                );

            transform: translateY(-1px);

            box-shadow:
                0 9px 20px rgba(37, 99, 235, 0.32);
        }


        /* FOOTER */

        .footer-text {

            margin-top: 22px;

            font-size: 12px;

            color: #94a3b8;
        }


        /* MOBILE */

        @media (max-width: 500px) {

            .logout-modal {
                padding: 30px 24px;
            }

            .buttons {
                flex-direction: column;
            }

            .buttons button {
                width: 100%;
            }
        }

    </style>

</head>


<body>


    <div class="logout-modal">


        <!-- ICON -->

        <div class="logout-icon">
            ⇥
        </div>


        <!-- TITLE -->

        <h1>
            Confirm Logout
        </h1>


        <!-- MESSAGE -->

        <p class="message">

            Are you sure you want to logout
            from the <strong>Employee Information System</strong>?

            <br>

            You will need to login again to access
            your account.

        </p>


        <!-- BUTTONS -->

        <form method="POST">

            <div class="buttons">


                <!-- CANCEL -->

                <button
                    type="button"
                    class="cancel-btn"
                    onclick="window.location.href='index.php';"
                >
                    Cancel
                </button>


                <!-- CONFIRM -->

                <button
                    type="submit"
                    name="confirm_logout"
                    value="yes"
                    class="confirm-btn"
                >
                    Yes, Logout
                </button>


            </div>

        </form>


        <div class="footer-text">

            Employee Information System

        </div>


    </div>


</body>

</html>