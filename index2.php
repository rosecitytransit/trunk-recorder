<!DOCTYPE html>
<html lang="en">
<head>
<?php
date_default_timezone_set('America/Los_Angeles');
parse_str(str_replace("&amp;","&",$_SERVER['QUERY_STRING']), $pagequery);
if (isset($pagequery['m']))
	$m = $pagequery['m'];
else
	$m=date('Y')."-".date('n');
if (isset($pagequery['d']))
	$d = $pagequery['d'];
else
	$d=date('j');
if (isset($pagequery['tgs']))
	$tgs = $pagequery['tgs'];
else
	$tgs="ALL";

$dir = str_replace("-","/",$m)."/".$d."/";

echo "<base id=\"myBase\" href=\"".$dir."\">";

$talkgroups = array('ALL'=>"ALL",
28000=>"Service Control Management", 28001=>"Bus Broadcast Default", 28002=>"Fallback Announcement Default",
28003=>"Transit Coordinator 1", 28004=>"Transit Coordinator 2", 28005=>"Transit Coordinator 3", 28006=>"Transit Coordinator 4",
28007=>"Transit Coordinator 5", 28008=>"Transit Coordinator 6", 28009=>"Transit Coordinator 7", 28010=>"Transit Coordinator 8",
28011=>"Transit Coordinator 9", 28012=>"Transit Coordinator 10", 28013=>"Transit Coordinator 11",
28014=>"Transit Coordinator1 Fallback Grp", 28015=>"Transit Coordinator2 Fallback Grp", 28016=>"Transit Coordinator3 Fallback Grp",
28017=>"Transit Coordinator4 Fallback Grp", 28018=>"Transit Coordinator5 Fallback Grp", 28019=>"Transit Coordinator6 Fallback Grp",
28020=>"Transit Coordinator7 Fallback Grp", 28021=>"Transit Coordinator8 Fallback Grp", 28022=>"Transit Coordinator9 Fallback Grp",
28023=>"Transit Coordinator10 Fallback Grp", 28024=>"Transit Coordinator11 Fallback Grp",
28025=>"Field OPS", 28026=>"Service Quality TAC1", 28027=>"Service Quality TAC2", 28028=>"Vehicle Maintenance",
28029=>"Power Distribution 1", 28030=>"Power Distribution 2", 28031=>"Power Distribution 3", 28032=>"Radio Maintenance 1",
28033=>"Facilities Maintenance 1", 28034=>"Facilities Maintenance 2", 28035=>"DSTT Maintenance",
28036=>"Safety", 28037=>"Facilities Security", 28038=>"Facilities Security  Tac 1", 28039=>"Base Cars",
28040=>"Incident Command", 28041=>"Incident Operations", 28042=>"Incident Logistics",
28043=>"Radio Maintenance 2", 28044=>"Radio Maintenance 3",
28045=>"Test Talkgroup 1", 28046=>"Test Talkgroup 2", 28047=>"Test Talkgroup 3", 28048=>"DSTT OPS",
28049=>"Street Cars OPS", 28050=>"Street Cars Sup", 28051=>"PowerFacilities",
28052=>"Bus Fallback Multigroup", 28053=>"Emergency Multigroup",
28054=>"Metro Link Operations 1", 28055=>"Metro Link Operations 2", 28056=>"DSTT Operations", 28057=>"Yard Operations",
28058=>"Maintenance Channel 1", 28059=>"Maintenance Channel 2", 28060=>"DSTT Maintenance", 28061=>"Fare Inspection",
28062=>"Security 1", 28063=>"Security 2", 28064=>"Sound Transit Multigroup")
?>
<meta charset="UTF-8">
<title>King County Metro <?php
if (isset($talkgroups[$tgs]) && ($tgs != "ALL")) echo $talkgroups[$tgs];
else echo "radio calls" ?> on <?php echo substr($m,5)."/".$d."/".substr($m,0,4); ?></title>
<script type="text/javascript">
	var player;
	var playlist;
	var currentcall;
	function init() {
		player = document.getElementById('audioplayer');
		playlist = document.getElementById('theplaylist');
		currentcall = playlist.getElementsByTagName('div')[0];
		if (currentcall.getElementsByTagName('a')[0])
			player.setAttribute('src',currentcall.getElementsByTagName('a')[0]);
                //document.getElementById("myBase").href = "<?php echo $dir; ?>";
		player.volume = 0.2;

		playlist.addEventListener('click',function (e) {
			//e.preventDefault();
		if (e.target.parentElement.getElementsByTagName('a')[0]) {
			currentcall.style.fontWeight = "normal";
			currentcall = e.target.parentElement;
			currentcall.style.fontWeight = "bold";
			player.setAttribute('src',currentcall.getElementsByTagName('a')[0]);
			player.load();
			player.play();
		} }, false);

		player.addEventListener('ended',function () { 
		var newcall = currentcall;
		while (newcall.nextSibling) {
			newcall = newcall.nextSibling;
			if (newcall.getElementsByTagName('a')[0]) {
				currentcall.style.fontWeight = "normal";
				currentcall = newcall;
				currentcall.style.fontWeight = "bold";
				player.setAttribute('src',currentcall.getElementsByTagName('a')[0]);
				player.load();
				player.play();
				break;
			}
		}
		}, false);

	}
	window.onload=init;
</script>
<style type="text/css">span {padding-right: 10px; display: table-cell; max-width: 550px;}</style>
<body style="font-family: Arial;" ><div style="position:fixed; background: white; top: 0; width: 100%;">
<h1>King County Metro <?php
if (isset($talkgroups[$tgs]) && ($tgs != "ALL")) echo $talkgroups[$tgs];
else echo "radio calls" ?> on <?php echo substr($m,5)."/".$d."/".substr($m,0,4); ?></h1>
<form>Change: Month: <select name="m">
<?php foreach (glob("2*/*", GLOB_ONLYDIR) as $mon) {
	echo '<option value="'.str_replace("/","-",$mon).'"';
	if ($mon == str_replace("-","/",$m)) echo ' selected="selected"';
	echo '>'.$mon.'</option>
'; }
unset ($mon);
echo '</select> Day: <select name="d">';
for ($x = 1; $x <= 31; $x++) {
	echo '<option value="'.$x.'"';
	if ($x == $d) echo ' selected="selected"';
	echo '>'.$x.'</option>
'; }
unset($x);
echo '</select> Channel(s): <select name="tgs">';

foreach ($talkgroups as $thistg => $thisvalue) {
	echo '<option value="'.$thistg.'"';
	if ($tgs == $thistg) echo ' selected="selected"';
	echo '>'.$thisvalue.'</option>
';
}
unset($thistg); ?>
</select> <input type="submit" value="Go"></form>

	<audio id="audioplayer" src="/250ms.mp3" preload="none" tabindex="0" controls>
		Sorry, your browser does not support HTML5 audio.
	</audio></div>

        <p style="font-weight: bold; margin-top: 150px;">Click on a row to begin sequential playback, click file size to download</p>

	<div id="theplaylist" style="display: table;">
<?php
if (file_exists($dir."calllog.txt")) {
	$filedata = file($dir."calllog.txt",FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	foreach ($filedata as $wholeline) {
		if (substr($wholeline,-1) == ";")
			$wholeline = substr($wholeline, 0, -1);
		$allparts = explode(";", $wholeline);
		$section1 = explode(",", $allparts[0]);
		if (($section1[2] == $tgs) || ($tgs == "ALL")) {
    			echo "<div
style=\"display: table-row;";
			if ($section1[3] == "1") echo " color: red;";
			echo "\"><span>";
			echo substr($section1[0],0,2).":".substr($section1[0],2,2).":".substr($section1[0],4,2)."</span><span>";
			echo $section1[1]."s</span><span>";
			if (isset($talkgroups[$section1[2]])) echo $talkgroups[$section1[2]]; else echo $section1[2];
			echo "</span><span>";
			$sources = array_unique(explode(",", $allparts[1]));
			foreach ($sources as $source) {
				if (substr($source, -7, 3) == "280")
					echo substr($source, 3)." ";
				else
					echo $source." ";
			}
			unset($source, $allparts[0], $allparts[1]);
			echo "</span><span>";
			//if (isset($allparts[2])) {
				foreach ($allparts as $i => $section3) {
					$freqs = explode(",", $section3);
					echo $freqs[0]." MHz (".$freqs[1]."len ".$freqs[2]."err ".$freqs[3]."spk)";
					if (isset($allparts[$i+1])) echo ",<br />";
				}
				unset($section3, $freqs);
			//}
			$file = $dir.$section1[0]."-".$section1[2].".mp3";
			if (file_exists($file))
				echo "</span><span><a href=\"" . $section1[0]."-".$section1[2] . ".mp3\">".round(filesize($file) / 1024)."k</a>";
			echo "</span></div>";
		}
	}
	unset($wholeline);
}


elseif (file_exists($dir)) {
chdir($dir);
foreach (glob("*.mp3") as $file) {
  if ((substr($file,7,-4) == $tgs) || ($tgs == "ALL")) {
    echo "<div style=\"display: table-row;\"><span>";
    echo substr($file,0,2).":".substr($file,2,2).":".substr($file,4,2)."</span><span>";
    if (isset($talkgroups[substr($file,7,-4)])) echo $talkgroups[substr($file,7,-4)]; else echo substr($file,7,-4);
    echo "</span><span>";
    echo "<a href=\"" . $file . "\">".round(filesize($file) / 1024)."k</a></span></div>
"; } } }
else echo "Pick a different date";
?>
</div></body></html>