<?php
// 1. SYSTEM CHRONOMETER ENGINE
date_default_timezone_set('UTC'); // Synchronize to standard Starfleet temporal grid (UTC)

$current_timestamp = time();
$current_year      = (int)date('Y', $current_timestamp);
$day_of_year       = (int)date('z', $current_timestamp); // 0 to 364
$seconds_today     = ((int)date('G', $current_timestamp) * 3600) + ((int)date('i', $current_timestamp) * 60) + (int)date('s', $current_timestamp);

$days_in_year = (checkdate(2, 29, $current_year)) ? 366 : 365;
$year_progress = ($day_of_year + ($seconds_today / 86400)) / $days_in_year;

// 2. FORMULA CALCULATIONS MATRIX
// Real World Stardate: Current Year + Year Progress Fraction
$real_world_stardate = number_format($current_year + $year_progress, 2, '.', '');

// TNG / DS9 / Voyager / Picard Base Chronology (1 Year = 1000 Units. Year 0 = 2323 AD)
// TNG Start (Season 1): 2364 AD = Stardate 41000
// DS9 Start (Season 1): 2369 AD = Stardate 46000
// Voyager Start (Season 1): 2371 AD = Stardate 48000
// Picard Start (Season 1): 2399 AD = Stardate 76000
$years_since_tng_epoch = ($current_year - 2026); // Baseline from today forward

$tng_stardate     = number_format(41000 + ($years_since_tng_epoch * 1000) + ($year_progress * 1000), 1, '.', '');
$ds9_stardate     = number_format(46000 + ($years_since_tng_epoch * 1000) + ($year_progress * 1000), 1, '.', '');
$voyager_stardate = number_format(48000 + ($years_since_tng_epoch * 1000) + ($year_progress * 1000), 1, '.', '');
$picard_stardate  = number_format(76000 + ($years_since_tng_epoch * 1000) + ($year_progress * 1000), 1, '.', '');

// TOS Scale (2265 AD Epoch Baseline. 1 Day = 1 Unit)
$tos_stardate     = number_format(1312.4 + ($years_since_tng_epoch * $days_in_year) + ($day_of_year), 1, '.', '');
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>LCARS - Temporal Chronometer</title>
<style>
    :root { --o: #ff9900; --b: #33ccff; --p: #cc6699; --bg: #000000; --g: #444444; }
    body { background: var(--bg); color: #fff; font-family: Arial, sans-serif; margin: 0; padding: 15px; text-transform: uppercase; letter-spacing: 1px; }
    .h { display: flex; align-items: flex-end; margin-bottom: 15px; }
    .hb { background: var(--o); height: 35px; flex-grow: 1; border-bottom-left-radius: 18px; margin-right: 15px; position: relative; }
    .hb::before { content: "TIME-SYNC-802"; position: absolute; left: 25px; bottom: 3px; color: #000; font-weight: bold; font-size: 13px; }
    .ht { font-size: 24px; font-weight: bold; margin: 0; white-space: nowrap; color: var(--b); }
    .c { display: flex; min-height: calc(100vh - 120px); }
    .lb { width: 140px; display: flex; flex-direction: column; margin-right: 20px; }
    .le { background: var(--o); height: 50px; border-top-left-radius: 18px; border-bottom-left-radius: 18px; margin-bottom: 15px; position: relative; }
    .le::after { content: ""; position: absolute; background: var(--bg); width: 105px; height: 30px; bottom: 0; right: 0; border-top-left-radius: 12px; }
    .m { display: flex; flex-direction: column; gap: 8px; }
    .btn { background: var(--p); color: #000; padding: 10px 15px; text-decoration: none; font-weight: bold; font-size: 12px; text-align: right; border-radius: 4px 0 0 4px; }
    .btn:hover { background: #ffaaee; } .bb { background: var(--b); } .bb:hover { background: #88e2ff; }
    .mp { flex-grow: 1; display: flex; flex-direction: column; }
    .bi { border-bottom: 4px solid var(--o); padding-bottom: 8px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end; }
    .bi h1 { margin: 0; font-size: 20px; font-weight: normal; color: var(--o); }
    
    /* TIME GRID STYLING */
    .tg { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; margin-top: 10px; }
    .tc { background: #111; border-left: 8px solid var(--b); padding: 15px; border-radius: 0 8px 8px 0; transition: background 0.2s; }
    .tc:hover { background: #151515; }
    .tc h3 { margin: 0 0 10px 0; font-size: 14px; color: var(--o); border-bottom: 1px solid #333; padding-bottom: 5px; }
    .tv { font-family: monospace; font-size: 24px; font-weight: bold; color: #fff; }
    .td { font-size: 10px; color: #aaa; margin-top: 8px; }
</style></head><body>
    <header class="h"><div class="hb"></div><h2 class="ht">TEMPORAL COORDINATE MATRIX</h2></header>
    <div class="c">
        <nav class="lb"><div class="le"></div><div class="m">
            <a href="academy.php" class="btn">ACADEMY TERM</a><a href="welcome.php" class="btn bb">MAIN TERM</a>
        </div></nav>
        <main class="mp">
            <div class="bi"><h1>FEDERATION TIME DEFLECTION BRIDGE</h1><span style="color:var(--b);font-weight:bold;font-size:11px;">UTC STREAM LOCKED</span></div>
            
            <div class="tg">
                <div class="tc" style="border-left-color: var(--o);">
                    <h3>CURRENT EARTH TIMESTAMP</h3>
                    <div class="tv"><?php echo date('Y-m-d H:i:s'); ?></div>
                    <div class="td">STANDARD GREGORIAN CALENDAR ALIGNMENT</div>
                </div>
                <div class="tc" style="border-left-color: #55ff55;">
                    <h3>REAL-WORLD STARDATE</h3>
                    <div class="tv"><?php echo $real_world_stardate; ?></div>
                    <div class="td">CURRENT YEAR + FRACTIONAL PROGRESSION TRACK</div>
                </div>
                <div class="tc">
                    <h3>STAR TREK: PICARD TIMELINE</h3>
                    <div class="tv"><?php echo $picard_stardate; ?></div>
                    <div class="td">SCALED 24th-25th CENTURY SERIES REFERENCE SCHEMA</div>
                </div>
                <div class="tc">
                    <h3>STAR TREK: VOYAGER TIMELINE</h3>
                    <div class="tv"><?php echo $voyager_stardate; ?></div>
                    <div class="td">DELTA QUADRANT SECTOR CALIBRATION RATIO</div>
                </div>
                <div class="tc">
                    <h3>STAR TREK: DEEP SPACE NINE</h3>
                    <div class="tv"><?php echo $ds9_stardate; ?></div>
                    <div class="td">BAJORAN SYSTEM CHRONOGRAPH SHIFT INTERVAL</div>
                </div>
                <div class="tc">
                    <h3>STAR TREK: THE NEXT GENERATION</h3>
                    <div class="tv"><?php echo $tng_stardate; ?></div>
                    <div class="td">CLASSIC 24th CENTURY PRODUCTION MATHEMATICS</div>
                </div>
                <div class="tc" style="border-left-color: var(--p);">
                    <h3>THE ORIGINAL SERIES (TOS)</h3>
                    <div class="tv"><?php echo $tos_stardate; ?></div>
                    <div class="td">23rd CENTURY VARIABLE VELOCITY PROJECTED MATRIX</div>
                </div>
            </div>
        </main>
    </div>
</body></html>
