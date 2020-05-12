<?php
include_once(dirname(__FILE__)."/../../../../fileadmin/function/config.php");
include_once(dirname(__FILE__)."/../../../../fileadmin/function/function.php");
include_once(dirname(__FILE__)."/../../../../fileadmin/function/util.php");
include_once("search_functions.php");

GLOBAL $GUESTID, $Language;

if(t3lib_div::GPvar('L')==1) $L=$Language["en"];
else $L=$Language["de"];

$db=new DB_MYSQL;
$searchtext=SearchText();

if($searchtext=="") $searchtext=$L["Suchbegriff"];
//Eingabe Suchwörter
?>

<form action="<?php print $L["SuchURL"];?>" method="post">
<a href="/"><img id="logo_geoportal" src="fileadmin/design/logo_geoportal.png" alt="Logo: Geoportal Rheinland Pfalz" /></a>
<fieldset class="hidden">
<input name="act" type="hidden" value="search" />
<input name="selectsearch" type="hidden" value="0" />
</fieldset>
<fieldset>
	<input name="searchtext" class="text" type="text" value="<?php print PtH($searchtext); ?>" onfocus="if(this.value == '<?php print $L["Suchbegriff"];?>') { this.value = '' }" />
	<input class="btn" type="image" src="fileadmin/design/icn_search.png" alt="<?php print $L['Suchen']; ?>" name="Abschicken" />
</fieldset>
<p><a href="<?php print $L['ErwSuchGUIURL']; ?>"><?php print $L['ErwSuche']; ?></a></p>
</form>
