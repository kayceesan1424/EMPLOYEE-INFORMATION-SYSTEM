<?php
session_start();

/* AUTHENTICATION */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include "config.php";


/* DELETE SUCCESS MESSAGE */
$deleteSuccess = (($_GET['deleted'] ?? '') === 'success');


/* FILTERS */

$search     = trim($_GET['search'] ?? '');
$department = trim($_GET['department'] ?? '');
$status     = trim($_GET['status'] ?? '');
$view       = trim($_GET['view'] ?? 'employees');


/* STATISTICS */

$totalEmployees    = 0;
$activeEmployees   = 0;
$inactiveEmployees = 0;
$totalDepartments  = 0;


/* TOTAL EMPLOYEES */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM employees
");

if ($result) {
    $row = $result->fetch_assoc();
    $totalEmployees = (int)($row['total'] ?? 0);
}


/* ACTIVE EMPLOYEES */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM employees
    WHERE status = 'Active'
");

if ($result) {
    $row = $result->fetch_assoc();
    $activeEmployees = (int)($row['total'] ?? 0);
}


/* INACTIVE EMPLOYEES */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM employees
    WHERE status = 'Inactive'
");

if ($result) {
    $row = $result->fetch_assoc();
    $inactiveEmployees = (int)($row['total'] ?? 0);
}


/* TOTAL DEPARTMENTS */

$result = $conn->query("
    SELECT COUNT(DISTINCT department) AS total
    FROM employees
    WHERE department IS NOT NULL
    AND department != ''
");

if ($result) {
    $row = $result->fetch_assoc();
    $totalDepartments = (int)($row['total'] ?? 0);
}


/* DEPARTMENT LIST */

$departmentList = [];

$result = $conn->query("
    SELECT DISTINCT department
    FROM employees
    WHERE department IS NOT NULL
    AND department != ''
    ORDER BY department ASC
");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $departmentList[] = $row['department'];
    }
}


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


/* EMPLOYEE QUERY */

$sql = "
    SELECT *
    FROM employees
    WHERE 1=1
";

$params = [];
$types  = "";


/* SEARCH */

if ($search !== '') {

    $sql .= "
        AND (
            employee_id LIKE ?
            OR first_name LIKE ?
            OR middle_name LIKE ?
            OR last_name LIKE ?
            OR suffix LIKE ?
            OR email LIKE ?
            OR phone LIKE ?
            OR department LIKE ?
            OR position LIKE ?
        )
    ";

    $searchTerm = "%" . $search . "%";

    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;

    $types .= "sssssssss";
}


/* DEPARTMENT FILTER */

if ($department !== '') {

    $sql .= "
        AND department = ?
    ";

    $params[] = $department;
    $types .= "s";
}


/* STATUS FILTER */

if ($status !== '') {

    $sql .= "
        AND status = ?
    ";

    $params[] = $status;
    $types .= "s";
}


/* ORDER */

$sql .= "
    ORDER BY id DESC
";


/* PREPARE EMPLOYEE QUERY */

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error: " . htmlspecialchars($conn->error));
}


/* BIND PARAMETERS */

if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );
}


/* EXECUTE */

if (!$stmt->execute()) {
    die(
        "Query execution failed: " .
        htmlspecialchars($stmt->error)
    );
}


/* GET RESULT */

$employees = $stmt->get_result();

if (!$employees) {
    die(
        "Unable to retrieve employee records: " .
        htmlspecialchars($stmt->error)
    );
}


/* CURRENT PAGE */

$currentPage = basename($_SERVER['PHP_SELF']);
$currentView = $_GET['view'] ?? '';


/* CHECK IF FILTER IS ACTIVE */

$hasFilters = (
    $search !== '' ||
    $department !== '' ||
    $status !== ''
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Employee Information System
    </title>

    <!-- EXTERNAL CSS -->
    <link
        rel="stylesheet"
        href="style.css"
    >

    <style>

        /* =====================================================
           FILTER AREA
           ===================================================== */

        .search-area {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;

            /* Keeps filters visible while scrolling */
            position: sticky;
            top: 0;
            z-index: 50;

            background: #ffffff;
            padding: 16px 0;

            border-bottom: 1px solid #e5e7eb;
        }


        /* SEARCH BOX */

        .search-box {
            flex: 1;
            min-width: 250px;
        }


        .search-box input {
            width: 100%;
            height: 42px;
            padding: 0 14px;

            border: 1px solid #d1d5db;
            border-radius: 9px;

            font-size: 14px;
            outline: none;

            transition: 0.2s;
        }


        .search-box input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
        }


        /* FILTER SELECT */

        .filter-select {
            height: 42px;
            min-width: 170px;

            padding: 0 35px 0 12px;

            border: 1px solid #d1d5db;
            border-radius: 9px;

            background: #ffffff;

            color: #334155;
            font-size: 14px;

            cursor: pointer;
            outline: none;

            transition: 0.2s;
        }


        .filter-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
        }


        /* SEARCH BUTTON */

        .btn-search {
            height: 42px;

            padding: 0 18px;

            border: none;
            border-radius: 9px;

            background: #2563eb;
            color: #ffffff;

            font-size: 14px;
            font-weight: 600;

            cursor: pointer;

            transition: 0.2s;
        }


        .btn-search:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }


        /* CLEAR BUTTON */

        .btn-clear {
            height: 42px;

            padding: 0 17px;

            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;

            border: 1px solid #d1d5db;
            border-radius: 9px;

            background: #ffffff;
            color: #475569;

            text-decoration: none;

            font-size: 14px;
            font-weight: 600;

            cursor: pointer;

            transition: 0.2s;
        }


        .btn-clear:hover {
            background: #f8fafc;
            border-color: #94a3b8;
            color: #dc2626;
        }


        /* ADD EMPLOYEE */

        .btn-add {
            height: 42px;

            padding: 0 18px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            border-radius: 9px;

            background: #0f3d91;
            color: #ffffff;

            text-decoration: none;

            font-size: 14px;
            font-weight: 600;

            transition: 0.2s;
        }


        .btn-add:hover {
            background: #0b3277;
            transform: translateY(-1px);
        }


        /* FILTER ACTIVE INDICATOR */

        .filter-active-label {
            width: 100%;

            display: flex;
            align-items: center;
            gap: 7px;

            margin-top: 2px;

            color: #64748b;
            font-size: 12px;
        }


        .filter-active-dot {
            width: 7px;
            height: 7px;

            border-radius: 50%;

            background: #2563eb;
        }


        /* MOBILE */

        @media (max-width: 900px) {

            .search-area {
                position: sticky;
                top: 0;
            }

            .search-box {
                min-width: 100%;
                flex: 100%;
            }

            .filter-select {
                flex: 1;
                min-width: 140px;
            }

        }


        @media (max-width: 600px) {

            .search-area {
                align-items: stretch;
            }

            .filter-select,
            .btn-search,
            .btn-clear,
            .btn-add {
                width: 100%;
            }

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
                class="<?= (
                    $currentPage === 'index.php'
                    && $currentView === ''
                ) ? 'active' : '' ?>"
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
                class="<?= (
                    $currentPage === 'add_employee.php'
                ) ? 'active' : '' ?>"
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
                href="departments.php?view=departments"
                class="<?= (
                    $currentPage === 'index.php'
                    && $currentView === 'departments'
                ) ? 'active' : '' ?>"
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


            <!-- LOGOUT -->

            <a
                href="logout.php"
                class="logout"
                onclick="return confirm('Are you sure you want to logout?');"
            >
                Logout
            </a>


        </div>

    </header>



    <!-- CONTENT -->

    <section class="content">


        <!-- WELCOME -->

        <div class="welcome">

            <div>

                <h2>
                    Welcome back, System Administrator!
                </h2>

                <p>
                    Manage your employees efficiently and effectively.
                </p>

            </div>


            <div class="current-date">

                📅

                <?php
                echo date("F d, Y");
                ?>

            </div>

        </div>



        <!-- STATISTICS -->

        <div class="stats">


            <!-- TOTAL EMPLOYEES -->

            <a
                href="index.php"
                class="stat-card total"
            >

                <div class="stat-top">

                    <div class="stat-icon blue-icon">
                        👥
                    </div>

                    <div class="stat-arrow">
                        →
                    </div>

                </div>


                <div class="stat-content">

                    <span class="stat-label">
                        Total Employees
                    </span>

                    <strong class="stat-number">
                        <?php echo $totalEmployees; ?>
                    </strong>

                    <span class="stat-link">
                        View all employees →
                    </span>

                </div>

            </a>



            <!-- ACTIVE EMPLOYEES -->

            <a
                href="index.php?status=Active"
                class="stat-card active-card"
            >

                <div class="stat-top">

                    <div class="stat-icon green-icon">
                        ✓
                    </div>

                    <div class="stat-arrow">
                        →
                    </div>

                </div>


                <div class="stat-content">

                    <span class="stat-label">
                        Active Employees
                    </span>

                    <strong class="stat-number">
                        <?php echo $activeEmployees; ?>
                    </strong>

                    <span class="stat-link">
                        View active employees →
                    </span>

                </div>

            </a>



            <!-- INACTIVE EMPLOYEES -->

            <a
                href="index.php?status=Inactive"
                class="stat-card inactive-card"
            >

                <div class="stat-top">

                    <div class="stat-icon red-icon">
                        −
                    </div>

                    <div class="stat-arrow">
                        →
                    </div>

                </div>


                <div class="stat-content">

                    <span class="stat-label">
                        Inactive Employees
                    </span>

                    <strong class="stat-number">
                        <?php echo $inactiveEmployees; ?>
                    </strong>

                    <span class="stat-link">
                        View inactive employees →
                    </span>

                </div>

            </a>



            <!-- DEPARTMENTS -->

            <a
                href="departments.php?view=departments"
                class="stat-card department-card"
            >

                <div class="stat-top">

                    <div class="stat-icon purple-icon">
                        🏢
                    </div>

                    <div class="stat-arrow">
                        →
                    </div>

                </div>


                <div class="stat-content">

                    <span class="stat-label">
                        Departments
                    </span>

                    <strong class="stat-number">
                        <?php echo $totalDepartments; ?>
                    </strong>

                    <span class="stat-link">
                        View departments →
                    </span>

                </div>

            </a>


        </div>



        <?php if ($view === 'departments'): ?>


        <!-- DEPARTMENT VIEW -->

        <div class="table-card">


            <div class="department-header">

                <div>

                    <h2>
                        Department Overview
                    </h2>

                    <p>
                        View employee distribution by department.
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

                            <th>Department</th>
                            <th>Total Employees</th>
                            <th>Active</th>
                            <th>Inactive</th>
                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (!empty($departmentSummary)): ?>


                        <?php foreach ($departmentSummary as $dept): ?>


                            <tr>

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


                                <td>

                                    <span class="count-badge">

                                        <?php
                                        echo (int)$dept['employee_count'];
                                        ?>

                                        Employee(s)

                                    </span>

                                </td>


                                <td>

                                    <span class="active-count">

                                        ●

                                        <?php
                                        echo (int)$dept['active_count'];
                                        ?>

                                    </span>

                                </td>


                                <td>

                                    <span class="inactive-count">

                                        ●

                                        <?php
                                        echo (int)$dept['inactive_count'];
                                        ?>

                                    </span>

                                </td>


                                <td>

                                    <a
                                        href="index.php?department=<?php echo urlencode($dept['department']); ?>"
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


        <?php else: ?>


        <!-- EMPLOYEE TABLE -->

        <div class="table-card">


            <!-- SEARCH / FILTER -->

            <form
                method="GET"
                action="index.php"
                class="search-area"
                id="employeeFilterForm"
            >


                <!-- SEARCH -->

                <div class="search-box">

                    <input
                        type="text"
                        name="search"
                        id="searchInput"
                        placeholder="Search by name, employee ID, email, department..."
                        value="<?php
                            echo htmlspecialchars($search);
                        ?>"
                    >

                </div>



                <!-- DEPARTMENT FILTER -->

                <select
                    name="department"
                    id="departmentFilter"
                    class="filter-select"
                    title="Filter by Department"
                >

                    <option value="">
                        All Departments
                    </option>


                    <?php foreach ($departmentList as $dept): ?>

                        <option
                            value="<?php echo htmlspecialchars($dept); ?>"
                            <?php
                            echo ($department === $dept)
                                ? 'selected'
                                : '';
                            ?>
                        >

                            <?php
                            echo htmlspecialchars($dept);
                            ?>

                        </option>

                    <?php endforeach; ?>

                </select>



                <!-- STATUS FILTER -->

                <select
                    name="status"
                    id="statusFilter"
                    class="filter-select"
                    title="Filter by Status"
                >

                    <option value="">
                        All Status
                    </option>


                    <option
                        value="Active"
                        <?php
                        echo ($status === 'Active')
                            ? 'selected'
                            : '';
                        ?>
                    >
                        Active
                    </option>


                    <option
                        value="Inactive"
                        <?php
                        echo ($status === 'Inactive')
                            ? 'selected'
                            : '';
                        ?>
                    >
                        Inactive
                    </option>

                </select>



                <!-- SEARCH BUTTON -->

                <button
                    type="submit"
                    class="btn-search"
                >
                    🔍 Search
                </button>



                <!-- CLEAR BUTTON -->

                <?php if ($hasFilters): ?>

                    <a
                        href="index.php"
                        class="btn-clear"
                        title="Clear all search and filters"
                    >
                        ✕ Clear
                    </a>

                <?php endif; ?>



                <!-- ADD EMPLOYEE -->

                <a
                    href="add_employee.php"
                    class="btn-add"
                >
                    + Add Employee
                </a>



                <!-- ACTIVE FILTER MESSAGE -->

                <?php if ($hasFilters): ?>

                    <div class="filter-active-label">

                        <span class="filter-active-dot"></span>

                        Filter is active —
                        <?php echo $employees->num_rows; ?>
                        employee(s) found.

                    </div>

                <?php endif; ?>


            </form>



            <!-- TABLE -->

            <div class="table-wrapper">

                <table>


                    <thead>

                        <tr>

                            <th>Employee ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Hire Date</th>
                            <th>Salary</th>
                            <th>Status</th>
                            <th>Action</th>

                        </tr>

                    </thead>



                    <tbody>


                    <?php if ($employees && $employees->num_rows > 0): ?>


                        <?php while ($employee = $employees->fetch_assoc()): ?>


                            <?php

                            /* BUILD FULL NAME */

                            $fullName = trim(
                                ($employee['first_name'] ?? '') . ' ' .
                                ($employee['middle_name'] ?? '') . ' ' .
                                ($employee['last_name'] ?? '') . ' ' .
                                ($employee['suffix'] ?? '')
                            );

                            ?>


                            <tr>


                                <!-- EMPLOYEE ID -->

                                <td>

                                    <span class="employee-id">

                                        <?php
                                        echo htmlspecialchars(
                                            $employee['employee_id'] ?? ''
                                        );
                                        ?>

                                    </span>

                                </td>



                                <!-- FULL NAME -->

                                <td>

                                    <div class="employee-name">

                                        <?php
                                        echo htmlspecialchars(
                                            $fullName
                                        );
                                        ?>

                                    </div>

                                </td>



                                <!-- EMAIL -->

                                <td>

                                    <span class="employee-email">

                                        <?php
                                        echo htmlspecialchars(
                                            $employee['email'] ?? ''
                                        );
                                        ?>

                                    </span>

                                </td>



                                <!-- DEPARTMENT -->

                                <td>

                                    <span class="department-name">

                                        <?php
                                        echo htmlspecialchars(
                                            $employee['department'] ?? ''
                                        );
                                        ?>

                                    </span>

                                </td>



                                <!-- POSITION -->

                                <td>

                                    <span class="position-name">

                                        <?php
                                        echo htmlspecialchars(
                                            $employee['position'] ?? ''
                                        );
                                        ?>

                                    </span>

                                </td>



                                <!-- HIRE DATE -->

                                <td>

                                    <?php

                                    if (!empty($employee['hire_date'])) {

                                        echo date(
                                            "M d, Y",
                                            strtotime(
                                                $employee['hire_date']
                                            )
                                        );

                                    } else {

                                        echo "—";

                                    }

                                    ?>

                                </td>



                                <!-- SALARY -->

                                <td>

                                    <span class="salary">

                                        ₱<?php

                                        echo number_format(
                                            (float)(
                                                $employee['salary'] ?? 0
                                            ),
                                            2
                                        );

                                        ?>

                                    </span>

                                </td>



                                <!-- STATUS -->

                                <td>

                                    <?php

                                    if (
                                        ($employee['status'] ?? '')
                                        === 'Active'
                                    ):

                                    ?>

                                        <span class="status active">
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span class="status inactive">
                                            Inactive
                                        </span>

                                    <?php endif; ?>

                                </td>



                                <!-- ACTION -->

                                <td>

                                    <div class="actions">


                                        <!-- EDIT -->

                                        <a
                                            href="edit_employee.php?id=<?php
                                                echo (int)$employee['id'];
                                            ?>"
                                            class="action-btn edit-btn"
                                            title="Edit Employee"
                                        >
                                            ✏️
                                        </a>


                                        <!-- DELETE -->

                                        <a
                                            href="delete_employee.php?id=<?php
                                                echo (int)$employee['id'];
                                            ?>"
                                            class="action-btn delete-btn"
                                            title="Delete Employee"
                                            onclick="return confirm('Are you sure you want to delete this employee?');"
                                        >
                                            🗑️
                                        </a>


                                    </div>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="9"
                                style="text-align:center; padding:55px;"
                            >

                                <div class="empty-state">

                                    <div class="empty-icon">
                                        👥
                                    </div>

                                    <h3>
                                        No Employees Found
                                    </h3>

                                    <p>
                                        Try changing your search
                                        or filter options.
                                    </p>

                                    <?php if ($hasFilters): ?>

                                        <a
                                            href="index.php"
                                            class="btn-clear"
                                            style="margin-top:15px;"
                                        >
                                            ✕ Clear Filters
                                        </a>

                                    <?php endif; ?>

                                </div>

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>



            <!-- FOOTER -->

            <div class="table-footer">

                <span>

                    Showing

                    <strong>

                        <?php
                        echo $employees
                            ? $employees->num_rows
                            : 0;
                        ?>

                    </strong>

                    employee(s)

                </span>


                <span>
                    Employee Information System
                </span>

            </div>


        </div>


        <?php endif; ?>


    </section>


</main>



<!-- DELETE SUCCESS MESSAGE -->

<?php if ($deleteSuccess): ?>

<script>

    window.addEventListener('DOMContentLoaded', function () {

        alert('Delete Employee Successful');

        const url = new URL(window.location.href);

        url.searchParams.delete('deleted');

        window.history.replaceState(
            {},
            document.title,
            url.pathname + url.search
        );

    });

</script>

<?php endif; ?>



<!-- AUTOMATIC DEPARTMENT / STATUS FILTER -->

<script>

document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('employeeFilterForm');

    const departmentFilter =
        document.getElementById('departmentFilter');

    const statusFilter =
        document.getElementById('statusFilter');


    /*
     * AUTOMATICALLY SEARCH WHEN
     * DEPARTMENT IS CHANGED
     */

    if (departmentFilter) {

        departmentFilter.addEventListener('change', function () {

            form.submit();

        });

    }


    /*
     * AUTOMATICALLY SEARCH WHEN
     * STATUS IS CHANGED
     */

    if (statusFilter) {

        statusFilter.addEventListener('change', function () {

            form.submit();

        });

    }

});

</script>


</body>

</html>