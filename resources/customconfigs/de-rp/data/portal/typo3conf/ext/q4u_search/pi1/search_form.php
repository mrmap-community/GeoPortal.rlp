<?php
include_once("fileadmin/function/util.php");
include_once("fileadmin/function/function.php");
include_once("search_functions.php");

$searchtext=SearchText();
$sql="SELECT * FROM search WHERE uid=1 ORDER BY name";
$db->query($sql);
?>

<div class="box" id="suche_speichern">
<h2><span>Aktuelle Suche speichern</span></h2>
<form action="" id="searchtoolbox" method="post">
<fieldset class="hidden">
<input type="hidden" name="act" value="save" />
<input type="hidden" name="searchid" value="<?php print PtH($_REQUEST["searchid"]);?>" />
<input type="hidden" name="cat" value="<?php print PtH($_REQUEST["cat"]);?>" />
<input type="hidden" name="uid" value="<?php print PtH($_REQUEST["uid"]);?>" />
</fieldset>
<fieldset class="data">
<label for="name">Name</label>
<input type="text" name="name" id="name" value="<?php print $searchtext;?>" />
</fieldset>
<fieldset class="control">
<input type="submit" value="Speichern" />
</fieldset>
</form>
</div>

<div class="box" id="suchen_verwalten">
<h2><span>Gespeicherte Suchen verwalten</span></h2>
<?php
$url=SearchURL(true);

if($db->num_rows()) {
	print '
		<ul>';
  while($db->next_record()) {
  	print '
  		<li>
  			<a class="icon" href=""><img src="fileadmin/design/images/icon_preferences.gif" width="16" height="16" alt="'.$db->f('name').' bearbeiten" /></a>
  			<a class="icon" href="'.$url.'&amp;act=delete&amp;deleteid='.$db->f('id').'"><img src="fileadmin/design/images/icon_delete.gif" width="16" height="16" alt="Suche löschen" /></a>
  			<a href="'.$url.'?act=search&amp;searchid='.$db->f('id').'">'.$db->f('name').'</a>
  		</li>';
  }
	print '
		</ul>';
}
?>

<div class="clearer"></div>
</div>
