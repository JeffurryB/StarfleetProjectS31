<?php
// 1. Session, Config, and Layout Linkage
include("session.php");
include("config.php"); 
include("functions.php"); // 🔒 Ensures background security logging helpers are accessible

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the local file script maps cleanly to your config's database variable
if (isset($db) && !isset($conn)) {
    $conn = $db;
}

// Barrier 1: Check if an active authenticated session identifier exists
if (!isset($login_session)) {
    header("Location: notauthorized.php");
    exit;
}

// Barrier 2: SECURE MASTER AUTHORIZATION CHECK (Verify 'dh' column is exactly 1)
$stmt_auth = $conn->prepare("SELECT dh FROM accounts WHERE username = ? LIMIT 1");
$stmt_auth->bind_param("s", $login_session);
$stmt_auth->execute();
$res_auth = $stmt_auth->get_result();

if ($res_auth && $res_auth->num_rows > 0) {
    $current_user = $res_auth->fetch_assoc();
    if ((int)$current_user['dh'] !== 1) {
        $stmt_auth->close();
        header("Location: notauthorized.php?error=access_denied");
        exit;
    }
} else {
    $stmt_auth->close();
    header("Location: notauthorized.php");
    exit;
}
$stmt_auth->close();

// 2. FETCH ALL ASSETS FROM IN-WORLD DATA GRID TABLE
$all_assets = [];
// Queries target schema table fields: aid, uuid, type, name
$sql_assets = "SELECT aid, uuid, type, name FROM assets ORDER BY name ASC";
$result_assets = $conn->query($sql_assets);

if ($result_assets && $result_assets->num_rows > 0) {
    /* @var mysqli_result $result_assets */
    while ($row = $result_assets->fetch_assoc()) {
        $all_assets[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS Asset Matrix Manifest</title>
    <style>
        :root {
            --lcars-purple: #9966cc; --lcars-orange: #ff9900;
            --lcars-pink: #cc6699; --lcars-blue: #33ccff;
            --lcars-bg: #000000; --lcars-green: #33cc33;
            --lcars-dark-red: #331111;
        }
        body {
            background-color: var(--lcars-bg); color: #ffffff;
            font-family: Arial, sans-serif; margin: 0; padding: 15px;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .lcars-header { display: flex; align-items: flex-end; margin-bottom: 15px; }
        .lcars-bar-top { background-color: var(--lcars-orange); height: 40px; flex-grow: 1; border-bottom-left-radius: 20px; margin-right: 15px; position: relative; }
        .lcars-bar-top::before { content: "SYS-ASSET-LOG-99"; position: absolute; left: 25px; bottom: 3px; color: #000000; font-weight: bold; font-size: 14px; }
        .lcars-title { color: var(--lcars-orange); font-size: 28px; font-weight: 300; margin: 0; white-space: nowrap; }
        .lcars-container { display: flex; min-height: 80vh; }
        .lcars-left-bracket { width: 150px; display: flex; flex-direction: column; margin-right: 20px; }
        .lcars-elbow { background-color: var(--lcars-orange); height: 60px; border-top-left-radius: 20px; border-bottom-left-radius: 20px; margin-bottom: 15px; position: relative; }
        .lcars-elbow::after { content: ""; position: absolute; background-color: var(--lcars-bg); width: 110px; height: 35px; bottom: 0; right: 0; border-top-left-radius: 15px; }
        .lcars-btn { background-color: var(--lcars-blue); color: #000000; padding: 10px; text-decoration: none; font-weight: bold; font-size: 13px; text-align: right; margin-bottom: 5px; border-radius: 5px 0 0 5px; }
        .lcars-main-panel { flex-grow: 1; display: flex; flex-direction: column; }
        
        /* Table Layout Styling */
        .manifest-container { background-color: #111116; border-left: 6px solid var(--lcars-purple); padding: 20px; border-radius: 0 10px 10px 0; margin-bottom: 20px; }
        .manifest-table { width: 100%; border-collapse: collapse; margin-top: 15px; text-transform: none; }
        .manifest-table th { background-color: var(--lcars-purple); color: #000000; font-weight: bold; text-align: left; padding: 10px; text-transform: uppercase; font-size: 13px; }
        .manifest-table td { padding: 10px; border-bottom: 1px solid #2d3748; font-size: 14px; font-family: monospace; color: #dddddd; }
        .manifest-table tr:hover { background-color: #1a1a24; }
        .asset-name { color: var(--lcars-orange); font-weight: bold; text-transform: uppercase; font-family: Arial, sans-serif; }
        .asset-type { color: var(--lcars-blue); text-transform: uppercase; }
        .asset-uuid { color: #888888; }
    </style>
</head>
<body>
    <header class="lcars-header">
        <div class="lcars-bar-top"></div>
        <h2 class="lcars-title">IN-WORLD ASSET MANIFEST</h2>
    </header>
    
    <div class="lcars-container">
        <!-- Left Sidebar Navigation Block -->
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <a href="dhpanel.php" class="lcars-btn">CENTRAL</a>
            <a href="asset.php" class="lcars-btn" style="background-color: var(--lcars-pink);">ASSET MGT</a>
        </nav>
        
        <!-- Main Data Telemetry Display Grid -->
        <main class="lcars-main-panel">
            <h2>GRID ENVIRONMENT INVENTORY LISTING</h2>
            <p style="text-transform: none; color: #aaa; margin-bottom: 20px;">
                Authorized Terminal Node: <strong><?php echo htmlspecialchars($login_session); ?></strong> [CLEARANCE: LEVEL 1 MASTER ADMIN]
            </p>
            
            <div class="manifest-container">
                <?php if (!empty($all_assets)): ?>
                    <table class="manifest-table">
                        <thead>
                            <tr>
                                <th style="width: 8%;">AID</th>
                                <th style="width: 30%;">ASSET NAME</th>
                                <th style="width: 15%;">RESOURCE TYPE</th>
                                <th style="width: 47%;">GRID OBJECT UUID (IN-WORLD SECTOR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_assets as $asset): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($asset['aid']); ?></td>
                                    <td class="asset-name"><?php echo htmlspecialchars($asset['name']); ?></td>
                                    <td class="asset-type"><?php echo htmlspecialchars($asset['type']); ?></td>
                                    <td class="asset-uuid"><?php echo htmlspecialchars($asset['uuid']); ?></td>
                                </tr>
                            <?php endforeach; // 🛠️ FIXED: Replaced invalid endphp with endforeach ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: var(--lcars-pink); font-weight: bold; padding: 15px; background: #221111; border-radius: 4px;">
                        TELEMETRY RECOVERY FAILURE: NO ITEM RECORDS FOUND IN THE IN-WORLD DATA MATRIX.
                    </p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
