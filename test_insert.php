<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

$sql = "INSERT INTO events (title, description, event_date, created_by, user_email, status) VALUES (?, ?, ?, ?, ?, 'pending')";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$title = 'Test event';
$description = 'This is a test description.';
$event_date = date('Y-m-d', strtotime('+1 day'));
$created_by = 'admin@example.com';

$stmt->bind_param("sssss", $title, $description, $event_date, $created_by, $created_by);

if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
} else {
    echo "Event inserted successfully!";
}

$stmt->close();
$conn->close();
