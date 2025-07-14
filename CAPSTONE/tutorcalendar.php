<?php
// --- PHP: Include database connection and start session ---
include "myconnector.php";
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- Link to calendar CSS -->
    <link rel="stylesheet" href="calendar.css">
</head>
<body>
  <!-- ================= NAVBAR SECTION ================= -->
  <nav class="navbar">
    <!-- Burger Menu for Sidebar Toggle -->
    <div class="burger-menu" onclick="toggleSidebar()">
      <div></div>
      <div></div>
      <div></div>
    </div>
    <!-- Website Title -->
    <div class="nav-center">Classix</div>
    <!-- User Info (Name and Icon) -->
    <div class="user-info">
        <span><?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
        <img src="Images/user-svgrepo-com.svg" alt="User Icon">
    </div>
  </nav>

  <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <ul>
            <li><a href="tutorlanding.php"><img src="Images/home-svgrepo-com.svg" alt="Home Icon"> Home</a></li>
            <li><a href="tutorcalendar.php"><img src="Images/calendar-month-svgrepo-com.svg" alt="Calendar Icon"> Calendar</a></li>
            <li><a href="tutormodule.php"><img src="Images/book-svgrepo-com.svg" alt="Modules Icon"> Modules</a></li>
            <li><a href="studentlist.php"><img src="Images/user-svgrepo-com.svg" alt="Students Icon"> Students</a></li>
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
    <!-- Notification Modal -->
    <div id="calendarNotifyModal" class="modal" style="display:none;">
    <div class="modal-content calendar-notify-modal-content">
        <div class="calendar-notify-checkmark">
        <svg width="80" height="80" viewBox="0 0 120 120">
            <circle cx="60" cy="60" r="54" fill="none" stroke="#19d219" stroke-width="4"/>
            <polyline points="40,65 55,80 80,45" fill="none" stroke="#19d219" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        </div>
        <h2 id="calendarNotifyTitle" class="calendar-notify-title"></h2>
        <p id="calendarNotifyMsg" class="calendar-notify-message"></p>
    </div>
    </div>


  <!-- ================= CALENDAR SECTION ================= -->
  <div class="container">
      <h1 class="text-center mb-4">Calendar</h1>
      <div id="calendar"></div>
  </div>

  <!-- ================= JAVASCRIPT SECTION ================= -->
  <!-- FullCalendar JS CDN -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <script>
    // --- Sidebar Toggle Function ---
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      sidebar.classList.toggle('active');
    }

    // --- Close Sidebar When Clicking Outside ---
    document.addEventListener('click', function (event) {
      const sidebar = document.getElementById('sidebar');
      const burgerMenu = document.querySelector('.burger-menu');
      if (!sidebar.contains(event.target) && !burgerMenu.contains(event.target)) {
        sidebar.classList.remove('active');
      }
    });

    // --- Dropdown Menu Toggle ---
    document.querySelectorAll('.dropdown-toggle').forEach(item => {
        item.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent default anchor behavior
            const dropdownMenu = this.nextElementSibling; // Get dropdown menu
            dropdownMenu.classList.toggle('active'); // Toggle active class
        });
    });

    // --- Close Dropdown If Clicked Outside ---
    document.addEventListener('click', function(event) {
        const dropdowns = document.querySelectorAll('.dropdown-menu');
        dropdowns.forEach(dropdown => {
            if (!dropdown.previousElementSibling.contains(event.target) && dropdown.classList.contains('active')) {
                dropdown.classList.remove('active'); // Close dropdown if clicked outside
            }
        });
    });

    // --- FULLCALENDAR INITIALIZATION AND LOGIC ---
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');
        // Get user role from PHP session
        const userRole = "<?php echo $_SESSION['role'] ?? ''; ?>";

        // Initialize FullCalendar
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            themeSystem: 'bootstrap5',
            selectable: true, // Allow both tutor and student to create slots
            editable: userRole === 'tutor',   // Only tutors can drag/drop events (optional: set to true for all if you want)
            eventSources: [
                {
                    url: 'calendar_api.php', // Fetch events from API
                    method: 'GET',
                    failure: () => { alert('There was an error fetching events!'); }
                }
            ],

            // --- CREATE SLOT (for both tutor and student now) ---
            select: async function(info) {
                // Fetch all users except self
                const res = await fetch('calendar_api.php?get_users=1');
                const users = await res.json();
                if (!Array.isArray(users) || users.length === 0) {
                    alert('No users found.');
                    return;
                }
                let userOptions = users.map(u => `<option value="${u.user_id}|${u.role}">${u.name} (${u.role})</option>`).join('');
                let dropdownHtml = `<select id="userSelect">${userOptions}</select>`;
                const wrapper = document.createElement('div');
                wrapper.innerHTML = `
                    <div class="calendar-create-modal">
                        <h3 class="calendar-create-title">Choose User</h3>
                        ${dropdownHtml}
                        <label class="calendar-create-label">Session Name:</label>
                        <input id="sessionName" class="calendar-create-input">
                        <label class="calendar-create-label">Location/Mode:</label>
                        <input id="locationMode" class="calendar-create-input">
                        <label class="calendar-create-label">Start Time: (HH : MM)</label>
                        <input id="startTime" class="calendar-create-input" value="08:00">
                        <label class="calendar-create-label">End Time: (HH : MM)</label>
                        <input id="endTime" class="calendar-create-input" value="09:00">
                        <div class="calendar-create-btn-row">
                        <button id="createBtn" class="calendar-create-btn calendar-create-btn-green">Create</button>
                        <button id="cancelBtn" class="calendar-create-btn calendar-create-btn-red">Cancel</button>
                        </div>
                    </div>
                    `;
                document.body.appendChild(wrapper);

                document.getElementById('cancelBtn').onclick = () => document.body.removeChild(wrapper);
                document.getElementById('createBtn').onclick = () => {
                    const [otherUserId, otherUserRole] = document.getElementById('userSelect').value.split('|');
                    const day = document.getElementById('sessionName').value;
                    const location = document.getElementById('locationMode').value;
                    const from = document.getElementById('startTime').value;
                    const to = document.getElementById('endTime').value;
                    if (otherUserId && otherUserRole && day && from && to && location) {
                        fetch('calendar_api.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                action: 'create',
                                other_user_id: otherUserId,
                                other_user_role: otherUserRole,
                                day: day,
                                session_date: info.startStr,
                                available_from: from + ':00',
                                available_to: to + ':00',
                                location_mode: location
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                calendar.refetchEvents();
                                showCalendarNotify(true, "Schedule created successfully!");
                            } else {
                                alert(data.message || 'Failed to create slot');
                            }
                        });
                        document.body.removeChild(wrapper);
                    } else {
                        alert('Please fill all fields.');
                    }
                };
                calendar.unselect();
            },

            // --- EVENT CLICK: TUTOR (EDIT/DELETE) & STUDENT (BOOK/CANCEL) ---
            eventClick: function(info) {
                const eventObj = info.event;
                const props = eventObj.extendedProps;

                // --- Show event/session details for everyone ---
                let details = `Session: ${eventObj.title.replace(/^(Available: |Booked: )/, '')}\n`;
                details += `Date: ${eventObj.start.toLocaleDateString()}\n`;
                details += `Time: ${eventObj.start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`;
                if (eventObj.end) {
                    details += ` - ${eventObj.end.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`;
                }
                details += `\nLocation/Mode: ${props.location_mode || 'N/A'}`;
                details += `\nStatus: ${props.is_booked ? 'Booked' : 'Available'}`;

                // Add both user names
                details += `\nScheduled by: ${props.created_by == <?php echo $_SESSION['user_id']; ?> ? 'You' : (props.tutor_id == props.created_by ? props.tutor_name : props.student_name)}`;
                details += `\nTutor: ${props.tutor_name || 'N/A'}`;
                details += `\nStudent: ${props.student_name || 'N/A'}`;

                alert(details);

                // --- Existing logic for tutor and student actions below ---
                // Tutor: Edit or Delete their own slots
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
                                if (data.success) {
                                    calendar.refetchEvents();
                                    showCalendarNotify(false, "Schedule deleted successfully!");
                                } else {
                                    alert(data.message || 'Failed to delete');
                                }
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
                // Student: Book or Cancel booking
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

            // --- TUTOR: DRAG & DROP TO UPDATE DATE/TIME ---
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

        // --- Render the calendar on the page ---
        calendar.render();
    });

    // Show notification modal
    function showCalendarNotify(success, message) {
      const modal = document.getElementById('calendarNotifyModal');
      const title = document.getElementById('calendarNotifyTitle');
      const msg = document.getElementById('calendarNotifyMsg');
      title.textContent = success ? "Success" : "Deleted";
      msg.textContent = message;
      modal.style.display = 'flex';
      setTimeout(() => {
        modal.style.display = 'none';
      }, 1800);
    }

    // Show notification based on URL parameter
    window.addEventListener('DOMContentLoaded', function() {
      const params = new URLSearchParams(window.location.search);
      if (params.get('calendar') === 'created') {
        showCalendarNotify(true, "Schedule created successfully!");
      } else if (params.get('calendar') === 'deleted') {
        showCalendarNotify(false, "Schedule deleted successfully!");
      }
    });
  </script>


</body>
</html>