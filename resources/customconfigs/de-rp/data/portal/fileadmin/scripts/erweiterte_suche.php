<form class="extsearch" action="<?php print $URL; ?>" method="post">

<h2><span>Was? (Thema)</span></h2>

<fieldset>
<label class="text" for="suchbegriff"><strong title="Pflichtfeld">Suchbegriff</strong></label>
<input class="text" id="suchbegriff" type="text" name="Name" maxlength="40" value="<?php print $_REQUEST["Name"]; ?>" onfocus="clearField(this)" />
<div class="clearer"></div>
</fieldset>

<h2><span>Auswahl Fachkategorien</span></h2>

<fieldset class="checkbox">
<label for="natur">Natur und Umwelt</label>
<input id="natur" type="checkbox" name="fachkategorien" checked="checked" />
<label for="basisdaten">Basisdaten</label>
<input id="basisdaten" type="checkbox" name="fachkategorien" checked="checked" />
<label for="luft">Luft</label>
<input id="luft" type="checkbox" name="fachkategorien" checked="checked" />
<label for="wasser">Wasser</label>
<input id="wasser" type="checkbox" name="fachkategorien" checked="checked" />
<label for="statistik">Statistik</label>
<input id="statistik" type="checkbox" name="fachkategorien" />
<label for="infrastruktur">Infrastruktur</label>
<input id="infrastruktur" type="checkbox" name="fachkategorien" />
<label for="geologie">Geologie</label>
<input id="geologie" type="checkbox" name="fachkategorien" />
<label for="landwirtschaft">Landwirtschaft</label>
<input id="landwirtschaft" type="checkbox" name="fachkategorien" />
<div class="clearer"></div>
</fieldset>

<h2><span>Wann? (Zeit)</span></h2>

<fieldset class="select">
<p>Von</p>
<label class="select" for="vonmonat"><strong title="Pflichtfeld">Monat</strong></label>
<select id="vonmonat">
<option>MM</option>
<option>01</option>
</select>
<label class="select" for="vonjahr"><strong title="Pflichtfeld">Jahr</strong></label>
<select id="vonjahr">
<option>JJJJ</option>
<option>2000</option>
</select>
</fieldset>
<fieldset class="select">
<p>bis</p>
<label class="select" for="bismonat"><strong title="Pflichtfeld">Monat</strong></label>
<select id="bismonat">
<option>MM</option>
<option>01</option>
</select>
<label class="select" for="bisjahr"><strong title="Pflichtfeld">Jahr</strong></label>
<select id="bisjahr">
<option>JJJJ</option>
<option>2000</option>
</select>
</fieldset>

<h2><span>Wo? (Ort)</span></h2>

<fieldset>
<label class="text" for="ort"><strong title="Pflichtfeld">Ort</strong></label>
<input class="text" id="ort" type="text" name="Name" maxlength="40" value="<?php print $_REQUEST["Name"]; ?>" onfocus="clearField(this)" />
<div class="clearer"></div>
</fieldset>

<fieldset class="center">
<p>Bitte nur einen geographischen Begriff eingeben. <input class="button" type="button" value="Geothesaurus" /></p>
</fieldset>

<fieldset>
<div id="karte">
</div>

<p>Bei der Suche wird sowohl das Textfeld als auch die Kartenauswahl berücksichtigt.</p>

<div id="kartetoolbox">
</div>

<p><strong>Koordinaten nach WGS 84</strong></p>
<table>
<tr>
	<th>Norden:</th><td>50.99°</td>
</tr>
<tr>
	<th>Süden:</th><td>48.69°</td>
</tr>
<tr>
	<th>Osten:</th><td>8.72°</td>
</tr>
<tr>
	<th>Westen:</th><td>5.84°</td>
</tr>
</table>
<label class="checkbox" for="koordinatensuche">Koordinatensuche deaktivieren</label>
<input class="checkbox" id="koordinatensuche" type="checkbox" />
<div class="clearer"></div>
</fieldset>

<fieldset class="control">
<input type="submit" value="Suche starten" />
</fieldset>

</form>
