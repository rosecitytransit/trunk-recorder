<?php error_reporting(0);
$starttime=time();
//$garage = false;

//$fp = fopen('/var/www/html/radio/radios','r+');
$radiofile = file_get_contents('/var/www/html/radio/radios');
//while (!feof($fp))
//	$radiofile .= fread($fp, 4000);
$radiolist = unserialize($radiofile);
$radiolist[$argv[1]] = $argv[2]. " ".$starttime;
file_put_contents('/var/www/html/radio/radios',serialize($radiolist));
//fwrite($fp, serialize($radiolist));
//fclose($fp);


if (($argv[1] > 2000) && ($argv[1] < 8000)) {

sleep(30);
$inservice = "all";
include('/var/www/html/systemmapper/cache.php');

foreach ($resultSet->vehicle as $veh) {
	if ($veh['vehicleID'] == $argv[1]) {
		if (((time() - (int)substr($veh['time'],0,-3)) < 92) &&
 (($veh['longitude'] <= -122.6482) || ($veh['longitude'] > -122.6464) || ($veh['latitude'] > 45.4936) || ($veh['latitude'] < 45.4904)) &&
 (($veh['longitude'] < -122.565) || ($veh['longitude'] > -122.5617) || ($veh['latitude'] > 45.4963) || ($veh['latitude'] < 45.4937)) &&
 (($veh['longitude'] < -122.8457) || ($veh['longitude'] > -122.8422) || ($veh['latitude'] > 45.5048) || ($veh['latitude'] < 45.5018)) ) {

$tglist = array(17000=>'AdminAnnc', 17005=>'AdHoc1', 17010=>'AdHoc2', 17015=>'AdHoc3', 17020=>'AdHoc4', 17025=>'AdHoc5', 17030=>'AdHoc6',
17035=>'AdHoc7', 17040=>'AdHoc8', 17045=>'AdHoc9', 17050=>'AdHoc10', 17055=>'AdHoc11', 17100=>'Announce', 17105=>'Fallback0', 17110=>'Fallback1',
17115=>'Fallback2', 17120=>'Fallback3', 17125=>'Fallback4', 17205=>'EMERGNCY', 17305=>'Default');

			if (isset($tglist[$argv[2]])) $talkgroup = $tglist[$argv[2]]; else $talkgroup = $argv[2];

			//if ( @file_exists("unitreg.txt")) {
			$fp = fopen("/var/www/html/radio/unitreg-".date("mdy").".txt",'a+');
			$output = $argv[1]." ".$veh['routeNumber']." ".$veh['direction']." ".$talkgroup." ".$veh['latitude']." ".$veh['longitude']." ".date("H:i:s", $starttime)." ".((int)substr($veh['time'],0,-3) - $starttime);


		if ($argv[2] == "17105")
			$output .= " small_yellow";
		elseif ($argv[2] == "17305")
			$output .= " small_green";
		elseif ($argv[2] == "on")
			$output .= " small_blue";
		elseif ($argv[2] == "off")
			$output .= " measle_brown";
		elseif ($argv[2] == "ackresp")
			$output .= " small_purple";


			$pos = -1; $line = ''; $c = '';
			do {
				$line = $c . $line;
				fseek($fp, $pos--, SEEK_END);
				$c = fgetc($fp);
			} while (($c !== false) && ($c != "\n"));

			if ($line !== $output)
				fwrite ($fp, "
".$output);
			fclose($fp);
			//}
		} else {
			$fp = fopen("/var/www/html/radio/unitreg-".date("mdy")."-bad.txt",'a');
			fwrite ($fp, "
".$argv[1]." ".$veh['routeNumber']." ".$veh['direction']." ".$argv[2]." ".$veh['latitude']." ".$veh['longitude']." ".date("H:i:s", $starttime)." ".date("H:i:s", substr($veh['time'],0,-3)));
			fclose($fp);
		}
		break;
	}
} }
?>