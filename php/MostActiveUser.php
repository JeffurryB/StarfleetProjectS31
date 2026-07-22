<?php
header('Content-type: text/html; charset=utf-8');
include("config.php");
mysqli_set_charset($db, "utf8");

// Grab the top 5 most active members from the Time clock
$ActiveMembersQuery = "SELECT IFNULL(a.DisplayName,a.username) AS `name`, SEC_TO_TIME(SUM(`time_out` - `time_in`)) AS `total` FROM `Time Clock` as t JOIN `accounts` a ON t.user_id = a.ID GROUP BY t.`user_id` ORDER BY total DESC LIMIT 5";
$result = mysqli_query($db, $ActiveMembersQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS Starfleet Activity Leaderboard</title>
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
        }
        .lcars-header { display: flex; align-items: flex-end; margin-bottom: 15px; }
        .lcars-bar-top { background-color: var(--lcars-blue); height: 40px; flex-grow: 1; border-bottom-left-radius: 20px; margin-right: 15px; position: relative; }
        .lcars-bar-top::before { content: "SYS-TIME-881"; position: absolute; left: 25px; bottom: 3px; color: #000000; font-weight: bold; font-size: 14px; }
        .lcars-title { color: var(--lcars-blue); font-size: 28px; font-weight: 300; margin: 0; white-space: nowrap; }

        .lcars-container { display: flex; min-height: 80vh; }
        .lcars-left-bracket { width: 150px; display: flex; flex-direction: column; margin-right: 20px; }
        .lcars-elbow { background-color: var(--lcars-blue); height: 60px; border-top-left-radius: 20px; border-bottom-left-radius: 20px; margin-bottom: 15px; position: relative; }
        .lcars-elbow::after { content: ""; position: absolute; background-color: var(--lcars-bg); width: 110px; height: 35px; bottom: 0; right: 0; border-top-left-radius: 15px; }
        
        .lcars-btn { background-color: var(--lcars-orange); color: #000000; padding: 10px; text-decoration: none; font-weight: bold; font-size: 13px; text-align: right; margin-bottom: 5px; border-radius: 5px 0 0 5px; }
        .lcars-btn.btn-back { background-color: var(--lcars-purple); }
        .lcars-main-panel { flex-grow: 1; display: flex; flex-direction: column; }

        .lcars-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .lcars-table th {
            background-color: var(--lcars-orange);
            color: #000000;
            text-align: left;
            padding: 12px;
            font-size: 14px;
            font-weight: bold;
        }
        .lcars-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #22222b;
            font-size: 14px;
            vertical-align: middle;
        }
        .lcars-table tr:hover { background-color: #111116; }
    </style>
</head>
<body>
    <header class="lcars-header">
        <div class="lcars-bar-top"></div>
        <h2 class="lcars-title">ACTIVITY MATRIX</h2>
    </header>

    <div class="lcars-container">
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <a href="welcome.php" class="lcars-btn btn-back">MAIN TERM</a>
            <a href="staff_list.php" class="lcars-btn">STAFF LIST</a>
            <a href="stats.php" class="lcars-btn">STATS</a>
            <a href="grades.php" class="lcars-btn" style="background-color: var(--lcars-pink);">GRADES</a>
        </nav>

        <main class="lcars-main-panel">
            <h2>TOP 5 MOST ACTIVE MEMBERS</h2>
            <p style="text-transform: none; color: #aaa; margin-bottom: 20px;">
                Starfleet Chrono-Telemetry Array // Time Clock Metrics Compiled
            </p>

            <table class="lcars-table">
                <thead>
                    <tr>
                        <th style="width: 100px;">RANKING</th>
                        <th>OFFICER DESIGNATION</th>
                        <th style="text-align: right;">TOTAL COMMITTED TIME</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result) {
                        $ranking = 1;
                        while ($row = mysqli_fetch_array($result)) {
                            // First place gets a distinct bright blue color indicator
                            $rank_color = ($ranking === 1) ? 'var(--lcars-blue)' : 'var(--lcars-purple)';
                            
                            echo "<tr>";
                            echo "<td style='color: " . $rank_color . "; font-weight: bold;'>#" . $ranking . "</td>";
                            echo "<td style='color: #ffffff; font-weight: bold;'>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td style='text-align: right; color: var(--lcars-green); font-weight: bold;'>" . htmlspecialchars($row['total']) . "</td>";
                            echo "</tr>";
                            $ranking++;
                        }
                    } else {
                        echo "<tr><td colspan='3' style='color: var(--lcars-pink); text-align: center;'>ERROR FETCHING MANIFEST: " . htmlspecialchars(mysqli_error($db)) . "</td></tr>";
                    }
                    
                    mysqli_close($db);
                    ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
