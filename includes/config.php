<?php
// Define application constants
define('BASE_URL', 'http://localhost/online-quiz-pro'); // Adjust to your local path
define('SITE_NAME', 'Online Quiz Pro');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'online_quiz_pro');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
session_start();