<?php
require_once __DIR__ . '/../auth.php';
require_login();

// ✅ E USER ROLE (Prakash)
if (!isset($_SESSION['utype']) || $_SESSION['utype'] !== 'euser') {
    logout();
    header('Location: login.php');
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = db();

$monthsMap = [
    'January'=>1,'February'=>2,'March'=>3,'April'=>4,'May'=>5,'June'=>6,
    'July'=>7,'August'=>8,'September'=>9,'October'=>10,'November'=>11,'December'=>12
];
$monthsRev = array_flip($monthsMap);

function monthCaseSql($col = 'report_month') {
    return "CASE $col
        WHEN 'January' THEN 1
        WHEN 'February' THEN 2
        WHEN 'March' THEN 3
        WHEN 'April' THEN 4
        WHEN 'May' THEN 5
        WHEN 'June' THEN 6
        WHEN 'July' THEN 7
        WHEN 'August' THEN 8
        WHEN 'September' THEN 9
        WHEN 'October' THEN 10
        WHEN 'November' THEN 11
        WHEN 'December' THEN 12
        ELSE 0 END";
}

/**
 * ✅ KPI mapping for EUser dashboard ONLY
 * Most tables: company_name + emission_scope + activity_type
 * Solar table: company_name + energy_type + activity_type
 */
$KPI = [
    'diesel_generators_acl_cables' => [
        'title' => 'Monthly Diesel Consumption – ACL Cables Generators',
        'value_col' => 'diesel_litres',
        'unit' => 'Litres',
        'icon' => 'bi-fuel-pump-diesel-fill',
        'company_col' => 'company_name',
        'scope_col' => 'emission_scope',
        'activity_col' => 'activity_type'
    ],
    'diesel_generators_ceylon_copper' => [
        'title' => 'Monthly Diesel Consumption – Ceylon Copper Generators',
        'value_col' => 'diesel_litres',
        'unit' => 'Litres',
        'icon' => 'bi-fuel-pump-fill',
        'company_col' => 'company_name',
        'scope_col' => 'emission_scope',
        'activity_col' => 'activity_type'
    ],
    'electricity_acl_cables' => [
        'title' => 'Monthly Electricity Consumption (kWh) ACL Cables',
        'value_col' => 'electricity_kwh',
        'unit' => 'kWh',
        'icon' => 'bi-lightning-charge',
        'company_col' => 'company_name',
        'scope_col' => 'emission_scope',
        'activity_col' => 'activity_type'
    ],
    'electricity_acl_metals_alloys' => [
        'title' => 'Monthly Electricity Consumption (kWh) ACL Metals & Alloys',
        'value_col' => 'electricity_kwh',
        'unit' => 'kWh',
        'icon' => 'bi-plug-fill',
        'company_col' => 'company_name',
        'scope_col' => 'emission_scope',
        'activity_col' => 'activity_type'
    ],
    'electricity_ceylon_copper' => [
        'title' => 'Monthly Electricity Consumption (kWh) Ceylon Copper',
        'value_col' => 'electricity_kwh',
        'unit' => 'kWh',
        'icon' => 'bi-building-fill-check',
        'company_col' => 'company_name',
        'scope_col' => 'emission_scope',
        'activity_col' => 'activity_type'
    ],
    'solar_generation_acl_cables' => [
        'title' => 'Solar Electricity Generation (kWh) ACL Cables',
        'value_col' => 'solar_kwh',
        'unit' => 'kWh',
        'icon' => 'bi-sun',
        'company_col' => 'company_name',
        'scope_col' => 'energy_type',   // ✅ solar table uses energy_type
        'activity_col' => 'activity_type'
    ],
];

// -------------------------
// Read filters (GET)
// -------------------------
$startPeriod = $_GET['start'] ?? ''; // YYYY-MM
$endPeriod   = $_GET['end'] ?? '';   // YYYY-MM
$tableSel    = $_GET['kpi'] ?? '';   // KPI table name

$data = [];
$errorMsg = '';

function parsePeriod($p) {
    if (!preg_match('/^\d{4}-\d{2}$/', $p)) return null;
    $y = (int)substr($p, 0, 4);
    $m = (int)substr($p, 5, 2);
    if ($y < 2000 || $m < 1 || $m > 12) return null;
    return [$y, $m];
}

$sp = $startPeriod ? parsePeriod($startPeriod) : null;
$ep = $endPeriod ? parsePeriod($endPeriod) : null;

// Default current month if missing
if (!$sp) {
    $sp = [(int)date('Y'), (int)date('n')];
    $startPeriod = sprintf('%04d-%02d', $sp[0], $sp[1]);
}
if (!$ep) {
    $ep = [(int)date('Y'), (int)date('n')];
    $endPeriod = sprintf('%04d-%02d', $ep[0], $ep[1]);
}

$startKey = $sp[0]*100 + $sp[1];
$endKey   = $ep[0]*100 + $ep[1];

if ($startKey > $endKey) {
    [$sp, $ep] = [$ep, $sp];
    [$startKey, $endKey] = [$endKey, $startKey];
    $startPeriod = sprintf('%04d-%02d', $sp[0], $sp[1]);
    $endPeriod   = sprintf('%04d-%02d', $ep[0], $ep[1]);
}

// ✅ Always require a valid KPI selection; default to first
if ($tableSel === '' || !isset($KPI[$tableSel])) {
    $tableSel = array_key_first($KPI);
}

$cfg = $KPI[$tableSel];

// -------------------------
// Query data + totals + chart aggregation
// -------------------------
$totalValue = 0.0;
$chartAgg = [];
$case = monthCaseSql('report_month');

try {
    $valCol      = $cfg['value_col'];
    $companyCol  = $cfg['company_col'];
    $scopeCol    = $cfg['scope_col'];
    $activityCol = $cfg['activity_col'];

    // Rows (newest first)
    $sql = "SELECT id, report_year, report_month,
                   $valCol AS kpi_value,
                   $companyCol AS company_name,
                   $scopeCol AS kpi_scope,
                   $activityCol AS activity_type,
                   created_by, created_at
            FROM $tableSel
            WHERE (report_year * 100 + $case) BETWEEN ? AND ?
            ORDER BY (report_year * 100 + $case) DESC, created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $startKey, $endKey);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($r = $res->fetch_assoc()) {
        $data[] = $r;

        $val = isset($r['kpi_value']) ? (float)$r['kpi_value'] : 0.0;
        $totalValue += $val;

        $mName = $r['report_month'] ?? '';
        $mNo = $monthsMap[$mName] ?? 0;
        $y = (int)($r['report_year'] ?? 0);

        if ($y > 0 && $mNo > 0) {
            $key = sprintf('%04d-%02d', $y, $mNo);
            if (!isset($chartAgg[$key])) $chartAgg[$key] = 0.0;
            $chartAgg[$key] += $val;
        }
    }
    $stmt->close();

} catch (mysqli_sql_exception $e) {
    $errorMsg = "Database error: " . htmlspecialchars($e->getMessage());
}

ksort($chartAgg);
$chartLabels = array_keys($chartAgg);
$chartValues = array_map(fn($v) => round((float)$v, 2), array_values($chartAgg));

$headerTitle = $cfg['title'];
$headerSub   = "Period: {$startPeriod} to {$endPeriod} | Newest first";
$totalUnit   = $cfg['unit'] ?? 'Units';

// -------------------------
// CSV download
// -------------------------
if (isset($_GET['download']) && $_GET['download'] === 'csv' && empty($errorMsg)) {

    $fname = 'kpi_' . preg_replace('/[^a-zA-Z0-9_-]+/', '_', $tableSel) . '_' . $startPeriod . '_to_' . $endPeriod . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$fname.'"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['KPI', 'Company', 'Scope/Energy', 'Activity', 'Year', 'Month', 'Value', 'Unit', 'Entered By', 'Date Entered']);

    foreach ($data as $r) {
        $val = isset($r['kpi_value']) ? (float)$r['kpi_value'] : 0.0;

        fputcsv($out, [
            $cfg['title'],
            $r['company_name'] ?? '',
            $r['kpi_scope'] ?? '',
            $r['activity_type'] ?? '',
            $r['report_year'] ?? '',
            $r['report_month'] ?? '',
            number_format($val, 2, '.', ''),
            $totalUnit,
            $r['created_by'] ?? '',
            isset($r['created_at']) ? date('Y-m-d', strtotime($r['created_at'])) : ''
        ]);
    }

    fclose($out);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Period Results – EUser</title>

<link rel="stylesheet" href="../styles/indexstyle.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Jockey+One&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<style>
.content-wrap{max-width:1100px;margin:40px auto;padding:0 15px;}
.header-card,.section-card{border:none;border-radius:16px;box-shadow:0 10px 25px rgba(0,0,0,.08);overflow:hidden;}
.header-card .card-body{background:linear-gradient(135deg,#f8fafc 0%,#ffffff 60%,#f0f9ff 100%);}
.section-top{padding:18px 20px;border-bottom:1px solid #e5e7eb;background:#f8fafc;}
.section-title{margin:0;font-family:"Jockey One",sans-serif;font-size:22px;color:#0f172a;display:flex;align-items:center;gap:10px;}
.section-sub{margin-top:6px;color:#64748b;font-weight:600;font-size:.95rem;}
.filter-row{display:flex;gap:12px;flex-wrap:wrap;align-items:end;}
.filter-row .form-label{font-weight:800;}
.btn-ghost{border-radius:999px;}
.table thead th{background:#f8fafc;font-weight:800;color:#0f172a;}
.kpi-chip{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;background:#f1f5f9;color:#0f172a;font-weight:800;font-size:.85rem;}
.kpi-chip i{color:#0f766e;}

.total-card{border-radius:16px;border:1px solid #e5e7eb;background:linear-gradient(135deg,#ffffff 0%,#f8fafc 100%);padding:16px 18px;display:flex;align-items:center;justify-content:space-between;gap:12px;}
.total-left{display:flex;align-items:center;gap:12px;}
.total-icon{width:44px;height:44px;border-radius:14px;display:flex;align-items:center;justify-content:center;background:#ecfeff;border:1px solid #cffafe;color:#0e7490;font-size:20px;}
.total-title{font-weight:900;color:#0f172a;}
.total-sub{color:#64748b;font-weight:700;font-size:.9rem;}
.total-value{text-align:right;}
.total-value .num{font-size:1.4rem;font-weight:900;color:#0f172a;}
.total-value .unit{color:#64748b;font-weight:800;font-size:.9rem;}

.chart-wrap{border-radius:16px;border:1px solid #e5e7eb;background:#ffffff;padding:14px 16px 10px 16px;}
.chart-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:8px;}
.chart-title{font-weight:900;color:#0f172a;}
.chart-note{color:#64748b;font-weight:700;font-size:.9rem;}
</style>
</head>

<body>

<div class="topbar">
    <h1 class="topbar-text">Welcome <?php echo htmlspecialchars(current_username()); ?></h1>
    <a href="../logout.php"><h1 class="topbar-logout">Logout &nbsp;</h1></a>
    <h1 class="topbar-username"><?php echo htmlspecialchars(current_username()); ?>&nbsp;</h1>
</div>

<div class="content-wrap">

    <div class="card header-card mb-3">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-calendar2-range text-primary fs-5"></i>
                <div>
                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($headerTitle); ?></div>
                    <div class="text-muted fw-semibold"><?php echo htmlspecialchars($headerSub); ?></div>
                </div>
            </div>

            <a href="dashboard.php" class="btn btn-outline-primary btn-sm btn-ghost">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </div>
    </div>

    <div class="card section-card mb-3">
        <div class="section-top">
            <h2 class="section-title"><i class="bi bi-funnel-fill text-primary"></i> Filter by Period</h2>
            <div class="section-sub">Choose start and end Month/Year, and select the KPI table to view.</div>
        </div>

        <div class="card-body">
            <form method="get" class="filter-row">
                <div>
                    <label class="form-label">Start (Month/Year)</label>
                    <input type="month" name="start" value="<?php echo htmlspecialchars($startPeriod); ?>" class="form-control" required>
                </div>

                <div>
                    <label class="form-label">End (Month/Year)</label>
                    <input type="month" name="end" value="<?php echo htmlspecialchars($endPeriod); ?>" class="form-control" required>
                </div>

                <div>
                    <label class="form-label">Select KPI</label>
                    <select name="kpi" class="form-select" required>
                        <?php foreach ($KPI as $t => $c): ?>
                            <option value="<?php echo htmlspecialchars($t); ?>" <?php echo ($tableSel===$t)?'selected':''; ?>>
                                <?php echo htmlspecialchars($c['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ms-auto d-flex gap-2">
                    <button class="btn btn-success btn-ghost" name="submit" value="1">
                        <i class="bi bi-search"></i> Show Results
                    </button>

                    <button class="btn btn-outline-secondary btn-ghost" name="download" value="csv">
                        <i class="bi bi-download"></i> Download CSV
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card section-card mb-3">
        <div class="section-top">
            <h2 class="section-title"><i class="bi bi-table text-success"></i> Results</h2>
            <div class="section-sub">Showing newest records first.</div>
        </div>

        <div class="card-body">

            <?php if ($errorMsg): ?>
                <div class="alert alert-danger rounded-4">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?php echo $errorMsg; ?>
                </div>

            <?php elseif (empty($data)): ?>
                <div class="alert alert-warning rounded-4">
                    <i class="bi bi-info-circle-fill"></i>
                    No records found for the selected period.
                </div>

            <?php else: ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Scope/Energy</th>
                                <th>Activity</th>
                                <th>Year</th>
                                <th>Month</th>
                                <th class="text-end">Value</th>
                                <th>Entered By</th>
                                <th>Date Entered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $r): ?>
                                <tr>
                                    <td class="text-muted fw-semibold"><?php echo htmlspecialchars($r['company_name'] ?? ''); ?></td>
                                    <td class="text-muted fw-semibold"><?php echo htmlspecialchars($r['kpi_scope'] ?? ''); ?></td>
                                    <td class="text-muted fw-semibold"><?php echo htmlspecialchars($r['activity_type'] ?? ''); ?></td>

                                    <td><?php echo htmlspecialchars($r['report_year']); ?></td>
                                    <td><?php echo htmlspecialchars($r['report_month']); ?></td>

                                    <td class="text-end fw-bold">
                                        <?php
                                        $val = isset($r['kpi_value']) ? (float)$r['kpi_value'] : 0.0;
                                        echo number_format($val, 2);
                                        ?>
                                    </td>

                                    <td><?php echo htmlspecialchars($r['created_by'] ?? ''); ?></td>
                                    <td><?php echo isset($r['created_at']) ? date('Y-m-d', strtotime($r['created_at'])) : ''; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- TOTAL -->
                <div class="mt-3 total-card">
                    <div class="total-left">
                        <div class="total-icon"><i class="bi bi-calculator"></i></div>
                        <div>
                            <div class="total-title">Total for Selected Period</div>
                            <div class="total-sub">
                                KPI: <?php echo htmlspecialchars($cfg['title']); ?> |
                                Period: <?php echo htmlspecialchars($startPeriod); ?> to <?php echo htmlspecialchars($endPeriod); ?>
                            </div>
                        </div>
                    </div>
                    <div class="total-value">
                        <div class="num"><?php echo number_format($totalValue, 2); ?></div>
                        <div class="unit"><?php echo htmlspecialchars($totalUnit); ?></div>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </div>

    <?php if (!$errorMsg && !empty($data) && !empty($chartLabels)): ?>
    <div class="card section-card">
        <div class="section-top">
            <h2 class="section-title"><i class="bi bi-graph-up-arrow text-primary"></i> Trend Graph</h2>
            <div class="section-sub">Month vs total value (sum of all rows in that month).</div>
        </div>

        <div class="card-body">
            <div class="chart-wrap">
                <div class="chart-head">
                    <div>
                        <div class="chart-title"><?php echo htmlspecialchars($cfg['title']); ?></div>
                        <div class="chart-note">Unit: <?php echo htmlspecialchars($totalUnit); ?></div>
                    </div>
                    <div class="text-muted fw-semibold">Line Graph</div>
                </div>

                <canvas id="trendChart" height="90"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php if (!$errorMsg && !empty($data) && !empty($chartLabels)): ?>
<script>
(function(){
    const labels = <?php echo json_encode($chartLabels, JSON_UNESCAPED_SLASHES); ?>;
    const values = <?php echo json_encode($chartValues, JSON_UNESCAPED_SLASHES); ?>;

    const monthNames = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
    const prettyLabels = labels.map(k => {
        const [y, m] = k.split("-");
        const mi = parseInt(m, 10) - 1;
        return (monthNames[mi] || m) + " " + y;
    });

    const ctx = document.getElementById('trendChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: prettyLabels,
            datasets: [{
                label: 'Value (<?php echo htmlspecialchars($totalUnit); ?>)',
                data: values,
                tension: 0.35,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const v = Number(context.parsed.y || 0);
                            return " " + v.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) + " <?php echo htmlspecialchars($totalUnit); ?>";
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true } },
                y: {
                    beginAtZero: true,
                    ticks: { callback: (value) => Number(value).toLocaleString() }
                }
            }
        }
    });
})();
</script>
<?php endif; ?>

</body>
</html>
