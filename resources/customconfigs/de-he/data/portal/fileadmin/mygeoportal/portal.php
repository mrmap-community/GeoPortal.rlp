<?php

$page=parse_url($_SERVER["REQUEST_URI"]);
$url=$page['path'];
//echo $url;
//die();

include(dirname(__FILE__)."/../scripts/language.php");
include(dirname(__FILE__)."/../scripts/chkform.php");
include_once(dirname(__FILE__)."/../function/util.php");
include_once(dirname(__FILE__)."/../function/function.php");

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

$Message["EMail"] = $L["ErrEMail"];
$Message["Textsize"] = $L["ErrTextsize"];
$Message["Glossar"] = $L["ErrGlossar"];
$Message["mb_user_spatial_suggest"] = $L["ErrSpatial"];
$Message["mb_user_newsletter"] = $L["ErrNewsletter"];
$Message["mb_user_allow_survey"] = $L["ErrSurvey"];

$Default=array();

$Checkfields="EMail,true,email|Glossar,true,radio|mb_user_spatial_suggest,true,radio|mb_user_allow_survey,true,radio|mb_user_newsletter,true,radio";

function chkForm() {
	global $Message;
	if(chkFormular()) {
		if($_REQUEST["Passwort"]==$_REQUEST["Passwort2"]) return true;
		print '
			<div class="highlight">
				<p>
					<strong>'.$Message["ChkError"].'</strong><br />
					'.$Message["Passwort2"].'
				</p>
			</div>';
	}
	return false;
}

if ($_REQUEST["act"]!="save" || !chkForm()) {
	if($_REQUEST["act"]!="save") {
		foreach($Default as $key => $value) {
			// Daten einlesen
/*
			$sql="SELECT * FROM mygeoportal WHERE uid=".UserID();
			$db->query($sql);
			if($db->num_rows()) {
				$db->next_record();
				$textsize[$db->f("textsize")]="checked=\"checked\"";
				$glossar[$db->f("glossar")]="checked=\"checked\"";
			}
*/
			$DATA[$key]=$value;
		}
		$DATA["EMail"]=$_SESSION["mb_user_email"];
		$DATA["Beschreibung"]=$_SESSION["mb_user_description"];
		$DATA["Glossar"]=$_SESSION["Glossar"];
		$DATA["mb_user_spatial_suggest"]=$_SESSION["mb_user_spatial_suggest"];
		$DATA["Textsize"]=$_SESSION["Textsize"];
		$DATA["mb_user_newsletter"]=$_SESSION["mb_user_newsletter"];
		$DATA["mb_user_allow_survey"]=$_SESSION["mb_user_allow_survey"];
	} else {
		foreach($_REQUEST as $key => $value) {
			$DATA[$key]=$value;
		}
	}		
	$_REQUEST["Benutzername"]=$DATA["Benutzername"]=$_SESSION["mb_user_name"];

	$CHK_Textsize[$DATA["Textsize"]]='checked="checked"';
	$CHK_Glossar[$DATA["Glossar"]]='checked="checked"';
	$CHK_mb_user_spatial_suggest[$DATA["mb_user_spatial_suggest"]]='checked="checked"';
	$CHK_mb_user_newsletter[$DATA["mb_user_newsletter"]]='checked="unchecked"';
	$CHK_mb_user_allow_survey[$DATA["mb_user_allow_survey"]]='checked="unchecked"';

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
	function chkForm(el) {
		if(chkFormular(el)) {
			if(el.Passwort.value==el.Passwort2.value) return true;
			el.Passwort2.focus();
		}
		return false;
	}
</script>

<form class="mygeoportal" action="https://<?php print $_SERVER["SERVER_NAME"] . $url;?>" method="post" onsubmit="return chkForm(this);">

<fieldset>

<h2><?php print $L["Logindaten"]; ?></h2>

<label class="mygeoportal_label" for="benutzername"><?php print $L["Benutzername"]; ?></label>
<span class="mygeoportal_username"><?php print $DATA["Benutzername"]; ?></span>
<br class="clr" />

<label class="mygeoportal_label" for="passwort"><?php print $L["Passwort"]; ?></label>
<input class="text" id="passwort" type="password" name="Passwort" maxlength="40" />
<br class="clr" />

<label class="mygeoportal_label" for="passwortwiederholung"><?php print $L["Passwort2"]; ?></label>
<input class="text" id="passwortwiederholung" type="password" name="Passwort2" maxlength="40" />
<br class="clr" />

<label class="mygeoportal_label" for="email"><?php print $L["EMail"]; ?>*</label>
<input class="text" id="email" type="text" name="EMail" maxlength="40" value="<?php print $DATA["EMail"]; ?>" onfocus="clearField(this)" />
<br class="clr" />

<label class="mygeoportal_label" for="beschreibung"><?php print $L["Kommentar"]; ?></label>
<textarea class="mygeoportal_logindescription" id="beschreibung" cols="5" rows="5" name="Beschreibung" onfocus="clearField(this)"><?php print $DATA["Beschreibung"]; ?></textarea>
<br class="clr" />

</fieldset>

<!--
<h2><span><?php print $L["Darstellung"]; ?></span></h2>

<fieldset class="textsize">
<label for="textsize1"><?php print $L["NormaleDarstellung"]; ?><br /><img src="fileadmin/design/images/textsize1.gif" width="140" height="90" alt="<?php print $L["NormaleDarstellung"]; ?>" title="<?php print $L["NormaleDarstellung"]; ?>" /></label>
<input id="textsize1" type="radio" name="Textsize" value="textsize1" <?php print $CHK_Textsize["textsize1"];?> />
<div class="clearer"></div>
</fieldset>

<fieldset class="textsize">
<label for="textsize2"><?php print $L["GrosseDarstellung"]; ?><br /><img src="fileadmin/design/images/textsize2.gif" width="140" height="90" alt="<?php print $L["GrosseDarstellung"]; ?>" title="<?php print $L["GrosseDarstellung"]; ?>" /></label>
<input id="textsize2" type="radio" name="Textsize" value="textsize2" <?php print $CHK_Textsize["textsize2"];?> />
<div class="clearer"></div>
</fieldset>

<fieldset class="textsize">
<label for="textsize3"><?php print $L["SehrGrosseDarstellung"]; ?><br /><img src="fileadmin/design/images/textsize3.gif" width="140" height="90" alt="<?php print $L["SehrGrosseDarstellung"]; ?>" title="<?php print $L["SehrGrosseDarstellung"]; ?>" /></label>
<input id="textsize3" type="radio" name="Textsize" value="textsize3" <?php print $CHK_Textsize["textsize3"];?> />
<div class="clearer"></div>
</fieldset>
-->
<h2>Weitere Einstellungen</h2><div class="mygeoportal_moreoptions">
<span class="mygeoportal_toggledescription"><?php print $L["GlossarAnzeigen"]; ?></span>

<fieldset class="radio">
<label><input type="radio" name="Glossar" id="ja" value="ja" <?php print $CHK_Glossar["ja"];?> /><?php print $L["Ja"]; ?></label>
<label><input type="radio" name="Glossar" id="nein" value="nein" <?php print $CHK_Glossar["nein"];?> /><?php print $L["Nein"]; ?></label>
<br class="clr" />
</fieldset>

<span class="mygeoportal_toggledescription"><?php print $L["SpatialActivate"]; ?></span>

<fieldset class="radio">
<label><input type="radio" name="mb_user_spatial_suggest" id="ja" value="ja" <?php print $CHK_mb_user_spatial_suggest["ja"];?> /><?php print $L["Ja"]; ?></label>
<label><input type="radio" name="mb_user_spatial_suggest" id="nein" value="nein" <?php print $CHK_mb_user_spatial_suggest["nein"];?> /><?php print $L["Nein"]; ?></label>
<br class="clr" />
</fieldset>

<span class="mygeoportal_toggledescription"><?php print $L["UserNewsletter"]; ?></span>

<fieldset class="radio">
<label><input type="radio" name="mb_user_newsletter" id="ja" value="ja" <?php print $CHK_mb_user_newsletter["ja"];?> /><?php print $L["Ja"]; ?></label>
<label><input type="radio" name="mb_user_newsletter" id="nein" value="nein" <?php print $CHK_mb_user_newsletter["nein"];?> /><?php print $L["Nein"]; ?></label>
<br class="clr" />
</fieldset>

<span class="mygeoportal_toggledescription"><?php print $L["UserSurvey"]; ?></span>

<fieldset class="radio">
<label><input type="radio" name="mb_user_allow_survey" id="ja" value="ja" <?php print $CHK_mb_user_allow_survey["ja"];?> /><?php print $L["Ja"]; ?></label>
<label><input type="radio" name="mb_user_allow_survey" id="nein" value="nein" <?php print $CHK_mb_user_allow_survey["nein"];?> /><?php print $L["Nein"]; ?></label>
<br class="clr" />
</fieldset></div>

<fieldset class="control">
<input type="hidden" name="act" value="save" />
<input type="hidden" id="CHECK" name="CHECK" value="<?php print $Checkfields; ?>" />
<input type="submit" value="<?php print $L["Speichern"]; ?>" />
</fieldset>

</form>

<?php

} else {
	
/*
	$db=new DB_MYSQL;
	
	if($_REQUEST["act"]=="save") {
		$sql="DELETE FROM mygeoportal where uid=".UserID();
		$db->query($sql);
	
		$sql="INSERT INTO mygeoportal (uid, textsize, glossar, datetime)
		           VALUES (".UserID().", '".$_REQUEST["textsize"]."', '".$_REQUEST["glossar"]."' , ".time().")";
		$db->query($sql);
	}
*/
	$mb_user_name=$_REQUEST["Benutzername"];
	$mb_user_password=$_REQUEST["Passwort"];
	$mb_user_description=$_REQUEST["Beschreibung"];
	$mb_user_email=$_REQUEST["EMail"];
	$Textsize=$_REQUEST["Textsize"];
	$Glossar=$_REQUEST["Glossar"];
	$mb_user_spatial_suggest=$_REQUEST["mb_user_spatial_suggest"];
	$mb_user_newsletter=$_REQUEST["mb_user_newsletter"];
	$mb_user_allow_survey=$_REQUEST["mb_user_allow_survey"];
	//Daten speichern
	include(dirname(__FILE__)."/../../../mapbender/http/geoportal/updateUserDataIntoDb.php");

 header ("Location: http://".$_SERVER['HTTP_HOST']."/portal/anmelden/saved.html".$URLAdd);
/*	print "<p>".$L["ProfileUpdateOK"]."</p>";*/
}
?>
