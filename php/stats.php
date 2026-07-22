<?php
header('Content-type: text/html; charset=utf-8');
include("config.php");
mysqli_set_charset($db, "utf8");

// FIXED: Use a ternary operator to fall back to 'rank' if no POST data exists yet
$type = isset($_POST['stat']) ? $_POST['stat'] : 'rank';

$stat_title = "";
$query = "";

if ($type == "division") {
    $stat_title = "DIVISION MANIFEST MATRIX";
    $query = "SELECT d.dname AS `name`, COUNT(*) AS `people` FROM `divisions` d INNER JOIN `accounts` a ON a.DivID=d.did GROUP BY d.did ORDER BY name";
} else {
    // Default fallback to prevent undefined variable crashes
    $type = 'rank';
    $stat_title = "RANK HIERARCHY MATRIX";
    $query = "SELECT r.rname AS `name`, COUNT(*) AS `people` FROM `Rank` r INNER JOIN `accounts` a ON a.RankID=r.RankID GROUP BY r.RankID ORDER BY r.RankID";
}

$result = mysqli_query($db, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS Starfleet Personnel Statistics</title>
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
        .lcars-bar-top { background-color: var(--lcars-purple); height: 40px; flex-grow: 1; border-bottom-left-radius: 20px; margin-right: 15px; position: relative; }
        .lcars-bar-top::before { content: "SYS-STAT-330"; position: absolute; left: 25px; bottom: 3px; color: #000000; font-weight: bold; font-size: 14px; }
        .lcars-title { color: var(--lcars-purple); font-size: 28px; font-weight: 300; margin: 0; white-space: nowrap; }

        .lcars-container { display: flex; min-height: 80vh; }
        .lcars-left-bracket { width: 150px; display: flex; flex-direction: column; margin-right: 20px; }
        .lcars-elbow { background-color: var(--lcars-purple); height: 60px; border-top-left-radius: 20px; border-bottom-left-radius: 20px; margin-bottom: 15px; position: relative; }
        .lcars-elbow::after { content: ""; position: absolute; background-color: var(--lcars-bg); width: 110px; height: 35px; bottom: 0; right: 0; border-top-left-radius: 15px; }
        
        .lcars-btn { background-color: var(--lcars-orange); color: #000000; padding: 10px; text-decoration: none; font-weight: bold; font-size: 13px; text-align: right; margin-bottom: 5px; border-radius: 5px 0 0 5px; }
        .lcars-btn.btn-back { background-color: var(--lcars-blue); }
        .lcars-main-panel { flex-grow: 1; display: flex; flex-direction: column; }

        .lcars-dropdown-container {
            margin-bottom: 25px;
            background: #111116;
            padding: 15px;
            border-left: 6px solid var(--lcars-orange);
            border-radius: 0 10px 10px 0;
        }
        .lcars-select {
            background-color: #000000;
            color: var(--lcars-orange);
            border: 2px solid var(--lcars-orange);
            padding: 10px;
            font-size: 16px;
            text-transform: uppercase;
            border-radius: 5px;
            width: 100%;
            max-width: 400px;
            cursor: pointer;
            letter-spacing: 1px;
        }

        .lcars-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .lcars-table th {
            background-color: var(--lcars-blue);
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
        }
        .lcars-table tr:hover { background-color: #111116; }
    </style>
</head>
<body>
    <header class="lcars-header">
        <div class="lcars-bar-top"></div>
        <h2 class="lcars-title">STARFLEET QUANTUM STATISTICS</h2>
    </header>

    <div class="lcars-container">
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <a href="welcome.php" class="lcars-btn btn-back">MAIN TERM</a>
            <a href="staff_list.php" class="lcars-btn">STAFF LIST</a>
            <a href="academy.php" class="lcars-btn" style="background-color: var(--lcars-pink);">ACADEMY TERM</a>
            <a href="stardate.php" class="lcars-btn">STARDATE CLOCK</a>
        </nav>

        <main class="lcars-main-panel">
            
            <!-- Interactive Statistic Vector Filter Selector -->
            <div class="lcars-dropdown-container">
                <form method="POST" action="" id="stat-form">
                    <label for="stat-select" style="color: var(--lcars-orange); display: block; margin-bottom: 8px; font-weight: bold;">SELECT METRIC QUANDRANT:</label>
                    <select id="stat-select" name="stat" class="lcars-select" onchange="this.form.submit();">
                        <option value="rank" <?php echo ($type == 'rank') ? 'selected' : ''; ?>>PERSONNEL COUNT BY RANK</option>
                        <option value="division" <?php echo ($type == 'division') ? 'selected' : ''; ?>>PERSONNEL COUNT BY DIVISION</option>
                    </select>
                </form>
            </div>

            <h2>DATA STREAM: <?php echo $stat_title; ?></h2>
            <p style="text-transform: none; color: #aaa; margin-bottom: 20px;">
                Starfleet Command Analytics Sensor Array Feed
            </p>

            <table class="lcars-table">
                <thead>
                    <tr>
                        <th>DESIGNATION CLASSIFICATION</th>
                        <th style="text-align: right; width: 200px;">ACTIVE COMPLEMENT COUNT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_array($result)) {
                            echo "<tr>";
                            echo "<td style='color: var(--lcars-blue); font-weight: bold;'>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td style='text-align: right; color: var(--lcars-green); font-weight: bold;'>" . htmlspecialchars($row['people']) . " </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2' style='color: var(--lcars-pink); text-align: center;'>METRIC SYSTEM COMPILATION BLOCKED // NO ROWS FOUND</td></tr>";
                    }
                    
                    mysqli_close($db);
                    ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
