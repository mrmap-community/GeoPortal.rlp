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
$spatial_CHK=($_SESSION["mb_user_spatial_suggest"]=='ja')?'checked="checked"':'';
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
<fieldset>
	<input name="spatial" type="checkbox" value="ja" <?php print $spatial_CHK; ?> /> <?php print $L['Spatial']; ?>
</fieldset>
<p><table cols=2><tr><td><a href="<?php print $L['ErwSuchGUIURL']; ?>"><?php print $L['ErwSuche']; ?></a></td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td><a href="<?php print $L['keywordListURL']; ?>"><?php print $L['keywordList']; ?></a></td></tr></table></p>
</form>
