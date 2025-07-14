<?php
include "myconnector.php";
session_start();

// 1. Get the student's approved tutor(s)
$student_id = $_SESSION['user_id'];
$tutor_ids = [];
$tutor_query = "SELECT tutor_id FROM tutor_applications WHERE student_id=? AND status='approved'";
$stmt = $conn->prepare($tutor_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$tutor_result = $stmt->get_result();
while ($row = $tutor_result->fetch_assoc()) {
    $tutor_ids[] = $row['tutor_id'];
}

// 2. If no approved tutors, show message
if (empty($tutor_ids)) {
    echo '<p>No approved tutor modules available. Please apply and get approved by a tutor.</p>';
} else {
    // 3. Get tutor names for display (optional)
    $tutor_ids_str = implode(',', array_map('intval', $tutor_ids));
    $tutor_names = [];
    $name_query = "SELECT user_id, first_name, last_name FROM users WHERE user_id IN ($tutor_ids_str)";
    $name_result = $conn->query($name_query);
    while ($row = $name_result->fetch_assoc()) {
        $tutor_names[$row['user_id']] = $row['first_name'] . ' ' . $row['last_name'];
    }

    // 4. Show only modules uploaded by those tutors and approved
    $query = "SELECT material_id, title, uploaded_by, approved_at, file_url, uploaded_by_id 
              FROM learning_materials 
              WHERE is_approved = 1 AND uploaded_by_id IN ($tutor_ids_str)
              ORDER BY approved_at DESC";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo '<h2>Hello ' . htmlspecialchars($_SESSION['first_name']) . '</h2>';
        echo '<div class="module-list">';
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<div class="module-item">';
            // Module Header
            echo '<div class="module-header">';
            echo '  <div class="module-user">';
            echo '      <img src="Images/user-svgrepo-com.svg" alt="User Icon" class="module-user-icon">';
            // Show tutor name from $tutor_names
            echo '      <span class="module-user-name">' . htmlspecialchars($tutor_names[$row['uploaded_by_id']] ?? $row['uploaded_by']) . '</span>';
            echo '      <img src="Images/verified.png" alt="Verified" class="module-verified-icon">';
            echo '  </div>';
            echo '  <div class="module-date">' . date("F j, Y", strtotime($row['approved_at'])) . '</div>';
            echo '</div>';
            // Module Body
            echo '<div class="module-body">';
            echo '  <div class="module-title">' . htmlspecialchars($row['title']) . '</div>';
            echo '  <div class="module-actions">';
            echo '      <a href="modules.php?file_url=' . urlencode($row['file_url']) . '" class="module-edit">View</a>';
            echo '  </div>';
            echo '</div>';
            echo '</div>'; // .module-item
        }
        echo '</div>'; // .module-list
    } else {
        echo '<p>No materials found from your approved tutor(s).</p>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="tutormodule.css">
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

    // Upload box modal
    document.getElementById('open-upload-box').addEventListener('click', function() {
      document.getElementById('upload-box-modal').style.display = 'flex';
    });

    // Close modal when clicking outside of the modal content
    window.addEventListener('click', function(event) {
      const modal = document.getElementById('upload-box-modal');
      if (event.target == modal) {
        modal.style.display = 'none';
      }
    });
  </script>
</body>
</html>