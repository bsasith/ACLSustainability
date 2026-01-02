<?php
require_once __DIR__ . '/config.php';

// Start session once
function start_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
    }
}

// Simple CSRF token helpers
function csrf_token() {
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_validate($token) {
    start_session();
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token ?? '');
}

// Auth helpers
function is_logged_in() {
    start_session();
    return !empty($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
    
}

function current_username() {
    start_session();
    return $_SESSION['username'] ?? null;
}

// Attempt login
function attempt_login($username, $password) {
    $username = trim((string)$username);
    $password = (string)$password;

    if ($username === '' || $password === '') {
        return [false, 'Username and password are required.'];
    }

    $conn = db();
    $stmt = $conn->prepare('SELECT id, username, password_hash,utype FROM users WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    // Always use password_verify to avoid timing differences (even if user not found)
    $hash = $user['password_hash'] ?? '$2y$10$usesomesillystringforsalt$';
    $ok = password_verify($password, $hash);
    $_SESSION['utype']=$user['utype'] ?? '';

    if (!$user || !$ok) {
        return [false, 'Invalid username or password.'];
    }

    // Session hardening
    start_session();
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['username'] = $user['username'];

    return [true, null];
}

function logout() {
    start_session();
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
