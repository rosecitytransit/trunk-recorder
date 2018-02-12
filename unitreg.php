<?php error_reporting(0);
$inservice = "all";
include('/var/www/html/systemmapper/cache.php');
foreach ($resultSet->vehicle as $vehicle) {
	if ($vehicle['vehicleID'] == $argv[1]) {
		//if ( @file_exists("unitreg.txt")) {
		$fp = fopen("unitreg.txt",'a');
		fwrite ($fp, $argv[1]." ".$argv[2]." ".$vehicle['longitude']." ".$vehicle['latitude']." ".date("Ymd Hi")."
");
		fclose($fp);
		//}
		break;
	}
}
?>