<?php
include_once(dirname(__FILE__)."/../function/config.php");

if(t3lib_div::GPvar('L')==1) {
	$L=$Language["en"];
	$LangParam="en";
} else {
	$L=$Language["de"];
	$LangParam="";
}
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
print "       						\""._mb("Ja - Profil löschen!")."\": function() {\n";
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

print "<script type=\"text/javascript\"> ";
print "$(document).ready(function(){ ";
print "$(\"#profileOptions\").hide(); ";
print "}); ";
print "$(document).ready(function(){ ";
print "$('.UserBtn').click(function(){ ";
print "$('#profileOptions').toggle('fast'); ";
print "})}); ";
print " ";
print " ";
print " ";
print "</script> ";
?>

<?php
if($_SESSION["mb_user_id"]=="" || $_SESSION["mb_user_id"]==2) {
	print "<span>".$L["ToLoginString"]."</span>";
	?>
        <a class="AnmBtn" tabindex="4" href="<?php print $L["AnmeldenURL"]; ?>"><?php print "".$L["Anmelden"].""; ?></a>
	<?php
} else {
?>
	<a class="UserBtn" >
        <?php
        print str_replace("###USER###",$_SESSION["mb_user_name"],$L["LoginString"]);
	#print str_replace("###USER###","nix",$L["LoginString"]);
        ?>
        </a>
      
	<a class="AbmBtn" tabindex="4" href="/mapbender/geoportal/mod_logout.php<?php if($LangParam!="") print "?L=".$LangParam; ?>"><?php print $L["Abmelden"]; ?></a>
        <div id="profileOptions"><div class="arrow-up"></div><p><a class="changeprofile" href="<?php print $L["ProfilURL"]; ?>">Profil bearbeiten</a></p><p><a class="profiledelete" style="cursor:pointer" onclick="deleteProfile();" >Profil löschen</a></p>
	<?php 
	if ($LangParam =='') {
        	echo "<p><a class=\"subscriptions\" href=\"/portal/anmelden/abosanzeigen.html\">".$L["AbosAnzeigen"]."</a></p> ";
	} else {
		echo "<p><a class=\"subscriptions\" href=\"/portal/".$LangParam."/service/subscriptions.html\">".$L["AbosAnzeigen"]."</a></p> ";
	}
	?>
	</div><div id="dialogContainer"></div>
<?php
}
?>
