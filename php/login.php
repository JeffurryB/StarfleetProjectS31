<-- ®Starfield Constructions & Jeffery Biedermann -->
<?php
// 1. Error reporting to monitor system execution
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("config.php");
include_once("functions.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure file queries link seamlessly with your config's connection variable
if (isset($db) && !isset($conn)) {
    $conn = $db;
}

$error = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 🔒 PARAMETERIZED FIX: Accept variables purely as raw string data inputs
    $myusername = isset($_POST['username']) ? trim($_POST['username']) : '';
    $mypassword = isset($_POST['password']) ? $_POST['password'] : ''; 

    if ($myusername !== '' && $mypassword !== '') {
        // 🔒 PARAMETERIZED FIX: Replaced open string concatenation query with a parameterized selector
        $sql = "SELECT ID, UUID, password, active, dh FROM accounts WHERE username = ? LIMIT 1";
        
        if ($stmt_login = $conn->prepare($sql)) {
            $stmt_login->bind_param("s", $myusername);
            $stmt_login->execute();
            $result = $stmt_login->get_result();
            
            if ($result && $result->num_rows === 1) {
                $row = $result->fetch_assoc();
                $stored_signature = $row['password'];
                $is_password_valid = false;
                $requires_migration = false;

                // Check if the stored signature string length matches a standard Bcrypt footprint (60 characters)
                if (strlen($stored_signature) === 60 && strpos($stored_signature, '$2y$') === 0) {
                    // 🔒 KEY STRETCHING PROTOCOL: Verify modern Bcrypt hash signatures safely
                    if (password_verify($mypassword, $stored_signature)) {
                        $is_password_valid = true;
                    }
                } else {
                    // 🔄 LAZY MIGRATION ROUTINE: Evaluate legacy SHA-512 fallback signatures
                    $uuid = $row['UUID'];
                    $pepper = "K@7_mX9!pL2#vQ8!wZ1*rT5&"; 
                    
                    $hashInput = $mypassword . $uuid . $pepper;
                    $legacyCalculatedHash = hash('sha512', $hashInput);

                    if (hash_equals($stored_signature, $legacyCalculatedHash)) {
                        $is_password_valid = true;
                        $requires_migration = true;
                    }
                }

                if ($is_password_valid) {
                    if ((int)$row['active'] === 1) {
                        
                        // 🔒 LIVE CONVERSION EXECUTIVE MATRIX
                        if ($requires_migration) {
                            // Calculate modern secure Bcrypt stretched keys instantly in background memory
                            $modern_secure_hash = password_hash($mypassword, PASSWORD_BCRYPT, ['cost' => 12]);
                            
                            $update_sql = "UPDATE accounts SET password = ? WHERE ID = ?";
                            if ($stmt_mig = $conn->prepare($update_sql)) {
                                $stmt_mig->bind_param("si", $modern_secure_hash, $row['ID']);
                                $stmt_mig->execute();
                                $stmt_mig->close();
                                
                                // File security audit trail record mapping the profile upgrade completion
                                $log_telemetry = "Automated account security upgrade sequence successful. Legacy SHA-512 authentication record migrated to modern parameter arrays.";
                                record_security_log($conn, $myusername, 'UPDATE', 'SECURITY_UPGRADE', $uuid, $log_telemetry);
                            }
                        }

                        // Initialize user session attributes securely
                        $_SESSION['login_user'] = $myusername;
                        $stmt_login->close();

                        // Route based on administrative permission array flags
                        if ((int)$row['dh'] === 1) {
                            header("Location: dhpanel.php");
                        } else {
                            header("Location: welcome.php");
                        }
                        exit();
                        
                    } else {
                        $error = "ERROR: YOUR ACCOUNT IS CURRENTLY INACTIVE.";
                    }
                } else {
                    // 🔒 USER SECURITY FIX: Defend against user enumeration by providing completely uniform fallback error prompts
                    $error = "CRITICAL: INVALID CREDENTIALS MATRIX PARAMETERS.";
                }
            } else {
                $error = "CRITICAL: INVALID CREDENTIALS MATRIX PARAMETERS.";
            }
            $stmt_login->close();
        } else {
            $error = "SYS_ERR: CRITICAL CORES OFFLINE // HANDSHAKE TIMED OUT.";
        }
    } else {
        $error = "ERROR: LOGIN INTERFACE ARGUMENTS CANNOT BE BLANK.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>RPGROUP Terminal Authorization Gateway</title>
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
            padding: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .lcars-login-wrapper {
            width: 100%;
            max-width: 480px;
            padding: 20px;
        }

        .lcars-header { display: flex; align-items: flex-end; margin-bottom: 15px; }
        .lcars-bar-top { background-color: var(--lcars-blue); height: 40px; flex-grow: 1; border-bottom-left-radius: 20px; margin-right: 15px; position: relative; }
        .lcars-bar-top::before { content: "SYS-AUTH-001"; position: absolute; left: 25px; bottom: 3px; color: #000000; font-weight: bold; font-size: 14px; }
        .lcars-title { color: var(--lcars-blue); font-size: 24px; font-weight: 300; margin: 0; white-space: nowrap; }

        .lcars-container { display: flex; }
        .lcars-left-bracket { width: 90px; display: flex; flex-direction: column; margin-right: 15px; }
        .lcars-elbow { background-color: var(--lcars-blue); height: 60px; border-top-left-radius: 20px; border-bottom-left-radius: 20px; margin-bottom: 10px; position: relative; }
        .lcars-elbow::after { content: ""; position: absolute; background-color: var(--lcars-bg); width: 60px; height: 35px; bottom: 0; right: 0; border-top-left-radius: 15px; }
        .lcars-side-block { background-color: var(--lcars-purple); height: 35px; border-radius: 5px 0 0 5px; margin-bottom: 5px; }

        .lcars-main-panel { flex-grow: 1; display: flex; flex-direction: column; }
        
        .lcars-input-container {
            background: #111116;
            padding: 25px;
            border-left: 6px solid var(--lcars-orange);
            border-radius: 0 10px 10px 0;
            margin-bottom: 15px;
        }

        .lcars-field-group { margin-bottom: 20px; }
        .lcars-label { display: block; color: var(--lcars-orange); font-size: 13px; font-weight: bold; margin-bottom: 8px; }
        
        .lcars-input {
            background-color: #000000;
            color: #ffffff;
            border: 2px solid var(--lcars-orange);
            padding: 12px;
            font-size: 16px;
            border-radius: 5px;
            width: 100%;
            box-sizing: border-box;
        }
        .lcars-input:focus { outline: none; border-color: var(--lcars-purple); }

        .engage-btn {
            background-color: var(--lcars-green);
            color: #000000;
            border: none;
            padding: 15px 30px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 10px;
            letter-spacing: 2px;
            width: 100%;
        }
        .engage-btn:hover { background-color: #55ee55; }

        .lcars-error-box {
            background-color: #331111;
            border-left: 6px solid var(--lcars-pink);
            color: #ff5555;
            padding: 20px;
            margin-top: 15px;
            border-radius: 0 10px 10px 0;
            font-size: 13px;
            font-weight: bold;
            line-height: 1.5;
            text-transform: none;
            word-break: break-all;
        }
        .lcars-forgot-btn {
        background: transparent; color: var(--lcars-orange); border: none; font-size: 13px; font-weight: bold; cursor: pointer; letter-spacing: 1px; text-transform: uppercase;
    }
    .lcars-forgot-btn:hover { color: #ffffff; }
    
    /* Overlay Background Mask */
    .lcars-modal-overlay {
        display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.85); align-items: center; justify-content: center;
    }
    
    /* Modal Main Body Core */
    .lcars-modal-box {
        background-color: var(--lcars-bg); border: 2px solid var(--lcars-red); border-radius: 12px; width: 90%; max-width: 500px; padding: 15px; box-shadow: 0 0 25px rgba(204, 51, 51, 0.4);
    }
    .modal-header { display: flex; align-items: flex-end; margin-bottom: 15px; }
    .modal-bar-top { background-color: var(--lcars-red); height: 30px; flex-grow: 1; border-bottom-left-radius: 15px; margin-right: 12px; }
    .modal-title { color: var(--lcars-red); font-size: 20px; margin: 0; white-space: nowrap; font-weight: 300; }
    
    .modal-body-panel { background: #111116; border-left: 6px solid var(--lcars-orange); border-radius: 0 8px 8px 0; padding: 20px; }
    .modal-input-group { margin-bottom: 15px; }
    .modal-label { color: var(--lcars-orange); display: block; margin-bottom: 6px; font-weight: bold; font-size: 13px; }
    
    .modal-input, .modal-select {
        background-color: #000000; color: var(--lcars-blue); border: 2px solid var(--lcars-blue); padding: 10px; font-size: 15px; border-radius: 5px; width: 100%; box-sizing: border-box; text-transform: none;
    }
    .modal-select { text-transform: uppercase; color: var(--lcars-orange); border-color: var(--lcars-orange); cursor: pointer; }
    
    .modal-btn-group { display: flex; gap: 12px; margin-top: 20px; }
    .modal-engage-btn {
        background-color: var(--lcars-green); color: #000000; border: none; padding: 12px; font-size: 15px; font-weight: bold; cursor: pointer; border-radius: 5px; flex-grow: 1; letter-spacing: 1px; text-transform: uppercase;
    }
    .modal-engage-btn.btn-close { background-color: var(--lcars-purple); }
    .modal-status { padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 13px; font-weight: bold; }
    </style>
</head>
<body>
    
    <div class="lcars-login-wrapper">
        <!-- LCARS GROUP LOGO INTEGRATION MATRIX -->
<div style="display: flex; justify-content: center; width: 100%; margin-bottom: 25px;">
    <div class="profile-wrapper" style="width: 220px; border: 3px solid #cc99cc; padding: 8px; border-radius: 10px; background-color: #050505; box-sizing: border-box; display: flex; justify-content: center; align-items: center;">
        <!-- Static path: Replace 'images/your_group_logo.png' with your actual logo file path -->
        <img src="images/logo1.png" alt="Starfleet Group Registry Logo" style="width: 200px; height: 200px; object-fit: cover; border-radius: 4px; border: 1px solid #5599cc;">
    </div>
</div>
        <header class="lcars-header">
            <div class="lcars-bar-top"></div>
            <h2 class="lcars-title">ACCESS PORTAL</h2>
        </header>

        <div class="lcars-container">
            <nav class="lcars-left-bracket">
                <div class="lcars-elbow"></div>
                <div class="lcars-side-block"></div>
                <div class="lcars-side-block" style="background-color: var(--lcars-pink);"></div>
            </nav>

            <main class="lcars-main-panel">
                <form action="" method="post">
                    <div class="lcars-input-container">
                        <div class="lcars-field-group">
                            <label class="lcars-label" for="username-input">USER NAME IDENTIFIER:</label>
                            <input type="text" id="username-input" name="username" class="lcars-input" required autocomplete="username">
                        </div>
                        
                        <div class="lcars-field-group">
                            <label class="lcars-label" for="password-input">SECURE SUB-KEY CODE:</label>
                            <input type="password" id="password-input" name="password" class="lcars-input" required autocomplete="current-password">
                            <!-- LCARS CHECKBOX ROW -->
<div style="display: flex; align-items: center; gap: 10px; margin-top: 10px; padding-left: 5px;">
    <input type="checkbox" id="toggle_password" onclick="togglePasswordVisibility()" style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--lcars-orange, #ff9900);">
    <label for="toggle_password" style="color: var(--lcars-blue, #33ccff); font-size: 14px; font-weight: bold; cursor: pointer; user-select: none;">VIEW PASSWORD PROTOCOL</label>
</div>
                        </div>
                    </div>

                    <button type="submit" class="engage-btn">ENGAGE TERMINAL ENTRY</button>
                </form>
<!-- Forgot Password Matrix Trigger -->
<p style="text-align: center; margin-top: 15px;">
    <button type="button" class="lcars-forgot-btn" onclick="openResetModal()">[ RECOVER COMMAND CREDENTIALS ]</button>
</p>

<!-- LCARS Emergency Override Overlay Modal -->
<div id="reset-modal" class="lcars-modal-overlay">
    <div class="lcars-modal-box">
        <header class="modal-header">
            <div class="modal-bar-top"></div>
            <h3 class="modal-title">CREDENTIAL RECOVERY ARRAY</h3>
        </header>
        
        <div class="modal-body-panel">
            <p style="color: var(--lcars-red); margin-top: 0; font-weight: bold; font-size: 12px;">
                ALERT // TRANSMITTING DIRECT PASSWORD INTERCEPT REQUEST
            </p>
            
            <div id="modal-status-banner" style="display: none;"></div>

            <form id="reset-request-form" onsubmit="submitResetRequest(event)">
                <div class="modal-input-group">
                    <label class="modal-label">Username Identity:</label>
                    <input type="text" id="req-username" class="modal-input" required autocomplete="off">
                </div>
                
                <div class="modal-input-group">
                    <label class="modal-label">Subspace Email Node:</label>
                    <input type="email" id="req-email" class="modal-input" required autocomplete="off">
                </div>
                
                <div class="modal-input-group">
                    <label class="modal-label">Select Department Head (DH):</label>
                    <select id="req-dh" class="modal-select" required>
                        <option value="" selected disabled>-- LOCATE REGIONAL COMMANDER --</option>
                        <?php
                        // Dynamically pull only active Admin accounts (dh = 1) to populate the drop-down list
                        $dh_query = "SELECT id, username FROM accounts WHERE dh = 1 ORDER BY username ASC";
                        $dh_result = mysqli_query($db, $dh_query);
                        if ($dh_result && mysqli_num_rows($dh_result) > 0) {
                            while($dh_row = mysqli_fetch_assoc($dh_result)) {
                                echo '<option value="'.(int)$dh_row['id'].'">'.htmlspecialchars($dh_row['username']).' [DEPT HEAD]</option>';
                            }
                        } else {
                            echo '<option value="" disabled>NO COMMANDING OFFICERS DETECTED</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="modal-btn-group">
                    <button type="submit" id="modal-submit-btn" class="modal-engage-btn">TRANSMIT CODE REQUEST</button>
                    <button type="button" class="modal-engage-btn btn-close" onclick="closeResetModal()">ABORT LINK</button>
                </div>
            </form>
        </div>
    </div>
</div>  
<!--- End Forgot PW button --->
                
                <?php if (!empty($error)): ?>
                    <!-- TERMINAL REJECTION DIAGNOSTIC BOX -->
                    <div class="lcars-error-box">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <script>
function togglePasswordVisibility() {
    var passField = document.getElementById("password-input");
    if (passField.type === "password") {
        passField.type = "text";
    } else {
        passField.type = "password";
    }
}
        function openResetModal() {
    document.getElementById('reset-modal').style.display = 'flex';
    document.getElementById('modal-status-banner').style.display = 'none';
    document.getElementById('reset-request-form').reset();
    document.getElementById('modal-submit-btn').disabled = false;
}

function closeResetModal() {
    document.getElementById('reset-modal').style.display = 'none';
}

function submitResetRequest(event) {
    event.preventDefault();
    
    const user = document.getElementById('req-username').value;
    const email = document.getElementById('req-email').value;
    const dhId = document.getElementById('req-dh').value;
    const submitBtn = document.getElementById('modal-submit-btn');
    const banner = document.getElementById('modal-status-banner');
    
    submitBtn.disabled = true;
    
    // Package parameters into a clean URL payload form data block
    const formData = new URLSearchParams();
    formData.append('username', user);
    formData.append('email', email);
    formData.append('dh_id', dhId);
    
    fetch('send_reset_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            banner.className = "modal-status";
            banner.style.backgroundColor = "rgba(51, 204, 51, 0.15)";
            banner.style.color = "var(--lcars-green)";
            banner.style.borderLeft = "4px solid var(--lcars-green)";
            banner.innerHTML = "TRANSMISSION SUCCESSFUL // DEPT HEAD ALERTED";
            banner.style.display = "block";
            // Auto close modal windows after a brief confirmation break
            setTimeout(closeResetModal, 2500);
        } else {
            throw new Error(data.message || "Subspace transmission drop.");
        }
    })
    .catch(error => {
        banner.className = "modal-status";
        banner.style.backgroundColor = "rgba(204, 51, 51, 0.15)";
        banner.style.color = "var(--lcars-red)";
        banner.style.borderLeft = "4px solid var(--lcars-red)";
        banner.innerHTML = "TRANSMISSION FAILED: " + error.message;
        banner.style.display = "block";
        submitBtn.disabled = false;
    });
}
</script>
</body>
</html>
