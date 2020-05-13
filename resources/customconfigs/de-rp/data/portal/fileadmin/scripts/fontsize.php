<?php
$ZoomLevel=(int)$_COOKIE['zoom'];
if($_GET['zoom']=='In' && $ZoomLevel<=2) $ZoomLevel++;
if($_GET['zoom']=='Out' && $ZoomLevel>=0) $ZoomLevel--;
setcookie('zoom',$ZoomLevel,0,'/');
$content='zoom'.$ZoomLevel;
?>