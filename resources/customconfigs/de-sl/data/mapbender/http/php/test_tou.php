<?php
require_once(dirname(__FILE__) . "/../classes/class_tou.php");

session_start();
Mapbender::session()->delete("acceptedTou");

$a = new tou();
$result = $a->check('wms',1243);
print_r(Mapbender::session()->get("acceptedTou"));
echo "<br>";
$result = $a->set('wms',1243);
print_r(Mapbender::session()->get("acceptedTou"));
echo "<br>";
$result = $a->check('wms',1243);
print_r(Mapbender::session()->get("acceptedTou"));
echo "<br>";
$result = $a->set('wfs',1248);
print_r(Mapbender::session()->get("acceptedTou"));
echo "<br>";
#print_r(Mapbender::session()->get("acceptedTou"));
#echo "<br>";
$result = $a->set('wms',1248);
print_r(Mapbender::session()->get("acceptedTou"));
echo "<br>";
$result = $a->check('wms',1248);

print_r($result);
print "<br><b>";
print($result['accepted']);
print "</b>";
echo "<br>";
$result = $a->set('wms',1248);
print_r(Mapbender::session()->get("acceptedTou"));

echo "<br>";


?>
