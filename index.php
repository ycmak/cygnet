<?php
	include('config.inc.php');
?>

<html>

<head>
	<title>Menu</title>
	<link rel='stylesheet' href='menu.css'>
</head>

<script language='javascript'>

	function hideSplash(){
			document.getElementById("splash").style.display = "none";
			document.getElementById("splashBg").style.display = "none";
			document.body.style.overflow = "auto";
	}

	function nerve(){
		hello = new XMLHttpRequest();
		hello.open("GET", "brain.php?sX="+screen.width+"&sY="+screen.height);
		hello.onreadystatechange = function(){
			if(hello.readyState==4){
				loadSplash();
			}
		}
		hello.send(null);
	}

	function updatepage(txt){
		document.getElementById("ticker").innerHTML = txt;
	}
	
	function loadSplash(){
		document.body.style.overflow = "hidden";
		document.getElementById("splash").style.left = (screen.width/2-280)+"px";
		document.getElementById("splash").style.top = (screen.height/2-230)+"px";
		document.getElementById("splash").style.dislpay = "inline";
		document.getElementById("splashBg").style.dislpay = "inline";
		loadSplashOpacity(0);
	}
	
	function loadSplashOpacity(a){
		document.getElementById("splash").style.opacity = a;
		if(a<100) setTimeout("loadSplashOpacity("+(a+0.1)+")",100);
	}
	
</script>

<body onload='nerve();'>

	<h2>Cygnet</h2>
	<p>
		Goals: <font style='font-weight:bold;color:orange;'>reliable approach, ease of use by analysts, allows for value-added analysis</font>.
	</p>
	<p>
		You are logged in to <b><?php print($_SESSION['db']); ?></b>. [<a href='select_db.php'>Switch Database</a>]
	</p>
	<ul>
		<li><a href='editnodes.php'>Edit Nodes</a></li>
		<li><a href='editlinks.php'>Edit Links</a></li>
		<li><a href='nodepos.php'>Position Nodes</a></li>
		<li><a href='smap.php'>System Map</a></li>
		<li><a href='visual.php'>Visual Map</a></li>
		<li><a href='genloops2.php'>Generate Loops</a></li>
		<li><a href='settings.php'>Change Settings</a></li>
	</ul>
	<h3>Working Ideas</h3>
	<ul>
		<li>User Interface/Features
			<ul>
				<li>Implement consistent theme for the entire system.</li>
				<li>Allow users to highlight links to/from a particular node.</li>
				<li>Improve GUI for creating/editing nodes and links -- integrate with Visual Map?</li>
				<li style='color:skyblue;'>GUI for creating map: context menu allows one to create spin-off nodes, modelling the thought process?</li>
				<li style='color:skyblue;'>Click on a link and specify the opacity of the neighbouring nodes as a function of latency/magnitude.</li>
				<li>Create wizard to allow user to more easily input scale factors for the scaling approach.</li>
				<li>Allow for export of system maps (.cyg, .png).</li>
				<li>Enable a high-contrast scheme?</li>
				<li>Status change for links/nodes -- user-defined, batch mode, to simplify batch editing</li>
				<li style='color:orange;'>Allow nodes and links to be deactivated and reactivated -- select subgraph.</li>
				<li style='color:skyblue;'>Create a mode of exploring nodes and links, showing one node in the center at a time and showing outgoing/incoming links to/from other nodes, placed around the center node.</li>
				<li>Show all links from A to B, and all feedback loops involving A and B.</li>
                <li style='color:orange;'>Implement customisable graphing options.</li>
                <li style='color:orange;'>Automatically consider and batch run through various combinations. <b>Intelligent Scanning Mode</b></li>
			</ul>
		</li>
		<li>Core Engine/Methodology</li>
			<ul>
				<li style='color:silver;'>(tentative) More efficient algorithm for generating feedback loops?</li>
				<li>Allow for pre-existing environmental conditions, i.e. let certain nodes run before t=0</li>
				<li>Extend a field for each node for its "scale" -- purely for reference?</li>
				<li>Concept of morphological analysis as extended to links?</li>
				<li>Allow different accounts to specify scale and latency of links? Ref. James Surowiecki, Wisdom of Crowds</li>
			</ul>
		</li>
		<li>Other Areas</li>
			<ul>
				<li>Write paper for Cygnet.</li>
				<li>Write user guide for Cygnet.</li>
			</ul>
		</li>
	</ul>
	<h3>Changelog</h3>
	<ul>
		<li>Wednesday, 20 August 2008
			<ul>
				<li>Accounted properly for links with zero latency.</li>
				<li>Fixed the change settings page -- made it automatic detection?</li>
			</ul>
		</li>    	
		<li>Monday, 18 August 2008
			<ul>
				<li>Updated feedback loops visual interface and implemented options and sorting by latency.</li>
			</ul>
		</li>    	
		<li>Tuesday, 5 August 2008
			<ul>
				<li style='color:blue;'>Finalized implementation of core methodology for scaling approach.</li>
				<li>Corrected bug in traffic mode.</li>
			</ul>
		</li>
		<li>Thursday, 31 July 2008
			<ul>
				<li style='color:blue;'>Added 'traffic' mode for simulation.</li>
				<li>Updated interface for simulation -- added mouse controls.</li>
				<li>Integrated (partially) the ability to generate and display feedback loops in the simulation page.</li>
			</ul>
		</li>
		<li>Wednesday, 30 July 2008
			<ul>
				<li style='color:blue;'>Implemented and revised diminishing marginal returns effect.</li>
				<li>Imposed bottom limit on node change (-100%).</li>
				<li>Improved readability of simulation screen.</li>
			</ul>
		</li>
		<li>Tuesday, 29 July 2008
			<ul>
				<li>Added support for notes for each link.</li>
				<li style='color:blue;'>Implement the "scaling" approach for simulation.</li>
				<li>Implemented support for notes for nodes.</li>
				<li>Updated interface for edit links.</li>
			</ul>
		</li>
		<li>Friday, 25 July 2008
			<ul>
				<li>Updated visual map interface.</li>
				<li>Added systems settings menu option and automatic scaling of visual map to fit multiple resolutions.</li>
				<li>Added support for multiple databases and database switching.</li>
				<li>Added keyboard shortcuts for visual map.</li>
				<li style='color:blue;'>Changed core simulation engine. Complexity of algorithm now O(E) instead of O(ET).</li>
				<li>Added edit/delete function for links.</li>
				<li>Added edit function for nodes.</li>
				<li>Updated menu interface.</li>
			</ul>
		</li>
		<li>Thursday, 24 July 2008
			<ul>
				<li>Added feedback loop generating function.</li>
			</ul>
		</li>
		<li>Wednesday, 23 July 2008
			<ul>
				<li>Added node positioning interface.</li>
			</ul>
		</li>
		<li>Thursday, 10 July 2008
			<ul>
				<li>Completed first working version of Cygnet.</li>
			</ul>
		</li>
		<li>Tuesday, 8 July 2008
			<ul>
				<li>'Ground-breaking' moment: started coding first working version of Cygnet.</li>
			</ul>
		</li>
		<li>Wednesday, 2 July 2008
			<ul>
				<li>Conceptualized initial methodology for Cygnet.</li>
			</ul>
		</li>
	</ul>

	<div id='splashBg'></div>
	<div id='splash'>
    	<p class='splashTitle'>Cygnet</p>
        <p align='center' style='font-weight:bold;'>Version 2.1</p>
        <p align='center'>
        	Copyright &copy; 2008 by ???. All rights reserved.<br>
        	Developed by Mak Yiing Chau in PMO/NSCS/JCTC.<br>
            [relevant messages]
        </p>
        <p align='center' style='margin-top:50px;'>
        	<input type='button' class='splashBtn' value='Continue' onclick='hideSplash();'>
        </p>
    </div>

</body>

</html>