<?php
include( 'session.php' );

if (!isset($db)) {
    include("config.php"); 
}

// Clean and cross-check active session tracking identifier
$auth_username = mysqli_real_escape_string($db, $login_session);

// Query database cluster node for administrative privileges
$sql_check = "SELECT dh FROM accounts WHERE username = '$auth_username' LIMIT 1";
$res_check = mysqli_query($db, $sql_check);

if ($res_check && mysqli_num_rows($res_check) == 1) {
    $auth_data = mysqli_fetch_assoc($res_check);
    
    // Strict integer evaluation: if dh is 0, reject entry immediately
    if ((int)$auth_data['dh'] !== 1) {
        header("Location: notauthorized.php?error=clearance_insufficient");
        exit();
    }
} else {
    // If user mapping record disappears from system array entirely
    header("Location: index.php");
    exit();
}
// Check for unread message telemetry inside the message cluster matrix
$check_user = mysqli_real_escape_string($db, $login_session);
$sql_mail = "SELECT COUNT(*) as total_msg FROM `messages` WHERE `to_username` = '$check_user' AND `is_read` = 0";
$res_mail = mysqli_query($db, $sql_mail);
$unread_count = 0;
$has_mail = false;

if ($res_mail) {
    $mail_row = mysqli_fetch_assoc($res_mail);
    $unread_count = (int)$mail_row['total_msg'];
    if ($unread_count > 0) {
        $has_mail = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UFPGC - DH Main Terminal</title>
    <style>
        /* LCARS Color Palette */
        :root {
            --lcars-purple: #9966cc;
            --lcars-orange: #ff9900;
            --lcars-pink: #cc6699;
            --lcars-blue: #33ccff;
            --lcars-dark-blue: #5588ff;
            --lcars-bg: #000000;
        }

        body {
            background-color: var(--lcars-bg);
            color: #ffffff;
            font-family: "Arial Custom", "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 15px;
            text-transform: uppercase; /* LCARS style standard uppercase */
            letter-spacing: 1px;
            overflow-x: hidden;
        }

        /* Top Banner Layout */
        .lcars-header {
            display: flex;
            align-items: flex-end;
            margin-bottom: 15px;
        }

        .lcars-bar-top {
            background-color: var(--lcars-purple);
            height: 40px;
            flex-grow: 1;
            border-bottom-left-radius: 20px;
            margin-right: 15px;
            position: relative;
        }

        .lcars-bar-top::before {
            content: "SD-2026";
            position: absolute;
            left: 25px;
            bottom: 3px;
            color: #000000;
            font-weight: bold;
            font-size: 14px;
        }

        .lcars-title {
            color: var(--lcars-orange);
            font-size: 28px;
            font-weight: 300;
            margin: 0;
            line-height: 1;
            white-space: nowrap;
        }

        /* Main Window Split Column Layout */
        .lcars-container {
            display: flex;
            min-height: calc(100vh - 120px);
        }

        /* Left Side-Nav Bracket Structural Block */
        .lcars-left-bracket {
            width: 150px;
            display: flex;
            flex-direction: column;
            margin-right: 20px;
        }

        .lcars-elbow {
            background-color: var(--lcars-purple);
            height: 60px;
            border-top-left-radius: 20px;
            border-bottom-left-radius: 20px;
            margin-bottom: 15px;
            position: relative;
        }

        .lcars-elbow::after {
            content: "";
            position: absolute;
            background-color: var(--lcars-bg);
            width: 110px;
            height: 35px;
            bottom: 0;
            right: 0;
            border-top-left-radius: 15px;
        }

        /* Navigation List Styling */
        .lcars-menu {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .lcars-btn {
            background-color: var(--lcars-orange);
            color: #000000;
            padding: 10px 15px;
            text-decoration: none;
            font-weight: bold;
            font-size: 13px;
            text-align: right;
            border-radius: 5px 0 0 5px;
            transition: background 0.2s;
        }

        .lcars-btn:hover {
            background-color: #ffcc00;
        }

        /* Specialized colors for varied menu buttons */
        .btn-blue { background-color: var(--lcars-blue); }
        .btn-blue:hover { background-color: #88e2ff; }
        .btn-pink { background-color: var(--lcars-pink); }
        .btn-pink:hover { background-color: #ff99cc; }
        .btn-logout { background-color: #cc3333; color: #ffffff; margin-top: 20px;}
        .btn-logout:hover { background-color: #ff5555; }

        /* Right Content Panel Display */
        .lcars-main-panel {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .lcars-user-banner {
            border-bottom: 4px solid var(--lcars-blue);
            padding-bottom: 10px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .lcars-user-banner h1 {
            margin: 0;
            font-size: 22px;
            color: var(--lcars-blue);
            font-weight: normal;
        }

        .system-status {
            font-size: 12px;
            color: var(--lcars-dark-blue);
        }

        /* Responsive Grid Interface for Portal Links */
        .lcars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        .grid-card {
            border-left: 6px solid var(--lcars-orange);
            background-color: #111116;
            padding: 15px;
            text-decoration: none;
            color: #ffffff;
            border-radius: 0 8px 8px 0;
            transition: background-color 0.2s, transform 0.2s;
        }

        .grid-card:hover {
            background-color: #1c1c24;
            transform: translateX(3px);
        }

        .grid-card h3 {
            margin: 0 0 5px 0;
            color: var(--lcars-orange);
            font-size: 16px;
        }

        .grid-card.card-alt { border-left-color: var(--lcars-pink); }
        .grid-card.card-alt h3 { color: var(--lcars-pink); }
        .grid-card.card-info { border-left-color: var(--lcars-blue); }
        .grid-card.card-info h3 { color: var(--lcars-blue); }

        .grid-card p {
            margin: 0;
            font-size: 11px;
            color: #aaaaaa;
            text-transform: none; /* Keep text descriptions normal case */
        }
        
        .grid-card.disabled {
  pointer-events: none; /* Blocks all mouse clicks */
  cursor: not-allowed;  /* Shows a blocked cursor icon */
  opacity: 0.6;         /* Visually grays out the card */
}
    </style>
</head>
<body>

    <!-- Top Decorative Line Block -->
    <header class="lcars-header">
        <div class="lcars-bar-top"></div>
        <h2 class="lcars-title">STARFLEET COMPUTER TERMINAL</h2>
    </header>

    <div class="lcars-container">
        
        <!-- Left Side Navigation Segment -->
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <div class="lcars-menu">
                <a href="stats.php" class="lcars-btn">SYS STATS</a>
                <a href="#" class="lcars-btn">THIS</a>
                <a href="#" class="lcars-btn btn-blue">IS</a>
                <a href="#" class="lcars-btn btn-blue">ONLY</a>
                <a href="#" class="lcars-btn btn-pink">A TEST</a>
                <!-- DYNAMIC SUBSPACE COM-LINK TRANSMISSION ALERT BUTTON -->
<?php if ($has_mail): ?>
    <!-- ACTIVE TRANSMISSION DETECTED: FLASHING WARNING WITH COUNT -->
    <style>
        @keyframes lcars-pulse {
            0% { background-color: var(--lcars-pink, #cc6699); opacity: 1.0; }
            50% { background-color: #ff5555; opacity: 0.4; }
            100% { background-color: var(--lcars-pink, #cc6699); opacity: 1.0; }
        }
        .lcars-mail-flash { animation: lcars-pulse 1.2s infinite ease-in-out; border-left: 5px solid #ffffff !important; color: #000000 !important; }
    </style>
    <a href="messages.php" class="lcars-btn lcars-mail-flash" title="<?php echo $unread_count; ?> UNREAD TRANSMISSIONS">MAIL (<?php echo $unread_count; ?>)</a>
<?php else: ?>
    <!-- NO ACTIVE PAYLOADS: STEADY ROUTINE LINK BUTTON -->
    <a href="messages.php" class="lcars-btn btn-blue" title="COM CHANNELS SECURE // NO NEW MESSAGES">MESSAGES</a>
<?php endif; ?>
                <a href="logout.php" class="lcars-btn btn-logout">DISENGAGE</a>
            </div>
        </nav>

        <!-- Right Side Main Dashboard Segment -->
        <main class="lcars-main-panel">
            <div class="lcars-user-banner">
                <h1>WELCOME, <?php echo htmlspecialchars($login_session); ?></h1>
                <div class="system-status">SYS STATUS: ACTIVE // AUTH_LEVEL: SECURE</div>
            </div>

            <!-- Central Grid Layout Link Interface -->
            <div class="lcars-grid">
                
                <a href="rank_marks.php" class="grid-card">
                    <h3>RANK MARKS</h3>
                    <p>Review insignias, classifications, and regulatory metadata.</p>
                </a>
                
                <a href="academy_scores.php" class="grid-card card-alt">
                    <h3>Academy Scores</h3>
                    <p>Shows Scores and answers to exams take for users.</p>
                </a>

                <a href="staff_list.php" class="grid-card">
                    <h3>STAFF MANIFEST</h3>
                    <p>Access directory indexes for rostered officers and personnel.</p>
                </a>
                
                <a href="civilian_list.php" class="grid-card">
                    <h3>CIVILIAN MANIFEST</h3>
                    <p>Access directory indexes for Civilians.</p>
                </a>

                <a href="stats.php" class="grid-card card-info">
                    <h3>DATABASE STATS</h3>
                    <p>Review standard core metrics and traffic diagnostics data.</p>
                </a>

                <a href="#" class="grid-card card-info disabled">
                    <h3>MANIFEST ACTIVE</h3>
                    <p>Monitor active active connections on this subnet node.</p>
                </a>

                <a href="MostActiveUser.php" class="grid-card card-info">
                    <h3>PREEMINENT USER</h3>
                    <p>Analyze transaction logs for primary terminal usage records.</p>
                </a>

                <a href="grades.php" class="grid-card card-alt">
                    <h3>ACADEMY GRADES</h3>
                    <p>View current performance reviews and assessment reports.</p>
                </a>

                <a href="exams_web.php" class="grid-card card-alt">
                    <h3>TESTING ARRAYS</h3>
                    <p>Access active examination templates and testing portals.</p>
                </a>

                <a href="courses.php" class="grid-card card-alt">
                    <h3>CURRICULUM LIST</h3>
                    <p>Review available educational streams and flight profiles.</p>
                </a>

                <a href="#" class="grid-card disabled">
                    <h3>COMM CHANNELS</h3>
                    <p>Open sub-space communications link logs and routing tables.</p>
                </a>

                <a href="stardate.php" class="grid-card">
                    <h3>STARDATE CLOCK</h3>
                    <p>Display baseline synchronized atomic time arrays.</p>
                </a>

                <a href="asset.php" class="grid-card">
                    <h3>ASSET MANAGEMENT</h3>
                    <p>Audit hardware inventories, tools, and vessel manifests.</p>
                </a>
                
                <a href="web_course.php" class="grid-card">
                    <h3>WEB COURSES</h3>
                    <p>Take academy courses online using this system.</p>
                </a>
                
                <a href="service_jacket.php" class="grid-card">
                    <h3>Service Jacket</h3>
                    <p>View and edit your Current Service Jacket.</p>
                </a>
                
                <a href="dhsystem.php" class="grid-card">
                    <h3>DH System</h3>
                    <p>Let's a DH Update a member's account information.</p>
                </a>
                
                <a href="dh_delete.php" class="grid-card card-info">
                    <h3>PURGE MESSAGES</h3>
                    <p>WARNING: This deletes ALL user messages in their inbox/outbox.</p>
                </a>
                
                <a href="security_logs.php" class="grid-card card-info">
                    <h3>SECURITY LOGS</h3>
                    <p>Allows Authorized Users to view Change Logs on Active member profiles.</p>
                </a>

            </div>
        </main>
    </div>

</body>
</html>
