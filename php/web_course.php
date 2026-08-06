<?php
include('session.php'); 
include('config.php'); 

// 1. Keep your server safe from leaking path files
ini_set('display_errors', 0); 
error_reporting(E_ALL);

// 2. Sanitize the session variable just in case it's used elsewhere on the page
$user_check = mysqli_real_escape_string($db, $login_session);

$course_directory = "doc/sdq_classes/";
$text_files = glob($course_directory . "*.txt");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS Academy Course Viewer</title>
    <style>
        :root {--lcars-purple:#9966cc;--lcars-orange:#ff9900;--lcars-pink:#cc6699;--lcars-blue:#33ccff;--lcars-bg:#000;--lcars-green:#33cc33;}
        body {background-color:var(--lcars-bg);color:#fff;font-family:Arial,sans-serif;margin:0;padding:15px;text-transform:uppercase;letter-spacing:1px;}
        .lcars-header{display:flex;align-items:flex-end;margin-bottom:15px;}
        .lcars-bar-top{background-color:var(--lcars-blue);height:40px;flex-grow:1;border-bottom-left-radius:20px;margin-right:15px;position:relative;}
        .lcars-bar-top::before{content:"SYS-LEARN-102";position:absolute;left:25px;bottom:3px;color:#000;font-weight:bold;font-size:14px;}
        .lcars-title{color:var(--lcars-blue);font-size:28px;font-weight:300;margin:0;white-space:nowrap;}
        .lcars-container{display:flex;min-height:80vh;}
        .lcars-left-bracket{width:150px;display:flex;flex-direction:column;margin-right:20px;}
        .lcars-elbow{background-color:var(--lcars-blue);height:60px;border-top-left-radius:20px;border-bottom-left-radius:20px;margin-bottom:15px;position:relative;}
        .lcars-elbow::after{content:"";position:absolute;background-color:var(--lcars-bg);width:110px;height:35px;bottom:0;right:0;border-top-left-radius:15px;}
        .lcars-btn{background-color:var(--lcars-orange);color:#000;padding:10px;text-decoration:none;font-weight:bold;font-size:13px;text-align:right;margin-bottom:5px;border-radius:5px 0 0 5px;}
        .lcars-btn.btn-back{background-color:var(--lcars-purple);}
        .lcars-main-panel{flex-grow:1;display:flex;flex-direction:column;}
        .lcars-dropdown-container{margin-bottom:25px;background:#111116;padding:15px;border-left:6px solid var(--lcars-orange);border-radius:0 10px 10px 0;}
        .lcars-select{background-color:#000;color:var(--lcars-orange);border:2px solid var(--lcars-orange);padding:10px;font-size:16px;text-transform:uppercase;border-radius:5px;width:100%;max-width:400px;cursor:pointer;}
        .terminal-screen{background-color:#050508;border-left:6px solid var(--lcars-purple);border-radius:0 10px 10px 0;padding:25px;height:350px;overflow-y:auto;font-family:'Courier New',monospace;font-size:16px;line-height:1.6;color:#55ff55;text-transform:none;margin-bottom:25px;scroll-behavior:smooth;}
        .engage-btn{background-color:var(--lcars-pink);color:#000;border:none;padding:15px 30px;font-size:18px;font-weight:bold;cursor:pointer;border-radius:10px;letter-spacing:2px;width:100%;text-align:center;text-decoration:none;display:inline-block;}
        .engage-btn:disabled{background-color:#333;color:#666;cursor:not-allowed;}
        .prompt-box{background-color:#111116;border-left:6px solid var(--lcars-green);padding:20px;border-radius:0 10px 10px 0;margin-top:20px;display:none;}
        .btn-group{display:flex;gap:15px;margin-top:0;margin-bottom:15px;}
        .course-media-frame{margin:15px 0;text-align:center;background:#000;border:2px solid var(--lcars-blue);border-radius:8px;padding:10px;box-shadow:0 0 15px rgba(51,204,255,0.2);}
        .course-img{max-width:100%;max-height:250px;border-radius:4px;object-fit:contain;}
        .course-text-line{display:block;margin-bottom:12px;color:#55ff55;font-family:'Courier New',monospace;font-size:16px;}
    </style>
</head>
<body>
    <header class="lcars-header"><div class="lcars-bar-top"></div><h2 class="lcars-title">CURRICULUM TERMINAL ARRAY</h2></header>
    <div class="lcars-container">
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <a href="welcome.php" class="lcars-btn btn-back">MAIN TERM</a>
            <a href="transcript.php" class="lcars-btn">TRANSCRIPT</a>
        </nav>
        <main class="lcars-main-panel">
            <div class="lcars-dropdown-container">
                <label for="course-select" style="color:var(--lcars-orange);display:block;margin-bottom:8px;font-weight:bold;">CHOOSE FIELD OBJECTIVE:</label>
                <select id="course-select" class="lcars-select" onchange="enableStartButton()">
                    <option value="" selected disabled>-- DEPLOY ACADEMY DATASTREAM --</option>
                    <?php 
                    if (!empty($text_files)) {
                        foreach ($text_files as $file) {
                            $course_name = basename($file, ".txt");
                            echo '<option value="'.htmlspecialchars($course_name).'">'.htmlspecialchars($course_name).'</option>';
                        }
                    } else {echo '<option value="" disabled>NO LECTURE TEXT FILES DETECTED</option>';}
                    ?>
                </select>
            </div>
            <div id="terminal" class="terminal-screen">SYSTEM STANDBY // SELECT COURSE MATRIX TO LOAD TELEMETRY...</div>
            <div class="btn-group">
                <button id="start-btn" class="engage-btn" disabled onclick="startCourseLoad()">ENGAGE COURSE STREAM</button>
                <button id="pause-btn" class="engage-btn" style="background-color:var(--lcars-orange);display:none;" onclick="togglePause()">PAUSE MATRIX STREAM</button>
            </div>
            <div id="remediation-prompt" class="prompt-box">
                <h3 style="color:var(--lcars-green);margin-top:0;">COURSE READING COMPLETED. WOULD YOU LIKE TO STUDY REVIEWS BEFORE INITIAL EVALUATION?</h3>
                <div class="btn-group">
                    <button class="engage-btn" style="background-color:var(--lcars-blue);" onclick="studyChoice(true)">YES // INITIATE STUDY STAGE</button>
                    <button class="engage-btn" style="background-color:var(--lcars-orange);" onclick="studyChoice(false)">NO // PROCEED TO TESTING</button>
                </div>
            </div>
            <a id="test-redirect-btn" href="#" class="engage-btn" style="background-color:var(--lcars-green);margin-top:20px;display:none;">ENGAGE TESTING ARRAY TERMINAL</a>
        </main>
    </div>
        <script>
            const currentAcademyUser = "<?php echo htmlspecialchars($login_session); ?>";
        let courseLines=[],lineIndex=0,isPaused=false,streamInterval=null;const lineDelay=3500;
        
        function enableStartButton(){
            document.getElementById('start-btn').disabled=false;
        }
        
        function startCourseLoad(){
            const selectedCourse=document.getElementById('course-select').value;
            const terminal=document.getElementById('terminal');
            clearTimeout(streamInterval);
            terminal.innerHTML="ESTABLISHING MAIN SUBSPACE BRIDGE LINK...";
            document.getElementById('remediation-prompt').style.display='none';
            document.getElementById('test-redirect-btn').style.display='none';
            document.getElementById('start-btn').disabled=true;
            isPaused=false;
            const pauseBtn=document.getElementById('pause-btn');
            pauseBtn.innerHTML="PAUSE MATRIX STREAM";
            pauseBtn.style.backgroundColor="var(--lcars-orange)";
            pauseBtn.style.display='none';
            
            fetch('doc/sdq_classes/' + selectedCourse + '.txt', { cache: 'no-store' })
    .then(r => { if (!r.ok) throw new Error("Target file not located."); return r.text(); })
    .then(t => {
        terminal.innerHTML = "";
        courseLines = t.split(/\r?\n/).map(l => l.trim()).filter(l => l.length > 0);
        lineIndex = 0;
        pauseBtn.style.display = 'inline-block';
        streamCourseLines();
    })
    .catch(e => {
        terminal.innerHTML = "CRITICAL MAINFRAME REJECTION: \n" + e.message;
        document.getElementById('start-btn').disabled = false;
    });
        }
        
        function streamCourseLines(){
    if(isPaused)return;
    const terminal=document.getElementById('terminal');
    
    if(lineIndex < courseLines.length){
        let currentLine = courseLines[lineIndex];
        
        // 1. DYNAMIC MATCHING: Replace the {USER} placeholder token with the live session name
        if (currentLine.includes('{USER}')) {
            currentLine = currentLine.replace(/{USER}/g, currentAcademyUser);
        }
        
        let match = currentLine.match(/<([^>]+)>/);
        
        if(match){
            let bracketContent = match[1].trim();
            let linkParts = bracketContent.split('|');
            let mediaUrl = linkParts[0].trim();
            let fileExtension = mediaUrl.split('.').pop().toLowerCase();
            
            if(['png','jpg','jpeg','gif','webp'].includes(fileExtension)){
                terminal.innerHTML += `<div class="course-media-frame"><img src="${mediaUrl}" alt="Academy Visual Matrix" class="course-img" /></div>`;
            }else if(['mp3','wav','ogg'].includes(fileExtension)){
                let lineAudio = new Audio(mediaUrl);
                lineAudio.play().catch(e => console.log("Audio stream blocked:", e));
            }else {
                // Incorporates the custom link display name fix from earlier!
                let linkDisplayName = linkParts[1] ? linkParts[1].trim() : "[EXTERNAL SYSTEM DATASTREAM]";
                terminal.innerHTML += `<span class="course-text-line"><a href="${mediaUrl}" target="_blank" style="color:var(--lcars-blue);text-decoration:underline;">${linkDisplayName}</a></span>`;
            }
        } else {
            // Standard text output line with user tokens securely parsed
            terminal.innerHTML += `<span class="course-text-line">${currentLine}</span>`;
        }
        
        lineIndex++;
        terminal.scrollTop = terminal.scrollHeight;
        streamInterval = setTimeout(streamCourseLines, lineDelay);
    } else {
        document.getElementById('pause-btn').style.display = 'none';
        document.getElementById('remediation-prompt').style.display = 'block';
    }
}
        
        function togglePause(){
            const pb=document.getElementById('pause-btn');
            if(!isPaused){
                isPaused=true;
                clearTimeout(streamInterval);
                pb.innerHTML="RESUME MATRIX STREAM";
                pb.style.backgroundColor="var(--lcars-green)";
            }else{
                isPaused=false;
                pb.innerHTML="PAUSE MATRIX STREAM";
                pb.style.backgroundColor="var(--lcars-orange)";
                streamCourseLines();
            }
        }
        
        function studyChoice(s){
            const cv=document.getElementById('course-select').value;
            const url="exams_web.php?courses="+encodeURIComponent(cv);
            if(s){
                const btn=document.getElementById('test-redirect-btn');
                btn.href=url;
                btn.style.display='inline-block';
            }else{
                window.location.href=url;
            }
        }
    </script>
    <!-- Open Source Matrix Footer Verification -->
    <?php include('footer.php'); ?>
</body>
</html>
