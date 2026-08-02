<?php
// 1. Session Check
include('session.php'); // Protects the page, ensures $login_session exists
include('config.php'); // Kept if needed for header connections elsewhere

ini_set('display_errors', 1);
error_reporting(E_ALL);

$user_check = mysqli_real_escape_string($db, $login_session);

// AUTOMATED PATH SCANNER: Pulls all .txt files matching your subdirectory
$course_directory = "doc/sdq_classes/";
$text_files = glob($course_directory . "*.txt");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS Academy Course Viewer</title>
    <style>
    :root { --lc-p: #96c; --lc-o: #f90; --lc-pk: #c69; --lc-b: #3cf; --lc-bg: #000; --lc-g: #33cc33; }
    body { background: var(--lc-bg); color: #fff; font-family: Arial, sans-serif; margin: 0; padding: 15px; text-transform: uppercase; letter-spacing: 1px; }
    
    .lcars-header { display: flex; align-items: flex-end; margin-bottom: 15px; }
    .lcars-bar-top { background: var(--lc-b); height: 40px; flex-grow: 1; border-bottom-left-radius: 20px; margin-right: 15px; position: relative; }
    .lcars-bar-top::before { content: "SYS-LEARN-102"; position: absolute; left: 25px; bottom: 3px; color: #000; font-weight: bold; font-size: 14px; }
    .lcars-title { color: var(--lc-b); font-size: 28px; font-weight: 300; margin: 0; white-space: nowrap; }

    .lcars-container { display: flex; min-height: 80vh; }
    .lcars-left-bracket { width: 150px; display: flex; flex-direction: column; margin-right: 20px; }
    .lcars-elbow { background: var(--lc-b); height: 60px; border-radius: 20px 0 0 20px; margin-bottom: 15px; position: relative; }
    .lcars-elbow::after { content: ""; position: absolute; background: var(--lc-bg); width: 110px; height: 35px; bottom: 0; right: 0; border-top-left-radius: 15px; }
    
    .lcars-btn { background: var(--lc-o); color: #000; padding: 10px; text-decoration: none; font-weight: bold; font-size: 13px; text-align: right; margin-bottom: 5px; border-radius: 5px 0 0 5px; }
    .lcars-btn.btn-back { background: var(--lc-p); }
    .lcars-main-panel { flex-grow: 1; display: flex; flex-direction: column; }
    
    .lcars-dropdown-container { margin-bottom: 25px; background: #111116; padding: 15px; border-left: 6px solid var(--lc-o); border-radius: 0 10px 10px 0; }
    .lcars-select { background: #000; color: var(--lc-o); border: 2px solid var(--lc-o); padding: 10px; font-size: 16px; text-transform: uppercase; border-radius: 5px; width: 100%; max-width: 400px; cursor: pointer; }

    .terminal-screen { background: #050508; border-left: 6px solid var(--lc-p); border-radius: 0 10px 10px 0; padding: 25px; height: 350px; overflow-y: auto; font-family: 'Courier New', Courier, monospace; font-size: 16px; line-height: 1.6; color: #55ff55; text-transform: none; margin-bottom: 25px; white-space: pre-wrap; scroll-behavior: smooth; }

    .engage-btn { background: var(--lc-pk); color: #000; border: none; padding: 15px 30px; font-size: 18px; font-weight: bold; cursor: pointer; border-radius: 10px; letter-spacing: 2px; width: 100%; text-align: center; text-decoration: none; display: inline-block; }
    .engage-btn:disabled { background: #333; color: #666; cursor: not-allowed; }

    .prompt-box { background: #111116; border-left: 6px solid var(--lc-g); padding: 20px; border-radius: 0 10px 10px 0; margin-top: 20px; display: none; }
    .btn-group { display: flex; gap: 15px; margin-top: 15px; }
</style>
</head>
<body>
    <header class="lcars-header">
        <div class="lcars-bar-top"></div>
        <h2 class="lcars-title">CURRICULUM TERMINAL ARRAY</h2>
    </header>

    <div class="lcars-container">
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <a href="welcome.php" class="lcars-btn btn-back">MAIN TERM</a>
            <a href="transcript.php" class="lcars-btn">TRANSCRIPT</a>
        </nav>

        <main class="lcars-main-panel">
            <div class="lcars-dropdown-container">
                <label for="course-select" style="color: var(--lcars-orange); display: block; margin-bottom: 8px; font-weight: bold;">CHOOSE FIELD OBJECTIVE:</label>
                <select id="course-select" class="lcars-select" onchange="enableStartButton()">
                    <option value="" selected disabled>-- DEPLOY ACADEMY DATASTREAM --</option>
                    <?php 
                    if (!empty($text_files)) {
                        foreach ($text_files as $file) {
                            $course_name = basename($file, ".txt");
                            echo '<option value="' . htmlspecialchars($course_name) . '">' . htmlspecialchars($course_name) . '</option>';
                        }
                    } else {
                        echo '<option value="" disabled>NO LECTURE TEXT FILES DETECTED</option>';
                    }
                    ?>
                </select>
            </div>

            <div id="terminal" class="terminal-screen">SYSTEM STANDBY // SELECT COURSE MATRIX TO LOAD TELEMETRY...</div>

            <div class="btn-group" style="margin-top: 0; margin-bottom: 15px;">
                <button id="start-btn" class="engage-btn" disabled onclick="startCourseLoad()">ENGAGE COURSE STREAM</button>
                <button id="pause-btn" class="engage-btn" style="background-color: var(--lcars-orange); display: none;" onclick="togglePause()">PAUSE MATRIX STREAM</button>
            </div>

            <div id="remediation-prompt" class="prompt-box">
                <h3 style="color: var(--lcars-green); margin-top: 0;">COURSE READING COMPLETED. WOULD YOU LIKE TO STUDY REVIEWS BEFORE INITIAL EVALUATION?</h3>
                <div class="btn-group">
                    <button class="engage-btn" style="background-color: var(--lcars-blue);" onclick="studyChoice(true)">YES // INITIATE STUDY STAGE</button>
                    <button class="engage-btn" style="background-color: var(--lcars-orange);" onclick="studyChoice(false)">NO // PROCEED TO TESTING</button>
                </div>
            </div>

            <a id="test-redirect-btn" href="#" class="engage-btn" style="background-color: var(--lcars-green); margin-top: 20px; display: none;">ENGAGE TESTING ARRAY TERMINAL</a>
        </main>
    </div>

            <script>
        let currentText = "";
        let index = 0;
        let isPaused = false;
        let typewriterTimeout = null;
        const speed = 30; 

        function enableStartButton() {
            document.getElementById('start-btn').disabled = false;
        }

        function startCourseLoad() {
            let selectedCourse = document.getElementById('course-select').value;
            const terminal = document.getElementById('terminal');
            
            // Clean up any old running typewriter instances immediately
            clearTimeout(typewriterTimeout);
            
            terminal.innerHTML = "ESTABLISHING MAIN SUBSPACE BRIDGE LINK...";
            document.getElementById('remediation-prompt').style.display = 'none';
            document.getElementById('test-redirect-btn').style.display = 'none';
            document.getElementById('start-btn').disabled = true;
            
            isPaused = false;
            const pauseBtn = document.getElementById('pause-btn');
            pauseBtn.innerHTML = "PAUSE MATRIX STREAM";
            pauseBtn.style.backgroundColor = "var(--lc-o)"; // Matching your condensed variable names
            pauseBtn.style.display = 'none';

            // 🔒 PATH TRAVERSAL FIX: Strip dots, slashes, and spaces
            selectedCourse = selectedCourse.replace(/[^a-zA-Z0-9_\-]/g, '');

            if (!selectedCourse) {
                terminal.innerHTML = "CRITICAL MAINFRAME REJECTION: \nINVALID SECTOR PROTOCOL STRINGS DETECTED.";
                document.getElementById('start-btn').disabled = false;
                return;
            }

            fetch('doc/sdq_classes/' + selectedCourse + '.txt')
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Target file not located in server path.");
                    }
                    return response.text();
                })
                .then(text => {
                    terminal.innerHTML = "";
                    currentText = text;
                    index = 0;
                    pauseBtn.style.display = 'inline-block'; // Displays the pause button when content successfully loads
                    typeWriter();
                })
                .catch(error => {
                    terminal.innerHTML = "CRITICAL MAINFRAME REJECTION: \n" + error.message;
                    document.getElementById('start-btn').disabled = false;
                });
        }

        function typeWriter() {
            // If the user paused the stream, exit the loop cleanly without printing characters
            if (isPaused) return; 

            if (index < currentText.length) {
                const terminal = document.getElementById('terminal');
                terminal.innerHTML += currentText.charAt(index);
                index++;
                terminal.scrollTop = terminal.scrollHeight;
                
                // Continue running the typewriter loop
                typewriterTimeout = setTimeout(typeWriter, speed);
            } else {
                // Course stream completed successfully
                document.getElementById('pause-btn').style.display = 'none';
                document.getElementById('remediation-prompt').style.display = 'block';
            }
        }

        function togglePause() {
            const pauseBtn = document.getElementById('pause-btn');
            
            if (!isPaused) {
                isPaused = true;
                clearTimeout(typewriterTimeout); // Instantly freeze the active countdown loop
                pauseBtn.innerHTML = "RESUME MATRIX STREAM";
                pauseBtn.style.backgroundColor = "var(--lc-g)"; // Uses the green theme for resume state
            } else {
                isPaused = false;
                pauseBtn.innerHTML = "PAUSE MATRIX STREAM";
                pauseBtn.style.backgroundColor = "var(--lc-o)"; // Reverts to orange theme for pause state
                typeWriter(); // Safely restart the loop from the current text index position
            }
        }

        function studyChoice(wantStudy) {
            let courseValue = document.getElementById('course-select').value;
            
            // Apply the same path filtering verification before transferring data targets
            courseValue = courseValue.replace(/[^a-zA-Z0-9_\-]/g, '');
            
            const testUrl = "exams_web.php?courses=" + encodeURIComponent(courseValue);
            
            if (wantStudy) {
                const testBtn = document.getElementById('test-redirect-btn');
                testBtn.href = testUrl;
                testBtn.style.display = 'inline-block';
            } else {
                window.location.href = testUrl;
            }
        }
    </script>
</body>
</html>
