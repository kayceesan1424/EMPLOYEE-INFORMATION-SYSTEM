<?php
session_start();

/* AUTHENTICATION */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include "config.php";

/* CURRENT PAGE
   This is used to highlight only the page currently being viewed.
*/
$currentPage = basename($_SERVER['PHP_SELF']);

/* DEPARTMENT SUMMARY */
$departmentSummary = [];

$result = $conn->query("
    SELECT
        department,
        COUNT(*) AS employee_count,
        SUM(status = 'Active') AS active_count,
        SUM(status = 'Inactive') AS inactive_count
    FROM employees
    WHERE department IS NOT NULL
      AND department != ''
    GROUP BY department
    ORDER BY department ASC
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $departmentSummary[] = $row;
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

    <title>Departments - Employee Information System</title>

    <!-- SAME CSS USED BY INDEX.PHP -->
    <link rel="stylesheet" href="style.css">
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


    <!-- CONTENT -->
    <section class="content">


        <!-- WELCOME / PAGE HEADER -->
        <div class="welcome">

            <div>

                <h2>
                    Department Overview
                </h2>

                <p>
                    View employee distribution by department.
                </p>

            </div>


            <div class="current-date">

                📅

                <?php
                echo date("F d, Y");
                ?>

            </div>

        </div>


        <!-- DEPARTMENT TABLE -->
        <div class="table-card">


            <div class="department-header">

                <div>

                    <h2>
                        Departments
                    </h2>

                    <p>
                        View employee records by department.
                    </p>

                </div>


                <a
                    href="index.php"
                    class="back-btn"
                >
                    ← Back to Employees
                </a>

            </div>


            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Department
                            </th>

                            <th>
                                Total Employees
                            </th>

                            <th>
                                Active
                            </th>

                            <th>
                                Inactive
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (!empty($departmentSummary)): ?>


                        <?php foreach ($departmentSummary as $dept): ?>


                            <tr>


                                <!-- DEPARTMENT -->

                                <td>

                                    <div class="department-title-cell">

                                        <span class="department-icon">
                                            🏢
                                        </span>

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $dept['department']
                                            );
                                            ?>

                                        </strong>

                                    </div>

                                </td>


                                <!-- TOTAL EMPLOYEES -->

                                <td>

                                    <span class="count-badge">

                                        <?php
                                        echo (int)$dept['employee_count'];
                                        ?>

                                        Employee(s)

                                    </span>

                                </td>


                                <!-- ACTIVE -->

                                <td>

                                    <span class="active-count">

                                        ●

                                        <?php
                                        echo (int)$dept['active_count'];
                                        ?>

                                    </span>

                                </td>


                                <!-- INACTIVE -->

                                <td>

                                    <span class="inactive-count">

                                        ●

                                        <?php
                                        echo (int)$dept['inactive_count'];
                                        ?>

                                    </span>

                                </td>


                                <!-- ACTION -->

                                <td>

                                    <a
                                        href="index.php?department=<?php
                                            echo urlencode(
                                                $dept['department']
                                            );
                                        ?>"
                                        class="back-btn"
                                    >
                                        View Employees
                                    </a>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="5"
                                class="empty-state"
                            >

                                No departments found.

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </div>


    </section>

</main>

</body>
</html>
