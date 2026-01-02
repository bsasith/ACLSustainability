<?php
// Database config (edit these)
define('DB_HOST', 'localhost');
define('DB_NAME', 'simple_auth');
define('DB_USER', 'root');
define('DB_PASS', '');

// App config
define('APP_NAME', 'ACL Sustainability');
define('SESSION_NAME', 'simple_login_session');

// Create a MySQLi connection
function db() {
    static $conn = null;
    if ($conn !== null) return $conn;

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
    return $conn;
}
