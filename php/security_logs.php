<?php
include("config.php"); include("session.php"); include("functions.php");
ini_set('display_errors', 1); error_reporting(E_ALL);
mysqli_set_charset($db, "utf8");

$auth_user = mysqli_real_escape_string($db, $login_session);
$res_auth = mysqli_query($db, "SELECT dh FROM accounts WHERE username = '$auth_user' LIMIT 1");
if ($res_auth && $auth_data = mysqli_fetch_assoc($res_auth)) {
    if ((int)$auth_data['dh'] !== 1) { header("Location: notauthorized.php"); exit(); }
} else { header("Location: index.php"); exit(); }

$log_res = mysqli_query($db, "SELECT * FROM `security_logs` ORDER BY `log_id` DESC LIMIT 100");
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>LCARS - Security Audit Log</title>
<style>
    :root { --r: #cc3333; --dr: #551111; --o: #ff9900; --b: #33ccff; --bg: #000000; --a: #ffcc33; }
    body { background: var(--bg); color: #fff; font-family: Arial, sans-serif; margin: 0; padding: 15px; text-transform: uppercase; letter-spacing: 1px; }
    @keyframes rA { 0%, 100% { background: var(--r); box-shadow: 0 0 10px var(--r); } 50% { background: var(--dr); box-shadow: none; } }
    @keyframes tW { 0%, 100% { color: #fff; opacity: 1; } 50% { color: #f55; opacity: 0.4; } }
    .h { display: flex; align-items: flex-end; margin-bottom: 15px; }
    .hb { animation: rA 1.5s infinite ease-in-out; height: 35px; flex-grow: 1; border-bottom-left-radius: 18px; margin-right: 15px; position: relative; }
    .hb::before { content: "SEC-LOG-902"; position: absolute; left: 25px; bottom: 3px; color: #000; font-weight: bold; font-size: 13px; }
    .ht { animation: tW 1.5s infinite ease-in-out; font-size: 24px; font-weight: bold; margin: 0; white-space: nowrap; }
    .c { display: flex; min-height: calc(100vh - 120px); }
    .lb { width: 140px; display: flex; flex-direction: column; margin-right: 20px; }
    .le { background: var(--o); height: 50px; border-top-left-radius: 18px; border-bottom-left-radius: 18px; margin-bottom: 15px; position: relative; animation: rA 1.5s infinite ease-in-out; }
    .le::after { content: ""; position: absolute; background: var(--bg); width: 105px; height: 30px; bottom: 0; right: 0; border-top-left-radius: 12px; }
    .m { display: flex; flex-direction: column; gap: 8px; }
    .btn { background: var(--o); color: #000; padding: 10px 15px; text-decoration: none; font-weight: bold; font-size: 12px; text-align: right; border-radius: 4px 0 0 4px; }
    .btn:hover { background: #ffcc00; } .bb { background: var(--b); } .bb:hover { background: #88e2ff; } .br { background: var(--r); color: #fff; } .br:hover { background: #f55; }
    .mp { flex-grow: 1; display: flex; flex-direction: column; }
    .bi { border-bottom: 4px solid var(--r); padding-bottom: 8px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end; }
    .bi h1 { margin: 0; font-size: 20px; font-weight: normal; color: var(--r); }
    .t { width: 100%; border-collapse: collapse; font-size: 11px; }
    .t th { background: var(--r); color: #fff; font-weight: bold; padding: 10px; text-align: left; }
    .t th:first-child { border-radius: 4px 0 0 4px; } .t th:last-child { border-radius: 0 4px 4px 0; }
    .row { border-bottom: 2px solid #222; transition: background 0.15s; } .row:hover { background: #1a0505; }
    .cell { padding: 12px 10px; vertical-align: top; }
    .bdg { font-weight: bold; color: #000; padding: 3px 6px; border-radius: 3px; font-size: 10px; display: inline-block; text-align: center; width: 65px; }
    .a-upd { background: var(--a); } .a-ins { background: #55ff55; } .a-del { background: var(--r); color: #fff; } .a-prg { background: #f5f; }
    .tel { text-transform: none; color: #ffcccc; font-family: monospace; font-size: 12px; line-height: 1.4; word-break: break-word; }
</style></head><body>
    <header class="h"><div class="hb"></div><h2 class="ht">⚠️ AUDIT LOG SYSTEM ⚠️</h2></header>
    <div class="c">
        <nav class="lb"><div class="le"></div><div class="m">
            <a href="dhpanel.php" class="btn">DH PANEL</a><a href="welcome.php" class="btn bb">MAIN TERM</a><a href="security_logs.php" class="btn br">AUDIT LOGS</a>
            <!-- This button calls your new secure script line -->
<a href="clear_logs.php" class="btn br" onclick="return confirm('⚠️ CRITICAL COMMAND ALERT: CONFIRM RECONSTITUTION WIPE OF TOTAL SECTOR LOG MANIFEST?');" style="margin-top: 15px; text-align: center; display: block; border: 2px dashed #ff0000;">PURGE ARCHIVE</a>

        </div></nav>
        <main class="mp"><div class="bi"><h1>MANIFEST OVERRIDE HISTORY</h1><span style="color:var(--r);font-size:11px;font-weight:bold;">TRACE ACTIVE</span></div>
            <table class="t"><thead><tr>
                <th style="width:50px;">ID</th><th style="width:140px;">TIMESTAMP</th><th style="width:100px;">OPERATOR</th>
                <th style="width:85px;text-align:center;">ACTION</th><th style="width:100px;">MODULE</th><th style="width:100px;">TARGET</th><th>TELEMETRY DATA EXTRACT</th>
            </tr></thead><tbody>
                <?php if ($log_res && mysqli_num_rows($log_res) > 0): while ($log = mysqli_fetch_assoc($log_res)): 
                    $bg = 'a-upd'; if ($log['action_type'] === 'INSERT') $bg = 'a-ins'; if ($log['action_type'] === 'DELETE') $bg = 'a-del'; if ($log['action_type'] === 'TRUNCATE' || $log['action_type'] === 'PURGE') $bg = 'a-prg';
                ?>
                    <tr class="row">
                        <td class="cell" style="font-family:monospace;color:var(--r);font-weight:bold;">#<?php echo sprintf("%04d", $log['log_id']); ?></td>
                        <td class="cell" style="color:#aaa;font-family:monospace;"><?php echo htmlspecialchars($log['log_timestamp']); ?></td>
                        <td class="cell" style="font-weight:bold;color:var(--b);"><?php echo htmlspecialchars($log['operator_username']); ?></td>
                        <td class="cell" style="text-align:center;"><span class="bdg <?php echo $bg; ?>"><?php echo htmlspecialchars($log['action_type']); ?></span></td>
                        <td class="cell" style="font-weight:bold;color:var(--o);">`<?php echo htmlspecialchars($log['target_module']); ?>`</td>
                        <td class="cell" style="font-family:monospace;color:var(--a);"><?php echo htmlspecialchars($log['target_identifier']); ?></td>
                        <td class="cell tel"><?php echo htmlspecialchars($log['change_telemetry']); ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="7" class="cell" style="text-align:center;color:var(--o);font-weight:bold;padding:40px;">NO TRANSACTIONS DETECTED.</td></tr>
                <?php endif; ?>
            </tbody></table></main></div></body></html><?php mysqli_close($db); ?>
