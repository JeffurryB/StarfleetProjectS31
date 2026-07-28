<?php
include("config.php"); // 🗄️ Loads your global database configuration connection

// Ensure the local file script maps cleanly to your config's database variable
if (isset($db) && !isset($conn)) {
    $conn = $db;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch columns from your divisions table using MySQLi
$divisions = [];
$sql_div = "SELECT did, dname FROM divisions ORDER BY dname ASC";
$result_div = $conn->query($sql_div);

if ($result_div && $result_div->num_rows > 0) {
    while ($row = $result_div->fetch_assoc()) {
        $divisions[] = $row;
    }
}
$message = ""; 

// 2. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = isset($_POST['username']) ? trim($_POST['username']) : '';
    $uuid = isset($_POST['uuid']) ? trim($_POST['uuid']) : '';
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $rawPassword = isset($_POST['password']) ? $_POST['password'] : '';
    // FIXED: Correctly extracting 'DivID' from the form post data
    $divisionId = isset($_POST['DivID']) ? trim($_POST['DivID']) : ''; 

    // Explicitly verify no variable is empty before proceeding
    if ($user !== '' && $email !== '' && $rawPassword !== '' && $divisionId !== '') {
        
        // 3. Generate a unique UUID for the new account
      //  $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
       //     mt_rand(0, 0xffff), mt_rand(0, 0xffff),
       //     mt_rand(0, 0xffff),
       //     mt_rand(0, 0x0fff) | 0x4000,
        //    mt_rand(0, 0x3fff) | 0x8000,
        //    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
      //  );

        // 4. Generate the 128-character SHA-512 Hash
        $pepper = "K@7_mX9!pL2#vQ8!wZ1*rT5&"; 
        $hashInput = $rawPassword . $uuid . $pepper;
        $sha512Hash = hash('sha512', $hashInput); 

       // 5. INSERT into accounts table (FIXED: Using MySQLi Prepared Statement and 'DivID' column)
        $sql = "INSERT INTO accounts (username, gender, email, password, UUID, DisplayName, DivID) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            // "ssssssi" means 6 strings and 1 integer (DivID)
            $stmt->bind_param("ssssssi", $user, $gender, $email, $sha512Hash, $uuid, $user, $divisionId);
            
            if ($stmt->execute()) {
                $stmt->close();
                // If you want a message, do not redirect. If you want a redirect, leave this as is:
                header("Location: welcome.php");
                exit();
            } else {
                // Check for MySQL duplicate entry error code (1062)
                if ($conn->errno == 1062) {
                    $message = "<p class='lcars-text-error'>SYS_ERR: Username or Email already exists.</p>";
                } else {
                    $message = "<p class='lcars-text-error'>SYS_ERR: " . htmlspecialchars($conn->error) . "</p>";
                }
            }
            $stmt->close();
        } else {
            $message = "<p class='lcars-text-error'>SYS_ERR: Statement preparation failed.</p>";
        }
    } else {
        $message = "<p class='lcars-text-error'>SYS_ERR: All parameters required.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>[<?php echo GROUP_ABBR; ?>] - Personnel Registration</title>
    <style>
        @import url('https://googleapis.com');
        
        body {
            background-color: #000000;
            color: #ff9900;
            font-family: 'Antonio', sans-serif;
            letter-spacing: 0.05em;
            margin: 20px;
            text-transform: uppercase;
        }
        .lcars-container {
            display: flex;
            max-width: 900px;
            margin: 0 auto;
        }
        .lcars-left-bar {
            width: 150px;
            border-right: 15px solid #cc99cc;
            border-radius: 40px 0 0 40px;
            padding-right: 15px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: flex-end;
        }
        .lcars-pill {
            background-color: #ff9900;
            color: #000;
            width: 100px;
            height: 35px;
            border-radius: 20px;
            text-align: center;
            line-height: 35px;
            font-weight: bold;
            font-size: 14px;
        }
        .lcars-pill.blue { background-color: #5599cc; }
        .lcars-pill.purple { background-color: #cc99cc; }
        
        .lcars-main-content {
            flex-grow: 1;
            padding-left: 30px;
        }
        .lcars-header {
            font-size: 38px;
            color: #ffcc00;
            margin-bottom: 20px;
            border-bottom: 4px solid #ff9900;
            padding-bottom: 5px;
            display: flex;
            justify-content: space-between;
        }
        .lcars-header-index {
            color: #5599cc;
            font-size: 18px;
            align-self: flex-end;
        }
        .lcars-form-group {
            margin-bottom: 20px;
        }
        .lcars-form-group label {
            display: block;
            font-size: 18px;
            color: #cc99cc;
            margin-bottom: 5px;
        }
        .lcars-input, .lcars-select {
            width: 100%;
            max-width: 400px;
            background-color: #000;
            border: 2px solid #ff9900;
            color: #fff;
            padding: 8px;
            font-family: 'Antonio', sans-serif;
            font-size: 18px;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .lcars-input:focus, .lcars-select:focus {
            outline: none;
            border-color: #5599cc;
            box-shadow: 0 0 8px rgba(85, 153, 204, 0.5);
        }
        .lcars-btn {
            background-color: #ffcc00;
            color: #000;
            border: none;
            padding: 10px 30px;
            font-family: 'Antonio', sans-serif;
            font-weight: bold;
            font-size: 20px;
            border-radius: 0 25px 25px 0;
            cursor: pointer;
            transition: background 0.2s;
            text-transform: uppercase;
            margin-top: 10px;
        }
        .lcars-btn:hover {
            background-color: #ff9900;
        }
        .lcars-text-success { color: #00ff00; font-size: 18px; font-weight: bold; }
        .lcars-text-error { color: #ff3333; font-size: 18px; font-weight: bold; }
    </style>
</head>
<body>

<div class="lcars-container">
    <div class="lcars-left-bar">
        <div class="lcars-pill">REG-01</div>
        <div class="lcars-pill blue">SYS-42</div>
        <div class="lcars-pill purple">SEC-88</div>
    </div>

    <div class="lcars-main-content">
        <div class="lcars-header">
            <span>Personnel Registration</span>
            <span class="lcars-header-index">INDEX // 402</span>
        </div>

        <?php echo $message; ?>

        <form method="POST">
            <div class="lcars-form-group">
                <label>Username / Sub-ID Token</label>
                <input type="text" name="username" class="lcars-input" required autocomplete="off">
            </div>
            
            <div class="lcars-form-group">
                <label>Gender</label>
                <select id="reg_gender" name="gender" class="lcars-select" required>
        <option value="">-- SELECT CLASSIFICATION REFERENCE --</option>
        <option value="1">MALE</option>
        <option value="2">FEMALE</option>
        <option value="3">NON-BINARY</option>
    </select>
            </div>
            
            <div class="lcars-form-group">
                <label>Avatar UUID</label>
                <input type="text" name="uuid" class="lcars-input" required autocomplete="off">
            </div>
            
            <div class="lcars-form-group">
                <label>Subspace Email Destination</label>
                <input type="email" name="email" class="lcars-input" required autocomplete="off">
            </div>

            <div class="lcars-form-group">
                <label>Security Access Cipher (Password)</label>
                <input type="password" name="password" class="lcars-input" required>
            </div>

            <!-- FIXED: Changed input element's name attribute to 'DivID' to match PHP target -->
            <div class="lcars-form-group">
                <label>Assigned Starfleet Division</label>
                <select name="DivID" class="lcars-select" required>
                    <option value="" disabled selected hidden>Select Fleet Division...</option>
                    <?php foreach ($divisions as $div): ?>
                        <option value="<?php echo htmlspecialchars($div['did']); ?>">
                            <?php echo htmlspecialchars($div['dname']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="lcars-btn">Initialize Account</button>
        </form>
    </div>
</div>

</body>
</html>
