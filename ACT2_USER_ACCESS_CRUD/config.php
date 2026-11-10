<?php
$host     = "localhost";
$dbname   = "library_system";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed. Check config.php and import database.sql.");
}

if (session_status() === PHP_SESSION_NONE) session_start();

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function require_login() {
    if (empty($_SESSION['user'])) {
        header("Location: index.php");
        exit;
    }
}

function is_admin() {
    return ($_SESSION['user']['role'] ?? '') === 'admin';
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        exit("Access denied.");
    }
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
        !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit("Invalid request token.");
    }
}

function flash($message) {
    $_SESSION['flash'] = $message;
}

function show_flash() {
    if (!empty($_SESSION['flash'])) {
        echo '<p class="notice">' . h($_SESSION['flash']) . '</p>';
        unset($_SESSION['flash']);
    }
}
