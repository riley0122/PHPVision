<?php
// *************************
//
//      Configuration
//
// *************************

// The database name / url
$db_server_name = "127.0.0.1:3307";
// The database users username
$db_username = "root";
// The password for that user
$db_password = "strong_password";

if (!isset($GLOBALS["db_server_name"])) $GLOBALS["db_server_name"] = $db_server_name;
if (!isset($GLOBALS["db_username"])) $GLOBALS["db_username"] = $db_username;
if (!isset($GLOBALS["db_password"])) $GLOBALS["db_password"] = $db_password
?>