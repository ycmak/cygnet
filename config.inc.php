<?php
	$link = mysql_connect('localhost','root','') or die('Cannot connect to DB');
	session_start();

	# READ LIST OF DATABASES
	$__DBLIST = array();
	$fp = fopen('db_list.php','r');
	while(!feof($fp)){
		$__DBLIST[] = trim(fgets($fp));
	}

	if(!isset($_SESSION['sX'])) $_SESSION['sX'] = 1280;
	if(!isset($_SESSION['sY'])) $_SESSION['sY'] = 700;

	if(!isset($_SESSION['db'])&&!isset($__SKIP_DB_CHECK)){
		header('Location:select_db.php');
		die();
	}elseif(!isset($__SKIP_DB_CHECK)){
		mysql_select_db($_SESSION['db'],$link) or die('Cannot select DB');
	}


	function getNodeName($node_id){
		global $link;
		$r2 = mysql_query('SELECT `name` FROM `nodes` WHERE `node_id`='.$node_id,$link);
		$r = mysql_fetch_assoc($r2);
		return $r['name'];
	}

?>