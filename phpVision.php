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
  `data` json DEFAULT NULL,
  `id` INT auto_increment NOT NULL,
  CONSTRAINT Events_PK PRIMARY KEY (id)
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@100..900&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #fbfbfe;
            margin: 0;
        }

        #container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            grid-template-rows: 1.1fr 1fr;
            grid-column-gap: 0px;
            grid-row-gap: 0px;
            width: 100vw;
            height: 100vh;
        }

        #ActiveUserGraphBox { grid-area: 1 / 1 / 2 / 3; }
        #PageHitsBox { grid-area: 2 / 1 / 3 / 2; }
        #CustomEventsBox { grid-area: 2 / 2 / 3 / 3; }

        .dataBox {
            margin: 30px;
            padding: 20px;
            padding-top: 0;
            border: #433BFF solid 5px;
            border-radius: 50px;
            font-family: "Noto Sans Lao", sans-serif;
        }

        hr {
            background-color: #dedcff;
            color: #dedcff;
            height: 2px;
            border: none;
            margin-top: none;
        }

        .dataBoxTitle {
            margin-bottom: 3px;
        }

        #ActiveUserGraph {
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            justify-content: space-evenly;
        }

        .datapoint {
            margin-top: 10px;
            height: 30vh;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }

        .GraphElement {
            background-color: #050315;
            height: 100%;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
    </style>
</head>
<body>
    <div id="container">
        <div id="ActiveUserGraphBox" class="dataBox">
            <h2 class="dataBoxTitle">Active Users</h2>
            <hr>
            <div id="ActiveUserGraph">
                <?php
                    $emptyActivityData = false;

                    // Generate timestamp 14 days ago
                    $timestamp = strtotime("-14 days");

                    // SQL get all datapoints with that timestamp
                    $sql = "SELECT * FROM Events WHERE Time > DATE_SUB(NOW(), INTERVAL 14 DAY) AND type = 'activeUser'";
                    $result = $conn->query($sql);
                    $events = array();

                    if ($result->num_rows > 0){
                        while($row = $result->fetch_assoc()) {
                            $events[] = date("d-m", strtotime($row["Time"]));
                        }
                    } else {
                        echo '<div class="NoData">No data found</div>';
                        $emptyActivityData = true;
                    }

                    $activityData = array();
                    foreach ($events as $event) {
                        if (!array_key_exists($event, $activityData)) {
                            $activityData[$event] = 0;
                        }

                        $activityData[$event] += 1;
                    }

                    echo "<script>console.log('Activity data: ' + `" . print_r($activityData, true) . "`);</script>";

                    // Create div for each datapoint
                    if (!$emptyActivityData) {
                        for ($i=1; $i <= 14; $i++) {
                            $date = date("d-m", strtotime("-" . 14 - $i . " days"));
                            if (!array_key_exists($date, $activityData)) {
                                $activityData[$date] = 0;
                            }

                            // Caluclate value between 20 and 100 percent based of the min and max values
                            $minimum = min($activityData);
                            $maximum = max($activityData);
                            if (count($activityData) < 14) {
                                $minimum = 0;
                            }

                            $thisValue = ($activityData[$date] - $minimum) / ($maximum - $minimum);
                            $thisValue *= 100;

                            ob_start(); ?>
                            <div class="datapoint">
                                <div class="GraphElement" style="height: <?php echo $thisValue?>%;"></div>
                                <div class="DateMarker"><?php echo $date; ?></div>
                            </div>
                        <?php echo ob_get_clean();
                        }
                    }
                ?>
            </div>
        </div>
        <div id="PageHitsBox" class="dataBox">
            <h2 class="dataBoxTitle">Page Activity</h2>
            <hr>
        </div>
        <div id="CustomEventsBox" class="dataBox">
            <h2 class="dataBoxTitle">Custom Events</h2>
            <hr>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>