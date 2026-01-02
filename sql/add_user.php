<?php
/**
 * Run this from CLI to create a user with a bcrypt password hash.
 *
 * Usage:
 *   php sql/add_user.php admin MyPassword123
 *
 * It will print the SQL you can run in MySQL.
 */
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "Run from CLI only.";
    exit;
}

if ($argc < 3) {
    echo "Usage: php sql/add_user.php <username> <password>\n";
    exit(1);
}

$username = trim($argv[1]);
$password = $argv[2];

if ($username === '' || $password === '') {
    echo "Username/password required\n";
    exit(1);
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$sql = "INSERT INTO users (username, password_hash) VALUES ('" . addslashes($username) . "', '" . addslashes($hash) . "');\n";
echo $sql;
