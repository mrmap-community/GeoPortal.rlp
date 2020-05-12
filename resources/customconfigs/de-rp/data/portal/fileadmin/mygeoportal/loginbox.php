<?php
include_once(dirname(__FILE__)."/../function/config.php");

if(t3lib_div::GPvar('L')==1) {
	$L=$Language["en"];
	$LangParam="en";
} else {
	$L=$Language["de"];
	$LangParam="";
}



if($_SESSION["mb_user_id"]=="" || $_SESSION["mb_user_id"]==$GUESTID) {
?>
	<form action="http://<?php print $_SERVER["HTTP_HOST"];?>/mapbender/geoportal/authentication.php" method="post">
	<fieldset>
	<?php if($LangParam!="") { print '<input name="L" value="'.$LangParam.'" type="hidden" />'; } ?>
	<label for="user"><?php print $L["Benutzername"]; ?>:</label>
	<input id="user" class="text" name="name" type="text" />
	<br class="clr" />
	<label for="password"><?php print $L["Passwort"]; ?>:</label>
	<input id="password" class="text" name="password" type="password" />
	<br class="clr" />
	</fieldset>
	<fieldset class="control">
	<input type="submit" value="<?php print $L["Anmelden"]; ?>" />
	</fieldset>
	</form>
<?php	
} else {
?>
	<p>
	<?php 
	print str_replace("###USER###",$_SESSION["mb_user_name"],$L["LoginString"]);
	?>
	</p>

<?php
}
?>
