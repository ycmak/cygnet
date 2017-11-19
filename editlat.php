<?php
	include('config.inc.php');

	$r2 = mysql_query('SELECT * FROM `links`',$link);
	$cnt = 0;
	$totlat = 0;
	while($r = mysql_fetch_assoc($r2)){
		$cnt++;
		$totlat += $r['latency'];
	}
	print($_SESSION['db'].': average latency is '.round($totlat/$cnt*100)/100);

?>