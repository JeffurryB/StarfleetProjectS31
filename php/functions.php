<?php

function GetAssetName( $string )
{
    return str_replace( "INVENTORY_", "", $string );
}


function Advanced_HTTP_Request( $Host, $PostData = "" )
{
    $Method = "POST";
    if ( empty( $PostData ) )
    {
        $Method = "GET";
    }
    $Port = 80;
    if ( strtolower( substr( $Host, 0, 5 ) ) == "https" )
    {
        $Port = 443;
    }
    $Host = explode( "//", $Host, 2 );
    if ( count( $Host ) < 2 )
    {
        $Host[1] = $Host[0];
    }
    $Host = explode( "/", $Host[1], 2 );
    if ( $Port == 443 )
    {
        $SSLAdd = "ssl://";
    }
    $Host[0] = explode( ":", $Host[0] );
    if ( count( $Host[0] ) > 1 )
    {
        $Port = $Host[0][1];
        $Host[0] = $Host[0][0];
    }
    else
    {
        $Host[0] = $Host[0][0];
    }
    $Socket = fsockopen( $SSLAdd.$Host[0], $Port, $Dummy1, $Dummy2, 10 );
    if ( $Socket )
    {
        fputs( $Socket, "$Method /$Host[1] HTTP/1.1\r\n".
               "Host: $Host[0]\r\n".
               "Content-type: application/x-www-form-urlencoded\r\n".
               "User-Agent: Opera/9.01 (Windows NT 5.1; U; en)\r\n".
               "Accept-Language: de-DE,de;q=0.9,en;q=0.8\r\n".
               "Accept-Charset: iso-8859-1, utf-8, utf-16, *;q=0.1\r\n".
               "Content-length: ".strlen( $PostData )."\r\n".
               "Connection: close\r\n".
               "\r\n".
               $PostData );
        $Tme = time();
        while( !feof( $Socket ) && $Tme + 30 > time() )
        {
            $Res = $Res.fgets( $Socket, 256 );
        }
        fclose( $Socket );
    }
    $Res = explode( "\r\n\r\n", $Res, 2 );
    return $Res[1];
}

/**
 * Core Security Log System - Commits system overrides into the logging matrix
 *
 * @param mysqli $dbConnection  Active SQL resource link
 * @param string $operator      Logged-in account username triggering the task
 * @param string $actionType    Type of operation (UPDATE, INSERT, DELETE, PURGE)
 * @param string $module        Target panel name (PERSONNEL, ASSETS, MESSAGES)
 * @param string $identifier    Unique identity handle (Row ID, UUID, serial)
 * @param string $telemetry     Text detailing exactly what changed
 * @return bool                 True if transaction successfully logged
 */
function record_security_log($dbConnection, $operator, $actionType, $module, $identifier, $telemetry) {
    $log_sql = "INSERT INTO `security_logs` 
                (`operator_username`, `action_type`, `target_module`, `target_identifier`, `change_telemetry`) 
                VALUES (?, ?, ?, ?, ?)";
                
    $stmt_log = $dbConnection->prepare($log_sql);
    if ($stmt_log) {
        $stmt_log->bind_param("sssss", $operator, $actionType, $module, $identifier, $telemetry);
        $success = $stmt_log->execute();
        $stmt_log->close();
        return $success;
    }
    return false;
}

?>
