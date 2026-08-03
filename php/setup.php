<?php
// 🔒 LOCK EXCLUSIVE MATRIX CHECKPOINT: Prevent unauthorized reruns if config file exists
if (file_exists('group_config.json')) {
    die("Security Lock: System is already configured. Delete 'group_config.json' via FTP to rerun setup.");
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Sanitize text inputs
    $group_name = trim(filter_input(INPUT_POST, 'group_name', FILTER_SANITIZE_SPECIAL_CHARS));
    $group_abbr = strtoupper(trim(filter_input(INPUT_POST, 'group_abbr', FILTER_SANITIZE_SPECIAL_CHARS)));
    
    // Admin Account Inputs (Captured from the form)
    $admin_user = trim(filter_input(INPUT_POST, 'admin_username', FILTER_SANITIZE_SPECIAL_CHARS));
    $admin_email = filter_input(INPUT_POST, 'admin_email', FILTER_VALIDATE_EMAIL);
    $admin_pass = isset($_POST['admin_password']) ? $_POST['admin_password'] : '';
    $admin_gender = isset($_POST['admin_gender']) ? trim($_POST['admin_gender']) : '1'; // Mapping natively to gender codes

    if (empty($group_name) || empty($group_abbr) || empty($admin_user) || ! $admin_email || empty($admin_pass)) {
        $message = "Error: All fields, including complete Admin credentials, are required.";
    } else {
        $upload_ok = true;

        // Process file stream updates securely
        $file_jobs = [
            'group_logo' => ['dir' => 'images/', 'prefix' => 'logo_', 'default' => 'images/logo.png', 'target_var' => 'current_logo'],
            'default_avatar' => ['dir' => 'ProfilePics/', 'prefix' => 'avatar_', 'default' => 'ProfilePics/default.png', 'target_var' => 'current_avatar']
        ];

        // Initialize variables with defaults in case no custom file is sent
        $current_logo = $file_jobs['group_logo']['default'];
        $current_avatar = $file_jobs['default_avatar']['default'];

        foreach ($file_jobs as $form_key => $job) {
            if (!empty($_FILES[$form_key]['tmp_name']) && $_FILES[$form_key]['error'] === UPLOAD_ERR_OK) {
                
                // 🔒 UPLOAD FIX LAYER 1: Extract and strictly validate file extension structure
                $file_ext = strtolower(pathinfo($_FILES[$form_key]['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['png', 'jpg', 'jpeg', 'gif'];
                
                if (!in_array($file_ext, $allowed_extensions, true)) {
                    $message .= "Error: Invalid file extension rejected on " . strtoupper($form_key) . ". ";
                    $upload_ok = false;
                    break;
                }

                // 🔒 UPLOAD FIX LAYER 2: Server-Side MIME-Type Signature Verification (Bypasses polyglot hacks)
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $real_mime = $finfo->file($_FILES[$form_key]['tmp_name']);
                $allowed_mimes = ['image/png', 'image/jpeg', 'image/gif'];
                
                if (!in_array($real_mime, $allowed_mimes, true)) {
                    $message .= "Error: Unrecognized image data signature detected for " . strtoupper($form_key) . ". ";
                    $upload_ok = false;
                    break;
                }

                if (!is_dir($job['dir'])) {
                    mkdir($job['dir'], 0755, true);
                }

                // 🔒 UPLOAD FIX LAYER 3: Completely randomize the filename to prevent unmanaged execution
                $secure_filename = $job['prefix'] . bin2hex(random_bytes(16)) . '.' . $file_ext;
                $target_path = $job['dir'] . $secure_filename;

                if (move_uploaded_file($_FILES[$form_key]['tmp_name'], $target_path)) {
                    $$job['target_var'] = $target_path;
                } else {
                    $message .= "Error uploading image to {$job['dir']}. ";
                    $upload_ok = false;
                    break;
                }
            }
        }

        // 2. Compile Configuration Parameters Securely
        if ($upload_ok) {
            $new_config = [
                'GROUP_NAME' => $group_name,
                'GROUP_ABBR' => $group_abbr,
                'GROUP_LOGO' => $current_logo,
                'DEFAULT_AVATAR' => $current_avatar
            ];

            // 🔒 REFACTOR FIX: Writing strictly to a structural JSON data store eliminates RCE injection risks entirely
            if (file_put_contents('group_config.json', json_encode($new_config, JSON_PRETTY_PRINT))) {
                
                // 3. SECURE ADMIN CREATION ROUTINE
                include_once("config.php");
                $conn = isset($db) ? $db : $conn;

                if ($conn) {
                    // Generate a standard unique UUIDv4 string for your virtual environment indexes
                    $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                        mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
                        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                    );
                    
                    // 🔒 AUTHENTICATION PROTOCOL FIX: Deploying military-grade Bcrypt stretching
                    $secure_bcrypt_hash = password_hash($admin_pass, PASSWORD_BCRYPT, ['cost' => 12]);
                    
                    $admin_flag = 1; // 👑 Level 9 Administrative Division Head Master Flag

                    $sql = "INSERT INTO accounts (username, gender, email, password, UUID, DisplayName, profile_img, dh) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("sssssssi", $admin_user, $admin_gender, $admin_email, $secure_bcrypt_hash, $uuid, $admin_user, $current_avatar, $admin_flag);
                        
                        if ($stmt->execute()) {
                            $message = "SUCCESS: Configuration matrix initialized and Master Admin Account compiled securely! Please delete 'setup.php' from your server root via FTP immediately.";
                        } else {
                            $message = "CONFIG SAVED WITH WARNING: Administrative insertion failed. " . htmlspecialchars($conn->error);
                        }
                        $stmt->close();
                    } else {
                        $message = "CONFIG SAVED WITH WARNING: Database statement validation failed.";
                    }
                } else {
                    $message = "CONFIG SAVED WITH WARNING: Core database handshake unavailable.";
                }
            } else {
                $message = "Error: Failed to write configuration matrix to disk file storage. Check directory node privileges.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Star Trek RP Group Setup Panel</title>
    <style>
        body { background: #0c0f12; color: #e2e8f0; font-family: sans-serif; padding: 40px; }
        .setup-card { max-width: 500px; margin: 0 auto; background: #1a202c; padding: 25px; border-radius: 8px; border: 1px solid #2d3748; }
        h2 { color: #f6ad55; text-align: center; margin-top: 0; }
        h3 { color: #63b3ed; font-size: 16px; margin: 25px 0 10px 0; border-bottom: 1px solid #4a5568; padding-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; }
        input[type="text"], input[type="email"], input[type="password"], input[type="file"], select { width: 100%; padding: 8px; box-sizing: border-box; background: #2d3748; border: 1px solid #4a5568; color: #fff; border-radius: 4px; }
        select { cursor: pointer; }
        button { width: 100%; padding: 12px; background: #3182ce; border: none; color: white; font-weight: bold; cursor: pointer; border-radius: 4px; margin-top: 15px; }
        button:hover { background: #2b6cb0; }
        .alert { padding: 12px; background: #2c5282; border-radius: 4px; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>
    <div class="setup-card">
        <h2>🚀 Starfleet Command Setup</h2>
        <?php if (!empty($message)): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <h3>🛸 Fleet Configuration</h3>
            <div class="form-group">
                <label>Full Group Name:</label>
                <input type="text" name="group_name" placeholder="e.g. Star Trek Horizon Roleplay" required>
            </div>
            <div class="form-group">
                <label>Abbreviated Initials (Caps):</label>
                <input type="text" name="group_abbr" placeholder="e.g. STHR" required>
            </div>
            <div class="form-group">
                <label>Group Logo Image (Saves to images/logo.png):</label>
                <input type="file" name="group_logo" accept="image/*">
            </div>
            <div class="form-group">
                <label>Default Profile Picture (Saves to ProfilePics/default.png):</label>
                <input type="file" name="default_avatar" accept="image/*">
            </div>

            <h3>👑 Master Admin Account Creation</h3>
            <div class="form-group">
                <label>Admin Username:</label>
                <input type="text" name="admin_username" placeholder="e.g. Admin_Kirk" required>
            </div>
            <div class="form-group">
                <label>Admin Email Address:</label>
                <input type="email" name="admin_email" placeholder="e.g. admin@yourfleet.com" required>
            </div>
            <div class="form-group">
                <label>Admin Password:</label>
                <input type="password" name="admin_password" placeholder="••••••••••••" required>
            </div>
            <div class="form-group">
                <label>Admin Gender Identification:</label>
                <select name="admin_gender">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other / Non-Binary</option>
                </select>
            </div>

            <button type="submit">Compile Fleet & Initialize Admin</button>
        </form>
    </div>
</body>
</html>
