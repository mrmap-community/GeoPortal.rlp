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

//if($searchtext=="") $searchtext=$L["Suchbegriff"];
$spatial_CHK=($_SESSION["mb_user_spatial_suggest"]=='ja')?'checked="checked"':'';
//Eingabe Suchwörter
?>

<form action="<?php print $L["SuchURL"];?>" method="post">

<fieldset class="hidden">
<input name="act" type="hidden" value="search" />
<input name="selectsearch" type="hidden" value="0" />
</fieldset>
<fieldset>
	<a class="btn_erw_suche" href="<?php print $L['ErwSuchGUIURL']; ?>"  title="Erweiterte Suche starten" id="SucheStarten"> </a>
	<input name="searchtext" class="text" type="text" value="<?php print PtH($searchtext); ?>" onfocus="if(this.value == '<?php print $L["Suchbegriff"];?>') { this.value = '' }" placeholder="Geoportal durchsuchen..." />
	<input class="btn" type="image" src="fileadmin/design/icn_search_he.png" alt="<?php print $L['Suchen']; ?>" name="Abschicken" title="Suche starten" />
</fieldset>
</form>
