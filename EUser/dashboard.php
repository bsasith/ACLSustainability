<?php
require_once __DIR__ . '/../auth.php';
require_login();

if (!isset($_SESSION['utype']) || $_SESSION['utype'] !== 'euser') {
    logout();
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sustainability Measures and KPIs (ISSB Based)</title>

    <!-- Existing CSS -->
    <link rel="stylesheet" href="../styles/indexstyle.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Jockey+One&display=swap" rel="stylesheet">

    <style>
        /* ===== Content styling only – header NOT touched ===== */

        .content-wrap{
            max-width:1100px;
            margin:40px auto;
            padding:0 15px;
        }

        .section-card{
            border:none;
            border-radius:16px;
            box-shadow:0 10px 25px rgba(0,0,0,.08);
            margin-bottom:22px;
        }

        .section-header{
            padding:18px 22px;
            border-bottom:1px solid #e5e7eb;
            background:#f8fafc;
        }

        .section-title{
            margin:0;
            font-family:"Jockey One", sans-serif;
            font-size:22px;
            color:#0f172a;
            display:flex;
            align-items:center;
            gap:10px;
        }

        .section-sub{
            margin-top:6px;
            font-size:.95rem;
            color:#64748b;
            font-weight:600;
        }

        .kpi-link{
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:16px 20px;
            border-bottom:1px solid #e5e7eb;
            transition:.2s ease;
            text-decoration:none;
        }

        .kpi-link:last-child{border-bottom:none;}

        .kpi-link:hover{
            background:#f0f9f5;
            transform:translateX(6px);
        }

        .kpi-left{
            display:flex;
            align-items:flex-start;
            gap:12px;
        }

        .kpi-icon{
            font-size:1.3rem;
            color:#0f766e;
            margin-top:2px;
        }

        .kpi-text{
            font-weight:700;
            color:#0f172a;
        }

        .kpi-meta{
            display:block;
            font-size:.9rem;
            color:#64748b;
            margin-top:4px;
        }

        .badge-pill{
            padding:8px 12px;
            border-radius:999px;
            font-size:.75rem;
            letter-spacing:.6px;
        }

        .badge-scope1{background:#ef4444;}
        .badge-scope2{background:#f59e0b;color:#111827;}
        .badge-renew{background:#22c55e;}
    </style>
</head>

<body>

<!-- ===== HEADER (UNCHANGED) ===== -->
<div class="topbar">
    <h1 class="topbar-text">
        Welcome <?php echo htmlspecialchars(current_username()); ?>
    </h1>

    <a href="..\logout.php">
        <h1 class="topbar-logout">Logout &nbsp;</h1>
    </a>

    <h1 class="topbar-username">
        <?php echo htmlspecialchars(current_username()); ?>&nbsp;
    </h1>
</div>

<!-- ===== CONTENT ===== -->
<div class="content-wrap">

    <!-- Responsible Department -->
    <div class="alert alert-light border rounded-4 mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-people-fill text-primary"></i>
        <strong>Responsible Department & User:</strong> Electrical / Prakash
    </div>

    <!-- ================= Scope 1 ================= -->
    <div class="card section-card">
        <div class="section-header">
            <h2 class="section-title">
                <i class="bi bi-fuel-pump-diesel"></i>
                Fossil Fuel Consumption (Scope 1 : Direct GHG Emissions)
            </h2>
            <div class="section-sub">Diesel usage for standby generators</div>
        </div>

        <a href="diesel_acl.php" class="kpi-link">
            <div class="kpi-left">
                <i class="bi bi-fuel-pump-diesel kpi-icon"></i>
                <div>
                    <span class="kpi-text">a) Monthly Diesel Consumption – Standby Generators</span>
                    <span class="kpi-meta">ACL Cables PLC</span>
                </div>
            </div>
            <span class="badge badge-scope1 badge-pill">
                <i class="bi bi-cloud-minus-fill"></i> Scope 1
            </span>
        </a>

        <a href="diesel_ceylon_copper.php" class="kpi-link">
            <div class="kpi-left">
                <i class="bi bi-fuel-pump-diesel kpi-icon"></i>
                <div>
                    <span class="kpi-text">b) Monthly Diesel Consumption – Standby Generators</span>
                    <span class="kpi-meta">Ceylon Copper Pvt Ltd</span>
                </div>
            </div>
            <span class="badge badge-scope1 badge-pill">
                <i class="bi bi-cloud-minus-fill"></i> Scope 1
            </span>
        </a>
    </div>

    <!-- ================= Scope 2 ================= -->
    <div class="card section-card">
        <div class="section-header">
            <h2 class="section-title">
                <i class="bi bi-lightning-charge-fill"></i>
                Electricity Consumption (Scope 2 : Indirect GHG Emissions)
            </h2>
            <div class="section-sub">Grid electricity consumption (kWh)</div>
        </div>

        <a href="electricity_acl_cables.php" class="kpi-link">
            <div class="kpi-left">
                <i class="bi bi-lightning-charge-fill kpi-icon"></i>
                <div>
                    <span class="kpi-text">a) Monthly Electricity Consumption (kWh)</span>
                    <span class="kpi-meta">ACL Cables PLC</span>
                </div>
            </div>
            <span class="badge badge-scope2 badge-pill">
                <i class="bi bi-cloud-plus-fill"></i> Scope 2
            </span>
        </a>

        <a href="electricity_acl_metals.php" class="kpi-link">
            <div class="kpi-left">
                <i class="bi bi-lightning-charge-fill kpi-icon"></i>
                <div>
                    <span class="kpi-text">b) Monthly Electricity Consumption (kWh)</span>
                    <span class="kpi-meta">ACL Metals</span>
                </div>
            </div>
            <span class="badge badge-scope2 badge-pill">
                <i class="bi bi-cloud-plus-fill"></i> Scope 2
            </span>
        </a>

        <a href="electricity_ceylon_copper.php" class="kpi-link">
            <div class="kpi-left">
                <i class="bi bi-lightning-charge-fill kpi-icon"></i>
                <div>
                    <span class="kpi-text">c) Monthly Electricity Consumption (kWh)</span>
                    <span class="kpi-meta">Ceylon Copper</span>
                </div>
            </div>
            <span class="badge badge-scope2 badge-pill">
                <i class="bi bi-cloud-plus-fill"></i> Scope 2
            </span>
        </a>
    </div>

    <!-- ================= Renewable ================= -->
    <div class="card section-card">
        <div class="section-header">
            <h2 class="section-title">
                <i class="bi bi-sun-fill"></i>
                Renewable Energy Generation
            </h2>
            <div class="section-sub">On-site renewable electricity generation</div>
        </div>

        <a href="solar_acl_cables.php" class="kpi-link">
            <div class="kpi-left">
                <i class="bi bi-sun-fill kpi-icon"></i>
                <div>
                    <span class="kpi-text">a) Solar Electricity Generation</span>
                    <span class="kpi-meta">ACL Cables PLC</span>
                </div>
            </div>
            <span class="badge badge-renew badge-pill">
                <i class="bi bi-check-circle-fill"></i> Renewable
            </span>
        </a>
    </div>

</div>

</body>
</html>
