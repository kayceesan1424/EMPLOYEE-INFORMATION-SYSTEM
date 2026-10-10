<?php

session_start();



if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");

    exit;

}



include "config.php";



/* CURRENT PAGE FOR NAVIGATION HIGHLIGHT */

$currentPage = basename($_SERVER['PHP_SELF']);



/* LOGOUT */

if (isset($_GET['logout']) && $_GET['logout'] === '1') {

    session_unset();

    session_destroy();

    header("Location: login.php");

    exit;

}



$message = "";



if ($_SERVER["REQUEST_METHOD"] === "POST") {



    /* GET FORM DATA */



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



    /* ==========================================

       PHONE VALIDATION

       MUST CONTAIN EXACTLY 11 NUMBERS

       ========================================== */



    if ($phone !== '') {



        // Remove any spaces or non-numeric characters

        if (!preg_match('/^[0-9]{11}$/', $phone)) {



            $message = "Phone number must contain exactly 11 numbers.";



        }

    }



    /* VALIDATE STATUS */



    if ($status !== 'Active' && $status !== 'Inactive') {

        $status = 'Active';

    }



    /* INSERT EMPLOYEE

       ONLY IF THERE IS NO VALIDATION ERROR */



    if ($message === "") {



        $stmt = $conn->prepare("

            INSERT INTO employees

            (

                employee_id,

                first_name,

                middle_name,

                last_name,

                suffix,

                email,

                phone,

                department,

                position,

                hire_date,

                salary,

                status

            )

            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)

        ");



        /* Check prepare */



        if (!$stmt) {

            die("Prepare failed: " . $conn->error);

        }



        /* BIND PARAMETERS */



        $stmt->bind_param(

            "ssssssssssds",

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

            $status

        );



        /* EXECUTE */



        if ($stmt->execute()) {



            $stmt->close();



            header("Location: index.php?success=Employee added successfully");



            exit;



        } else {



            if ($stmt->errno == 1062) {



                $message =

                    "Employee ID already exists. Please use a different Employee ID.";



            } else {



                $message =

                    "Unable to add employee: " . $stmt->error;

            }

        }



        $stmt->close();

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



    <title>Add Employee</title>



    <link rel="stylesheet" href="style.css">



    <style>



        /* SIDEBAR */



        .sidebar {

            position: fixed;

            left: 0;

            top: 0;

            width: 250px;

            height: 100vh;

            background:

                linear-gradient(

                    180deg,

                    #073b91 0%,

                    #0b3d91 55%,

                    #062b6f 100%

                );

            padding: 28px 18px;

            color: white;

            box-shadow: 5px 0 25px rgba(0,0,0,.08);

            z-index: 1000;

        }



        .logo {

            padding: 5px 18px 30px;

        }



        .logo h1 {

            font-size: 23px;

            line-height: 1.45;

            font-weight: 800;

            letter-spacing: .5px;

        }



        .logo h1 span {

            color: #60a5fa;

        }



        .logo p {

            margin-top: 7px;

            font-size: 12px;

            color: #bfdbfe;

        }



        /* NAVIGATION */



        .nav-menu {

            list-style: none;

            margin-top: 15px;

            padding: 0;

        }



        .nav-menu li {

            margin-bottom: 8px;

        }



        .nav-menu a {

            display: flex;

            align-items: center;

            gap: 14px;

            padding: 14px 17px;

            color: #dbeafe;

            border-radius: 12px;

            font-size: 15px;

            text-decoration: none;

            transition:

                background .2s ease,

                transform .2s ease;

        }



        .nav-menu a:hover {

            background: rgba(255,255,255,.12);

            color: white;

            transform: translateX(2px);

        }



        .nav-menu a.active {

            background:

                linear-gradient(

                    90deg,

                    #2563eb,

                    #3b82f6

                );

            color: white;

            box-shadow:

                0 7px 20px

                rgba(37,99,235,.35);

        }



        .nav-icon {

            width: 25px;

            text-align: center;

            font-size: 18px;

        }



        /* MAIN */



        .main {

            margin-left: 250px;

            min-height: 100vh;

            width: calc(100% - 250px);

            background: #f4f7fb;

        }



        /* TOP BAR */



        .topbar {

            height: 85px;

            background: #ffffff;

            border-bottom: 1px solid #e2e8f0;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 40px;

        }



        .page-title {

            font-size: 30px;

            font-weight: 800;

            color: #0f172a;

        }



        .user-info {

            display: flex;

            align-items: center;

            gap: 15px;

            font-size: 14px;

            font-weight: 700;

            color: #111827;

        }



        .admin-name {

            text-align: right;

        }



        .admin-name strong {

            display: block;

            font-size: 14px;

            color: #0f172a;

        }



        .admin-name small {

            display: block;

            margin-top: 3px;

            color: #64748b;

            font-size: 12px;

            font-weight: 400;

        }



        .user-avatar {

            width: 52px;

            height: 52px;

            border-radius: 50%;

            background: #347ff0;

            color: #ffffff;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 20px;

            font-weight: 700;

            box-shadow:

                0 5px 15px

                rgba(37,99,235,.20);

        }



        /* CONTENT */



        .content {

            width: 100%;

            max-width: none;

            margin: 0;

            padding: 34px 40px 50px;

        }



        /* FORM */



        .form-container {

            width: 100%;

            max-width: none;

            margin: 0;

            padding: 32px;

            background: #ffffff;

            border: 1px solid #e2e8f0;

            border-radius: 20px;

            box-shadow:

                0 8px 24px

                rgba(15,23,42,.05);

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

            grid-template-columns: repeat(2, 1fr);

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

            box-sizing: border-box;

        }



        .form-group input:focus,

        .form-group select:focus {

            border-color: #2563eb;

            box-shadow:

                0 0 0 3px

                rgba(37,99,235,.10);

        }



        .form-group input::placeholder {

            color: #94a3b8;

        }



        /* REQUIRED */



        .required {

            color: #dc2626;

        }



        /* OPTIONAL */



        .optional {

            color: #94a3b8;

            font-size: 12px;

            font-weight: normal;

        }



        /* SECTION */



        .section-label {

            grid-column: 1 / -1;

            font-size: 16px;

            font-weight: 700;

            color: #1e3a8a;

            padding-bottom: 10px;

            border-bottom: 1px solid #e5e7eb;

            margin-top: 5px;

        }



        /* PHONE */



        .phone-help {

            margin-top: 6px;

            font-size: 12px;

            color: #64748b;

        }



        /* FORM ACTIONS */



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



        /* ALERT */



        .alert {

            width: 100%;

            max-width: none;

            margin: 0 0 24px;

            padding: 14px 18px;

            background: #fee2e2;

            color: #b91c1c;

            border: 1px solid #fecaca;

            border-radius: 10px;

            font-size: 14px;

            font-weight: 600;

        }



        /* RESPONSIVE */



        @media (max-width: 900px) {



            .sidebar {

                width: 80px;

                padding: 25px 10px;

            }



            .logo {

                padding: 5px 5px 25px;

            }



            .logo h1 {

                font-size: 13px;

            }



            .logo h1 span,

            .logo p,

            .nav-menu a span:not(.nav-icon) {

                display: none;

            }



            .nav-menu a {

                justify-content: center;

                padding: 14px 5px;

            }



            .main {

                margin-left: 80px;

                width: calc(100% - 80px);

            }



            .content {

                padding: 24px;

            }

        }



        @media (max-width: 700px) {



            .form-grid {

                grid-template-columns: 1fr;

            }



            .form-container {

                padding: 20px;

            }



            .form-actions {

                flex-direction: column;

            }



            .form-actions .btn {

                width: 100%;

            }



            .topbar {

                padding: 0 20px;

            }



            .page-title {

                font-size: 20px;

            }



            .admin-name {

                display: none;

            }

        }



        @media (max-width: 500px) {



            .topbar {

                height: 70px;

            }



            .content {

                padding: 15px;

            }

        }




        /* PROFESSIONAL SAVE CONFIRMATION MODAL */
        .confirm-overlay {
            position: fixed;
            inset: 0;
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, 0.58);
            backdrop-filter: blur(5px);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .22s ease, visibility .22s ease;
        }

        .confirm-overlay.show {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .confirm-dialog {
            width: 100%;
            max-width: 440px;
            padding: 32px;
            text-align: center;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .25);
            transform: translateY(12px) scale(.98);
            transition: transform .22s ease;
        }

        .confirm-overlay.show .confirm-dialog {
            transform: translateY(0) scale(1);
        }

        .confirm-icon {
            width: 66px;
            height: 66px;
            margin: 0 auto 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2563eb;
            background: #eff6ff;
            border: 1px solid #dbeafe;
            border-radius: 50%;
        }

        .confirm-icon svg {
            width: 30px;
            height: 30px;
            stroke: currentColor;
        }

        .confirm-dialog h2 {
            margin: 0 0 10px;
            color: #0f172a;
            font-size: 23px;
            font-weight: 800;
            letter-spacing: -.4px;
        }

        .confirm-dialog p {
            margin: 0 auto;
            max-width: 340px;
            color: #64748b;
            font-size: 14px;
            line-height: 1.7;
        }

        .confirm-note {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            margin-top: 18px;
            padding: 12px 14px;
            text-align: left;
            color: #1e40af;
            background: #eff6ff;
            border: 1px solid #dbeafe;
            border-radius: 10px;
            font-size: 12px;
            line-height: 1.5;
        }

        .confirm-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 26px;
        }

        .confirm-actions .btn {
            min-width: 140px;
            min-height: 45px;
        }

        .btn-confirm-cancel {
            color: #334155;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
        }

        .btn-confirm-cancel:hover {
            background: #e2e8f0;
        }

        .btn-confirm-save {
            color: #ffffff;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            box-shadow: 0 5px 14px rgba(37, 99, 235, .22);
        }

        .btn-confirm-save:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: translateY(-1px);
        }

        @media (max-width: 480px) {
            .confirm-dialog { padding: 26px 20px; }
            .confirm-dialog h2 { font-size: 20px; }
            .confirm-actions { flex-direction: column-reverse; }
            .confirm-actions .btn { width: 100%; }
        }

    </style>



</head>



<body>



<!-- SIDEBAR -->



<aside class="sidebar">



    <div class="logo">



        <h1>

            EMPLOYEE

            <br>

            <span>INFORMATION SYSTEM</span>

        </h1>



        <p>

            Employee Management Portal

        </p>



    </div>



    <ul class="nav-menu">



        <!-- DASHBOARD -->



        <li>



            <a

                href="index.php"

                class="<?= ($currentPage === 'index.php') ? 'active' : '' ?>"

            >



                <span class="nav-icon">⌂</span>



                <span>Dashboard</span>



            </a>



        </li>



        <!-- ADD EMPLOYEE -->



        <li>



            <a

                href="add_employee.php"

                class="<?= ($currentPage === 'add_employee.php') ? 'active' : '' ?>"

            >



                <span class="nav-icon">＋</span>



                <span>Add Employee</span>



            </a>



        </li>



        <!-- DEPARTMENTS -->



        <li>



            <a

                href="departments.php"

                class="<?= ($currentPage === 'departments.php') ? 'active' : '' ?>"

            >



                <span class="nav-icon">▦</span>



                <span>Departments</span>



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







    <!-- CONTENT -->



    <div class="content">



        <!-- ERROR MESSAGE -->



        <?php if ($message): ?>



            <div class="alert">

                <?= htmlspecialchars($message) ?>

            </div>



        <?php endif; ?>



        <!-- FORM -->



        <div class="form-container">



            <div class="form-title">

                Employee Information

            </div>



            <div class="form-subtitle">

                Enter the employee's personal and employment information.

            </div>



            <form method="POST" id="employeeForm">



                <div class="form-grid">



                    <!-- EMPLOYEE ID -->



                    <div class="form-group">



                        <label>

                            Employee ID

                            <span class="required">*</span>

                        </label>



                        <input

                            type="text"

                            name="employee_id"

                            placeholder="EMP-004"

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

                            placeholder="employee@example.com"

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

                            placeholder="Juan"

                            required

                        >



                    </div>



                    <!-- MIDDLE NAME -->



                    <div class="form-group">



                        <label>

                            Middle Name

                            <span class="optional">(Optional)</span>

                        </label>



                        <input

                            type="text"

                            name="middle_name"

                            placeholder="Dela Cruz"

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

                            placeholder="Santos"

                            required

                        >



                    </div>



                    <!-- SUFFIX -->



                    <div class="form-group">



                        <label>

                            Name Suffix

                            <span class="optional">(Optional)</span>

                        </label>



                        <select name="suffix">



                            <option value="">

                                No Suffix

                            </option>



                            <option value="Jr.">

                                Jr.

                            </option>



                            <option value="Sr.">

                                Sr.

                            </option>



                            <option value="II">

                                II

                            </option>



                            <option value="III">

                                III

                            </option>



                            <option value="IV">

                                IV

                            </option>



                            <option value="V">

                                V

                            </option>



                        </select>



                    </div>



                    <!-- PHONE -->



                    <div class="form-group">



                        <label>

                            Phone

                            <span class="optional">(11 digits)</span>

                        </label>



                        <input

                            type="tel"

                            name="phone"

                            id="phone"

                            placeholder="09XXXXXXXXX"

                            inputmode="numeric"

                            maxlength="11"

                            pattern="[0-9]{11}"

                            title="Phone number must contain exactly 11 numbers."

                            autocomplete="tel"

                        >



                        <div class="phone-help">

                            Enter exactly 11 numbers. Example: 09123456789

                        </div>



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

                            placeholder="Information Technology"

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

                            placeholder="Software Developer"

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

                        >



                    </div>



                    <!-- SALARY -->



                    <div class="form-group">



                        <label>

                            Salary

                            <span class="required">*</span>

                        </label>



                        <input

                            type="number"

                            name="salary"

                            placeholder="35000.00"

                            min="0"

                            step="0.01"

                            required

                        >



                    </div>



                    <!-- STATUS -->



                    <div class="form-group">



                        <label>

                            Employment Status

                            <span class="required">*</span>

                        </label>



                        <select name="status" required>



                            <option value="Active" selected>

                                Active

                            </option>



                            <option value="Inactive">

                                Inactive

                            </option>



                        </select>



                    </div>



                </div>



                <!-- BUTTONS -->



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

                        Save Employee

                    </button>



                </div>



            </form>



        </div>
        </div>

        <!-- SAVE CONFIRMATION MODAL -->
        <div class="confirm-overlay" id="saveConfirmModal" role="dialog"
             aria-modal="true" aria-labelledby="confirmTitle" aria-describedby="confirmDescription">
            <div class="confirm-dialog">
                <div class="confirm-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 3h11l4 4v14H5z"></path>
                        <path d="M9 3v6h7V5"></path>
                        <path d="M9 21v-7h7v7"></path>
                    </svg>
                </div>
                <h2 id="confirmTitle">Confirm Save Employee</h2>
                <p id="confirmDescription">
                    Are you sure you want to save this employee's information?
                    Please make sure the information you entered is correct before continuing.
                </p>
                <div class="confirm-note">
                    <span aria-hidden="true">&#9432;</span>
                    <span>Once confirmed, this employee record will be saved to the system.</span>
                </div>
                <div class="confirm-actions">
                    <button type="button" class="btn btn-confirm-cancel" id="cancelSave">Cancel</button>
                    <button type="button" class="btn btn-confirm-save" id="confirmSave">Confirm Save</button>
                </div>
            </div>
        </div>




    </div>



</div>



<!-- PHONE NUMBER INPUT CONTROL -->



<script>



const phoneInput = document.getElementById("phone");



/*

 * Allow numbers only.

 * Automatically remove letters, spaces,

 * symbols and other characters.

 */

phoneInput.addEventListener("input", function () {



    this.value = this.value

        .replace(/[^0-9]/g, '')

        .slice(0, 11);



});



/*

 * Prevent invalid paste/input before it happens.

 */

phoneInput.addEventListener("keypress", function (event) {



    if (!/[0-9]/.test(event.key)) {

        event.preventDefault();

    }



});



/*

 * Final validation before submitting.

 */

const employeeForm = document.getElementById("employeeForm");
const saveConfirmModal = document.getElementById("saveConfirmModal");
const confirmSaveButton = document.getElementById("confirmSave");
const cancelSaveButton = document.getElementById("cancelSave");
let saveConfirmed = false;

employeeForm.addEventListener("submit", function (event) {
    const phone = phoneInput.value.trim();

    if (phone !== "" && !/^[0-9]{11}$/.test(phone)) {
        event.preventDefault();
        alert("Please enter exactly 11 numbers for the phone number.");
        phoneInput.focus();
        return;
    }

    if (!saveConfirmed) {
        event.preventDefault();
        saveConfirmModal.classList.add("show");
        confirmSaveButton.focus();
    }
});

confirmSaveButton.addEventListener("click", function () {
    saveConfirmed = true;
    saveConfirmModal.classList.remove("show");
    employeeForm.submit();
});

cancelSaveButton.addEventListener("click", function () {
    saveConfirmModal.classList.remove("show");
    document.querySelector('button[type="submit"]').focus();
});

saveConfirmModal.addEventListener("click", function (event) {
    if (event.target === saveConfirmModal) {
        saveConfirmModal.classList.remove("show");
        document.querySelector('button[type="submit"]').focus();
    }
});

document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && saveConfirmModal.classList.contains("show")) {
        saveConfirmModal.classList.remove("show");
        document.querySelector('button[type="submit"]').focus();
    }
});</script>



</body>



</html>