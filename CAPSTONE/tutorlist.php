<?php
include "myconnector.php";
session_start();

echo '<div class="user-list-outer">';
echo '<h2>Registered Tutors</h2>';

$query = "SELECT user_id, first_name, last_name, email, role FROM users WHERE role IN ('tutor') ORDER BY role, first_name";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="user-card">';
        echo '  <div class="user-card-name">';
        echo '    <img src="Images/user-svgrepo-com.svg" alt="User Icon">';
        echo '    <span>' . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . '</span>';
        echo '  </div>';
        echo '  <div class="user-card-actions">';
        echo '    <button class="user-card-btn invite-btn apply-btn" data-tutor-id="' . $row['user_id'] . '">Apply</button>';
        echo '    <button class="user-card-btn view-btn">View</button>';
        echo '  </div>';
        echo '</div>';
    }
} else {
    echo '<p>No students or parents found.</p>';
}
echo '</div>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="tutorlist.css">
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
    <div class="nav-center">ClassXic</div>
    <!-- User Info -->
        <div class="user-info">
            <span><?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
            <img src="Images/user-svgrepo-com.svg" alt="User Icon">
        </div>
  </nav>

  <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <ul>
            <li><a href="landingpage.php"><img src="Images/home-svgrepo-com.svg" alt="Home Icon"> Home</a></li>
            <li><a href="calendar.php"><img src="Images/calendar-month-svgrepo-com.svg" alt="Calendar Icon"> Calendar</a></li>
            <li><a href="studentmodule.php"><img src="Images/book-svgrepo-com.svg" alt="Modules Icon"> Modules</a></li>
            <li><a href="tutorlist.php"><img src="Images/user-svgrepo-com.svg" alt="Tutors Icon"> Tutor</a></li>
           <!-- <li><a href="progress.php"><img src="Images/progress-svgrepo-com.svg" alt="Progress Icon">Progress</a></li>-->
            <li>
                <a href="#" class="dropdown-toggle">-Option-</a>
                <ul class="dropdown-menu">
                    <li><a href="#features-section"><img src="Images/idea-svgrepo-com.svg" alt="Features Icon">Features</a></li>
                    <li><a href="#about-us"><img src="Images/about-filled-svgrepo-com.svg" alt="About-Us Icon">About Us</a></li>
                    <li><a href="#settings"><img src="Images/settings-2-svgrepo-com.svg" alt="Settings Icon"> Settings</a></li>
                    <li><a href="logout.php"><img src="Images/logout-svgrepo-com.svg" alt="Logout Icon">Log out</a></li>
                </ul>
            <li>
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

document.querySelectorAll('.apply-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tutorId = this.getAttribute('data-tutor-id');
        fetch('apply_tutor.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'tutor_id=' + encodeURIComponent(tutorId)
        })
        .then(res => res.text())
        .then(msg => alert(msg));
    });
});
  </script>
</body>
</html>