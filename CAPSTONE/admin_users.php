<?php
include "myconnector.php";
session_start();
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