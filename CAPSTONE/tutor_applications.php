<?php
include "myconnector.php";
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tutor') {
    die("Only tutors can view applications.");
}

$tutor_id = $_SESSION['user_id'];

$query = "SELECT ta.id, u.first_name, u.last_name, u.email, ta.status, ta.applied_at
          FROM tutor_applications ta
          JOIN users u ON ta.student_id = u.user_id
          WHERE ta.tutor_id = ?
          ORDER BY ta.applied_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $tutor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_id'], $_POST['action'])) {
    $app_id = intval($_POST['app_id']);
    $action = $_POST['action'] === 'approve' ? 'approved' : 'rejected';
    $update = $conn->prepare("UPDATE tutor_applications SET status=? WHERE id=? AND tutor_id=?");
    $update->bind_param("sii", $action, $app_id, $tutor_id);
    $update->execute();
    // Optional: Add a success message or redirect to avoid resubmission
    header("Location: tutor_applications.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Applications</title>
    <link rel="stylesheet" href="studentlist.css">
</head>
<body>
    <h2>Student Applications</h2>
    <table class="app-table">
        <tr>
            <th>Name</th>
            <th>Gmail</th>
            <th>Applied At</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['applied_at']) ?></td>
            <td>
                <?php if ($row['status'] === 'pending'): ?>
                    <span class="status-badge status-pending">Pending</span>
                <?php elseif ($row['status'] === 'approved'): ?>
                    <span class="status-badge status-approved">Approved</span>
                <?php else: ?>
                    <span class="status-badge status-rejected">Rejected</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($row['status'] === 'pending'): ?>
                    <form method="post" action="tutor_applications.php" style="display:inline;">
                        <input type="hidden" name="app_id" value="<?= $row['id'] ?>">
                        <button type="submit" name="action" value="approve" class="btn-approve">Approve</button>
                        <button type="submit" name="action" value="reject" class="btn-reject">Reject</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>