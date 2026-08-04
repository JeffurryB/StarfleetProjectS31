<?php
// 1. INTEGRATE AUTHENTICATION FRAMEWORK
include('session.php'); 
include('config.php'); 

// Secure production environment variables
ini_set('display_errors', 0); 
error_reporting(E_ALL);

// 2. QUERY THE COMPLETE STARFLEET MANIFEST
// Pulls all registered vessels sorted alphabetically by ship name
$query = "SELECT ship_name, ncc_number, captain_name, status, quadrant FROM starships ORDER BY ship_name ASC";
$result = mysqli_query($conn, $query);

$starships_list = [];

if ($result) {
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $starships_list[] = $row;
    }
    mysqli_free_result($result);
} else {
    // Fallback if table query fails or database times out
    $error_log_msg = "CRITICAL MAINFRAME REJECTION // DATASTREAM LOG OFFLINE";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS Starfleet Active Registry Array</title>
    <style>
        :root{--lcars-purple:#9966cc;--lcars-orange:#ff9900;--lcars-pink:#cc6699;--lcars-blue:#33ccff;--lcars-dark-blue:#5588ff;--lcars-bg:#000000}
        body{background-color:var(--lcars-bg);color:#fff;font-family:"Arial Custom","Helvetica Neue",Arial,sans-serif;margin:0;padding:15px;text-transform:uppercase;letter-spacing:1px;overflow-x:hidden}
        .lcars-header{display:flex;align-items:flex-end;margin-bottom:15px}
        .lcars-bar-top{background-color:var(--lcars-purple);height:40px;flex-grow:1;border-bottom-left-radius:20px;margin-right:15px;position:relative}
        .lcars-bar-top::before{content:"SD-2026";position:absolute;left:25px;bottom:3px;color:#000;font-weight:bold;font-size:14px}
        .lcars-title{color:var(--lcars-orange);font-size:28px;font-weight:300;margin:0;line-height:1;white-space:nowrap}
        .lcars-container{display:flex;min-height:calc(100vh - 120px)}
        .lcars-left-bracket{width:150px;display:flex;flex-direction:column;margin-right:20px}
        .lcars-elbow{background-color:var(--lcars-purple);height:60px;border-top-left-radius:20px;border-bottom-left-radius:20px;margin-bottom:15px;position:relative}
        .lcars-elbow::after{content:"";position:absolute;background-color:var(--lcars-bg);width:110px;height:35px;bottom:0;right:0;border-top-left-radius:15px}
        .lcars-menu{display:flex;flex-direction:column;gap:8px}
        .lcars-btn{background-color:var(--lcars-orange);color:#000;padding:10px 15px;text-decoration:none;font-weight:bold;font-size:13px;text-align:right;border-radius:5px 0 0 5px;transition:background .2s;border:none;cursor:pointer;font-family:inherit;letter-spacing:inherit}
        .lcars-btn:hover{background-color:#fcc}
        .btn-blue{background-color:var(--lcars-blue)}
        .btn-blue:hover{background-color:#88e2ff}
        .btn-pink{background-color:var(--lcars-pink)}
        .btn-pink:hover{background-color:#ff99cc}
        .lcars-main-panel{flex-grow:1;display:flex;flex-direction:column}
        .lcars-user-banner{border-bottom:4px solid var(--lcars-blue);padding-bottom:10px;margin-bottom:25px;display:flex;justify-content:space-between;align-items:center}
        .lcars-user-banner h1{margin:0;font-size:22px;color:var(--lcars-blue);font-weight:normal}
        .system-status{font-size:12px;color:var(--lcars-dark-blue)}
        
        /* AUTHENTIC LCARS DATA TABLE STRUCTURE */
        .lcars-table-container { background-color: #111116; border-left: 6px solid var(--lcars-blue); padding: 20px; border-radius: 0 8px 8px 0; margin-bottom: 25px; }
        .lcars-table-title { margin: 0 0 15px 0; color: var(--lcars-blue); font-size: 18px; font-weight: normal; }
        .lcars-table { width: 100%; border-collapse: separate; border-spacing: 0 6px; font-size: 14px; text-align: left; }
        .lcars-table th { color: var(--lcars-orange); font-size: 12px; font-weight: bold; padding: 10px; border-bottom: 2px solid var(--lcars-dark-blue); }
        .lcars-table td { padding: 12px 10px; background-color: #050508; border-top: 1px solid #1a1a24; border-bottom: 1px solid #1a1a24; color: #ddd; }
        
        /* LCARS Pill Borders for Rows */
        .lcars-table td:first-child { border-left: 4px solid var(--lcars-orange); border-top-left-radius: 4px; border-bottom-left-radius: 4px; color: #fff; font-weight: bold; }
        .lcars-table td:last-child { border-top-right-radius: 4px; border-bottom-right-radius: 4px; }
        
        /* Contextual Status Colors */
        .status-active { color: var(--lcars-blue) !important; font-weight: bold; }
        .status-refit { color: var(--lcars-purple) !important; }
        .status-damaged { color: var(--lcars-pink) !important; }
        .status-mia { color: #ff3333 !important; }
    </style>
</head>
<body>
    <header class="lcars-header">
        <div class="lcars-bar-top"></div>
        <h2 class="lcars-title">ACTIVE ROSTER DISPLAY MODALITY</h2>
    </header>
    
    <div class="lcars-container">
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <div class="lcars-menu">
                <a href="welcome.php" class="lcars-btn">MAIN TERM</a>
                <a href="web_course.php" class="lcars-btn btn-blue">CURRICULUM</a>
                <?php if (isset($_SESSION['login_user'])): ?>
                    <!-- Optional shortcut linking back to management if their user state has admin rights -->
                    <a href="ship_mgt.php" class="lcars-btn btn-pink">ASSET MGT</a>
                <?php endif; ?>
            </div>
        </nav>
        
        <main class="lcars-main-panel">
            <div class="lcars-user-banner">
                <h1>STARFLEET VESSEL MASTER MANIFEST</h1>
                <div class="system-status">
                    <?php echo isset($error_log_msg) ? htmlspecialchars($error_log_msg) : "TOTAL VESSELS REGISTERED: " . count($starships_list); ?>
                </div>
            </div>

            <div class="lcars-table-container">
                <h3 class="lcars-table-title">CURRENT FLEET METRICS AND TELEMETRY</h3>
                
                <?php if (!empty($starships_list)): ?>
                    <table class="lcars-table">
                        <thead>
                            <tr>
                                <th>VESSEL NAME</th>
                                <th>REGISTRY NO.</th>
                                <th>COMMANDING OFFICER</th>
                                <th>QUADRANT SECTOR</th>
                                <th>MISSION STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($starships_list as $ship): ?>
                                <?php 
                                    // Assign custom structural text status tags based on data configurations
                                    $status_class = "status-active";
                                    if ($ship['status'] === 'Drydock Refit') $status_class = "status-refit";
                                    if ($ship['status'] === 'Damaged // Inoperable') $status_class = "status-damaged";
                                    if ($ship['status'] === 'Missing In Action') $status_class = "status-mia";
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ship['ship_name']); ?></td>
                                    <td style="color:var(--lcars-blue);"><?php echo htmlspecialchars($ship['ncc_number']); ?></td>
                                    <td><?php echo htmlspecialchars($ship['captain_name']); ?></td>
                                    <td><?php echo htmlspecialchars($ship['quadrant']); ?> QUADRANT</td>
                                    <td class="<?php echo $status_class; ?>"><?php echo htmlspecialchars($ship['status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="color:var(--lcars-pink); padding: 15px 0;">NO STARFLEET ASSET REGISTRIES DETECTED IN LOGICAL MATRIX ARCHIVE.</div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
