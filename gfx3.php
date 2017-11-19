<?php
	include('config.inc.php');
	header("Content-type: image/svg+xml");
	print('<?xml version="1.0" encoding="UTF-8" standalone="no"?>');
$paksux = 0;
$xFac = 5;

	
	function walk($goal,$elapsed,$effect,$maxtime){
		global $link,$links,$link_info,$_GET,$nodes,$max,$donemonth,$exofacs,$paksux,$n2m,$mPush,$xFac;
		if(isset($nodes[$goal])) $nodes[$goal] += ($effect); // linear scale, additive effect
		else $nodes[$goal] = $effect;
		if(abs($nodes[$goal])>$max) $max = abs($nodes[$goal]);
		# CHECK GLOBAL CALENDAR

		for($x=0;$x<count($links[$goal]);$x++){
		//print($elapsed+$link_info[$links[$goal][$x]['id']]['latency'].'|');
			if($elapsed+$link_info[$links[$goal][$x]['id']]['latency']<$maxtime){
				#### NEW CODE ####
				$mPush[$elapsed+$link_info[$links[$goal][$x]['id']]['latency']][$links[$goal][$x]['to']] += ($link_info[$links[$goal][$x]['id']]['magnitude']/$xFac*$effect);
			}
		}
	}

	###################
	## NEW CODE ##
	###################
	$mPush = array();
	function monthRun($month){
		global $mPush,$_GET;
		// change the effect of affected nodes every month
		if($month==NULL) $month = 0;
		for($x=1;$x<=$_GET['z'];$x++){
			if($_GET['often'.$x]!=0&&$month%$_GET['often'.$x]==0){
				if($month==0){
					$mPush[$month][$_GET['nodec'.$x]] = $_GET['mag'.$x];
				}else{
					if(!isset($mPush[$month][$_GET['nodec'.$x]])) $mPush[$month][$_GET['nodec'.$x]] = $_GET['mag'.$x];
					else $mPush[$month][$_GET['nodec'.$x]] += $_GET['mag'.$x];
				}
				//print($mPush[$month][$_GET['nodec'.$x]].'a');
			}
		}
		// restart the walk for the month
		foreach($mPush[$month] as $key=>$value){
			walk($key,$month,$value,$_GET['maxtime']);
		}
//		print($month.'asd');
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
		settype($_GET['z'],'integer');
		for($w=1;$w<=$_GET['z'];$w++) settype($_GET['nodec'.$w],'integer');

		
		settype($_GET['maxtime'],'integer');

		###############
		## CATER FOR MONTH
		###############
		
		for($x=0;$x<=$_GET['maxtime'];$x++){
			$mPush[$x] = array();
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
		}

//		var_dump($mPush);
//		die();

	//	for($x=1;$x<=$_GET['z'];$x++){
	/*		if($_GET['often'.$x]>0){
				for($m=0;$m<=$_GET['maxtime'];$m+=$_GET['often'.$x]){
					$mPush[$m][$_GET['nodec'.$x]] = $_GET['mag'.$x];
					//$nodes[$_GET['nodec'.$x]] = $_GET['mag'.$x];
	/*				for($t=0;$t<count($links[$_GET['nodec'.$x]]);$t++){
						$mPush[$m][$links[$_GET['nodec'.$x]][$t]['to']] = ($link_info[$links[$_GET['nodec'.$x]][$t]['id']]['magnitude']/$xFac*$_GET['mag'.$x]);
					}*/
				//}
		//	}else{
				//$mPush[0][$_GET['nodec'.$x]] = $_GET['mag'.$x];
		//	}
	//	} // repeat this loop

		### START WALKING HERE ###
		monthRun(0);		

		// consider the effects of pumping it in.
		# fetch names
		$nodenames = array();
		$r2 = mysql_query('SELECT * FROM `nodes`',$link);
		while($r = mysql_fetch_assoc($r2)){
			$nodenames[$r['node_id']] = $r['name'];
		}
		# show results
		$y = true;
		$nodez = $nodes;
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
	
	<defs>
	  <marker id="endArrow" viewBox="0 0 10 10" refX="1" refY="5" markerUnits="strokeWidth" orient="auto" markerWidth="5" markerHeight="4">
		 <polyline points="0,0 10,5 0,10 1,5" fill="darkblue" opacity='0.2' />
	  </marker>

	  <marker id="startArrow" viewBox="0 0 10 10" refX="1" refY="5" markerUnits="strokeWidth" orient="auto" markerWidth="5" markerHeight="4">
		 <polyline points="10,0 0,5 10,10 9,5" fill="darkblue" opacity='0.2' />
	  </marker>

	</defs>

	<rect width="150" height="40" x="0" y="0" fill="black" />
	<text x="20" y="30" style="font-family:arial;font-size:14pt;font-weight:bold;" fill="orange">C  Y  G  N  E  T</text>

	<?php
		$nodes = array();
		$links = array();
		$r2 = mysql_query('SELECT * FROM `nodes`',$link);
		while($r = mysql_fetch_assoc($r2)){
			$r['coord_x'] = round($r['coord_x']*$_SESSION['sX']/1250);
			$r['coord_y'] = round($r['coord_y']*$_SESSION['sY']/1000);
			$nodes[$r['node_id']] = $r;
			?>
			<g>
				<?php
					$fill = 'lavender';
					for($w=1;$w<=$_GET['z'];$w++){
						if($r['node_id']==$_GET['nodec'.$w]) $fill = "purple";
						//else $fill = "lavender";
					}
				?>
				<rect id="node_<?php print($r['node_id']); ?>" width="30" height="30" x="<?php print($r['coord_x']); ?>" y="<?php print($r['coord_y']); ?>" fill="<?php print($fill); ?>" />
				<text id="ntxt_<?php print($r['node_id']); ?>" width="30" style='font-family:arial;font-size:8pt;width:30px;' x="<?php print($r['coord_x']); ?>" y="<?php print($r['coord_y']); ?>">
					<?php print($r['name']); ?>
				</text>
				<?php
					if($y){
						if($nodez[$r['node_id']]>0) $fcolor = "green";
						elseif($nodez[$r['node_id']]<0) $fcolor = "red";
						else $fcolor = "black";
						print('<text fill="'.$fcolor.'" style="font-family:arial;font-size:8pt;font-weight:bold;" x="'.($r['coord_x']+30).'" y="'.($r['coord_y']+10).'">'.(round($nodez[$r['node_id']]*1000)/1000).'</text>'."\n");
					}
				?>
				<?php
					if($y){
						if($nodez[$r['node_id']]>0){
							$tmp = dechex(round(255-hexdec("FF")*($nodez[$r['node_id']]/$max)));
							if($tmp==0) $tmp = "00";
							$fcolor = '#'.$tmp.'FF'.$tmp;
							$opacity = round($nodez[$r['node_id']]/$max*100)/200+0.5;
						}
						elseif($nodez[$r['node_id']]<0){
							$tmp = dechex(round(255-hexdec("FF")*(abs($nodez[$r['node_id']])/$max)));
							if($tmp==0) $tmp = "00";
							$fcolor = '#FF'.$tmp.$tmp;
							$opacity = round(abs($nodez[$r['node_id']])/$max*100)/200+0.5;
						}
						//$opacity = 1;
						if($nodez[$r['node_id']]!=0){
							$pn = $nodez[$r['node_id']]/abs($nodez[$r['node_id']]);
							print('<rect x="'.($r['coord_x']+11).'" y="'.($r['coord_y']+15-max($pn*round(sqrt(abs(320*$nodez[$r['node_id']]))),0)).'" width="10" height="'.round(sqrt(320*abs($nodez[$r['node_id']]))).'" fill="'.$fcolor.'" opacity="'.$opacity.'" />');
						}
					}
				?>
			</g>
			<?php
		}
		$r2 = mysql_query('SELECT * FROM `links`',$link);
		while($r = mysql_fetch_assoc($r2)){
			?>
			<line id='<?php print($r['link_id']); ?>' x1="<?php print($nodes[$r['node_from']]['coord_x']+15); ?>" y1="<?php print($nodes[$r['node_from']]['coord_y']+15); ?>" x2="<?php print($nodes[$r['node_to']]['coord_x']+15); ?>" y2="<?php print($nodes[$r['node_to']]['coord_y']+15); ?>" stroke="<?php if($r['magnitude']>0){print('green');}else{print('red');} ?>" stroke-opacity='0.2' stroke-width="<?php print(abs($r['magnitude'])); ?>"  marker-end="url(#endArrow)" onclick='handleLinkSelect(<?php print($r['link_id']); ?>,this,<?php print($r['latency']); ?>);'>
			</line>
			<?php
		}
			if($y){
				print('<rect x="890" y="938" width="500" height="75" fill="#EEEEEE" />');
				//print('<text x="900" y="950" style="font-family:arial;font-size:8pt;font-weight:bold;" fill="blue">Factor: '.$nodes[$_GET['nodec']]['name'].'</text>');
				print('<text x="900" y="950" style="font-family:arial;font-size:8pt;font-weight:bold;" fill="blue">Factor: '.$paksux.'</text>');
				print('<text x="900" y="960" style="font-family:arial;font-size:8pt;font-weight:bold;" fill="blue">Mag: '.$_GET['mag'].'</text>');
				print('<text x="900" y="970" style="font-family:arial;font-size:8pt;font-weight:bold;" fill="blue">Timeframe: '.$_GET['maxtime'].'</text>');
			}
	?>

</svg>					
<?php
	/*
marker-start="url(#startArrow)"
	*/
?>