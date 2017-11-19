<?php
	include('config.inc.php');
	header("Content-type: image/svg+xml");
	print('<?xml version="1.0" encoding="UTF-8" standalone="no"?>');

	$paksux = '';
	$xFac = 5;
	$__scalePos = M_E*2;
	$__scaleNeg = M_E/2;

	function walk($goal,$elapsed,$effect,$maxtime,$tmpPush=0){
		global $link,$links,$link_info,$_GET,$nodes,$max,$mPush,$mPush2,$xFac,$mCnt,$paksux,$__scalePos,$__scaleNeg;
		
		$nodes[$goal] += $effect;
		if($tmpPush!=0){
			$tmp = $tmpPush*(abs($nodes[$goal])+1); // POINT OF CONTENTION: *(abs($nodes[$goal])+1) ? the +1 part? then -1 later?
//			if($tmp>0) $tmp = log($tmp+1,$__scalePos);
//			else $tmp = -1*log(abs($tmp)+1,$__scalePos);
			$nodes[$goal] += $tmp;
			$effect += $tmp;
		}

		# for display purposes
		if(abs($nodes[$goal])>$max) $max = abs($nodes[$goal]);

		# $effect should always be a CHANGE.
		# node shd always be the ref value at t=0 (initially 1).

		for($x=0;$x<count($links[$goal]);$x++){
			if($elapsed+$link_info[$links[$goal][$x]['id']]['latency']<$maxtime){
				$pChange = $effect*$link_info[$links[$goal][$x]['id']]['magnitude'];
				if(!isset($mPush[$elapsed+$link_info[$links[$goal][$x]['id']]['latency']][$links[$goal][$x]['to']])){
					$mPush[$elapsed+$link_info[$links[$goal][$x]['id']]['latency']][$links[$goal][$x]['to']] = $pChange;
					$mPush2[$elapsed+$link_info[$links[$goal][$x]['id']]['latency']][$links[$goal][$x]['id']] = $pChange;
					$mCnt[$elapsed+$link_info[$links[$goal][$x]['id']]['latency']][$links[$goal][$x]['to']] = abs($link_info[$links[$goal][$x]['id']]['magnitude']);
					//$paksux += $pChange;
				}else{
					$mPush[$elapsed+$link_info[$links[$goal][$x]['id']]['latency']][$links[$goal][$x]['to']] += $pChange;
					$mPush2[$elapsed+$link_info[$links[$goal][$x]['id']]['latency']][$links[$goal][$x]['id']] += $pChange;
					$mCnt[$elapsed+$link_info[$links[$goal][$x]['id']]['latency']][$links[$goal][$x]['to']] += abs($link_info[$links[$goal][$x]['id']]['magnitude']);
					//$paksux += $pChange;
				}
			}
		}
	}

	###################
	## NEW CODE ##
	###################
	$mPush = array();
	$mPush2 = array();
	$mCnt = array();
	function monthRun($month){
		global $mPush,$_GET,$nodes,$mCnt,$paksux,$__scalePos,$__scaleNeg,$link_info,$linksZero;
		// change the effect of affected nodes every month
		if($month==NULL) $month = 0;
		$mForce = array();
		for($x=1;$x<=$_GET['z'];$x++){
			if($_GET['often'.$x]!=0&&$month%$_GET['often'.$x]==0){
				if($month==0){
					# any reason to have the following line?
					//$nodes[$_GET['nodec'.$x]] = 1+$_GET['mag'.$x]; # 1 is the initial value of the node.
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

		// factor in links which may have zero latency... update mPush
		/*
		for($x=0;$x<count($linksZero);$x++){
			if(isset($mPush[$month][$link_info[$linksZero[$x]]['node_from']])){
				$newValue = $mPush[$month][$link_info[$linksZero[$x]]['node_from']];
				# BOTTOM AT -100 LDMR CORRECTION
				//if($newValue>0) $newValue = log($newValue+1,$__scalePos);
				//else $newValue = pow($__scaleNeg,$newValue)-1;
				# update the end node's mPush
				$mPush[$month][$link_info[$linksZero[$x]]['node_to']] += $link_info[$linksZero[$x]]['magnitude'] * $newValue;
			}
		}
		*/

		// restart the walk for the month
		foreach($mPush[$month] as $key=>$value){
			$newValue = $value;
		
			# BOTTOM AT -100 LDMR CORRECTION
			if($newValue>0) $newValue = log($newValue+1,$__scalePos);
			else $newValue = pow($__scaleNeg,$newValue)-1;
		
			if(isset($mForce[$key])) walk($key,$month,$newValue,$_GET['maxtime'],$mForce[$key]);
			elseif($newValue!=0) walk($key,$month,$newValue,$_GET['maxtime']);
		}
		if($month<$_GET['maxtime']) monthRun($month+1);
	}

	###################

	$y = false;
	$max = 0;
	$exofacs = array();
	$donemonth = array();
	$n2m = array();
	if(isset($_GET['act'])&&$_GET['act']=='calceffects'){
		$nodes = array();
		$links = array();
		$link_info = array();
		$linksZero = array();
		settype($_GET['z'],'integer');
		for($w=1;$w<=$_GET['z'];$w++) settype($_GET['nodec'.$w],'integer');

		
		settype($_GET['maxtime'],'integer');

		###############
		## CATER FOR MONTH
		###############
		
		for($x=0;$x<=$_GET['maxtime'];$x++){
			$mPush[$x] = array();
			$mPush2[$x] = array();
			$mCnt[$x] = array();
		}

		###############

		// new less memory intensive method
		$r2 = mysql_query('SELECT * FROM `links`',$link);
		while($r = mysql_fetch_assoc($r2)){
			if(!isset($links[$r['node_from']]))
				$links[$r['node_from']] = array();
			$links[$r['node_from']][] = array();
			$links[$r['node_from']][count($links[$r['node_from']])-1]['to'] = $r['node_to'];
			$links[$r['node_from']][count($links[$r['node_from']])-1]['id'] = $r['link_id'];
			$link_info[$r['link_id']] = $r;
			// search for links with zero latency
			if($r['latency']==0) $linksZero[] = $r['link_id'];
		}

		# fetch names
		$nodenames = array();
		$r2 = mysql_query('SELECT * FROM `nodes`',$link);
		while($r = mysql_fetch_assoc($r2)){
			$nodenames[$r['node_id']] = $r['name'];
			$nodes[$r['node_id']] = 0;
		}

		### START WALKING HERE ###
		monthRun(0);
		
		foreach($nodes as $key=>$value){
			## LOG METHOD OF LDMR
			if($value>0) $nodes[$key] = log($value+1,$__scalePos);
			else $nodes[$key] = -1*log(abs($value)+1,$__scalePos);
		}

		# show results
		$y = true;
		$nodez = $nodes;

		# LINK TRAFFIC CODE

		if(isset($_GET['mode'])&&$_GET['mode']=='traffic'){
			$traffic = array();
			$trafficMax = 0;
			foreach($link_info as $key=>$value){
				$traffic[$key] = 0;
				for($x=0;$x<$_GET['maxtime'];$x++){
					$traffic[$key] += abs($mPush2[$x][$key]);
				}
				if($traffic[$key]>$trafficMax) $trafficMax = $traffic[$key];
			}
		}

	}

?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.0//EN" "http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="<?php print($_SESSION['sX']); ?>" height="<?php print($_SESSION['sY']); ?>" onclick='clickHandler(evt);'>

	<script>
		<![CDATA[

			linkz = new Array();
			<?php
				$r2 = mysql_query('SELECT * FROM `links`',$link);
				while($r = mysql_fetch_assoc($r2)){
					print('linkz['.$r['link_id'].'] = new Object;'."\n");
					print('linkz['.$r['link_id'].'].node_from = '.$r['node_from'].';'."\n");
					print('linkz['.$r['link_id'].'].node_to = '.$r['node_to'].';'."\n");
					print('linkz['.$r['link_id'].'].selected = false;'."\n");
				}
			?>
			
			dragged = null;
			function startDrag(that){
				dragged = that;
				that.setAttributeNS(null, "fill", "blue");
			}

			function stopDrag(evt){
				dragged.setAttributeNS(null, "x", evt.clientX);
				dragged.setAttributeNS(null, "y", evt.clientY);
				//window.alert(evt.clientX);
			}

			function clickHandler(evt){
				if(dragged!=null) stopDrag(evt);
				return false;
			}

			function handleLinkSelect(link_id,that,latency){
				if(!linkz[link_id].selected){
					linkz[link_id].selected = true;
					that.setAttributeNS(null, "stroke-opacity", 1);
					document.getElementById("node_"+linkz[link_id].node_from).setAttributeNS(null, "fill", "yellow");
					document.getElementById("node_"+linkz[link_id].node_to).setAttributeNS(null, "fill", "orange");
					document.getElementById("ntxt_"+linkz[link_id].node_from).setAttributeNS(null, "font-weight", "bold");
					document.getElementById("ntxt_"+linkz[link_id].node_to).setAttributeNS(null, "font-weight", "bold");
					window.alert('Latency: '+latency+' months.');
				}else{
					linkz[link_id].selected = false;
					that.setAttributeNS(null, "stroke-opacity", 0.2);
					document.getElementById("node_"+linkz[link_id].node_from).setAttributeNS(null, "fill", "lavender");
					document.getElementById("node_"+linkz[link_id].node_to).setAttributeNS(null, "fill", "lavender");
					document.getElementById("ntxt_"+linkz[link_id].node_from).setAttributeNS(null, "font-weight", "normal");
					document.getElementById("ntxt_"+linkz[link_id].node_to).setAttributeNS(null, "font-weight", "normal");
				}
			}

		]]>
	</script>
	
	<?php
		$tO = (isset($_GET['mode'])&&$_GET['mode']=='floops') ? 0.7 : 0.1;
		$tC = (isset($_GET['mode'])&&$_GET['mode']=='floops') ? 'lime' : 'yellow';
		//$tC2 = (isset($_GET['mode'])&&$_GET['mode']=='floops') ? 'orange' : 'yellow';
		$tC2 = 'orange';
	?>

	<defs>
	  <marker id="endArrow" viewBox="0 0 10 10" refX="7" refY="5" markerUnits="strokeWidth" orient="auto" markerWidth="5" markerHeight="5">
		 <polyline points="0,0 10,5 0,10 3,5" fill="<?php print($tC); ?>" opacity='<?php print($tO); ?>' />
	  </marker>

	  <marker id="endArrow2" viewBox="0 0 10 10" refX="7" refY="5" markerUnits="strokeWidth" orient="auto" markerWidth="5" markerHeight="5">
		 <polyline points="0,0 10,5 0,10 3,5" fill="<?php print($tC2); ?>" opacity='1' />
	  </marker>

	  <marker id="startArrow" viewBox="0 0 10 10" refX="1" refY="5" markerUnits="strokeWidth" orient="auto" markerWidth="5" markerHeight="5">
		 <polyline points="10,0 0,5 10,10 9,5" fill="orange" opacity='1' />
	  </marker>

	</defs>

	<rect width="150" height="40" x="0" y="0" fill="black" />
	<text x="20" y="30" style="font-family:arial;font-size:14pt;font-weight:bold;" fill="orange">C  Y  G  N  E  T</text>

	<?php

		if($y){
			print('<rect x="0" y="938" width="100%" height="75" fill="#EEEEEE" />');
			//print('<text x="900" y="950" style="font-family:arial;font-size:8pt;font-weight:bold;" fill="blue">Factor: '.$nodes[$_GET['nodec']]['name'].'</text>');
			print('<text x="10" y="950" style="font-family:arial;font-size:8pt;font-weight:bold;" fill="blue">Traffic Max: '.$trafficMax.'</text>');
			print('<text x="10" y="960" style="font-family:arial;font-size:8pt;font-weight:bold;" fill="blue">Mag: '.$_GET['mag'].'</text>');
			print('<text x="10" y="970" style="font-family:arial;font-size:8pt;font-weight:bold;" fill="blue">Timeframe: '.$_GET['maxtime'].'</text>');
		}

		$nodes = array();
		$links = array();

		$r2 = mysql_query('SELECT * FROM `nodes`',$link);
		while($r = mysql_fetch_assoc($r2)){
			$r['coord_x'] = round($r['coord_x']*$_SESSION['sX']/1250);
			$r['coord_y'] = round($r['coord_y']*$_SESSION['sY']/1000);
			$nodes[$r['node_id']] = $r;
		}

		#####################
		# PRINTING OF LINKS #
		#####################
		if(!isset($traffic)&&!isset($_GET['mode'])){
			###########################
			# REGULAR LINKS AND ARROWS
			###########################
			$r2 = mysql_query('SELECT * FROM `links`',$link);
			while($r = mysql_fetch_assoc($r2)){
				$sw = (abs($r['magnitude'])>1) ? $r['magnitude'] : abs(1/$r['magnitude'])*2;
				//$sw *= 2;
				$sw += 2;
				if($nodes[$r['node_from']]['coord_x']>$nodes[$r['node_to']]['coord_x'])	$xD = $nodes[$r['node_from']]['coord_x']-$nodes[$r['node_to']]['coord_x'];
				else $xD = $nodes[$r['node_to']]['coord_x']-$nodes[$r['node_from']]['coord_x'];
				if($nodes[$r['node_from']]['coord_y']>$nodes[$r['node_to']]['coord_y'])	$yD = $nodes[$r['node_from']]['coord_y']-$nodes[$r['node_to']]['coord_y'];
				else $yD = $nodes[$r['node_to']]['coord_y']-$nodes[$r['node_from']]['coord_y'];
				
				$angle = atan2($yD,$xD);

				if($nodes[$r['node_from']]['coord_x']>$nodes[$r['node_to']]['coord_x'])	$x2 = $nodes[$r['node_from']]['coord_x']-50*cos($angle)+15;
				else $x2 = $nodes[$r['node_from']]['coord_x']+50*cos($angle)+15;
				
				if($nodes[$r['node_from']]['coord_y']>$nodes[$r['node_to']]['coord_y'])	$y2 = $nodes[$r['node_from']]['coord_y']-50*sin($angle)+15;
				else $y2 = $nodes[$r['node_from']]['coord_y']+50*sin($angle)+15;
				?>
				<line id='<?php print($r['link_id']); ?>' x1="<?php print($nodes[$r['node_from']]['coord_x']+15); ?>" y1="<?php print($nodes[$r['node_from']]['coord_y']+15); ?>" x2="<?php print($x2); ?>" y2="<?php print($y2); ?>" stroke="<?php if($r['magnitude']>0){print('black');}else{print('black');} ?>" stroke-opacity='1' stroke-width="2"  marker-end="url(#endArrow2)">
				</line>


				<line id='<?php print($r['link_id']); ?>' x1="<?php print($nodes[$r['node_from']]['coord_x']+15); ?>" y1="<?php print($nodes[$r['node_from']]['coord_y']+15); ?>" x2="<?php print($nodes[$r['node_to']]['coord_x']+15); ?>" y2="<?php print($nodes[$r['node_to']]['coord_y']+15); ?>" stroke="<?php if($r['magnitude']>0){print('blue');}else{print('red');} ?>" stroke-opacity='0.2' stroke-width="<?php print($sw); ?>"  marker-end="url(#endArrow)" onclick='handleLinkSelect(<?php print($r['link_id']); ?>,this,<?php print($r['latency']); ?>);'>
				</line>
				<?php
			}
		}elseif(isset($_GET['mode'])&&$_GET['mode']=='floops'){
			####################
			# FEEDBACK LOOPS
			####################
			$fnodes = explode('|',$_GET['nodes']);
			$flinks2 = array();
			for($x=0;$x<count($fnodes)-1;$x++){
				$flinks2[$fnodes[$x]] = $fnodes[$x+1];
			}
			$flinks2[$fnodes[count($fnodes)-1]] = $fnodes[0];
			$r2 = mysql_query('SELECT * FROM `links`',$link);
			$tLat = 0;

			while($r = mysql_fetch_assoc($r2)){
				if(isset($flinks2[$r['node_from']])&&$flinks2[$r['node_from']]==$r['node_to']){
					$tLat += $r['latency'];
					$sw = (abs($r['magnitude'])>1) ? $r['magnitude'] : abs(1/$r['magnitude'])*2;
					$sw += 2;
					$so = 0.5;

					if($nodes[$r['node_from']]['coord_x']>$nodes[$r['node_to']]['coord_x'])	$xD = $nodes[$r['node_from']]['coord_x']-$nodes[$r['node_to']]['coord_x'];
					else $xD = $nodes[$r['node_to']]['coord_x']-$nodes[$r['node_from']]['coord_x'];
					if($nodes[$r['node_from']]['coord_y']>$nodes[$r['node_to']]['coord_y'])	$yD = $nodes[$r['node_from']]['coord_y']-$nodes[$r['node_to']]['coord_y'];
					else $yD = $nodes[$r['node_to']]['coord_y']-$nodes[$r['node_from']]['coord_y'];
					
					$angle = atan2($yD,$xD);

					if($nodes[$r['node_from']]['coord_x']>$nodes[$r['node_to']]['coord_x'])	$x2 = $nodes[$r['node_from']]['coord_x']-50*cos($angle)+15;
					else $x2 = $nodes[$r['node_from']]['coord_x']+50*cos($angle)+15;
					
					if($nodes[$r['node_from']]['coord_y']>$nodes[$r['node_to']]['coord_y'])	$y2 = $nodes[$r['node_from']]['coord_y']-50*sin($angle)+15;
					else $y2 = $nodes[$r['node_from']]['coord_y']+50*sin($angle)+15;

					?>
					<line id='<?php print($r['link_id']); ?>' x1="<?php print($nodes[$r['node_from']]['coord_x']+15); ?>" y1="<?php print($nodes[$r['node_from']]['coord_y']+15); ?>" x2="<?php print($nodes[$r['node_to']]['coord_x']+15); ?>" y2="<?php print($nodes[$r['node_to']]['coord_y']+15); ?>" stroke="<?php if($r['magnitude']>0){print('blue');}else{print('red');} ?>" stroke-opacity='<?php print($so); ?>' stroke-width="<?php print($sw); ?>"  onclick='handleLinkSelect(<?php print($r['link_id']); ?>,this,<?php print($r['latency']); ?>);'>
					</line>
					<line id='<?php print($r['link_id']); ?>' x1="<?php print($nodes[$r['node_from']]['coord_x']+15); ?>" y1="<?php print($nodes[$r['node_from']]['coord_y']+15); ?>" x2="<?php print($x2); ?>" y2="<?php print($y2); ?>" stroke="<?php if($r['magnitude']>0){print('black');}else{print('black');} ?>" stroke-opacity='1' stroke-width="5"  marker-end="url(#endArrow2)">
					</line>
				<?php
				}
			}
			$_y = floor($tLat/12);
			$_m = $tLat%12;
			print('<text x="170" y="30" style="font-family:arial;font-size:12pt;font-weight:bold;" fill="black">Length of Loop: '.$_y.' years '.$_m.' months</text>');
		}else{
			####################
			# TRAFFC SIMULATION
			####################
			$r2 = mysql_query('SELECT * FROM `links`',$link);
			while($r = mysql_fetch_assoc($r2)){
				$sw = round(pow($traffic[$r['link_id']],1))*1+1;
				$so = ($sw/sqrt($trafficMax))*0.9+0.1;

				if($nodes[$r['node_from']]['coord_x']>$nodes[$r['node_to']]['coord_x'])	$xD = $nodes[$r['node_from']]['coord_x']-$nodes[$r['node_to']]['coord_x'];
				else $xD = $nodes[$r['node_to']]['coord_x']-$nodes[$r['node_from']]['coord_x'];
				if($nodes[$r['node_from']]['coord_y']>$nodes[$r['node_to']]['coord_y'])	$yD = $nodes[$r['node_from']]['coord_y']-$nodes[$r['node_to']]['coord_y'];
				else $yD = $nodes[$r['node_to']]['coord_y']-$nodes[$r['node_from']]['coord_y'];
				
				$angle = atan2($yD,$xD);

				if($nodes[$r['node_from']]['coord_x']>$nodes[$r['node_to']]['coord_x'])	$x2 = $nodes[$r['node_from']]['coord_x']-50*cos($angle)+15;
				else $x2 = $nodes[$r['node_from']]['coord_x']+50*cos($angle)+15;
				
				if($nodes[$r['node_from']]['coord_y']>$nodes[$r['node_to']]['coord_y'])	$y2 = $nodes[$r['node_from']]['coord_y']-50*sin($angle)+15;
				else $y2 = $nodes[$r['node_from']]['coord_y']+50*sin($angle)+15;

				?>
				<line id='<?php print($r['link_id']); ?>' x1="<?php print($nodes[$r['node_from']]['coord_x']+15); ?>" y1="<?php print($nodes[$r['node_from']]['coord_y']+15); ?>" x2="<?php print($nodes[$r['node_to']]['coord_x']+15); ?>" y2="<?php print($nodes[$r['node_to']]['coord_y']+15); ?>" stroke="<?php if($r['magnitude']>0){print('blue');}else{print('red');} ?>" stroke-opacity='<?php print($so); ?>' stroke-width="<?php print($sw); ?>"  marker-end="url(#endArrow)" onclick='handleLinkSelect(<?php print($r['link_id']); ?>,this,<?php print($r['latency']); ?>);'>
				</line>

				<line id='<?php print($r['link_id']); ?>' x1="<?php print($nodes[$r['node_from']]['coord_x']+15); ?>" y1="<?php print($nodes[$r['node_from']]['coord_y']+15); ?>" x2="<?php print($x2); ?>" y2="<?php print($y2); ?>" stroke="<?php if($r['magnitude']>0){print('black');}else{print('black');} ?>" stroke-opacity='1' stroke-width="2"  marker-end="url(#endArrow2)">
				</line>
				<?php
			}
		}

		#####################
		# PRINTING OF NODES #
		#####################
		$r2 = mysql_query('SELECT * FROM `nodes`',$link);
		while($r = mysql_fetch_assoc($r2)){
			$r['coord_x'] = round($r['coord_x']*$_SESSION['sX']/1250);
			$r['coord_y'] = round($r['coord_y']*$_SESSION['sY']/1000);
			$nodes[$r['node_id']] = $r;
			?>
			<g>
				<?php
					$fill = '#888888';
					for($w=1;$w<=$_GET['z'];$w++){
						if($r['node_id']==$_GET['nodec'.$w]) $fill = "purple";
						//else $fill = "lavender";
					}
				?>
				<rect id="node_<?php print($r['node_id']); ?>" width="30" height="30" x="<?php print($r['coord_x']); ?>" y="<?php print($r['coord_y']); ?>" fill="<?php print($fill); ?>" stroke="black" stroke-width="1" stroke-opacity="1" opacity="1" />
				<text id="ntxt_<?php print($r['node_id']); ?>" width="30" style='font-family:arial;font-size:8pt;width:30px;font-weight:bold;' x="<?php print($r['coord_x']); ?>" y="<?php print($r['coord_y']-3); ?>">
					<?php print($r['name']); ?>
				</text>
				<?php
					if($y){
						if($nodez[$r['node_id']]>0) $fcolor = "green";
						elseif($nodez[$r['node_id']]<0) $fcolor = "red";
						else $fcolor = "black";
						print('<text fill="'.$fcolor.'" style="font-family:arial;font-size:10pt;font-weight:bold;" x="'.($r['coord_x']+30).'" y="'.($r['coord_y']+14).'">'.(round($nodez[$r['node_id']]*100000)/1000).'%</text>'."\n");
					}
				?>
				<?php
					if($y){
						if($nodez[$r['node_id']]>0){
/*							$tmp = dechex(round(255-hexdec("FF")*($nodez[$r['node_id']]/$max)));
							if($tmp==0) $tmp = "00";
							$fcolor = '#'.$tmp.'FF'.$tmp;
							$opacity = round($nodez[$r['node_id']]/$max*100)/200+0.5;
*/
							$fcolor = 'lime';
						}
						elseif($nodez[$r['node_id']]<0){
/*							$tmp = dechex(round(255-hexdec("FF")*(abs($nodez[$r['node_id']])/$max)));
							if($tmp==0) $tmp = "00";
							$fcolor = '#FF'.$tmp.$tmp;
							$opacity = round(abs($nodez[$r['node_id']])/$max*100)/200+0.5;
*/
							$fcolor = 'red';
						}
						$opacity = 1;
						if($nodez[$r['node_id']]!=0){
							$pn = $nodez[$r['node_id']]/abs($nodez[$r['node_id']]);
							print('<rect x="'.($r['coord_x']+11).'" y="'.($r['coord_y']+15-max($pn*round(50*abs($nodez[$r['node_id']])),0)).'" width="10" height="'.round(50*abs($nodez[$r['node_id']])).'" fill="'.$fcolor.'" opacity="'.$opacity.'" />');
						}
					}
				?>
			</g>
			<?php
		}
	?>

</svg>