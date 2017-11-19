<?php
	include('config.inc.php');
?>
<html>

<head>
	<title>System Map</title>
	<link rel='stylesheet' href='main.css'>
</head>

<script language='javascript'>

	function nerve(){
		hello = new XMLHttpRequest();
		hello.open("GET", "brain.php");
		hello.onreadystatechange = function(){
			if(hello.readyState==4){
				updatepage(hello.responseText);
			}
		}
		hello.send(null);
	}

	function updatepage(txt){
		document.getElementById("helloz").innerHTML = txt;
	}

	function submitForm(){
		str = "gfx.php?";
		z = 0;
		for(x=1;x<=5;x++){
			if(document.getElementById("mag"+x).value!="0"){
				str += "nodec"+x+"="+document.getElementById("nodec"+x).value+"&mag"+x+"="+document.getElementById("mag"+x).value+"&often"+x+"="+document.getElementById("often"+x).value+"&";
				z++;
			}
			// Assumes that no gaps between the inputs.
		}
		str += "maxtime="+document.getElementById("maxtime").value+"&act=calceffects&z="+z;
		document.getElementById("smap").src = str;
		document.getElementById("smap2").style.display = "none";
		document.getElementById("smap").style.display = "inline";
		document.getElementById("menu").style.display = "none";
	}

	function genExcel(){
		str = "gfx2.php?";
		z = 0;
		for(x=1;x<=5;x++){
			if(document.getElementById("mag"+x).value!="0"){
				str += "nodec"+x+"="+document.getElementById("nodec"+x).value+"&mag"+x+"="+document.getElementById("mag"+x).value+"&often"+x+"="+document.getElementById("often"+x).value+"&";
				z++;
			}
			// Assumes that no gaps between the inputs.
		}
		str += "maxtime="+document.getElementById("maxtime").value+"&act=calceffects&z="+z+"&tframe="+document.getElementById("tframe").value+"&fname="+document.getElementById("fname").value+"&stage=0";
		document.getElementById("smap").style.display = "none";
		document.getElementById("smap2").style.display = "inline";
		document.getElementById("smap2").src = str;
		document.getElementById("menu").style.display = "none";
	}

	function showMenu(){
		document.getElementById("menu").style.display = "inline";
	}

	function tinput(){
		a = window.prompt("What is the maximum time frame? (no. of months)","");
		b = window.prompt("Enter interval (no. of months)","6");
		a = parseInt(a);
		b = parseInt(b);
		str = "";
		for(i=b;i<=a;i+=b){
			if(i!=b) str += ",";
			str += i;
		}
		document.getElementById("tframe").value = str;
	}

</script>

<body oncontextmenu='showMenu();return false;'>

	<div style='text-align:center;margin-top:5px;'>
		<embed id='smap' style='background-color:white;' src="gfx.php" width="1250" height="1000" type="image/svg+xml" style='border:1px solid black;'></embed>
		<iframe id='smap2' frameborder='0' style='background-color:white;display:none;' src="" width="1250" height="1000" style='border:1px solid black;'></iframe>
	</div>
	<div id='menu' style='display:none;opacity:0.8;position:absolute;left:150px;top:200px;width:900px;height:500px;background-color:white;border:3px solid black;font-family:arial;font-size:8pt;margin-top:5px;padding-left:15px;'>
		<div id='mleft' style='float:left;width:440px;border-right:3px solid black;'>
			<big><b>Check Node Influence</b></big><br><br>
			<form>
				<b>Time to Elapse:</b> Stop after <input type='text' id='maxtime' style='font-size:8pt;'> months.<br><br>
				<?php
					$options = array();
					$r2 = mysql_query('SELECT * FROM `nodes` ORDER BY `name` ASC',$link);
					while($r = mysql_fetch_assoc($r2)){
						$options[] = '<option value="'.$r['node_id'].'">'.$r['name'].'</option>';
					}
					for($i=1;$i<=5;$i++){
						print('Node <big><b>'.$i.'</b></big>: <select id="nodec'.$i.'" style="font-size:8pt;">');
						for($x=0;$x<count($options);$x++){
							print($options[$x]);
						}
						print('</select><br>Magnitude of Change: '."<input type='text' id='mag".$i."' style='font-size:8pt;' value='0'> (scale of -5 to 5)<br>");
						print('</select><br>How Often? Once per '."<input type='text' id='often".$i."' style='font-size:8pt;'> months (0 for single impact at start)<br>");
						print('<hr style="border:1px solid black;size:1px;">');
					}
				?>
				<input type='hidden' name='act' value='calceffects'>
				<input type='button' style='border:2px solid silver;font-size:8pt;font-weight:bold;background-color:black;color:yellow;' value='Calculate Effects' onclick='submitForm();'>
				<input type='button' style='border:2px solid silver;font-size:8pt;font-weight:bold;background-color:black;color:white;' value='Cancel' onclick='document.getElementById("menu").style.display="none";'>
			</form>
		</div>
		<div id='mright' style='float:left;width:440px;padding-left:15px;'>
			<big><b>Generate Excel Graph</b></big><br><br>
			<form>
				Save As: <input type='text' id='fname' style='font-size:8pt;'>.txt<br><br>
				Multiple Time Frames: <input type='text' id='tframe' style='font-size:8pt;'> months (separated by commas)<br><br>
				<input type='button' style='border:2px solid silver;font-size:8pt;font-weight:bold;background-color:black;color:white;' value='Enter Intervals / Maximum Period' onclick='tinput();'><br>
				<input type='button' style='border:2px solid silver;font-size:8pt;font-weight:bold;background-color:black;color:yellow;' value='Generate Output Data' onclick='genExcel();'>
							<br><br><br>
			Developed in 2008 by Mak Yiing Chau.<br>
			All rights reserved.<br>
			Systems Thinking methodology based on RAHS.<br><br>
			Version 2: More efficient engine implemented.

			</form>
		</div>
	</div>

</body>

</html>