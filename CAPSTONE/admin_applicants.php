<?php
include "myconnector.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    if (isset($_POST['approve'])) {
        $conn->query("UPDATE users SET status='approved' WHERE user_id=$user_id");
    } elseif (isset($_POST['reject'])) {
        $conn->query("UPDATE users SET status='rejected' WHERE user_id=$user_id");
    }
    header("Location: admin_applicants.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar">
    <!-- Burger Menu -->
    <div class="burger-menu" onclick="toggleSidebar()">
      <div></div>
      <div></div>
      <div></div>
    </div>
    <!-- Title -->
    <div class="nav-center">Classix</div>
    <!-- User Info -->
    <div class="user-info">
      <img src="Images/user-svgrepo-com.svg" alt="User Icon">
    </div>
  </nav>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <ul>
        <li><a href="admin_landing.php"><img src="Images/dashboard-svgrepo-com.svg" alt="Home Icon">Dashboard</a></li>
        <li><a href="admin_users.php"><img src="Images/user-svgrepo-com.svg" alt="Features Icon">Users</a></li>
        <li><a href="admin_modules.php"><img src="Images/book-svgrepo-com.svg" alt="About-Us Icon">Modules</a></li>
        <li><a href="admin_applicants.php"><img src="Images/users-svgrepo-com.svg" alt="About-Us Icon">Applicants</a></li>
     </ul>
  </div>
 
  <div class="main-content" style="margin-left:260px; margin-top:50px; padding:32px;">
    <h2>Applicants List</h2>
    <div class="table-wrapper">
      <table style="width:100%; border-collapse:collapse;">
          <thead>
              <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Contact Number</th>
                  <th>Applied At</th>
                  <th>Action</th>
              </tr>
          </thead>
          <tbody>
          <?php
          $result = $conn->query("SELECT user_id, first_name, last_name, email, contact_number, created_at FROM users WHERE role='tutor' AND status='pending'");
          if ($result && $result->num_rows > 0):
              while ($row = $result->fetch_assoc()):
          ?>
              <tr>
                  <td><?=htmlspecialchars($row['first_name'].' '.$row['last_name'])?></td>
                  <td><?=htmlspecialchars($row['email'])?></td>
                  <td><?=htmlspecialchars($row['contact_number'])?></td>
                  <td><?=htmlspecialchars($row['created_at'])?></td>
                  <td>
                      <form method="post" style="display:inline;">
                          <input type="hidden" name="user_id" value="<?=$row['user_id']?>">
                          <button name="approve" class="btn btn-success btn-sm">Approve</button>
                          <button name="reject" class="btn btn-danger btn-sm">Reject</button>
                      </form>
                  </td>
              </tr>
          <?php endwhile; else: ?>
              <tr><td colspan="4">No pending tutor applicants.</td></tr>
          <?php endif; ?>
          </tbody>
      </table>
    </div>  
</div>

  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      sidebar.classList.toggle('active');
    }

    document.addEventListener('click', function (event) {
      const sidebar = document.getElementById('sidebar');
      const burgerMenu = document.querySelector('.burger-menu');

      // Close sidebar if clicked outside
      if (!sidebar.contains(event.target) && !burgerMenu.contains(event.target)) {
        sidebar.classList.remove('active');
      }
    });

document.querySelectorAll('.dropdown-toggle').forEach(item => {
    item.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent the default anchor behavior
        const dropdownMenu = this.nextElementSibling; // Get the dropdown menu
        dropdownMenu.classList.toggle('active'); // Toggle the active class
    });
});
// Close the dropdown if clicked outside
document.addEventListener('click', function(event) {
    const dropdowns = document.querySelectorAll('.dropdown-menu');
    dropdowns.forEach(dropdown => {
        if (!dropdown.previousElementSibling.contains(event.target) && dropdown.classList.contains('active')) {
            dropdown.classList.remove('active'); // Close the dropdown if clicked outside
        }
    });
});

  </script>
</body>
</html>