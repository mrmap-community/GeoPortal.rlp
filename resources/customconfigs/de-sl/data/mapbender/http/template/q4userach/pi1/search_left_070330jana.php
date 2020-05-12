<?php
include_once(dirname(__FILE__)."/../../../../fileadmin/function/config.php");
include_once(dirname(__FILE__)."/../../../../fileadmin/function/util.php");
include_once(dirname(__FILE__)."/../../../../fileadmin/function/function.php");
include_once("search_functions.php");

GLOBAL $GUESTID, $Language;

if(t3lib_div::GPvar('L')==1) $L=$Language["en"];
else $L=$Language["de"];

$db=new DB_MYSQL;
$searchtext=SearchText();

if($searchtext=="") $searchtext=$L["Suchbegriff"];
//Eingabe Suchwörter
?>

<div class="box" id="suche">
<h2><span><?php print $L["Suche"]; ?></span><a href="<?php print $L["HilfeSucheURL"]; ?>"><img src="fileadmin/design/images/questionmark.gif" width="14" height="14" alt="<?php print $L["Hilfe"];?>" title="<?php print $L["HilfezurSuche"];?>" /></a></h2>
<form class="search" action="<?php print $L["SuchURL"];?>" method="post">
<fieldset class="hidden">
<input name="act" type="hidden" value="search" />
<input name="selectsearch" type="hidden" value="0" />
</fieldset>
<fieldset class="data">
<label for="searchsearchtext"><?php print $L["Suchbegriff"];?></label>
<input name="searchtext" id="searchsearchtext" type="text" value="<?php print PtH($searchtext); ?>" onfocus="if(this.value == '<?php print $L["Suchbegriff"];?>') { this.value = '' }" />
</fieldset>

<fieldset class="control">
<input type="submit" value="<?php print $L["Suchen"];?>" />
</fieldset>
</form>

<ul>
<li><a href="<?php print $L["KeywordlisteURL"]; ?>"><?php print $L["Keywordliste"]; ?></a></li>
<li><a href="<?php print $L["DienstelisteURL"]; ?>"><?php print $L["Diensteliste"]; ?></a></li>
<li><a href="<?php print $L["DatenanbieterlisteURL"]; ?>"><?php print $L["Datenanbieterliste"]; ?></a></li>
</ul>

</div>

<?php
// Abgespeicherte Suchen
if($_SESSION["mb_user_id"]!=$GUESTID) {
?>

<div class="box" id="gespeicherte_suche">
<h2><span><?php print $L["Gespeicherte Suchen"]; ?></span><a href="<?php print $L["HilfeSucheURL"]; ?>"><img src="fileadmin/design/images/questionmark.gif" width="14" height="14" alt="<?php print $L["Hilfe"];?>" title="<?php print $L["HilfezugespeichertenSuchen"];?>" /></a></h2>

<?php
$sql="SELECT * FROM search WHERE uid='".$_SESSION["mb_user_id"]."' AND lastsearch=1 ORDER BY datetime desc";
$db->query($sql);
if($db->num_rows()) {
?>
<form id="savedsearch" action="<?php print $L["SuchURL"];?>" method="post">
<fieldset class="hidden">
<input name="act" type="hidden" value="search" />
<input name="selectsearch" type="hidden" value="1" />
</fieldset>
<fieldset class="data">
<select name="searchid" onchange="document.getElementById('savedsearch').submit();">
<?php
  $db->next_record();
	if($_REQUEST["searchid"]!="" && $_REQUEST["searchid"]==$db->f("id") && $_REQUEST["selectsearch"]=="1") {
		print "<option value=\"".$db->f("id")."\" selected=\"selected\">".$db->f("name")."</option>\n";
	} else {
		print "<option value=\"".$db->f("id")."\">".$L["letzte Suche"]."</option>\n";
	}

	$sql="SELECT * FROM search WHERE uid='".$_SESSION["mb_user_id"]."' AND lastsearch=0 ORDER BY name";
	$db->query($sql);
	if($db->num_rows()) {
	  while($db->next_record()) {
	  	if($_REQUEST["searchid"]!="" && $_REQUEST["searchid"]==$db->f("id") && $_REQUEST["selectsearch"]=="1") {
	  		print "<option value=\"".$db->f("id")."\" selected=\"selected\">".$db->f("name")."</option>\n";
	  	} else {
	  		print "<option value=\"".$db->f("id")."\">".$db->f("name")."</option>\n";
	  	}
	  }
	}
?>
</select>
</fieldset>
<fieldset class="control">
<input type="submit" value="<?php print $L["Aufrufen"];?>" />
</fieldset>
</form>

<?php
} else {
	print "<p>Sie haben noch keine Suche gestartet.</p>";
}
?>
</div>

<?php
}
?>
