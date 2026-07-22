<?php
header('Content-type: text/html; charset=utf-8');
include("config.php");
mysqli_set_charset($db,"utf8");
$query="SELECT rname FROM `Rank`";
$result=mysqli_query($db,$query);

function getRankVectorInsignia($rankName){
    // Clean and normalize text strings, converting dots into spaces to match formatting variations
    $cleanRank = str_replace('.', ' ', strtoupper(trim($rankName)));
    // Reduce multiple consecutive spaces down to a single clean space break
    $cleanRank = preg_replace('/\s+/', ' ', $cleanRank);
    
    $goldG="url(#goldGrad)";$silverG="url(#silverGrad)";
    $goldStroke='stroke="#8a6d0f" stroke-width="1"';
    $silverStroke='stroke="#555" stroke-width="0.5"';
    $baseSvg='<svg width="120" height="32" viewBox="0 0 120 32" style="display:block;">';
    $stdBox='<rect width="120" height="32" rx="6" fill="#18181e" stroke="#33ccff" stroke-width="1.5" />';
    $admBox='<rect width="120" height="32" rx="6" fill="#18181e" stroke="#cc6699" stroke-width="1.5" /><rect x="8" y="4" width="104" height="24" rx="3" fill="none" stroke="url(#goldGrad)" stroke-width="2" />';
    $cdtBox='<rect width="120" height="32" rx="6" fill="#18181e" stroke="#cc6699" stroke-width="1.5" />';
    $endSvg='</svg>';

    if(strpos($cleanRank,'FLEET ADMIRAL')!==false){
        return $baseSvg.$admBox.'<circle cx="24" cy="16" r="6" fill="'.$goldG.'" '.$goldStroke.'/><circle cx="44" cy="16" r="6" fill="'.$goldG.'" '.$goldStroke.'/><circle cx="60" cy="16" r="6" fill="'.$goldG.'" '.$goldStroke.'/><circle cx="76" cy="16" r="6" fill="'.$goldG.'" '.$goldStroke.'/><circle cx="96" cy="16" r="6" fill="'.$goldG.'" '.$goldStroke.'/>'.$endSvg;
    }
        if (strpos($cleanRank, 'COMMODORE') !== false) {
        return $baseSvg . '<rect width="120" height="32" rx="6" fill="#18181e" stroke="#33ccff" stroke-width="1.5" /><rect x="35" y="4" width="50" height="24" rx="3" fill="none" stroke="url(#goldGrad)" stroke-width="2" />' . '<circle cx="60" cy="16" r="6" fill="' . $goldG . '" ' . $goldStroke . '/>' . $endSvg;
    }
    if(strpos($cleanRank,'REAR ADMIRAL')!==false){
        return $baseSvg.'<rect width="120" height="32" rx="6" fill="#18181e" stroke="#33ccff" stroke-width="1.5" /><rect x="25" y="4" width="70" height="24" rx="3" fill="none" stroke="url(#goldGrad)" stroke-width="2" /><circle cx="45" cy="16" r="6" fill="'.$goldG.'" '.$goldStroke.'/><circle cx="75" cy="16" r="6" fill="'.$goldG.'" '.$goldStroke.'/>'.$endSvg;
    }
    if(strpos($cleanRank,'ADMIRAL')!==false){
        return $baseSvg.'<rect width="120" height="32" rx="6" fill="#18181e" stroke="#33ccff" stroke-width="1.5" /><rect x="15" y="4" width="90" height="24" rx="3" fill="none" stroke="url(#goldGrad)" stroke-width="2" /><circle cx="30" cy="16" r="6" fill="'.$goldG.'" '.$goldStroke.'/><circle cx="50" cy="16" r="6" fill="'.$goldG.'" '.$goldStroke.'/><circle cx="70" cy="16" r="6" fill="'.$goldG.'" '.$goldStroke.'/><circle cx="90" cy="16" r="6" fill="'.$goldG.'" '.$goldStroke.'/>'.$endSvg;
    }
    if(strpos($cleanRank,'CAPTAIN')!==false){
        return $baseSvg.$stdBox.'<circle cx="20" cy="16" r="8" fill="'.$goldG.'" '.$goldStroke.'/><circle cx="45" cy="16" r="8" fill="'.$goldG.'" '.$goldStroke.'/><circle cx="70" cy="16" r="8" fill="'.$goldG.'" '.$goldStroke.'/><circle cx="95" cy="16" r="8" fill="'.$goldG.'" '.$goldStroke.'/>'.$endSvg;
    }
    if(strpos($cleanRank,'LIEUTENANT COMMANDER')!==false||strpos($cleanRank,'LT COMMANDER')!==false){
        return $baseSvg.$stdBox.'<circle cx="20" cy="16" r="8" fill="'.$goldG.'" '.$goldStroke.'/><circle cx="45" cy="16" r="8" fill="'.$goldG.'" '.$goldStroke.'/><circle cx="70" cy="16" r="8" fill="#111" stroke="#d4af37" stroke-width="2"/>'.$endSvg;
    }
    if(strpos($cleanRank,'COMMANDER')!==false){
        return $baseSvg.$stdBox.'<circle cx="20" cy="16" r="8" fill="'.$goldG.'" '.$goldStroke.'/><circle cx="45" cy="16" r="8" fill="'.$goldG.'" '.$goldStroke.'/><circle cx="70" cy="16" r="8" fill="'.$goldG.'" '.$goldStroke.'/>'.$endSvg;
    }
        if (strpos($cleanRank,'LIEUTENANT JUNIOR GRADE')!==false || strpos($cleanRank,'LIEUTENANT J.G.')!==false || strpos($cleanRank,'LTJG')!==false || strpos($cleanRank,'LT JG')!==false){
        return $baseSvg.$stdBox.'<circle cx="20" cy="16" r="8" fill="'.$goldG.'" '.$goldStroke.'/><circle cx="45" cy="16" r="8" fill="#111" stroke="#d4af37" stroke-width="2"/>'.$endSvg;
    }
    if(strpos($cleanRank,'LIEUTENANT')!==false){
        return $baseSvg.$stdBox.'<circle cx="20" cy="16" r="8" fill="'.$goldG.'" '.$goldStroke.'/><circle cx="45" cy="16" r="8" fill="'.$goldG.'" '.$goldStroke.'/>'.$endSvg;
    }
    if(strpos($cleanRank,'ENSIGN')!==false){
        return $baseSvg.$stdBox.'<circle cx="20" cy="16" r="8" fill="'.$goldG.'" '.$goldStroke.'/>'.$endSvg;
    }
    if(strpos($cleanRank,'CADET 4')!==false){
        return $baseSvg.$cdtBox.'<rect x="18" y="6" width="6" height="20" rx="1" fill="'.$silverG.'" '.$silverStroke.' /><rect x="43" y="6" width="6" height="20" rx="1" fill="'.$silverG.'" '.$silverStroke.' /><rect x="68" y="6" width="6" height="20" rx="1" fill="'.$silverG.'" '.$silverStroke.' /><rect x="93" y="6" width="6" height="20" rx="1" fill="'.$silverG.'" '.$silverStroke.' />'.$endSvg;
    }
    if(strpos($cleanRank,'CADET 3')!==false){
        return $baseSvg.$cdtBox.'<rect x="30" y="6" width="6" height="20" rx="1" fill="'.$silverG.'" '.$silverStroke.' /><rect x="55" y="6" width="6" height="20" rx="1" fill="'.$silverG.'" '.$silverStroke.' /><rect x="80" y="6" width="6" height="20" rx="1" fill="'.$silverG.'" '.$silverStroke.' />'.$endSvg;
    }
    if(strpos($cleanRank,'CADET 2')!==false){
        return $baseSvg.$cdtBox.'<rect x="43" y="6" width="6" height="20" rx="1" fill="'.$silverG.'" '.$silverStroke.' /><rect x="68" y="6" width="6" height="20" rx="1" fill="'.$silverG.'" '.$silverStroke.' />'.$endSvg;
    }
    if(strpos($cleanRank,'CADET 1')!==false){
        return $baseSvg.$cdtBox.'<rect x="57" y="6" width="6" height="20" rx="1" fill="'.$silverG.'" '.$silverStroke.' />'.$endSvg;
    }
            if (strpos($cleanRank, '═══════') !== false || strpos($cleanRank, 'CIVILIAN') !== false) {
        // Returns a clean, empty black plate with a muted border — no marks or dots!
        return $baseSvg . '<rect width="120" height="32" rx="6" fill="#18181e" stroke="#555" stroke-width="1.5" />' . $endSvg;
    }
    return $baseSvg.$stdBox.'<circle cx="20" cy="16" r="4" fill="#666" />'.$endSvg;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS Starfleet Rank Registry</title>
    <style>:root{--lcars-purple:#9966cc;--lcars-orange:#ff9900;--lcars-pink:#cc6699;--lcars-blue:#33ccff;--lcars-bg:#000000}body{background-color:var(--lcars-bg);color:#fff;font-family:Arial,sans-serif;margin:0;padding:15px;text-transform:uppercase;letter-spacing:1px}.lcars-header{display:flex;align-items:flex-end;margin-bottom:15px}.lcars-bar-top{background-color:var(--lcars-blue);height:40px;flex-grow:1;border-bottom-left-radius:20px;margin-right:15px;position:relative}.lcars-bar-top::before{content:"SYS-RANK-712";position:absolute;left:25px;bottom:3px;color:#000;font-weight:bold;font-size:14px}.lcars-title{color:var(--lcars-blue);font-size:28px;font-weight:300;margin:0;white-space:nowrap}.lcars-container{display:flex;min-height:80vh}.lcars-left-bracket{width:150px;display:flex;flex-direction:column;margin-right:20px}.lcars-elbow{background-color:var(--lcars-blue);height:60px;border-top-left-radius:20px;border-bottom-left-radius:20px;margin-bottom:15px;position:relative}.lcars-elbow::after{content:"";position:absolute;background-color:var(--lcars-bg);width:110px;height:35px;bottom:0;right:0;border-top-left-radius:15px}.lcars-btn{background-color:var(--lcars-orange);color:#000;padding:10px;text-decoration:none;font-weight:bold;font-size:13px;text-align:right;margin-bottom:5px;border-radius:5px 0 0 5px}.lcars-btn.btn-back{background-color:var(--lcars-purple)}.lcars-main-panel{flex-grow:1;display:flex;flex-direction:column}.lcars-table{width:100%;border-collapse:collapse;margin-top:15px}.lcars-table th{background-color:var(--lcars-purple);color:#000;text-align:left;padding:12px;font-size:14px;font-weight:bold}.lcars-table td{padding:12px;border-bottom:1px solid #22222b;font-size:16px;vertical-align:middle}.lcars-table tr:hover{background-color:#111116}.logo-display{width:130px}</style>
</head>
<body>
    <header class="lcars-header">
        <div class="lcars-bar-top"></div>
        <h2 class="lcars-title">STARFLEET RANK REGISTRY</h2>
    </header>
    <div class="lcars-container">
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <a href="welcome.php" class="lcars-btn btn-back">MAIN TERM</a>
            <a href="staff_list.php" class="lcars-btn">STAFF LIST</a>
            <a href="courses.php" class="lcars-btn">COURSES</a>
            <a href="exams_web.php" class="lcars-btn" style="background-color:var(--lcars-pink);">TEST ARRAY</a>
        </nav>
        <main class="lcars-main-panel">
            <h2>OFFICIAL ACADEMY INSIGNIA INDEX</h2>
            <p style="text-transform:none;color:#aaa;margin-bottom:20px;">Starfleet Command Telemetry Matrix // Structural Hierarchy Verified</p>
            <table class="lcars-table">
                <thead>
                    <tr>
                        <th>RANK DESIGNATION NAME</th>
                        <th>VISUAL MARKER / LOGO FIELD</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($result && mysqli_num_rows($result)>0){
                        while($row=mysqli_fetch_array($result)){
                            echo "<tr>";
                            echo "<td style='color:var(--lcars-orange);font-weight:bold;'>".htmlspecialchars($row['rname'])."</td>";
                            echo "<td class='logo-display'>".getRankVectorInsignia($row['rname'])."</td>";
                            echo "</tr>";
                        }
                    }else{
                        echo "<tr><td colspan='2' style='color:var(--lcars-pink);text-align:center;'>NO COMM-CHANNELS CONNECTED TO RANK DATA RECORDS.</td></tr>";
                    }
                    mysqli_close($db);
                    ?>
                </tbody>
            </table>
        </main>
    </div>
    <svg width="0" height="0" style="position:absolute;">
        <defs>
            <linearGradient id="goldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#fffbcf" />
                <stop offset="30%" stop-color="#ffcc00" />
                <stop offset="70%" stop-color="#d4af37" />
                <stop offset="100%" stop-color="#8a6d0f" />
            </linearGradient>
            <linearGradient id="silverGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#ffffff" />
                <stop offset="30%" stop-color="#e1e1e1" />
                <stop offset="70%" stop-color="#aaaaaa" />
                <stop offset="100%" stop-color="#555555" />
            </linearGradient>
        </defs>
    </svg>
</body>
</html>
