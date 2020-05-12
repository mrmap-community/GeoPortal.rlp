<?php
include_once(dirname(__FILE__)."/../../../../fileadmin/function/util.php");
include_once(dirname(__FILE__)."/../../../../fileadmin/function/config.php");
include_once(dirname(__FILE__)."/../../../../fileadmin/function/function.php");
include_once("search_functions.php");

global $GUESTID, $Language;
global $LinkURL;
$page=parse_url($_SERVER['REQUEST_URI']);
$LinkURL=$page['path'];

if(t3lib_div::GPvar('L')==1) $L=$Language["en"];
else $L=$Language["de"];

$db=new DB_MYSQL;
$searchtext=SearchText();
?>

<h2><!-- <a href="<?php print $L["HilfeSucheURL"]; ?>"><img src="fileadmin/design/icn_help.png" width="16" height="16" alt="Hilfe" title="<?php print $L["HilfezuSuchespeichern"];?>" /></a> --><?php print $L["Aktuelle Suche speichern"]; ?></h2>
<?php if($_SESSION["mb_user_id"]!=$GUESTID) { ?>
<form action="" id="searchsave" method="post">
<fieldset>
<label for="searchsavename"><?php print $L["Name"]; ?>:</label>
<input class="text" type="text" name="name" id="searchsavename" value="<?php print PtH($searchtext);?>" />
<br class="clr" />
<label for="searchsavesearchtext"><?php print $L["Suchbegriff"]; ?>:</label>
<input class="text" type="text" name="searchtext2" id="searchsavesearchtext" value="<?php print PtH($searchtext);?>" />
<br class="clr" />
</fieldset>
<fieldset class="control">
<input type="hidden" name="act" value="save" />
<input type="hidden" name="searchid" value="<?php print PtH($_REQUEST["searchid"]);?>" />
<input type="hidden" name="selectsearch" value="<?php print PtH($_REQUEST["selectsearch"]);?>" />
<input type="hidden" name="cat" value="<?php print PtH($_REQUEST["cat"]);?>" />
<input type="hidden" name="uid" value="<?php print PtH($_REQUEST["uid"]);?>" />
<input type="submit" value="<?php print $L["Speichern"]; ?>" />
</fieldset>
</form>
<?php } else { ?>
<p><?php print $L["erst nach Login"];?></p>
<?php } ?>

<?php
if($_SESSION["mb_user_id"]!=$GUESTID) {
?>
<div class="search-saved">
<h2><!-- <a href="<?php print $L["HilfeSucheURL"]; ?>"><img src="fileadmin/design/icn_help.png" width="16" height="16" alt="Hilfe" title="<?php print $L["HilfezugespeicherteSucheverwalten"];?>" /></a> --><?php print $L["Gespeicherte Suchen verwalten"];?></h2>
<?php

print '
	<table>';

$sql="SELECT * FROM search WHERE uid='".$db->v($_SESSION["mb_user_id"])."' AND lastsearch=1 ORDER BY datetime desc";
$db->query($sql);
if($db->num_rows()) {
  $db->next_record();
	print '
		<tr>
			<td colspan="3" style="border:0"><a href="'.SearchURL($db->f('id')).'">'.$L['letzte Suche'].'</a></td>
		</tr>';
}

$sql="SELECT * FROM search WHERE uid='".$db->v($_SESSION["mb_user_id"])."' AND lastsearch=0 ORDER BY name";
$db->query($sql);
if($db->num_rows()) {
  while($db->next_record()) {
  	$url=SearchURL();

  	$confirm=$L["Loeschenanfragelang"];
  	$confirm=str_replace("###NAME###",$db->f("name"),$confirm);
  	$confirm=str_replace("\"","",$confirm);

  	print '
  	<tr>
  		<td style="width:200px"><a href="'.SearchURL($db->f('id')).'">'.$db->f('name').'</a></td>
 			<td style="width:16px;padding:2px 2px 0 0"><a href="'.$url.'&amp;act=edit&amp;editid='.$db->f('id').'"><img src="fileadmin/design/icn_edit.png" width="22" height="22" alt="'.$db->f('name').' bearbeiten" /></a></td>
 			<td style="width:16px;padding:2px 2px 0 0"><a onclick="if(confirm(\''.$confirm.'\')){this.href+=\'&ok=1\'; return true;}else{return false;};" href="'.$url.'&amp;act=delete&amp;deleteid='.$db->f('id').'"><img src="fileadmin/design/icn_delete.png" width="22" height="22" alt="Suche löschen" /></td></a>
  	</tr>';
  	if($_REQUEST["act"]=="delete" && $_REQUEST["deleteid"]==$db->f("id")) {
  		$confirm=$L["Loeschenanfragekurz"];
  		$confirm=str_replace("###NAME###",$db->f("name"),$confirm);

  		print '
  	<tr>
  		<td colspan="3" class="query">
  			<p>'.$confirm.'</p>
  			<p class="center">
  				<a href="'.$url.'&amp;act=delete&amp;ok=1&amp;deleteid='.$db->f('id').'">'.$L['Ja'].'</a>
  				<a href="'.$url.'">'.$L['Nein'].'</a>
  			</p>
  		</td>
  	</tr>';
  	}
  	if($_REQUEST["act"]=="edit" && $_REQUEST["editid"]==$db->f("id")) {
?>
<tr>
	<td colspan="3">';
<form action="" id="searchedit" method="post">
<fieldset>
<label for="searcheditname">Name:</label>
<input class="text" type="text" name="name" id="searcheditname" value="<?php print PtH($db->f("name"));?>" />
<br class="clr" />
<label for="searcheditsearchtext">Suchbegriffe:</label>
<input class="text" type="text" name="searchtext" id="searcheditsearchtext" value="<?php print PtH($db->f("searchtext"));?>" />
<br class="clr" />
</fieldset>
<fieldset class="control">
<input type="hidden" name="act" value="change" />
<input type="hidden" name="searchid" value="<?php print PtH($_REQUEST["searchid"]);?>" />
<input type="hidden" name="editid" value="<?php print $db->f("id");?>" />
<input type="hidden" name="selectsearch" value="<?php print PtH($_REQUEST["selectsearch"]);?>" />
<input type="hidden" name="cat" value="<?php print PtH($_REQUEST["cat"]);?>" />
<input type="hidden" name="uid" value="<?php print PtH($_REQUEST["uid"]);?>" />
<input type="submit" value="Speichern" />
</fieldset>
</form>
	</td>
</tr>
<?php
  	}
  }
}
print '
	</table>';
?>
</div>

<?php
}
?>

<div class="search-filter">
	<div id="search-filter-adr">
<?php
print getAdrCat();
?>
	</div>
	<div id="search-filter-srv">
<?php
print getServiceCat();
?>
	</div>
</div>
<link rel="stylesheet" type="text/css" href="../../../fileadmin/design/newcat.css">
<script type="text/javascript">
$(document).ready(function(){
$('ul.search-srvcat-ul').each(function(){

  var LiN = $(this).find('li').length;

  if( LiN > 5){    
    $('li', this).eq(4).nextAll().hide().addClass('toggleable');
    $(this).append('<li class="search-srvcat-more">+ mehr</li>');    
  }

});


$('ul.search-srvcat-ul').on('click','.search-srvcat-more', function(){

  if( $(this).hasClass('less') ){    
    $(this).text('+ mehr').removeClass('less');    
  }else{
    $(this).text('- weniger').addClass('less'); 
  }

  $(this).siblings('li.toggleable').slideToggle();

}); 
});

</script>
