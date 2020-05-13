<!-- Diese Datei ist für die rechte Seite der Suchanzeige gedacht. sie wird in das Joomla integriert und muss nach dem Ausführen des ant-Skripts noch händisch in den joomla-Ordnaer kopiert werden. -->

<div class="tx-q4usearch-pi2">
	<div id="incontentRight" style="float:right;position:relative;width:217px;padding-left:5px;margin-right:-22px">
		<div id="saveSearch">
			<h3 class="header-right"><?php echo JText::_('TPL_Q4U_MB_SAVE_SEARCH'); ?></h3>
			<div id="search_notlogged">
			    <span>Diese Funktionalität steht nur  angemeldeten Benutzern zur Verfügung.</span>
			</div>
			<div id="search_logged" style="display:none;">
				<form method="post" id="searchsave" action="">
					<fieldset>
						<label for="savename">Name:</label>
						<input type="text" value="" id="savename" name="name" class="text">
						<br class="clr" />
						<label for="savesearchtext">Suchbegriffe:</label>
						<input type="text" value="" id="savesearchtext" name="searchtext" class="text">
						<br class="clr" />
						<input type="button" value="Speichern" onclick="editSearch('save')">
					</fieldset>
				</form>
				<br class="clr" />
				<div class="search-saved">
					<h3 class="header-right">Gespeicherte Suchen verwalten</h3>
				</div>
			</div>
		</div>
		<div id="searchCategories" class="displayService" style="display:none;">
			<h3 id="headerCategory" class="header-right"><?php echo JText::_('TPL_Q4U_MB_CATEGORIES'); ?></h3>
			<div id="categoryContent"></div>
		</div>
		<div id="searchLegend" class="displayLegend" style="display:none;">
			<h3 class="header-right">Legende</h2>
				<dl>
				<dt><img src="/mapbender/img/search/icn_zoommap.png" alt=""></dt>
				<dd>Hinzuladen auf Ausdehnung des Darstellungsdienstes</dd>
				<dt><img src="/mapbender/img/search/icn_map.png" alt=""></dt>
				<dd>Hinzuladen auf letzte Kartenansicht</dd>
				<dt><img src="/mapbender/img/search/icn_georss_22.png" alt=""></dt>
				<dd>GeoRSS hinzuladen</dd>
				<dt><img src="/mapbender/img/search/icn_encrypted.png" alt=""></dt>
				<dd>Zugangsbeschränkung</dd>
				<dt><img src="/mapbender/img/search/icn_encrypted_mail.png" alt=""></dt>
				<dd>Zugangsbeschränkung (Antrag per Mail)</dd>
				<dt><img src="/mapbender/img/search/wappen_DE-SL.png" alt=""></dt>
				<dd>Landeswappen</dd>
				<dt><img src="/mapbender/img/search/icn_warn.png" alt=""></dt>
				<dd>Nutzungsbedingungen</dd>
				<dt><img src="/mapbender/img/search/icn_eingeschraenketes_netz.png" alt=""></dt>
				<dd>nur über Intranet abrufbar</dd>
				<dt><img src="/mapbender/img/search/icn_euro.png" alt=""></dt>
				<dd>Gebührenpflichtig</dd>
				<dt><img src="/mapbender/img/search/icn_logging.png" alt=""></dt>
				<dd>Dienst/Klick wird protokolliert</dd>
				<dt><img src="/mapbender/img/search/icn_go.png" alt=""></dt>
				<dd>grün: steht zur Verfügung<br>gelb: Metadatenänderung<br>rot: z.Z. keine Verfügung</dd>
				<dt><div style="background:url(/mapbender/img/search/icn_empty.png);width:24px;height:24px;text-align:center;line-height:24px">%</div></dt>
				<dd>durchschn. Verfügbarkeit</dd>
				<dt><img src="/mapbender/img/search/icn_info.png" alt=""></dt>
				<dd>abfragbare Ebenen</dd>
				<dt><img src="/mapbender/img/search/icn_epsg.png" alt=""></dt>
				<dd>CRS nicht unterstützt</dd>
				<dt><img src="/mapbender/img/search/icn_download.png" alt=""></dt>
				<dd>Downloaddienst hinzuladen</dd>
				<dt><img src="/mapbender/img/search/icn_suche.png" alt=""></dt>
				<dd>Suchdienst hinzuladen</dd>
			</dl>
			<div class="clr"></div>
		</div>
	</div>
	<div id="incontentLeft" style="float:right; position:relative;">
		<div class="search-filter">
			<div id="headerAll"></div>
			<div id="headerDienst" class="displayService" style="display:none;"></div>
		</div>
		<div id="tabs">
			<ul class="search-cat">
				<li><a href="#tabs-1" onclick="clearHeader(); clearLegend()"><?php echo JText::_('TPL_Q4U_MB_OVERVIEW'); ?></a></li>
				 <li><a href="#tabs-2" onclick="clearHeader(); clearLegend()">Adressen</a><img class="loader" id="statusAdressTab" src="/mapbender/img/search/loader_lightblue.gif" height="15 px" align="center"></li>
				<li><a href="#tabs-3" onclick="showHeader(); showLegend()"><?php echo JText::_('TPL_Q4U_MB_INTERACTIVE_DATA'); ?></a><img class="loader" id="statusServiceTab" src="/mapbender/img/search/loader_lightblue.gif" height="15 px" align="center"></li>
				<li><a href="#tabs-4" onclick="clearHeader(); showLegend()"><?php echo JText::_('TPL_Q4U_MB_METADATA'); ?></a><img class="loader" id="statusMetaTab" src="/mapbender/img/search/loader_lightblue.gif" height="15 px" align="center"></li>
			</ul>
			<div id="tabs-1" class="search-container">
				<a onclick="showTab('#tabs-2',1)" title="Alle Ergebnisse aus dem Cointainer Adressen anzeigen"><h2>Adressen</h2></a>
				<div id="search-container-adress">
					<img class="loader" id="statusAdressBod" src="/mapbender/img/search/loader_lightblue.gif" height="15 px" align="center">
				</div>
				<a onclick="showTab('#tabs-3',2)" title="Alle Ergebnisse aus dem Container interaktive Daten anzeigen" ><h2><?php echo JText::_('TPL_Q4U_MB_INTERACTIVE_DATA'); ?></h2></a>
				<div id="search-container-dienste">
					<img class="loader" id="statusServiceBod" src="/mapbender/img/search/loader_lightblue.gif" height="15 px" align="center">
				</div>
				<a onclick="showTab('#tabs-4',3)"  title="Alle Ergebnisse aus dem Container Metadaten anzeigen"><h2><?php echo JText::_('TPL_Q4U_MB_METADATA'); ?></h2></a>
				<div id="search-container-meta">
					<img class="loader" id="statusMetaBod" src="/mapbender/img/search/loader_lightblue.gif" height="15 px" align="center">
				</div>
			</div>
			<div id="tabs-2">
			</div>
			<div id="tabs-3">
			</div>
			<div id="tabs-4">
			</div>

		</div>
	</div>
</div>
