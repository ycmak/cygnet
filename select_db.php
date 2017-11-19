<?php

	$__SKIP_DB_CHECK = false;
	include('config.inc.php');

	if(isset($_GET['db'])){
		$_SESSION['db'] = $_GET['db'];
		header('Location:index.php');
		die();
	}
	
	if(isset($_GET['dbNew'])){
		$_GET['dbNew'] = 'cygnet_'.$_GET['dbNew'];
		mysql_query('CREATE DATABASE `'.$_GET['dbNew'].'`;',$link);
		mysql_select_db($_GET['dbNew'],$link);
		$sql = 'CREATE TABLE IF NOT EXISTS `links` (
		  `link_id` int(11) NOT NULL auto_increment,
		  `node_from` int(11) default NULL,
		  `node_to` int(11) default NULL,
		  `latency` int(11) default NULL,
		  `magnitude` float default NULL,
		  `notes` text NOT NULL,
		  PRIMARY KEY  (`link_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=100 ;';
		mysql_query($sql,$link);
		$sql = 'CREATE TABLE IF NOT EXISTS `nodes` (
		  `node_id` int(11) NOT NULL auto_increment,
		  `name` varchar(255) default NULL,
		  `desc` text,
		  `coord_x` int(11) default NULL,
		  `coord_y` int(11) default NULL,
		  PRIMARY KEY  (`node_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=37 ;';
		mysql_query($sql,$link);
		
		# APPEND TO DB LIST
		$fp = fopen('db_list.php','a+');
		fwrite($fp,"\n".$_GET['dbNew']);
		fclose($fp);
		$__DBLIST[] = $_GET['dbNew'];
		
		print('Database <b>'.$_GET['dbNew'].'</b> successfully created.<hr>');
	}
?>
<html>

<head>
	<title>Menu</title>
	<link rel='stylesheet' href='menu.css'>
</head>

<script>

	function submitForm(){
		document.location.href = "select_db.php?db="+document.getElementById("db_name").value;
	}
	
	function submitForm2(){
		document.location.href = "select_db.php?dbNew="+document.getElementById("dbnew").value;
	}

</script>

<body>

	<h2>Cygnet Login</h2>
	<form method='post'>

		Please select database:
		<select id='db_name'>
			<?php
				for($x=0;$x<count($__DBLIST);$x++){
					print('<option value="'.$__DBLIST[$x].'">'.$__DBLIST[$x].'</option>');
				}
			?>
		</select>
		<input type='button' value='Go!' onclick='submitForm();'>
		
        <p><b>Create New Database</b></p>
        <p>
        	Name: <input type='text' id='dbnew'> <input type='button' value='Create Database' onclick='submitForm2();'>
        </p>

	</form>

</body>

</html>