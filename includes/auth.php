<?php
require_once 'config.php';
require_once 'db.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }

    // Register new user
    public function register($data) {
    // Validate input
    $errors = [];
    if (empty($data['first_name'])) $errors[] = "First name is required";
    if (empty($data['last_name'])) $errors[] = "Last name is required";
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    if (empty($data['password']) || strlen($data['password']) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    if ($data['password'] !== $data['confirm_password']) {
        $errors[] = "Passwords do not match";
    }
    
    if (!empty($errors)) return ['success' => false, 'errors' => $errors];
    
    // Check if email exists
    $email = $this->db->escapeString($data['email']);
    $result = $this->db->query("SELECT id FROM users WHERE email = '$email'");
    
    if ($result->num_rows > 0) {
        return ['success' => false, 'errors' => ['Email already exists']];
    }
    
    // Hash password
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Prepare data
    $firstName = $this->db->escapeString($data['first_name']);
    $lastName = $this->db->escapeString($data['last_name']);
    $role = $this->db->escapeString($data['role'] ?? 'student');
    
    // Handle department - properly escape and quote if exists
    $department = isset($data['department']) && !empty($data['department']) 
        ? "'" . $this->db->escapeString($data['department']) . "'" 
        : "NULL";
    
    $sql = "INSERT INTO users (first_name, last_name, email, password, role, department) 
            VALUES ('$firstName', '$lastName', '$email', '$hashedPassword', '$role', $department)";
    
    if ($this->db->query($sql)) {
        return ['success' => true, 'user_id' => $this->db->getLastInsertId()];
    }
    
    return ['success' => false, 'errors' => ['Registration failed. Please try again.']];
}

    // Login user
    public function login($email, $password) {
    $email = $this->db->escapeString($email);
    $sql = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $result = $this->db->query($sql);
    
    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['profile_image'] = $user['profile_image'] ?? null;
            
            // Update last login - only if column exists
            $update_sql = "UPDATE users SET last_login = NOW() WHERE id = {$user['id']}";
            @$this->db->query($update_sql); // @ suppresses error if column doesn't exist
            
            return true;
        }
    }
    return false;
}

    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // Check user role
    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['role'] == 'admin';
    }

    public function isLecturer() {
        return $this->isLoggedIn() && $_SESSION['role'] == 'lecturer';
    }

    public function isStudent() {
        return $this->isLoggedIn() && $_SESSION['role'] == 'student';
    }

    // Logout
    public function logout() {
        session_unset();
        session_destroy();
        header("Location: " . BASE_URL . "/login.php");
        exit();
    }
}

$auth = new Auth();
?>