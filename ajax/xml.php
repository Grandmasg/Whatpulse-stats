<?php
error_reporting(0);
if (isset($_POST["get"]) && !empty($_POST["get"])) { //Checks if action value exists
    $action = $_POST["get"];
    switch($action) { //Switch case for value of action
        case "xml": xml_function(); break;
    }
}

function addDotsKM( $num, $added ) {
    if ($num=="" || $num=="0") {return '-';} else {
		if ($num > 1000000) {
			$num = $num / 1000000;
			$added = " M";
		} else if ($num > 1000) {
			$num = $num / 1000;
			$added = " K";
		}
        $num = trim($num);
        return number_format($num,0,'.','.').$added;
    }
}
function addDotssec( $num, $added ) {
    if ($num=="" || $num=="0") {return '-';} else {
		if ($added == 'min') {
			$num = $num / 60;
		} else if ($added == 'uur') {
			$num = $num / (60*60);
		}
		if ($added) {
			$added = ' '.$added;	
		}
        $num = trim($num);
        return number_format($num,0,'.','.').$added;
    }
}
function addDotsGB( $num, $added ) {
    if ($num=="" || $num=="0") {return '-';} else {
		if ($num > (1024*1024)) {
			$num = $num / (1024*1024);
			$added = " TB";
		} else if ($num > 1024) {
			$num = $num / 1024;
			$added = " GB";
		} else if ($num < 1024) {
			$added = " MB";	
		}
        $num = trim($num);
        return number_format($num,0,'.','.').$added;
    }
}
function addDots( $num, $added ) {
    if ($num=="" || $num=="0") {return '-';} else {
        $num = trim($num);
		if ($added) {
			$added = ' '.$added;	
		}
        return number_format($num,0,'.','.').$added;
    }
}
function addColorDots( $num, $added ) {
    if ($num==0) { $num = "(-)"; } else {
        if ($num > 0) {
            $num = "[green] (+".addDots($num).") ".$added."[/]"; 
        }
        if ($num < 0) {
            $num = "[red] (".addDots($num).") ".$added."[/]"; 
        }
    }
    return $num;
}

function xml_function(){
include('../include/config.php');
if( isset($_POST['Team']) ) {
    $Team = $_POST[Team];
    if ($Team == 'wp' || $Team == 'geil') { $Team = '#'.$Team; }
    $search = "[$Team]";
} else {
    $Team = "";
    $search = "";
}
if( isset($_POST['Offset']) && !empty($_POST["Offset"]) ) { $Offset = $_POST['Offset']; } else { $Offset = 0; }
if ($Offset >= 0) {echo "No stats today";} else {
$dag = date("Ymd", mktime(0, 0, 0, date("m") , date("d") + $Offset, date("Y")));
$dag1 = date("Ymd", mktime(0, 0, 0, date("m") , date("d") + $Offset - 1, date("Y")));
$dag2 = date("Ymd", mktime(0, 0, 0, date("m") , date("d") + $Offset - 2 , date("Y")));

$daglang = date("Y-m-d", mktime(0, 0, 0, date("m") , date("d") + $Offset , date("Y")));

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
SELECT s1.UserID as UserIDT, s1.Username as UsernameT, s1.Keys1 as Keys1T, s1.Clicks as ClicksT, s1.DownloadMB as DownloadMBT, s1.UploadMB as UploadMBT, s1.UptimeSeconds as UptimeSecondsT,
    ifnull(s1.Keys1,0) - ifnull(s2.Keys1,0) AS diffrence_keys, 
    ifnull(s1.Clicks,0) - ifnull(s2.Clicks,0) AS diffrence_clicks,
    ifnull(s1.DownloadMB,0) - ifnull(s2.DownloadMB,0) AS diffrence_DownloadMB,
    ifnull(s1.UploadMB,0) - ifnull(s2.UploadMB,0) AS diffrence_UploadMB,
    s1.UptimeSeconds - s2.UptimeSeconds AS diffrence_UptimeSeconds,
    s1.Rank_Keys AS Rank_Keys_today, s2.Rank_Keys AS Rank_Keys_yesterday,
    s2.Rank_Keys - s1.Rank_Keys AS diffrence_Rank_Keys,
    s1.Rank_Clicks AS Rank_Clicks_today, s2.Rank_Clicks AS Rank_Clicks_yesterday,
    s2.Rank_Clicks - s1.Rank_Clicks AS diffrence_Rank_Clicks,
    s1.Rank_Download AS Rank_Download_today, s2.Rank_Download AS Rank_Download_yesterday,
    s2.Rank_Download - s1.Rank_Download AS diffrence_Rank_Download,
    s1.Rank_Upload AS Rank_Upload_today, s2.Rank_Upload AS Rank_Upload_yesterday,
    s2.Rank_Upload - s1.Rank_Upload AS diffrence_Rank_Upload,
    s1.Rank_Uptime AS Rank_Uptime_today, s2.Rank_Uptime AS Rank_Uptime_yesterday,
    s2.Rank_Uptime - s1.Rank_Uptime AS diffrence_Rank_Uptime,
    s1.Pulses AS PulsesT, s2.Pulses AS PulsesY,
    s1.Pulses - s2.Pulses AS diffrence_Pulses,
    s1.LastPulse,
    s1.Team
    FROM whatpulse_data AS s1, whatpulse_data AS s2
    WHERE s1.UserID = s2.UserID and s1.datum = '".$dag."' AND s2.datum = '".$dag1."' AND s1.Username LIKE '%".$search."%'
    GROUP BY s1.UserID
    ORDER BY Keys1T DESC, ClicksT DESC
";
$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
$i = 1;
$arr = array();
if($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
            $letter_position1 = strpos($row['UsernameT'], "[", 0);
            $letter_position2 = strpos($row['UsernameT'], "]", 0);
            if (strpos($row['Username'], "[", 0) >= 1) {
                $row['Username'] = substr($row['UsernameT'], $letter_position1);
                $letter_position1 = strpos($row['UsernameT'], "[", 0);
                $letter_position2 = strpos($row['UsernameT'], "]", 0);
            }
            $naam = substr($row['Username'], $letter_position1, $letter_position2+1);
            if ($letter_position1 !== false && $letter_position2 !== false ) {
                if ($letter_position1 > 0) { $naam = substr($row['UsernameT'], $letter_position1); }
                $row['UsernameT'] = str_replace($naam, "", $row['UsernameT']);
                $row['Team'] = $naam;
                $row['UsernameT'] = trim($row['UsernameT']);
            } else {
                $row['UsernameT'] = trim($row['UsernameT']);
            }
            $row['today'] = $i;
            $row['yesterday'] = $dailyflushgister[$row['UserID']];
            $arr[] = $row;
        $i++;
	}
}

$query="
SELECT Username
FROM whatpulse_data
WHERE datum = '$dag' AND Username LIKE '%".$search."%'
";

$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
$aantalA=0;
$arrayA = array();
if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
		array_push ($arrayA, array("Username" => $row['Username']) );
		$aantalA++;
    }
}

$query="
SELECT Username
FROM whatpulse_data
WHERE datum = '$dag1' AND Username LIKE '%".$search."%'
";

$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
$aantalB=0;
$arrayB = array();
if($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		array_push ($arrayB, array("Username" => $row['Username']) );
		$aantalB++;
    }
}

foreach($arrayA as $num => $values){
    $Usernamea[] = $values['Username'];
}

foreach($arrayB as $num => $values){
    $Usernameb[] = $values['Username'];
}

$resultadded = array_diff($Usernamea,$Usernameb);
$resultleft = array_diff($Usernameb,$Usernamea);

$query="
SELECT s1.Team, sum(s1.Keys1) as Keys1T, sum(s1.Clicks) as ClicksT, sum(s1.DownloadMB) as DownloadMBT, sum(s1.UploadMB) as UploadMBT, sum(s1.UptimeSeconds) as UptimeSecondsT,
    sum(ifnull(s1.Keys1,0) - ifnull(s2.Keys1,0)) AS diffrence_keys, 
    sum(ifnull(s1.Clicks,0) - ifnull(s2.Clicks,0)) AS diffrence_clicks,
    sum(ifnull(s1.DownloadMB,0) - ifnull(s2.DownloadMB,0)) AS diffrence_DownloadMB,
    sum(ifnull(s1.UploadMB,0) - ifnull(s2.UploadMB,0)) AS diffrence_UploadMB,
    sum(s1.UptimeSeconds - s2.UptimeSeconds) AS diffrence_UptimeSeconds,
    sum(s1.Pulses) AS PulsesT, sum(s2.Pulses) AS PulsesY,
    sum(s1.Pulses - s2.Pulses) AS diffrence_Pulses
    FROM whatpulse_data AS s1, whatpulse_data AS s2
    WHERE s1.UserID = s2.UserID and s1.datum = '".$dag."' AND s2.datum = '".$dag1."' AND s1.Username LIKE '%".$search."%'
    GROUP BY s1.Team
    ORDER BY diffrence_keys DESC, Keys1T DESC
";

$result = $mysqli->query($query) or die($mysqli->error.__LINE__);
$teamC=0;
$Tarr = array();
if($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		array_push ($Tarr, array($row));
		$teamC++;
    }
}
$teamC--;
$teamC--;

$aantal = 0;
foreach($arr as $num => $values) {
    $aantal++;
	$Keys1T += $values['Keys1T'];
	$ClicksT += $values['ClicksT'];
	$DownloadMBT += $values['DownloadMBT'];
	$UploadMBT += $values['UploadMBT'];
	$UptimeSecondsT += $values['UptimeSecondsT'];
	$PulsesT += $values['PulsesT'];
    $diffrence_keys += $values['diffrence_keys'];
    $diffrence_clicks += $values['diffrence_clicks'];
    $diffrence_DownloadMB += $values['diffrence_DownloadMB'];
    $diffrence_UploadMB += $values['diffrence_UploadMB'];
    $diffrence_UptimeSeconds += $values['diffrence_UptimeSeconds'];    
	$diffrence_Pulses += $values['diffrence_Pulses'];
}

//XML daily
$xml ="[nosmilies]
[table border=1 width=100% fontsize=11]
[tr][th colspan=2][h1]Stats - ".$daglang."[/][/][/]
[tr][th]Team[/][td]Dutch Power Cows[/][/]
[tr][th]Keys[/][td]".addDots($Keys1T)." ".addColorDots($diffrence_keys)."[/][/]
[tr][th]Clicks[/][td]".addDots($ClicksT)." ".addColorDots($diffrence_clicks)."[/][/]
[tr][th]Download[/][td]".addDots($DownloadMBT, 'MB')." ".addColorDots($diffrence_DownloadMB, 'MB')."[/][/]
[tr][th]Upload[/][td]".addDots($UploadMBT, 'MB')." ".addColorDots($diffrence_UploadMB, 'MB')."[/][/]
[tr][th]Uptime[/][td]".addDots($UptimeSecondsT, 'sec')." ".addColorDots($diffrence_UptimeSeconds, 'sec')."[/][/]
[tr][th]Pulses[/][td]".addDots($PulsesT)." ".addColorDots($diffrence_Pulses)."[/][/]
[tr][th]Members[/][td]".addDots($aantal)." ".addColorDots($aantalA-$aantalB)."[/][/]
[/][hr]";

//Team
$xml .= "[table border=1 width=100% fontsize=11]
[tr][th colspan=8][h1]Subteams[/][/][/]
[tr][th]#[/][th]Team[/][th]Keys[/][th]Clicks[/][th]Download[/][th]Upload[/][th]Uptime[/][/]
";

for ($i = 0; $i < 16; $i++) {
	$j++;
	if ($Tarr[$i][0]['Team'] == '-') {$i++;}
	$xml .="[tr][td]".$j."[/][td]".$Tarr[$i][0]['Team']."[/][td][blue]".addDotsKM($Tarr[$i][0]['Keys1T'])."[/] ".addColorDots($Tarr[$i][0]['diffrence_keys'])."[/]"."[td]".addDotsKM($Tarr[$i][0]['ClicksT'])." ".addColorDots($Tarr[$i][0]['diffrence_clicks'])."[/][td]".addDotsGB($Tarr[$i][0]['DownloadMBT'])." ".addColorDots($Tarr[$i][0]['diffrence_DownloadMB'], 'MB')."[/][td]".addDotsGB($Tarr[$i][0]['UploadMBT'])." ".addColorDots($Tarr[$i][0]['diffrence_UploadMB'], 'MB')."[/][td]".addDotssec($Tarr[$i][0]['UptimeSecondsT'], 'uur')." ".addColorDots($Tarr[$i][0]['diffrence_UptimeSeconds'], 'sec')."[/][/]\n";
}

$xml .= "[/]Totaal zijn er [b]".$teamC." subteams[/] geregistreerd![hr]";


//Keys
$xml .= "[table border=1 width=100% fontsize=11]
[tr][th colspan=8][h1]Top 25 Keys[/][/][/]
[tr][th]#[/][th]Rank[/][th]User[/][th]Keys[/][th]Clicks[/][/]
";

for ($i = 0; $i < 25; $i++) {
	$j=$i+1;
	$xml .="[tr][td]".$j."[/][td]".addDots($arr[$i]['Rank_Keys_today'])." ".addColorDots($arr[$i]['diffrence_Rank_Keys'])."[/][td]".$arr[$i]['UsernameT']."[/][td][blue]".addDots($arr[$i]['Keys1T'])."[/] ".addColorDots($arr[$i]['diffrence_keys'])."[/]"."[td]".addDots($arr[$i]['ClicksT'])." ".addColorDots($arr[$i]['diffrence_clicks'])."[/][/]\n";
}

$xml .= "[/][hr]";

//Keys
function keySort($item1,$item2) {
    if ($item1['diffrence_keys'] == $item2['diffrence_keys']) {
        return $item1['diffrence_clicks'] < $item2['diffrence_clicks'] ? 1 : -1;
    } else {
        return ($item1['diffrence_keys'] < $item2['diffrence_keys']) ? 1 : -1;
    }
}
usort($arr,'keySort');

$xml .= "[table border=1 width=100% fontsize=11]
[tr][th colspan=8][h1]Ticking goat[/][/][/]
[tr][th]#[/][th]Rank[/][th]User[/][th]Keys[/][th]Clicks[/][/]
";

for ($i = 0; $i < 15; $i++) {
	$j=$i+1;
	$xml .="[tr][td]".$j."[/][td]".addDots($arr[$i]['Rank_Keys_today'])." ".addColorDots($arr[$i]['diffrence_Rank_Keys'])."[/][td]".$arr[$i]['UsernameT']."[/][td][blue]".addDots($arr[$i]['Keys1T'])."[/] ".addColorDots($arr[$i]['diffrence_keys'])."[/]"."[td]".addDots($arr[$i]['ClicksT'])." ".addColorDots($arr[$i]['diffrence_clicks'])."[/][/]\n";
}

$xml .= "[/][hr]";

//Clicks
function clickSort($item1,$item2) {
    if ($item1['diffrence_clicks'] == $item2['diffrence_clicks']) {
        return $item1['diffrence_keys'] < $item2['diffrence_keys'] ? 1 : -1;
    } else {
        return ($item1['diffrence_clicks'] < $item2['diffrence_clicks']) ? 1 : -1;
    }
}
usort($arr,'clickSort');

$xml .= "[table border=1 width=100% fontsize=11]
[tr][th colspan=8][h1]Mouse killer[/][/][/]
[tr][th]#[/][th]Rank[/][th]User[/][th]Keys[/][th]Clicks[/][/]
";

for ($i = 0; $i < 15; $i++) {
	$j=$i+1;
	$xml .="[tr][td]".$j."[/][td]".addDots($arr[$i]['Rank_Clicks_today'])." ".addColorDots($arr[$i]['diffrence_Rank_Clicks'])."[/][td]".$arr[$i]['UsernameT']."[/][td]".addDots($arr[$i]['Keys1T'])." ".addColorDots($arr[$i]['diffrence_keys'])."[/]"."[td][blue]".addDots($arr[$i]['ClicksT'])."[/] ".addColorDots($arr[$i]['diffrence_clicks'])."[/][/]\n";
}

$xml .= "[/][hr]";

//Download
function downloadSort($item1,$item2) {
    if ($item1['diffrence_DownloadMB'] == $item2['diffrence_DownloadMB']) return 0;
    return ($item1['diffrence_DownloadMB'] < $item2['diffrence_DownloadMB']) ? 1 : -1;
}
usort($arr,'downloadSort');

$xml .= "[table border=1 width=100% fontsize=11]
[tr][th colspan=8][h1]Bytes guzzler[/][/][/]
[tr][th]#[/][th]Rank[/][th]User[/][th]Download[/][th]Upload[/][th]Uptime[/][/]
";

for ($i = 0; $i < 15; $i++) {
	$j=$i+1;
	$xml .="[tr][td]".$j."[/][td]".addDots($arr[$i]['Rank_Download_today'])." ".addColorDots($arr[$i]['diffrence_Rank_Download'])."[/][td]".$arr[$i]['UsernameT']."[/][td][blue]".addDotsGB($arr[$i]['DownloadMBT'])."[/] ".addColorDots($arr[$i]['diffrence_DownloadMB'], 'MB')."[/][td]".addDotsGB($arr[$i]['UploadMBT'])." ".addColorDots($arr[$i]['diffrence_UploadMB'], 'MB')."[/][td]".addDotssec($arr[$i]['UptimeSecondsT'], 'uur')." ".addColorDots($arr[$i]['diffrence_UptimeSeconds'], 'sec')."[/][/]\n";
}

$xml .= "[/][hr]";

//Upload
function uploadSort($item1,$item2) {
    if ($item1['diffrence_UploadMB'] == $item2['diffrence_UploadMB']) return 0;
    return ($item1['diffrence_UploadMB'] < $item2['diffrence_UploadMB']) ? 1 : -1;
}
usort($arr,'uploadSort');

$xml .= "[table border=1 width=100% fontsize=11]
[tr][th colspan=8][h1]Bytes pusher[/][/][/]
[tr][th]#[/][th]Rank[/][th]User[/][th]Download[/][th]Upload[/][th]Uptime[/][/]
";

for ($i = 0; $i < 15; $i++) {
	$j=$i+1;
	$xml .="[tr][td]".$j."[/][td]".addDots($arr[$i]['Rank_Upload_today'])." ".addColorDots($arr[$i]['diffrence_Rank_Upload'])."[/][td]".$arr[$i]['UsernameT']."[/][td]".addDotsGB($arr[$i]['DownloadMBT'])." ".addColorDots($arr[$i]['diffrence_DownloadMB'], 'MB')."[/][td][blue]".addDotsGB($arr[$i]['UploadMBT'])."[/] ".addColorDots($arr[$i]['diffrence_UploadMB'], 'MB')."[/][td]".addDotssec($arr[$i]['UptimeSecondsT'], 'uur')." ".addColorDots($arr[$i]['diffrence_UptimeSeconds'], 'sec')."[/][/]\n";
}

$xml .= "[/][hr]";

//Uptime
function uptimeSort($item1,$item2) {
    if ($item1['diffrence_UptimeSeconds'] == $item2['diffrence_UptimeSeconds']) return 0;
    return ($item1['diffrence_UptimeSeconds'] < $item2['diffrence_UptimeSeconds']) ? 1 : -1;
}
usort($arr,'uptimeSort');

$xml .= "[table border=1 width=100% fontsize=11]
[tr][th colspan=8][h1]ˈʌpˌtaɪm[/][/][/]
[tr][th]#[/][th]Rank[/][th]User[/][th]Download[/][th]Upload[/][th]Uptime[/][/]
";

for ($i = 0; $i < 15; $i++) {
	$j=$i+1;
	$xml .="[tr][td]".$j."[/][td]".addDots($arr[$i]['Rank_Upload_today'])." ".addColorDots($arr[$i]['diffrence_Rank_Upload'])."[/][td]".$arr[$i]['UsernameT']."[/][td]".addDotsGB($arr[$i]['DownloadMBT'])." ".addColorDots($arr[$i]['diffrence_DownloadMB'], 'MB')."[/][td]".addDotsGB($arr[$i]['UploadMBT'])." ".addColorDots($arr[$i]['diffrence_UploadMB'], 'MB')."[/][td][blue]".addDotssec($arr[$i]['UptimeSecondsT'], 'uur')."[/] ".addColorDots($arr[$i]['diffrence_UptimeSeconds'], 'sec')."[/][/]\n";
}

$xml .= "[/][hr]";

//end
$xml .= "[/]";
$xml = str_replace("[", "&#091;", $xml);
$xml = str_replace("]", "&#093;", $xml);
echo $xml;
}
}
?>