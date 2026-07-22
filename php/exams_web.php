<?php
// 1. Session and Database Check
include('session.php'); // Protects the page, ensures $login_session exists
include('config.php');
include('functions.php'); // 🔒 LOAD SECURITY ARCHIVE LOG HELPER

ini_set('display_errors', 1);
error_reporting(E_ALL);

$user_check = mysqli_real_escape_string($db, $login_session);

$exam_submitted = false;
$access_blocked = false;
$block_reason = "";

// AUTOMATED PATH SCANNER: Pull available course text files
$course_directory = "doc/sdq_exams/";
$text_files = glob($course_directory . "*.txt");

$selected_course = isset($_GET['courses']) ? mysqli_real_escape_string($db, $_GET['courses']) : '';

if (empty($selected_course) && !empty($text_files)) {
    $selected_course = basename($text_files[0], ".txt");
}

$parsed_questions = [];
$target_file_path = $course_directory . $selected_course . ".txt";

if (!empty($selected_course) && file_exists($target_file_path)) {
    $raw_content = file_get_contents($target_file_path);
    $question_blocks = explode("===", $raw_content);
    
    $q_index = 1;
    foreach ($question_blocks as $block) {
        $block = trim($block);
        if (empty($block)) continue;
        
        $lines = explode("\n", $block);
        $q_data = ['question_number' => $q_index];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            if (strpos($line, "QUESTION:") === 0) $q_data['question'] = substr($line, 9);
            elseif (strpos($line, "A:") === 0) $q_data['a'] = substr($line, 2);
            elseif (strpos($line, "B:") === 0) $q_data['b'] = substr($line, 2);
            elseif (strpos($line, "C:") === 0) $q_data['c'] = substr($line, 2);
            elseif (strpos($line, "D:") === 0) $q_data['d'] = substr($line, 2);
            elseif (strpos($line, "ANSWER:") === 0) $q_data['answer'] = trim(substr($line, 7));
        }
        
        if (isset($q_data['question'])) {
            $parsed_questions[$q_index] = $q_data;
            $q_index++;
        }
    }
}

// 3. RETAKE & SECURITY VERIFICATION POLICY
if (!empty($selected_course)) {
    $check_grade_query = mysqli_query($db, "SELECT Grade FROM gradebook WHERE username = '$user_check' AND courses = '$selected_course' LIMIT 1");
    
    if (mysqli_num_rows($check_grade_query) > 0) {
        $grade_row = mysqli_fetch_assoc($check_grade_query);
        $current_grade = $grade_row['Grade'];

        if ($current_grade >= 80.00) {
            $access_blocked = true;
            $block_reason = "CRITICAL LOCKOUT: EFFICIENCY RATING SATISFACTORY (" . $current_grade . "%). RETAKE PARAMETERS DENIED.";
        } else {
            // 🔒 SECURITY UPDATE: Adjusted from COUNT(*) to check our new single-row layout layout rules
            $check_attempts_query = mysqli_query($db, "SELECT COUNT(*) as total_answers FROM scores WHERE username = '$user_check' AND course_id = '$selected_course'");
            $attempt_row = mysqli_fetch_assoc($check_attempts_query);
            
            if ($attempt_row['total_answers'] > 0) {
                $access_blocked = true;
                $block_reason = "CRITICAL LOCKOUT: MAXIMUM ALLOWED ACADEMY REMEDIATION ATTEMPTS EXHAUSTED FOR THIS SECTOR.";
            }
        }
    }
}

// 4. Handle Form Submission (GRADED MANIFEST STRINGS FLIGHT PATH)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_exam']) && !$access_blocked) {
    $submitted_answers = isset($_POST['answers']) ? $_POST['answers'] : [];
    $total_questions = count($parsed_questions);
    $correct_count = 0;

    // Arrays to collect data columns in memory
    $compiled_q_nums = [];
    $compiled_u_ans  = [];

    // 📡 THE CORE UPGRADE: Loop directly over the posted answers array from the browser!
    if (!empty($submitted_answers) && is_array($submitted_answers)) {
        
        foreach ($submitted_answers as $q_num => $user_ans) {
            $q_num = intval($q_num);
            $user_ans = trim($user_ans);
            if (empty($user_ans)) { $user_ans = "X"; } // Handle skips safely

            // Compile everything into memory arrays
            $compiled_q_nums[] = $q_num;
            $compiled_u_ans[]  = strtoupper($user_ans);

            // Grade the item against the loaded text file definitions
            if (isset($parsed_questions[$q_num]) && strtoupper($user_ans) === strtoupper($parsed_questions[$q_num]['answer'])) {
                $correct_count++;
            }
        }

        // Convert tracking arrays into clean hyphen-separated single strings
        $final_question_string = implode("-", $compiled_q_nums); // "1-2-3-4-5-6-7-8-9-10"
        $final_answer_string   = implode("-", $compiled_u_ans);   // "A-B-C-D-A-D-B-C-D-A"

        // Recalculate score metrics based on text file limits
        if ($total_questions > 0) {
            $final_grade = ($correct_count / $total_questions) * 100;
            $final_grade = round($final_grade, 2);
        } else {
            $final_grade = 0.00;
        }

        // 🛠️ COMPRESSION OVERWRITE INSERTION: Commit everything as ONE single row item
        $stmt_score = $db->prepare("REPLACE INTO `scores` (`username`, `course_id`, `question_number`, `user_answer`) VALUES (?, ?, ?, ?)");
        $stmt_score->bind_param("ssss", $user_check, $selected_course, $final_question_string, $final_answer_string);
        $stmt_score->execute();
        $stmt_score->close();

        // Push final grade data to the core gradebook matrix grid
        $stmt_grade = $db->prepare("REPLACE INTO `gradebook` (`username`, `courses`, `Grade`) VALUES (?, ?, ?)");
        $stmt_grade->bind_param("ssd", $user_check, $selected_course, $final_grade);
        
        if ($stmt_grade->execute()) {
            $exam_submitted = true;

            // 🔒 SECURITY ARCHIVE LOGGING
            $log_summary = "Completed Academy Exam Course [".$selected_course."]. Score achieved: [".$final_grade."%]. Data payload condensed to single-row matrix structure.";
            record_security_log($db, $login_session, 'INSERT', 'ACADEMY_EXAMS', $selected_course, $log_summary);
        }
        $stmt_grade->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS Academy Testing Array</title>
    <style>
        :root {
            --lcars-purple: #9966cc; --lcars-orange: #ff9900;
            --lcars-pink: #cc6699; --lcars-blue: #33ccff;
            --lcars-bg: #000000; --lcars-green: #33cc33;
        }
        body {
            background-color: var(--lcars-bg); color: #ffffff;
            font-family: Arial, sans-serif; margin: 0; padding: 15px;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .lcars-header { display: flex; align-items: flex-end; margin-bottom: 15px; }
        .lcars-bar-top { background-color: var(--lcars-pink); height: 40px; flex-grow: 1; border-bottom-left-radius: 20px; margin-right: 15px; position: relative; }
        .lcars-bar-top::before { content: "SYS-TEST-99"; position: absolute; left: 25px; bottom: 3px; color: #000000; font-weight: bold; font-size: 14px; }
        .lcars-title { color: var(--lcars-pink); font-size: 28px; font-weight: 300; margin: 0; white-space: nowrap; }
        .lcars-container { display: flex; min-height: 80vh; }
        .lcars-left-bracket { width: 150px; display: flex; flex-direction: column; margin-right: 20px; }
        .lcars-elbow { background-color: var(--lcars-pink); height: 60px; border-top-left-radius: 20px; border-bottom-left-radius: 20px; margin-bottom: 15px; position: relative; }
        .lcars-elbow::after { content: ""; position: absolute; background-color: var(--lcars-bg); width: 110px; height: 35px; bottom: 0; right: 0; border-top-left-radius: 15px; }
        .lcars-btn { background-color: var(--lcars-orange); color: #000000; padding: 10px; text-decoration: none; font-weight: bold; font-size: 13px; text-align: right; margin-bottom: 5px; border-radius: 5px 0 0 5px; }
        .lcars-btn.btn-back { background-color: var(--lcars-blue); }
        .lcars-main-panel { flex-grow: 1; display: flex; flex-direction: column; }
        .lcars-dropdown-container { margin-bottom: 25px; background: #111116; padding: 15px; border-left: 6px solid var(--lcars-purple); border-radius: 0 10px 10px 0; }
        .lcars-select { background-color: #000000; color: var(--lcars-orange); border: 2px solid var(--lcars-purple); padding: 10px; font-size: 16px; text-transform: uppercase; border-radius: 5px; width: 100%; max-width: 400px; cursor: pointer; }
        .question-block { background-color: #111116; border-left: 6px solid var(--lcars-blue); padding: 20px; margin-bottom: 20px; border-radius: 0 10px 10px 0; }
        .question-text { color: var(--lcars-orange); font-size: 18px; margin-bottom: 15px; }
        .option-label { display: block; margin: 10px 0; font-size: 14px; cursor: pointer; text-transform: none; color: #dddddd; }
        .option-label input { margin-right: 10px; accent-color: var(--lcars-orange); }
        .engage-btn { background-color: var(--lcars-pink); color: #000000; border: none; padding: 15px 30px; font-size: 18px; font-weight: bold; cursor: pointer; border-radius: 10px; letter-spacing: 2px; width: 100%; }
        .lcars-confirmation-panel { background-color: #111116; border-left: 8px solid var(--lcars-green); padding: 40px; border-radius: 0 15px 15px 0; margin-top: 20px; }
        .lcars-confirmation-header { color: var(--lcars-green); font-size: 24px; font-weight: bold; margin-bottom: 20px; border-bottom: 2px solid var(--lcars-green); padding-bottom: 10px; }
        .lcars-confirmation-text { color: #ffffff; font-size: 16px; line-height: 1.6; text-transform: none; margin-bottom: 30px; }
        .lcars-lockout-box { background-color: #331111; border-left: 8px solid var(--lcars-pink); color: #ff5555; padding: 30px; border-radius: 0 10px 10px 0; font-weight: bold; margin-top: 15px; }
    </style>
</head>
<body>
    <header class="lcars-header"><div class="lcars-bar-top"></div><h2 class="lcars-title">ACADEMY TESTING MATRIX</h2></header>
    <div class="lcars-container">
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <a href="welcome.php" class="lcars-btn btn-back">MAIN TERM</a>
            <a href="transcript.php" class="lcars-btn" style="background-color: var(--lcars-purple);">TRANSCRIPT</a>
        </nav>
        <main class="lcars-main-panel">
            <?php if ($exam_submitted == true) { ?>
                <div class="lcars-confirmation-panel">
                    <div class="lcars-confirmation-header">DATA TRANSMISSION SUCCESSFUL</div>
                    <div class="lcars-confirmation-text">
                        The examination matrix for <strong><?php echo htmlspecialchars($selected_course); ?></strong> has been sealed and compiled.<br><br>
                        All evaluation answers have been logged into telemetry. <strong>An email containing your full test results will be dispatched shortly.</strong>
                    </div>
                    <a href="welcome.php" class="engage-btn" style="display: inline-block; text-align: center; width: auto; text-decoration: none;">RETURN TO CENTRAL MANIFEST</a>
                </div>
            <?php } else { ?>
                <div class="lcars-dropdown-container">
                    <label for="courses-select" style="color: var(--lcars-purple); display: block; margin-bottom: 8px; font-weight: bold;">SELECT ASSIGNMENT COURSE:</label>
                    <select id="courses-select" class="lcars-select" onchange="location = '?courses=' + encodeURIComponent(this.value);">
                        <?php 
                        if (!empty($text_files)) {
                            foreach ($text_files as $file) {
                                $c_name = basename($file, ".txt");
                                $selected_attr = ($c_name === $selected_course) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($c_name) . '" ' . $selected_attr . '>' . htmlspecialchars($c_name) . '</option>';
                            }
                        } else { echo '<option value="">NO COURSES FOUND IN SUBDIRECTORY</option>'; }
                        ?>
                    </select>
                </div>
                <h2>COURSE FIELD: <?php echo htmlspecialchars($selected_course); ?></h2>
                <p style="text-transform: none; color: #aaa;">Authorized Terminal User: <?php echo htmlspecialchars($login_session); ?></p>
                <?php if ($access_blocked == true) { ?>
                    <div class="lcars-lockout-box">
                        <div><?php echo $block_reason; ?></div>
                        <p style="text-transform: none; font-weight: normal; color: #ccc; margin-top: 15px;">Command protocols specify exams scoring 80% or better are finalized. Only a single remediation attempt is issued for scores under 80%.</p>
                    </div>
                <?php } else { ?>
                    <form method="POST" action="">
                        <?php 
                        if (!empty($parsed_questions)) {
                            foreach ($parsed_questions as $q_num => $row) {
                                echo '<div class="question-block">';
                                echo '<div class="question-text">' . $q_num . '. ' . htmlspecialchars($row['question']) . '</div>';
                                foreach (['a', 'b', 'c', 'd'] as $letter) {
                                    if (empty($row[$letter])) continue; // Handle true/false or shorter choices dynamically
                                    $upper_letter = strtoupper($letter);
                                    $req_attr = ($letter === 'a') ? 'required' : '';
                                    echo '<label class="option-label">';
                                    echo '<input type="radio" name="answers[' . $q_num . ']" value="' . $upper_letter . '" ' . $req_attr . '>';
                                    echo '<strong>' . $upper_letter . ':</strong> ' . htmlspecialchars($row[$letter]);
                                    echo '</label>';
                                }
                                echo '</div>';
                            }
                            echo '<button type="submit" name="submit_exam" class="engage-btn">ENGAGE GRADING CYCLE</button>';
                        } else { echo "<p style='color: var(--lcars-orange);'>NO VALID TELEMETRY FOUND IN THIS ASSIGNMENT FILE.</p>"; }
                        ?>
                    </form>
                <?php } ?>
            <?php } ?>
        </main>
    </div>
</body>
</html>
