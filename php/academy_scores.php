<?php
header('Content-type: text/html; charset=utf-8');
include('session.php'); // Protects the page, ensures $login_session exists

if (!isset($db)) {
    include("config.php"); 
}
mysqli_set_charset($db, "utf8");

// 🔒 STRICT DEPARTMENT HEAD AUTHORIZATION GUARD (Level 10 Clearance Enforced)
$auth_username = mysqli_real_escape_string($db, $login_session);
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

// 📡 FIXED JOIN QUERY: Links your compressed scores data cleanly with their calculated gradebook score!
$query_string = "SELECT s.`username`, s.`course_id`, s.`question_number`, s.`user_answer`, s.`submitted_at`, g.`Grade` 
                 FROM `scores` s 
                 LEFT JOIN `gradebook` g ON s.`username` = g.`username` AND s.`course_id` = g.`courses` 
                 ORDER BY s.`username` ASC";

$result = mysqli_query($db, $query_string);
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>LCARS Academy Scores Registry</title>
<style>
    :root { --lcars-purple: #9966cc; --lcars-orange: #ff9900; --lcars-pink: #cc6699; --lcars-blue: #33ccff; --lcars-bg: #000000; --lcars-green: #33cc33; }
    body { background-color: var(--lcars-bg); color: #ffffff; font-family: Arial, sans-serif; margin: 0; padding: 15px; text-transform: uppercase; letter-spacing: 1px; }
    .lcars-header { display: flex; align-items: flex-end; margin-bottom: 15px; }
    .lcars-bar-top { background-color: var(--lcars-pink); height: 40px; flex-grow: 1; border-bottom-left-radius: 20px; margin-right: 15px; position: relative; }
    .lcars-bar-top::before { content: "SYS-GRAD-501"; position: absolute; left: 25px; bottom: 3px; color: #000000; font-weight: bold; font-size: 14px; }
    .lcars-title { color: var(--lcars-pink); font-size: 28px; font-weight: 300; margin: 0; white-space: nowrap; }
    .lcars-container { display: flex; min-height: 80vh; }
    .lcars-left-bracket { width: 150px; display: flex; flex-direction: column; margin-right: 20px; }
    .lcars-elbow { background-color: var(--lcars-pink); height: 60px; border-top-left-radius: 20px; border-bottom-left-radius: 20px; margin-bottom: 15px; position: relative; }
    .lcars-elbow::after { content: ""; position: absolute; background-color: var(--lcars-bg); width: 110px; height: 35px; bottom: 0; right: 0; border-top-left-radius: 15px; }
    .lcars-btn { background-color: var(--lcars-orange); color: #000000; padding: 10px; text-decoration: none; font-weight: bold; font-size: 13px; text-align: right; margin-bottom: 5px; border-radius: 5px 0 0 5px; }
    .lcars-btn.btn-back { background-color: var(--lcars-blue); }
    .lcars-main-panel { flex-grow: 1; display: flex; flex-direction: column; }
    .lcars-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    .lcars-table th { background-color: var(--lcars-purple); color: #000000; text-align: left; padding: 12px; font-size: 14px; font-weight: bold; }
    .lcars-table td { padding: 15px 12px; border-bottom: 1px solid #22222b; font-size: 14px; vertical-align: middle; }
    .lcars-table tr:hover { background-color: #111116; }
</style>
</head>
<body>
    <header class="lcars-header"><div class="hb lcars-bar-top"></div><h2 class="ht lcars-title">Academy Scores MATRIX</h2></header>
    <div class="lcars-container">
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <a href="dhpanel.php" class="lcars-btn btn-back">DH Panel</a>
            <a href="staff_list.php" class="lcars-btn">STAFF LIST</a>
            <a href="courses.php" class="lcars-btn">COURSES</a>
            <a href="exams_web.php" class="lcars-btn" style="background-color: var(--lcars-purple);">TEST ARRAY</a>
        </nav>
        <main class="lcars-main-panel">
            <h2>ACADEMY-WIDE PERFORMANCE RECORDS</h2>
            <p style="text-transform: none; color: #aaa; margin-bottom: 20px;">Starfleet Training Command Registry Feed // Academic Standing Manifest</p>

            <table class="lcars-table">
                <thead>
                    <tr>
                        <th>CADET DESIGNATION NAME</th>
                        <th>ASSIGNED COURSE FIELD</th>
                        <th>QUESTION NUMBERS</th>
                        <th>QUESTION ANSWERS</th>
                        <th>SUBMISSION DATE</th>
                        <th style="text-align: right;">EVALUATION EFFICIENCY</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Safely extract the dynamic grade score percent values
                            $grade_score = isset($row['Grade']) ? (float)$row['Grade'] : 0.00;
                            $score_color = ($grade_score >= 80.00) ? 'var(--lcars-green)' : 'var(--lcars-pink)';
                            
                            echo "<tr>";
                            echo "<td style='color: var(--lcars-orange); font-weight: bold;'>" . htmlspecialchars($row['username']) . "</td>";
                            echo "<td style='color: var(--lcars-blue);'>" . htmlspecialchars($row['course_id']) . "</td>";
                            echo "<td style='color: var(--lcars-blue);'>" . htmlspecialchars($row['question_number']) . "</td>";
                            echo "<td style='color: var(--lcars-blue);'>" . htmlspecialchars($row['user_answer']) . "</td>";
                            echo "<td style='color: #888888; font-family: monospace;'>" . htmlspecialchars($row['submitted_at']) . "</td>";
                            echo "<td style='text-align: right; color: " . $score_color . "; font-weight: bold;'>" . number_format($grade_score, 2) . "%</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' style='color: var(--lcars-orange); text-align: center;'>NO ACADEMIC GRADES RECORDED ON MAINFRAME STORAGE SENSORS.</td></tr>";
                    }
                    mysqli_close($db);
                    ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
