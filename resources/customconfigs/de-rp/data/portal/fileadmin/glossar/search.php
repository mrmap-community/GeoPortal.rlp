<?php
$URL="http://".$_SERVER["HTTP_HOST"].$_SERVER['REDIRECT_URL'];
?>

<div class="search-lexikon">
<img src="fileadmin/user_upload/Bilder/lexikon-suche.jpg" alt="" />

<form action="<?php print $URL;?>" id="search" method="post">
<fieldset class="hidden">
<input type="hidden" name="action" value="search" />
</fieldset>

<fieldset class="data">
<input type="text" name="searchtext" id="searchtext" value="<?PHP print $_REQUEST["searchtext"];?>" />
</fieldset>

<fieldset class="control">
<input type="submit" value="suchen" />
</fieldset>

</form>
</div>