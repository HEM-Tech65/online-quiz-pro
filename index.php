<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$page_title = "Online Quiz System";

// Redirect logged-in users to their dashboard
if ($auth->isLoggedIn()) {
    header("Location: " . BASE_URL . "/" . 
          ($auth->isAdmin() ? "admin/dashboard.php" : 
          ($auth->isLecturer() ? "lecturer/dashboard.php" : "student/dashboard.php")));
    exit();
}

include 'includes/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="stylesheet" href="/assets/css/custom.css">
<style>
    body {
        background-image: url('assets/images/view4.JPG');
        background-repeat: no-repeat;
        background-position: center;
        background-size: cover;
        
    }
</style>


<div class="hero-section text-white py-5 animate-on-scroll">
    <div class="container text-center py-3">
        <img src="assets/images/logoc.png" alt="gctu logo" class="img-fluid mb-3 justify-content-center" style="width: 120px; height: 120px;">
        <h1 class="display-4 fw-bold mb-4">Welcome to GCTU Online Quiz Pro</h1>
        <p class="lead mb-5">A professional platform for creating and taking quizzes online</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="register.php" class="btn btn-light btn-lg px-4">Register Now</a>
            <a href="login.php" class="btn btn-outline-light btn-lg px-4">Login</a>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm animate-on-scroll delay-1">
                <div class="card-body text-center p-4">
                    <div class="icon-circle bg-light-primary text-primary mb-3 mx-auto">
                        <i class="fas fa-chalkboard-teacher fa-2x"></i>
                    </div>
                    <h3 class="fw-bold mb-4">For Lecturers</h3>
                    <p class="text-muted">Create and manage quizzes, track student progress, and analyze results.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm animate-on-scroll delay-2">
                <div class="card-body text-center p-4">
                    <div class="icon-circle bg-light-success text-success mb-3 mx-auto">
                        <i class="fas fa-user-graduate fa-2x"></i>
                    </div>
                    <h3>For Students</h3>
                    <p class="text-muted">Take quizzes anytime, anywhere, and get instant results with feedback.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm animate-on-scroll delay-3">
                <div class="card-body text-center p-4">
                    <div class="icon-circle bg-light-info text-info mb-3 mx-auto">
                        <i class="fas fa-university fa-2x"></i>
                    </div>
                    <h3>For Institutions</h3>
                    <p class="text-muted">Streamline your examination process with our secure platform.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bg-light py-5">
    <div class="container text-center py-4">
        <h2 class="mb-4 animate-on-scroll">How It Works</h2>
        <div class="row g-4">
            <div class="col-md-3">
                <div class="step-card animate-on-scroll delay-1">
                    <div class="step-number">1</div>
                    <h4>Register</h4>
                    <p>Create your account as lecturer or student</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="step-card animate-on-scroll delay-2">
                    <div class="step-number">2</div>
                    <h4>Create</h4>
                    <p>Lecturers set up quizzes with questions</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="step-card animate-on-scroll delay-3">
                    <div class="step-number">3</div>
                    <h4>Attempt</h4>
                    <p>Students take assigned quizzes</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="step-card animate-on-scroll delay-4">
                    <div class="step-number">4</div>
                    <h4>Analyze</h4>
                    <p>View results and performance analytics</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>