<?php
include_once(dirname(__FILE__)."/../function/config.php");

if(t3lib_div::GPvar('L')==1) {
	$L=$Language["en"];
	$LangParam="en";
} else {
	$L=$Language["de"];
	$LangParam="";
}
print "<script></script>";//integrate jquery from mapbender to have the possibility to use ajax
print "<script type=\"text/javascript\">function deleteProfile(){";
print "			deleteDialogDiv = $(document.createElement('div')).appendTo($(\"#dialogContainer\"));";
print "			deleteDialogDiv.attr({'id':'deleteDialogDiv'});";
print "			deleteDialogDivText = $(document.createTextNode('"._mb("Are you sure to delete all of your data? This action cannot be reverted afterwards. All your map combinations and owned data in the geoportal will be deleted irretrievable!")."')).appendTo($(\"#deleteDialogDiv\"));";
print "	  		$(function() {\n";
print "	   			$( \"#deleteDialogDiv\" ).dialog({\n";
print "						    modal: true,\n";
print "						    resizable: false,\n";
print "                         draggable: false,\n";
print "     					buttons: {\n";
print "       						\""._mb("Yes - delete my profile")."\": function() {\n";
print "								$.ajax({\n";
print " 								url: '../../../mapbender/geoportal/deleteUserProfile.php',\n";
print " 								type: \"post\",\n";
print "									async: true, \n";
print "									data: {url: \"test\", method: \"deleteUserProfile\" , id: \"id\"},\n";
print "      								dataType: \"json\",\n";
print "								}).done(function( result ) {\n";
print "									//alert(JSON.stringify(result.result.error));\n";
print "									if (result.error !== null) {\n";
print "										alert(result.error.message);\n";
print "									} else {\n";
print "										alert(result.result.message);\n";
print "										window.location.href = \"../../../mapbender/geoportal/mod_logout.php\";\n";
print "									}\n";
print "								});\n";
print "       							},\n";
print "		       					\""._mb("Abort")."\": function() {\n";
print "         						$( this ).dialog( \"close\" );\n";
print "       							}\n";
print "     					}\n";
print "				});\n";	
print "	 		});\n";
print "		};";
print "</script>";
print "<span style=\"font-size:150%; color:#871e32; font-weight:bold;\">".$L["MyGeoportal"]."</span><br>";
if($_SESSION["mb_user_id"]=="" || $_SESSION["mb_user_id"]==2) {
	print $L["ToLoginString"];
	?>
        <ul>
        <li><a href="<?php print $L["AnmeldenURL"]; ?>"><?php print "<b><i>".$L["Anmelden"]."</i></b>"; ?></a></li>
	<li><a href="<?php print $L["RegistrierenURL"]; ?>"><?php print $L["Registrieren"]; ?></a></li>
	<li><a href="<?php print $L["PasswortVergessenURL"]; ?>"><?php print $L["PasswortVergessen"]; ?></a></li>
	<ul>
	<?php
} else {
?>
        <p>
        <?php
        print str_replace("###USER###","<span style=\"font-size:150%\">".$_SESSION["mb_user_name"]."</span>",$L["LoginString"]);
	#print str_replace("###USER###","nix",$L["LoginString"]);
        ?>
        </p>
        <ul>
	<li><a onclick="deleteProfile();"><?php print "<b><i>".$L["DeleteProfile"]."</i></b>"; ?></a></li>
	<li><div id="dialogContainer"></div></li>
        <li><a href="<?php print $L["ProfilURL"]; ?>"><?php print $L["ProfilBearbeiten"]; ?></a></li>
	<?php
	if ($LangParam =='') {
        	echo "<li><a href=\"/portal/servicebereich/abos-anzeigen.html\">".$L["AbosAnzeigen"]."</a></li> ";
	} else {
		echo "<li><a href=\"/portal/".$LangParam."/service/subscriptions.html\">".$L["AbosAnzeigen"]."</a></li> ";
	}
	?>
	<li><a href="/mapbender/geoportal/mod_logout.php<?php if($LangParam!="") print "?L=".$LangParam; ?>"><?php print "<b><i>".$L["Abmelden"]."</i></b>"; ?></a></li>
        </ul>
<?php
}
?>
