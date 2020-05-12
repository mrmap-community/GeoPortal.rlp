<?php
require_once(dirname(__FILE__)."/../classes/class_metadata_new.php");
debugMD("metadataWrite0");
$userId = $_SERVER["argv"][1];
$searchId = $_SERVER["argv"][2];
$searchText = $_SERVER["argv"][3];
$registratingDepartments = $_SERVER["argv"][4];
$isoCategories = $_SERVER["argv"][5];
$inspireThemes = $_SERVER["argv"][6];
$timeBegin = $_SERVER["argv"][7];
$timeEnd = $_SERVER["argv"][8];
$regTimeBegin = $_SERVER["argv"][9];
$regTimeEnd = $_SERVER["argv"][10];
$maxResults = $_SERVER["argv"][11];
$searchBbox = $_SERVER["argv"][12];
$searchTypeBbox = $_SERVER["argv"][13];
$accessRestrictions = $_SERVER["argv"][14];
$languageCode = $_SERVER["argv"][15];
$searchEPSG = $_SERVER["argv"][16];
$searchResources = $_SERVER["argv"][17];
$searchPages = $_SERVER["argv"][18];
$outputFormat = $_SERVER["argv"][19];
$resultTarget = $_SERVER["argv"][20];
$searchURL = $_SERVER["argv"][21];
$customCategories = $_SERVER["argv"][22];
$hostName = $_SERVER["argv"][23];
$orderBy = $_SERVER["argv"][24];
$resourceIds = $_SERVER["argv"][25];
$restrictToOpenData = $_SERVER["argv"][26];
debugMD("metadataWrite1");

$metadata = new searchMetadata($userId, $searchId, $searchText, $registratingDepartments, $isoCategories, $inspireThemes, $timeBegin, $timeEnd, $regTimeBegin, $regTimeEnd, $maxResults, $searchBbox, $searchTypeBbox, $accessRestrictions, $languageCode, $searchEPSG, $searchResources, $searchPages, $outputFormat, $resultTarget, $searchURL, $customCategories, $hostName, $orderBy, $resourceIds, $restrictToOpenData);
?>
