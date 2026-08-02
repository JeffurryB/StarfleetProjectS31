<?php
header( 'Content-type: text/html; charset=utf-8' );
include( "config.php" );
include( "session.php");
mysqli_set_charset( $db,"utf8" );

// 1. Get the parameter safely (fallback to empty string if not set)
$uuid = $_GET['uuid'] ?? '';

$ERROR = "\n\nFailed to update Group Tag.\nContact Admiral Name with the provided Error Message.";

// 2. Use a secure placeholder (?) instead of direct variable insertion
$Tag = "SELECT IFNULL(a.`DisplayName`, a.`username`) AS `name`, a.`active`, r.`rname`, t.`tag_name`, d.`colorX`, d.`colorY`, d.`ColorZ`, r.`RankLogo` 
        FROM `accounts` a 
        INNER JOIN `divisions` d ON a.`DivID` = d.`did` 
        INNER JOIN `Rank` r ON a.`RankID` = r.`RankID` 
        INNER JOIN `Titles` t ON a.`TitleID` = t.`tid` 
        WHERE `UUID` = ? 
        LIMIT 1";

// 3. Prepare the statement
$stmt = mysqli_prepare($db, $Tag);

if ($stmt) {
    // 4. Bind the UUID as a string parameter
    mysqli_stmt_bind_param($stmt, "s", $uuid);

    // 5. Execute the query
    mysqli_stmt_execute($stmt);

    // 6. Retrieve the secure result set
    $query = mysqli_stmt_get_result($stmt);
    
    // Get row count from the statement result
    $Rows = mysqli_num_rows($query);

    if ( $Rows == 0 ) // Is there a record already?
    {
        //No record on file they must be a civilian\observer
        echo "<255,255,255>:═══════\nCivilian\nRPGROUP";
    }
    elseif ( $Rows == 1 )
    {
        // Use mysqli_fetch_array to maintain compatibility with your original code
        $list = mysqli_fetch_array( $query );

        $name = $list['name'];
        $rank = $list['rname'];
        $tag = $list['tag_name'];
        $colorX = $list['colorX'];
        $colorY = $list['colorY'];
        $colorZ = $list['ColorZ'];
        $logo = $list['RankLogo'];

        if ( 0 == $list['active'] )
        {
            echo "<255,255,255>:".$logo."\nCivilian\nRPGROUP";
        }
        else
        {
            echo "<".$colorX.",".$colorY.",".$colorZ.">:".$logo."\n".$rank."\n".$name."\n".$tag."\nRPGROUP";
        }
    }
    
    // Close the statement to clean up resources
    mysqli_stmt_close($stmt);
} else {
    // Fallback error handling if database fails to prepare statement
    echo "<255,255,255>:" . $ERROR;
}
?>
