<?php
declare(strict_types=1);

require_once __DIR__ . '/../auth.php';
require_login();

// Allow only logged-in users of this section (acuser/euser/houser)
$expectedUtype = basename(__DIR__); // "AcUser", "EUser", "HOUser" (folder name)
$map = [
    'AcUser' => 'acuser',
    'EUser'  => 'euser',
    'HOUser' => 'houser',
];
$required = $map[$expectedUtype] ?? null;

if (!$required || !isset($_SESSION['utype']) || $_SESSION['utype'] !== $required) {
    logout();
    header('Location: ../login.php');
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = db();
$conn->set_charset('utf8mb4');

$userId   = (int)($_SESSION['user_id'] ?? 0);
$username = (string)($_SESSION['username'] ?? '');

$success  = isset($_GET['success']);
$errorMsg = '';

function valid_password(string $pw): bool {
    // Basic strong-ish policy: 8+ chars, at least 1 letter and 1 number
    if (strlen($pw) < 8) return false;
    if (!preg_match('/[A-Za-z]/', $pw)) return false;
    if (!preg_match('/\d/', $pw)) return false;
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf'] ?? '')) {
        $errorMsg = 'Invalid request. Please refresh and try again.';
    } else {
        $current = (string)($_POST['current_password'] ?? '');
        $new1    = (string)($_POST['new_password'] ?? '');
        $new2    = (string)($_POST['confirm_password'] ?? '');

        if ($current === '' || $new1 === '' || $new2 === '') {
            $errorMsg = 'Please fill in all fields.';
        } elseif ($new1 !== $new2) {
            $errorMsg = 'New password and confirmation do not match.';
        } elseif (!valid_password($new1)) {
            $errorMsg = 'New password must be at least 8 characters and include at least one letter and one number.';
        } elseif ($new1 === $current) {
            $errorMsg = 'New password must be different from the current password.';
        } else {
            // Fetch current hash
            $stmt = $conn->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            $stmt->close();

            $hash = $row['password_hash'] ?? '';

            if (!$row || !password_verify($current, (string)$hash)) {
                $errorMsg = 'Current password is incorrect.';
            } else {
                // Update password
                $newHash = password_hash($new1, PASSWORD_DEFAULT);

                $upd = $conn->prepare('UPDATE users SET password_hash = ? WHERE id = ? LIMIT 1');
                $upd->bind_param('si', $newHash, $userId);
                $upd->execute();
                $upd->close();

                // Optional: regenerate session id after sensitive change
                // start_session(); // (usually not needed if session already started in auth.php)
                session_regenerate_id(true);

                header('Location: change_password.php?success=1');
                exit;
            }
        }
    }
}

$dashboard = 'dashboard.php';

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password</title>

<link rel="stylesheet" href="../styles/indexstyle.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<?php if ($success): ?>
    <!-- Fallback redirect even if JS is blocked -->
    <meta http-equiv="refresh" content="1.5;url=<?= e($dashboard) ?>">
    <script>
        setTimeout(function () {
            window.location.href = "<?= e($dashboard) ?>";
        }, 1500);
    </script>
<?php endif; ?>
</head>

<body class="bg-light">
<div class="container py-5" style="max-width: 720px;">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h2 class="mb-0">Change Password</h2>
            <div class="text-muted small">Signed in as <strong><?= e($username) ?></strong></div>
        </div>
        <a class="btn btn-outline-secondary" href="<?= e($dashboard) ?>">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            Password updated successfully. Redirecting to dashboard…
        </div>
        <noscript>
            <div class="alert alert-info">
                JavaScript is disabled. <a href="<?= e($dashboard) ?>">Click here to go to dashboard</a>.
            </div>
        </noscript>
    <?php endif; ?>

    <?php if ($errorMsg !== ''): ?>
        <div class="alert alert-danger"><?= e($errorMsg) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="post" autocomplete="off">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

                <div class="mb-3">
                    <label class="form-label">Current password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">New password</label>
                    <input type="password" name="new_password" class="form-control" minlength="8" required>
                    <div class="form-text">Minimum 8 characters, include at least one letter and one number.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Confirm new password</label>
                    <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-shield-lock"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="text-muted small mt-3">
        Tip: Don’t reuse passwords you use on other websites.
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
