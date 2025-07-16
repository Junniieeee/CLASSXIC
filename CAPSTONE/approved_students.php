<?php
include "myconnector.php";
session_start();

// Query for approved/enrolled students (adjust table/column names as needed)
$query = "
    SELECT u.first_name, u.last_name, u.email, ta.applied_at
    FROM tutor_applications ta
    JOIN users u ON ta.student_id = u.user_id
    WHERE ta.status = 'approved'
    ORDER BY ta.applied_at DESC
";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approved/Enrolled Students</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f9f9f9; margin: 0; }
        h2 { margin-top: 0; background: #fffde7; padding: 16px 24px; border-radius: 8px 8px 0 0; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 12px 8px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #fffde7; }
        tr:last-child td { border-bottom: none; }
        @media (max-width: 700px) {
            table, thead, tbody, th, td, tr { display: block; }
            th, td { padding: 8px 4px; }
            tr { margin-bottom: 1rem; }
        }
    </style>
</head>
<body>
    <h2>Approved/Enrolled Students</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Approved At</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['applied_at']); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="3">No approved/enrolled students found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>