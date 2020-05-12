<?php
include_once(dirname(__FILE__)."/../../../../fileadmin/function/config.php");
include_once(dirname(__FILE__)."/../../../../fileadmin/function/util.php");
include_once(dirname(__FILE__)."/../../../../fileadmin/function/function.php");
include_once("search_functions.php");

GLOBAL $GUESTID, $Language;

if(t3lib_div::GPvar('L')==1) $L=$Language["en"];
else $L=$Language["de"];

$db=new DB_MYSQL;

// Abgespeicherte Suchen
if($_SESSION["mb_user_id"]!=$GUESTID) {

	$sql="SELECT * FROM search WHERE uid='".$db->v($_SESSION["mb_user_id"])."' AND lastsearch=1 ORDER BY datetime desc";
	$db->query($sql);
	if($db->num_rows()) {

		print '
		<form id="savedsearch" action="'.$L["SuchURL"].'" method="post">
		<fieldset class="hidden">
		<input name="act" type="hidden" value="search" />
		<input name="selectsearch" type="hidden" value="1" />
		</fieldset>
		<fieldset class="data">
		<select name="searchid" onchange="document.getElementById(\'savedsearch\').submit();">';

		$db->next_record();
		if($_REQUEST["searchid"]!="" && $_REQUEST["searchid"]==$db->f("id") && $_REQUEST["selectsearch"]=="1") {
			print "<option value=\"".$db->f("id")."\" selected=\"selected\">".$db->f("name")."</option>\n";
		} else {
			print "<option value=\"".$db->f("id")."\">".$L["letzte Suche"]."</option>\n";
		}

		$sql="SELECT * FROM search WHERE uid='".$db->v($_SESSION["mb_user_id"])."' AND lastsearch=0 ORDER BY name";
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
		print '
		</select>
		</fieldset>
		<fieldset class="control">
		<input type="submit" value="'.$L["Aufrufen"].'" />
		</fieldset>
		</form>';
	} else {
		print "<p>Sie haben noch keine Suche gestartet.</p>";
	}
}
?>