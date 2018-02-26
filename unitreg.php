<?php //error_reporting(0);
$inservice = "all";
include('/var/www/html/systemmapper/cache.php');
foreach ($resultSet->vehicle as $veh) {
	if (($veh['vehicleID'] == $argv[1]) &&
 ((($veh['longitude'] <= -122.6482) || ($veh['longitude'] > -122.6464)) && (($veh['latitude'] > 45.4904) || ($veh['latitude'] < 45.4917))) &&
 ((($veh['longitude'] < -122.565) || ($veh['longitude'] > -122.5617)) && (($veh['latitude'] > 45.4963) || ($veh['latitude'] < 45.4937))) &&
 ((($veh['longitude'] < -122.8457) || ($veh['longitude'] > -122.8422)) && (($veh['latitude'] > 45.5048) || ($veh['latitude'] < 45.5018)))
	) {
$tglist = array(17000=>'AdminAnnc', 17005=>'AdHoc1', 17010=>'AdHoc2', 17015=>'AdHoc3', 17020=>'AdHoc4', 17025=>'AdHoc5', 17030=>'AdHoc6',
17035=>'AdHoc7', 17040=>'AdHoc8', 17045=>'AdHoc9', 17050=>'AdHoc10', 17055=>'AdHoc11', 17100=>'Announce', 17105=>'Fallback0', 17110=>'Fallback1',
17115=>'Fallback2', 17120=>'Fallback3', 17125=>'Fallback4', 17205=>'EMERGNCY', 17305=>'Default');

		if (isset($tglist[$argv[2]])) $talkgroup = $tglist[$argv[2]]; else $talkgroup = $argv[2];

		//if ( @file_exists("unitreg.txt")) {
		$fp = fopen("/var/www/html/radio/unitreg-".date("mdy").".txt",'a');
		fwrite ($fp, $argv[1]." ".$veh['routeNumber']." ".$veh['direction']." ".$talkgroup." ".$veh['latitude']." ".$veh['longitude']." ".date("H:i:s")."
");
		fclose($fp);
		//}
		break;
	}
}
?>