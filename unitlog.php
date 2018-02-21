<?php //error_reporting(0);
/* $inservice = "all";
include('/var/www/html/systemmapper/cache.php');

$unitlog = file('/var/www/html/radio/unitlog.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$tglist = array(17000=>'AdminAnnc', 17005=>'AdHoc1', 17010=>'AdHoc2', 17015=>'AdHoc3', 17020=>'AdHoc4', 17025=>'AdHoc5', 17030=>'AdHoc6',
17035=>'AdHoc7', 17040=>'AdHoc8', 17045=>'AdHoc9', 17050=>'AdHoc10', 17055=>'AdHoc11', 17100=>'Announce', 17105=>'Fallback0', 17110=>'Fallback1',
17115=>'Fallback2', 17120=>'Fallback3', 17125=>'Fallback4', 17205=>'EMERGNCY', 17305=>'Default');

foreach ($unitlog as $unitentry) {
  $radio = substr($unitentry, 0, strpos($unitentry, ":"))
    if (($radio > 1000) && ($radio < 8000)) {
      foreach ($resultSet->vehicle as $veh) { //array_search
        if (($veh['vehicleID'] == $radio) &&
 ((($veh['longitude'] <= -122.6482) || ($veh['longitude'] > -122.6464)) && (($veh['latitude'] > 45.4904) || ($veh['latitude'] < 45.4917))) &&
 ((($veh['longitude'] < -122.565) || ($veh['longitude'] > -122.5617)) && (($veh['latitude'] > 45.4963) || ($veh['latitude'] < 45.4937))) &&
 ((($veh['longitude'] < -122.8457) || ($veh['longitude'] > -122.8422)) && (($veh['latitude'] > 45.5048) || ($veh['latitude'] < 45.5018)))  ) {

  $tg = substr($unitentry, strpos($unitentry, ":"))
  if (isset($tglist[$tg])) $entry = $tglist[$tg]; else $entry = $tg;

  $entry .= " ".round($veh['latitude'], 3)." ".round($veh['longitude'], 3)."
";




		//if ( @file_exists("unitreg.txt")) {
		$fp = fopen("/var/www/html/radio/unitreg.txt",'a');
		fwrite ($fp, $argv[1]." ".$veh['block']." ".$talkgroup." ".$veh['longitude']." ".$veh['latitude']." ".date("Y-m-d H:i:s")."
");
		fclose($fp);
		//}
		break;
	}
} */
?>