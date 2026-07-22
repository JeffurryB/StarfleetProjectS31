<?php
header('Content-type: text/html; charset=utf-8');
include("config.php");
mysqli_set_charset($db, "utf8");

$DivHeadersQuery = "SELECT `did`, `dname` FROM `divisions` WHERE `did` NOT IN (1,2,3,4,5,6,7,8,9,11,12,13,14,16) ORDER BY `dname`";
$DivResult = mysqli_query($db, $DivHeadersQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS Civilian Manifest</title>
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
        .lcars-bar-top::before { content: "SYS-MANIFEST-209"; position: absolute; left: 25px; bottom: 3px; color: #000000; font-weight: bold; font-size: 14px; }
        .lcars-title { color: var(--lcars-purple); font-size: 28px; font-weight: 300; margin: 0; white-space: nowrap; }

        .lcars-container { display: flex; min-height: 80vh; }
        .lcars-left-bracket { width: 150px; display: flex; flex-direction: column; margin-right: 20px; }
        .lcars-elbow { background-color: var(--lcars-purple); height: 60px; border-top-left-radius: 20px; border-bottom-left-radius: 20px; margin-bottom: 15px; position: relative; }
        .lcars-elbow::after { content: ""; position: absolute; background-color: var(--lcars-bg); width: 110px; height: 35px; bottom: 0; right: 0; border-top-left-radius: 15px; }
        
        .lcars-btn { background-color: var(--lcars-orange); color: #000000; padding: 10px; text-decoration: none; font-weight: bold; font-size: 13px; text-align: right; margin-bottom: 5px; border-radius: 5px 0 0 5px; }
        .lcars-btn.btn-back { background-color: var(--lcars-blue); }
        .lcars-main-panel { flex-grow: 1; display: flex; flex-direction: column; }

        .division-header {
            color: var(--lcars-orange);
            font-size: 18px;
            font-weight: bold;
            margin: 25px 0 10px 0;
            border-bottom: 2px solid var(--lcars-orange);
            padding-bottom: 5px;
        }

        .lcars-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .lcars-table th {
            background-color: var(--lcars-blue);
            color: #000000;
            text-align: left;
            padding: 10px;
            font-size: 13px;
            font-weight: bold;
        }
        .lcars-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #22222b;
            font-size: 14px;
            vertical-align: middle;
        }
        .lcars-table tr:hover { background-color: #111116; }
        
        .member-link {
            color: #ffcc00;
            text-decoration: none;
            font-weight: bold;
        }
        .member-link:hover { text-decoration: underline; color: #ffffff; }
        .rank-container { font-size: 12px; text-align: center; color: #aaa; }
        .error-text { color: var(--lcars-pink); font-weight: bold; }
    </style>
</head>
<body>
    <header class="lcars-header">
        <div class="lcars-bar-top"></div>
        <h2 class="lcars-title">CIVILIANS LIST</h2>
    </header>

    <div class="lcars-container">
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <a href="welcome.php" class="lcars-btn btn-back">MAIN TERM</a>
            <a href="staff_list.php" class="lcars-btn">ROSTER</a>
            <a href="exams_web.php" class="lcars-btn" style="background-color: var(--lcars-pink);">TEST ARRAY</a>
            <a href="transcript.php" class="lcars-btn">TRANSCRIPT</a>
        </nav>

        <main class="lcars-main-panel">
            <h2>CIVILIAN ROSTER</h2>
            <p style="text-transform: none; color: #aaa; margin-bottom: 10px;">
                Active Civilian Manifest // Real-Time Grid Synchronized
            </p>

            <?php
            if (!$DivResult) {
                echo "<h3 class='error-text'>ERROR EXECUTING COMMAND: " . htmlspecialchars(mysqli_error($db)) . "</h3>";
            } else {
                while ($divRow = mysqli_fetch_array($DivResult)) {
                    $divId = $divRow['did'];
                    echo "<div class='division-header'>" . htmlspecialchars($divRow['dname']) . "</div>";
                    
                    echo "<table class='lcars-table'>";
                    echo "<thead><tr><th style='width: 150px; text-align: center;'>RANK</th><th>NAME</th><th>TITLE</th></tr></thead>";
                    echo "<tbody>";

                    $MembersByDivision = "SELECT a.username, r.RankLogo, r.rname, IFNULL(a.DisplayName, a.username) AS `name`, t.tag_name, a.`UUID` 
                      FROM `accounts` a 
                      INNER JOIN `divisions` d ON a.`DivID` = d.`did` 
                      INNER JOIN `Rank` r ON a.`RankID` = r.`RankID` 
                      INNER JOIN `Titles` t ON a.`TitleID` = t.`tid` 
                      WHERE a.active = '1' 
                      AND d.`did` NOT IN (1,2,3,4,5,6,7,8,9,11,12,13,14,15) 
                      AND d.`did` = '" . mysqli_real_escape_string($db, $divId) . "' 
                      ORDER BY r.`RankID`";

                    $result = mysqli_query($db, $MembersByDivision);
                    if (!$result) {
                        echo "<tr><td colspan='3' class='error-text'>GRID ERROR: " . htmlspecialchars(mysqli_error($db)) . "</td></tr>";
                    } elseif (mysqli_num_rows($result) === 0) {
                        echo "<tr><td colspan='3' style='color: #666;'>NO MEMBERS RECORDED IN THIS QUADRANT</td></tr>";
                    } else {
                        while ($row = mysqli_fetch_array($result)) {
                            echo "<tr>";
                            // Rank column combining textual name and graphics tags securely
                            echo "<td class='rank-container'>" . htmlspecialchars($row['rname']) . "<br>" . $row['RankLogo'] . "</td>";
                            // Native SL App link layer Integration
                             echo "<td><a class='member-link' href='servicejacket.php?username=" . urlencode($row['username']) . "'>" . htmlspecialchars($row['name']) . "</a></td>";
                            echo "<td style='color: var(--lcars-blue);'>" . htmlspecialchars($row['tag_name']) . "</td>";
                            echo "</tr>";
                        }
                    }
                    echo "</tbody></table>";
                }
            }
            mysqli_close($db);
            ?>
        </main>
    </div>
</body>
</html>
