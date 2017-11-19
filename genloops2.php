<?php

	include('config.inc.php');

	/*$fp = fopen('primes.dat','r');
	while(!feof($fp)) $g = fgets($fp);
	$primes = explode(' ',$g);
	fclose($fp);
	*/
	
	$cnt = 0;
	// CONSTANTS
	$maxLength = (isset($_GET['maxLength'])) ? $_GET['maxLength'] : 8;
	$maxLat = (isset($_GET['maxLat'])) ? $_GET['maxLat'] : 48;
	settype($maxLength, 'int');
	settype($maxLat, 'int');
	$maxLength++;

	// ARRAYS
	$n = array();
	$l = array();
	$lat = array();
	$loops = array();	// index is the loop itself
						// $loops is assoc array
	$loops2 = array();
	$hasht = array();
	$nh = array();

	// EXTRACT DATA FROM DB
	$r2 = mysql_query('SELECT * FROM `nodes`',$link);
	$x = 0;
	while($r = mysql_fetch_assoc($r2)){
		$n[] = $r;
		$nh[$r['node_id']] = $primes[$x];
		$x++;
	}

	$r2 = mysql_query('SELECT * FROM `links`',$link);
	while($r = mysql_fetch_assoc($r2)){
		if(!isset($l[$r['node_from']])) $l[$r['node_from']] = array();
		if(!isset($lat[$r['node_from']])) $lat[$r['node_from']] = array();
		$l[$r['node_from']][] = $r['node_to'];
		$lat[$r['node_from']][$r['node_to']] = $r['latency'];
	}

	// FUNCTIONS
	function calcLoopHash($path,$newnode){
		global $loops,$cnt,$loops2;
		$cnt++;
		// calculate loop hash
		$pathE = explode('|',substr($path,1));
		$pathE2 = array();
		$started = false;
		//$this_hash = 1;
		for($x=0;$x<count($pathE);$x++){
			if($pathE[$x]==$newnode){
				$started = true;
			}
			if($started) $pathE2[] = $pathE[$x];
			//$this_hash *= $nh[$pathE[$x]];
		}
		$pathE = $pathE2;
		// check for hash to eliminate loops early
		//if(isset($hasht[$this_hash])) return false;
		// reorientate loop
		$bigE = 0;
		$bigI = 0;
		for($x=0;$x<count($pathE);$x++){
			if($pathE[$x]>$bigE){
				$bigE = $pathE[$x];
				$bigI = $x;
			}
		}
		$part1 = array_slice($pathE,$bigI);
		if($bigI>0) $part2 = array_slice($pathE,0,$bigI);
		else $part2 = array();
		$newPath = implode('|',array_merge($part1,$part2));
		if(!isset($loops[$newPath])){
			$loops[$newPath] = 1;
			$loops2[$newPath] = array_merge($part1,$part2);
			$hasht[$this_hash] = 1;
		}
	}

	# TODO: consider merging functions of $path and $xn? compare performance...

	function walk($path,$pathLength,$pathLat,$xn,$newnode){
		global $maxLength,$maxLat,$n,$l,$lat,$cnt;
		// checks for depth/latency
		if($pathLength==$maxLength||$pathLat>=$maxLat) return false;
		// continues the walk
		for($x=0;$x<count($l[$newnode]);$x++){
			if(isset($xn[$l[$newnode][$x]])){
				// if node has occured, terminate walk
				calcLoopHash($path,$l[$newnode][$x]);
			}else{
				// if new node, continue walk
				$xn2 = $xn;
				$xn2[$l[$newnode][$x]] = 1;
				walk($path.'|'.$l[$newnode][$x],$pathLength+1,$pathLat+$lat[$newnode][$l[$newnode][$x]],$xn2,$l[$newnode][$x]);
			}
		}
	}

	$a = microtime(true);
	$xn = array();
	for($x=0;$x<count($n);$x++){
		walk('',0,0,$xn,$n[$x]['node_id']);
	}
	$b = microtime(true);

	// SORT BY LATENCY
	$tlength = array();
	$key2 = array();
	foreach($loops as $key=>$value){
		$tlength[$key] = 0;
		$key2[$key] = '';
		for($x=0;$x<count($loops2[$key])-1;$x++){
			$tlength[$key] += $lat[$loops2[$key][$x]][$loops2[$key][$x+1]];
			$key2[$key] .= substr($n[$loops2[$key][$x]]['name'],0,100).' &raquo; ';
		}
		$tlength[$key] += $lat[$loops2[$key][count($loops2[$key])-1]][$loops2[$key][0]];
		$key2[$key] .= substr($n[$loops2[$key][count($loops2[$key])-1]]['name'],0,100).' &raquo; '.substr($n[$loops2[$key][0]]['name'],0,100);
	}
	
	asort($tlength);

	// PRINT LOOPS
	$cnt2 = 0;
	print('<p>Click on a feedback loop to display it.</p>');
	print('<table id="flTable" cellspacing="0">');
	print('<tr><th>No.</th><th>No. of Nodes</th><th>Length of Loop</th><th>Elements</th></tr>');
	foreach($tlength as $key=>$value){
		if(count($loops2[$key])>2){
			$cnt2++;
			$ty = floor($tlength[$key]/12);
			$tm = $tlength[$key]%12;
			$bgcolor = ($cnt2%2==1) ? 'black' : '#333333';
			print('<tr bgcolor="'.$bgcolor.'" onmouseover="this.bgColor=\'gray\';this.style.cursor=\'pointer\';" onmouseout="this.bgColor=\''.$bgcolor.'\';" onclick="floopShow(\''.$key.'\');"><td>'.$cnt2.'</td><td>'.count($loops2[$key]).'</td><td>'.$ty.'y '.$tm.'m</td><td style="text-align:left;font-size:8pt;">'.$key2[$key].'</td></tr>');
			//print($cnt.' <a href="javascript:floopShow(\''.$key.'\');">'.$key.'</a> ('.$tlength[$key].' months, '.count($loops2[$key]).' elements)<br>');
		}
	}
	print('</table>');
	print('<p>Total loops: '.$cnt2.'; Total paths considered: '.$cnt.'<br>');
	print('Execution Time: '.($b-$a).' seconds.</p>');

?>