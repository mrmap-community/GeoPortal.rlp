<div id="startcontent" class="content">
<form method="post">
<h2><span>Search geo information</span></h2>
<fieldset class="hidden">
<input type="hidden" name="do" value="list" />
</fieldset>

<fieldset>
<label class="text" for="suchbegriff">Search term</label>
<input class="text" id="suchbegriff" type="text" name="searchstring" value="<?php print $_REQUEST["suchbegriff"]; ?>" onfocus="if(this.value == '<?php print $L["Suchbegriff"];?>') { this.value = '' }" />
<div class="clearer"></div>
</fieldset>

<fieldset class="control">
<input type="submit" value="Suchen" />
</fieldset>
</form>
</div>