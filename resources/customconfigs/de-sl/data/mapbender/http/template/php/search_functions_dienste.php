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

	$url=$LinkURL.'?searchid='.$_REQUEST['searchid'].'&amp;selectsearch='.intval($_REQUEST['selectsearch']).'&amp;uid='.$_REQUEST['uid'];

	$catpages=explode('|',$page);
	for($i=0;$i<count($searchResources);$i++) {
		$catpages[$i]=intval($catpages[$i]);
	}

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

		if(!file_exists($filename)) {
			parse_str($OrigURL,$URLParts);
			$URLParts['searchResources']=$resource;
			$URLParts['searchPages']=$currentpage+1;
			$URLParts['hostName']=$_SERVER['HTTP_HOST'];
			$SearchParams=http_build_query($URLParts);
			file_get_contents('http://localhost/mapbender/php/mod_callMetadata.php?'.$SearchParams);

			// Warten nach der ersten Suche
			$microsPerSecond = 1000000;
			usleep(($microsPerSecond/5));

		}
		if(file_exists($filename) && filesize($filename)>0) {
			$DATA=json_decode(file_get_contents($filename));
			if($DATA) {
				foreach($DATA as $class=>$ClassData) {
					$Info=$ClassData->md;
					$allcounter+=$Info->nresults;
					if(!$startpage) {

						$out[$name].='
							<div class="search-cat '.((isset($_REQUEST['pos']) && $_REQUEST['pos']==$numRessource-1)?'opened':'closed').'">
							<div class="search-header" onclick="openclose2(this);"><h2>'.$resourceTitle.'</h2><p>('.$Info->nresults.' '.$L['Trefferkurz'].' in '.round($Info->genTime,2).' '.$L['Sekunden'].')</p></div>';

						getServiceTagCloud($tagcloudfilename);

						$out[$name].=PageCounter($Info->nresults, $Info->rpp, $url, $name, count($searchResources), $numRessource-1);

						foreach($ClassData->srv as $Service) {
							#$out[$name].=print_r($Service, true);
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
			if(file_exists($filename) && filesize($filename)>0) {
				$DATA=json_decode(file_get_contents($filename));
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
	global $out, $name, $startpage, $L, $Lang, $page, $Wappen;
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
	}

	$out[$name].='
	<div>
		<div class="search-titleicons">
			<div class="search-checkbox">';

	// Schloss-Symbol oder Checkbox
	if($Class=="ftype" or $Class=='wmc') {
		if($Class=="ftype") {
			$out[$name].='<img src="fileadmin/design/clear.png" />';
		}
		if($Class=="wmc") {
			$out[$name].='<img src="fileadmin/design/Mapset.png" />';
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
	}
	$out[$name].='</div>';

	// Titel
	$out[$name].='<div class="search-title"><a href="'.$Layer->mdLink.'" target="_blank" onclick="metadataWindow = window.open(this.href,\'width=400,height=250,left=50,top=50,scrollbars=yes\');metadataWindow.focus(); return false">'.$Layer->title.'</a></div>';


	// Icons
	$out[$name].='
		<br class="clr" />
		<div class="search-icons">';

	//Preview
	if($Layer->previewURL!='') {
		$out[$name].='
			<img class="search-icons-preview" src="'.$Layer->previewURL.'" title="Vorschau" alt="Fehler in Vorschau">';
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
	if($Service->hasConstraints=='1') {
		$values='id='.$Service->id.'ÿtype='.$Type.'ÿlang='.$Lang;
		$code=CodeParameter($values);
		$out[$name].='<a href="javascript:opentou(\''.$code.'\');"><img src="'.$Service->symbolLink.'" title="'.$L['TermsOfUse'].'" /></a>';
	} else {
		$out[$name].='<img src="'.$Service->symbolLink.'" title="'.$L['TermsOfUseOK'].'" />';
	}

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

	// Availability
	if ($Class != 'wmc') {
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
	if ($Class != 'wmc') {
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
					case 0:
						$image='download';
						break;
					case 1:
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
		<div class="search-info-dep"><b>'.$Service->respOrg.'</b></div>
		<div class="search-info">'.$Service->date.'</div>
		<div class="search-text">'.textcut2($Layer->abstract,200).'</div>
	</div>
	';
}

function getServiceTagCloud($filename) {
	global $out, $name;
	global $LinkURL;

	$url=$LinkURL.'?cat='.$_REQUEST['cat'].'&searchfilter=';

	$DATA=json_decode(file_get_contents($filename));
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

function CountDienste($file) {
	global $out, $name, $L;
	global $filterResources;

	$ready=1;
	$allcounter=0;
	$numRessource=0;

	if(count($filterResources)==0)
		getSearchFilter(str_replace('###SERVICE###','_filter',$file));

	foreach($filterResources as $resource=>$resourceTitle) {

		$numRessource++;
		$counter=0;

		$filename=str_replace('###SERVICE###','_'.$resource.'_1',$file);

		if(file_exists($filename) && filesize($filename)>0) {
			$DATA=json_decode(file_get_contents($filename));
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
