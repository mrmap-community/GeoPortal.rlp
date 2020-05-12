<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$xml = new DOMDocument();
$xml->encoding = "UTF-8";
$xml->formatOutput = true;
$xml->load("/data/mapbender/http/tmp/".$_REQUEST["file"]);
echo '<?xml version="1.0" encoding="UTF-8"?>
'.$xml->saveHTML();
?>
