<?php
	include('config.inc.php');

$xFac = 5;
$paksux = 0;
	function walk($goal,$elapsed,$effect,$maxtime){
		global $link,$links,$link_info,$_GET,$nodes,$max,$donemonth,$exofacs,$paksux,$n2m,$mPush,$xFac;
		if(isset($nodes[$goal])) $nodes[$goal] += ($effect); // linear scale, additive effect
		else $nodes[$goal] = $effect;
		if(abs($nodes[$goal])>$max) $max = abs($nodes[$goal]);
		# CHECK GLOBAL CALENDAR

		for($x=0;$x<count($links[$goal]);$x++){
			if($elapsed+$link_info[$links[$goal][$x]['id']]['latency']<$maxtime){
				//walk($links[$goal][$x]['to'],$elapsed+$link_info[$links[$goal][$x]['id']]['latency'],($link_info[$links[$goal][$x]['id']]['magnitude']/5*$effect),$maxtime); // linear scale, multiplicative effect
				#### NEW CODE ####
				$mPush[$elapsed+$link_info[$links[$goal][$x]['id']]['latency']][$links[$goal][$x]['to']] += ($link_info[$links[$goal][$x]['id']]['magnitude']/$xFac*$effect);
			}
		}
	}

	###################
	## NEW CODE ##
	###################
	
	$mPush = array();
	$mNodes = array();
	function monthRun($month){
		global $mPush,$_GET,$mNodes;
		// change the effect of affected nodes every month
		for($x=0;$x<count($_GET['z']);$x++){
			if($_GET['often']!=0&&$month%$_GET['often'.$x]==0){
				if(!isset($mPush[$month][$_GET['nodec'.$x]])) $mPush[$month][$_GET['nodec'.$x]] = $_GET['mag'.$x];
				else $mPush[$month][$_GET['nodec'.$x]] += $_GET['mag'.$x];
			}
		}
		if($month==NULL) $month = 0;
		// restart the walk for the month
		foreach($mPush[$month] as $key=>$value){
			walk($key,$month,$value,$_GET['maxtime']);
		}
		$mNodes[$month] = $nodes;
		if($month<$_GET['maxtime']) monthRun($month+1);
	}

	###################

	$y = false;
	$max = 0;
	$exofacs = array();
	$donemonth = array();
	$n2m = array();
	if(isset($_GET['stage'])) settype($_GET['stage'],'integer');
	if(isset($_GET['s2'])) settype($_GET['s2'],'integer');
	$_GET['stage'] = $_GET['s2'];
	if(isset($_GET['tframe'])){
		$tf = explode(',',$_GET['tframe']);
	}
	if(isset($_GET['act'])&&$_GET['act']=='calceffects'){
		$nodes = array();
		$links = array();
		$link_info = array();
		settype($_GET['z'],'integer');
		for($w=1;$w<=$_GET['z'];$w++) settype($_GET['nodec'.$w],'integer');

		
		settype($_GET['maxtime'],'integer');
		$_GET['maxtime'] = $tf[$_GET['stage']];
		print('Performing calculations for stage '.($_GET['stage']+1).' out of '.count($tf).'...<br>');
		print('Time Period: '.$tf[$_GET['stage']].' months<br><br>');
		// setup exofacs
		for($x=1;$x<=$_GET['maxtime'];$x++){
			$donemonth[$x] = true;
			$exofacs[$x] = array();
		}

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
		/*
		for($x=0;$x<count($links[$_GET['nodec']]);$x++){
			walk($links[$_GET['nodec']][$x]['to'],0,$_GET['mag']/5,$_GET['maxtime']); # added /5 here! NOTE!
		}
		*/

		for($x=1;$x<=$_GET['z'];$x++){
			$n2m[$_GET['nodec'.$x]] = $_GET['mag'.$x];
			if($_GET['often'.$x]>0){
				for($y=0;$y<=$_GET['maxtime'];$y+=$_GET['often'.$x]){
					$exofacs[$y][] = array();
					$exofacs[$y][count($exofacs[$y])-1]['node_id'] = $_GET['nodec'.$x];
					$exofacs[$y][count($exofacs[$y])-1]['mag'] = $_GET['mag'.$x];
					$donemonth[$y] = false;
				}
			}
			$nodes[$_GET['nodec'.$x]] = $_GET['mag'.$x];
			for($t=0;$t<count($links[$_GET['nodec'.$x]]);$t++){
				$mPush[0][$links[$_GET['nodec'.$x]][$t]['to']] = ($link_info[$links[$_GET['nodec'.$x]][$t]['id']]['magnitude']/$xFac*$_GET['mag'.$x]);
			}
		} // repeat this loop
		
		### START WALKING HERE ###
		monthRun(0);

		/*for($a=1;$a<=$_GET['z'];$a++){
			if($_GET['often'.$a]==0){
				$nodes[$_GET['nodec'.$a]] = $_GET['mag'.$a];
				for($x=0;$x<count($links[$_GET['nodec'.$a]]);$x++){
					walk($links[$_GET['nodec'.$a]][$x]['to'],0,$_GET['mag'.$a],$_GET['maxtime']); # added /5 here! NOTE!
				}
			}
		}*/
		// consider the effects of pumping it in.
		


		/*$r2 = mysql_query('SELECT * FROM `links` WHERE `node_from`='.$_GET['nodec'],$link);
		while($r = mysql_fetch_assoc($r2)){
			walk($r['node_to'],0,$_GET['mag'],$_GET['maxtime']);
		}
		*/

		# PRINTING TO FILE
		if($_GET['stage']==0){
			$fp = fopen($_GET['fname'].'.txt','w');
			fwrite($fp,"Time Frame\t");
			$r2 = mysql_query('SELECT * FROM `nodes`',$link);
			while($r = mysql_fetch_assoc($r2)){
				fwrite($fp,$r['name']."\t");
			}
			fwrite($fp,"\n");
			fwrite($fp,$tf[$_GET['stage']]."\t");
		}else{
			$fp = fopen($_GET['fname'].'.txt','a');
			fwrite($fp,$tf[$_GET['stage']]."\t");
		}
		
		# fetch names
		$nodenames = array();
		$r2 = mysql_query('SELECT * FROM `nodes`',$link);
//		$fp = fopen($_GET['fname'].'.txt','a');
//		fwrite($fp,$tf[$_GET['stage']]."\t");
		while($r = mysql_fetch_assoc($r2)){
			if(!isset($nodes[$r['node_id']])) $nodes[$r['node_id']] = 0;
//			print($r['name'].': '.$nodes[$r['node_id']].'<br>');
			fwrite($fp,$nodes[$r['node_id']]."\t");
			$nodenames[$r['node_id']] = $r['name'];
		}
		fwrite($fp,"\n");
		fclose($fp);
		
		# show results
		$y = true;
		$nodez = $nodes;
		/*
		print('<h2>Results</h2>');
		print('<p>If '.getNodeName($_GET['nodec']).' changes by '.$_GET['mag'].', then in a timeframe of '.$_GET['maxtime'].' months, the effects are:</p>');
		print('<ul>');
		foreach($nodes as $node_id=>$value){
			$c = ($value>0) ? 'green' : 'red';
			print('<li><big><font color="'.$c.'">'.$value.'</font></big> '.$nodenames[$node_id].'</li>');
		}
		print('</ul>');
		print('<hr>');
		*/
	}

# redirect
#############
## CAN BE MUCH MORE EFFICIENT. DO IN ONE SITTING.
#############

if($_GET['stage']<count($tf)-1){
	print('<script language="javascript">document.location.href="gfx4.php?'.$_SERVER['QUERY_STRING'].'&s2='.($_GET['stage']+1).'";</script>');
	die();
}else{
	//var_dump($tf);
	print('Generated file '.$_GET['fname'].'.txt.<br>');
	die('Calculation Complete.<br>');
}
//print('<script language="javascript">document.location.href="gfx2.php?'.$_SERVER['QUERY_STRING'].'&s2='.($_GET['stage']+1).'";</script>');
//die(' ');
?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.0//EN" "http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="1250" height="1000" onclick='clickHandler(evt);'>

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