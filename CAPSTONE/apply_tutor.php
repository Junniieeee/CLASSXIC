<?php
include "myconnector.php";
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Only students can apply.");
}

$tutor_id = intval($_POST['tutor_id'] ?? 0);
$student_id = $_SESSION['user_id'];

if ($tutor_id > 0) {
    // Prevent duplicate applications
    $check = $conn->prepare("SELECT status FROM tutor_applications WHERE tutor_id=? AND student_id=? ORDER BY id DESC LIMIT 1");
    $check->bind_param("ii", $tutor_id, $student_id);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['status'] !== 'rejected') {
            echo "You have already applied to this tutor.";
            exit;
        }
        // If rejected, allow re-application by inserting a new row
    }

    $stmt = $conn->prepare("INSERT INTO tutor_applications (tutor_id, student_id, status) VALUES (?, ?, 'pending')");
    $stmt->bind_param("ii", $tutor_id, $student_id);
    if ($stmt->execute()) {
        echo "Application sent!";
    } else {
        echo "Failed to apply.";
    }
} else {
    echo "Invalid tutor.";
}
?>