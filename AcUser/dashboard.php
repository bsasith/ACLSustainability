<?php
require_once __DIR__ . '/../auth.php';
require_login();

if (!isset($_SESSION['utype']) || $_SESSION['utype'] !== 'acuser') {
    logout();
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Scope 1 – ACL Factory Accounts (Inventory) / Sujeewa</title>

<link rel="stylesheet" href="../styles/indexstyle.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Jockey+One&display=swap" rel="stylesheet">

<style>
/* ===== Good UI (no clickable rows, only buttons) ===== */
.content-wrap{
    max-width:1100px;
    margin:40px auto;
    padding:0 15px;
}

.header-card{
    border:none;
    border-radius:16px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
    margin-bottom:18px;
    overflow:hidden;
}

.header-card .card-body{
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 60%, #f0f9ff 100%);
}

.section-card{
    border:none;
    border-radius:16px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
    overflow:hidden;
}

.section-top{
    padding:18px 20px;
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
    color:#64748b;
    font-weight:600;
    font-size:.95rem;
}

.kpi-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:16px 20px;
    border-bottom:1px solid #e5e7eb;
    gap:14px;
}

.kpi-row:last-child{ border-bottom:none; }

.kpi-left{
    display:flex;
    align-items:flex-start;
    gap:12px;
    min-width:0;
}

.kpi-icon{
    font-size:1.25rem;
    color:#0f766e;
    margin-top:3px;
    flex:0 0 auto;
}

.kpi-text{
    font-weight:800;
    color:#0f172a;
    line-height:1.25;
}

.kpi-meta{
    display:block;
    margin-top:6px;
    font-size:.9rem;
    color:#64748b;
    font-weight:600;
}

.kpi-actions{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
    justify-content:flex-end;
}

.badge-pill{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:8px 12px;
    border-radius:999px;
    font-size:.78rem;
    letter-spacing:.4px;
    white-space:nowrap;
}

.badge-scope1{
    background:#ef4444;
    color:#fff;
}

.btn-ghost{
    border-radius:999px;
}

@media (max-width: 768px){
    .kpi-row{ align-items:flex-start; flex-direction:column; }
    .kpi-actions{ width:100%; justify-content:flex-start; }
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

    <!-- Responsible Department Card -->
    <div class="card header-card">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-people-fill text-primary fs-5"></i>
                <div>
                    <div class="fw-bold text-dark">Responsible Department &amp; User</div>
                    <div class="text-muted fw-semibold">ACL Factory Accounts (Inventory) / Sujeewa</div>
                </div>
            </div>

            
        </div>
    </div>

    <!-- Scope 1 Section -->
    <div class="card section-card">
        <div class="section-top">
            <h2 class="section-title">
                <i class="bi bi-cloud-minus-fill text-danger"></i>
                Fossil Fuel Consumption (Scope 1 : Direct GHG Emissions)
            </h2>
            <div class="section-sub">
                Monthly fuel consumption tracking for factory operations (Inventory records)
            </div>
        </div>

        <!-- a) Steam Boilers -->
        <div class="kpi-row">
            <div class="kpi-left">
                <i class="bi bi-fire kpi-icon"></i>
                <div>
                    <div class="kpi-text">a) Monthly Diesel Consumption – Steam Boilers</div>
                    <span class="kpi-meta">ACL Cables PLC</span>
                </div>
            </div>
            <div class="kpi-actions">
               
                <a href="diesel_boilers_acl_cables.php" class="btn btn-success btn-sm btn-ghost">
                    <i class="bi bi-plus-circle"></i> Enter Data
                </a>
                <a href="diesel_boilers_acl_cables_view_edit.php" class="btn btn-warning btn-sm btn-ghost">
                    <i class="bi bi-pencil-square"></i> Edit / View
                </a>
            </div>
        </div>

        <!-- b) Forklifts -->
        <div class="kpi-row">
            <div class="kpi-left">
                <i class="bi bi-truck-flatbed kpi-icon"></i>
                <div>
                    <div class="kpi-text">b) Monthly Diesel Consumption – Forklifts</div>
                    <span class="kpi-meta">ACL Cables PLC</span>
                </div>
            </div>
            <div class="kpi-actions">
                
                <a href="diesel_forklifts_acl_cables.php" class="btn btn-success btn-sm btn-ghost">
                    <i class="bi bi-plus-circle"></i> Enter Data
                </a>
                <a href="diesel_forklifts_acl_cables_view_edit.php" class="btn btn-warning btn-sm btn-ghost">
                    <i class="bi bi-pencil-square"></i> Edit / View
                </a>
            </div>
        </div>

        <!-- b) Furnace Oil (kept label as you provided) -->
        <div class="kpi-row">
            <div class="kpi-left">
                <i class="bi bi-droplet-fill kpi-icon"></i>
                <div>
                    <div class="kpi-text">b) Monthly Furnace Oil Consumption</div>
                    <span class="kpi-meta">ACL Metals &amp; Alloys</span>
                </div>
            </div>
            <div class="kpi-actions">
                
                <a href="furnace_oil_acl_metals_alloys.php" class="btn btn-success btn-sm btn-ghost">
                    <i class="bi bi-plus-circle"></i> Enter Data
                </a>
                <a href="furnace_oil_acl_metals_alloys_view_edit.php" class="btn btn-warning btn-sm btn-ghost">
                    <i class="bi bi-pencil-square"></i> Edit / View
                </a>
            </div>
        </div>

        <!-- c) Transportation & Logistics -->
        <div class="kpi-row">
            <div class="kpi-left">
                <i class="bi bi-truck kpi-icon"></i>
                <div>
                    <div class="kpi-text">c) Monthly Diesel Consumption – Transportation &amp; Logistics</div>
                    <span class="kpi-meta">ACL Factory Complex</span>
                </div>
            </div>
            <div class="kpi-actions">
                
                <a href="diesel_transport_logistics_acl_complex.php" class="btn btn-success btn-sm btn-ghost">
                    <i class="bi bi-plus-circle"></i> Enter Data
                </a>
                <a href="diesel_transport_logistics_acl_complex_view_edit.php" class="btn btn-warning btn-sm btn-ghost">
                    <i class="bi bi-pencil-square"></i> Edit / View
                </a>
            </div>
        </div>

        <!-- d) Senior Executive Vehicles -->
        <div class="kpi-row">
            <div class="kpi-left">
                <i class="bi bi-car-front-fill kpi-icon"></i>
                <div>
                    <div class="kpi-text">d) Monthly Diesel Consumption – Senior Executive Vehicles</div>
                    <span class="kpi-meta">ACL Cables Factory Complex</span>
                </div>
            </div>
            <div class="kpi-actions">
                
                <a href="diesel_senior_vehicles_acl_complex.php" class="btn btn-success btn-sm btn-ghost">
                    <i class="bi bi-plus-circle"></i> Enter Data
                </a>
                <a href="diesel_senior_vehicles_acl_complex_view_edit.php" class="btn btn-warning btn-sm btn-ghost">
                    <i class="bi bi-pencil-square"></i> Edit / View
                </a>
            </div>
   
      
    </div>          
    </div>  
      <!-- added -->
    <!-- <div class="card section-card">
        <div class="section-top">
            <h2 class="section-title">
                <i class="bi bi-download text-danger"></i>
               View and Download Data
            </h2>
            <div class="section-sub">
               Download previously entered data for certain periods
            </div>
        </div> -->
<br>
<br>
        <!-- a) Downlaod CSV -->
        <div class="kpi-row">
            <div class="kpi-left">
                 <i class="bi bi-download text-danger"></i>
                <div>
                    <div class="kpi-text">View and Download Data</div>
                    <span class="kpi-meta">Download previously entered data for certain periods</span>
                </div>
            </div>
            <div class="kpi-actions">
               
                <a href="acuser_view_data.php" class="btn btn-success btn-sm btn-ghost">
                    <i class="bi bi-plus-circle"></i> Download/View Data
                </a>
                <!-- <a href="diesel_boilers_acl_cables_view_edit.php" class="btn btn-warning btn-sm btn-ghost">
                    <i class="bi bi-pencil-square"></i> Edit / View
                </a> -->
            </div>
        </div>

</div>  
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>        
</body>
</html>
