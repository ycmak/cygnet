<?php

	include('config.inc.php');
	
	if(isset($_GET['node_id'])&&isset($_GET['cX'])&&isset($_GET['cY'])){
		$r2 = mysql_query('UPDATE `nodes` SET `coord_x`='.$_GET['cX'].',`coord_y`='.$_GET['cY'].' WHERE `node_id`='.$_GET['node_id'],$link);
		print('<font color="green">New node position saved.</font>');
	}
	
	if(isset($_GET['sX'])&&isset($_GET['sY'])){
		settype($_GET['sX'],'int');
		settype($_GET['sY'],'int');
		$_SESSION['sX'] = $_GET['sX']-140;
		$_SESSION['sY'] = $_GET['sY']-20;
		print('Done.');
	}

?>