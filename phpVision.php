<?php
// *************************
//
//      Configuration
//
// *************************

$db_server_name = "127.0.0.1:3307";
$db_username = "root";
$db_password = "strong_password";
?>


<?php
$conn = new mysqli($db_server_name, $db_username, $db_password);
if ($conn->connect_error) {
    die("Connection to database failed.<br>error: " . $conn->connect_error);
}

echo "<script>console.log('Connected to db succesfully!')</script>";

$sql = "CREATE DATABASE IF NOT EXISTS phpVision";
if ($conn->query($sql) === TRUE) {
    echo "<script>console.log('Database created successfully');</script>";
} else {
    echo "<script>console.log(`Error creating database: " . $conn->error . "`);</script>";
}

mysqli_select_db($conn, "phpVision");

$sql = "CREATE TABLE IF NOT EXISTS `Events` (
  `Time` datetime DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `data` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";
if ($conn->query($sql) === TRUE) {
    echo "<script>console.log('Events table created successfully');</script>";
} else {
    echo "<script>console.log(`Error creating events table: " . $conn->error . "`);</script>";
}

$sql = "CREATE TABLE IF NOT EXISTS phpVision.Users (
	username varchar(100) NULL,
	auth_key varchar(100) NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;";
  if ($conn->query($sql) === TRUE) {
      echo "<script>console.log('user table created successfully');</script>";
  } else {
      echo "<script>console.log(`Error creating user table: " . $conn->error . "`);</script>";
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHPVision analytics dashboard</title>
    <style>
        body {
            background-color: #fbfbfe;
        }
    </style>
</head>
<body>
    <div id="ActiveUserGraph">
        Coming soon...
    </div>
    <div id="PageHits">
        Coming Soon...
    </div>
    <div id="CustomEvents">
        Coming Soon...
    </div>
</body>
</html>

<?php $conn->close(); ?>