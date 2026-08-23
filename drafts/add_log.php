<?php
// 1. INCLUDE YOUR CENTRAL AUTHENTICATION ENGINE
// This automatically starts the session, connects to the DB, and validates cookies
include 'session.php'; 

// Safety check: Make sure your session file successfully shared the database link
if (!isset($db) && isset($conn)) { $db = $conn; }
if (!isset($db)) { die('ERROR: NO DB CONNECTION'); }

// Your session.php sets the character name variable as $login_session on Line 72
$session_username = $login_session; 

$msg = '';
$msg_color = 'var(--blu)';

// Handle Form Submission Protocol
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ENHANCEMENT: Enforce the session variable on the backend to prevent input spoofing
    $user  = $session_username; 
    $title = trim($_POST['log_name']);
    $entry = trim($_POST['log_entry']);

    if (empty($title) || empty($entry)) {
        $msg = 'SUBMISSION ERROR: ALL FIELDS REQUIRED';
        $msg_color = 'var(--red)';
    } else {
        // Secure strings against SQL injections using native mysqli handler
        $user_esc  = mysqli_real_escape_string($db, $user);
        $title_esc = mysqli_real_escape_string($db, $title);
        $entry_esc = mysqli_real_escape_string($db, $entry);

        $sql = "INSERT INTO Plogs (username, log_name, log_entry) VALUES ('$user_esc', '$title_esc', '$entry_esc')";
        
        if (mysqli_query($db, $sql)) {
            $msg = 'TRANSMISSION COMPLETE: LOG SUBMITTED';
            $msg_color = 'var(--l-org)';
        } else {
            $msg = 'SYSTEM CRITICAL: FILE TRANSMISSION FAILURE';
            $msg_color = 'var(--red)';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS - ADD PERSONAL LOG</title>
    <link href="https://googleapis.com" rel="stylesheet">
    <style>
        :root { --org: #f90; --l-org: #fc0; --blu: #9cf; --d-blu: #69c; --pur: #c9c; --red: #c33; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #000; color: #fff; font-family: 'Antonio', 'Oswald', sans-serif; text-transform: uppercase; padding: 20px; }
        .lcars-h { display: flex; align-items: flex-end; margin-bottom: 5px; }
        .lcars-h .b-l { width: 150px; height: 45px; background: var(--pur); border-top-left-radius: 20px; margin-right: 5px; }
        .lcars-h .b-m { flex-grow: 1; height: 15px; background: var(--pur); margin-right: 5px; }
        .lcars-h .txt { color: var(--org); font-size: 2.2rem; font-weight: bold; padding-right: 15px; }
        .lcars-m { display: flex; }
        .lcars-s { width: 150px; display: flex; flex-direction: column; gap: 5px; margin-right: 15px; }
        .s-el { height: 40px; background: var(--blu); text-align: right; padding-right: 10px; line-height: 40px; color: #000; font-weight: bold; }
        .s-el.pur { background: var(--pur); } .s-el.org { background: var(--org); }
        .s-sp { flex-grow: 1; background: var(--d-blu); border-bottom-left-radius: 20px; min-height: 100px; }
        .lcars-c { flex-grow: 1; padding: 20px 10px; }
        .title { color: var(--l-org); font-size: 1.8rem; margin-bottom: 25px; border-bottom: 2px solid var(--blu); padding-bottom: 5px; }
        .f-grp { margin-bottom: 20px; }
        .f-grp label { display: block; color: var(--blu); font-size: 1.2rem; margin-bottom: 8px; }
        .inp, .txt-ara { width: 100%; background: #111; color: var(--org); border: 2px solid var(--org); padding: 12px; font-family: 'Antonio'; font-size: 1.2rem; text-transform: uppercase; outline: none; }
        .inp.locked { background: #080811; color: var(--pur); border-color: var(--pur); cursor: not-allowed; }
        .txt-ara { font-family: monospace; text-transform: none; height: 200px; resize: vertical; color: #cdf; }
        .inp:focus, .txt-ara:focus { border-color: var(--l-org); }
        .status-msg { padding: 12px; border-left: 5px solid; font-size: 1.2rem; margin-bottom: 20px; background: #112; }
        .btn { font-family: 'Antonio'; padding: 10px 25px; font-size: 1.2rem; border: none; cursor: pointer; font-weight: bold; text-transform: uppercase; background: var(--org); color: #000; border-radius: 15px; text-decoration: none; display: inline-block; }
        .btn.back { background: var(--d-blu); }
        .btn:hover { filter: brightness(1.2); }
    </style>
</head>
<body>
    <div class="lcars-h"><div class="b-l"></div><div class="b-m"></div><div class="txt">LCARS INPUT TERMINAL</div></div>
    <div class="lcars-m">
        <nav class="lcars-s"><div class="s-el">SYS 02</div><div class="s-el pur">COMPOSE</div><div class="s-el org">DATA</div><div class="s-sp"></div></nav>
        <main class="lcars-c">
            <h2 class="title">File New Personal Log</h2>
            
            <?php if (!empty($msg)): ?>
                <div class="status-msg" style="border-color: <?= $msg_color ?>; color: <?= $msg_color ?>;">
                    <?= $msg ?>
                </div>
            <?php endif; ?>

            <form action="add_log.php" method="POST">
                <!-- Connects directly to your site-wide token setup from session.php line 86 -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

                <div class="f-grp">
                    <label>Personnel Source Identity (Locked to Secure Session Profile)</label>
                    <!-- FIXED: Aligns perfectly with your session.php variable scope output -->
                    <input type="text" class="inp locked" value="<?php echo htmlspecialchars($session_username, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                </div>
                <div class="f-grp">
                    <label>Log Classification Name / Stardate Designation</label>
                    <input type="text" name="log_name" class="inp" autocomplete="off" placeholder="E.G. STARDATE 41153.7 - ENCOUNTER" required>
                </div>
                <div class="f-grp">
                    <label>Log Narrative Entry (Plaintext Console Format)</label>
                    <textarea name="log_entry" class="txt-ara" required></textarea>
                </div>
                <div style="display: flex; gap: 15px;">
                    <button type="submit" class="btn">Transmit Entry</button>
                    <a href="log.php" class="btn back">&lt; View Index Terminal</a>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
