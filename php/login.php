<-- ®Starfield Constructions & Jeffery Biedermann -->
<?php
// 1. Error reporting to monitor system execution
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("config.php");
include("functions.php");
session_start();

$error = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Clean username, keep password raw for hashing
    $myusername = mysqli_real_escape_string($db, $_POST['username']);
    $mypassword = $_POST['password']; 

    // Fetch account details - UPDATED to include the 'dh' administration access column token
    $sql = "SELECT ID, UUID, password, active, dh FROM accounts WHERE username = '$myusername' LIMIT 1";
    $result = mysqli_query($db, $sql);
    
    if ($result && mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Rebuild the exact 128-character SHA-512 hash using the account's UUID
        $uuid = $row['UUID'];
        $pepper = "JSDJFH*&#($#EORFW897632hEW*#69"; // MUST match your password update script perfectly!  CHANGE THIS TO SOMETHING ONLY YOU KNOW!
        
        $hashInput = $mypassword . $uuid . $pepper;
        $calculatedHash = hash('sha512', $hashInput);

        // Compare hashes
        if ($calculatedHash === $row['password']) {
            if ((int)$row['active'] === 1) {
                $_SESSION['login_user'] = $myusername;

                // Check administrative permission array flags
                if ((int)$row['dh'] === 1) {
                    // Route Level 10 Administrators straight to the management terminal hub
                    header("Location: dhpanel.php");
                } else {
                    // Route standard officers straight to the default gateway landing layout
                    header("Location: welcome.php");
                }
                exit();
            } else {
                $error = "ERROR: YOUR ACCOUNT IS CURRENTLY INACTIVE.";
            }
        } else {
            // Debugging output formatted neatly for LCARS layout box containers
            $error = "CRITICAL: INVALID CREDENTIALS MATRIX PARAMETERS.<br><br>" .
                     "SUBMITTED INPUT: " . htmlspecialchars($mypassword) . "<br>"; //.
                  //   "GENERATED TEleMETRY HASH: <small><code>" . $calculatedHash . "</code></small><br>" .
                   //  "DATABASE MANIFEST HASH: <small><code>" . $row['password'] . "</code></small>";
        }
    } else {
        $error = "ERROR: INTERFACE PROTOCOLS REJECT USER ENTRY '" . htmlspecialchars($_POST['username']) . "' - NOT FOUND IN MASTER REPOSITORY.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Terminal Authorization Gateway</title>
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
    </style>
</head>
<body>
    
    <div class="lcars-login-wrapper">
        <!-- LCARS GROUP LOGO INTEGRATION MATRIX -->
<div style="display: flex; justify-content: center; width: 100%; margin-bottom: 25px;">
    <div class="profile-wrapper" style="width: 220px; border: 3px solid #cc99cc; padding: 8px; border-radius: 10px; background-color: #050505; box-sizing: border-box; display: flex; justify-content: center; align-items: center;">
        <!-- Static path: Replace 'images/your_group_logo.png' with your actual logo file path -->
        <img src="images/your_group_logo.png" alt="Starfleet Group Registry Logo" style="width: 200px; height: 200px; object-fit: cover; border-radius: 4px; border: 1px solid #5599cc;">
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
</script>
</body>
</html>
