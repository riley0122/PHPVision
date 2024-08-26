<?php
require "phpVisionConfig.php";

// SQL
$sqlendsequence= "";
$conn = new mysqli($GLOBALS["db_server_name"], $GLOBALS["db_username"], $GLOBALS["db_password"]);
if ($conn->connect_error) {
    die("Connection to database failed.<br>error: " . $conn->connect_error);
}

$sqlendsequence .= "<script>console.log('Connected to db succesfully!')</script>";

$sql = "CREATE DATABASE IF NOT EXISTS phpVision";
if ($conn->query($sql) === TRUE) {
    $sqlendsequence .= "<script>console.log('Database created successfully');</script>";
} else {
    $sqlendsequence .= "<script>console.log(`Error creating database: " . $conn->error . "`);</script>";
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
    $sqlendsequence .= "<script>console.log('Events table created successfully');</script>";
} else {
    $sqlendsequence .= "<script>console.log(`Error creating events table: " . $conn->error . "`);</script>";
}

$sql = "CREATE TABLE IF NOT EXISTS phpVision.Users (
	username varchar(100) NULL,
	auth_key varchar(100) NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;";
if ($conn->query($sql) === TRUE) {
    $sqlendsequence .= "<script>console.log('user table created successfully');</script>";
} else {
    $sqlendsequence .= "<script>console.log(`Error creating user table: " . $conn->error . "`);</script>";
}

$sql = "SELECT * FROM Users";
$result = $conn->query($sql);
if ($result->num_rows == 0){
    if (!isset($_POST["u"]) || !isset($_POST["p"])) {
        ob_start(); ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>phpVision installation</title>
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@100..900&display=swap" rel="stylesheet">
                <style>
                    body {
                        background-color: #fbfbfe;
                        margin: 0;
                        font-family: "Noto Sans Lao", sans-serif;
                    }

                    #container {
                        border: #433BFF solid 5px;
                        border-radius: 20px;
                        width: max-content;
                        height: max-content;
                        padding: 20px;
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        display: flex;
                        flex-direction: column;
                        flex-wrap: wrap;
                        align-items: center;
                    }

                    form {
                        display: flex;
                        flex-direction: column;
                        flex-wrap: wrap;
                        align-items: center;
                    }

                    input {
                        margin: 2px;
                    }
                </style>
            </head>
            <body>
                <div id="container">
                    <h1>Register a user</h1>
                    <p style="text-align: center; font-size: small;">Because this is the first time that you acces phpVision,<br>you need to register a user for you to log in with.<br>You can add additional users later on via the database.</p>
                    <form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
                        <input type="text" placeholder="username" name="u" required>
                        <input type="password" name="p" placeholder="password" required>
                        <input type="submit" value="Submit">
                    </form>
                </div>
            </body>
            </html>
        <?php echo ob_get_clean();
    } else {
        $stmt = $conn->prepare("INSERT INTO Users (username, auth_key) VALUES (?, ?)");
        $pass = md5($_POST["p"]);
        $stmt->bind_param("ss", $_POST["u"], $pass);
        $stmt->execute();
        ob_start(); ?>
        <h1>Success</h1>
        <p>Succesfully added user!</p>
        <hr>
        <p>Succesfully stored data for user <?php echo $_POST["u"] ?>. Refresh this page to log in!</p>
        <?php echo ob_get_clean();
    }
    exit;
}

// AUTH

if(!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="phpVision"');
    header('HTTP/1.0 401 Unauthorized');
    echo $sqlendsequence;
    ob_start(); ?>
        <h1>401</h1>
        <p>Unauthorized!</p>
        <hr>
        <p>You need to log in to view this page!</p>
    <?php echo ob_get_clean();
    exit;
} else {
    // Check authentication
    $user = $_SERVER['PHP_AUTH_USER'];
    $passwd = md5($_SERVER['PHP_AUTH_PW']);

    $sql = "SELECT * from Users WHERE username='$user'";
    $result = $conn->query($sql);

    if ($result->num_rows == 0){
        header('WWW-Authenticate: Basic realm="phpVision"');
        header('HTTP/1.0 401 Unauthorized');
        ob_start(); ?>
        <h1>401</h1>
        <p>Unauthorized!</p>
        <hr>
        <p>Authentication failed.</p>
        <?php echo ob_get_clean();
        $conn->close();
        exit;
    } else if ($result->num_rows > 1) {
        header('HTTP/1.0 500 Internal server error');
        ob_start(); ?>
        <h1>500</h1>
        <p>Internal server error!</p>
        <hr>
        <p>Something went wrong, contact the database administrator!</p>
        <?php echo ob_get_clean();
        $conn->close();
        exit;
    } else {
        // Check password
        $row = $result->fetch_assoc();
        if ($row["auth_key"] !== $passwd) {
            header('WWW-Authenticate: Basic realm="phpVision"');
            header('HTTP/1.0 401 Unauthorized');
            ob_start(); ?>
            <h1>401</h1>
            <p>Unauthorized!</p>
            <hr>
            <p>Authentication failed.</p>
            <?php echo ob_get_clean();
            $conn->close();
        }
    }
}

echo $sqlendsequence;
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
            border-radius: 30px;
            font-family: "Noto Sans Lao", sans-serif;
        }

        hr {
            background-color: #dedcff;
            color: #dedcff;
            height: 3px;
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

        table {
            border-collapse: collapse;
            width: 100%;
        }

        tr {
            border-bottom: 2px solid #dedcff;
        }

        td:nth-child(even) {
            border-left: 2px solid #dedcff;
            padding-left: 20px;
            width: 25%;
        }

        th:nth-child(even) {
            width: 25%;
            text-align: left;
            padding-left: 30px;
        }

        th:nth-child(odd), td:nth-child(odd) {
            padding-left: 15px;
        }

        th:nth-child(even), td:nth-child(even) {
            padding-right: 20px;
        }

        tr {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            width: 100%;
        }

        #pageHitsBox {
            overflow-y: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        #pageHitsBox::-webkit-scrollbar {
            display: none;
        }

        #tooltip {
            position: absolute;
            pointer-events: none;
            padding: 15px;
            background-color: #dedcff;
            border-radius: 15px;
            border-bottom-left-radius: 0;
            font-family: "Noto Sans Lao", sans-serif;
            transform: translateY(-100%);
        }


        @media only screen and (max-width: 800px) {
            #container {
                display: flex;
                flex-direction: column;
                flex-wrap: nowrap;
            }

            .dataBox {
                margin-top: 10px;
                margin-bottom: 0;
            }

            #ActiveUserGraph>div:nth-child(-n+8) {
                display: none;
            }

            #tooltip {
                border-bottom-left-radius: 15px;
            }
        }

        @media only screen and (max-width: 450px) {
            #ActiveUserGraph>div:nth-child(-n+12) {
                display: none;
            }
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

                    // SQL get all datapoints in the past 14 days
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

                    $tooltip_texts = new stdClass;
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
                            <div class="datapoint"">
                                <div class="GraphElement tooltipItem" style="height: <?php echo $thisValue?>%;" id="GraphPoint<?php echo $i; ?>"></div>
                                <div class="DateMarker"><?php echo $date; ?></div>
                            </div>
                        <?php echo ob_get_clean();
                            $tooltip_texts->{"GraphPoint" . $i} = $activityData[$date] . " Active users\non " . $date;
                        }
                    }
                    $tooltip_texts = json_encode($tooltip_texts);
                ?>
            </div>
        </div>
        <div id="PageHitsBox" class="dataBox">
            <h2 class="dataBoxTitle">Page Activity</h2>
            <span id="pageHitsMoreRows" style="display: none;">↓ Scroll for more rows ↓</span>
            <hr>
            <table>
                <tr>
                    <th>Page</th>
                    <th>Hits</th>
                </tr>
                <?php
                    $noPageViews = false;

                    // SQL get all datapoints in the last 7 days
                    $sql = "SELECT data FROM Events WHERE Time > DATE_SUB(NOW(), INTERVAL 7 DAY) AND type = 'pageView'";
                    $result = $conn->query($sql);
                    $events = array();

                    if ($result->num_rows > 0){
                        while($row = $result->fetch_assoc()) {
                            $events[] = json_decode($row["data"], true);
                        }
                    } else {
                        echo '<div class="NoData">No data found</div>';
                        $noPageViews = true;
                    }

                    // create an array with endpoints as keys and hits as value

                    $endpointHits = new stdClass;
                    foreach ($events as $event) {
                        if (!isset($event["page"])) continue;
                        if(!isset($endpointHits->{$event["page"]})) $endpointHits->{$event["page"]} = 0;

                        $endpointHits->{$event["page"]} += 1;
                    }
                    $endpointHits = (array) $endpointHits;
                    arsort($endpointHits);
                    echo "<script>console.log('Page hit data: ' + `" . print_r($endpointHits, true) . "`);</script>";

                    // loop over that array making new rows
                    foreach ($endpointHits as $endpoint => $hits) {
                        ob_start(); ?>
                        <tr>
                            <td><?php echo $endpoint; ?></td>
                            <td><?php echo $hits; ?></td>
                        </tr>
                    <?php echo ob_get_clean();
                    }
                ?>
            </table>
        </div>
        <div id="CustomEventsBox" class="dataBox">
            <h2 class="dataBoxTitle">Custom Events</h2>
            <hr>
            <div class="NoData">No data found</div>
        </div>
    </div>
    <div id="tooltip" style="opacity: 0;"></div>
    <script>
        const pageHitsBox = document.getElementById("PageHitsBox");
        if (pageHitsBox.scrollHeight > pageHitsBox.clientHeight) {
            document.getElementById("pageHitsMoreRows").style.display = "inline";
        }

        const tooltip_texts = <?php echo $tooltip_texts ?>

        const tooltip_items = document.getElementsByClassName("tooltipItem");

        const tooltip = document.getElementById("tooltip");

        for (let i = 0; i < tooltip_items.length; i++) {
            const tooltip_item = tooltip_items[i];

            tooltip_item.addEventListener("mouseenter", (event) => {
                if (tooltip_texts.hasOwnProperty(event.target.id)) tooltip.innerText = tooltip_texts[event.target.id];
                setTimeout(()=>{
                    tooltip.style.opacity = 1;
                }, 10);
            });

            tooltip_item.addEventListener("mouseleave", () => {
                tooltip.style.opacity = 0;
            });
        }

        document.addEventListener("mousemove", (event) => {
            let offset = "0";
            tooltip.style.borderBottomLeftRadius = "";
            if(window.innerWidth - event.clientX <= tooltip.offsetWidth) {
                offset = tooltip.offsetWidth + "px";
                tooltip.style.borderBottomLeftRadius = "15px";
            }
            tooltip.style.left = (event.clientX - offset) + "px";
            tooltip.style.top = event.clientY + "px";
        });

    </script>
</body>
</html>

<?php $conn->close(); ?>