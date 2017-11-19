<?php
	include('config.inc.php');

	if(isset($_POST['act'])&&$_POST['act']=='addnode'){
		mysql_query('INSERT INTO `nodes`(`name`,`desc`,`coord_x`,`coord_y`) VALUES("'.$_POST['name'].'","'.$_POST['desc'].'","0","0")');
		print('<font color="green">Node added successfully.</font><hr>');
	}

	if(isset($_POST['act'])&&$_POST['act']=='editnode'){
		mysql_query('UPDATE `nodes` SET `name`="'.$_POST['name'].'",`desc`="'.$_POST['desc'].'" WHERE `node_id`='.$_POST['node_id'],$link);
		print('<font color="green">Node edited successfully.</font><hr>');
	}
?>

<html>

<head>
	<title>Edit Nodes</title>
	<link rel='stylesheet' href='menu.css'>
</head>

<body>

	<?php
		include('nav.php');
	?>
	<h2>Edit Nodes</h2>
	<?php
		if(isset($_GET['edit_id'])){
			$q2 = mysql_query('SELECT * FROM `nodes` WHERE `node_id`='.$_GET['edit_id'],$link);
			$q = mysql_fetch_assoc($q2);
			?>
			<h3>Modify Node</h3>
			<form method='post' action='editnodes.php'>
				Name: <textarea style='width:800px;height:30px;font-family:arial;font-size:10pt;' name='name'><?php print($q['name']); ?></textarea><br>
				Description: <textarea name='desc' style='width:600px;height:100px;color:black;font-family:arial;font-size:10pt;'><?php print($q['desc']); ?></textarea><br><br>
				<input type='hidden' name='act' value='editnode'>
				<input type='hidden' name='node_id' value='<?php print($q['node_id']); ?>'>
				<input type='submit' value='Modify Node'>
			</form>
			<?php
		}else{
			?>
			<h3>Add Node</h3>
			<form method='post' action='editnodes.php'>
				Name: <input type='text' name='name'><br>
				Description:<textarea name='desc' style='width:600px;height:100px;color:black;font-family:arial;font-size:10pt;'></textarea><br><br>
				<input type='hidden' name='act' value='addnode'>
				<input type='submit' value='Add Node'>
			</form>
			<?php
		}
	?>
	<h3>List of Nodes</h3>
	<?php
		$r2 = mysql_query('SELECT * FROM `nodes` ORDER BY `name` ASC',$link);
		if(mysql_num_rows($r2)>0){
			print('<ol>');
			while($r = mysql_fetch_assoc($r2)){
				print('<li>'.$r['name'].' [<a href="editnodes.php?edit_id='.$r['node_id'].'">edit</a>]');
				if(!empty($r['desc'])) print('<br><font style="font-size:8pt;color:orange;font-family:arial;">'.$r['desc'].'</font>');
				print('</li>');
			}
			print('</ol>');
		}else{
			print('No nodes available.');
		}
	?>

</body>

</html>