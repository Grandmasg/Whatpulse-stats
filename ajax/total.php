<?php
error_reporting(0);
if (isset($_POST["get"]) && !empty($_POST["get"])) { //Checks if action value exists
    $action = $_POST["get"];
    switch($action) { //Switch case for value of action
        case "total": total_function(); break;
    }
}

function total_function(){
include('../include/config.php');
if( isset($_POST['Team']) ) {
    $Team = $_POST[Team];
    $search = "[$Team]";
} else {
    $Team = "";
    $search = "";
}
if( isset($_POST['Offset']) ) { $Offset = $_POST['Offset']; } else { $Offset = 0; }
$dag = date("Ymd", mktime(0, 0, 0, date("m") , date("d") + $Offset, date("Y")));
$dag1 = date("Ymd", mktime(0, 0, 0, date("m") , date("d") + $Offset - 1 , date("Y")));

$query="
SELECT UserID, Username, ifnull(Keys1,0) AS diffrence_keys
FROM whatpulse_data
WHERE datum = '$dag1' AND Username LIKE '%$search%'
ORDER BY diffrence_keys DESC, Clicks DESC
";
$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
$i = 1;
$arr = array();
if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $totalflushgister[$row['UserID']] = $i;
        $i++;
    }
}

$query="
select * from whatpulse_data WHERE datum = '$dag' AND Username LIKE '%$search%'
GROUP BY UserID
ORDER BY Keys1 DESC, Clicks DESC
";
$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
$i = 1;
$arr = array();
if($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
            $letter_position1 = strpos($row['Username'], "[", 0);
            $letter_position2 = strpos($row['Username'], "]", 0);
            if (strpos($row['Username'], "[", 0) >= 1) {
                $row['Username'] = substr($row['Username'], $letter_position1);
                $letter_position1 = strpos($row['Username'], "[", 0);
                $letter_position2 = strpos($row['Username'], "]", 0);
            }
            $naam = substr($row['Username'], $letter_position1, $letter_position2+1);
        if ($letter_position1 !== false && $letter_position2 !== false ) {
            $row['Username'] = str_replace($naam, "", $row['Username']);
            $row['Username'] = trim($row['Username']);
        } else {
            $row['Username'] = trim($row['Username']);
        }
        $row['today'] = $i;
        $row['yesterday'] = $totalflushgister[$row['UserID']];
        $arr[] = $row;
        $i++;
    }
}
# JSON-encode the response
$json_response = json_encode($arr, JSON_NUMERIC_CHECK);

// # Return the response
echo $json_response;
}
?>