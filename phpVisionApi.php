<?php
require "phpVisionConfig.php";

function register_active_user() {
    if(session_status() !== PHP_SESSION_ACTIVE) session_start();
    if ($_SESSION["phpvision_has_registered"]) return;
    $_SESSION["phpvision_has_registered"] = true;
    $conn = new mysqli($GLOBALS["db_server_name"], $GLOBALS["db_username"], $GLOBALS["db_password"]);
    mysqli_select_db($conn, "phpVision");
    $stmt = $conn->prepare("INSERT INTO Events (Time, type) VALUES (NOW(), ?)");
    $type = "activeUser";
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $conn->close();
}

function register_page_hit($path) {
    $conn = new mysqli($GLOBALS["db_server_name"], $GLOBALS["db_username"], $GLOBALS["db_password"]);
    mysqli_select_db($conn, "phpVision");
    $stmt = $conn->prepare("INSERT INTO Events (Time, type, data) VALUES (NOW(), ?, ?)");
    $type = "pageView";
    $data = json_encode(array("page" => $path));
    $stmt->bind_param("ss", $type, $data);
    $stmt->execute();
    $conn->close();
}
?>