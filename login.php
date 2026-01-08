<?php
require_once __DIR__ . '/auth.php';

start_session();

// if (is_logged_in()) {
//     header('Location: dashboard.php');
//     exit;
// }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf'] ?? '')) {
        $error = 'Invalid request. Please refresh and try again.';
    } else {
        [$ok, $msg] = attempt_login($_POST['username'] ?? '', $_POST['password'] ?? '');
        if ($ok) {
          if($_SESSION['utype']==='euser')
            header('Location: .\EUser\dashboard.php');
          if($_SESSION['utype']==='houser')
            header('Location: .\HOUser\dashboard.php');
          if($_SESSION['utype']==='acuser')
            header('Location: .\AcUser\dashboard.php');
          exit;
        }
        $error = $msg ?: 'Login failed.';
    }
}

$token = csrf_token();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars(APP_NAME); ?> - Login</title>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background:#f5f7fb;
}
.login-card{
    max-width:420px;
}
</style>
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100">

<div class="card shadow-sm login-card w-100">
    <div class="card-body p-4">
      <div class="mb-4 text-center"><img src="logo.jpg" style="width:300px;"></div>
        <h4 class="mb-4 text-center fw-bold">Sustainability Measures and KPIs (ISSB Based) Login</h4>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php" >
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($token); ?>">

            <div class="mb-3">
                <label class="form-label">Username</label>
                <input
                    type="text"
                    name="username"
                    class="form-control"
                    required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input
                    type="password"
                    name="password"
                    class="form-control"
                    required>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Sign In
            </button>
        </form>

        
    </div>
</div>

</body>
</html>
