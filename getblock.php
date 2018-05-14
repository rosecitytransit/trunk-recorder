<?php error_reporting(0);
$inservice = "all";
include('/var/www/html/systemmapper/cache.php');
foreach ($resultSet->vehicle as $vehicle) {
	if ($vehicle['vehicleID'] == $argv[1]) {
		if ($vehicle['blockID'] != "") {
			echo $vehicle['blockID'];
			break; }
		elseif ($vehicle['extraBlockID'] != "") {
			echo "extra".$vehicle['extraBlockID'];
			break; }
	}
}
?>