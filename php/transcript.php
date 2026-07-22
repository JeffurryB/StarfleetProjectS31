<?php
// 1. Session and Database Check
include('session.php'); // Protects the page, ensures $login_session exists
include('config.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Get the user's information from the database using their session name
$user_check = mysqli_real_escape_string($db, $login_session);

// 2. Fetch User's Grades from Gradebook
$grades_query = mysqli_query($db, "SELECT courses, Grade, date_completed FROM gradebook WHERE username = '$user_check' ORDER BY date_completed DESC");

// Calculate general performance metrics
$total_exams = mysqli_num_rows($grades_query);
$grade_sum = 0;
$average_grade = 0;

if ($total_exams > 0) {
    while($row = mysqli_fetch_assoc($grades_query)) {
        $grade_sum += $row['Grade'];
    }
    $average_grade = round($grade_sum / $total_exams, 2);
    mysqli_data_seek($grades_query, 0); // Reset pointer loop back to start for display below
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS Academy Cadet Transcript</title>
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
        .lcars-bar-top::before { content: "SYS-REC-402"; position: absolute; left: 25px; bottom: 3px; color: #000000; font-weight: bold; font-size: 14px; }
        .lcars-title { color: var(--lcars-purple); font-size: 28px; font-weight: 300; margin: 0; white-space: nowrap; }

        .lcars-container { display: flex; min-height: 80vh; }
        .lcars-left-bracket { width: 150px; display: flex; flex-direction: column; margin-right: 20px; }
        .lcars-elbow { background-color: var(--lcars-purple); height: 60px; border-top-left-radius: 20px; border-bottom-left-radius: 20px; margin-bottom: 15px; position: relative; }
        .lcars-elbow::after { content: ""; position: absolute; background-color: var(--lcars-bg); width: 110px; height: 35px; bottom: 0; right: 0; border-top-left-radius: 15px; }
        
        .lcars-btn { background-color: var(--lcars-orange); color: #000000; padding: 10px; text-decoration: none; font-weight: bold; font-size: 13px; text-align: right; margin-bottom: 5px; border-radius: 5px 0 0 5px; }
        .lcars-btn.btn-back { background-color: var(--lcars-blue); }

        .lcars-main-panel { flex-grow: 1; display: flex; flex-direction: column; }

        /* Metrics Data Row styling */
        .lcars-summary-grid {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }
        .metric-card {
            background-color: #111116;
            border-left: 6px solid var(--lcars-orange);
            padding: 15px 25px;
            border-radius: 0 10px 10px 0;
            flex: 1;
        }
        .metric-title { color: var(--lcars-orange); font-size: 12px; margin-bottom: 5px; font-weight: bold; }
        .metric-value { font-size: 24px; font-weight: 300; color: #ffffff; }

        /* Transcript Data Table */
        .transcript-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .transcript-table th {
            background-color: var(--lcars-pink);
            color: #000000;
            text-align: left;
            padding: 12px;
            font-size: 14px;
            font-weight: bold;
        }
        .transcript-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #22222b;
            font-size: 14px;
        }
        .transcript-table tr:hover {
            background-color: #111116;
        }
        
        /* Status Colors */
        .grade-pass { color: var(--lcars-green); font-weight: bold; }
        .grade-fail { color: var(--lcars-pink); font-weight: bold; }
    </style>
</head>
<body>

    <header class="lcars-header">
        <div class="lcars-bar-top"></div>
        <h2 class="lcars-title">ACADEMY PERFORMANCE TRANSCRIPT</h2>
    </header>

    <div class="lcars-container">
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <a href="welcome.php" class="lcars-btn btn-back">MAIN TERM</a>
            <a href="exams_web.php" class="lcars-btn">TEST ARRAY</a>
        </nav>

        <main class="lcars-main-panel">
            <h2>ACADEMY RECORD FOR: <?php echo htmlspecialchars($login_session); ?></h2>
            <p style="text-transform: none; color: #aaa; margin-bottom: 25px;">Starfleet Personnel File Registry // Secure Subspace Connection Verified</p>

            <!-- Overview Data Row -->
            <div class="lcars-summary-grid">
                <div class="metric-card">
                    <div class="metric-title">COMPLETED QUADRANTS</div>
                    <div class="metric-value"><?php echo $total_exams; ?> EXAMS LOGGED</div>
                </div>
                <div class="metric-card" style="border-left-color: var(--lcars-blue);">
                    <div class="metric-title">CUMULATIVE EFFICIENCY</div>
                    <div class="metric-value"><?php echo $average_grade; ?>% AVG</div>
                </div>
            </div>

            <!-- Main Transcript Sheet -->
            <?php if ($total_exams > 0): ?>
                <table class="transcript-table">
                    <thead>
                        <tr>
                            <th>COURSE FIELD OBJECTIVE</th>
                            <th>EVALUATION SCORE</th>
                            <th>STATUS CODE</th>
                            <th>TIMESTAMP LOGGED</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($grades_query)): 
                            $grade = $row['Grade'];
                            // Simple Starfleet passing criteria (e.g. 70%)
                            $is_pass = ($grade >= 70); 
                            ?>
                            <tr>
                                <td style="color: var(--lcars-blue); font-weight: bold;"><?php echo htmlspecialchars($row['courses']); ?></td>
                                <td><?php echo $grade; ?>%</td>
                                <td class="<?php echo $is_pass ? 'grade-pass' : 'grade-fail'; ?>">
                                    <?php echo $is_pass ? 'PASSED // CERTIFIED' : 'FAILED // ACADEMY REVIEW'; ?>
                                </td>
                                <td style="color: #aaaaaa; text-transform: none;"><?php echo date("Y-m-d H:i", strtotime($row['date_completed'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="background-color: #111116; border-left: 6px solid var(--lcars-pink); padding: 25px; border-radius: 0 10px 10px 0;">
                    <p style="color: var(--lcars-pink); margin: 0; font-weight: bold;">NO TRANSCRIPT ENTRIES FOUND FOR THIS ACCOUNT RECORD.</p>
                    <p style="text-transform: none; color: #ccc; margin: 10px 0 0 0;">Please access the Testing Array terminal link on the left panel to complete your first evaluation layout.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>

</body>
</html>
