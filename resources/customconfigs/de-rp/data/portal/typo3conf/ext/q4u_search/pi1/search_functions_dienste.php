<?php
function Dienste($file, $max=0) {
	global $out, $name, $startpage, $L, $page, $items_page, $ServiceCat;
	global $unique;
	global $OrigURL;
	global $counter;
	global $searchResources;
	global $filterResources;
	global $Wappen;
	global $LinkURL;
	global $admin;
	global $mapbenderUrl;

	if (defined("MAPBENDER_PATH") && MAPBENDER_PATH != '') { 
		$mapbenderUrl = MAPBENDER_PATH;
	} else {
		$mapbenderUrl = "http://www.geoportal.rlp.de/mapbender";
	}

	$Wappen=array(
		'DE',
		'DE-RP',
		'DE-BE',
		'DE-BR',
		'DE-BW',
		'DE-BY',
		'DE-HB',
		'DE-HE',
		'DE-HH',
		'DE-MV',
		'DE-NI',
		'DE-NW',
		'DE-SH',
		'DE-SL',
		'DE-SN',
		'DE-ST',
		'DE-TH',
	);

	if(!$startpage) {
		$out[$name].='
		<form action="'.$L['KarteURL'].'" method="get" name="formmaps">
			<fieldset class="hidden">
				<input name="zoomToLayer" type="hidden" value="0" />
			</fieldset>
		';
	}

	$url=$LinkURL.'?searchid='.intval($_REQUEST['searchid']).'&amp;selectsearch='.intval($_REQUEST['selectsearch']).'&amp;uid='.PtH($_REQUEST['uid']);

	$catpages=explode('|',$page);
	//if page empty
	/*$e = new mb_exception("search_functions.php: count searchResources: ".count($searchResources));
	if (count($catpages) !== count($searchResources)) {
		for($i=0;$i<count($searchResources);$i++) {
			$catpages[$i]= 0;
		}
		$page = implode("|",$catpages);
	} else {*/
		for($i=0;$i<count($searchResources);$i++) {
			$catpages[$i]=intval($catpages[$i]);
		}
	/*}*/
	$ready=1;
	$allcounter=0;

	if(count($filterResources)==0)
		getSearchFilter(str_replace('###SERVICE###','_filter',$file));

	$numRessource=0;

	foreach($filterResources as $resource=>$resourceTitle) {

		$currentpage=$catpages[$numRessource];
		$numRessource++;
		$counter=0;

		$filename=str_replace('###SERVICE###','_'.$resource.'_'.($currentpage+1),$file);

		$tagcloudfilename=str_replace('###SERVICE###','_'.$resource.'_keywords',$file);
		//getFromStorage($filename, $cacheType)
		//$admin = new administration();
		$fileExists = $admin->getFromStorage($filename, TMP_SEARCH_RESULT_STORAGE);
		if ($fileExists == false) {
			parse_str($OrigURL,$URLParts);
			$URLParts['searchResources']=$resource;
			$URLParts['searchPages']=$currentpage+1;
			$URLParts['hostName']=$_SERVER['HTTP_HOST'];
			$SearchParams=http_build_query($URLParts);
			$connector = new connector();
			$connector->set("timeOut", "1");
			$connector->load('http://localhost/mapbender/php/mod_callMetadata.php?'.$SearchParams);
			//file_get_contents('http://localhost/mapbender/php/mod_callMetadata.php?'.$SearchParams);

			// Warten nach der ersten Suche
			$microsPerSecond = 1000000;
			usleep(($microsPerSecond/5));

		}
		if($fileExists !== false) {
			$DATA=json_decode($fileExists);
			if($DATA) {
				foreach($DATA as $class=>$ClassData) {
					$Info=$ClassData->md;
					$allcounter+=$Info->nresults;
					if(!$startpage) {

						$out[$name].='
							<div class="search-cat '.((isset($_REQUEST['pos']) && $_REQUEST['pos']==$numRessource-1)?'opened':'closed').'">
							<div class="search-header" onclick="openclose2(this);">
								<img class="icon" src="fileadmin/design/s_'.$class.'.png">
								<h2>'.$resourceTitle.'</h2>
								<p>('.$Info->nresults.' '.$L['Trefferkurz'].' in '.round($Info->genTime,2).' '.$L['Sekunden'].')</p>
								<div class="clr"></div>
							</div>';

						getServiceTagCloud($tagcloudfilename);

						$out[$name].=PageCounter($Info->nresults, $Info->rpp, $url, $name, count($searchResources), $numRessource-1);

						foreach($ClassData->srv as $Service) {
							//$out[$name].=print_r($Service, true);
							switch($class) {
								case "wms":
									getServiceWMS($Service,$Service);
									break;
								case "wfs":
									getServiceWFS($Service);
									break;
								case "wmc":
									getServiceWMC($Service);
									break;
								case "dataset":
									getServiceDataset($Service);
									break;
							}
						}

						if($class=="wms") $out[$name].='<fieldset class="search-dienste"><input type="submit" value="'.$L['In Karte aufnehmen'].'" /></fieldset><div class="clearer"></div>';
						$out[$name].=PageCounter($Info->nresults, $Info->rpp, $url, $name, count($searchResources), $numRessource-1);


						$out[$name].='
							</div>';
					}
				}

			} else {
				$ready=0;
			}
		} else {
			$ready=0;
		}
	}
	$ServiceCat=array();
	if($ready==1) {
		foreach($filterResources as $resource=>$resourceTitle) {
		$filename=str_replace('###SERVICE###','_'.$resource.'_cat',$file);
			$fileExists = $admin->getFromStorage($filename, TMP_SEARCH_RESULT_STORAGE);
			if ($fileExists !== false) {
			//if(file_exists($filename) && filesize($filename)>0) {
				$DATA=json_decode($fileExists);
				if($DATA) {
					$ServiceCat[]=$DATA;
				} else {
					$ready=0;
				}
			} else {
				$ready=0;
			}
		}
	}
	if($ready==0) $ServiceCat=array();

	if(!$startpage) {
		if($counter!=0) {
			$out[$name].='
				<fieldset class="search-dienste"><input type="submit" value="'.$L['In Karte aufnehmen'].'" /></fieldset>
				<br class="clr" />
			';
		}

		$out[$name].='
			</form>
		';
	} else {
		if($ready==1) {
			if($allcounter==0) {
				$out[$name].='<p>'.$L['kein Ergbnis'].'</p>';
			} else {
				$out[$name].='<p>'.$allcounter.' '.$L['Trefferkurz'].'</p>';
			}
		}
	}


	return $ready;

}


// WMS-Struktur parsen
function getServiceWMS($Service, $ParentLayer, $depth=0) {
	global $out, $name, $startpage, $L, $page, $Wappen;
	global $counter;


	if($depth==0) {
		$out[$name].='<ul class="search-tree">';
	} else {
		$out[$name].='<ul class="search-tree" style="display:block">';
	}

	foreach($ParentLayer->layer as $Layer) {

		$counter++;

		$HasSub=(count($Layer->layer)>0);

		$out[$name].='<li class="search-item">';

		getServiceDetails($Service,$Layer,'layer',$HasSub);

		if($HasSub)
			getServiceWMS($Service,$Layer, $depth+1);

		$out[$name].='</li>';

	}

	$out[$name].='</ul>';

}

// WMC-Struktur parsen
function getServiceWMC($Service) {
	global $out, $name, $startpage, $L, $page, $Wappen;
	global $counter;

	$out[$name].='<ul class="search-tree">';
		$out[$name].='<li class="search-item">';
		getServiceDetails($Service,$Service,'wmc',0);
		$out[$name].='</li>';
	$out[$name].='</ul>';
}
// Dataset-Struktur parsen
function getServiceDataset($Service) {
	global $out, $name, $startpage, $L, $page, $Wappen, $admin;
	global $counter, $mapbenderUrl;
	$out[$name].='<ul class="search-tree">';
	$out[$name].='<li class="search-item">';
	//dataset object - folder - TODO change symbol +/-	
	getServiceDetails($Service,$Service,'dataset',0);
	$countLayers = 0;
	$countAtomFeeds = 0;
	$countFeaturetypes = 0;
	$countModules = 0;

	$layerList = "";
	$atomFeedList = "";
	$featuretypeList = "";
	$moduleList = "";
	//pull all coupled layers
	$Type = 'wms';
	$image = 'map';
	$parameter_name = 'LAYER';
	
	if (count($Service->coupledResources->layer) > 0) {
		$layerList.='<div id="viewdialog'.$Service->id.'"  class="resource-list" style="display: none;"><ul>'.'<li class="resource-category-header"><img title="'._mb('Map Layers').'" src="'.MAPBENDER_PATH.'/img/osgeo_graphics/geosilk/server_map.png">'._mb('Map Layers').'</li>';	
		foreach($Service->coupledResources->layer as $coupledLayer) {
			$searchLayerId = $coupledLayer->id;
			$Layer = getLayerObject($coupledLayer, $coupledLayer->id);
			if ($Layer !== null) {
			//foreach($coupledLayer->srv->layer as $Layer) {
			
				$counter++;
				$countLayers++;
				//$HasSub=(count($Layer->layer)>0);	
				//show layer title, abstract, time, constraints, extent, preview, featureinfo, epsg, log, restricted network, ..., load buttons if possible
				$layerList.='<li class="resource-element">';
				//$out[$name].= $Layer->title;
				$layerList.='<a href="'.$Layer->mdLink.'" target="_blank">'.$Layer->title.'</a><br>';
				//http://10.50.165.218/mapbender/geoportal/mod_showPreview.php?resource=layer&id=36681
				$layerList.='<img width="40px" height="40px" title="Preview" src="'.MAPBENDER_PATH.'/geoportal/mod_showPreview.php?resource=layer&id='.$Layer->id.'">';
				//parse bbox
				//$bbox = explode(",", $Layer->bbox);
				$getMapUrl = $admin->getExtentGraphic(explode(",", $Layer->bbox));
				//TODO change metadata polygon before commit
				//$out[$name].='<img width="40px" height="40px" title="Extent" src="'.MAPBENDER_PATH.'/../cgi-bin/mapserv?map=/data/mapbender/tools/wms_extent/extents.map&VERSION=1.1.1&REQUEST=GetMap&SERVICE=WMS&LAYERS=demis,background,extent,metadata_polygon&STYLES=&SRS=EPSG:4326&BBOX=5.9087735,48.792855,8.7466965,51.106045&WIDTH=120&HEIGHT=120&FORMAT=image/png&BGCOLOR=0xffffff&TRANSPARENT=TRUE&EXCEPTIONS=application/vnd.ogc.se_inimage&minx='.$bbox[0].'&miny='.$bbox[1].'&maxx='.$bbox[2].'&maxy='.$bbox[3].'">';
				$layerList.='<img width="40px" height="40px" title="Extent" src="'.$getMapUrl.'">';
				
				//$Type = "wms";
				//Zoom to layer extent symbol
				//$e = new mb_exception("search_functions_dienste.php: constraints for wms  ".$coupledLayer->srv->id." : ".$coupledLayer->srv->hasConstraints." - layer id: ".$Layer->id);
				if($Layer->permission!='true') {
					if($_SESSION['mb_user_id']==2) {
						$layerList.='<img class="search-mapicons" src="fileadmin/design/icn_encrypted.png" alt="'.$L['Schloss'].'" title="'.$L['DiensteBerechtigung'].'" />';
					} else {
						$values='ID='.$Layer->id.'ÿTITLE='.$Layer->title.'ÿTYPE='.$Type.'ÿTO='.$Layer->permission.'ÿlang='.$Lang;
						$code=CodeParameter($values);
						$layerList.='<a href="fileadmin/scripts/register_service.php?service='.$code.'" target="_blank" onclick="openwindow(this.href); return false"><img class="search-mapicons" src="fileadmin/design/icn_encrypted_mail.png" alt="'.$L['Schloss'].'" title="'.$L['DiensteBerechtigung'].'" /></a>';
					}
				} else {
				//add layer symbol
				if($coupledLayer->srv->hasConstraints=='1') {
					$values='id='.$coupledLayer->srv->id.'ÿel='.$Type.$counter.'ÿtype='.$Type.'ÿlang='.$Lang.'ÿurl='.$L['KarteURL'].'?'.$parameter_name.'[id]='.$Layer->id;
					$code=CodeParameter($values);
					$valuesZoom='id='.$coupledLayer->srv->id.'ÿel='.$Type.$counter.'ÿtype='.$Type.'ÿlang='.$Lang.'ÿurl='.$L['KarteURL'].'?'."LAYER[zoom]=1&".$parameter_name.'[id]='.$Layer->id;
					$codeZoom=CodeParameter($valuesZoom);
					$layerList.='<a onclick="return tou2(this,document.getElementById(\''.$Type.$counter.'\'),'.$coupledLayer->srv->id.',\''.$Type.'\',\''.$code.'\');" href="'.$L['KarteURL'].'?'.$parameter_name.'[id]='.$Layer->id.'"><img  class="search-mapicons" src="fileadmin/design/icn_'.$image.'.png" title="'.$L['In Karte aufnehmen'].'" /></a>';
					$layerList.='<a onclick="return tou2(this,document.getElementById(\''.$Type.$counter.'\'),'.$coupledLayer->srv->id.',\''.$Type.'\',\''.$codeZoom.'\');" href="'.$L['KarteURL'].'?LAYER[zoom]=1&'.$parameter_name.'[id]='.$Layer->id.'"><img class="search-mapicons" src="fileadmin/design/icn_zoommap.png" title="Auf Ebenenausdehnung zoomen" /></a>';
				} else {
					$layerList.='<a href="'.$L['KarteURL'].'?'.$parameter_name.'[id]='.$Layer->id.'"><img class="search-mapicons" src="fileadmin/design/icn_'.$image.'.png" title="'.$L['In Karte aufnehmen'].'" /></a>';
					$layerList.='<a href="'.$L['KarteURL'].'?LAYER[zoom]=1&'.$parameter_name.'[id]='.$Layer->id.'"><img class="search-mapicons" src="fileadmin/design/icn_zoommap.png" title="Auf Ebenenausdehnung zoomen" /></a>';
				}
				$layerList.='<a href="'.$coupledLayer->srv->layer[0]->getCapabilitiesUrl.'" target="_blank"><img class="search-mapicons" src="'.MAPBENDER_PATH.'/img/osgeo_graphics/layer-wms.png" title="'._mb('Capabilities').'" /></a>';
				}
				//further things from detail function
				// Inspire
				if($Layer->inspire==1) {
					$layerList.='<img src="fileadmin/design/icn_inspire.png" title="Inspire" />';
				}
				// AccessConstraints/TermsOfUse
				if($coupledLayer->srv->hasConstraints=='1') {
					$values='id='.$coupledLayer->srv->id.'ÿtype='.$Type.'ÿlang='.$Lang;
					$code=CodeParameter($values);
					$layerList.='<a href="javascript:opentou(\''.$code.'\');"><img src="'.$coupledLayer->srv->symbolLink.'" title="'.$L['TermsOfUse'].'" /></a>';
				} else {
					$layerList.='<img src="'.$coupledLayer->srv->symbolLink.'" title="'.$L['TermsOfUseOK'].'" />';
				}
				//openData
				if($coupledLayer->srv->isopen==1) {
					$layerList.='<a target="_blank" href="http://www.opendefinition.org"><img src="fileadmin/design/od_80x15_blue.png" title="'.$L['OpenData'].'" /></a>';
				}
				
				// Costs
				if($coupledLayer->srv->price>0) {
					$layerList.='<img src="fileadmin/design/icn_euro.png" title="Costs" />';
				}

				// Logging
				if($coupledLayer->srv->logged==1) {
					$layerList.='<img src="fileadmin/design/icn_logging.png" title="Logged" />';
				}

				// Network Restrictions
				if($coupledLayer->srv->nwaccess==1) {
					$layerList.='<img src="fileadmin/design/icn_eingeschraenketes_netz.png" title="Network Restrictions" />';
				}
				// Status
				switch($coupledLayer->srv->status) {
					case '1':
						$layerList.='<img src="fileadmin/design/icn_go.png" title="'.$L['Monitoring1'].'" />';
						break;
					case '0':
						$layerList.='<img src="fileadmin/design/icn_wait.png" title="'.$L['Monitoring0'].'" />';
						break;
					case '-1':
						$layerList.='<img src="fileadmin/design/icn_stop.png" title="'.$L['Monitoring-1'].'" />';
						break;
					case '-2':
						$layerList.='<img src="fileadmin/design/icn_refresh.png" title="'.$L['Monitoring-2'].'" />';
						break;
					case '-3':
						$layerList.='<img src="fileadmin/design/icn_warning.png" title="'.$L['Monitoring-3'].'" />';
						break;
					default:
						if ($Class != 'wmc') {
							$layerList.='<img src="fileadmin/design/icn_ampel_grau.png" title="Monitoring nicht aktiv" />';
						}
						break;
				}
				
				// Availability
				if($coupledLayer->srv->avail=='') {
					$layerList.='<span class="search-icons-availabilty"><span title="'._mb('Monitoring not activated').'">? %</span></span>';
				} else {
					$layerList.='<span class="search-icons-availabilty">'.$coupledLayer->srv->avail.' %</span>';
				}
				if($Layer->queryable=='true') {
					$layerList.='<img src="fileadmin/design/icn_info.png" title="'.$L['Queryable'].'" />';
				} else {
					$layerList.='<img src="fileadmin/design/icn_info_grau.png" title="'.$L['NotQueryable'].'" />';
				}
				if($Layer->srsProblem=='false') {
					$layerList.='<img src="fileadmin/design/icn_epsg.png" title="'.$L['NotEPSG'].'" />';
				} else {
					$layerList.='<img src="fileadmin/design/icn_epsg_grau.png" title="'.$L['EPSG'].'" />';
				}
				$layerList.='</li>';
			}
		}
		$layerList.='</ul></div>';
	}

	//pull all coupled inspire download services
	if (count($Service->coupledResources->inspireAtomFeeds) > 0) {
		$atomFeedList.='<div id="downloaddialog'.$Service->id.'"  class="resource-list" style="display: none;"><ul>'.'<li class="resource-category-header"><img title="'._mb('Download').'" src="'.MAPBENDER_PATH.'/img/gnome/document-save.png">'._mb('Downloadservices').'</li>';	
		foreach($Service->coupledResources->inspireAtomFeeds as $atomFeed) {
			$countAtomFeeds++;
			//getCoupledInspireDls($coupledLayer->srv, $coupledLayer->srv, 1);
			$currentUuid = $Service->uuid;
			$atomFeedList.='<li class="resource-element">';
			$atomFeedList.='<img class="resource-category-header" width="20px" height="20px" src="'.MAPBENDER_PATH.'/img/inspire_tr_36.png" title="'._mb("INSPIRE").'" />';
			switch ($atomFeed->type) {
				case "wmslayergetmap":
					$atomFeedList .= _mb('Download raster data from INSPIRE Download Service')."<a href='".MAPBENDER_PATH."/plugins/mb_downloadFeedClient.php?url=".urlencode($mapbenderUrl."/php/mod_inspireDownloadFeed.php?id=".$currentUuid."&type=SERVICE&generateFrom=wmslayer&layerid=".$atomFeed->resourceId)."' target='_blank'><img class='search-mapicons' src='".MAPBENDER_PATH."/img/osgeo_graphics/geosilk/raster_download.png' title='"._mb('Download raster data from INSPIRE Download Service')."'/></a>";
					break;
				case "wmslayerdataurl":
					$atomFeedList .=  _mb('Download linked data from INSPIRE Download Service')."<a href='".MAPBENDER_PATH."/plugins/mb_downloadFeedClient.php?url=".urlencode($mapbenderUrl."/php/mod_inspireDownloadFeed.php?id=".$currentUuid."&type=SERVICE&generateFrom=dataurl&layerid=".$atomFeed->resourceId)."' target='_blank'><img class='search-mapicons' src='".MAPBENDER_PATH."/img/osgeo_graphics/geosilk/link_download.png' title='"._mb('Download linked data from INSPIRE Download Service')."'/></a>";
					break;
				case "wfsrequest":
					$atomFeedList .=  _mb('Download GML data from INSPIRE Download Service')."<a href='".MAPBENDER_PATH."/plugins/mb_downloadFeedClient.php?url=".urlencode($mapbenderUrl."/php/mod_inspireDownloadFeed.php?id=".$currentUuid."&type=SERVICE&generateFrom=wfs&wfsid=".$atomFeed->serviceId)."' target='_blank'><img class='search-mapicons' src='".MAPBENDER_PATH."/img/osgeo_graphics/geosilk/vector_download.png' title='"._mb('Download GML data from INSPIRE Download Service')."'/></a>";
					break;
				case "downloadlink":
					$atomFeedList .=  _mb('Download linked data from INSPIRE Download Service')."<a href='".MAPBENDER_PATH."/plugins/mb_downloadFeedClient.php?url=".urlencode($mapbenderUrl."/php/mod_inspireDownloadFeed.php?id=".$currentUuid."&type=SERVICE&generateFrom=metadata")."' target='_blank'><img class='search-mapicons' src='".MAPBENDER_PATH."/img/osgeo_graphics/geosilk/link_download.png' title='"._mb('Download linked data from INSPIRE Download Service')."'/></a>";
					break;
			}
			$atomFeedList.='</li>';
		}
		$atomFeedList.='</ul></div>';
	}
	//pull all coupled searchable featuretypes
	//pull all coupled featuretypes - show only links to featuretype metadata and symbol for geometrytype, epsg?
	//$Type = 'wms';
	//$image = 'map';
	//$parameter_name = 'LAYER';	
	if (count($Service->coupledResources->featuretype) > 0) {
		$Type = 'wfs';
		$featuretypeList.='<div id ="featuretypedialog'.$Service->id.'" class="resource-list" style="display: none;"><ul>'.'<li class="resource-category-header"><img title="'._mb('Featuretypes').'" src="'.MAPBENDER_PATH.'/img/osgeo_graphics/geosilk/server_vector.png">'._mb('Featuretypes').'</li>';	
		foreach($Service->coupledResources->featuretype as $featuretype) {
			foreach($featuretype->srv->ftype as $subFeaturetype) {
				$countFeaturetypes++;
				$featuretypeList.='<li class="resource-element">';
				//Geometrietyp
				$featuretypeList.='<a href="'.$subFeaturetype->mdLink.'" target="_blank">'.$subFeaturetype->title.' ('.$subFeaturetype->name.')'.'</a><br>';
				//$featuretypeList.='<div class="search-icons">';
				switch($subFeaturetype->geomtype) {
					case 'PointPropertyType':
						$featuretypeList.='<img class="resource-category-header" src="fileadmin/design/icn_pkt.png" title="'._mb("Punktgeometrie").'" />';
						break;
					case 'GeometryPropertyType':
						$featuretypeList.='<img class="resource-category-header" src="fileadmin/design/icn_geo_unbekannt.png" title="'._mb("Geometrietyp unbekannt").'" />';
						break;
					case 'PolygonPropertyType':
						$featuretypeList.='<img class="resource-category-header" src="fileadmin/design/icn_poly.png" title="'._mb("Flächengeometrie").'" />';
						break;
					case 'LinePropertyType':
						$featuretypeList.='<img class="resource-category-header" src="fileadmin/design/icn_line.png" title="'._mb("Liniengeometrie").'" />';
						break;
					default:
						$featuretypeList.='<img class="resource-category-header" src="fileadmin/design/icn_geo_unbekannt.png" title="'._mb("Geometrietyp unbekannt").'" />';
						break;
				}
				//$bbox = explode(',', $subFeaturetype->bbox);
				//TODO change metadata polygon before commit
				//$out[$name].='<img width="40px" height="40px" title="Extent" src="'.MAPBENDER_PATH.'/../cgi-bin/mapserv?map=/data/mapbender/tools/wms_extent/extents.map&VERSION=1.1.1&REQUEST=GetMap&SERVICE=WMS&LAYERS=demis,background,extent,metadata_polygon&STYLES=&SRS=EPSG:4326&BBOX=5.9087735,48.792855,8.7466965,51.106045&WIDTH=120&HEIGHT=120&FORMAT=image/png&BGCOLOR=0xffffff&TRANSPARENT=TRUE&EXCEPTIONS=application/vnd.ogc.se_inimage&minx='.$bbox[0].'&miny='.$bbox[1].'&maxx='.$bbox[2].'&maxy='.$bbox[3].'">';
				$getMapUrl = $admin->getExtentGraphic(explode(",", $subFeaturetype->bbox));
				$featuretypeList.='<img width="40px" height="40px" title="Extent" src="'.$getMapUrl.'"/>';
				// Inspire
				if($subFeaturetype->inspire==1) {
					$featuretypeList.='<img src="fileadmin/design/icn_inspire.png" title="Inspire" />';
				}
				// AccessConstraints/TermsOfUse
				if($featuretype->srv->hasConstraints=='1') {
					$values='id='.$featuretype->srv->id.'ÿtype='.$Type.'ÿlang='.$Lang;
					$code=CodeParameter($values);
					$featuretypeList.='<a href="javascript:opentou(\''.$code.'\');"><img src="'.$featuretype->srv->symbolLink.'" title="'.$L['TermsOfUse'].'" /></a>';
				} else {
					$featuretypeList.='<img src="'.$featuretype->srv->symbolLink.'" title="'.$L['TermsOfUseOK'].'" />';
				}
				//openData
				if($featuretype->srv->isopen==1) {
					$featuretypeList.='<a target="_blank" href="http://www.opendefinition.org"><img src="fileadmin/design/od_80x15_blue.png" title="'.$L['OpenData'].'" /></a>';
				}
				// Costs
				if($featuretype->srv->price>0) {
					$featuretypeList.='<img src="fileadmin/design/icn_euro.png" title="Costs" />';
				}

				// Logging
				if($featuretype->srv->logged==1) {
					$featuretypeList.='<img src="fileadmin/design/icn_logging.png" title="Logged" />';
				}

				// Network Restrictions
				if($featuretype->srv->nwaccess==1) {
					$featuretypeList.='<img src="fileadmin/design/icn_eingeschraenketes_netz.png" title="Network Restrictions" />';
				}
				//show urls to describe featuretype and url to capabilities
				$featuretypeList.='<a href="'.$subFeaturetype->schema.'" target="_blank"><img src="'.MAPBENDER_PATH.'/img/osgeo_graphics/geosilk/application_view_columns.png" title="'._mb('Data schema').'" /></a>';
				
				$featuretypeList.='<a href="'.$featuretype->srv->getCapabilitiesUrl.'" target="_blank"><img class="search-mapicons" src="'.MAPBENDER_PATH.'/img/osgeo_graphics/layer-wfs.png" title="'._mb('Capabilities-URL').'" /></a>';
				//show wfs modules and wfs 2.0 stored queries
				if (count($subFeaturetype->modul) > 0) {
					$countModules++;
					$counter++; //TODO - test if counter is needed
					$moduleList.='<div id="moduldialog'.$Service->id.'"  class="resource-list" style="display: none;"><ul>'.'<li class="resource-category-header"><img title="'._mb('Featuretypes').'" src="'.MAPBENDER_PATH.'/img/gnome/preferences-desktop-remote-desktop.png">'._mb('Predefined modules').'</li>';
					foreach($subFeaturetype->modul as $subFeaturetypeModule) {
						$moduleList.='<li class="resource-element">';
						switch($subFeaturetypeModule->type) {
							case 0:
								$moduleList.='<img onclick="openclose3(this);" src="fileadmin/design/icn_abfragemodul.png" alt="WFS" title="'.$L['WFSKartenwerk'].'" />';
								break;
							case 1:
								$moduleList.='<img onclick="openclose3(this);" src="fileadmin/design/icn_suchmodul.png" alt="WFS" title="'.$L['WFSKartenwerk'].'" />';
								break;
						}
						$moduleList.=$subFeaturetypeModule->title;
						//map things for featuretype
						//type
						$Type = 'wfs';
						$parameter_name = 'FEATURETYPE';
						$id_field='wfs_conf_id';
						switch($subFeaturetypeModule->type) {
							case 0:
								$image='download';
								break;
							case 1:
								$image='suche';
								break;
						}
						if($subFeaturetypeModule->permission!='true') {
							if($_SESSION['mb_user_id']==2) {
								$moduleList.='<img class="search-mapicons" src="fileadmin/design/icn_encrypted.png" alt="'.$L['Schloss'].'" title="'.$L['DiensteBerechtigung'].'" />';
							} else {
								$values='ID='.$subFeaturetypeModule->id.'ÿTITLE='.$subFeaturetypeModule->title.'ÿTYPE='.$Type.'ÿTO='.$subFeaturetypeModule->permission.'ÿlang='.$Lang;
								$code=CodeParameter($values);
								$moduleList.='<a href="fileadmin/scripts/register_service.php?service='.$code.'" target="_blank" onclick="openwindow(this.href); return false"><img class="search-mapicons" src="fileadmin/design/icn_encrypted_mail.png" alt="'.$L['Schloss'].'" title="'.$L['DiensteBerechtigung'].'" /></a>';
							}
						} else {
							if($featuretype->srv->hasConstraints=='1') {
								$values='id='.$featuretype->srv->id.'ÿel='.$Type.$counter.'ÿtype='.$Type.'ÿlang='.$Lang.'ÿurl='.$L['KarteURL'].'?'.$parameter_name.'[id]='.$subFeaturetypeModule->id;
								$code=CodeParameter($values);
								$moduleList.='<a onclick="return tou2(this,document.getElementById(\''.$Type.$counter.'\'),'.$featuretype->srv->id.',\''.$Type.'\',\''.$code.'\');" href="'.$L['KarteURL'].'?'.$parameter_name.'[id]='.$subFeaturetypeModule->id.'"><img class="search-mapicons" src="fileadmin/design/icn_'.$image.'.png" title="'.$L['In Karte aufnehmen'].'" /></a>';
							} else {
								$moduleList.='<a href="'.$L['KarteURL'].'?'.$parameter_name.'[id]='.$subFeaturetypeModule->id.'"><img class="search-mapicons" src="fileadmin/design/icn_'.$image.'.png" title="'.$L['In Karte aufnehmen'].'" /></a>';
							}
							$moduleList.='</li>';
						}
					}
					$moduleList.='</ul></div>';
				}
			}
			$featuretypeList.='</li>';
		}		
		$featuretypeList.='</ul></div>';
	}
	//pull all coupled modules?
	$out[$name].= '<ul>';
	if ($countLayers > 0) {
		$out[$name].= '<div onclick="$(\'#viewdialog'.$Service->id.'\' ).css(\'display\',\'block\');$(\'#viewdialog'.$Service->id.'\' ).dialog({dialogClass:\'fixed-jqdialog\', height: innerHeight * 0.8, width:600, modal: true, draggable: false, buttons: {\''._mb('Close').'\': function() {$( this ).dialog( \'close\' );}}});" class="resource-type-button"><img style="width: 20px; height: 20px;" title="'._mb('Map Layers').'('.$countLayers.')'.'" src="'.MAPBENDER_PATH.'/img/osgeo_graphics/geosilk/show.png">'._mb('View').' ('.$countLayers.')'.'</div>';
	}
	//$e = new mb_exception("search_functions_dienste.php: md_id: ".$Service->id." count atiom feeds: ".count($countAtomFeeds));
	if ($countAtomFeeds > 0) {
		$out[$name].= '<div onclick="$(\'#downloaddialog'.$Service->id.'\' ).css(\'display\',\'block\');$(\'#downloaddialog'.$Service->id.'\' ).dialog({dialogClass:\'fixed-jqdialog\', height: innerHeight * 0.8, width:600, modal: true,draggable: false,buttons: {\''._mb('Close').'\': function() {$( this ).dialog( \'close\' );}}});" class="resource-type-button"><img style="width: 20px; height: 20px;" title="'._mb('Download').'('.$countAtomFeeds.')'.'" src="'.MAPBENDER_PATH.'/img/gnome/document-save.png">'._mb('Download').' ('.$countAtomFeeds.')'.'</div>';
	}
	if ($countModules > 0) {
		$out[$name].= '<div onclick="$(\'#moduldialog'.$Service->id.'\' ).css(\'display\',\'block\');$(\'#moduldialog'.$Service->id.'\' ).dialog({dialogClass:\'fixed-jqdialog\', height: innerHeight * 0.8, width:600, modal: true,draggable: false,buttons: {\''._mb('Close').'\': function() {$( this ).dialog( \'close\' );}}});" class="resource-type-button"><img style="width: 20px; height: 20px;" title="'._mb('Modules').'('.$countModules.')'.'" src="'.MAPBENDER_PATH.'/img/gnome/preferences-desktop-remote-desktop.png">'._mb('Modules').' ('.$countModules.')'.'</div>';
	}
	if ($countFeaturetypes > 0) {
		$out[$name].= '<div onclick="$(\'#featuretypedialog'.$Service->id.'\' ).css(\'display\',\'block\');$(\'#featuretypedialog'.$Service->id.'\' ).dialog({dialogClass:\'fixed-jqdialog\', height: innerHeight * 0.8 ,width:600, modal: true,draggable: false,buttons: {\''._mb('Close').'\': function() {$( this ).dialog( \'close\' );}}});" class="resource-type-button"><img style="width: 20px; height: 20px;" title="'._mb('Featuretypes').'('.$countFeaturetypes.')'.'" src="'.MAPBENDER_PATH.'/img/osgeo_graphics/layer-vector.png">'._mb('Featuretypes').' ('.$countFeaturetypes.')'.'</div>';
	}
	$out[$name].= '</ul>';
	$out[$name].= $layerList;
	$out[$name].= $atomFeedList;
	$out[$name].= $featuretypeList;
	$out[$name].= $moduleList;
	$out[$name].='</li>';
	$out[$name].='</ul>';	
}

// WFS-Struktur parsen
function getServiceWFS($Service) {
	global $out, $name, $startpage, $L, $page, $Wappen;
	global $counter;

	$out[$name].='<ul class="search-tree">';

	foreach($Service->ftype as $FType) {

		$counter++;

		$HasSub=is_array($FType->modul);

		$out[$name].='<li class="search-item">';

		getServiceDetails($Service,$FType,'ftype',$HasSub);

		if($HasSub) {

			$out[$name].='<ul class="search-tree" style="display:block">';

			foreach($FType->modul as $Modul) {

				$counter++;

				$out[$name].='<li class="search-item">';

				getServiceDetails($Service,$Modul,'module',$HasSub);

				$out[$name].='</li>';
			}

			$out[$name].='</ul>';

		}

		$out[$name].='</li>';

	}

	$out[$name].='</ul>';
}

// Infos ausgeben
function getServiceDetails($Service,$Layer,$Class,$HasSub) {
	global $out, $name, $startpage, $L, $Lang, $page, $Wappen, $admin;
	global $counter;

	switch($Class) {
		case 'layer':
			$parameter_name='LAYER';
			$id_field='id';
			$Type='wms';
			break;

		case 'module':
		case 'ftype':
			$parameter_name='FEATURETYPE';
			$id_field='wfs_conf_id';
			$Type='wfs';
			break;
		case 'wmc':
			$parameter_name='WMC';
			$id_field='wmc_id';
			$Type='wmc';
			break;	
		case 'dataset':
			$parameter_name='DATASET';
			$id_field='id';
			$Type='dataset';
			break;	
	}
	
	$out[$name].='
	<div>
		<div class="search-titleicons">
			<div class="search-checkbox">';

	// Schloss-Symbol oder Checkbox
	if($Class=="ftype" or $Class=='wmc' or $Class=='dataset') {
		if($Class=="ftype") {
			$out[$name].='<img src="fileadmin/design/clear.png" />';
		}
		if($Class=="wmc") {
			$out[$name].='<img src="fileadmin/design/Mapset.png" />';
		}
		if($Class=="dataset") {
			//$out[$name].='<img src="fileadmin/design/Mapset.png" />';
		}
	} else {
		if($Layer->permission!='true') {
			if($_SESSION['mb_user_id']==2) {
				$out[$name].='<img src="fileadmin/design/icn_encrypted.png" alt="'.$L['Schloss'].'" title="'.$L['DiensteBerechtigung'].'" />';
			} else {
				$values='ID='.$Layer->id.'ÿTITLE='.$Layer->title.'ÿTYPE='.$Type.'ÿTO='.$Layer->permission.'ÿlang='.$Lang;
				$code=CodeParameter($values);
				$out[$name].='<a href="fileadmin/scripts/register_service.php?service='.$code.'" target="_blank" onclick="openwindow(this.href); return false"><img src="fileadmin/design/icn_encrypted_mail.png" alt="'.$L['Schloss'].'" title="'.$L['DiensteBerechtigung'].'" /></a>';
			}
		} else {
			if($Service->hasConstraints=='1') {
				$values='id='.$Service->id.'ÿel='.$Type.$counter.'ÿtype='.$Type.'ÿlang='.$Lang;
				$code=CodeParameter($values);
				$out[$name].='<input name="'.$parameter_name.'[]" value="'.$Layer->id.'" id="'.$Type.$counter.'" type="checkbox" onclick="return tou(this,'.$Service->id.',\''.$Type.'\',\''.$code.'\');"/>';
			} else {
				$out[$name].='<input name="'.$parameter_name.'[]" value="'.$Layer->id.'" id="'.$Type.$counter.'" type="checkbox" />';
			}
		}
	}
	$out[$name].='</div>';

	// Icon mit Klapp-Funktion
	switch($Class) {

		// wms-Layer
		case 'layer':
			if($Layer->isRoot) {
				if($HasSub) {
					$out[$name].='<img onclick="openclose3(this);" src="fileadmin/design/icn_wms2.png" alt="WMS" title="'.$L['WMSKartenwerk'].'" />';
				} else {
					$out[$name].='<img src="fileadmin/design/icn_wms.png" alt="WMS" title="'.$L['WMSKartenwerk'].'" />';
				}
			} else {
				if($HasSub) {
					$out[$name].='<img onclick="openclose3(this);" src="fileadmin/design/icn_layer2.png" alt="Layer" />';
				} else {
					$out[$name].='<img src="fileadmin/design/icn_layer.png" alt="Layer" />';
				}
			}
			break;

		// wfs-FeatureType
		case 'ftype':
			if($HasSub) {
				$out[$name].='<img onclick="openclose3(this);" src="fileadmin/design/icn_wfs2.png" alt="WFS" title="'.$L['WFSKartenwerk'].'" />';
			} else {
				$out[$name].='<img src="fileadmin/design/icn_wfs.png" alt="WFS" title="'.$L['WFSKartenwerk'].'" />';
			}
			break;

		// wfs-Module (abfrage/suche)
		case 'module':
			switch($Layer->type) {
				case 0:
					$out[$name].='<img onclick="openclose3(this);" src="fileadmin/design/icn_abfragemodul.png" alt="WFS" title="'.$L['WFSKartenwerk'].'" />';
					break;
				case 1:
					$out[$name].='<img onclick="openclose3(this);" src="fileadmin/design/icn_suchmodul.png" alt="WFS" title="'.$L['WFSKartenwerk'].'" />';
					break;
			}
			break;
		case 'dataset':
				//$out[$name].='<img onclick="openclose3(this);" src="fileadmin/design/icn_wfs.png" alt="Dataset" title="'.$L['WFSKartenwerk'].'" />';
			break;
	}
	$out[$name].='</div>';

	// Titel
	if ($Class == 'dataset') {
		$out[$name].='<div class="search-title"><a href="'.$Layer->mdLink.'" target="_blank" onclick="metadataWindow = window.open(this.href,\'width=400,height=250,left=50,top=50,scrollbars=yes,resizable=yes\');metadataWindow.focus(); return false">'.$Layer->title.' (ID='.$Service->id.')</a>';

	} else {
		$out[$name].='<div class="search-title"><a href="'.$Layer->mdLink.'" target="_blank" onclick="metadataWindow = window.open(this.href,\'width=400,height=250,left=50,top=50,scrollbars=yes,resizable=yes\');metadataWindow.focus(); return false">'.$Layer->title.'</a>';
	}
	// Download option (armin 2014-07-30)
	//$out[$name] .= " - ".json_encode($Layer->downloadOptions);
	$out[$name].='</div>';


	// Icons
	$out[$name].='
		<br class="clr" />
		<div class="search-icons">';

	//Preview
	if($Layer->previewURL!='') {
		$out[$name].='
			<img class="search-icons-preview" src="'.$Layer->previewURL.'" title="Vorschau" alt="Fehler in Vorschau">';
	}
	if ($Class == 'dataset') {
		$getMapUrl = $admin->getExtentGraphic(explode(",", $Service->bbox[0]));
		$out[$name].='<img width="75px" height="75px" title="Extent" src="'.$getMapUrl.'"/>';
	}
	// Logo armin 2010.09.07
	if($Service->logoUrl!='') {
		$out[$name].='<img src="'.$Service->logoUrl.'" title="'.$Service->respOrg.'" height="40"/>';
	} 
	/*else {
		$out[$name].='<img src="fileadmin/design/icn_wappen_grau.png" title="'.$Service->respOrg.'" />';
	}*/
	// Wappen
	if($Service->iso3166!='' && in_array($Service->iso3166,$Wappen) || !isset($Service->iso3166)) {
		$out[$name].='<img src="fileadmin/design/wappen_'.$Service->iso3166.'.png" title="'.$L[$Service->iso3166].'" />';
	} else {
		$out[$name].='<img src="fileadmin/design/icn_wappen_grau.png" title="Länderkennung fehlt" />';
	}
	// Inspire
	if($Layer->inspire==1) {
		$out[$name].='<img src="fileadmin/design/icn_inspire.png" title="Inspire" />';
	}
	// AccessConstraints/TermsOfUse
	if($Service->hasConstraints=='1' && $Class !== 'dataset') {
		$values='id='.$Service->id.'ÿtype='.$Type.'ÿlang='.$Lang;
		$code=CodeParameter($values);
		$out[$name].='<a href="javascript:opentou(\''.$code.'\');"><img src="'.$Service->symbolLink.'" title="'.$L['TermsOfUse'].'" /></a>';
	} else {
		$out[$name].='<img src="'.$Service->symbolLink.'" title="'.$L['TermsOfUseOK'].'" />';
	}
	//openData
	if($Service->isopen==1) {
		$out[$name].='<a target="_blank" href="http://www.opendefinition.org"><img src="fileadmin/design/od_80x15_blue.png" title="'.$L['OpenData'].'" /></a>';
	}
	if ($Class =='layer' || $Class == 'ftype' || $Class == 'module') {
		// Costs
		if($Service->price>0) {
			$out[$name].='<img src="fileadmin/design/icn_euro.png" title="Costs" />';
		}

		// Logging
		if($Service->logged==1) {
			$out[$name].='<img src="fileadmin/design/icn_logging.png" title="Logged" />';
		}

		// Network Restrictions
		if($Service->nwaccess==1) {
			$out[$name].='<img src="fileadmin/design/icn_eingeschraenketes_netz.png" title="Network Restrictions" />';
		}

		// Status
		switch($Service->status) {
			case '1':
				$out[$name].='<img src="fileadmin/design/icn_go.png" title="'.$L['Monitoring1'].'" />';
				break;
			case '0':
				$out[$name].='<img src="fileadmin/design/icn_wait.png" title="'.$L['Monitoring0'].'" />';
				break;
			case '-1':
				$out[$name].='<img src="fileadmin/design/icn_stop.png" title="'.$L['Monitoring-1'].'" />';
				break;
			case '-2':
				$out[$name].='<img src="fileadmin/design/icn_refresh.png" title="'.$L['Monitoring-2'].'" />';
				break;
			case '-3':
				$out[$name].='<img src="fileadmin/design/icn_warning.png" title="'.$L['Monitoring-3'].'" />';
				break;
			default:
				if ($Class != 'wmc') {
					$out[$name].='<img src="fileadmin/design/icn_ampel_grau.png" title="Monitoring nicht aktiv" />';
				}
				break;
		}
	}
	// Availability
	if ($Class != 'wmc' && $Class != 'dataset') {
		if($Service->avail=='') {
			$out[$name].='<span class="search-icons-availabilty"><span title="Monitoring nicht aktiv">? %</span></span>';
		} else {
			$out[$name].='<span class="search-icons-availabilty">'.$Service->avail.' %</span>';
		}
	}
	//Geometrietyp
	if($Type=='wfs') {
		switch($Layer->geomtype) {
			case 'PointPropertyType':
				$out[$name].='<img src="fileadmin/design/icn_pkt.png" title="Punktgeometrie" />';
				break;
			case 'GeometryPropertyType':
				$out[$name].='<img src="fileadmin/design/icn_geo_unbekannt.png" title="Geometrietyp unbekannt" />';
				break;
			case 'PolygonPropertyType':
				$out[$name].='<img src="fileadmin/design/icn_poly.png" title="Flächengeometrie" />';
				break;
			case 'LinePropertyType':
				$out[$name].='<img src="fileadmin/design/icn_line.png" title="Liniengeometrie" />';
				break;
			default:
				$out[$name].='<img src="fileadmin/design/icn_geo_unbekannt.png" title="Geometrietyp unbekannt" />';
				break;
		}
	}

	//Queryable
	if($Type=='wms') {
		if($Layer->queryable=='true') {
			$out[$name].='<img src="fileadmin/design/icn_info.png" title="'.$L['Queryable'].'" />';
		} else {
			$out[$name].='<img src="fileadmin/design/icn_info_grau.png" title="'.$L['NotQueryable'].'" />';
		}
	}


	// EPSG
	if ($Class != 'wmc' && $Class != 'dataset') {
		if($Layer->srsProblem=='false') {
			$out[$name].='<img src="fileadmin/design/icn_epsg.png" title="'.$L['NotEPSG'].'" />';
		} else {
			$out[$name].='<img src="fileadmin/design/icn_epsg_grau.png" title="'.$L['EPSG'].'" />';
		}
	}
	$out[$name].='</div>';

	$out[$name].='<div class="search-mapicons">';
	if($Layer->permission=='true') {
		if($Type=='wms') {
			if ($Layer->downloadOptions == null) {
				//$out[$name] .= " - download not possible";
			} else {
				$idList = "";
				foreach ($Layer->downloadOptions as $uuid) {
					$idList .= $uuid->uuid.",";
				}
				$idList = rtrim($idList,',');
				//get list of ids
				
				$out[$name] .= '<a href="../../mapbender/php/mod_getDownloadOptions.php?id='.$idList.'&outputFormat=html&languageCode='.$Lang.'" target="_blank" onclick="downloadWindow = window.open(this.href,\'downloadWindow\',\'width=600,height=400,left=100,top=100,scrollbars=yes,menubar=yes,toolbar=yes,resizable=yes\');downloadWindow.focus(); return false"><img width=24,height=24 src="../../mapbender/img/gnome/document-save.png" title="Download" /></a>';
				//$out[$name] .= " - " . json_encode($Layer->downloadOptions);
			}
			if($Service->hasConstraints=='1') {
				$values='id='.$Service->id.'ÿel='.$Type.$counter.'ÿtype='.$Type.'ÿlang='.$Lang.'ÿurl='.$L['KarteURL'].'?LAYER[zoom]=1&'.$parameter_name.'[id]='.$Layer->id;
				$code=CodeParameter($values);
				$out[$name].='<a onclick="return tou2(this,document.getElementById(\''.$Type.$counter.'\'),'.$Service->id.',\''.$Type.'\',\''.$code.'\');" href="'.$L['KarteURL'].'?LAYER[zoom]=1&'.$parameter_name.'[id]='.$Layer->id.'"><img src="fileadmin/design/icn_zoommap.png" title="Auf Ebenenausdehnung zoomen" /></a>';
			} else {
				$out[$name].='<a href="'.$L['KarteURL'].'?LAYER[zoom]=1&'.$parameter_name.'[id]='.$Layer->id.'"><img src="fileadmin/design/icn_zoommap.png" title="Auf Ebenenausdehnung zoomen" /></a>';
			}
		}

		switch($Type) {
			case 'wms':
				$image='map';
				break;
			case 'wmc':
				$image='map';
				break;
			
			case 'wfs':
				switch($Layer->type) {
					case 2:
						$image='download';
						break;
					case 0:
						$image='suche';
						break;
				}
				break;
		}

/*
		if($Service->hasConstraints=='1') {
			$values='id='.$Service->id.'ÿel='.$Type.$counter.'ÿtype='.$Type.'ÿlang='.$Lang;
			$code=CodeParameter($values);
			$out[$name].='<input type="image" onclick="return tou3(this,document.getElementById(\''.$Type.$counter.'\'),'.$Service->id.',\''.$Type.'\',\''.$code.'\');" src="fileadmin/design/icn_'.$image.'.png" title="'.$L['In Karte aufnehmen'].'" />';
		} else {
			$out[$name].='<input type="image" onclick="document.getElementById(\'c'.$counter.'\').checked=true;" src="fileadmin/design/icn_'.$image.'.png" title="'.$L['In Karte aufnehmen'].'" />';
		}
*/
		if($Service->hasConstraints=='1') {
			$values='id='.$Service->id.'ÿel='.$Type.$counter.'ÿtype='.$Type.'ÿlang='.$Lang.'ÿurl='.$L['KarteURL'].'?'.$parameter_name.'[id]='.$Layer->id;
			$code=CodeParameter($values);
			$out[$name].='<a onclick="return tou2(this,document.getElementById(\''.$Type.$counter.'\'),'.$Service->id.',\''.$Type.'\',\''.$code.'\');" href="'.$L['KarteURL'].'?'.$parameter_name.'[id]='.$Layer->id.'"><img src="fileadmin/design/icn_'.$image.'.png" title="'.$L['In Karte aufnehmen'].'" /></a>';
		} else {
			$out[$name].='<a href="'.$L['KarteURL'].'?'.$parameter_name.'[id]='.$Layer->id.'"><img src="fileadmin/design/icn_'.$image.'.png" title="'.$L['In Karte aufnehmen'].'" /></a>';
		}

	}
	if($Type=='wmc') { //always show map symbol
		if($Service->hasConstraints=='1') {
			/*$values='id='.$Service->id.'ÿel='.$Type.$counter.'ÿtype='.$Type.'ÿlang='.$Lang.'ÿurl='.$L['KarteURL'].'?LAYER[zoom]=1&'.$parameter_name.'[id]='.$Layer->id;
			$code=CodeParameter($values);
			$out[$name].='<a onclick="return tou2(this,document.getElementById(\''.$Type.$counter.'\'),'.$Service->id.',\''.$Type.'\',\''.$code.'\');" href="'.$L['KarteURL'].'?LAYER[zoom]=1&'.$parameter_name.'[id]='.$Layer->id.'"><img src="fileadmin/design/icn_zoommap.png" title="Auf Ebenenausdehnung zoomen" /></a>';*/
		} else {
			$out[$name].='<a href="'.$L['KarteURL'].'?'.$parameter_name."=".$Layer->id.'"><img src="fileadmin/design/icn_map.png" title="" /></a>';
		}
	}





	$out[$name].='</div><br class="clr" />';


	// Beschreibung
	$out[$name].='
		<div class="search-info-dep"><b>'._mb('Responsible Party').'</b>: '.$Service->respOrg.'</div>';
	$out[$name].='	
		<div class="search-info"><b>'._mb('Metadata date').'</b>: '.$Service->date.'</div>';
	if ($Type=='dataset' && ($Service->timeBegin !=="" || $Service->timeEnd !=="")) {
		$out[$name].='<div class="search-info-actuality"><b>'._mb('Temporal extent').'</b>: '.$Service->timeBegin.' '._mb('to').' '.$Service->timeEnd.'</div>';
		}
	$out[$name].='	<div class="search-text">'.textcut2($Layer->abstract,200).'</div>
	</div>
	';
}

function getServiceTagCloud($filename) {
	global $out, $name;
	global $LinkURL;
	global $admin;
	$url=$LinkURL.'?cat='.PtH($_REQUEST['cat']).'&searchfilter=';

	$fileExists = $admin->getFromStorage($filename, TMP_SEARCH_RESULT_STORAGE);
        if ($fileExists !== false) {
		$DATA=json_decode($fileExists);
	}
	//$DATA=json_decode(file_get_contents($filename));
	if($DATA) {
		$out[$name].='
		<div class="tagcloud">
			<h3 onclick="jQuery(this).toggleClass(\'open\').next().toggle();">'.$DATA->tagCloud->title.'</h3>
			<div class="cloud" style="display:none">';
		if(is_array($DATA->tagCloud->tags)) {
			foreach($DATA->tagCloud->tags as $tag) {
				global $LinkURL;
				$out[$name].='
				<a href="'.$url.urlencode($tag->url).'" title="'.$tag->title.'" style="font-size:'.$tag->weight.'px">'.$tag->title.'</a>';
			}
		}
		$out[$name].='
			</div>
		</div>';
	}
}

function getLayerObject($coupledLayer, $searchLayerId) {
	//check if srv object exists, if so - use array below server else use layer array directly
	$return = null;
	if (isset($coupledLayer->srv->id)) {
		//echo "<br>srv object found<br>";
		$layerArray = $coupledLayer->srv->layer;
	} else {
		//echo "<br>srv object not found<br>";
		$layerArray = $coupledLayer->layer;
	}
	foreach($layerArray as $Layer) {
		//echo "<br>compare ids: ".$Layer->id." - ".$searchLayerId."<br>";
		if ($searchLayerId == $Layer->id) {
			//echo "- identical<br>";
			//echo "layer name: ".$Layer->name."<br>";
			$return = $Layer;
			break;
		} else {
			//echo "- not identical - go deeper<br>";
			$newLayer = getLayerObject($Layer, $searchLayerId);
			if ($newLayer !== null) {
				$return = $newLayer;
				break;
			}
		}
	}
	return $return;
}

function CountDienste($file) {
	global $out, $name, $L;
	global $filterResources;
	global $admin;
	$ready=1;
	$allcounter=0;
	$numRessource=0;

	if(count($filterResources)==0)
		getSearchFilter(str_replace('###SERVICE###','_filter',$file));

	foreach($filterResources as $resource=>$resourceTitle) {

		$numRessource++;
		$counter=0;

		$filename=str_replace('###SERVICE###','_'.$resource.'_1',$file);
		$fileExists = $admin->getFromStorage($filename, TMP_SEARCH_RESULT_STORAGE);
		if ($fileExists !== false) {
		//if(file_exists($filename) && filesize($filename)>0) {
			$DATA=json_decode($fileExists);
			if($DATA) {
				foreach($DATA as $class=>$ClassData) {
					$Info=$ClassData->md;
					$allcounter+=$Info->nresults;
				}
			} else {
				$ready=0;
			}
		} else {
			$ready=0;
		}
	}

	return array("ready"=>$ready,"count"=>$allcounter);
}


?>
