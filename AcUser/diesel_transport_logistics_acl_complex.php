<?php
require_once __DIR__ . '/../auth.php';
require_login();

if (!isset($_SESSION['utype']) || $_SESSION['utype'] !== 'acuser') {
    logout();
    header('Location: login.php');
    exit;
}

/**
 * IMPORTANT:
 * - We use PRG pattern (POST -> Redirect -> GET) so success message shows after redirect
 * - We catch duplicate entry (1062) and show red bootstrap alert
 */

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = db();
$errorMsg = '';
$success  = isset($_GET['success']); // PRG pattern

// Display values after redirect (GET)
$dispYear   = $_GET['year'] ?? '';
$dispMonth  = $_GET['month'] ?? '';
$dispLitres = $_GET['litres'] ?? '';

$monthsList = [
    'January','February','March','April','May','June',
    'July','August','September','October','November','December'
];

$monthsMap = [
    'January' => 1, 'February' => 2, 'March' => 3,
    'April' => 4, 'May' => 5, 'June' => 6,
    'July' => 7, 'August' => 8, 'September' => 9,
    'October' => 10, 'November' => 11, 'December' => 12
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $month  = trim($_POST['month'] ?? '');
    $year   = (int)($_POST['year'] ?? 0);
    $litres = (float)($_POST['diesel_litres'] ?? -1);

    // Basic validation
    if ($month === '' || $year <= 0 || $litres < 0) {
        $errorMsg = "Please fill all fields correctly.";
    } elseif (!isset($monthsMap[$month])) {
        $errorMsg = "Invalid month selected.";
    } else {

        // ✅ Block future month/year (server-side)
        $selectedMonth = $monthsMap[$month];
        $currentYear   = (int)date('Y');
        $currentMonth  = (int)date('n'); // 1 = January

        if ($year > $currentYear) {
            $errorMsg = "You cannot enter data for a future year.";
        } elseif ($year === $currentYear && $selectedMonth > $currentMonth) {
            $errorMsg = "You cannot enter data for a future month.";
        }
    }

    // If no error, insert
    if ($errorMsg === '') {
        try {
            $sql = "INSERT INTO diesel_transport_logistics_acl_complex
                    (report_month, report_year, diesel_litres, created_by, company_name, emission_scope, activity_type)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $conn = db();
            $stmt = $conn->prepare($sql);

            $username       = current_username();
            $company        = "ACL Cables PLC";
            $activity_type  = "Transport & Logistics";
            $emission_scope = "Scope 1";

            // month(s), year(i), litres(d), created_by(s), company(s), scope(s), activity(s)
            $stmt->bind_param("sidssss", $month, $year, $litres, $username, $company, $emission_scope, $activity_type);
            $stmt->execute();

            // ✅ PRG redirect with success info for display + 3s redirect to dashboard
            $qMonth  = urlencode($month);
            $qYear   = urlencode((string)$year);
            $qLitres = urlencode((string)$litres);

            header("Location: diesel_transport_logistics_acl_complex.php?success=1&year={$qYear}&month={$qMonth}&litres={$qLitres}");
            exit;

        } catch (mysqli_sql_exception $e) {

            // Duplicate entry error code in MySQL = 1062
            if ((int)$e->getCode() === 1062) {
                $errorMsg = "Duplicate entry: Data for <strong>" . htmlspecialchars($month) . " " . htmlspecialchars((string)$year) . "</strong> already exists.";
            } else {
                $errorMsg = "Database error: " . htmlspecialchars($e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Diesel Consumption – Transport & Logistics (ACL Cables PLC)</title>

    <!-- Existing CSS -->
    <link rel="stylesheet" href="../styles/indexstyle.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Jockey+One&display=swap" rel="stylesheet">

    <style>
        /* ===== Form Styling Only ===== */
        .content-wrap {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 15px;
        }

        .form-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
        }

        .form-header {
            padding: 18px 22px;
            border-bottom: 1px solid #e5e7eb;
            background: #f8fafc;
        }

        .form-title {
            margin: 0;
            font-family: "Jockey One", sans-serif;
            font-size: 22px;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-sub {
            margin-top: 6px;
            font-size: .95rem;
            color: #64748b;
            font-weight: 600;
        }

        .form-label {
            font-weight: 700;
            color: #0f172a;
        }

        .btn-submit {
            padding: 10px 22px;
            font-weight: 700;
            border-radius: 999px;
        }
    </style>
</head>

<body>

    <!-- ===== HEADER (UNCHANGED) ===== -->
    <div class="topbar">
        <h1 class="topbar-text">
            Welcome <?php echo htmlspecialchars(current_username()); ?>
        </h1>

        <a href="../logout.php">
            <h1 class="topbar-logout">Logout &nbsp;</h1>
        </a>

        <h1 class="topbar-username">
            <?php echo htmlspecialchars(current_username()); ?>&nbsp;
        </h1>
    </div>

    <!-- ===== CONTENT ===== -->
    <div class="content-wrap">

        <!-- ERROR (red) -->
        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger rounded-4">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <?php echo $errorMsg; ?>
            </div>
        <?php endif; ?>

        <!-- SUCCESS (green) -->
        <?php if ($success): ?>
            <div class="alert alert-success rounded-4">
                <i class="bi bi-check-circle-fill"></i>
                Monthly diesel consumption data saved successfully.
                <br>
                <strong>Year:</strong> <?php echo htmlspecialchars($dispYear); ?>,
                <strong>Month:</strong> <?php echo htmlspecialchars($dispMonth); ?>,
                <strong>Litres:</strong> <?php echo htmlspecialchars($dispLitres); ?>
                <br>
                <small class="text-muted">Redirecting to dashboard in 3 seconds…</small>
            </div>

            <script>
                setTimeout(function () {
                    window.location.href = "dashboard.php";
                }, 3000);
            </script>
        <?php endif; ?>

        <div class="card form-card">
            <div class="form-header">
                <h2 class="form-title">
                    <!-- <i class="bi bi-fire"></i> -->
                    <i class="bi bi-truck kpi-icon"></i>
                    Monthly Diesel Consumption – Transport & Logistics
                </h2>
                <div class="form-sub">
                    ACL Cables PLC | Scope 1 – Direct GHG Emissions
                </div>
            </div>

            <div class="card-body">
                <form method="post">

                    <!-- Month -->
                    <div class="mb-3">
                        <label class="form-label">Month</label>
                        <select name="month" class="form-select" required>
                            <option value="">Select Month</option>
                            <?php
                            $months = [
                                'January','February','March','April','May','June',
                                'July','August','September','October','November','December'
                            ];
                            foreach ($months as $m) {
                                echo '<option value="'.htmlspecialchars($m).'">'.htmlspecialchars($m).'</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Year -->
                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <select name="year" class="form-select" required>
                            <option value="">Select Year</option>
                            <?php
                            $currentYear = (int)date('Y');
                            for ($y = $currentYear; $y >= 2020; $y--) {
                                echo '<option value="'.$y.'">'.$y.'</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Diesel Litres -->
                    <div class="mb-4">
                        <label class="form-label">Diesel Consumption (Litres)</label>
                        <input
                            type="number"
                            name="diesel_litres"
                            class="form-control"
                            placeholder="Enter total diesel consumption for the month"
                            step="0.01"
                            min="0"
                            required>
                    </div>

                    <!-- Submit -->
                    <div class="d-flex justify-content-end">
                        <a href="dashboard.php">
                        <button type="button" class="btn btn-warning btn-submit">
                            <i class="bi bi-arrow-left"></i>
                            Back to Dashboard
                        </button>
                        </a>
                        
                        <button type="submit" class="btn btn-success btn-submit mx-3">
                            <i class="bi bi-save-fill"></i>
                            Save Monthly Data
                        </button>
                        
                    </div>

                </form>
            </div>
        </div>

    </div>

</body>
</html>
