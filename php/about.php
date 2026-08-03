<?php
// 1. INCLUDE EXISTING SESSION MANAGEMENT
include("session.php");

// Assumes session.php provides $login_session. Fallback for guests if page is public.
$display_user = isset($login_session) ? htmlspecialchars($login_session) : "GUEST OFFICER";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo GROUP_ABBR; ?> - System Specifications</title>
    <style>
        :root { --lcars-purple: #9966cc; --lcars-orange: #ff9900; --lcars-pink: #cc6699; --lcars-blue: #33ccff; --lcars-dark-blue: #5588ff; --lcars-bg: #000000; --lcars-green: #33cc66; }
        body { background-color: var(--lcars-bg); color: #ffffff; font-family: "Arial Custom", "Helvetica Neue", Arial, sans-serif; margin: 0; padding: 15px; text-transform: uppercase; letter-spacing: 1px; overflow-x: hidden; }
        .lcars-header { display: flex; align-items: flex-end; margin-bottom: 15px; }
        .lcars-bar-top { background-color: var(--lcars-purple); height: 40px; flex-grow: 1; border-bottom-left-radius: 20px; margin-right: 15px; position: relative; }
        .lcars-bar-top::before { content: "SYS-DOC-001"; position: absolute; left: 25px; bottom: 3px; color: #000000; font-weight: bold; font-size: 14px; }
        .lcars-title { color: var(--lcars-orange); font-size: 28px; font-weight: 300; margin: 0; line-height: 1; white-space: nowrap; }
        .lcars-container { display: flex; min-height: calc(100vh - 120px); }
        .lcars-left-bracket { width: 150px; display: flex; flex-direction: column; margin-right: 20px; }
        .lcars-elbow { background-color: var(--lcars-purple); height: 60px; border-top-left-radius: 20px; border-bottom-left-radius: 20px; margin-bottom: 15px; position: relative; }
        .lcars-elbow::after { content: ""; position: absolute; background-color: var(--lcars-bg); width: 110px; height: 35px; bottom: 0; right: 0; border-top-left-radius: 15px; }
        .lcars-menu { display: flex; flex-direction: column; gap: 8px; }
        .lcars-btn { background-color: var(--lcars-orange); color: #000000; padding: 10px 15px; text-decoration: none; font-weight: bold; font-size: 13px; text-align: right; border-radius: 5px 0 0 5px; transition: background 0.2s; border: none; cursor: pointer; text-transform: uppercase; }
        .lcars-btn:hover { background-color: #ffcc00; }
        .btn-blue { background-color: var(--lcars-blue); }
        .btn-blue:hover { background-color: #88e2ff; }
        .btn-pink { background-color: var(--lcars-pink); }
        .btn-pink:hover { background-color: #ff99cc; }
        .lcars-main-panel { flex-grow: 1; display: flex; flex-direction: column; }
        .lcars-user-banner { border-bottom: 4px solid var(--lcars-blue); padding-bottom: 10px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .lcars-user-banner h1 { margin: 0; font-size: 22px; color: var(--lcars-blue); font-weight: normal; }
        .system-status { font-size: 12px; color: var(--lcars-dark-blue); }
        
        /* Specialized About Page Sections */
        .lcars-section { background-color: #111116; border-left: 6px solid var(--lcars-orange); padding: 25px; border-radius: 0 8px 8px 0; margin-bottom: 25px; }
        .lcars-section.sec-alt { border-left-color: var(--lcars-pink); }
        .lcars-section h3 { margin: 0 0 15px 0; font-size: 18px; color: var(--lcars-orange); letter-spacing: 2px; }
        .lcars-section.sec-alt h3 { color: var(--lcars-pink); }
        .lcars-section p { font-size: 13px; line-height: 1.6; color: #dddddd; text-transform: none; margin: 0 0 15px 0; }
        
        /* Visual Specs List */
        .specs-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px; }
        .spec-item { background: #000000; border: 1px solid #222230; padding: 12px; border-radius: 4px; border-top: 3px solid var(--lcars-blue); }
        .spec-label { color: var(--lcars-blue); font-size: 11px; font-weight: bold; margin-bottom: 4px; }
        .spec-value { font-size: 13px; color: #ffffff; }
    </style>
</head>
<body>

    <header class="lcars-header">
        <div class="lcars-bar-top"></div>
        <h2 class="lcars-title">STARFLEET INFORMATION REGISTRY</h2>
    </header>

    <div class="lcars-container">
        
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <div class="lcars-menu">
                <a href="welcome.php" class="lcars-btn">MAIN TERM</a>
                <a href="staff_list.php" class="lcars-btn btn-blue">ROSTER</a>
                <a href="about.php" class="lcars-btn btn-pink">ABOUT SYS</a>
            </div>
        </nav>

        <main class="lcars-main-panel">
            <div class="lcars-user-banner">
                <h1>SYSTEM OVERVIEW // ARCHITECTURE DOCUMENTATION</h1>
                <div class="system-status">VIEWER: <?php echo $display_user; ?> // ACCESS: OPEN</div>
            </div>

            <!-- Block 1: Group Description -->
            <section class="lcars-section">
                <h3>GROUP MANIFEST & MISSION PROTOCOLS</h3>
                <p>
                    <?php echo GROUP_NAME; ?>
                </p>
                <p>
                    By maintaining precise regulatory indicators—including roster assignments, division allocations, and gradebook record validation templates—we preserve operational readiness and coordinate stream activities efficiently across multiple subnet sectors.
                </p>
            </section>

            <!-- Block 2: System Specifications -->
            <section class="lcars-section sec-alt">
                <h3>CORE ARCHITECTURE SPECIFICATIONS</h3>
                <p>
                    This utility application functions as a custom Library Computer Access and Retrieval System (LCARS) overlay interface. It maps data matrices directly from secure MySQL storage clusters into clean visual frames tailored for organizational command chains.  The LCARS interface is currently in early alpha stage and some system componets may not function as intended. Please report any malfunctions to the Chief of Fleet Operations.
                </p>

                <div class="specs-grid">
                    <div class="spec-item">
                        <div class="spec-label">INTERFACE DESIGNATION</div>
                        <div class="spec-value">LCARS TERMINAL OVERLAY</div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">DATABASE CONNECTOR</div>
                        <div class="spec-value">MYSQLi PARAMETRIC EXTENSION - PREPARED STATEMENT MATRIX</div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">ENCRYPTION PROTOCOL</div>
                        <div class="spec-value">BCRYPT CRYPTOGRAPHIC KEY-STRETCHING PROTOCOL</div> 
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">STORAGE STORAGE POOL</div>
                        <div class="spec-value">INFINITYFREE SUBNET HOST</div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">SECURE MESSAGING</div>
                        <div class="spec-value">SECURE MESSAGES VIA WEBINTERFACE</div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">TIMELINE STARDATES</div>
                        <div class="spec-value">REAL AND ACCURATE STARDATES</div>
                    </div>
                </div>
            </section>
            
        </main>
    </div>

</body>
</html>
