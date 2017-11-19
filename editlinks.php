<?php
	include('config.inc.php');

	if(isset($_POST['act'])&&$_POST['act']=='addlink'){
		mysql_query('INSERT INTO `links`(`node_from`,`node_to`,`latency`,`magnitude`,`notes`) VALUES("'.$_POST['node_from'].'","'.$_POST['node_to'].'","'.$_POST['latency'].'","'.$_POST['magnitude'].'","'.$_POST['notes'].'")');
		print('<font color="green">Link added successfully.</font><hr>');
	}

	if(isset($_POST['act'])&&$_POST['act']=='editlink'){
		mysql_query('UPDATE `links` SET `node_from`='.$_POST['node_from'].',`node_to`='.$_POST['node_to'].',`latency`='.$_POST['latency'].',`magnitude`='.$_POST['magnitude'].',`notes`="'.$_POST['notes'].'" WHERE `link_id`='.$_POST['link_id'],$link);
		print('<font color="green">Link modified successfully.</font><hr>');
	}

	if(isset($_GET['del_id'])){
		mysql_query('DELETE FROM `links` WHERE `link_id`='.$_GET['del_id'],$link);
		print('<font color="red">Link deleted successfully.</font><hr>');
	}
?>

<html>

<head>
	<title>Edit Links</title>
	<link rel='stylesheet' href='menu.css'>
</head>

<body>

	<?php
		include('nav.php');
	?>
	
	<div style='float:left;width:400px;'>
	<h2>Manage Links</h2>
	<?php
		if(isset($_GET['edit_id'])){
			$q2 = mysql_query('SELECT * FROM `links` WHERE `link_id`='.$_GET['edit_id'],$link);
			$q = mysql_fetch_assoc($q2);
			?>
			<h3>Modify Link</h3>
			<form method='post' action='editlinks.php'>
				From: 
				<select name='node_from'>
					<?php
						$r2 = mysql_query('SELECT * FROM `nodes` ORDER BY `name` ASC',$link);
						while($r = mysql_fetch_assoc($r2)){
							if($q['node_from']==$r['node_id']) $sel = ' SELECTED';
							else $sel = '';
							print('<option value="'.$r['node_id'].'"'.$sel.'>'.$r['name'].'</option>');
						}
					?>
				</select><br>
				To: 
				<select name='node_to'>
					<?php
						$r2 = mysql_query('SELECT * FROM `nodes` ORDER BY `name` ASC',$link);
						while($r = mysql_fetch_assoc($r2)){
							if($q['node_to']==$r['node_id']) $sel = ' SELECTED';
							else $sel = '';
							print('<option value="'.$r['node_id'].'"'.$sel.'>'.$r['name'].'</option>');
						}
					?>
				</select><br>
				Scale Factor: <input type='text' name='magnitude' value='<?php print($q['magnitude']); ?>'> (small: 0.25; moderate: 0.5; high: 0.75) (temporarily)<br>
				Latency: ~<input type='text' name='latency' value='<?php print($q['latency']); ?>'> months<br> 
				Notes:<br><textarea name='notes' style='width:400px;height:100px;font-size:10pt;font-family:arial;color:olive;'><?php print($q['notes']); ?></textarea><br>
				<input type='hidden' name='act' value='editlink'>
				<input type='hidden' name='link_id' value='<?php print($q['link_id']); ?>'>
				<input type='submit' value='Edit Link'>
				<input type='button' value='Delete Link' style='color:red;font-weight:bold;' onclick='document.location.href="editlinks.php?del_id=<?php print($q['link_id']); ?>";'>
				<input type='button' value='Cancel' onclick='document.location.href="editlinks.php";'>
			</form>
			<?php
		}else{
			?>
			<h3>Add Link</h3>
			<form method='post' action='editlinks.php'>
				From: 
				<select name='node_from'>
					<?php
						$r2 = mysql_query('SELECT * FROM `nodes` ORDER BY `name` ASC',$link);
						while($r = mysql_fetch_assoc($r2)){
							print('<option value="'.$r['node_id'].'">'.$r['name'].'</option>');
						}
					?>
				</select><br>
				To: 
				<select name='node_to'>
					<?php
						$r2 = mysql_query('SELECT * FROM `nodes` ORDER BY `name` ASC',$link);
						while($r = mysql_fetch_assoc($r2)){
							print('<option value="'.$r['node_id'].'">'.$r['name'].'</option>');
						}
					?>
				</select><br>
				Scale Factor: <input type='text' name='magnitude'> (small: 0.25; moderate: 0.5; high: 0.75) (temporarily)<br>
				Latency: ~<input type='text' name='latency'> months<br> 
				Notes:<br><textarea name='notes' style='width:400px;height:100px;font-size:10pt;font-family:arial;color:olive;'></textarea><br>
				<input type='hidden' name='act' value='addlink'>
				<input type='submit' value='Add Link'>
			</form>
			<?php
		}
	?>
	</div>
	
	<div style='float:right;width:700px;overflow-y:scroll;height:800px;padding-left:50px;border-left:1px dotted orange;'>
	<h3>List of Links</h3>
	<?php
		$r2 = mysql_query('SELECT * FROM `nodes` ORDER BY `name` ASC',$link);
		if(mysql_num_rows($r2)>0){
			print('<ol>');
			while($r = mysql_fetch_assoc($r2)){
				$e2 = mysql_query('SELECT * FROM `links` WHERE `node_from`='.$r['node_id'],$link);
				if(mysql_num_rows($e2)){
					print('<li><b>'.$r['name'].'</b>');
					if(!empty($r['desc'])) print('<br><font style="font-size:8pt;font-family:arial;color:orange;">'.$r['desc'].'</font>');
					print('</li>');
					print('<ul>');
					while($e = mysql_fetch_assoc($e2)){
						print('<li>'.$e['magnitude'].' to '.getNodeName($e['node_to']).' (latency: '.$e['latency'].', ID: '.$e['link_id'].') [<a href="editlinks.php?edit_id='.$e['link_id'].'">edit</a>]');
						if(isset($e['notes'])&&!empty($e['notes']))
							print('<br><font style="color:olive;font-size:8pt;">'.$e['notes'].'</font>');
						print('</li>');
					}
					print('</ul><br>');
				}
			}
			print('</ol>');
		}else{
			print('No nodes available.');
		}
	?>
	</div>

</body>

</html>