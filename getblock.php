<?php error_reporting(0);
$inservice = "all";
include('/var/www/html/systemmapper/cache2.php');
if ($vehicles[$argv[1]]['blockID'])
echo "_".$vehicles[$argv[1]]['blockID'];
?>