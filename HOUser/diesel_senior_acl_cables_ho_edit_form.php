<?php
require_once __DIR__ . '/../auth.php';
require_login();

if (!isset($_SESSION['utype']) || $_SESSION['utype'] !== 'houser') {
    logout();
    header('Location: ../login.php');
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = db();
$errorMsg = '';
$success  = isset($_GET['success']);

// --------------------
// 1) Validate & get ID
// --------------------
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid record ID.");
}

// --------------------
// 2) Fetch existing row
// --------------------
$sql = "SELECT report_month, report_year, fuel_litres
        FROM ho_senior_managers_fuel_acl_cables
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    die("Record not found.");
}

// --------------------
// 3) Handle UPDATE
// --------------------

// Month map for validation + future blocking
$monthsMap = [
    'January' => 1,
    'February' => 2,
    'March' => 3,
    'April' => 4,
    'May' => 5,
    'June' => 6,
    'July' => 7,
    'August' => 8,
    'September' => 9,
    'October' => 10,
    'November' => 11,
    'December' => 12
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $month  = trim($_POST['month'] ?? '');
    $year   = (int)($_POST['year'] ?? 0);
    $litres = (float)($_POST['fuel_litres'] ?? -1);

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

    if ($errorMsg === '') {
        try {
            $sql = "UPDATE ho_senior_managers_fuel_acl_cables
                    SET report_month = ?, 
                        report_year = ?, 
                        fuel_litres = ?, 
                        updated_at = NOW()
                    WHERE id = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sidi", $month, $year, $litres, $id);
            $stmt->execute();
            $stmt->close();

            header("Location: diesel_senior_acl_cables_ho_edit_form.php?id={$id}&success=1");
            exit;

        } catch (mysqli_sql_exception $e) {
            if ((int)$e->getCode() === 1062) {
                $errorMsg = "Duplicate entry: data for <strong>{$month} {$year}</strong> already exists.";
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
<title>Edit Fuel Consumption – Senior Executive Vehicles Head Office</title>

<link rel="stylesheet" href="../styles/indexstyle.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Jockey+One&display=swap" rel="stylesheet">

<style>
.content-wrap{
    max-width:900px;
    margin:40px auto;
    padding:0 15px;
}
.form-card{
    border:none;
    border-radius:16px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}
.form-header{
    padding:18px 22px;
    border-bottom:1px solid #e5e7eb;
    background:#f8fafc;
}
.form-title{
    margin:0;
    font-family:"Jockey One", sans-serif;
    font-size:22px;
    display:flex;
    align-items:center;
    gap:10px;
}
.form-sub{
    margin-top:6px;
    color:#64748b;
    font-weight:600;
}
.btn-submit{
    padding:10px 22px;
    font-weight:700;
    border-radius:999px;
}
</style>
</head>

<body>

<!-- ===== HEADER (UNCHANGED) ===== -->
<div class="topbar">
    <h1 class="topbar-text">Welcome <?php echo htmlspecialchars(current_username()); ?></h1>
    <a href="../logout.php"><h1 class="topbar-logout">Logout &nbsp;</h1></a>
    <h1 class="topbar-username"><?php echo htmlspecialchars(current_username()); ?>&nbsp;</h1>
</div>

<div class="content-wrap">

<?php if ($success): ?>
    <div class="alert alert-success rounded-4">
        <i class="bi bi-check-circle-fill"></i>
        Data updated successfully.
    </div>
<?php endif; ?>

<?php if ($errorMsg): ?>
    <div class="alert alert-danger rounded-4">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <?php echo $errorMsg; ?>
    </div>
<?php endif; ?>

<div class="card form-card">
    <div class="form-header">
        <h2 class="form-title">
            <i class="bi bi-pencil-square"></i>
            Edit Monthly Fuel Consumption – Senior Executive Vehicles Head Office
        </h2>
        <div class="form-sub">
            ACL Cables PLC - Head Office | Scope 1 – Direct GHG Emissions
        </div>
    </div>

    <div class="card-body">
        <form method="post">

            <!-- Month -->
            <div class="mb-3">
                <label class="form-label fw-bold">Month</label>
                <select name="month" class="form-select" required>
                    <?php
                    $months = [
                        'January','February','March','April','May','June',
                        'July','August','September','October','November','December'
                    ];
                    foreach ($months as $m) {
                        $sel = ($m === $row['report_month']) ? 'selected' : '';
                        echo "<option value=\"$m\" $sel>$m</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Year -->
            <div class="mb-3">
                <label class="form-label fw-bold">Year</label>
                <select name="year" class="form-select" required>
                    <?php
                    $currentYear = (int)date('Y');
                    for ($y = $currentYear; $y >= 2020; $y--) {
                        $sel = ($y == $row['report_year']) ? 'selected' : '';
                        echo "<option value=\"$y\" $sel>$y</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Litres -->
            <div class="mb-4">
                <label class="form-label fw-bold">Fuel Consumption (Litres)</label>
                <input type="number"
                       name="fuel_litres"
                       class="form-control"
                       step="0.01"
                       min="0"
                       value="<?php echo htmlspecialchars($row['fuel_litres']); ?>"
                       required>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="diesel_senior_acl_cables_ho_view_edit.php" class="btn btn-outline-secondary btn-submit">
                    <i class="bi bi-table"></i> Back to Table
                </a>
                <button type="submit" class="btn btn-success btn-submit">
                    <i class="bi bi-save-fill"></i> Update Data
                </button>
            </div>

        </form>
    </div>
</div>

</div>
</body>
</html>
