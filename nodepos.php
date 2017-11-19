<?php
	include('config.inc.php');
?>

<html>

<head>
	<title>Edit Links</title>
	<style>
		body{
			margin: 0;
			background-color: white;
		}
	</style>
</head>

<script language='javascript'>

	function nerve(node_id,cX,cY){
		hello = new XMLHttpRequest();
		hello.open("GET", "brain.php?node_id="+node_id+"&cX="+cX+"&cY="+cY);
		hello.onreadystatechange = function(){
			if(hello.readyState==4){
				updatepage(hello.responseText);
			}
		}
		hello.send(null);
	}

	function updatepage(txt){
		document.getElementById("ticker").innerHTML = txt;
	}

	highlightedNode = 0;
	function selectNode(node_id){
		highlightedNode = node_id;
		document.getElementById("n_"+node_id).style.backgroundColor = "orange";
		document.getElementById("t_"+node_id).style.fontWeight = "bold";
		document.getElementById("ticker").innerHTML = "Node selected.";
	}

	function setNode(node_id,cX,cY){
		document.getElementById("n_"+node_id).style.backgroundColor = "lavender";
		document.getElementById("t_"+node_id).style.fontWeight = "normal";
/*		document.getElementById("n_"+node_id).style.left = (cX)+"px";
		document.getElementById("n_"+node_id).style.top = (cY)+"px";
		document.getElementById("t_"+node_id).style.left = (cX)+"px";
		document.getElementById("t_"+node_id).style.top = (cY)+"px"; */
		highlightedNode = 0;
		nerve(node_id,cX,cY);
	}

	ft = false;
	function clickHandler(event){
		if(highlightedNode!=0&&!ft){
			setNode(highlightedNode,event.clientX,event.clientY);
		}else{

		}
	}

	function moveHandler(event){
		if(highlightedNode!=0){
			document.getElementById("n_"+highlightedNode).style.left = (event.clientX)+"px";
			document.getElementById("n_"+highlightedNode).style.top = (event.clientY)+"px";
			document.getElementById("t_"+highlightedNode).style.left = (event.clientX)+"px";
			document.getElementById("t_"+highlightedNode).style.top = (event.clientY-10)+"px";
		}
	}

</script>

<body>

	<div style='width:1250px;height:1000px;border:1px solid black;' onclick='clickHandler(event);ft=false;' onmousemove='moveHandler(event);'>
		<?php
			$r2 = mysql_query('SELECT * FROM `nodes` ORDER BY `node_id` ASC',$link);
			while($r = mysql_fetch_assoc($r2)){
				$cX = ($r['coord_x']!=0) ? $r['coord_x'] : rand(50,1150);
				$cY = ($r['coord_y']!=0) ? $r['coord_y'] : rand(50,950);
				$col = ($r['coord_x']!=0&&$r['coord_y']!=0) ? 'white' : 'purple';
				?>
				<div id='n_<?php print($r['node_id']); ?>' style='position:absolute;left:<?php print($cX); ?>;top:<?php print($cY); ?>;background-color:lavender;width:30px;height:30px;border:2px solid <?php print($col); ?>' onclick='selectNode(<?php print($r['node_id']); ?>);ft=true;'></div>
				<div id='t_<?php print($r['node_id']); ?>' style='position:absolute;left:<?php print($cX); ?>;top:<?php print($cY-10); ?>;font-size:7pt;color:black;font-family:arial;'><?php print($r['name']); ?></div>
				<?php
			}
		?>
		<div id='ticker' style='position:absolute;top:0;left:0;border:1px solid silver;width:1240px;padding-left:10px;height:20px;font-size:8pt;font-family:arial;font-weight:bold;color:black;padding-top:10px;' onclick='nerve();'>Ready.</div>
	</div>

</body>

</html>