<?php
// 1. INTEGRATE SENSITIVE DATA AUTHENTICATION CONTEXTS
include('session.php'); 
include('config.php'); 
include('functions.php');

// Secure production environment variables 
ini_set('display_errors', 0); 
error_reporting(E_ALL);

// 2. ADMINISTRATIVE CLEARANCE AUTHORIZATION LOOKUP
$admin_check_stmt = mysqli_prepare($conn, "SELECT dh FROM accounts WHERE username = ?");
if ($admin_check_stmt) {
    mysqli_stmt_bind_param($admin_check_stmt, "s", $login_session);
    mysqli_stmt_execute($admin_check_stmt);
    $admin_result = mysqli_stmt_get_result($admin_check_stmt);
    
    if ($user_row = mysqli_fetch_array($admin_result, MYSQLI_ASSOC)) {
        if ((int)$user_row['dh'] !== 1) {
            header("Location: notauthrozied.php");
            exit();
        }
    } else {
        header("Location: notauthrozied.php");
        exit();
    }
    mysqli_stmt_close($admin_check_stmt);
} else {
    die("CRITICAL ACCESS CONTROLLER SECTOR FAULT.");
}

// 3. INITIALIZE INTERFACE RESPONSE PACKETS
$show_modal = false;
$modal_status = ""; // success or failure
$modal_title = "";
$modal_body = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'upload_course') {
    // Validate anti-CSRF token seed sequence
    if (!empty($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        
        // Extract and scrub database input elements
        $div_id         = (int)$_POST['div_id'];
        $author_id      = trim($_POST['author_id']);
        $class_name     = trim($_POST['class_name']);
        $class_desc     = trim($_POST['class_desc']);
        $required_score = (int)$_POST['required_score'];
        
        // File validation parameters
        $target_dir = "doc/sdq_classes/";
        $uploaded_file = $_FILES["course_file"];
        
        // Sanitize file name: remove spaces/special characters, enforce lowercase extension
        $clean_file_name = preg_replace("/[^a-zA-Z0-9_\-]/", "", str_replace(' ', '_', $class_name)) . ".txt";
        $target_file_path = $target_dir . $clean_file_name;
        $file_type = strtolower(pathinfo($uploaded_file["name"], PATHINFO_EXTENSION));

        // Security boundary validation checks
        if (empty($class_name) || empty($author_id) || empty($uploaded_file["name"])) {
            $show_modal = true;
            $modal_status = "failure";
            $modal_title = "REGISTRY REJECTION";
            $modal_body = "All diagnostic telemetry arrays must be fully configured. Data matrices cannot contain blank values.";
        } elseif ($file_type !== "txt") {
            $show_modal = true;
            $modal_status = "failure";
            $modal_title = "FILE TYPE MUTATION DETECTED";
            $modal_body = "The mainframe core rejects file stream profiles containing extensions other than <strong>.TXT</strong> arrays.";
        } elseif ($uploaded_file["size"] > 2000000) { // Limit to 2MB
            $show_modal = true;
            $modal_status = "failure";
            $modal_title = "DATAFRAME OVERLOAD";
            $modal_body = "The curriculum text file matrix payload size exceeds the maximum permitted baseline threshold.";
        } else {
            // Upload file to directory sector
            if (move_uploaded_file($uploaded_file["tmp_name"], $target_file_path)) {
                
                // Parameterized SQL Statement updating the specific courses columns
                $insert_query = "INSERT INTO courses (DivID, AutherID, `Class Name`, `Class Description`, `Required Score`) VALUES (?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $insert_query);
                
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "isssi", $div_id, $author_id, $class_name, $class_desc, $required_score);
                    if (mysqli_stmt_execute($stmt)) {
                        
                        // --- RECORD STARFLEET SECURITY LOG INTERFACE ---
                        $log_summary = "Uploaded new academy curriculum telemetry file. Target asset identifier: [".$clean_file_name."].";
                        record_security_log($conn, $login_session, 'INSERT', 'CURRICULUM_DATA', 'FILE_UPLOAD', $log_summary);
                        // --- END SECURITY LOG TRANSACTION ---

                        $show_modal = true;
                        $modal_status = "success";
                        $modal_title = "TRANSMISSION SECURED";
                        $modal_body = "Curriculum matrix <strong>" . htmlspecialchars($class_name) . "</strong> has been compiled successfully. File written cleanly to storage arrays and linked to primary database matrix nodes.";
                    } else {
                        $show_modal = true;
                        $modal_status = "failure";
                        $modal_title = "DATABASE WRITE CONFLICT";
                        $modal_body = "File uploaded safely, but primary SQL tables threw a structural insertion fault. Verify column schemas match entry telemetry.";
                    }
                    mysqli_stmt_close($stmt);
                }
            } else {
                $show_modal = true;
                $modal_status = "failure";
                $modal_title = "SUBSPACE PACKET DROP";
                $modal_body = "The local storage folder rejected file writing authorizations. Ensure target directory directory path structures exist and have write permissions.";
            }
        }
    } else {
        $show_modal = true;
        $modal_status = "failure";
        $modal_title = "FIRMWARE SECURITY CRASH";
        $modal_body = "Active anti-CSRF authentication handshake keys have expired or been altered. Session invalidated.";
    }
}
// --- START SECTOR DELETION OVERRIDE PROTOCOL ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete_course') {
    // Validate anti-CSRF token handshake stability
    if (!empty($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        
        // Securely cast the incoming record integer
        $course_id = (int)$_POST['course_id'];
        
        // 1. Trace the physical file name using ClassID
        $find_stmt = mysqli_prepare($conn, "SELECT `Class Name` FROM courses WHERE ClassID = ?");
        if ($find_stmt) {
            mysqli_stmt_bind_param($find_stmt, "i", $course_id);
            mysqli_stmt_execute($find_stmt);
            $find_res = mysqli_stmt_get_result($find_stmt);
            
            if ($course_row = mysqli_fetch_array($find_res, MYSQLI_ASSOC)) {
                $target_class_name = $course_row['Class Name'];
                
                // Format the file path to exactly match your upload cleanup routine
                $file_to_purge = "doc/sdq_classes/" . preg_replace("/[^a-zA-Z0-9_\-]/", "", str_replace(' ', '_', $target_class_name)) . ".txt";
                
                // 2. Erase the database reference row using ClassID
                $delete_stmt = mysqli_prepare($conn, "DELETE FROM courses WHERE ClassID = ?");
                if ($delete_stmt) {
                    mysqli_stmt_bind_param($delete_stmt, "i", $course_id);
                    
                    if (mysqli_stmt_execute($delete_stmt)) {
                        // 3. Vaporize physical .txt file asset from server storage disk if it exists
                        if (!empty($target_class_name) && file_exists($file_to_purge)) {
                            unlink($file_to_purge);
                        }
                        
                        // --- RECORD SECURITY TRANSACTION AUDIT ---
                        $log_summary = "Permanently deleted curriculum registry data. Erased structural file trace: [".$file_to_purge."].";
                        record_security_log($conn, $login_session, 'DELETE', 'CURRICULUM_DATA', 'FILE_PURGE', $log_summary);
                        // --- END AUDIT ---

                        $show_modal = true;
                        $modal_status = "success";
                        $modal_title = "SECTOR VAPORIZED";
                        $modal_body = "Curriculum array tracking nodes and physical storage text files have been securely dropped from the mainframe.";
                    } else {
                        $show_modal = true;
                        $modal_status = "failure";
                        $modal_title = "PURGE ACCESS DENIED";
                        $modal_body = "Database reference nodes rejected deletion instruction arrays due to an index structural fault.";
                    }
                    mysqli_stmt_close($delete_stmt);
                }
            } else {
                $show_modal = true;
                $modal_status = "failure";
                $modal_title = "TARGET INVISIBLE";
                $modal_body = "The specified course ID parameter does not map to any active telemetry matrix rows.";
            }
            mysqli_stmt_close($find_stmt);
        }
    } else {
        $show_modal = true;
        $modal_status = "failure";
        $modal_title = "FIRMWARE HANDSHAKE DROP";
        $modal_body = "Anti-CSRF session token checks failed. Core modification commands dropped.";
    }
}

// --- END SECTOR DELETION OVERRIDE PROTOCOL ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>LCARS Ingestion Matrix</title>
    <style>
        :root{--lcars-purple:#9966cc;--lcars-orange:#ff9900;--lcars-pink:#cc6699;--lcars-blue:#33ccff;--lcars-dark-blue:#5588ff;--lcars-bg:#000000}
        body{background-color:var(--lcars-bg);color:#fff;font-family:"Arial Custom",Arial,sans-serif;margin:0;padding:15px;text-transform:uppercase;letter-spacing:1px;overflow-x:hidden}
        .lcars-header{display:flex;align-items:flex-end;margin-bottom:15px}
        .lcars-bar-top{background-color:var(--lcars-purple);height:40px;flex-grow:1;border-bottom-left-radius:20px;margin-right:15px;position:relative}
        .lcars-bar-top::before{content:"SD-2026";position:absolute;left:25px;bottom:3px;color:#000;font-weight:bold;font-size:14px}
        .lcars-title{color:var(--lcars-orange);font-size:28px;font-weight:300;margin:0;line-height:1;white-space:nowrap}
        .lcars-container{display:flex;min-height:calc(100vh - 120px)}
        .lcars-left-bracket{width:150px;display:flex;flex-direction:column;margin-right:20px}
        .lcars-elbow{background-color:var(--lcars-purple);height:60px;border-top-left-radius:20px;border-bottom-left-radius:20px;margin-bottom:15px;position:relative}
        .lcars-elbow::after{content:"";position:absolute;background-color:var(--lcars-bg);width:110px;height:35px;bottom:0;right:0;border-top-left-radius:15px}
        .lcars-menu{display:flex;flex-direction:column;gap:8px}
        .lcars-btn{background-color:var(--lcars-orange);color:#000;padding:10px 15px;text-decoration:none;font-weight:bold;font-size:13px;text-align:right;border-radius:5px 0 0 5px;transition:background .2s;border:none;cursor:pointer;font-family:inherit;letter-spacing:inherit}
        .lcars-btn:hover{background-color:#fcc}.btn-blue{background-color:var(--lcars-blue)}.btn-blue:hover{background-color:#88e2ff}.btn-pink{background-color:var(--lcars-pink)}.btn-pink:hover{background-color:#ff99cc}
        .lcars-main-panel{flex-grow:1;display:flex;flex-direction:column}
        .lcars-user-banner{border-bottom:4px solid var(--lcars-blue);padding-bottom:10px;margin-bottom:25px;display:flex;justify-content:space-between;align-items:center}
        .lcars-user-banner h1{margin:0;font-size:22px;color:var(--lcars-blue);font-weight:normal}
        .system-status{font-size:12px;color:var(--lcars-dark-blue)}
        .lcars-admin-section{background-color:#111116;border-left:6px solid var(--lcars-purple);padding:20px;border-radius:0 8px 8px 0;margin-bottom:25px}
        .lcars-admin-section h3{margin:0 0 15px 0;color:var(--lcars-purple);font-size:18px}
        .form-row{display:flex;flex-direction:column;margin-bottom:15px}
        .form-row label{color:var(--lcars-blue);font-size:12px;margin-bottom:5px;font-weight:bold}
        .lcars-input,.lcars-select{background-color:#000;border:2px solid var(--lcars-dark-blue);color:#fff;padding:10px;font-size:14px;border-radius:4px;font-family:inherit;letter-spacing:1px;text-transform:uppercase;width:100%;box-sizing:border-box}
        .lcars-input:focus,.lcars-select:focus{outline:none;border-color:var(--lcars-blue)}
        .lcars-select{appearance:none;background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://w3.org' width='100' height='50'><polygon points='0,0 100,0 50,50' style='fill:%2333ccff;'/></svg>");background-repeat:no-repeat;background-size:12px 6px;background-position:right 15px center;padding-right:30px}
        .form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px}
        .form-actions{display:flex;gap:15px;margin-top:10px}.form-actions .lcars-btn{border-radius:4px;text-align:center;min-width:120px}
        .field-tip{font-size:11px;color:#aaa;text-transform:none;margin-top:5px}
        .lcars-modal-overlay{display:flex;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.9);z-index:9999;align-items:center;justify-content:center;padding:20px;box-sizing:border-box}
        .lcars-modal-box{background:#050505;border:4px solid #cc3333;border-radius:15px;max-width:550px;width:100%;padding:25px;box-sizing:border-box}
        .modal-success{border-color:var(--lcars-blue)}.lcars-modal-header{font-size:24px;font-weight:bold;border-bottom:3px solid #cc3333;padding-bottom:5px;margin-bottom:15px;display:flex;justify-content:space-between}
        .modal-success .lcars-modal-header{color:var(--lcars-blue);border-bottom-color:var(--lcars-blue)}
        .modal-failure .lcars-modal-header{color:#cc3333;border-bottom-color:#cc3333}
        .lcars-modal-body{color:#ddd;font-size:16px;line-height:1.4;text-transform:none;margin-bottom:20px}
        .lcars-modal-body strong{color:#ff9900;text-transform:uppercase}.lcars-modal-actions{display:flex;justify-content:flex-end}
    </style>
</head>
<body>
    <header class="lcars-header"><div class="lcars-bar-top"></div><h2 class="lcars-title">CURRICULUM INGESTION MAIN ARRAY</h2></header>
    <div class="lcars-container">
        <nav class="lcars-left-bracket"><div class="lcars-elbow"></div><div class="lcars-menu"><a href="dhpanel.php" class="lcars-btn">ADMIN</a><a href="courses.php" class="lcars-btn btn-blue">COURSES</a><a href="ship_mgt.php" class="lcars-btn btn-pink">SHIP MGT</a></div></nav>
        <main class="lcars-main-panel">
            <div class="lcars-user-banner"><h1>ACADEMY DATASTREAM INJECTOR TERMINAL</h1><div class="system-status">MAINFRAME STATUS // AUTHORIZATION ACCESS OVERRIDE ACTIVE</div></div>
            <div class="lcars-admin-section">
                <h3>UPLOAD AND INDEX NEW LECTURE LATTICES</h3>
                <form method="POST" action="course_upload.php" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload_course"><input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token']);?>">
                    <div class="form-grid">
                        <div class="form-row"><label>TARGET DIVISION SECTOR (DIVID)</label><select class="lcars-select" name="div_id" required><option value="2">DIVISION // COMMAND</option><option value="5">DIVISION // OPERATIONS</option><option value="6">DIVISION // SCIENCE</option><option value="4">DIVISION // ENGINEERING</option><option value="3">DIVISION // DIPLOMACY</option><option value="7">DIVISION // SECURITY</option><option value="9">DIVISION // MEDICAL</option><option value="13">DIVISION // JAG</option><option value="14">DIVISION // COMMS</option><option value="11">INFORMATION TECHNOLOGY DEPARTMENT</option><option value="10">CIVILIAN</option><option value="16">CONTRIBUTORS</option></select><div class="field-tip">Select academic branch categorization.</div></div>
                        <div class="form-row"><label>COMPILING INSTRUCTOR USERNAME (AUTHERID)</label><input type="text" class="lcars-input" name="author_id" value="<?=htmlspecialchars($login_session);?>" required autocomplete="off"><div class="field-tip">Defaults to active handle.</div></div>
                        <div class="form-row"><label>LECTURE TERMINAL OBJECTIVE CLASS NAME</label><input type="text" class="lcars-input" name="class_name" placeholder="E.G. WARP CORE METRICS 101" required autocomplete="off"><div class="field-tip">Used to establish file names.</div></div>
                        <div class="form-row"><label>MINIMUM COMPLIANCE EVALUATION SCORE (REQUIRED SCORE)</label><input type="number" class="lcars-input" name="required_score" min="1" max="100" placeholder="E.G. 75" required autocomplete="off"><div class="field-tip">Minimum baseline score.</div></div>
                    </div>
                    <div class="form-row" style="margin-top:20px;"><label>BRIEF LECTURE TELEMETRY COURSE DESCRIPTION</label><input type="text" class="lcars-input" name="class_desc" placeholder="OVERVIEW OF TRAINING CONTENT..." autocomplete="off"><div class="field-tip">A summary detailing core telemetry.</div></div>
                    <div class="form-row" style="margin-top:20px;"><label>CURRICULUM LECTURE SOURCE COMPONENT LAYER (.TXT FILE STREAM)</label><input type="file" class="lcars-input" name="course_file" accept=".txt" required style="border-style:dashed;"><div class="field-tip">Strictly limited to <strong>.txt</strong> layouts.</div></div>
                    <div class="form-actions"><button type="submit" class="lcars-btn btn-pink">DEPOSIT MATRIX</button><button type="reset" class="lcars-btn btn-blue">RESET STREAM</button></div>
                </form>
            </div>
            <!-- ADMINISTRATIVE PURGE INTERFACE CONTAINER -->
<div class="lcars-admin-section" style="margin-top: 30px; border-left-color: var(--lcars-pink);">
    <h3>TERMINATE ACTIVE ACADEMY COURSE CORES</h3>
    <div class="field-tip" style="margin-bottom:15px;">Warning: Triggering an engagement pipeline instantly vaporizes database references and server data files.</div>
    
    <?php
    // FIX: Selecting ClassID instead of the missing CourseID column
    $list_res = mysqli_query($conn, "SELECT ClassID, `Class Name`, AutherID FROM courses ORDER BY ClassID DESC");
    if ($list_res && mysqli_num_rows($list_res) > 0):
    ?>
        <div style="display:flex; flex-direction:column; gap:10px;">
            <?php while($c_row = mysqli_fetch_array($list_res, MYSQLI_ASSOC)): ?>
                <div style="display:flex; justify-content:space-between; align-items:center; background:#050508; padding:10px; border-left:4px solid var(--lcars-pink); border-radius:0 4px 4px 0;">
                    <div>
                        <span style="color:var(--lcars-blue); font-weight:bold;">ID: [<?php echo $c_row['ClassID']; ?>]</span> 
                        <span style="color:#fff; margin-left:10px;"><?php echo htmlspecialchars($c_row['Class Name']); ?></span>
                        <div class="field-tip">Author Matrix Key ID: <?php echo htmlspecialchars($c_row['AutherID']); ?></div>
                    </div>
                    
                    <form method="POST" action="" onsubmit="return confirm('CRITICAL RED ALERT // INITIATE PERMANENT SYSTEM PURGE?');" style="margin:0;">
                        <input type="hidden" name="action" value="delete_course">
                        <input type="hidden" name="course_id" value="<?php echo $c_row['ClassID']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <button type="submit" class="lcars-btn" style="background-color:#c33; color:#fff; font-size:11px; padding:6px 12px; border-radius:4px;">PURGE CORE</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div style="color:var(--lcars-dark-blue); font-size:13px;">NO COMPILED CURRICULUM DATASTREAM RECORDS DETECTED IN STORAGE BUFFERS.</div>
    <?php endif; ?>
</div>
        </main>
    </div>
    <?php if($show_modal): ?>
        <div id="lcars-modal" class="lcars-modal-overlay">
            <div class="lcars-modal-box <?=($modal_status==='success')?'modal-success':'modal-failure';?>">
                <div class="lcars-modal-header"><span><?=$modal_title;?></span><span style="font-size:12px;opacity:0.6;align-self:center;">SYS-DIAG-LOG-<?=time()%10000;?></span></div>
                <div class="lcars-modal-body"><?=$modal_body;?></div>
                <div class="lcars-modal-actions"><button class="lcars-btn <?=($modal_status==='success')?'btn-blue':'btn-pink';?>" onclick="document.getElementById('lcars-modal').style.display='none'">DISMISS ALERT</button></div>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>
