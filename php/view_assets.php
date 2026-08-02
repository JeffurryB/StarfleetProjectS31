<?php
// 1. Session, Config, and Layout Linkage
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once("session.php");
include_once("config.php"); 
include_once("functions.php"); // 🔒 Ensures background security logging helpers are accessible

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

// 🔒 CSRF INITIALIZATION MATRIX: Seed authorization verification signatures
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
        header("Location: notauthorized.php?error=clearance_insufficient");
        exit;
    }
} else {
    $stmt_auth->close();
    header("Location: notauthorized.php");
    exit;
}
$stmt_auth->close();

// 🛠️ SUBSYSTEM MODULE: SECURE ASSET REMOVAL PROCESSOR WITH DETAILED AUDITING LOGS
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'delete_asset') {
    
    // 🔒 CSRF FIREWALL: Drop cross-site unauthenticated wipe operations instantly
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        header("HTTP/1.1 403 Forbidden");
        die("CRITICAL SECURITY ERROR: CSRF MATRIX MALFUNCTION. ASSET REPURGING REFUSED.");
    }

    $target_delete_id = isset($_POST['delete_aid']) ? intval($_POST['delete_aid']) : 0;

    if ($target_delete_id > 0) {
        // 📡 1. PRE-DELETION SCAN: Capture the metadata before it is deleted from the database
        $asset_name = "UNKNOWN_NAME";
        $asset_uuid = "UNKNOWN_UUID";
        $asset_type = "UNKNOWN_TYPE";
        
        $fetch_sql = "SELECT name, uuid, type FROM assets WHERE aid = ? LIMIT 1";
        if ($stmt_fetch = $conn->prepare($fetch_sql)) {
            $stmt_fetch->bind_param("i", $target_delete_id);
            $stmt_fetch->execute();
            $res_fetch = $stmt_fetch->get_result();
            if ($res_fetch && $row_fetch = $res_fetch->fetch_assoc()) {
                $asset_name = $row_fetch['name'];
                $asset_uuid = $row_fetch['uuid'];
                $asset_type = $row_fetch['type'];
            }
            $stmt_fetch->close();
        }

        // Start a transactional operation to guarantee all operations pass or fail as one block
        $conn->begin_transaction();

        try {
            // Delete from child permission mapping matrix first to prevent constraint violations
            $delete_perms_sql = "DELETE FROM asset_perms WHERE asset_id = ?";
            $stmt_del_p = $conn->prepare($delete_perms_sql);
            $stmt_del_p->bind_param("i", $target_delete_id);
            $stmt_del_p->execute();
            $stmt_del_p->close();

            // Delete from parent registry matrix
            $delete_asset_sql = "DELETE FROM assets WHERE aid = ?";
            $stmt_del_a = $conn->prepare($delete_asset_sql);
            $stmt_del_a->bind_param("i", $target_delete_id);
            $stmt_del_a->execute();
            $stmt_del_a->close();

            // Commit transaction changes down to database tables
            $conn->commit();
            
            // 📝 2. DETAILED SECURITY TELEMETRY LOG: Writes complete name, uuid, and type to security_logs
            $log_telemetry = "Permanently purged asset registry record. Designation: [" . $asset_name . "] // UUID Token: [" . $asset_uuid . "] // Class ID: [" . $asset_type . "]. Relational permission sets deleted completely.";
            
            record_security_log(
                $conn, 
                $login_session,      // The admin username committing the delete
                'DELETE',            // The log database action type 
                'ASSETS',            // The dashboard module system block
                $asset_name,         // TARGET identifier field
                $log_telemetry       // Detailed text entry listing exactly what was removed
            );
            
            // Refresh layout matrix view state with clean status message
            header("Location: view_assets.php?status=deleted");
            exit();
        } catch (Exception $e) {
            // Roll back the entire operation safely if database interruptions happen
            $conn->rollback();
            $delete_error = "SYS_ERR: DELETION CORE ROUTINE ENCOUNTERED INTERRUPT OVERLAY REJECTION.";
        }
    }
}

// 2. FETCH ALL ASSETS JOINED WITH THEIR RELATIONAL PERMISSION SETS
$all_assets = [];
$sql_assets = "SELECT a.aid, a.uuid, a.type, a.name, p.Modify, p.Copy, p.Transfer 
               FROM assets a 
               LEFT JOIN asset_perms p ON a.aid = p.asset_id 
               ORDER BY a.name ASC";
$result_assets = $conn->query($sql_assets);

if ($result_assets && $result_assets->num_rows > 0) {
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
            --lcars-dark-red: #441111;
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
        
        /* Asset Permissions Badges Blocks */
        .perm-badge-container { display: flex; gap: 6px; font-family: "Courier New", Courier, monospace; font-weight: bold; font-size: 14px; }
        .perm-flag { padding: 2px 8px; border-radius: 3px; display: inline-block; text-align: center; min-width: 14px; }
        .perm-allowed { background-color: #113311; color: var(--lcars-green); border: 1px solid var(--lcars-green); }
        .perm-denied { background-color: var(--lcars-dark-red); color: #773333; border: 1px solid #552222; }
        .telemetry-banner { padding: 15px; font-weight: bold; font-size: 13px; margin-bottom: 20px; border-radius: 4px; border-left: 6px solid; text-transform: none; }
        .telemetry-success { background-color: #112211; color: #55ff55; border-left-color: var(--lcars-green); }
        .telemetry-failure { background-color: #221111; color: #ff5555; border-left-color: #cc3333; }
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
            <a href="welcome.php" class="lcars-btn">CENTRAL</a>
            <a href="asset.php" class="lcars-btn" style="background-color: var(--lcars-pink);">ASSET MGT</a>
        </nav>
        
        <!-- Main Data Telemetry Display Grid -->
        <main class="lcars-main-panel">
            <h2>GRID ENVIRONMENT INVENTORY LISTING</h2>
            <p style="text-transform: none; color: #aaa; margin-bottom: 20px;">
                Authorized Terminal Node: <strong><?php echo htmlspecialchars($login_session); ?></strong> [CLEARANCE: LEVEL 1 MASTER ADMIN]
            </p>

            <?php if (isset($_GET['status']) && $_GET['status'] === 'deleted'): ?>
                <div class="telemetry-banner telemetry-success">
                    OK | TERMINAL CONFIRMATION: ASSET CORE RECORD PURGED FROM FLEET DATA REGISTRY.
                </div>
            <?php endif; ?>

            <?php if (isset($delete_error)): ?>
                <div class="telemetry-banner telemetry-failure">
                    <?php echo htmlspecialchars($delete_error); ?>
                </div>
            <?php endif; ?>
            
            <div class="manifest-container">
                <?php if (!empty($all_assets)): ?>
                    <table class="manifest-table">
                        <thead>
                            <tr>
                                <th style="width: 6%;">AID</th>
                                <th style="width: 24%;">ASSET NAME</th>
                                <th style="width: 15%;">RESOURCE TYPE</th>
                                <th style="width: 35%;">GRID OBJECT UUID (IN-WORLD SECTOR)</th>
                                <th style="width: 12%;">PERMISSIONS (MCT)</th>
                                <th style="width: 8%;">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_assets as $asset): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($asset['aid']); ?></td>
                                    <td class="asset-name"><?php echo htmlspecialchars($asset['name']); ?></td>
                                    <td class="asset-type"><?php echo htmlspecialchars($asset['type']); ?></td>
                                    <td class="asset-uuid"><?php echo htmlspecialchars($asset['uuid']); ?></td>
                                    
                                    <td>
                                        <div class="perm-badge-container">
                                            <span class="perm-flag <?php echo ((int)$asset['Modify'] === 1) ? 'perm-allowed' : 'perm-denied'; ?>">
                                                <?php echo ((int)$asset['Modify'] === 1) ? 'M' : '-'; ?>
                                            </span>
                                            <span class="perm-flag <?php echo ((int)$asset['Copy'] === 1) ? 'perm-allowed' : 'perm-denied'; ?>">
                                                <?php echo ((int)$asset['Copy'] === 1) ? 'C' : '-'; ?>
                                            </span>
                                            <span class="perm-flag <?php echo ((int)$asset['Transfer'] === 1) ? 'perm-allowed' : 'perm-denied'; ?>">
                                                <?php echo ((int)$asset['Transfer'] === 1) ? 'T' : '-'; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" action="" onsubmit="return confirm('WARNING: CRITICAL SYSTEM CMD // PURGE THIS ASSET DATA MATRIX PERMANENTLY?');" style="margin:0; padding:0;">
                                            <!-- 🔒 CSRF SHIELD VALUE OVERLAY -->
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                                            <input type="hidden" name="action" value="delete_asset">
                                            <input type="hidden" name="delete_aid" value="<?php echo (int)$asset['aid']; ?>">
                                            <button type="submit" class="lcars-btn" style="background-color: #cc3333; color: #ffffff; padding: 4px 8px; font-size: 11px; text-align: center; width: 100%; border-radius: 3px; cursor: pointer;">
                                                DELETE
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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
