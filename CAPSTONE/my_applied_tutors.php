<?php

include "myconnector.php";
session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
if (!$user_id) {
    echo "<p>You must be logged in to view your applications.</p>";
    exit;
}

// Query: show tutors the student has applied to
$query = "
    SELECT u.first_name, u.last_name, u.email, ta.status, ta.applied_at
    FROM tutor_applications ta
    JOIN users u ON ta.tutor_id = u.user_id
    WHERE ta.student_id = $user_id
    ORDER BY ta.applied_at DESC
";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Tutor Applications</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f9f9f9; margin: 0; }
        h2 { margin-top: 0; background: #fffde7; padding: 16px 24px; border-radius: 8px 8px 0 0; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 12px 8px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #fffde7; }
        tr:last-child td { border-bottom: none; }
        .status-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 16px;
            font-weight: bold;
            font-size: 14px;
        }
        .status-pending { background: #aaa; color: #fff; }
        .status-approved { background: #4caf50; color: #fff; }
        .status-rejected { background: #e53935; color: #fff; }
        @media (max-width: 700px) {
            table, thead, tbody, th, td, tr { display: block; }
            th, td { padding: 8px 4px; }
            tr { margin-bottom: 1rem; }
        }
    </style>
</head>
<body>
    <h2>My Tutor Applications</h2>
    <table>
        <thead>
            <tr>
                <th>Tutor Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Applied At</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <?php
                        $status = strtolower($row['status']);
                        $badgeClass = "status-badge status-$status";
                        echo "<span class=\"$badgeClass\">" . ucfirst($status) . "</span>";
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['applied_at']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">You have not applied to any tutors yet.</td></tr>
        <?php endif; ?>
        </tbody>