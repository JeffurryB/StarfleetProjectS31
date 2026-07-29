<?php
$DB_SERVER = "YOUR SERVER NAME";
$DB_USERNAME = "DB USERNAME";
$DB_PASSWORD = "DB PASSWORD";
$DB_DATABASE = "DB NAME";
$DB_PORT = "DB PORT NUMBER";
$db = mysqli_connect($DB_SERVER, $DB_USERNAME, $DB_PASSWORD,$DB_DATABASE, $DB_PORT);
if(mysqli_connect_errno()) { die('Databse Connection Error - ' . mysqli_connect_error());}
// Check for and auto-inject dynamic roleplay group variables
if (file_exists(__DIR__ . '/group_config.php')) {
    include_once(__DIR__ . '/group_config.php');
} else {
    // Fallback default variables just in case they haven't run setup yet - DO NOT CHANGE THESE SETTINGS BELOW!!!
    define('GROUP_NAME', 'Starfleet Simulation');
    define('GROUP_ABBR', 'SFS');
    define('GROUP_LOGO', 'images/logo.png');
    define('DEFAULT_AVATAR', 'ProfilePics/default.png');
}
?>
