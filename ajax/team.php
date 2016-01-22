<?php
error_reporting(0);
if (isset($_POST["get"]) && !empty($_POST["get"])) { //Checks if action value exists
    $action = $_POST["get"];
    switch($action) { //Switch case for value of action
        case "team": team_function(); break;
    }
}

function team_function(){
include('../include/config.php');
$dag1 = date("Ymd", mktime(0, 0, 0, date("m") , date("d") -40 , date("Y")));

$query="
SELECT `Team`, count(`Team`) as aantal FROM `whatpulse_data` where `datum` = '".$dag1."' group by `Team` order by Team asc
";

$result = $mysqli->query($query) or die($mysqli->error.__LINE__);

$Uname = array();
while( $row = $result->fetch_assoc() ) {
	if ($row['Team'] == "-") {} else {
		$row['Teamname'] = substr($row['Team'], 1, -1);
		$Uname[] = $row;
	}
}

# JSON-encode the response
$json_response = json_encode($Uname, JSON_NUMERIC_CHECK);

// # Return the response
echo $json_response;
}
?>