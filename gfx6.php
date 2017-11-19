<?php

	# FILENAME: gfx6.php
	# Runs simulation and outputs data to Excel file
	#
	# INPUT:
	#
	# mag#		Magnitude of node change
	# nodec#	node_id
	# often#	Frequency of node change
	# maxtime	Length of simulation (how many periods)
	# fname		Name of file to be generated

	include('config.inc.php');

	// Define global variables
	$mPush = array();		// Stack for each month
	$mCnt = array();		// Total amount of traffic in a link -- NOT USED in gfx6.php
	$__scalePos = M_E*2;	// used by LDMR types 1 and 2
	$__scaleNeg = M_E/2;	// used by LDMR Type 1
	$nodes = array();		// Array of node values (float)
	$nodenames = array();	// Array of node names
	$links = array();		// Array of links: $links[originating_node][terminating_node]
	$link_info = array();	// Array of link information (e.g. latency, magnitude, etc)
	$linksZero = array();	// Array of links with zero latency

	// Propogate the traffic for each month
	function walk($goal,$elapsed,$effect,$maxtime,$tmpPush=0){
		global $link,$links,$link_info,$_GET,$nodes,$mPush,$mCnt,$__scalePos,$__scaleNeg;

		// Update the node values
		$nodes[$goal] += $effect;
		
		// Calculates the strength of the propogation
		if($tmpPush!=0){
			$tmp = $tmpPush*(abs($nodes[$goal])+1);
			$nodes[$goal] += $tmp;
			$effect += $tmp;
		}

		// Searches for neighbouring nodes to propogate the change
		for($x=0;$x<count($links[$goal]);$x++){
			if($elapsed+$link_info[$links[$goal][$x]['id']]['latency']<$maxtime){
				$pChange = $effect*$link_info[$links[$goal][$x]['id']]['magnitude'];
				if(!isset($mPush[$elapsed+$link_info[$links[$goal][$x]['id']]['latency']][$links[$goal][$x]['to']])){
					// Creates a new stack for this link/month
					$mPush[$elapsed+$link_info[$links[$goal][$x]['id']]['latency']][$links[$goal][$x]['to']] = $pChange;
					$mCnt[$elapsed+$link_info[$links[$goal][$x]['id']]['latency']][$links[$goal][$x]['to']] = abs($link_info[$links[$goal][$x]['id']]['magnitude']);
				}else{
					// Adds to an existing stack for this link/month
					$mPush[$elapsed+$link_info[$links[$goal][$x]['id']]['latency']][$links[$goal][$x]['to']] += $pChange;
					$mCnt[$elapsed+$link_info[$links[$goal][$x]['id']]['latency']][$links[$goal][$x]['to']] += abs($link_info[$links[$goal][$x]['id']]['magnitude']);
				}
			}
		}
	}
	
	// Propogate the traffic month by month
	function monthRun($month){
		global $mPush,$_GET,$nodes,$mCnt,$fp,$__scalePos,$__scaleNeg,$linksZero,$link_info;

		// Temporary -- if $month==0, may reflect as NULL.
		if($month==NULL) $month = 0;

		// Add any 'environmental stimulus' to the stack
		$mForce = array();
		for($x=1;$x<=$_GET['z'];$x++){
			if($_GET['often'.$x]!=0&&$month%$_GET['often'.$x]==0){
				if($month==0){
					$mPush[$month][$_GET['nodec'.$x]] = $_GET['mag'.$x];
					$mCnt[$month][$_GET['nodec'.$x]] = 1;
				}else{
					if(!isset($mPush[$month][$_GET['nodec'.$x]])){
						$mPush[$month][$_GET['nodec'.$x]] = 0; // to get access to the following foreach loop.
						$mCnt[$month][$_GET['nodec'.$x]] = 1;
					}
					$mForce[$_GET['nodec'.$x]] = $_GET['mag'.$x];
				}
			}
		}

		// Prepare the stack and pushes it through
		foreach($mPush[$month] as $key=>$value){
			$newValue = $value;
			// LDMR Type 1
			if($newValue>0) $newValue = log($newValue+1,$__scalePos);
			else $newValue = pow($__scaleNeg,$newValue)-1;
			// Push the stack through
			if(isset($mForce[$key])) walk($key,$month,$newValue,$_GET['maxtime'],$mForce[$key]);
			elseif($newValue!=0) walk($key,$month,$newValue,$_GET['maxtime']);
		}

		// Output data
		fwrite($fp,$month."\t");
		foreach($nodes as $key=>$value){
			// LDMR Type 2
			if($value>0) $value = log($value+1,$__scalePos);
			else $value = -1*log(abs($value)+1,$__scalePos);
			// Write to file
			$toWrite = round(($value)*10000000)/100000;
			fwrite($fp,$toWrite."\t");
		}
		fwrite($fp,"\n");

		// Move on to the next month
		if($month<$_GET['maxtime']) monthRun($month+1);
	}

	// MAIN CODE
	if(isset($_GET['act'])&&$_GET['act']=='calceffects'){
		
		// Set type of $_GET values
		settype($_GET['z'],'integer');
		for($w=1;$w<=$_GET['z'];$w++) settype($_GET['nodec'.$w],'integer');
		settype($_GET['maxtime'],'integer');

		// Define the stack for each month		
		for($x=0;$x<=$_GET['maxtime'];$x++){
			$mPush[$x] = array();
			$mCnt[$x] = array();
		}

		// Populate the $links, $link_info and $linksZero arrays
		$r2 = mysql_query('SELECT * FROM `links`',$link);
		while($r = mysql_fetch_assoc($r2)){
			if(!isset($links[$r['node_from']]))	$links[$r['node_from']] = array();
			$links[$r['node_from']][] = array();
			$links[$r['node_from']][count($links[$r['node_from']])-1]['to'] = $r['node_to'];
			$links[$r['node_from']][count($links[$r['node_from']])-1]['id'] = $r['link_id'];
			$link_info[$r['link_id']] = $r;
			// search for links with zero latency
			if($r['latency']==0) $linksZero[] = $r['link_id'];
		}

		// Populate the $nodes and $nodenames arrays
		$r2 = mysql_query('SELECT * FROM `nodes`',$link);
		while($r = mysql_fetch_assoc($r2)){
			$nodenames[$r['node_id']] = $r['name'];
			$nodes[$r['node_id']] = 0;
		}

		// Create TSV file and output headers
		$fp = fopen($_SESSION['db'].'/'.$_GET['fname'].'.xls','w');
		fwrite($fp,"Time Frame\t");
		$r2 = mysql_query('SELECT * FROM `nodes`',$link);
		while($r = mysql_fetch_assoc($r2)){
			fwrite($fp,$r['name']."\t");
		}
		fwrite($fp,"\n");

		// Start simulation
		$a = microtime(true);
		monthRun(0);
		$b = microtime(true);
		
		// Subtract the base value to obtain a percentage change
		foreach($nodes as $key=>$value){
			$nodes[$key] -= 1;
		}

		// Close file
		fclose($fp);
		
		print('Time taken: <b>'.(round(($b-$a)*1000)/1000).'</b> s.');

	}

?>