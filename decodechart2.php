<?php

$css = "<defs><style type=\"text/css\"><![CDATA[
#ms{stroke:red;}
#cc{stroke:blue;}
#osrppdxc{stroke:green;}
]]></style></defs>";    

header('Content-type: image/svg+xml');
header('Vary: Accept-Encoding');

//get "Control Channel Message Decode Rate" lines from log files
$logfiles = "";
if (isset($_SERVER['QUERY_STRING']) && (strlen($_SERVER['QUERY_STRING']) > 4)) {
  foreach (glob("/home/wladcoat/trunk-build/logs/".$_SERVER['QUERY_STRING']."*.log") as $filename)
    $logfiles .= file_get_contents($filename);
  $mytitle = $_SERVER['QUERY_STRING'];
}
if (strlen($logfiles) < 1000) {
  foreach (glob("/home/wladcoat/trunk-build/logs/".date("m-d-Y")."*.log") as $filename)
    $logfiles .= file_get_contents($filename);
  foreach (glob("/home/wladcoat/trunk-build/logs/".date("m-d-Y", mktime(0, 0, 0, date("m"), date("d")-1, date("Y")))."*.log") as $filename)
    $logfiles .= file_get_contents($filename);
  $mytitle = "Last 24 hours";
}

//parse the log lines
preg_match_all("/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}).*\[(.*)\].*Control Channel Message Decode Rate: (.*)\/sec/", $logfiles, $decode);
unset($decode[0]);


//combine the log lines from different systems into one row per unique date/time
foreach ($decode[1] as $theentry => $datetime) {
//if (isset($decode[1][$theentry-1]) && ($datetime == $decode[1][$theentry-1]))
  $decode2[$decode[2][$theentry]][] = $decode[3][$theentry];
  if (!in_array($datetime, $decode2["times"]))
    $decode2["times"][] = $datetime;
}
unset($theentry, $datetime);

//var_dump($decode2);

//start the SVG
echo '<svg width="1500" height="250" viewBox="0 0 1500 250" version="1.0" xmlns="http://www.w3.org/2000/svg">
'.$css.'
<line style="stroke: black; stroke-width:2;" x1="20" x2="20" y1="0" y2="250" />
<line style="stroke: black; stroke-width:2;" x1="20" x2="1500" y1="250" y2="250" />';

//label the axis
$currenthour = "";
$currentx = 20;
if ($currenthour != substr($datetime,0,13)) {
  echo $currenthour;
  $currenthour = substr($datetime,0,13);
}

//plot the data
foreach ($decode2 as $system => $entries) {
  //only show last 24 hours' worth
  $entries = array_slice($entries, -1440);
  echo '
<polyline id="'.$system.'" style="fill:none; stroke-width:2"
points="';
  foreach ($entries as $datarow => $datavalue)
    echo ($datarow+20).",".((40 - $datavalue)*5)." ";
  echo '" />';
}
echo '</svg>';
?>