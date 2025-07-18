<?php
include "myconnector.php";
session_start();

$signup_error = $signup_success = $login_error = "";

// SIGNUP HANDLER
if (isset($_POST['register'])) {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $secretkey = isset($_POST['secretkey']) ? trim($_POST['secretkey']) : '';
    $phonenumber = trim($_POST['phonenumber']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $birthday = trim($_POST['birthday']);
    $role = isset($_POST['role']) ? $_POST['role'] : 'student';
    if (empty($role) && !empty($_POST['secretkey'])) {
        $role = 'tutor';
    }
    if (empty($role)) $role = 'student'; // Default to student if not set


    if ($role === 'parent') {
        $student_firstname = trim($_POST['student_firstname']);
        $parent_secretkey = trim($_POST['parent_secretkey']);
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE first_name=? AND secret_key=? AND role='student'");
        $stmt->bind_param("ss", $student_firstname, $parent_secretkey);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows == 0) {
            $signup_error = "No student found with that name and secret key.";
        }
        $stmt->close();
        if (!empty($signup_error)) return;
    }

    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username=? OR email=?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $signup_error = "Username or email already exists.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        if ($role === 'tutor') {
            $status = 'pending';
        } else {
            $status = 'approved';
        }

        $stmt = $conn->prepare("INSERT INTO users (username, role, first_name, last_name, email, password_hash, secret_key, contact_number, address, date_of_birth, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssss", $username, $role, $firstname, $lastname, $email, $password_hash, $secretkey, $phonenumber, $address, $birthday, $status);
        if ($stmt->execute()) {
            $signup_success = "Registration successful! You can now log in.";
        } else {
            $signup_error = "Registration failed. Please try again.";
        }
    }
    $stmt->close();
}

// LOGIN HANDLER
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, username, password_hash, role, first_name, status FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($user_id, $db_username, $db_password_hash, $role, $first_name, $status);
        $stmt->fetch();
        if (password_verify($password, $db_password_hash)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $db_username;
            $_SESSION['role'] = $role;
            $_SESSION['first_name'] = $first_name;
            $_SESSION['status'] = $status;

            if ($role === 'tutor') {
                if ($status === 'pending') {
                    header("Location: tutor_pending.php"); // Show pending page
                } elseif ($status === 'approved') {
                    header("Location: tutorlanding.php"); // Tutor dashboard
                } elseif ($status === 'rejected') {
                    header("Location: tutor_rejected.php"); // Show rejection page
                }
            } elseif ($role === 'parent') {
                header("Location: landingpage.php");
            } elseif ($role === 'student') {
                header("Location: landingpage.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit();
        } else {
            $login_error = "Incorrect password.";
        }
    } else {
        $login_error = "Username not found.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Document</title>
    <link rel="stylesheet" href="loginpage.css">
</head>
<body>
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
        <img src="Images/user-svgrepo-com.svg" alt="User Icon">
        </div>
    </nav>
        <!-- Main Content -->
        <main class="main" id="main-page">
            <div class="left-section">
                <h2>
                    Lets <span class="green">Grow</span> Together and <br /> Learn From Each Other.
                </h2>
                <p> 
                    Our learning space helps students share ideas,
                    build skills, and grow side by side —
                    because learning is better when we work as a team.
                </p>
                <button data-bs-toggle="modal" data-bs-target="#loginModal" class="hire-button">
                    Join Us!
                </button>

            </div>
            <div class="right-section">
                <img src="Images/main.png" alt="Students learning together" class="students-image" />
                <h3 class="curve-text">Learning Connect Us All.</h3>
            </div>
        </main>
    </div>
  <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <ul>

            <li><a href="#main-page"><img src="Images/home-svgrepo-com.svg" alt="Home Icon"> Home</a></li>
            <li><a href="#features-box"><img src="Images/idea-svgrepo-com.svg" alt="Features Icon">Features</a></li>
            <li><a href="#about-us"><img src="Images/about-filled-svgrepo-com.svg" alt="About-Us Icon">About Us</a></li>
            <br>
            <li>
                <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
                    <img src="Images/login-3-svgrepo-com.svg" alt="Login Icon">
                    Log In
                </a>
            </li>
            <li>
                <a href="#" data-bs-toggle="modal" data-bs-target="#roleModal">
                    <img src="Images/sign-add-svgrepo-com.svg" alt="Sign Up Icon" >
                    Sign Up
                </a>
            </li>
        </ul>
    </div>
    <div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title">Sign Up As</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <button class="btn btn-primary m-2" onclick="openSignup('student')">Student</button>
                <button class="btn btn-success m-2" onclick="openSignup('parent')">Parent</button>
                <button class="btn btn-warning m-2" onclick="openSignup('tutor')">Tutor</button>
            </div>
            </div>
        </div>
    </div>

 <!-- Signup Modal -->
    <div class="modal fade" id="signupModal" tabindex="-1" aria-labelledby="signupModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content modal-content-login">
                <div class="modal-header">
                    <h1 class="modal-title typing" id="animatedText">Sign up!</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($signup_error): ?>
                        <div class="alert alert-danger"><?php echo $signup_error; ?></div>
                    <?php elseif ($signup_success): ?>
                        <div class="alert alert-success"><?php echo $signup_success; ?></div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data" action="">
                        <!-- ...existing signup fields... -->
                        <input type="hidden" id="signupRole" name="role" value="student">
                        <div class="mb-3">
                            <label for="firstname" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstname" name="firstname" placeholder="Type Here" required>
                        </div>
                        <div class="mb-3">
                            <label for="lastname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Type Here" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Type Here" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Type Here" required>
                        </div>
                        <!-- Secret Key for student/tutor only -->
                        <div class="mb-3 secretkey-only">
                            <label for="secretkey" class="form-label">Secret Key</label>
                            <input type="text" class="form-control" id="secretkey" name="secretkey" placeholder="Enter secret key">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Type Here" required>
                        </div>
                        <div class="mb-3">
                            <label for="phonenumber" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phonenumber" name="phonenumber" placeholder="Type Here">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" placeholder="Type Here">
                        </div>
                        <div class="mb-3">
                            <label for="birthday" class="form-label">Birthday</label>
                            <input type="date" class="form-control" id="birthday" name="birthday" placeholder="Select your birthday">
                        </div>
                        <!-- Parent fields only -->
                        <div class="mb-3 parent-only" style="display:none;">
                            <label for="student_firstname" class="form-label">Student's First Name</label>
                            <input type="text" class="form-control" id="student_firstname" name="student_firstname" placeholder="Enter your child's first name">
                        </div>
                        <div class="mb-3 parent-only" style="display:none;">
                            <label for="parent_secretkey" class="form-label">Student's Secret Key</label>
                            <input type="text" class="form-control" id="parent_secretkey" name="parent_secretkey" placeholder="Enter the secret key given to your child">
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="" id="flexCheckChecked" checked>
                            <label class="form-check-label" for="flexCheckChecked">
                                Agree to terms and conditions.
                            </label>
                            <p>
                                By clicking <a href="">Sign up</a> and <a href="">agree</a> to our <a href="">Terms of Service</a> and that you have read our <a href="">Privacy Policy</a>
                            </p>
                        </div>
                        <button type="submit" name="register" class="btn btn-success w-100" style="margin-bottom: 25px;">Sign Up</button>
                    </form>
                </div>
                <div class="">
                    <p class="text-center mb-4">
                        If you already have an account <a href="" data-bs-toggle="modal" data-bs-target="#loginModal">sign in here.</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content modal-content-login">
                <div class="modal-header">
                    <h1 class="modal-title typing" id="animatedText2">Log In</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($login_error): ?>
                        <div class="alert alert-danger"><?php echo $login_error; ?></div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data" action="">
                        <div class="mb-3">
                            <label for="loginUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="loginUsername" name="username" placeholder="Type Here" required>
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Type Here" required>
                        </div>
                        <p>
                            By clicking <a href="">agree</a> to our <a href="">Terms of Service</a> and that you have read our <a href="">Privacy Policy</a>
                        </p>
                        <div class="form-check" style="margin-bottom: 25px">
                            <input class="form-check-input" type="checkbox" value="" id="loginAgree" checked>
                            <label class="form-check-label" for="loginAgree">
                                Agree to terms and conditions.
                            </label>
                        </div>
                        <button type="submit" name="login" class="btn btn-success w-100" style="margin-bottom: 25px;">Enter</button>
                        <div>
                            <p class="text-center mb-4">
                                <a href="#">Forgot Password?</a>
                            </p>
                        </div>
                    </form>
                </div>
                <div class="">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#roleModal">
                        <p class="text-center mb-4">
                            If you don't have an account <a href="" data-bs-toggle="modal" data-bs-target="#roleModal">sign up here.</a>
                        </p>
                    </a>
                    
                </div>
            </div>
        </div>
    </div>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
       <!-- Classix's Features -->
    <div class="features-section" >
        <h1>ClassXic's Features</h1>
        <br>
        <br>
        <br>
       <div class="features-container" id="features-box">
            <div class="feature-box"
                 data-title="Customized Learning Management System"
                 data-description="A customized learning platform designed to meet the unique needs of dyslexic learners, offering tools and features that support easier reading, comprehension, and learning."
                 data-image="Images/custom.png">
                 
                <img src="Images/dashboard.png" alt="LMS Icon" class="feature-icon">
                <div class="feature-title">Customized Learning<br>Management System</div>
            </div>
            <div class="feature-box"
                 data-title="Dyslexic Friendly Formatting"
                 data-description="Uses OpenDyslexic fonts, increased line spacing, and appropriate color scheme to reduce visual stress for dyslexic students."
                 data-image="Images/opend.jpeg">
                <img src="Images/bald-head-with-puzzle-brain-svgrepo-com.svg" alt="Dyslexia Icon" class="feature-icon">
                <div class="feature-title">Dyslexic Friendly-<br>Formatting</div>
            </div>
            <div class="feature-box"
                 data-title="Text to Speech Technology"
                 data-description="Reads aloud on-screen text to assist with reading comprehension and reduce the difficulty of decoding written content."
                 data-image="Images/text.png">
                <img src="Images/text-to-speech.png" alt="Text to Speech Icon" class="feature-icon">
                <div class="feature-title">Text to Speech<br>Technology</div>
            </div>
            <div class="feature-box"
                 data-title="Phonetics"
                 data-description="Provides visual and audio cues for correct pronunciation and sound breakdown of words, helping improve reading fluency and vocabulary imbued a dictionary and its meaning."
                 data-image="Images/phone.jpg">
                <img src="Images/pronunciation.png" alt="Phonetics Icon" class="feature-icon">
                <div class="feature-title">Phonetics</div>
            </div>
        </div>
        <!-- Modal -->
        <div id="modal" class="modal ">
            <div class="modal-content modal-feature">
                <span id="close-modal">&times;</span>
                <h2 id="modal-title"></h2>
                <img id="modal-img" src="" alt="" style="max-width:300px; display:block; margin:0 auto 16px auto;">
                <p id="modal-text"></p>
            </div>
        </div>
    </div>
    <br>
    <br>
    <br>
    <br>
    <div class="about-section" id="about-us">
        <div class="about-text">
            <h1>About Us.</h1>
            <p>
            ClassXic is a dyslexic-friendly LMS is an accessible learning platform designed to support students with dyslexia. It features readable fonts, clear navigation, minimal distractions, high-contrast themes, and text-to-speech options to enhance comprehension and create an inclusive educational environment.
            </p>
        </div>
        <div class="about-image">
            <img src="Images/main.png" alt="OpenDyslexic Font Example">
        </div>
    </div>

    <footer class="site-footer">
        <div class="footer-left">
            © ClassXic
        </div>
        <div class="footer-right">
            <a href="#"><img src="Images/facebook.png" alt="Facebook"></a>
            <a href="#"><img src="Images/social.png" alt="Instagram"></a>
            <a href="#"><img src="Images/linkedin.png" alt="LinkedIn"></a>
            <a href="#"><img src="Images/twitter.png" alt="X"></a>
            <span class="footer-directory">SOCIAL MEDIA PAGES&rarr;</span>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php if ($signup_error || $signup_success): ?>
        <script>
            var signupModal = new bootstrap.Modal(document.getElementById('signupModal'));
            signupModal.show();
        </script>
        <?php endif; ?>

        <?php if ($login_error): ?>
        <script>
            var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
            loginModal.show();
        </script>
        <?php endif; ?>

    <script>
        function openSignup(role) {
            document.getElementById('signupRole').value = role;

            // Show/hide parent-only fields
            document.querySelectorAll('.parent-only').forEach(el => {
                el.style.display = (role === 'parent') ? 'block' : 'none';
            });
            // Show/hide secretkey-only field
            document.querySelectorAll('.secretkey-only').forEach(el => {
                el.style.display = (role === 'parent') ? 'none' : 'block';
            });

            // Hide role modal, show signup modal
            var roleModal = bootstrap.Modal.getInstance(document.getElementById('roleModal'));
            roleModal.hide();
            var signupModal = new bootstrap.Modal(document.getElementById('signupModal'));
            signupModal.show();
        }
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
    //featurebox
    const boxes = document.querySelectorAll('.feature-box');
    const modal = document.getElementById('modal');
    const modalTitle = document.getElementById('modal-title');
    const modalText = document.getElementById('modal-text');
    const modalImg = document.getElementById('modal-img');
    const closeModal = document.getElementById('close-modal');

    boxes.forEach(box => {
        box.addEventListener('click', () => {
            const title = box.getAttribute('data-title');
            const description = box.getAttribute('data-description');
            const image = box.getAttribute('data-image');
            modalTitle.textContent = title;
            modalText.innerHTML = `<span style="display:inline-block; width:2em;"></span>${description}`;
            modalImg.src = image;
            modalImg.alt = title + " icon";
            modal.style.display = 'flex';
        });
    });

    closeModal.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    </script>
</body>
</html>
