<?php
require_once __DIR__ . '/../auth.php';
require_login();

if (!isset($_SESSION['utype']) || $_SESSION['utype'] !== 'houser') {
    logout();
    header('Location: ../login.php');
    exit;
}

$conn = db();
$data = [];
// ---------- DELETE RECORD ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int) $_POST['delete_id'];

    if ($deleteId > 0) {
        $stmt = $conn->prepare("DELETE FROM ho_senior_managers_fuel_acl_cables WHERE id = ?");
        $stmt->bind_param("i", $deleteId);
        $stmt->execute();
        $stmt->close();
    }

    // Refresh page so deleted record disappears
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
// Fetch data (latest first)
$sql = "SELECT id, report_year, report_month, fuel_litres, created_by, created_at
        FROM ho_senior_managers_fuel_acl_cables
        ORDER BY id DESC
        LIMIT 15";


$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $data[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fuel Consumption – Senior Executive Vehicles Head Office (View Data)</title>

    <link rel="stylesheet" href="../styles/indexstyle.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Jockey+One&display=swap" rel="stylesheet">

    <style>
        .content-wrap {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 15px;
        }

        .card-ui {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
        }

        .table thead th {
            background: #f8fafc;
            font-weight: 800;
            color: #0f172a;
        }

        .badge-scope1 {
            background: #ef4444;
        }
    </style>
</head>

<body>

    <!-- ===== HEADER (UNCHANGED) ===== -->
    <div class="topbar">
        <h1 class="topbar-text">Welcome <?php echo htmlspecialchars(current_username()); ?></h1>
        <a href="../logout.php">
            <h1 class="topbar-logout">Logout &nbsp;</h1>
        </a>
        <h1 class="topbar-username"><?php echo htmlspecialchars(current_username()); ?>&nbsp;</h1>
    </div>

    <div class="content-wrap">

        <!-- Top info -->
        <div class="card card-ui mb-3">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-1 fw-bold">
                        <i class="bi bi-car-front-fill text-danger"></i>
                        <!-- <i class="bi bi-fire text-danger"></i> -->
                        Monthly Fuel Consumption – Senior Executive Vehicles Head Office
                    </h4>
                    <div class="text-muted fw-semibold">
                        ACL Cables PLC - Head Office | Scope 1 – Direct GHG Emissions
                    </div>
                </div>

                <div class="d-flex gap-2">


                    <a href="diesel_senior_acl_cables_ho.php" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Enter Data
                    </a>

                    <a href="dashboard.php" class="btn btn-outline-primary">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card card-ui">
            <div class="card-body">

                <?php if (empty($data)): ?>
                    <div class="alert alert-warning rounded-4">
                        <i class="bi bi-info-circle-fill"></i>
                        No data available yet.
                    </div>
                <?php else: ?>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>

                                    <th>Year</th>
                                    <th>Month</th>
                                    <th class="text-end">Fuel (Litres)</th>
                                    <th>Entered By</th>
                                    <th>Date Entered</th>
                                    <th>Edit/Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $row): ?>
                                    <tr>

                                        <td><?php echo htmlspecialchars($row['report_year']); ?></td>
                                        <td><?php echo htmlspecialchars($row['report_month']); ?></td>
                                        <td class="fw-bold text-end">
                                            <?php echo number_format($row['fuel_litres'], 2); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['created_by']); ?></td>
                                        <td>
                                            <?php echo date('Y-m-d', strtotime($row['created_at'])); ?>
                                        </td>
                                        <td>
                                            <a href="diesel_senior_acl_cables_ho_edit_form.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm btn-ghost">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </a>
                                            <form method="post" class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this record?');">
                                                <input type="hidden" name="delete_id" value="<?php echo (int)$row['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-trash3-fill"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php endif; ?>

            </div>
        </div>

    </div>
</body>

</html>