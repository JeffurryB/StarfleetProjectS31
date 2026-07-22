<?php
header('Content-type: text/html; charset=utf-8');
include("session.php"); 
include("config.php");
include("functions.php");
mysqli_set_charset($db, "utf8");

// 1. ADMIN AUTHORIZATION CHECK (dh = 1 required)
$auth_username = mysqli_real_escape_string($db, $login_session);
$sql_auth = "SELECT dh FROM accounts WHERE username = '$auth_username' LIMIT 1";
$res_auth = mysqli_query($db, $sql_auth);

if ($res_auth && mysqli_num_rows($res_auth) == 1) {
    $auth_data = mysqli_fetch_assoc($res_auth);
    if ((int)$auth_data['dh'] !== 1) {
        header("Location: notauthorized.php?error=clearance_insufficient");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

// 2. FETCH ASSET TYPES FROM DATABASE FOR SCREEN SELECTOR DROPDOWN
$sql_types = "SELECT atid, type FROM asset_types ORDER BY type ASC";
$res_types = mysqli_query($db, $sql_types);
$asset_types = [];
if ($res_types && mysqli_num_rows($res_types) > 0) {
    while ($row_type = mysqli_fetch_assoc($res_types)) {
        $asset_types[] = $row_type;
    }
}

// 3. TELEMETRY STATUS MESSAGE STORAGE
$status_message = "";
$status_type = "";

// 4. LOGIC ENGINE PROCESSOR ON FORM POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $raw_uuid = trim($_POST['uuid']);
    $raw_name = trim($_POST['name']);
    $raw_type = trim($_POST['type']); 

    if (empty($raw_uuid) || empty($raw_name) || empty($raw_type)) {
        $status_message = "ERROR: CRITICAL REGISTRY FIELD NULL EXCEPTION DETECTED.";
        $status_type = "failure";
    } else {
        // Secure Parameterized Query Structure
        $AssetUpload = "INSERT INTO `assets` (`uuid`, `type`, `name`) VALUES (?, ?, ?)";
        $stmt_upload = $db->prepare($AssetUpload);
        
        // Bind types: String (uuid), Integer (atid mapping parameter key), String (name)
        $stmt_upload->bind_param("sis", $raw_uuid, $raw_type, $raw_name);

        if ($stmt_upload->execute()) {
            $status_message = "OK | ASSET DATA CHRONOMETER CORE ARRAY INSERTED.";
            $status_type = "success";
        } else {
            $status_message = "ERROR | ASSET TELEMETRY WRITE EXCEPTION: " . htmlspecialchars($db->error);
            $status_type = "failure";
        }
        $stmt_upload->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>RP GROUP - Asset Registry Console</title>
    <style>
        :root { --lcars-purple: #9966cc; --lcars-orange: #ff9900; --lcars-pink: #cc6699; --lcars-blue: #33ccff; --lcars-dark-blue: #5588ff; --lcars-bg: #000000; --lcars-green: #33cc66; }
        body { background-color: var(--lcars-bg); color: #ffffff; font-family: "Arial Custom", "Helvetica Neue", Arial, sans-serif; margin: 0; padding: 15px; text-transform: uppercase; letter-spacing: 1px; overflow-x: hidden; }
        .lcars-header { display: flex; align-items: flex-end; margin-bottom: 15px; }
        .lcars-bar-top { background-color: var(--lcars-purple); height: 40px; flex-grow: 1; border-bottom-left-radius: 20px; margin-right: 15px; position: relative; }
        .lcars-bar-top::before { content: "ASSET-LOG-882"; position: absolute; left: 25px; bottom: 3px; color: #000000; font-weight: bold; font-size: 14px; }
        .lcars-title { color: var(--lcars-orange); font-size: 28px; font-weight: 300; margin: 0; line-height: 1; white-space: nowrap; }
        .lcars-container { display: flex; min-height: calc(100vh - 120px); }
        .lcars-left-bracket { width: 150px; display: flex; flex-direction: column; margin-right: 20px; }
        .lcars-elbow { background-color: var(--lcars-purple); height: 60px; border-top-left-radius: 20px; border-bottom-left-radius: 20px; margin-bottom: 15px; position: relative; }
        .lcars-elbow::after { content: ""; position: absolute; background-color: var(--lcars-bg); width: 110px; height: 35px; bottom: 0; right: 0; border-top-left-radius: 15px; }
        .lcars-menu { display: flex; flex-direction: column; gap: 8px; }
        .lcars-btn { background-color: var(--lcars-orange); color: #000000; padding: 10px 15px; text-decoration: none; font-weight: bold; font-size: 13px; text-align: right; border-radius: 5px 0 0 5px; transition: background 0.2s; border: none; cursor: pointer; font-family: inherit; letter-spacing: inherit; text-transform: uppercase; }
        .lcars-btn:hover { background-color: #ffcc00; }
        .btn-blue { background-color: var(--lcars-blue); }
        .btn-blue:hover { background-color: #88e2ff; }
        .btn-pink { background-color: var(--lcars-pink); }
        .btn-pink:hover { background-color: #ff99cc; }
        .btn-green { background-color: var(--lcars-green); }
        .btn-green:hover { background-color: #66ff99; }
        .btn-logout { background-color: #cc3333; color: #ffffff; margin-top: 20px;}
        .btn-logout:hover { background-color: #ff5555; }
        .lcars-main-panel { flex-grow: 1; display: flex; flex-direction: column; }
        .lcars-user-banner { border-bottom: 4px solid var(--lcars-blue); padding-bottom: 10px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .lcars-user-banner h1 { margin: 0; font-size: 22px; color: var(--lcars-blue); font-weight: normal; }
        .system-status { font-size: 12px; color: var(--lcars-dark-blue); }
        .lcars-admin-section { background-color: #111116; border-left: 6px solid var(--lcars-orange); padding: 25px; border-radius: 0 8px 8px 0; margin-bottom: 25px; }
        .lcars-admin-section h3 { margin: 0 0 20px 0; color: var(--lcars-orange); font-size: 18px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .form-row { display: flex; flex-direction: column; }
        .form-row label { color: var(--lcars-blue); font-size: 12px; margin-bottom: 6px; font-weight: bold; }
        .lcars-input, .lcars-select { background-color: #000000; border: 2px solid var(--lcars-dark-blue); color: #ffffff; padding: 12px; font-size: 14px; border-radius: 4px; font-family: inherit; letter-spacing: 1px; text-transform: uppercase; width: 100%; box-sizing: border-box; }
        .lcars-input:focus, .lcars-select:focus { outline: none; border-color: var(--lcars-blue); }
        .lcars-select { appearance: none; background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://w3.org' width='100' height='50'><polygon points='0,0 100,0 50,50' style='fill:%2333ccff;'/></svg>"); background-repeat: no-repeat; background-size: 12px 6px; background-position: right 15px center; padding-right: 30px; }
        .form-actions { display: flex; gap: 15px; border-top: 2px solid #222230; padding-top: 20px; }
        .telemetry-banner { padding: 15px; font-weight: bold; font-size: 13px; margin-bottom: 20px; border-radius: 4px; border-left: 6px solid; text-transform: none; }
        .telemetry-success { background-color: #112211; color: #55ff55; border-left-color: var(--lcars-green); }
        .telemetry-failure { background-color: #221111; color: #ff5555; border-left-color: #cc3333; }
    </style>
</head>
<body>

    <header class="lcars-header">
        <div class="lcars-bar-top"></div>
        <h2 class="lcars-title">STARFLEET COMPUTER TERMINAL</h2>
    </header>

    <div class="lcars-container">
        
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <div class="lcars-menu">
                <a href="welcome.php" class="lcars-btn">MAIN TERM</a>
                <a href="dhsystem.php" class="lcars-btn btn-blue">MEMBER MGMT</a>
                <a href="asset.php" class="lcars-btn btn-pink">ASSET REPOST</a>
                <a href="logout.php" class="lcars-btn btn-logout">DISENGAGE</a>
            </div>
        </nav>

        <main class="lcars-main-panel">
            <div class="lcars-user-banner">
                <h1>ASSET ALLOCATION CONFIGURATION INTERFACE</h1>
                <div class="system-status">SYS STATUS: ACTIVE // AUTH_LEVEL: ADMIN_01</div>
            </div>

            <?php if (!empty($status_message)): ?>
                <div class="telemetry-banner <?php echo ($status_type === 'success') ? 'telemetry-success' : 'telemetry-failure'; ?>">
                    DIAGNOSTIC STATUS LOG: <?php echo $status_message; ?>
                </div>
            <?php endif; ?>

            <section class="lcars-admin-section">
                <h3>REGISTER NEW ASSET SPECIFICATIONS</h3>
                
                <form method="POST" action="asset.php">
                    <div class="form-grid">
                        
                        <div class="form-row">
                            <label for="asset_uuid">ASSET UUID MATRIX IDENTIFIER</label>
                            <input type="text" id="asset_uuid" name="uuid" class="lcars-input" required placeholder="ENTER SERIAL EX-####-####">
                        </div>

                        <div class="form-row">
                            <label for="asset_type">ASSET TYPE FUNCTIONAL STREAM</label>
                            <select id="asset_type" name="type" class="lcars-select" required>
                                <option value="">-- CHOOSE SYSTEM SPECIFICATION --</option>
                                <?php foreach ($asset_types as $type_row): ?>
                                    <option value="<?php echo $type_row['atid']; ?>">
                                        <?php echo htmlspecialchars($type_row['type']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-row">
                            <label for="asset_name">ASSET TITLE / DESIGNATION NAME</label>
                            <input type="text" id="asset_name" name="name" class="lcars-input" required placeholder="E.G. MAIN PHASER BANK ALPHA">
                        </div>

                    </div>

                    <div class="form-actions">
                        <button type="submit" class="lcars-btn btn-green">ENGAGE DATA ENTRY</button>
                        <a href="asset.php" class="lcars-btn btn-pink" style="text-decoration:none; text-align:center;">CLEAR SYSTEM INPUTS</a>
                    </div>
                </form>
            </section>
            
        </main>
    </div>

</body>
</html>
<?php @mysqli_close($db); ?>

