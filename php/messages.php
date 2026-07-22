<?php
// 1. INCLUDE EXISTING SESSION AND CONFIGURATION MODULES FIRST
include("config.php");   // Loads the active $db connection resource
include("session.php");  // Loads login tracking context and populates $login_session
include("functions.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

mysqli_set_charset($db, "utf8");

$current_user = isset($login_session) ? trim($login_session) : '';
if (empty($current_user)) {
    header("Location: login.php");
    exit();
}

$status_msg = "";
$status_type = "";

// 2. PRIORITIZED SUBMISSION ROUTE: PRIVATE MESSAGE DATA PURGE PROTOCOL
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'delete_msg') {
    $msg_id = intval($_POST['msg_id']);
    $clean_current = mysqli_real_escape_string($db, $current_user);
    
    // Step A: Flag the message as hidden for the user executing the command
    $update_sender_sql   = "UPDATE `messages` SET `deleted_by_sender` = 1 WHERE `id` = ? AND `from_username` = ?";
    $update_receiver_sql = "UPDATE `messages` SET `deleted_by_receiver` = 1 WHERE `id` = ? AND `to_username` = ?";
    
    $stmt_s = $db->prepare($update_sender_sql);
    $stmt_s->bind_param("is", $msg_id, $clean_current);
    $stmt_s->execute();
    $stmt_s->close();
    
    $stmt_r = $db->prepare($update_receiver_sql);
    $stmt_r->bind_param("is", $msg_id, $clean_current);
    $stmt_r->execute();
    $stmt_r->close();
    
    // Step B: Housekeeping - If BOTH sides dropped it, wipe the row completely from disk
    $cleanup_sql = "DELETE FROM `messages` WHERE `id` = ? AND `deleted_by_sender` = 1 AND `deleted_by_receiver` = 1";
    $stmt_c = $db->prepare($cleanup_sql);
    $stmt_c->bind_param("i", $msg_id);
    $stmt_c->execute();
    $stmt_c->close();
    
    header("Location: messages.php?status=purged");
    exit();
}
// 3. SUBMISSION ROUTE: CONSTRUCT AND TRANSMIT OUTGOING MESSAGE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'send_msg') {
    $to_user = trim($_POST['to_username']);
    $msg_body = trim($_POST['message_text']);

    $clean_to = mysqli_real_escape_string($db, $to_user);
    $check_sql = "SELECT username FROM accounts WHERE username = '$clean_to' LIMIT 1";
    $check_res = mysqli_query($db, $check_sql);

    if (empty($to_user) || empty($msg_body)) {
        $status_msg = "ERROR: SUB-ROUTINE CRITICAL FAILURE // EMPTY FIELD ARGUMENTS INTRODUCED.";
        $status_type = "failure";
    } elseif (mysqli_num_rows($check_res) == 0) {
        $status_msg = "TRANSMISSION REJECTED: COURIER NODE ADDRESS '" . htmlspecialchars($to_user) . "' NOT FOUND IN MASTER REPOSITORY.";
        $status_type = "failure";
    } else {
        $insert_sql = "INSERT INTO `messages` (`from_username`, `to_username`, `message`) VALUES (?, ?, ?)";
        $stmt_send = $db->prepare($insert_sql);
        $stmt_send->bind_param("sss", $current_user, $to_user, $msg_body);

        if ($stmt_send->execute()) {
            $status_msg = "TRANSMISSION SUCCESSFUL: SUBNET BURST SENT TO [" . htmlspecialchars($to_user) . "].";
            $status_type = "success";
        } else {
            $status_msg = "ERROR: SUBSYSTEM TIMEOUT // SUB-STREAM ENCOUNTERED MEMORY WRITING FAULT.";
            $status_type = "failure";
        }
        $stmt_send->close();
    }
}

// Catch URL clean purge notice telemetry
if (isset($_GET['status']) && $_GET['status'] === 'purged') {
    $status_msg = "PURGE COMPLETE: TRANSMISSION COMPARTMENT WIPE SUCCESSFUL.";
    $status_type = "success";
}

// 4. TELEMETRY FETCH: COLLECT BOTH INBOX AND OUTBOX STREAM DATA
$clean_current = mysqli_real_escape_string($db, $current_user);

// Automatically mark all incoming non-deleted messages for this user as read upon loading
mysqli_query($db, "UPDATE `messages` SET `is_read` = 1 WHERE `to_username` = '$clean_current' AND `deleted_by_receiver` = 0");

// Pull Inbox Logs (Only if receiver hasn't hidden it)
$inbox_sql = "SELECT * FROM `messages` WHERE `to_username` = '$clean_current' AND `deleted_by_receiver` = 0 ORDER BY `date_received` DESC, `time_received` DESC";
$inbox_res = mysqli_query($db, $inbox_sql);

// Pull Outbox Logs (Only if sender hasn't hidden it)
$outbox_sql = "SELECT * FROM `messages` WHERE `from_username` = '$clean_current' AND `deleted_by_sender` = 0 ORDER BY `date_received` DESC, `time_received` DESC";
$outbox_res = mysqli_query($db, $outbox_sql);

// Pull List of Active User Accounts for Quick-Select Form Dropdown menus
$users_sql = "SELECT username, IFNULL(DisplayName, username) as dname FROM accounts WHERE username != '$clean_current' AND active = '1' ORDER BY username ASC";
$users_res = mysqli_query($db, $users_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>RP GROUP - Subspace Communications Terminal</title>
    <style>
        :root { --lcars-purple: #9966cc; --lcars-orange: #ff9900; --lcars-pink: #cc6699; --lcars-blue: #33ccff; --lcars-dark-blue: #5588ff; --lcars-bg: #000000; --lcars-green: #33cc66; }
        body { background-color: var(--lcars-bg); color: #ffffff; font-family: "Arial Custom", "Helvetica Neue", Arial, sans-serif; margin: 0; padding: 15px; text-transform: uppercase; letter-spacing: 1px; overflow-x: hidden; }
        .lcars-header { display: flex; align-items: flex-end; margin-bottom: 15px; }
        .lcars-bar-top { background-color: var(--lcars-purple); height: 40px; flex-grow: 1; border-bottom-left-radius: 20px; margin-right: 15px; position: relative; }
        .lcars-bar-top::before { content: "COM-SUB-NET-09"; position: absolute; left: 25px; bottom: 3px; color: #000000; font-weight: bold; font-size: 14px; }
        .lcars-title { color: var(--lcars-orange); font-size: 28px; font-weight: 300; margin: 0; line-height: 1; white-space: nowrap; }
        .lcars-container { display: flex; min-height: calc(100vh - 120px); }
        .lcars-left-bracket { width: 150px; display: flex; flex-direction: column; margin-right: 20px; }
        .lcars-elbow { background-color: var(--lcars-purple); height: 60px; border-top-left-radius: 20px; border-bottom-left-radius: 20px; margin-bottom: 15px; position: relative; }
        .lcars-elbow::after { content: ""; position: absolute; background-color: var(--lcars-bg); width: 110px; height: 35px; bottom: 0; right: 0; border-top-left-radius: 15px; }
        .lcars-menu { display: flex; flex-direction: column; gap: 8px; }
        .lcars-btn { background-color: var(--lcars-orange); color: #000000; padding: 10px 15px; text-decoration: none; font-weight: bold; font-size: 13px; text-align: right; border-radius: 5px 0 0 5px; border: none; cursor: pointer; text-transform: uppercase; }
        .lcars-btn:hover { background-color: #ffcc00; }
        .btn-blue { background-color: var(--lcars-blue); } .btn-blue:hover { background-color: #88e2ff; }
        .btn-pink { background-color: var(--lcars-pink); } .btn-pink:hover { background-color: #ff99cc; }
        .btn-green { background-color: var(--lcars-green); } .btn-green:hover { background-color: #66ff99; }
        .lcars-main-panel { flex-grow: 1; display: flex; flex-direction: column; }
        .lcars-user-banner { border-bottom: 4px solid var(--lcars-blue); padding-bottom: 10px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .lcars-user-banner h1 { margin: 0; font-size: 22px; color: var(--lcars-blue); font-weight: normal; }
        .system-status { font-size: 12px; color: var(--lcars-dark-blue); }
        .grid-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media(max-width: 900px){ .grid-layout { grid-template-columns: 1fr; } }
        .lcars-panel { background-color: #111116; border-left: 6px solid var(--lcars-orange); padding: 20px; border-radius: 0 8px 8px 0; margin-bottom: 20px; }
        .panel-blue { border-left-color: var(--lcars-blue); } .panel-pink { border-left-color: var(--lcars-pink); }
        .lcars-panel h3 { margin: 0 0 15px 0; font-size: 16px; color: inherit; letter-spacing: 1px; }
        .lcars-input, .lcars-select, .lcars-textarea { background-color: #000000; border: 2px solid var(--lcars-dark-blue); color: #ffffff; padding: 12px; font-size: 14px; border-radius: 4px; font-family: inherit; width: 100%; box-sizing: border-box; text-transform: uppercase; }
        .lcars-textarea { height: 100px; resize: none; text-transform: none; }
        .lcars-input:focus, .lcars-select:focus, .lcars-textarea:focus { outline: none; border-color: var(--lcars-blue); }
        .msg-feed { display: flex; flex-direction: column; gap: 12px; max-height: 450px; overflow-y: auto; padding-right: 5px; }
        .msg-node { background: #000000; border: 1px solid #222230; border-left: 4px solid var(--lcars-blue); padding: 12px; border-radius: 4px; }
        .out-node { border-left-color: var(--lcars-pink); }
        .msg-meta { display: flex; justify-content: space-between; font-size: 11px; color: var(--lcars-blue); margin-bottom: 6px; font-weight: bold; }
        .out-node .msg-meta { color: var(--lcars-pink); }
        .msg-body { font-size: 13px; line-height: 1.4; color: #dddddd; text-transform: none; word-break: break-word; }
        .telemetry-banner { padding: 12px; font-weight: bold; font-size: 12px; margin-bottom: 20px; border-radius: 4px; border-left: 6px solid; text-transform: none; }
        .telemetry-success { background-color: #112211; color: #55ff55; border-left-color: var(--lcars-green); }
        .telemetry-failure { background-color: #221111; color: #ff5555; border-left-color: #cc3333; }
        .tab-btn-row { display: flex; gap: 10px; margin-bottom: 15px; }
        .tab-btn { background: #1c1c24; color: #888; border: none; padding: 8px 16px; font-family: inherit; font-size: 12px; font-weight: bold; cursor: pointer; border-radius: 4px; text-transform: uppercase; }
        .tab-active { background: var(--lcars-blue); color: #000; }
        .tab-active.out-active { background: var(--lcars-pink); }
    </style>
</head>
<body>

    <header class="lcars-header">
        <div class="lcars-bar-top"></div>
        <h2 class="lcars-title">STARFLEET INTERFACE ARCHIVE</h2>
    </header>

    <div class="lcars-container">
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <div class="lcars-menu">
                <a href="welcome.php" class="lcars-btn">MAIN TERM</a>
                <a href="service_jacket.php" class="lcars-btn btn-blue">MY JACKET</a>
                <a href="messages.php" class="lcars-btn btn-pink">MESSAGES</a>
            </div>
        </nav>

        <main class="lcars-main-panel">
            <div class="lcars-user-banner">
                <h1>SUBSPACE COMMUNICATION MATRIX TERMINAL</h1>
                <div class="system-status">SYS USER: <?php echo htmlspecialchars($current_user); ?> // STREAMS: ENCRYPTED</div>
            </div>

            <?php if (!empty($status_msg)): ?>
                <div class="telemetry-banner <?php echo ($status_type === 'success') ? 'telemetry-success' : 'telemetry-failure'; ?>">
                    COM-LINK STATUS REPORT: <?php echo $status_msg; ?>
                </div>
            <?php endif; ?>

            <div class="grid-layout">
                <section class="lcars-panel panel-blue" style="color: var(--lcars-blue);">
                    <h3>TRANSMIT NEW BURST SIGNAL</h3>
                    <form method="POST" action="messages.php">
                        <input type="hidden" name="action" value="send_msg">
                        <div style="margin-bottom: 15px;">
                            <label style="display:block; font-size:11px; margin-bottom:6px; font-weight:bold;">DESTINATION HANDLER ADDRESS:</label>
                            <select name="to_username" class="lcars-select" required>
                                <option value="">-- CHOOSE RECIPIENT NODE --</option>
                                <?php while ($u_row = mysqli_fetch_assoc($users_res)): ?>
                                    <option value="<?php echo htmlspecialchars($u_row['username']); ?>"><?php echo htmlspecialchars($u_row['username'] . " [" . $u_row['dname'] . "]"); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="display:block; font-size:11px; margin-bottom:6px; font-weight:bold;">ENCRYPTED MESSAGE STREAM BLOCKS:</label>
                            <textarea name="message_text" class="lcars-textarea" placeholder="Input transmission log telemetry..." required></textarea>
                        </div>
                        <button type="submit" class="lcars-btn btn-green" style="width:100%; border-radius:4px; text-align:center;">ENGAGE SIGNAL REPEATER</button>
                    </form>
                </section>

                <section class="lcars-panel panel-pink" style="color: var(--lcars-pink);" id="stream-container">
                    <div class="tab-btn-row">
                        <button class="tab-btn tab-active" id="btn-inbox" onclick="switchStream('inbox')">INBOX DIRECTS</button>
                        <button class="tab-btn" id="btn-outbox" onclick="switchStream('outbox')">OUTBOX LOGS</button>
                    </div>

                    <div id="stream-inbox" class="msg-feed">
                        <?php if (mysqli_num_rows($inbox_res) == 0): ?>
                            <div class="msg-body" style="font-style: italic; color:#777;">NO INCOMING SIGNALS DETECTED WITHIN SECTOR LOGS.</div>
                        <?php else: ?>
                            <?php while ($msg = mysqli_fetch_assoc($inbox_res)): ?>
    <div class="msg-node" style="display: flex; flex-direction: column; justify-content: space-between; position: relative;">
        <div>
            <div class="msg-meta">
                <span>FROM: <?php echo htmlspecialchars($msg['from_username']); ?></span>
                <span><?php echo htmlspecialchars($msg['date_received'] . " // " . $msg['time_received']); ?></span>
            </div>
            <div class="msg-body" style="padding-right: 40px;"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
        </div>
        <form method="POST" action="messages.php" style="position: absolute; right: 10px; bottom: 8px; margin: 0;" onsubmit="return confirm('PURGE LOG DATA: CONFIRM OVERRIDE SEQUENCE?');">
            <input type="hidden" name="action" value="delete_msg">
            <input type="hidden" name="msg_id" value="<?php echo $msg['id']; ?>">
            <button type="submit" style="background: none; border: none; color: #cc6699; font-family: inherit; font-size: 11px; font-weight: bold; cursor: pointer; text-transform: uppercase; padding: 2px 5px;">[WIPE]</button>
        </form>
    </div>
<?php endwhile; ?>
                        <?php endif; ?>
                    </div>

                    <div id="stream-outbox" class="msg-feed" style="display: none;">
                        <?php if (mysqli_num_rows($outbox_res) == 0): ?>
                            <div class="msg-body" style="font-style: italic; color:#777;">NO RECENT SIGNAL TRANSMISSIONS ORIGINATED FROM THIS NODE.</div>
                        <?php else: ?>
                            <?php while ($msg = mysqli_fetch_assoc($outbox_res)): ?>
    <div class="msg-node out-node" style="display: flex; flex-direction: column; justify-content: space-between; position: relative;">
        <div>
            <div class="msg-meta">
                <span>TO: <?php echo htmlspecialchars($msg['to_username']); ?></span>
                <span><?php echo htmlspecialchars($msg['date_received'] . " // " . $msg['time_received']); ?></span>
            </div>
            <div class="msg-body" style="padding-right: 40px;"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
        </div>
        <form method="POST" action="messages.php" style="position: absolute; right: 10px; bottom: 8px; margin: 0;" onsubmit="return confirm('PURGE LOG DATA: CONFIRM OVERRIDE SEQUENCE?');">
            <input type="hidden" name="action" value="delete_msg">
            <input type="hidden" name="msg_id" value="<?php echo $msg['id']; ?>">
            <button type="submit" style="background: none; border: none; color: var(--lcars-blue, #33ccff); font-family: inherit; font-size: 11px; font-weight: bold; cursor: pointer; text-transform: uppercase; padding: 2px 5px;">[WIPE]</button>
        </form>
    </div>
<?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script>
        function switchStream(t){
            const i=document.getElementById('stream-inbox'), o=document.getElementById('stream-outbox'), bi=document.getElementById('btn-inbox'), bo=document.getElementById('btn-outbox');
            if(t==='inbox'){ i.style.display='flex'; o.style.display='none'; bi.classList.add('tab-active'); bi.classList.remove('out-active'); bo.classList.remove('tab-active','out-active'); }
            else{ i.style.display='none'; o.style.display='flex'; bo.classList.add('tab-active','out-active'); bi.classList.remove('tab-active','out-active'); }
        }
    </script>
</body>
</html>
<?php @mysqli_close($db); ?>
