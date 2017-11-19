<?php
	include('config.inc.php');

	function walk($goal,$elapsed,$effect,$maxtime){
		global $link,$_POST,$nodes;
		if(isset($nodes[$goal])) $nodes[$goal] += ($effect/5); // logarithmic scale, additive effect
		else $nodes[$goal] = $effect;
		$r2 = mysql_query('SELECT * FROM `links` WHERE `node_from`='.$goal,$link);
		if(mysql_num_rows($r2)>0){
			while($r = mysql_fetch_assoc($r2)){
				//print($elapsed+$r['latency'].'<br>');
				//print('walk('.$r['node_to'].','.($elapsed+$r['latency']).','.($r['magnitude']/5*$effect).','.$maxtime.');<br>');
				if($elapsed+$r['latency']<$maxtime){
					walk($r['node_to'],$elapsed+$r['latency'],($r['magnitude']/5*$effect),$maxtime); // linear scale, multiplicative effect
				}
			}
		}
	}

	if(isset($_POST['act'])&&$_POST['act']=='calceffects'){
		$nodes = array();
		settype($_POST['nodec'],'integer');
		$nodes[$_POST['nodec']] = $_POST['mag'];
		settype($_POST['maxtime'],'integer');
		$r2 = mysql_query('SELECT * FROM `links` WHERE `node_from`='.$_POST['nodec'],$link);
		while($r = mysql_fetch_assoc($r2)){
			walk($r['node_to'],0,$_POST['mag'],$_POST['maxtime']);
		}
		# fetch names
		$nodenames = array();
		$r2 = mysql_query('SELECT * FROM `nodes`',$link);
		while($r = mysql_fetch_assoc($r2)){
			$nodenames[$r['node_id']] = $r['name'];
		}
		# show results
		print('<h2>Results</h2>');
		print('<p>If '.getNodeName($_POST['nodec']).' changes by '.$_POST['mag'].', then in a timeframe of '.$_POST['maxtime'].' months, the effects are:</p>');
		print('<ul>');
		foreach($nodes as $node_id=>$value){
			$c = ($value>0) ? 'green' : 'red';
			print('<li><big><font color="'.$c.'">'.$value.'</font></big> '.$nodenames[$node_id].'</li>');
		}
		print('</ul>');
		print('<hr>');
	}
?>

<html>

<head>
	<title>System Map</title>
	<link rel='stylesheet' href='menu.css'>
</head>

<body>

	<?php
		include('nav.php');
	?>
	<h2>Check Node Influence</h2>
	<form method='post' action='smap.php'>
		Node to Change:
		<select name='nodec'>
			<?php
				$r2 = mysql_query('SELECT * FROM `nodes` ORDER BY `name` ASC',$link);
				while($r = mysql_fetch_assoc($r2)){
					print('<option value="'.$r['node_id'].'">'.$r['name'].'</option>');
				}
			?>
		</select><br>
		Magnitude of Change: <input type='text' name='mag'> (scale of -5 to 5)<br>
		Time to Elapse: Stop after <input type='text' name='maxtime'> months.<br><br>
		<input type='hidden' name='act' value='calceffects'>
		<input type='submit' value='Calculate Effects'>
	</form>

</body>

</html>