<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>King County Metro radio calls</title>
<script type="text/javascript">
<?php date_default_timezone_set('America/Los_Angeles');
$calls = explode("+",$_SERVER['QUERY_STRING']);
if (!isset($calls[2]))
	$calls[2] = date('Y-n-j');
if (!isset($calls[0]) || ($calls[0] == "")) {
	$calls[0] = "ALL";
	echo "	var calls=\"ALL\";
";
} else echo "	var calls=\"".$_SERVER['QUERY_STRING']."\";
";
?>
	var player;
	var playlist;
	var currentcall;
	var channel;
	var e;
	function init() {
		e = document.createElement('div');
		player = document.getElementById('audioplayer');
		playlist = document.getElementById('theplaylist');
		channel = document.getElementById('channels');
		getcalls();
		player.volume = 0.2;

		playlist.addEventListener('click',function (e) {
			//e.preventDefault();
			if ((e.target.parentElement.nodeName == "DIV") && (e.target.parentElement != playlist) && e.target.parentElement.getElementsByTagName('a')[0]) {
				playnext(e.target.parentElement);
			}
			else if ((e.target.parentElement == playlist) && e.target.getElementsByTagName('a')[0]) {
				playnext(e.target);
			}
		}, false);

		player.addEventListener('ended',function () {
			if (currentcall.nextSibling && (document.getElementById('autoplay').checked == true)) {
				playnext(currentcall.nextSibling);
			}
		}, false);
	}

	window.onload=init;
	var loadcalls=setInterval(getcalls, 20000);

	function getcalls() {
		downloadUrl("live.calls.php?"+calls, function(data) {
			if (document.getElementById('newcalls'))
				document.getElementById('newcalls').removeAttribute("id");
			calls = calls+"+";
			calls = calls.substr(0, calls.indexOf("+"))+"+"+data.match(/\d+/);
			data = data.replace(/\d+/,"");
			e.innerHTML = data;
			while(e.firstChild) {
				playlist.appendChild(e.firstChild);
			}
			if (!currentcall && document.getElementById('newcalls')) {	//playnext
				currentcall = document.getElementById('newcalls');
				currentcall.style.fontWeight = "bold";
				player.setAttribute('src',currentcall.getElementsByTagName('a')[0]);
				player.load();
			}
			if ((document.getElementById('autoplay').checked == true) && (player.ended == true) && document.getElementById('newcalls')) {
				playnext(document.getElementById('newcalls'));
				window.location="#newcalls";
			}
		});
	}

	function playnext(nextcall) {
		do {
			if (nextcall.getElementsByTagName('a')[0]) {
				currentcall.style.fontWeight = "normal";
				currentcall = nextcall;
				currentcall.style.fontWeight = "bold";
				player.setAttribute('src',currentcall.getElementsByTagName('a')[0]);
				player.load();
				player.play();
				break;
			}
		} while (nextcall = nextcall.nextSibling)
	}

	function changecalls(day,today) {
		newtitle = "KCM "+channel.options[channel.selectedIndex].text;
		calls = channel.value;
		if (day)
			calls += "+00";
		else 
			loadcalls=setInterval(getcalls, 20000);
		if (!today) {
			calls += "+"+document.getElementById("m").value+"-"+document.getElementById("d").value;
			newtitle += " on "+document.getElementById("m").value+"-"+document.getElementById("d").value;
			clearInterval(loadcalls);
		}
		document.title = newtitle;
		document.getElementById('curchan').innerHTML = newtitle;
		currentcall = "";
		playlist.innerHTML="";
		getcalls();
	}
</script>
<script type="text/javascript" src="util.js"></script>
<style>.t {padding-right: 10px; display: table-cell; max-width: 550px;} .r {display: table-row;} .re {display: table-row; color: red;}</style>



<body style="font-family: Arial;" ><div style="position:fixed; background: white; top: 0; width: 100%;">
<h1><span id="curchan">King County Metro radio calls</span></h1>
<form action="" name="changecall">Channel(s): <select name="channels" id="channels">

<?php $tglist = array('ALL'=>"ALL",
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
28062=>"Security 1", 28063=>"Security 2", 28064=>"Sound Transit Multigroup");

foreach ($tglist as $thistg => $thisvalue) {
	echo '<option value="'.$thistg.'"';
	if ($calls[0] == $thistg) echo ' selected="selected"';
	echo '>'.$thisvalue.'</option>
';
}
unset($thistg); ?>
</select><input type="button" value="Live" onclick="changecalls(false, true);"><input type="button" value="Today" onclick="changecalls(true, true);">
<select name="m" id="m">
<?php $themon = explode("-",$calls[2]);
foreach (glob("2*/*", GLOB_ONLYDIR) as $mon) {
	echo '<option value="'.str_replace("/","-",$mon).'"';
	if ($mon == $themon[0]."/".$themon[1]) echo ' selected="selected"';
	echo '>'.$mon.'</option>
'; }
unset ($mon);
echo '</select>/<select name="d" id="d">';
for ($x = 1; $x <= 31; $x++) {
	echo '<option value="'.$x.'"';
	if ($x == $themon[2]) echo ' selected="selected"';
	echo '>'.$x.'</option>
'; }
unset($x); ?></select><input type="button" value="Day" onclick="changecalls(true, false);">
	<br /><audio id="audioplayer" src="250ms.mp3" preload="none" tabindex="0" controls>
		Sorry, your browser does not support HTML5 audio.
	</audio><label><input id="autoplay" type="checkbox" checked="checked" />AutoPlay</label></form></div>

	<p style="font-weight: bold; margin-top: 150px;">Click on a row to begin sequential playback in AutoPlay, click file size to download</p>

	<div id="theplaylist" style="display: table;"></div>

</body></html>
