<?php
error_reporting(0);
if (isset($_POST["get"]) && !empty($_POST["get"])) { //Checks if action value exists
    $action = $_POST["get"];
    switch($action) { //Switch case for value of action
        case "daily": daily_function(); break;
    }
}

function daily_function(){
include('../include/config.php');
if( isset($_POST['Team']) && !empty($_POST["Team"])) {
    $Team = $_POST[Team];
    $search = "[$Team]";
} else { 
    $Team = ""; 
    $search = "";
}
if( isset($_POST['Offset']) && !empty($_POST["Offset"]) ) { $Offset = $_POST['Offset']; } else { $Offset = 0; }
$dag = date("Ymd", mktime(0, 0, 0, date("m") , date("d") + $Offset, date("Y")));
$dag1 = date("Ymd", mktime(0, 0, 0, date("m") , date("d") + $Offset - 1 , date("Y")));
$dag2 = date("Ymd", mktime(0, 0, 0, date("m") , date("d") + $Offset - 2 , date("Y")));

$query="
SELECT s1.UserID, ifnull(s1.Keys1,0) - ifnull(s2.Keys1,0) AS diffrence_keys, ifnull(s1.Clicks,0) - ifnull(s2.Clicks,0) AS diffrence_clicks
FROM whatpulse_data AS s1, whatpulse_data AS s2
WHERE s1.UserID = s2.userID and s1.datum = '".$dag1."'  AND s2.datum = '".$dag2."'
ORDER  BY diffrence_keys DESC, diffrence_clicks DESC
";

$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
$i = 1;
if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if ($row['diffrence_keys'] > 0 && $row['diffrence_clicks'] > 0) {
            $dailyflushgister[$row['UserID']] = $i;
        }
        $i++;
    }
}

$query="
SELECT s1.UserID, s1.Username,
    ifnull(s1.Keys1,0) - ifnull(s2.Keys1,0) AS diffrence_keys, 
    ifnull(s1.Clicks,0) - ifnull(s2.Clicks,0) AS diffrence_clicks,
    ifnull(s1.DownloadMB,0) - ifnull(s2.DownloadMB,0) AS diffrence_DownloadMB,
    ifnull(s1.UploadMB,0) - ifnull(s2.UploadMB,0) AS diffrence_UploadMB,
    s1.UptimeSeconds - s2.UptimeSeconds AS diffrence_UptimeSeconds,
    s1.Rank_Keys AS Rank_Keys_today, s2.Rank_Keys AS Rank_Keys_yesterday,
    s1.Rank_Keys - s2.Rank_Keys AS diffrence_Rank_Keys,
    s1.Rank_Clicks AS Rank_Clicks_today, s2.Rank_Clicks AS Rank_Clicks_yesterday,
    s1.Rank_Clicks - s2.Rank_Clicks AS diffrence_Rank_Clicks,
    s1.Rank_Download AS Rank_Download_today, s2.Rank_Download AS Rank_Download_yesterday,
    s1.Rank_Download - s2.Rank_Download AS diffrence_Rank_Download,
    s1.Rank_Upload AS Rank_Upload_today, s2.Rank_Upload AS Rank_Upload_yesterday,
    s1.Rank_Upload - s2.Rank_Upload AS diffrence_Rank_Upload,
    s1.Rank_Uptime AS Rank_Uptime_today, s2.Rank_Uptime AS Rank_Uptime_yesterday,
    s1.Rank_Uptime - s2.Rank_Uptime AS diffrence_Rank_Uptime,
    s1.Pulses AS Pulses_today, s2.Pulses AS Pulses_yesterday,
    s1.Pulses - s2.Pulses AS diffrence_Pulses,
    s1.LastPulse,
    s1.Team
    FROM whatpulse_data AS s1, whatpulse_data AS s2
    WHERE s1.UserID = s2.UserID and s1.datum = '".$dag."' AND s2.datum = '".$dag1."' AND s1.Username LIKE '%".$search."%'
    GROUP BY s1.UserID
    ORDER BY diffrence_keys DESC, diffrence_clicks DESC
";
$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
$i = 1;
$arr = array();
if($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
        if ($row['diffrence_keys'] > 0 || $row['diffrence_clicks'] > 0) {
            $letter_position1 = strpos($row['Username'], "[", 0);
            $letter_position2 = strpos($row['Username'], "]", 0);
            if (strpos($row['Username'], "[", 0) >= 1) {
                $row['Username'] = substr($row['Username'], $letter_position1);
                $letter_position1 = strpos($row['Username'], "[", 0);
                $letter_position2 = strpos($row['Username'], "]", 0);
            }
            $naam = substr($row['Username'], $letter_position1, $letter_position2+1);
            if ($letter_position1 !== false && $letter_position2 !== false ) {
                if ($letter_position1 > 0) { $naam = substr($row['Username'], $letter_position1); }
                $row['Username'] = str_replace($naam, "", $row['Username']);
                $row['Team'] = $naam;
                $row['Username'] = trim($row['Username']);
            } else {
                $row['Username'] = trim($row['Username']);
            }
            $row['today'] = $i;
            $row['yesterday'] = $dailyflushgister[$row['UserID']];
            $arr[] = $row;
        }
        $i++;
	}
}
# JSON-encode the response
$json_response = json_encode($arr, JSON_NUMERIC_CHECK);

// # Return the response
echo $json_response;

}
?>