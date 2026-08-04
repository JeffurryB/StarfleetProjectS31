<?php
// 1. INTEGRATE AUTHENTICATION FRAMEWORK
include('session.php'); 
include('config.php'); 

// Secure production environment variables
ini_set('display_errors', 0); 
error_reporting(E_ALL);

// 2. ADMINISTRATIVE CLEARANCE AUTHORIZATION CHECK
$admin_check_stmt = mysqli_prepare($conn, "SELECT dh FROM accounts WHERE username = ?");
if ($admin_check_stmt) {
    mysqli_stmt_bind_param($admin_check_stmt, "s", $login_session);
    mysqli_stmt_execute($admin_check_stmt);
    $admin_result = mysqli_stmt_get_result($admin_check_stmt);
    
    if ($user_row = mysqli_fetch_array($admin_result, MYSQLI_ASSOC)) {
        if ((int)$user_row['dh'] !== 1) {
            header("Location: notauthrozied.php");
            exit();
        }
    } else {
        header("Location: notauthrozied.php");
        exit();
    }
    mysqli_stmt_close($admin_check_stmt);
} else {
    die("CRITICAL ACCESS CONTROLLER SECTOR FAULT.");
}

// 3. SECURE PARAMETERIZED DATABASE INSERTION ARRAY
$message = "";
$message_class = "lcars-text-line";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'register_vessel') {
    if (!empty($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        
        $ship_name    = trim($_POST['ship_name']);
        $ncc_number   = trim($_POST['ncc_number']);
        $captain_name = trim($_POST['captain_name']);
        $status       = trim($_POST['status']);
        $quadrant     = trim($_POST['quadrant']);

        if (!empty($ship_name) && !empty($ncc_number)) {
            $insert_query = "INSERT INTO starships (ship_name, ncc_number, captain_name, status, quadrant) 
                             VALUES (?, ?, ?, ?, ?) 
                             ON DUPLICATE KEY UPDATE 
                             ship_name = VALUES(ship_name), 
                             captain_name = VALUES(captain_name), 
                             status = VALUES(status), 
                             quadrant = VALUES(quadrant)";
                             
            $stmt = mysqli_prepare($conn, $insert_query);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sssss", $ship_name, $ncc_number, $captain_name, $status, $quadrant);
                if (mysqli_stmt_execute($stmt)) {
                    $message = "SUCCESS // DEPLOYMENT MANIFEST SUBMITTED TO SUBMAINFRAME.";
                    $message_class = "lcars-text-success";
                } else {
                    $message = "COM-LINK FAULT: REGISTRY CONFLICT DETECTED.";
                    $message_class = "lcars-text-error";
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            $message = "REJECTION // SHIP CORE REGISTRY IDENTIFIERS CANNOT BE BLANK.";
            $message_class = "lcars-text-error";
        }
    } else {
        $message = "CRITICAL FIRMWARE REJECTION: CSRF LINK CORRUPTED.";
        $message_class = "lcars-text-error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS Starfleet Asset Controller Array</title>
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
        .system-status.success-alert { color: var(--lcars-blue); font-weight: bold; }
        .system-status.error-alert { color: var(--lcars-pink); font-weight: bold; }
        .lcars-admin-section{background-color:#111116;border-left:6px solid var(--lcars-purple);padding:20px;border-radius:0 8px 8px 0;margin-bottom:25px}
        .lcars-admin-section h3{margin:0 0 15px 0;color:var(--lcars-purple);font-size:18px}
        .form-row{display:flex;flex-direction:column;margin-bottom:15px}
        .form-row label{color:var(--lcars-blue);font-size:12px;margin-bottom:5px;font-weight:bold}
        .lcars-input,.lcars-select{background-color:#000;border:2px solid var(--lcars-dark-blue);color:#fff;padding:10px;font-size:14px;border-radius:4px;font-family:inherit;letter-spacing:1px;text-transform:uppercase;width:100%;box-sizing:border-box}
        .lcars-input:focus,.lcars-select:focus{outline:none;border-color:var(--lcars-blue)}
        .lcars-select{appearance:none;background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://w3.org' width='100' height='50'><polygon points='0,0 100,0 50,50' style='fill:%2333ccff;'/></svg>");background-repeat:no-repeat;background-size:12px 6px;background-position:right 15px center;padding-right:30px}
        .form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px}
        .form-actions{display:flex;gap:15px;margin-top:10px}
        .form-actions .lcars-btn{border-radius:4px;text-align:center;min-width:120px}
        .field-tip{font-size:11px;color:#aaa;text-transform:none;margin-top:5px}
    </style>
</head>
<body>
    <!-- Top LCARS Header Bar -->
    <header class="lcars-header">
        <div class="lcars-bar-top"></div>
        <h2 class="lcars-title">ASSET CONTROLLER MATRIX</h2>
    </header>
    
    <div class="lcars-container">
        <!-- Left Side Control Bracket Shape -->
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <div class="lcars-menu">
                <a href="welcome.php" class="lcars-btn">MAIN TERM</a>
                <a href="web_course.php" class="lcars-btn btn-blue">CURRICULUM</a>
            </div>
        </nav>
        
        <!-- Main Interface Display Area -->
        <main class="lcars-main-panel">
            <!-- Neon Blue User Status Banner Line -->
            <div class="lcars-user-banner">
                <h1>SECURE VESSEL INTERFACE MATRIX</h1>
                <div class="system-status <?php echo ($message_type === 'success') ? 'success-alert' : (($message_type === 'error') ? 'error-alert' : ''); ?>">
                    <?php if (!empty($message)): ?>
                        <?php echo htmlspecialchars($message); ?>
                    <?php else: ?>
                        SYSTEM ONLINE // CLEARANCE LEVEL: ADMIN COMPLIANT
                    <?php endif; ?>
                </div>
            </div>

            <!-- Purple Sidebar Container Box for Admin Controls -->
            <div class="lcars-admin-section">
                <h3>REGISTER OR REMAP NAVAL CONFIGURATIONS</h3>
                
                <form method="POST" action="ship_mgt.php">
                    <input type="hidden" name="action" value="register_vessel">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <!-- Grid Layout splits fields automatically into columns -->
                    <div class="form-grid">
                        <div class="form-row">
                            <label>VESSEL CLASS DESIGNATION NAME</label>
                            <input type="text" class="lcars-input" name="ship_name" placeholder="E.G. USS DEFIANT" required autocomplete="off">
                            <div class="field-tip">Enter official Starfleet registry name assignment.</div>
                        </div>

                        <div class="form-row">
                            <label>NCC REGISTRY SERIAL NUMBER (UNIQUE ID)</label>
                            <input type="text" class="lcars-input" name="ncc_number" placeholder="E.G. NCC-74205" required autocomplete="off">
                            <div class="field-tip">Duplicate registries trigger system data updates.</div>
                        </div>

                        <div class="form-row">
                            <label>ASSIGNED COMMANDING OFFICER</label>
                            <input type="text" class="lcars-input" name="captain_name" placeholder="E.G. CAPTAIN BENJAMIN SISKO" autocomplete="off">
                            <div class="field-tip">Leave blank to log vessel as unassigned.</div>
                        </div>

                        <div class="form-row">
                            <label>OPERATIONAL MISSION STATUS</label>
                            <select class="lcars-select" name="status">
                                <option value="Active Duty">ACTIVE DUTY</option>
                                <option value="Drydock Refit">DRYDOCK REFIT</option>
                                <option value="Damaged // Inoperable">SYSTEM FAULT // INOPERABLE</option>
                                <option value="Missing In Action">MIA // UNKNOWN COORDINATES</option>
                            </select>
                            <div class="field-tip">Select active hull deployment mode.</div>
                        </div>

                        <div class="form-row">
                            <label>CURRENT SYSTEM ASSIGNMENT QUADRANT</label>
                            <select class="lcars-select" name="quadrant">
                                <option value="Alpha">ALPHA QUADRANT</option>
                                <option value="Beta">BETA QUADRANT</option>
                                <option value="Gamma">GAMMA QUADRANT</option>
                                <option value="Delta">DELTA QUADRANT</option>
                            </select>
                            <div class="field-tip">Designate localized space quadrant sector.</div>
                        </div>
                    </div>

                    <!-- Custom rounded button actions row -->
                    <div class="form-actions">
                        <button type="submit" class="lcars-btn btn-pink">ENGAGE TRANS</button>
                        <button type="reset" class="lcars-btn btn-blue">RESET CORE</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
