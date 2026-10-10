<?php

include "config.php";

$username = "admin";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$full_name = "System Administrator";

$stmt = $conn->prepare("
    INSERT INTO users (username, password, full_name)
    VALUES (?, ?, ?)
");

$stmt->bind_param(
    "sss",
    $username,
    $password,
    $full_name
);

if ($stmt->execute()) {
    echo "Admin account created successfully.";
} else {
    echo "Error: " . $stmt->error;
}

?>