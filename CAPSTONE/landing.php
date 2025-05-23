<?php
include "myconnector.php";
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <!-- Navbar -->
        <nav class="navbar">
            <div class="nav-left">
                <a href="#">Home</a>
                <a href="#">Features</a>
                <a href="#">About Us</a>
            </div>
            <div class="nav-center">
                <h1>ClassXic</h1>
            </div>
            <div class="nav-right">
                <label for="search" class="visually-hidden">Search</label>
                <input type="text" id="search" placeholder="Discover" class="search" />
                <button class="login-button" data-bs-toggle="modal" data-bs-target="#loginModal">Log In</button>
                <button class="reg-button" data-bs-toggle="modal" data-bs-target="#signupModal">Register</button>
            </div>
        </nav>
        <!-- Main Content -->
        <main class="main">
            <div class="left-section">
                <h2>
                    Lets <span class="green">Grow</span> Together and <br /> Learn From Each Other.
                </h2>
                <p>
                    Our learning space helps students share ideas,
                    build skills, and grow side by side —
                    because learning is better when we work as a team.
                </p>
                <button class="hire-button">Hire Us!</button>
            </div>
            <div class="right-section">
                <img src="Images/main.png" alt="Students learning together" class="students-image" />
                <h3 class="curve-text">Learning Connect Us All.</h3>
            </div>
        </main>
    </div>


    <!-- Signup Modal -->
    <div class="modal fade" id="signupModal" tabindex="-1" aria-labelledby="signupModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title typing" id="animatedText">Sign up!</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data" action="">
                        <div class="mb-3">
                            <label for="firstname" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstname" name="firstname" placeholder="Type Here">
                        </div>
                        <div class="mb-3">
                            <label for="lastname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastname" name="lastname" placeholder="Type Here">
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Type Here">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Type Here">
                        </div>
                        <div class="mb-3">
                            <label for="phonenumber" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phonenumber" name="phonenumber" placeholder="Type Here">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Type Here">
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
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title typing" id="animatedText2">Log In</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data" action="">
                        <div class="mb-3">
                            <label for="loginUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="loginUsername" name="username" placeholder="Type Here">
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Type Here">
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
                    <p class="text-center mb-4">
                        If you don't have an account <a href="" data-bs-toggle="modal" data-bs-target="#signupModal">sign up here.</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
