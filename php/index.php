<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS Subspace Routing Error - 404</title>
    <style>
        :root {
            --lcars-purple: #9966cc;
            --lcars-orange: #ff9900;
            --lcars-pink: #cc6699;
            --lcars-blue: #33ccff;
            --lcars-bg: #000000;
            --lcars-green: #33cc33;
        }

        body {
            background-color: var(--lcars-bg);
            color: #ffffff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            flex-direction: column;
            min-height: 95vh;
        }

        .lcars-header { display: flex; align-items: flex-end; margin-bottom: 15px; }
        .lcars-bar-top { background-color: var(--lcars-pink); height: 40px; flex-grow: 1; border-bottom-left-radius: 20px; margin-right: 15px; position: relative; }
        .lcars-bar-top::before { content: "ERR-ROUTE-404"; position: absolute; left: 25px; bottom: 3px; color: #000000; font-weight: bold; font-size: 14px; }
        .lcars-title { color: var(--lcars-pink); font-size: 28px; font-weight: 300; margin: 0; white-space: nowrap; }

        .lcars-container { display: flex; flex-grow: 1; }
        .lcars-left-bracket { width: 150px; display: flex; flex-direction: column; margin-right: 20px; }
        .lcars-elbow { background-color: var(--lcars-pink); height: 60px; border-top-left-radius: 20px; border-bottom-left-radius: 20px; margin-bottom: 15px; position: relative; }
        .lcars-elbow::after { content: ""; position: absolute; background-color: var(--lcars-bg); width: 110px; height: 35px; bottom: 0; right: 0; border-top-left-radius: 15px; }
        .lcars-side-block { background-color: var(--lcars-purple); height: 45px; border-radius: 5px 0 0 5px; margin-bottom: 5px; }

        .lcars-main-panel { flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
        
        /* Immersive Pulsing Terminal Animation Layer */
        .lcars-alert-box {
            background-color: #220505;
            border-left: 8px solid var(--lcars-pink);
            padding: 40px;
            border-radius: 0 15px 15px 0;
            margin-top: 20px;
            animation: lcarsPulse 2s infinite ease-in-out;
        }

        .alert-heading {
            color: #ff5555;
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 15px;
            letter-spacing: 2px;
        }

        .alert-text {
            color: #ffffff;
            font-size: 16px;
            line-height: 1.6;
            text-transform: none;
        }

        .engage-btn {
            background-color: var(--lcars-orange);
            color: #000000;
            border: none;
            padding: 15px 30px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 10px;
            letter-spacing: 2px;
            text-decoration: none;
            text-align: center;
            display: inline-block;
            width: auto;
            max-width: 320px;
            margin-top: 30px;
        }
        .engage-btn:hover { background-color: #ffb74d; }

        @keyframes lcarsPulse {
            0% { border-left-color: var(--lcars-pink); background-color: #220505; box-shadow: 0 0 0px rgba(204, 102, 153, 0); }
            50% { border-left-color: #ff3333; background-color: #3d0a0a; box-shadow: 0 0 15px rgba(255, 51, 51, 0.3); }
            100% { border-left-color: var(--lcars-pink); background-color: #220505; box-shadow: 0 0 0px rgba(204, 102, 153, 0); }
        }
    </style>
</head>
<body>
    <header class="lcars-header">
        <div class="lcars-bar-top"></div>
        <h2 class="lcars-title">SYSTEM ALERT ARRAY</h2>
    </header>

    <div class="lcars-container">
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <div class="lcars-side-block"></div>
            <div class="lcars-side-block" style="background-color: var(--lcars-blue);"></div>
        </nav>

        <main class="lcars-main-panel">
            <div>
                <h2>SUBSPACE DISCONNECT</h2>
                <p style="text-transform: none; color: #aaa; margin-bottom: 20px;">
                    Starfleet Network Infrastructure // Vector Traversal Error
                </p>

                <!-- ANIMATED ALERT CONTAINER -->
                <div class="lcars-alert-box">
                    <div class="alert-heading">404 ERROR - SUBSYSTEM NOT FOUND</div>
                    <div class="alert-text">
                        The matrix sector path requested does not correlate to active mainframe storage coordinates.<br><br>
                        Telemetry grid checks indicate this directory layer might have been decommissioned, reassigned to a secure quadrant, or the internal database bridge routing link was incorrectly passed to this subpanel.
                    </div>
                </div>

                <!-- ROUTING CONTROLLER ACTION BUTTON -->
                <a href="login.php" class="engage-btn">RETURN TO MAIN SYSTEM LOGIN</a>
            </div>
        </main>
    </div>
</body>
</html>
