CREATE DATABASE IF NOT EXISTS employee_system;

USE employee_system;

CREATE TABLE employees (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    employee_id VARCHAR(30) NOT NULL UNIQUE,

    first_name VARCHAR(100) NOT NULL,

    last_name VARCHAR(100) NOT NULL,

    email VARCHAR(150) NOT NULL,

    phone VARCHAR(30),

    department VARCHAR(100),

    position VARCHAR(100),

    hire_date DATE,

    salary DECIMAL(12,2) NOT NULL DEFAULT 0.00,

    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO employees
(
    employee_id,
    first_name,
    last_name,
    email,
    phone,
    department,
    position,
    hire_date,
    salary,
    status
)

VALUES

(
    'EMP-001',
    'Juan',
    'Dela Cruz',
    'juan@example.com',
    '09171234567',
    'Human Resources',
    'HR Officer',
    '2024-01-15',
    20000.00,
    'Active'
),

(
    'EMP-002',
    'Maria',
    'Santos',
    'maria@example.com',
    '09181234567',
    'Information Technology',
    'System Administrator',
    '2023-08-10',
    20000.00,
    'Active'
),

(
    'EMP-003',
    'Carlos',
    'Reyes',
    'carlos@example.com',
    '09191234567',
    'Finance',
    'Accountant',
    '2022-05-20',
    20000.00,
    'Inactive'
);
