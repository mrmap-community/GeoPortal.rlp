<?php

$URL="http://".$_SERVER["SERVER_NAME"].$_SERVER['REDIRECT_URL'];

include_once("chkform.php");
include("language.php");

if($Lang=="en")
	$L=$Language["en"];
else 
	$L=$Language["de"];

global $Message;
global $Default;
global $Checkfields;

$Message["ChkError"] = $L["ChkError"];
$Message["ChkIncomplete"] = $L["ChkIncomplete"];
$Message["ChkGeneral"] = $L["ChkGeneral"];

$Message["Suchbegriff"] = $L["Suchbegriff"];
$Message["Suchbegriff"] = $L["ErrSuchbegriff"];

$Default["Suchbegriff"]=$L["Suchbegriff"];

$Checkfields="Suchbegriff,true,text";

if ($_REQUEST["act"]!="geosearch" || !chkFormular()) {
	if($_REQUEST["act"]!="geosearch") {
		foreach($Default as $key => $value) {
			$DATA[$key]=$value;
		}
	} else {
		foreach($_REQUEST as $key => $value) {
			$DATA[$key]=$value;
		}
	}		

?>
<script type="text/javascript">
<?php
foreach ($Message as $key => $value){
  print "message[\"".$key."\"] = \"".$value."\";\n";
}
foreach ($Default as $key => $value){
  print "vorgabe[\"".$key."\"] = \"".$value."\";\n";
}
?>
</script>

<form class="mygeoportal" action="<?php print $url;?>" method="post" onsubmit="return chkFormular(this);">

<h2><span><?php print $L["Suche"]; ?></span></h2>

<fieldset class="hidden">
<input type="hidden" name="act" value="geosearch" />
<input type="hidden" id="CHECK" name="CHECK" value="<?php print $Checkfields; ?>" />
<div class="clearer"></div>
</fieldset>

<fieldset>
<label class="text" for="suchbegriff"><strong title="Pflichtfeld"><?php print $L["Suchbegriff"]; ?></strong></label>
<input class="text" id="suchbegriff" type="text" name="Suchbegriff" maxlength="40" value="<?php print $DATA["Suchbegriff"]; ?>" onfocus="clearField(this)" />
<div class="clearer"></div>
</fieldset>

<fieldset class="control">
<input type="submit" value="<?php print $L["Suchen"]; ?>" />
</fieldset>

</form>


<?php
} else {
	print "Suche läuft...";
}

?>

