<?php
// Database connection — student_ms
$host     = "localhost";
$user     = "root";
$password = "";
$database = "student_ms";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

$conn->set_charset('utf8');

// Session cookie settings
session_set_cookie_params([
    'lifetime' => 3600,
    'path'     => '/',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

// ── Helper functions ──────────────────────────────────────────
function isLoggedIn() {
    return isset($_SESSION['user']);
}

function isSuperAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin';
}

function isAdmin() {
    return isset($_SESSION['role']) &&
           ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'superadmin');
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function requireSuperAdmin() {
    if (!isSuperAdmin()) {
        header("Location: index.php?error=access_denied");
        exit();
    }
}
function isStudent() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}
?>