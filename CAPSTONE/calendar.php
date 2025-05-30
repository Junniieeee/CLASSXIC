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
    <link rel="stylesheet" href="calendar.css">
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
        <li><a href="#"><img src="Images/home-svgrepo-com.svg" alt="Home Icon"> Home</a></li>
       <li><a href="#"><img src="Images/idea-svgrepo-com.svg" alt="Features Icon">Features</a></li>
        <li><a href="#"><img src="Images/about-filled-svgrepo-com.svg" alt="About-Us Icon">About Us</a></li>
        <li>
            <a href="#" class="dropdown-toggle">Here</a>
            <ul class="dropdown-menu">
                <li><a href="#"><img src="Images/calendar-month-svgrepo-com.svg" alt="Calendar Icon"> Calendar</a></li>
                <li><a href="#"><img src="Images/book-svgrepo-com.svg" alt="Modules Icon"> Modules</a></li>
                <li><a href="#"><img src="Images/user-svgrepo-com.svg" alt="Tutors Icon"> Tutor</a></li>
                <li><a href="#"><img src="Images/progress-svgrepo-com.svg" alt="Progress Icon">Progress</a></li>
                <li><a href="#"><img src="Images/settings-2-svgrepo-com.svg" alt="Settings Icon"> Settings</a></li>
            </ul>
        <li>
     </ul>
  </div>
    <div class="container">
        <h1 class="text-center mb-4">Calendar</h1>
        <div id="calendar"></div>
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


    document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    // Get user role from PHP session (add this line in your <head> or before the script)
    const userRole = "<?php echo $_SESSION['role'] ?? ''; ?>";

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        themeSystem: 'bootstrap5',
        selectable: userRole === 'tutor',
        editable: userRole === 'tutor',
        eventSources: [
            {
                url: 'calendar_api.php',
                method: 'GET',
                failure: () => { alert('There was an error fetching events!'); }
            }
        ],

        // TUTOR: CREATE slot
        select: function(info) {
            if (userRole !== 'tutor') return;
            const day = prompt('Enter session name (e.g. Math, English):');
            const location = prompt('Enter location/mode (e.g. Zoom):');
            const from = prompt('Start time (HH:MM, 24h):', '08:00');
            const to = prompt('End time (HH:MM, 24h):', '09:00');
            if (day && from && to && location) {
                fetch('calendar_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'create',
                        day: day,
                        session_date: info.startStr,
                        available_from: from + ':00',
                        available_to: to + ':00',
                        location_mode: location
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) calendar.refetchEvents();
                    else alert(data.message || 'Failed to create slot');
                });
            }
            calendar.unselect();
        },

        // TUTOR: UPDATE/DELETE slot
        eventClick: function(info) {
            const eventObj = info.event;
            const props = eventObj.extendedProps;
            if (userRole === 'tutor' && props.tutor_id == "<?php echo $_SESSION['user_id'] ?? 0; ?>") {
                const action = prompt(
                    `Edit session name or type "delete" to remove:\nCurrent: ${eventObj.title.replace(/^(Available: |Booked: )/, '')}`,
                    eventObj.title.replace(/^(Available: |Booked: )/, '')
                );
                if (action === null) return;
                if (action.toLowerCase() === 'delete') {
                    if (confirm('Delete this slot?')) {
                        fetch('calendar_api.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ action: 'delete', id: eventObj.id })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) calendar.refetchEvents();
                            else alert(data.message || 'Failed to delete');
                        });
                    }
                } else if (action !== eventObj.title.replace(/^(Available: |Booked: )/, '')) {
                    const location = prompt('Edit location/mode:', props.location_mode);
                    const from = prompt('Edit start time (HH:MM, 24h):', eventObj.start.toISOString().substr(11,5));
                    const to = prompt('Edit end time (HH:MM, 24h):', eventObj.end ? eventObj.end.toISOString().substr(11,5) : '');
                    fetch('calendar_api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'update',
                            id: eventObj.id,
                            day: action,
                            session_date: eventObj.start.toISOString().split('T')[0],
                            available_from: from + ':00',
                            available_to: to + ':00',
                            location_mode: location
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) calendar.refetchEvents();
                        else alert(data.message || 'Failed to update');
                    });
                }
            }
            // STUDENT: BOOK/CANCEL
            else if (userRole === 'student') {
                if (!props.is_booked || props.student_id == "<?php echo $_SESSION['user_id'] ?? 0; ?>") {
                    if (!props.is_booked) {
                        if (confirm('Book this session?')) {
                            fetch('calendar_api.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ action: 'book', id: eventObj.id })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) calendar.refetchEvents();
                                else alert(data.message || 'Failed to book');
                            });
                        }
                    } else if (props.student_id == "<?php echo $_SESSION['user_id'] ?? 0; ?>") {
                        if (confirm('Cancel your booking?')) {
                            fetch('calendar_api.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ action: 'cancel', id: eventObj.id })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) calendar.refetchEvents();
                                else alert(data.message || 'Failed to cancel');
                            });
                        }
                    }
                }
            }
        },

        // TUTOR: DRAG & DROP (update date/time)
        eventDrop: function(info) {
            if (userRole !== 'tutor') return;
            fetch('calendar_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'update',
                    id: info.event.id,
                    day: info.event.title.replace(/^(Available: |Booked: )/, ''),
                    session_date: info.event.start.toISOString().split('T')[0],
                    available_from: info.event.start.toISOString().substr(11,8),
                    available_to: info.event.end ? info.event.end.toISOString().substr(11,8) : null,
                    location_mode: info.event.extendedProps.location_mode
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) calendar.refetchEvents();
                else alert(data.message || 'Failed to update');
            });
        }
    });

    calendar.render();
});
  </script>
</body>
</html>