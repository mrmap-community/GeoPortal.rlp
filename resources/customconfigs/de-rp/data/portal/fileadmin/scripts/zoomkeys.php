<?php
$URL=$_SERVER["REQUEST_URI"];
if(($pos=strpos($URL,"?"))!==false) {
	$URL=substr($URL,0,$pos);
}
$URL.='?';

if($_GET['L']==1) {
	print 'Font:
		<a href="'.$URL.getParams('In').'">bigger</a> |
		<a href="'.$URL.getParams('Out').'">smaller</a>
	';
} else {
	print 'Schrift:
		<a href="'.$URL.getParams('In').'">größer</a> |
		<a href="'.$URL.getParams('Out').'">kleiner</a>
	';
}

function getParams($do) {
	$IgnoreParams=array(
		"zoom"
	);

	$param="";
	$array=array_merge($_GET,$_POST);
	if(is_array($array)){
		foreach($array AS $key=>$value) {
			if(!in_array($key,$IgnoreParams)) {
				$param.=htmlspecialchars($key).'='.htmlspecialchars($value).'&amp;';
			}
		}
	}
	$param.='zoom='.$do;
	return $param;
}
?>