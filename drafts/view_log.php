<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header('Location: log.php'); exit; }

include 'config.php';
if (!isset($db)) { die('ERROR: NO DB CONNECTION'); }

$id = (int)$_GET['id'];
$result = mysqli_query($db, "SELECT username, log_name, log_entry, created_at FROM Plogs WHERE id = $id LIMIT 1");
$log = ($result) ? mysqli_fetch_assoc($result) : null;

if (!$log) { header('Location: log.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS - VIEW PERSONAL LOG</title>
    <link href="https://googleapis.com" rel="stylesheet">
    <style>
        :root { --org: #f90; --l-org: #fc0; --blu: #9cf; --d-blu: #69c; --pur: #c9c; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #000; color: #fff; font-family: 'Antonio', 'Oswald', sans-serif; text-transform: uppercase; padding: 20px; }
        .lcars-h { display: flex; align-items: flex-end; margin-bottom: 5px; }
        .lcars-h .b-l { width: 150px; height: 45px; background: var(--pur); border-top-left-radius: 20px; margin-right: 5px; }
        .lcars-h .b-m { flex-grow: 1; height: 15px; background: var(--pur); margin-right: 5px; }
        .lcars-h .txt { color: var(--org); font-size: 2.2rem; font-weight: bold; padding-right: 15px; }
        .lcars-m { display: flex; }
        .lcars-s { width: 150px; display: flex; flex-direction: column; gap: 5px; margin-right: 15px; }
        .s-el { height: 40px; background: var(--blu); text-align: right; padding-right: 10px; line-height: 40px; color: #000; font-weight: bold; }
        .s-el.pur { background: var(--pur); } .s-el.org { background: var(--org); }
        .s-sp { flex-grow: 1; background: var(--d-blu); border-bottom-left-radius: 20px; min-height: 100px; }
        .lcars-c { flex-grow: 1; padding: 20px 10px; }
        .title { color: var(--l-org); font-size: 1.8rem; margin-bottom: 25px; border-bottom: 2px solid var(--blu); padding-bottom: 5px; }
        .box { background: #112; border-left: 6px solid var(--org); padding: 25px; font-family: monospace; text-transform: none; font-size: 1.15rem; line-height: 1.6; color: #cdf; margin-bottom: 30px; border-radius: 0 15px 15px 0; white-space: pre-wrap; }
        .meta { margin-bottom: 12px; font-size: 1.2rem; color: var(--blu); }
        .btn { font-family: 'Antonio'; padding: 10px 25px; font-size: 1.2rem; border: none; cursor: pointer; font-weight: bold; text-transform: uppercase; background: var(--d-blu); color: #000; border-radius: 15px; text-decoration: none; display: inline-block; }
        .btn:hover { background: var(--blu); }
    </style>
</head>
<body>
    <div class="lcars-h"><div class="b-l"></div><div class="b-m"></div><div class="txt">LCARS READOUT TERMINAL</div></div>
    <div class="lcars-m">
        <nav class="lcars-s"><div class="s-el">SYS 01</div><div class="s-el pur">READ</div><div class="s-el org">DATA</div><div class="s-sp"></div></nav>
        <main class="lcars-c">
            <h2 class="title">Personal Log Transmission</h2>
            <div class="meta">Source Identity: <span style="color:var(--org)"><?= htmlspecialchars($log['username']) ?></span></div>
            <div class="meta">Log Index ID: <span style="color:var(--l-org)"><?= htmlspecialchars($log['log_name']) ?></span></div>
            <div class="meta">Filing Timestamp: <span style="color:var(--blu)"><?= htmlspecialchars($log['created_at']) ?></span></div>
            <div class="box"><?= htmlspecialchars($log['log_entry']) ?></div>
            <a href="welcome.php" class="btn">&lt; Return to Welcome Page</a>
        </main>
    </div>
</body>
</html>
