<?php

	include('config.inc.php');

	die('This is still under construction.');

	function walk($node_id,$depth){
		global $links,$cnt,$paths,$maxdepth;
		$paths[$cnt][] = $node_id;
		$this_cnt = $cnt;
		if($depth==$maxdepth) return false;
		for($x=0;$x<count($links[$node_id]);$x++){
			$cnt++;
			$paths[$cnt] = $paths[$this_cnt];
			walk($links[$node_id][$x]['to'],$depth+1);
		}
	}

	print('Generating Loops...<br>');
	$links = array();
	$paths = array();
	$loops = array();
	$maxdepth = 3;
	$r2 = mysql_query('SELECT * FROM `links`',$link);
	while($r = mysql_fetch_assoc($r2)){
		if(!isset($links[$r['node_from']]))
			$links[$r['node_from']] = array();
		$links[$r['node_from']][] = array();
		$links[$r['node_from']][count($links[$r['node_from']])-1]['to'] = $r['node_to'];
		$links[$r['node_from']][count($links[$r['node_from']])-1]['id'] = $r['link_id'];
		$link_info[$r['link_id']] = $r;
	}

	$cnt = 0;
	foreach($links as $node_id=>$link){
		$paths[$cnt] = array();
		walk($node_id,0);
		$cnt++;
	}
	print('Generated '.$cnt.' paths.<br>');
	$cnt_loops = 0;
	for($x=0;$x<count($paths);$x++){
		$taken = array();
		$loop = false;
		for($y=0;$y<count($paths[$x]);$y++){
			if(isset($taken[$paths[$x][$y]])){
				$cnt_loops++;
				# extract the loop
				$looping = true;
				$i = 0;
				$rec = false;
				$loops[$cnt_loops] = '';
				while($looping){
					if($paths[$x][$i]==$paths[$x][$y]){
						if($rec){
							$looping = false;
							$rec = false;
						}elseif(!$rec) $rec = true;
					}
					if($rec){
						$loops[$cnt_loops] .= $paths[$x][$i].'|';
					}
					$i++;
				}
				break;
			}else{
				$taken[$paths[$x][$y]] = 1;
			}
		}
	}
	print('Extracted '.$cnt_loops.' loops.<br>');
	# unique loops
	function unique($a,$b){
		if(count($a)!=count($b)) return false;
		for($x=0;$x<count($b);$x++){
			if($b[$x]==$a[0]) $bi = $x;
			break;
		}
		if(!isset($bi)) return false;
		for($x=0;$x<count($a);$x++){
			if($a[$x]!=$b[$bi]) return false;
			if($bi==count($a)-1) $bi = 0;
			else $bi++;
		}
		return true;
	}
	for($x=0;$x<count($loops);$x++){
		$a = explode('|',$loops[$x]);
		unset($a[count($a)-1]);
		for($y=0;$y<$x;$y++){
			$b = explode('|',$loops[$y]);
			unset($b[count($b)-1]);
			if(!unique($a,$b)){
				$loops[$x] = null;
				//break 2;
			}
		}
	}
	$uloops = array();
	for($x=0;$x<count($loops);$x++){
		if($loops[$x]!=null) $uloops[] = $loops[$x];
	}
	print('Extracted '.count($uloops).' unique loops.<hr>');
	var_dump($uloops);
	/*for($x=0;$x<count($loops);$x++){
		print($loops[$x].'<br>');
	}*/
	

?>