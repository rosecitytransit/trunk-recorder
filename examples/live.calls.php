<?php //created by Jason McHuff, http://www.rosecitytransit.org/
include("live.config.php");
header("Cache-Control: no-cache, no-store, max-age=0, must-revalidate");
$fewer = "";
if (isset($_SERVER['QUERY_STRING'])) {
	$calls = explode("+",$_SERVER['QUERY_STRING']);
	if (end($calls) == "fewer")
		$fewer = "+".array_pop($calls);
}
if (!isset($calls[2]))
	$calls[2] = date('Y-n-j');
if (!isset($calls[0]) || ($calls[0] == "")) {
	$shortname = $default_system;
	$tg = $default_talkgoup;
} elseif (strpos($calls[0], "-") !== false) {
	$shortname = substr($calls[0], 0, strpos($calls[0], "-");
	$tg = substr($calls[0], strpos($calls[0], "-")+1);
} else {
	$shortname = $default_system;
	$tg = $calls[0];
}
if (!isset($calls[1]))
	$calls[1] = -20;

$dir = $captureDir.$shortname."/".str_replace("-","/",$calls[2])."/";

if (file_exists($dir."calllog.txt")) {
	$filedata = file($dir."calllog.txt",FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$firstcall = true;
	echo count($filedata);
	if ($calls[1] <= count($filedata))
		$filedata = array_slice($filedata, $calls[1], NULL, true);
	foreach ($filedata as $linenum => $wholeline) {
		$allparts = explode(",", $wholeline);
		if (($allparts[3] == $tg) || ($tg == 0)) {
			echo "<div";
			if ($firstcall) {
				echo " id=\"newcalls\"";
					$firstcall = false; }
			if ($allparts[4] == "1") echo " class=\"e\"";
			echo "><span><a href=\"?".$calls[0]."+".$linenum."+".$calls[2].$fewer."\">#".$linenum."</a></span><span>";
			echo date("H:i:s", $allparts[0])."</span><span>";
			echo $allparts[2]."s/".$allparts[1]."s</span><span>";
			if (!$fewer)
				echo "p".$allparts[5]."</span><span>";
			if (isset($talkgroups[$allparts[3]])) echo $talkgroups[$allparts[3]];
			elseif (isset($talkgroups[$shortname."-".$allparts[3]])) echo $talkgroups[$shortname."-".$allparts[3]];
			else echo $allparts[3];
			echo "</span><span>";
			foreach (array_unique(explode("|", $allparts[8])) as $source) {
				echo $source." ";
			}
			$file = $dir.$allparts[3]."-".$allparts[0]."_".substr($allparts[9],0,strpos($allparts[9],"|")).".".$filetype;
			unset($source, $allparts[0], $allparts[1], $allparts[2], $allparts[3], $allparts[4], $allparts[5], $allparts[6], $allparts[7], $allparts[8]); //, $allparts[9]
			if (!fewer) {
				echo "</span><span>";
				foreach ($allparts as $i => $section3) {
					$freqs = explode("|", $section3);
					echo ((int)$freqs[0]/1000000)." MHz (".$freqs[1]."len ".$freqs[2]."err ".$freqs[3]."spk)";
					if (isset($allparts[$i+1])) echo ",<br />";
				}
			}
			unset($section3, $freqs);
			if (file_exists($file))
				echo "</span><span><a href=\"".$file."\">".round(filesize($file) / 1024)."k</a>";
			echo "</span></div>";
		}
	}
	unset($wholeline);
}


elseif (file_exists($dir)) {
	chdir($dir);
	echo "00";
	$output = ""; $outputrows = array();
	foreach (glob("*.".$filetype) as $file) {
		$theparts = explode("-",substr($file,0,strpos($file,"_")));
		if (($theparts[0] == $tg) || ($tg == 0)) {
			$output .= "<div><span>";
			$output .= date("H:i:s", $allparts[1])."</span><span>";
			if (isset($talkgroups[$allparts[0]])) $output .= $talkgroups[$allparts[0]];
			elseif (isset($talkgroups[$shortname."-".$allparts[0]])) echo $talkgroups[$shortname."-".$allparts[0]];
			else $output .= $allparts[0];
			$output .= "</span><span>";
			$outputrows[] = $output . "<a href=\"" . $dir . $file . "\">".round(filesize($file) / 1024)."k</a></span></div>"; }
	}
	sort($outputrows);
	foreach ($outputrows as $outputrow)
		echo $outputrow."
";
} elseif (!is_dir($captureDir.$shortname)) {
	echo "bad directory configuration: ".$captureDir.$shortname." not found";
}
else echo "00"; ?>
