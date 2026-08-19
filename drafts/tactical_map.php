<?php
// Include your existing security and database configuration layers
include 'session.php';
include 'config.php';

// Determine if the current session profile is cleared for admin commands (dh == 1)
$is_admin = false;
if (isset($_SESSION['login_user'])) {
    $chk = $conn->prepare("SELECT dh FROM accounts WHERE username = ? LIMIT 1");
    $chk->bind_param("s", $_SESSION['login_user']);
    $chk->execute();
    $res = $chk->get_result()->fetch_assoc();
    if ($res && (int)$res['dh'] === 1) {
        $is_admin = true;
    }
    $chk->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS TACTICAL GRID DISPLAY</title>
    <style>
        body { 
            background-color: #000; 
            color: #ff9900; 
            font-family: "Arial Black", Arial, sans-serif; 
            text-transform: uppercase; 
            margin: 20px; 
        }
        .lcars-header { 
            display: flex; 
            justify-content: space-between; 
            border-bottom: 4px solid #ffcc00; 
            padding-bottom: 5px; 
            margin-bottom: 20px; 
            font-size: 24px; 
        }
        #tactical-frame { 
            position: relative; 
            width: 800px; 
            margin: 0 auto; 
        }
        
        /* Creates an authentic glowing sector backdrop mesh without image dependencies */
        canvas { 
            background-color: #030308;
            background-image: 
                radial-gradient(circle at 50% 50%, transparent 40%, rgba(85, 85, 119, 0.05) 41%, transparent 42%),
                radial-gradient(circle at 50% 50%, transparent 70%, rgba(85, 85, 119, 0.05) 71%, transparent 72%),
                radial-gradient(rgba(255, 255, 255, 0.15) 1px, transparent 1px);
            background-size: 100% 100%, 100% 100%, 40px 40px;
            border: 3px solid #555577; 
            border-radius: 12px; 
            display: block; 
            box-shadow: 0 0 30px rgba(85, 85, 119, 0.25); 
        }
        
        .tooltip { 
            position: absolute; 
            background: rgba(0, 10, 25, 0.95); 
            color: #33ccff; 
            border: 2px solid #5588ff; 
            padding: 10px; 
            font-size: 11px; 
            border-radius: 4px; 
            pointer-events: none; 
            display: none; 
            text-transform: none; 
            font-family: Arial, sans-serif; 
            box-shadow: 0 0 15px rgba(51, 204, 255, 0.4); 
            z-index: 10;
        }
        .no-ships-alert { 
            text-align: center; 
            color: #ff3366; 
            font-size: 18px; 
            font-weight: bold; 
            margin-top: 25px; 
            animation: pulse 1.2s infinite; 
        }
        @keyframes pulse { 0% { opacity: 0.2; } 50% { opacity: 1; } 100% { opacity: 0.2; } }
        
        .admin-panel { 
            max-width: 794px; 
            margin: 30px auto 0 auto; 
            background: #111116; 
            border: 2px solid #ff9900; 
            padding: 20px; 
            border-radius: 8px; 
        }
        .form-grid { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 15px; 
            margin-top: 15px; 
        }
        input, select, button { 
            background: #000; 
            border: 2px solid #ff9900; 
            color: #fff; 
            padding: 10px; 
            font-size: 14px; 
            border-radius: 4px; 
            font-family: Arial, sans-serif; 
        }
        button { 
            background: #ff9900; 
            color: #000; 
            font-weight: bold; 
            cursor: pointer; 
            text-transform: uppercase; 
        }
        button:hover { background: #ffcc00; }
    </style>
</head>
<body>

<div class="lcars-header">
    <span>Tactical Sensor Array Matrix</span>
    <span style="color:#33ccff;">Subspace Scanner Active</span>
</div>

<div id="tactical-frame">
    <canvas id="tacticalMap" width="800" height="500"></canvas>
    <div id="mapTooltip" class="tooltip"></div>
    <div id="alertBox" class="no-ships-alert" style="display:none;">NO SHIPS CURRENTLY DETECTED AS ACTIVE</div>
</div>

<!-- ==========================================
     ADMINISTRATIVE DEPLOYMENT MANAGEMENT PANEL
     ========================================== -->
<?php if ($is_admin): ?>
    <div class="admin-panel">
        <h3 style="color:#ff9900; margin:0 0 15px 0;">TACTICAL FLEET MANAGEMENT INTERFACE</h3>
        
       <!-- Dropdown Selector Form to Activate Stored Fleet Assets -->
<form method="POST" action="map_admin.php" style="border-bottom: 2px dashed #555577; padding-bottom: 20px; margin-bottom: 20px;">
    <!-- FIXED: Added 'for' attribute to map to the selection field element ID -->
    <label for="activate_ship_id" style="color:#33ccff; font-size: 12px; display:block; margin-bottom: 8px;">CHOOSE DEACTIVATED STARSHIP FOR GRID DEPLOYMENT:</label>
    <div style="display: flex; gap: 15px;">
        <!-- FIXED: Added 'id' attribute matching the label's 'for' value -->
        <select name="activate_ship_id" id="activate_ship_id" style="flex-grow: 1; text-transform: uppercase;">
            <?php
            // Query every ship that is NOT currently on active duty
            $stored_ships = $conn->query("SELECT id, ship_name, ncc_number, status FROM `starships` WHERE status != 'Active Duty' ORDER BY ship_name ASC");
            if ($stored_ships && $stored_ships->num_rows > 0) {
                while($s_row = $stored_ships->fetch_assoc()) {
                    echo "<option value='".$s_row['id']."'>".$s_row['ship_name']." [".$s_row['ncc_number']."] - STATUS: ".$s_row['status']."</option>";
                }
            } else {
                echo "<option value=''>NO COLD STATUS SHIPS IN RESERVE FILES</option>";
            }
            ?>
        </select>
        <button type="submit" name="deploy_stored_ship" style="white-space: nowrap;">Order Active Duty Launch</button>
    </div>
</form>

        <!-- Fallback Form to Register and Launch an Entirely New Starship Build -->
        <h4 style="color:#cc6699; margin:0;">COMMISSION NEW REGISTRY HULL VECTOR</h4>
        <form method="POST" action="map_admin.php">
            <div class="form-grid">
                <input type="text" name="ship_name" placeholder="STARSHIP NAME (e.g. USS ENTERPRISE)" required autocomplete="off">
                <input type="text" name="ncc_number" placeholder="REGISTRY NUMBER (e.g. NCC-1701-D)" required autocomplete="off">
                <input type="text" name="captain_name" placeholder="COMMANDING OFFICER NAME" required autocomplete="off">
            </div>
            <button type="submit" name="deploy_ship" style="width:100%; margin-top:15px;">Authorize Starship Launch Sequence</button>
        </form>
    </div>
<?php endif; ?>

<script>
const canvas = document.getElementById('tacticalMap');
const ctx = canvas.getContext('2d');
const tooltip = document.getElementById('mapTooltip');
const alertBox = document.getElementById('alertBox');
let activeStarships = [];

function fetchScannerData() {
    fetch('map_data.php')
        .then(res => res.json())
        .then(data => {
            activeStarships = Array.isArray(data) ? data : [];
            // Toggle flashing alert text if active fleet array size resolves to zero
            alertBox.style.display = (activeStarships.length === 0) ? 'block' : 'none';
            renderTacticalMap();
        })
        .catch(err => console.error("Telemetry failure:", err));
}

function renderTacticalMap() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // 1. Draw secondary sub-grid lines mesh
    ctx.strokeStyle = "rgba(85, 85, 119, 0.12)";
    ctx.lineWidth = 1;
    for (let x = 80; x < canvas.width; x += 80) { ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, canvas.height); ctx.stroke(); }
    for (let y = 80; y < canvas.height; y += 80) { ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(canvas.width, y); ctx.stroke(); }

    // 2. Draw Main Crosshair Grid Sectors
    ctx.strokeStyle = "rgba(255, 153, 0, 0.25)";
    ctx.lineWidth = 2;
    ctx.beginPath(); ctx.moveTo(canvas.width / 2, 0); ctx.lineTo(canvas.width / 2, canvas.height); ctx.stroke();
    ctx.beginPath(); ctx.moveTo(0, canvas.height / 2); ctx.lineTo(canvas.width, canvas.height / 2); ctx.stroke();

    // 3. Print Sector Quadrant Identification Identifiers
    ctx.fillStyle = "rgba(85, 136, 255, 0.4)";
    ctx.font = "bold 11px Arial Black";
    ctx.fillText("QUADRANT ALPHA // SEC-01", 20, 25);
    ctx.fillText("QUADRANT BETA // SEC-02", canvas.width - 220, 25);
    ctx.fillText("QUADRANT GAMMA // SEC-03", 20, canvas.height - 20);
    ctx.fillText("QUADRANT DELTA // SEC-04", canvas.width - 220, canvas.height - 20);

    // 4. Plot Active Threat Profiles and Friendly Vectors
    activeStarships.forEach(ship => {
        ctx.beginPath();
        if (ship.is_enemy === 0) {
            // Draw Allied Fleet Target (Green Triangle)
            ctx.fillStyle = "#00ff88";
            ctx.shadowColor = "#00ff88";
            ctx.shadowBlur = 4;
            ctx.moveTo(ship.x, ship.y - 10);
            ctx.lineTo(ship.x - 10, ship.y + 10);
            ctx.lineTo(ship.x + 10, ship.y + 10);
            ctx.closePath();
            ctx.fill();
        } else {
            // Draw Hostile Sensor Anomaly (Red Circle)
            ctx.fillStyle = "#ff3366";
            ctx.shadowColor = "#ff3366";
            ctx.shadowBlur = 5;
            ctx.arc(ship.x, ship.y, 7, 0, 2 * Math.PI);
            ctx.fill();
        }
        ctx.shadowBlur = 0; // Clear blur layer footprint
    });
}

// Coordinate intersection targeting update
canvas.addEventListener('mousemove', (e) => {
    const rect = canvas.getBoundingClientRect();
    const mouseX = e.clientX - rect.left;
    const mouseY = e.clientY - rect.top;
    let targetFound = false;

    activeStarships.forEach(ship => {
        const distance = Math.sqrt((mouseX - ship.x)**2 + (mouseY - ship.y)**2);
        if (distance < 14) {
            targetFound = true;
            tooltip.style.display = 'block';
            tooltip.style.left = (ship.x + 15) + 'px';
            tooltip.style.top = (ship.y - 20) + 'px';
            
            // 1. ALLIED FEDERATION STARSHIP VIEW
            if (ship.is_enemy === 0) {
                tooltip.innerHTML = `<strong>NAME:</strong> ${ship.ship_name}<br><strong>REGISTRY:</strong> ${ship.ncc_number}<br><strong>COMMANDER:</strong> ${ship.captain_name}`;
            } 
            // 2. HOSTILE VESSEL MATRIX IDENTIFICATION
            else {
                const upperName = ship.ship_name.toUpperCase();
                
                // If it contains the keyword UNKNOWN, hide metrics
                if (upperName.includes("UNKNOWN") || upperName.includes("INTRUDER")) {
                    tooltip.innerHTML = `<span style="color:#ff3366; font-weight:bold;">ALERT: UNKNOWN HOSTILE VESSEL</span>`;
                } 
                // Expose metrics if the enemy asset profile is known to computers
                else {
                    tooltip.innerHTML = `
                        <span style="color:#ff3366; font-weight:bold;">ALERT: HOSTILE IDENTIFIED</span><br>
                        <strong>CLASS/NAME:</strong> ${ship.ship_name}<br>
                        <strong>SIGNATURE:</strong> ${ship.ncc_number}<br>
                        <strong>TARGET CO:</strong> ${ship.captain_name}`;
                }
            }
        }
    });

    if (!targetFound) tooltip.style.display = 'none';
});

// Fire up automated telemetry scanner sync tracking links
fetchScannerData();
setInterval(fetchScannerData, 30000); // Check for fresh movement arrays inside database every 30 seconds
</script>

</body>
</html>
<?php $conn->close(); ?>
