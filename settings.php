<?php
	include('config.inc.php');

	if(isset($_GET['sX'])){
		settype($_GET['sX'],'int');
		settype($_GET['sY'],'int');
		$_SESSION['sX'] = $_GET['sX']-140;
		$_SESSION['sY'] = $_GET['sY']-20;
	}

?>

<html>

<head>
	<title>Settings</title>
	<link rel='stylesheet' href='menu.css'>
</head>

<script>

	function submitForm(){
		document.location.href = "settings.php?sX="+document.getElementById("sX").value+"&sY="+document.getElementById("sY").value;
	}

</script>

<body>

	<?php
		include('nav.php');
	?>
	<h2>Settings</h2>

	<form method='post' action='settings.php'>
		Screen Resolution: <input type='text' id='sX' maxlength='4' style='width:50px;' value='<?php if(isset($_SESSION['sX'])){print($_SESSION['sX']);} ?>'> by <input type='text' style='width:50px;' id='sY' maxlength='4' value='<?php if(isset($_SESSION['sY'])){print($_SESSION['sY']);} ?>'> pixels
		<br><br>
		<input type='button' value='Update Settings' onclick='submitForm();'>
	</form>

</body>

</html>