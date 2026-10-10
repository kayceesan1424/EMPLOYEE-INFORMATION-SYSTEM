<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include "config.php";

/* CURRENT PAGE FOR NAVIGATION HIGHLIGHT */
$currentPage = basename($_SERVER["PHP_SELF"]);


/* CHECK EMPLOYEE ID */

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);


/* GET EMPLOYEE */

$stmt = $conn->prepare(
    "SELECT * FROM employees WHERE id = ?"
);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$employee = $result->fetch_assoc();

$stmt->close();


/* UPDATE EMPLOYEE */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $employee_id = trim($_POST['employee_id'] ?? '');

    $first_name = trim($_POST['first_name'] ?? '');

    $middle_name = trim($_POST['middle_name'] ?? '');

    $last_name = trim($_POST['last_name'] ?? '');

    $suffix = trim($_POST['suffix'] ?? '');

    $email = trim($_POST['email'] ?? '');

    $phone = trim($_POST['phone'] ?? '');

    $department = trim($_POST['department'] ?? '');

    $position = trim($_POST['position'] ?? '');

    $hire_date = $_POST['hire_date'] ?? '';

    $salary = (float)($_POST['salary'] ?? 0);

    $status = $_POST['status'] ?? 'Active';


    /* VALIDATE STATUS */

    if ($status !== 'Active' && $status !== 'Inactive') {
        $status = 'Active';
    }


    /* UPDATE QUERY */

    $stmt = $conn->prepare("
        UPDATE employees SET

            employee_id = ?,
            first_name = ?,
            middle_name = ?,
            last_name = ?,
            suffix = ?,
            email = ?,
            phone = ?,
            department = ?,
            position = ?,
            hire_date = ?,
            salary = ?,
            status = ?

        WHERE id = ?
    ");


    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }


    /* BIND PARAMETERS */

    $stmt->bind_param(
        "ssssssssssdsi",
        $employee_id,
        $first_name,
        $middle_name,
        $last_name,
        $suffix,
        $email,
        $phone,
        $department,
        $position,
        $hire_date,
        $salary,
        $status,
        $id
    );


    /* EXECUTE UPDATE */

    if ($stmt->execute()) {

        $stmt->close();

        // Show a professional confirmation screen after a successful update.
        echo <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Successful</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: #f4f7fb;
            font-family: Arial, Helvetica, sans-serif;
            color: #0f172a;
        }
        .success-card {
            width: 100%;
            max-width: 460px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 22px;
            padding: 42px 35px 35px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(15, 23, 42, .12);
            animation: cardIn .35s ease-out;
        }
        .success-icon {
            width: 76px;
            height: 76px;
            margin: 0 auto 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #dcfce7;
            border: 7px solid #f0fdf4;
            color: #16a34a;
            font-size: 38px;
            font-weight: 800;
        }
        .success-card h1 {
            margin: 0 0 10px;
            color: #0f2d67;
            font-size: 25px;
        }
        .success-card p {
            margin: 0 auto 25px;
            color: #64748b;
            font-size: 15px;
            line-height: 1.6;
        }
        .success-card .status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 15px;
            margin-bottom: 25px;
            border-radius: 999px;
            background: #f0fdf4;
            color: #15803d;
            font-size: 13px;
            font-weight: 700;
        }
        .success-card .status span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22c55e;
        }
        .dashboard-btn {
            display: block;
            width: 100%;
            padding: 13px 20px;
            border-radius: 10px;
            background: #2563eb;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            transition: .2s ease;
        }
        .dashboard-btn:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }
        .redirect-text {
            margin-top: 15px !important;
            margin-bottom: 0 !important;
            font-size: 12px !important;
            color: #94a3b8 !important;
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(12px) scale(.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="success-icon">✓</div>
        <div class="status"><span></span> Update Completed</div>
        <h1>Employee Updated Successfully</h1>
        <p>The employee information has been updated and saved successfully.</p>
        <a class="dashboard-btn" href="index.php?success=Employee%20updated%20successfully">Continue to Dashboard</a>
        <p class="redirect-text">You will be redirected automatically...</p>
    </div>
    <script>
        setTimeout(function () {
            window.location.href = 'index.php?success=' + encodeURIComponent('Employee updated successfully');
        }, 1800);
    </script>
</body>
</html>
HTML;
        exit;

    } else {

        if ($stmt->errno == 1062) {

            die(
                "Employee ID already exists. " .
                "Please use a different Employee ID."
            );

        } else {

            die(
                "Update failed: " .
                $stmt->error
            );
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

    <title>Edit Employee</title>

    <link rel="stylesheet" href="style.css">

    <style>

        /* FORM IMPROVEMENTS */

        .form-container {
            max-width: 1100px;

            margin: 30px auto;

            background: #ffffff;

            padding: 30px;

            border-radius: 18px;

            border: 1px solid #e2e8f0;

            box-shadow:
                0 8px 25px rgba(15, 23, 42, 0.06);
        }


        .form-title {
            font-size: 24px;

            font-weight: 700;

            color: #0f2d67;

            margin-bottom: 8px;
        }


        .form-subtitle {
            color: #64748b;

            font-size: 14px;

            margin-bottom: 28px;
        }


        .form-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 22px;
        }


        .form-group {

            display: flex;

            flex-direction: column;
        }


        .form-group label {

            font-size: 14px;

            font-weight: 700;

            color: #334155;

            margin-bottom: 8px;
        }


        .form-group input,
        .form-group select {

            width: 100%;

            height: 46px;

            padding: 0 14px;

            border: 1px solid #d1d5db;

            border-radius: 9px;

            background: #ffffff;

            font-size: 14px;

            color: #0f172a;

            outline: none;

            transition: .2s;
        }


        .form-group input:focus,
        .form-group select:focus {

            border-color: #2563eb;

            box-shadow:
                0 0 0 3px
                rgba(37, 99, 235, .10);
        }


        .section-label {

            grid-column: 1 / -1;

            font-size: 16px;

            font-weight: 700;

            color: #1e3a8a;

            padding-bottom: 10px;

            border-bottom: 1px solid #e5e7eb;

            margin-top: 5px;
        }


        .required {

            color: #dc2626;
        }


        .optional {

            color: #94a3b8;

            font-size: 12px;

            font-weight: normal;
        }


        /* BUTTONS */

        .form-actions {

            display: flex;

            justify-content: flex-end;

            gap: 12px;

            margin-top: 30px;

            padding-top: 25px;

            border-top: 1px solid #e5e7eb;
        }


        .btn {

            border: none;

            text-decoration: none;

            cursor: pointer;

            padding: 12px 22px;

            border-radius: 9px;

            font-size: 14px;

            font-weight: 700;

            transition: .2s;

            display: inline-flex;

            align-items: center;

            justify-content: center;
        }


        .btn-primary {

            background: #2563eb;

            color: white;
        }


        .btn-primary:hover {

            background: #1d4ed8;

            transform: translateY(-1px);
        }


        .btn-cancel {

            background: #f1f5f9;

            color: #475569;
        }


        .btn-cancel:hover {

            background: #e2e8f0;
        }


        /* RESPONSIVE */

        @media (max-width: 700px) {

            .form-grid {

                grid-template-columns: 1fr;
            }

            .form-container {

                margin: 20px;

                padding: 20px;
            }

            .form-actions {

                flex-direction: column;
            }

            .form-actions .btn {

                width: 100%;
            }
        }

    </style>

</head>


<body>

<!-- SIDEBAR -->
<aside class="sidebar">

    <!-- LOGO -->
    <div class="logo">

        <h1>
            EMPLOYEE
            <br>
            <span>
                INFORMATION SYSTEM
            </span>
        </h1>

        <p>
            Employee Management Portal
        </p>

    </div>


    <!-- NAVIGATION -->
    <ul class="nav-menu">

        <!-- DASHBOARD -->
        <li>

            <a
                href="index.php"
                class="<?= ($currentPage === 'index.php') ? 'active' : '' ?>"
            >

                <span class="nav-icon">
                    ⌂
                </span>

                <span>
                    Dashboard
                </span>

            </a>

        </li>


        <!-- ADD EMPLOYEE -->
        <li>

            <a
                href="add_employee.php"
                class="<?= ($currentPage === 'add_employee.php') ? 'active' : '' ?>"
            >

                <span class="nav-icon">
                    ＋
                </span>

                <span>
                    Add Employee
                </span>

            </a>

        </li>


        <!-- DEPARTMENTS -->
        <li>

            <a
                href="departments.php"
                class="<?= ($currentPage === 'departments.php') ? 'active' : '' ?>"
            >

                <span class="nav-icon">
                    ▦
                </span>

                <span>
                    Departments
                </span>

            </a>

        </li>

    </ul>

</aside>

<!-- MAIN -->
<main class="main">


    <!-- HEADER -->
    <header class="header">

        <div class="header-title">
            Employee Information System
        </div>


        <div class="header-right">

            <div class="admin-name">

                <strong>
                    System Administrator
                </strong>

                <small>
                    Administrator
                </small>

            </div>


            <div class="avatar">
                A
            </div>


        </div>

    </header>
    </div>



    <!-- CONTENT -->

    <div class="content">


        <div class="form-container">


            <div class="form-title">

                Update Employee Information

            </div>


            <div class="form-subtitle">

                Update the employee's personal and employment information.

            </div>



            <form method="POST">


                <div class="form-grid">


                    <!-- EMPLOYEE INFORMATION -->

                    <div class="section-label">

                        Employee Information

                    </div>


                    <!-- EMPLOYEE ID -->

                    <div class="form-group">

                        <label>

                            Employee ID
                            <span class="required">*</span>

                        </label>


                        <input
                            type="text"
                            name="employee_id"
                            value="<?= htmlspecialchars(
                                $employee['employee_id'] ?? ''
                            ) ?>"
                            required
                        >

                    </div>



                    <!-- EMAIL -->

                    <div class="form-group">

                        <label>

                            Email
                            <span class="required">*</span>

                        </label>


                        <input
                            type="email"
                            name="email"
                            value="<?= htmlspecialchars(
                                $employee['email'] ?? ''
                            ) ?>"
                            required
                        >

                    </div>



                    <!-- PERSONAL INFORMATION -->

                    <div class="section-label">

                        Personal Information

                    </div>


                    <!-- FIRST NAME -->

                    <div class="form-group">

                        <label>

                            First Name
                            <span class="required">*</span>

                        </label>


                        <input
                            type="text"
                            name="first_name"
                            value="<?= htmlspecialchars(
                                $employee['first_name'] ?? ''
                            ) ?>"
                            required
                        >

                    </div>



                    <!-- MIDDLE NAME -->

                    <div class="form-group">

                        <label>

                            Middle Name

                            <span class="optional">
                                (Optional)
                            </span>

                        </label>


                        <input
                            type="text"
                            name="middle_name"
                            value="<?= htmlspecialchars(
                                $employee['middle_name'] ?? ''
                            ) ?>"
                            placeholder="Middle Name"
                        >

                    </div>



                    <!-- LAST NAME -->

                    <div class="form-group">

                        <label>

                            Last Name
                            <span class="required">*</span>

                        </label>


                        <input
                            type="text"
                            name="last_name"
                            value="<?= htmlspecialchars(
                                $employee['last_name'] ?? ''
                            ) ?>"
                            required
                        >

                    </div>



                    <!-- SUFFIX -->

                    <div class="form-group">

                        <label>

                            Name Suffix

                            <span class="optional">
                                (Optional)
                            </span>

                        </label>


                        <select name="suffix">

                            <option value="">

                                No Suffix

                            </option>


                            <option
                                value="Jr."
                                <?= (
                                    ($employee['suffix'] ?? '') === 'Jr.'
                                ) ? 'selected' : '' ?>
                            >

                                Jr.

                            </option>


                            <option
                                value="Sr."
                                <?= (
                                    ($employee['suffix'] ?? '') === 'Sr.'
                                ) ? 'selected' : '' ?>
                            >

                                Sr.

                            </option>


                            <option
                                value="II"
                                <?= (
                                    ($employee['suffix'] ?? '') === 'II'
                                ) ? 'selected' : '' ?>
                            >

                                II

                            </option>


                            <option
                                value="III"
                                <?= (
                                    ($employee['suffix'] ?? '') === 'III'
                                ) ? 'selected' : '' ?>
                            >

                                III

                            </option>


                            <option
                                value="IV"
                                <?= (
                                    ($employee['suffix'] ?? '') === 'IV'
                                ) ? 'selected' : '' ?>
                            >

                                IV

                            </option>


                            <option
                                value="V"
                                <?= (
                                    ($employee['suffix'] ?? '') === 'V'
                                ) ? 'selected' : '' ?>
                            >

                                V

                            </option>

                        </select>

                    </div>



                    <!-- PHONE -->

                    <div class="form-group">

                        <label>

                            Phone

                        </label>


                        <input
                            type="text"
                            name="phone"
                            value="<?= htmlspecialchars(
                                $employee['phone'] ?? ''
                            ) ?>"
                            placeholder="09XXXXXXXXX"
                            maxlength="11"
                            inputmode="numeric"
                            pattern="[0-9]{11}"
                            oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11)"
                        >

                    </div>



                    <!-- EMPLOYMENT INFORMATION -->

                    <div class="section-label">

                        Employment Information

                    </div>


                    <!-- DEPARTMENT -->

                    <div class="form-group">

                        <label>

                            Department

                        </label>


                        <input
                            type="text"
                            name="department"
                            value="<?= htmlspecialchars(
                                $employee['department'] ?? ''
                            ) ?>"
                        >

                    </div>



                    <!-- POSITION -->

                    <div class="form-group">

                        <label>

                            Position

                        </label>


                        <input
                            type="text"
                            name="position"
                            value="<?= htmlspecialchars(
                                $employee['position'] ?? ''
                            ) ?>"
                        >

                    </div>



                    <!-- HIRE DATE -->

                    <div class="form-group">

                        <label>

                            Hire Date

                        </label>


                        <input
                            type="date"
                            name="hire_date"
                            value="<?= htmlspecialchars(
                                $employee['hire_date'] ?? ''
                            ) ?>"
                        >

                    </div>



                    <!-- SALARY -->

                    <div class="form-group">

                        <label for="salary">

                            Salary
                            <span class="required">*</span>

                        </label>


                        <input
                            type="number"
                            name="salary"
                            id="salary"
                            step="0.01"
                            min="0"
                            value="<?= htmlspecialchars(
                                $employee['salary'] ?? '0.00'
                            ) ?>"
                            placeholder="35000.00"
                            required
                        >

                    </div>



                    <!-- STATUS -->

                    <div class="form-group">

                        <label for="status">

                            Employment Status
                            <span class="required">*</span>

                        </label>


                        <select
                            name="status"
                            id="status"
                            required
                        >

                            <option
                                value="Active"
                                <?= (
                                    ($employee['status'] ?? 'Active')
                                    === 'Active'
                                ) ? 'selected' : '' ?>
                            >

                                Active

                            </option>


                            <option
                                value="Inactive"
                                <?= (
                                    ($employee['status'] ?? '')
                                    === 'Inactive'
                                ) ? 'selected' : '' ?>
                            >

                                Inactive

                            </option>

                        </select>

                    </div>


                </div>



                <!--  ACTION BUTTONS -->

                <div class="form-actions">


                    <a
                        href="index.php"
                        class="btn btn-cancel"
                    >

                        Cancel

                    </a>


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        Update Employee

                    </button>


                </div>


            </form>

        </div>

    </div>

</div>


</body>

</html>