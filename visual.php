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

	function nerveGraph(src){
		hello = new XMLHttpRequest();
		hello.open("GET", src);
		str = "Please wait, generating graph...";
		document.getElementById("dialogGraphDone").innerHTML = str;
		document.getElementById("smap").style.display = "none";
		document.getElementById("menu").style.display = "none";
		document.getElementById("dialogGraphDone").style.display = "inline";
		hello.onreadystatechange = function(){
			if(hello.readyState==4){
				str = "<p>Your graph has been successfully generated.</p>";
				str += "<p>"+hello.responseText+"</p>";
				str += "<p>Location: <big><a href='<?php print($_SESSION['db']); ?>/"+document.getElementById("fname").value+".xls'><?php print($_SESSION['db']); ?>/"+document.getElementById("fname").value+".xls</a></big></p>";
				str += "<p><input type='button' value='Okay' onclick='dialogGraphDoneClose();'></p>";
				document.getElementById("dialogGraphDone").innerHTML = str;
			}
		}
		hello.send(null);
	}

	function dialogGraphDoneClose(){
		document.getElementById("dialogGraphDone").style.display = "none";
		document.getElementById("menu").style.display = "inline";
	}

	function updatepage(txt){
		document.getElementById("helloz").innerHTML = txt;
	}

	function submitForm(mode){
		str = "gfx5.php?";
		z = 0;
		for(x=1;x<=5;x++){
			if(document.getElementById("mag"+x).value!="0"){
				str += "nodec"+x+"="+document.getElementById("nodec"+x).value+"&mag"+x+"="+document.getElementById("mag"+x).value+"&often"+x+"="+document.getElementById("often"+x).value+"&";
				z++;
			}
			// Assumes that no gaps between the inputs.
		}
		str += "maxtime="+document.getElementById("maxtime").value+"&act=calceffects&z="+z;
		if(mode==1) simMode = "forecast";
		if(mode==2){
			simMode = "traffic";
			str += "&mode=traffic";
		}
		document.getElementById("smap").src = str;
		document.getElementById("smap").style.display = "inline";
		document.getElementById("menu").style.display = "none";
		// UPDATE TIME PERIOD
		_y = Math.floor(parseInt(document.getElementById("maxtime").value)/12);
		_m = parseInt(document.getElementById("maxtime").value)%12;
		document.getElementById("mt_maxtime").innerHTML = "Period:<br><font style='font-weight:bold;color:white;'><big>"+_y+"</big> years <big>"+_m+"</big> months</font>";
		// SIM MODE
		if(mode==1) document.getElementById("mt_simMode").innerHTML = "Simulation Mode:<br><font style='font-weight:bold;color:white;'>Forecast</font>";
		else if(mode==2) document.getElementById("mt_simMode").innerHTML = "Simulation Mode:<br><font style='font-weight:bold;color:white;'>Traffic</font>";
	}

	function genExcel(){
		str = "gfx6.php?";
		z = 0;
		for(x=1;x<=5;x++){
			if(document.getElementById("mag"+x).value!="0"){
				str += "nodec"+x+"="+document.getElementById("nodec"+x).value+"&mag"+x+"="+document.getElementById("mag"+x).value+"&often"+x+"="+document.getElementById("often"+x).value+"&";
				z++;
			}
			// Assumes that no gaps between the inputs.
		}
		str += "maxtime="+document.getElementById("maxtime").value+"&act=calceffects&z="+z+"&fname="+document.getElementById("fname").value+"&stage=0";
		nerveGraph(str);
	}

	function showMenu(){
		document.getElementById("smap").style.display = "none";
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

	simMode = "";
	function changeTime(period){
		nw = parseInt(document.getElementById("maxtime").value);
		if(nw+period>0){
			document.getElementById("maxtime").value = nw+period;
			if(simMode=="forecast") submitForm(1);
			else if(simMode=="traffic") submitForm(2);
		}
	}

	function floopMenuShow(){
		document.getElementById("menu").style.display = "none";
		document.getElementById("smap").style.display = "none";
		document.getElementById("dialogGraphDone").style.display = "none";
		document.getElementById("floopMenu").style.display = "inline";
	}

	function floopMenuHide(){
		document.getElementById("floopMenu").style.display = "none";
		document.getElementById("menu").style.display = "none";
		document.getElementById("smap").style.display = "inline";
		document.getElementById("dialogGraphDone").style.display = "none";
	}

	function floopGen(){
		document.getElementById("floopTxt").innerHTML = "Please wait, generating loops...";
		hello = new XMLHttpRequest();
		hello.open("GET", "genloops2.php?maxLength="+document.getElementById("maxLength").value+"&maxLat="+document.getElementById("maxLat").value);
		hello.onreadystatechange = function(){
			if(hello.readyState==4){
				document.getElementById("floopTxt").innerHTML = hello.responseText;
			}
		}
		hello.send(null);
	}

	function floopShow(a){
		str = "gfx5.php?mode=floops&nodes="+a;
		document.getElementById("smap").src = str;
		document.getElementById("smap").style.display = "inline";
		document.getElementById("menu").style.display = "none";
		document.getElementById("floopMenu").style.display = "none";
	}

</script>

<body oncontextmenu='showMenu();return false;'>

	<div style='text-align:right;margin-top:5px;'>
		<embed id='smap' style='background-color:white;' src="gfx5.php" width="<?php print($_SESSION['sX']); ?>" height="<?php print($_SESSION['sY']); ?>" type="image/svg+xml" style='border:1px solid black;'></embed>
	</div>
	
	<!-- MAIN MENU -->
	<div class='mOpt' style='top:0;left:0;' onmouseover='className="mOpt_o";' onmouseout='className="mOpt";' onclick='document.location.href="index.php";'>
		Main Menu
	</div>
	<div class='mOpt' style='top:31;left:0;' onmouseover='className="mOpt_o";' onmouseout='className="mOpt";' onclick='document.location.href="editnodes.php";'>
		Edit Nodes
	</div>
	<div class='mOpt' style='top:62;left:0;' onmouseover='className="mOpt_o";' onmouseout='className="mOpt";' onclick='document.location.href="nodepos.php";'>
		Position Nodes
	</div>
	<div class='mOpt' style='top:93;left:0;' onmouseover='className="mOpt_o";' onmouseout='className="mOpt";' onclick='document.location.href="editlinks.php";'>
		Edit Links
	</div>

	<div class='mOpt2' style='top:150;left:0;' onmouseover='className="mOpt2_o";' onmouseout='className="mOpt2";' onclick='showMenu();'>
		Run Simulation
	</div>

	<div class='mBut' style='top:340px;left:5px;' onmouseover='className="mBut_o";' onmouseout='className="mBut";' onclick='changeTime(-1);'>
		<font style='font-size:10pt;'>&laquo;</font>
	</div>
	<div class='mTxt2' style='top:340px;left:45px;width:50px;'>1</div>
	<div class='mBut' style='top:340px;left:95px;' onmouseover='className="mBut_o";' onmouseout='className="mBut";' onclick='changeTime(1);'>
		<font style='font-size:10pt;'>&raquo;</font>
	</div>

	<div class='mBut' style='top:390px;left:5px;' onmouseover='className="mBut_o";' onmouseout='className="mBut";' onclick='changeTime(-3);'>
		<font style='font-size:10pt;'>&laquo;</font>
	</div>
	<div class='mTxt2' style='top:390px;left:45px;width:50px;'>3</div>
	<div class='mBut' style='top:390px;left:95px;' onmouseover='className="mBut_o";' onmouseout='className="mBut";' onclick='changeTime(3);'>
		<font style='font-size:10pt;'>&raquo;</font>
	</div>

	<div class='mBut' style='top:440px;left:5px;' onmouseover='className="mBut_o";' onmouseout='className="mBut";' onclick='changeTime(-6);'>
		<font style='font-size:10pt;'>&laquo;</font>
	</div>
	<div class='mTxt2' style='top:440px;left:45px;width:50px;'>6</div>
	<div class='mBut' style='top:440px;left:95px;' onmouseover='className="mBut_o";' onmouseout='className="mBut";' onclick='changeTime(6);'>
		<font style='font-size:10pt;'>&raquo;</font>
	</div>

	<div class='mBut' style='top:490px;left:5px;' onmouseover='className="mBut_o";' onmouseout='className="mBut";' onclick='changeTime(-12);'>
		<font style='font-size:10pt;'>&laquo;</font>
	</div>
	<div class='mTxt2' style='top:490px;left:45px;width:50px;'>12</div>
	<div class='mBut' style='top:490px;left:95px;' onmouseover='className="mBut_o";' onmouseout='className="mBut";' onclick='changeTime(12);'>
		<font style='font-size:10pt;'>&raquo;</font>
	</div>

	<div class='mTxt'>
		<div id='mt_simMode'></div>
		<div id='mt_maxtime' style='margin-top:3px;'></div>
	</div>

	<div class='mTxt3'>
		Current Database:<br>
		<font style='font-weight:bold;color:white;font-size:12pt;'><?php print($_SESSION['db']); ?></font>
	</div>

	<div class='mOpt2' style='top:600;left:0;' onmouseover='className="mOpt2_o";' onmouseout='className="mOpt2";' onclick='floopMenuShow();'>
		Feedback Loops
	</div>	
	<!-- END MAIN MENU -->

	<div id='dialogGraphDone' style='display:none;position:absolute;left:<?php print($_SESSION['sX']/2-250); ?>px;top:<?php print($_SESSION['sY']/2-200); ?>px;background-color:lavender;border:2px solid white;padding:20px;font-family:arial;'></div>

	<!-- FLOOPS MENU -->
	<div id='floopMenu' style='width:<?php print($_SESSION['sX']-150); ?>px;height:<?php print($_SESSION['sY']-120); ?>px;'>
		<p class='dh1'>Feedback Loops</p>
		<p>
			<input type='button' class='dbt1' onmouseover='className="dbt1_o";' onmouseout='className="dbt1";' value='Generate Loops' onclick='floopGen();'>
			<input type='button' class='dbt1' onmouseover='className="dbt1_o";' onmouseout='className="dbt1";' value='Cancel' onclick='floopMenuHide();'>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            Max. no. of nodes: <input type='text' id='maxLength' class='ntb1' style='width:50px;' value='8'>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            Max. length of loop: <input type='text' id='maxLat' class='ntb1' style='width:50px;' value='48'> months
		</p>
		<div id='floopTxt' style='width:<?php print($_SESSION['sX']-200); ?>px;height:<?php print($_SESSION['sY']-300); ?>px;'></div>
	</div>
    <!-- END FLOOPS MENU -->

	<div id='menu' style='width:<?php print($_SESSION['sX']-150); ?>px;height:<?php print($_SESSION['sY']-120); ?>px;'>
		<div id='mleft' style='width:<?php print(round($_SESSION['sX']-150)/2-50); ?>px;'>
			<p class='dh1'>Run Simulation</p>
			<form>
				<b>Time to Elapse:</b> Stop after <input type='text' id='maxtime' class='ntb1' style='width:50px;'> month(s).<br><br>
				<?php
					$options = array();
					$r2 = mysql_query('SELECT * FROM `nodes` ORDER BY `name` ASC',$link);
					while($r = mysql_fetch_assoc($r2)){
						$options[] = '<option value="'.$r['node_id'].'">'.$r['name'].'</option>';
					}
					for($i=1;$i<=5;$i++){
						print('Node <big><b>'.$i.'</b></big>: <select id="nodec'.$i.'" class="ns1">');
						for($x=0;$x<count($options);$x++){
							print($options[$x]);
						}
						print('</select><br>Change of '."<input type='text' id='mag".$i."' class='ntb1' style='width:50px;' value='0'> ");
						print('every '."<input type='text' id='often".$i."' class='ntb1' style='width:50px;'> month(s)<br>");
						print('<hr class="hr1">');
					}
				?>
				<input type='hidden' name='act' value='calceffects'>
				<input type='button'  class='dbt1' onmouseover='className="dbt1_o";' onmouseout='className="dbt1";' value='Run Forecast' onclick='submitForm(1);'>
				<input type='button'  class='dbt1' onmouseover='className="dbt1_o";' onmouseout='className="dbt1";' value='Show Link Traffic' onclick='submitForm(2);'>
				<input type='button'  class='dbt1' onmouseover='className="dbt1_o";' onmouseout='className="dbt1";' value='Cancel' onclick='document.getElementById("menu").style.display="none";document.getElementById("smap").style.display="inline";'>
			</form>
		</div>
		<div id='mright' style='width:<?php print(round($_SESSION['sX']-150)/2-50); ?>px;'>
			<p class='dh1'>Generate Excel Graph</p>
			<form>
				Save As: <big><?php print($_SESSION['db']); ?>/<input type='text' id='fname' class='ntb1'>.xls</big><br><br>
				<input type='button'  class='dbt1' onmouseover='className="dbt1_o";' onmouseout='className="dbt1";' value='Generate Output Data' onclick='genExcel();'>
							<br><br><br>
                <p style='color:#bbbbbb;'>
                Developed in 2008 by Mak Yiing Chau.<br>
                All rights reserved.<br>
                Systems Thinking methodology based on RAHS.<br><br>
                Version 2: More efficient engine implemented.
                </p>
			</form>
		</div>
	</div>

</body>

</html>