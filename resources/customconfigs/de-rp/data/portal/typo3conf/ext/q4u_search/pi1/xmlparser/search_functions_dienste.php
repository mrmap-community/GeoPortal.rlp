<?php

function Dienste($file, $max=0) {
	global $output, $out, $name, $data, $counter, $startpage, $count, $ebene, $DoPrint, $ebeneoffen;
	global $xml_begin, $xml_end, $xml_content, $xml_data, $xml_typ, $L, $xml_cat, $xml_max;
	global $check_array;
	global $FeaturetypeID,$OpenedLists;
	global $Wappen;

	$Wappen=array(
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

	$xml_begin=$xml_end=$xml_content=$xml_data=false;
	$xml_max=$max;
	$counter=0;
	$ebeneoffen=$ebene=0;
	$DoPrint=false;

	$FeaturetypeID=array();
	$OpenedLists=0;
	$countMembers=0;

	$out[$name].='
	<div id="search-container-'.$name.'">
	<form action="karten.html" method="get" name="formmaps">
		<fieldset class="hidden">
			<input name="zoomToLayer" type="hidden" value="0" />
		</fieldset>
	';

	if(file_exists($file)) {
		$content=file_get_contents($file);
		if(strpos($content,'<ready>true</ready>')!==false) {
			$countMembers=substr_count($content,'<member');
			if(!$startpage) {
				if(strpos($content,'<overlimit>true</overlimit>')!==false) {
					$out[$name].='<p class="search-hits-info">Es wurden mehr als '.$countMembers.' Treffer gefunden.</p><br class="clr" />';
				}
			}


//			if(strpos($content,"<overlimit>true</overlimit>")!==false) {
//				$out[$name].="<p>".$L["OverLimit"]."</p>";
//			}

			$xmlFile = file($file);
			$parser = xml_parser_create();

			xml_set_element_handler($parser, 'startDienste', 'endDienste');
			xml_set_character_data_handler($parser, 'cdataDienste');

			foreach($xmlFile as $elem) {
				xml_parse($parser, $elem);
				if($max!=0 && $counter>=$max) break;
			}

			while($OpenedLists>0) {
				$out[$name].='</ul>';
				$OpenedLists--;
			}

			if($counter==0 && $xml_end) {
				$out[$name].='<p>'.$L['kein Ergbnis'].'</p>';
				$ready=1; // Suche abgeschlossen
			} elseif( (!$xml_end && !$startpage) || (!$xml_end && $startpage && $counter<$max) ) {
				$ready=0; // Suche noch nicht abgeschlossen
			} else {
				$ready=1; // Suche abgeschlossen
			}
			xml_parser_free($parser);
		} else {
			$ready=0; // Suche noch nicht abgeschlossen Ready fehlt
		}
	} else {
		$ready=0; // Suche noch nicht abgeschlossen Datei nicht gefunden
	}
	if($counter!=0) {
		if($startpage) {
			$out[$name].='<p class="search-hits-info">';
			if(strpos($content,'<overlimit>true</overlimit>')!==false) {
				$out[$name].='Es wurden mehr als '.$countMembers.' Treffer gefunden.<br/>';
			}
			$out[$name].='<a href="#" onclick="location.href=location.pathname+\'?searchid='.intval($_REQUEST['searchid']).'&amp;selectsearch=1&amp;uid='.$_REQUEST['uid'].'&amp;cat=dienste\';return false">Die ersten '.$countMembers.' Treffer anzeigen.</a></p>';
		}

		$out[$name].='
			<fieldset class="search-dienste"><input type="submit" value="'.$L['In Karte aufnehmen'].'" /></fieldset>
			<br class="clr" />
		';

	}

	$out[$name].='
		</form>
	</div>
	';

	return $ready;
}


function startDienste($parser, $element_name, $element_attribute) {
	global $output, $out, $name, $data, $counter, $startpage, $count, $page, $items_page, $ebene, $DoPrint, $ebeneoffen;
	global $xml_begin, $xml_end, $xml_content, $xml_data, $xml_typ, $L, $xml_cat, $xml_max;
	global $check_array;
	global $FeaturetypeID,$OpenedLists;

	$element_name = strtolower($element_name);

	switch($element_name) {
		case 'result':
			$xml_begin=true;
			break;
		case 'category':
			if($element_attribute['NAME']=='') $xml_cat=$L['keineKategorie'];
			else $xml_cat=$element_attribute['NAME'];
			if(!$startpage) {
				$out[$name].='
					<div class="search-cat closed">
					<div class="search-header" onclick="openclose2(this);"><h2>'.$xml_cat.'</h2><p>'.$element_attribute['COUNT'].' '.$L['Trefferkurz'].'</p></div>';
			}
			$FeaturetypeID=array();

			break;
		case 'member':
// Neu: FeatureTypes schachteln
// Start
			if($DoPrint) {
				if($FeaturetypeID[$ebene]!=$data['featuretype_id']) {
					if($FeaturetypeID[$ebene]!='') { // zumachen
						if(!$startpage) {
							$out[$name].='</li>';
							$out[$name].='</ul>';
							$OpenedLists--;
						}
					}
					if($data['featuretype_id']!='') { // aufmachen
						if(!$startpage) {
							if($OpenedLists==0) {
								$out[$name].='<ul class="search-tree" style="display: block">';
							} else {
								$out[$name].='<ul style="display: block">';
							}
							$out[$name].='<li class="search-item">';
							$OpenedLists++;
						}
						printDienste('start',true);
					}
					$FeaturetypeID[$ebene]=$data['featuretype_id'];
				}
			}
// Ende

			if($DoPrint) {
				if(!$startpage) {
					if($ebene>$ebeneoffen) {
						if($OpenedLists==0) {
							$out[$name].='<ul class="search-tree">';
						} elseif($FeaturetypeID[$ebene]!='') {
							$out[$name].='<ul style="display: block">';
						} else {
							$out[$name].='<ul style="display: none">';
						}
						$ebeneoffen++;
						$OpenedLists++;
					}
					$out[$name].='<li class="search-item">';
				}

				printDienste('start');
			}

			$data=array();
			$ebene++;

			$DoPrint=true;
			$xml_content=true;
			break;
		case 'ready':
			$xml_content=true;
			$xml_data=true;
			$xml_typ=$element_name;
			break;
		case 'overLimit':
			$xml_data=true;
			$xml_typ=$element_name;
			break;
		case 'id':
		case 'title':
		case 'abstract':
		case 'accessconstraints':
		case 'type':
		case 'date':
		case 'department':
		case 'queryable':
		case 'termsofuse':
		case 'permission':
		case 'epsg':
		case 'federalstate':
		case 'last_monitoring':
		case 'availability':
		case 'relevance':
		case 'layername':
		case 'layer_pos':
		case 'featuretype_id':
		case 'featuretype_title':
		case 'featuretype_abstract':
		case 'geomtype':
		case 'wfs_id':
		case 'wfs_conf_id':
		case 'wfs_conf_title':
		case 'wfs_conf_abstract':
		case 'wfs_conf_modul':
		case 'wms_id':
			if($xml_content) {
				$xml_typ=$element_name;
				$xml_data=true;
			}
			break;
	}
}

function endDienste($parser, $element_name) {
	global $output, $out, $name, $data, $counter, $startpage, $count, $page, $items_page, $ebene, $DoPrint, $ebeneoffen;
	global $xml_begin, $xml_end, $xml_content, $xml_data, $xml_typ, $L, $xml_cat, $xml_max;
	global $check_array;
	global $FeaturetypeID,$OpenedLists;

	$element_name = strtolower($element_name);

	switch($element_name) {
		case 'ready':
			$xml_content=false;
			$xml_data=false;
			if($data['ready']=='true') $xml_end=true;
			break;
		case 'result':
			$xml_begin=false;
			break;
		case 'category':
			if(!$startpage) {
				while($OpenedLists>0) {
					$out[$name].='</ul>
					';
					$OpenedLists--;
				}
				$out[$name].='</div>
				';
			}
			$ebene=0;
			$ebeneoffen=0;
			$OpenedLists=0;
			$FeaturetypeID=array();
			break;
		case 'member':

			if(!$startpage) {
				if($ebene<=$ebeneoffen) {
					if($FeaturetypeID[$ebeneoffen]!='') {
						if(!$startpage) {
							$out[$name].='</li>';
							$out[$name].='</ul>';
							$OpenedLists--;
							$FeaturetypeID[$ebeneoffen]='';
						}
					}
					$out[$name].='</ul>';
					$ebeneoffen--;
					$OpenedLists--;
				}
			}

// Neu: FeatureTypes schachteln
// Start
			if($DoPrint) {
				if($FeaturetypeID[$ebene]!=$data['featuretype_id']) {
					if($FeaturetypeID[$ebene]!='') { // zumachen
						if(!$startpage) {
							$out[$name].='</li>';
							$out[$name].='</ul>';
							$OpenedLists--;
						}
					}
					if($data['featuretype_id']!='') { // aufmachen
						if(!$startpage) {
							if($OpenedLists==0) {
								$out[$name].='<ul class="search-tree" style="display: block">';
							} else {
								$out[$name].='<ul style="display: block">';
							}
							$out[$name].='<li class="search-item">';
							$OpenedLists++;
						}
						printDienste('ende',true);
					}
					$FeaturetypeID[$ebene]=$data['featuretype_id'];
				}
			}
// Ende

			if($DoPrint) {
				if(!$startpage) {
					if($ebene>$ebeneoffen) {
						if($OpenedLists==0) {
							$out[$name].='<ul class="search-tree">';
						} elseif($FeaturetypeID[$ebene]!='') {
							$out[$name].='<ul style="display: block">';
						} else {
							$out[$name].='<ul style="display: none">';
						}
						$ebeneoffen++;
						$OpenedLists++;
					}
					$out[$name].='<li class="search-item">';
				}
				printDienste('ende');
			}

			$out[$name].='</li>';

			$DoPrint=false;
			$ebene--;
			$xml_content=false;
			break;
		case 'overLimit':
		case 'id':
		case 'title':
		case 'abstract':
		case 'accessconstraints':
		case 'type':
		case 'date':
		case 'department':
		case 'queryable':
		case 'termsofuse':
		case 'permission':
		case 'epsg':
		case 'federalstate':
		case 'last_monitoring':
		case 'availability':
		case 'relevance':
		case 'layername':
		case 'layer_pos':
		case 'featuretype_id':
		case 'featuretype_title':
		case 'featuretype_abstract':
		case 'geomtype':
		case 'wfs_id':
		case 'wfs_conf_id':
		case 'wfs_conf_title':
		case 'wfs_conf_abstract':
		case 'wfs_conf_modul':
		case 'wms_id':
			$xml_data=false;
			break;
	}
}

function cdataDienste($parser, $element_inhalt) {
	global $output, $out, $name, $data, $counter, $startpage, $count, $page, $items_page, $ebene, $DoPrint;
	global $xml_begin, $xml_end, $xml_content, $xml_data, $xml_typ, $L, $xml_cat, $xml_max;

	if($xml_begin && $xml_data) {
		$data[$xml_typ].=$element_inhalt;
	}
}

function printDienste($multi,$printFeatureType=false) {
	global $output, $out, $name, $data, $counter, $startpage, $count, $page, $items_page, $ebene, $DoPrint;
	global $L, $xml_cat, $xml_max;
//	global $FeaturetypeID;

	if($data['type']=='wfs') {
		$parameter_name='portal_services_wfs';
		$id_field='wfs_conf_id';
		$Type='wfs';
	} else {
		$parameter_name='portal_services';
		$id_field='id';
		$Type='wms';
	}

	if($DoPrint) {
		if($startpage) {
			if(!$printFeatureType) {
				if($counter<$xml_max) {
					$out[$name].='
					<div class="search-item">
						<div class="search-titleicons"><div class="search-checkbox">';

					if($printFeatureType) {
						$out[$name].='<img src="fileadmin/design/clear.png" />
						';
					} else {
						if($data['permission']!='true') {
							if($_SESSION['mb_user_id']==2) {
								$out[$name].='<img src="fileadmin/design/icn_encrypted.png" alt="'.$L['Schloss'].'" title="'.$L['DiensteBerechtigung'].'" />
								';
							} else {
								$values='ID='.$data['id'].'ÿTITLE='.$data['title'].'ÿAC='.$data['accessconstraints'].'ÿTO='.$data['permission'];
								$code=CodeParameter($values);

								$out[$name].='<a href="fileadmin/scripts/register_service.php?service='.$code.'" target="_blank" onclick="openwindow(this.href); return false"><img src="fileadmin/design/icn_encrypted_mail.png" alt="'.$L['Schloss'].'" title="'.$L['DiensteBerechtigung'].'" /></a>
								';
							}
						} else {
							if($data['termsofuse']!='') {
								$values='id='.$data['termsofuse'].'ÿel=c'.$counter.'ÿac='.$data['accessconstraints'].'ÿwms_id='.$data[$Type.'_id'].'ÿtype='.$Type;
								$code=CodeParameter($values);
								$out[$name].='<input name="'.$parameter_name.'[]" value="'.$data[$id_field].'" id="c'.$counter.'" type="checkbox" onclick="return tou(this,'.$data[$Type.'_id'].',\''.$Type.'\',\''.$code.'\');"/>
								';
							} elseif($data['accessconstraints']!='' && strtolower($data['accessconstraints'])!='none') {
								$values='id='.$data['termsofuse'].'ÿel=c'.$counter.'ÿac='.$data['accessconstraints'].'ÿwms_id='.$data[$Type.'_id'].'ÿtype='.$Type;
								$code=CodeParameter($values);
								$out[$name].='<input name="'.$parameter_name.'[]" value="'.$data[$id_field].'" id="c'.$counter.'" type="checkbox" onclick="return tou(this,'.$data[$Type.'_id'].',\''.$Type.'\',\''.$code.'\');"/>
								';
							} else {
								$out[$name].='<input name="'.$parameter_name.'[]" value="'.$data[$id_field].'" id="c'.$counter.'" type="checkbox" />
								';
							}
						}
					}
					$out[$name].='</div>';


					$ext='.png';

					if($data['type']=='wms') $out[$name].='<img src="fileadmin/design/wms'.$ext.'" alt="WMS" title="'.$L['WMSKartenwerk'].'" />';
					if($data['type']=='wfs' && $printFeatureType) $out[$name].='<img src="fileadmin/design/icn_wfs'.$ext.'" alt="WFS" title="'.$L['WFSKartenwerk'].'" />';
					if($data['type']=='wfs' && !$printFeatureType && $data['wfs_conf_modul']=='0') $out[$name].='<img src="fileadmin/design/icn_abfragemodul.png" alt="WFS" title="'.$L['WFSKartenwerk'].'" />';
					if($data['type']=='wfs' && !$printFeatureType && $data['wfs_conf_modul']!='0') $out[$name].='<img src="fileadmin/design/icn_suchmodul.png" alt="WFS" title="'.$L['WFSKartenwerk'].'" />';
					if($data['type']=='layer') $out[$name].='<img src="fileadmin/design/icn_'.$data['type'].$ext.'" alt="Layer" />';

					if($data['type']=='wfs' && $printFeatureType) {
						$Title=$data['featuretype_title'].' ('.$xml_cat.')';
					} elseif($data['type']=='wfs' && !$printFeatureType) {
						$Title=$data['wfs_conf_title'].' ('.$xml_cat.')';
					} else {
						$Title=$data['title'].' ('.$xml_cat.')';
					}
					if($data['type']=='wfs') {
						if($printFeatureType) {
							$out[$name].='</div><div class="search-title"><a href="/mapbender/x_geoportal/mod_featuretypeMetadata.php?wfs_conf_id='.$data['wfs_conf_id'].'" target="_blank" onclick="openwindow(this.href); return false">'.$data['featuretype_title'].' ('.$xml_cat.')</a></div>
							';
						} else {
							$out[$name].='</div><div class="search-title"><a href="/mapbender/x_geoportal/mod_featuretypeMetadata.php?wfs_conf_id='.$data['wfs_conf_id'].'" target="_blank" onclick="openwindow(this.href); return false">'.$data['wfs_conf_title'].' ('.$xml_cat.')</a></div>
							';
						}
					} else {
						$out[$name].='</div><div class="search-title"><a href="/mapbender/x_geoportal/mod_layerMetadata.php?id='.$data['id'].'" target="_blank" onclick="openwindow(this.href); return false">'.$data['title'].' ('.$xml_cat.')</a></div>
						';
					}


					if($data['type']!='layer' || ($data['type']=='layer' && $data['layername']!='') )
						printIconBlock($printFeatureType);

					$out[$name].='</div>
					';
					++$counter;
				}
			}
		} else {
			$out[$name].='<div>';
	//		$out[$name].='--'.$multi.'--'.$printFeatureType.'--'.$ebene.'--'.print_r($FeaturetypeID,true);

			$out[$name].='<div class="search-titleicons"><div class="search-checkbox">';

			if($printFeatureType) {
				$out[$name].='<img src="fileadmin/design/clear.png" />
				';
			} else {
				if($data['permission']!='true') {
					if($_SESSION['mb_user_id']==2) {
						$out[$name].='<img src="fileadmin/design/icn_encrypted.png" alt="'.$L['Schloss'].'" title="'.$L['DiensteBerechtigung'].'" />
						';
					} else {
						$values='ID='.$data['id'].'ÿTITLE='.$data['title'].'ÿAC='.$data['accessconstraints'].'ÿTO='.$data['permission'];
						$code=CodeParameter($values);

						$out[$name].='<a href="fileadmin/scripts/register_service.php?service='.$code.'" target="_blank" onclick="openwindow(this.href); return false"><img src="fileadmin/design/icn_encrypted_mail.png" alt="'.$L['Schloss'].'" title="'.$L['DiensteBerechtigung'].'" /></a>
						';
					}
				} else {
					if($data['termsofuse']!='') {
						$values='id='.$data['termsofuse'].'ÿel=c'.$counter.'ÿac='.$data['accessconstraints'].'ÿwms_id='.$data[$Type.'_id'].'ÿtype='.$Type;
						$code=CodeParameter($values);
						$out[$name].='<input name="'.$parameter_name.'[]" value="'.$data[$id_field].'" id="c'.$counter.'" type="checkbox" onclick="return tou(this,'.$data[$Type.'_id'].',\''.$Type.'\',\''.$code.'\');"/>
						';
					} elseif($data['accessconstraints']!='' && strtolower($data['accessconstraints'])!='none') {
						$values='id='.$data['termsofuse'].'ÿel=c'.$counter.'ÿac='.$data['accessconstraints'].'ÿwms_id='.$data[$Type.'_id'].'ÿtype='.$Type;
						$code=CodeParameter($values);
						$out[$name].='<input name="'.$parameter_name.'[]" value="'.$data[$id_field].'" id="c'.$counter.'" type="checkbox" onclick="return tou(this,'.$data[$Type.'_id'].',\''.$Type.'\',\''.$code.'\');"/>
						';
					} else {
						$out[$name].='<input name="'.$parameter_name.'[]" value="'.$data[$id_field].'" id="c'.$counter.'" type="checkbox" />
						';
					}
				}
			}

			$out[$name].='</div>';

			if($multi=='start') {
				$ext='_plus.png';
			} else {
				$ext='.png';
			}

			if($data['type']=='wms')   				$out[$name].='<img onclick="openclose3(this);" src="fileadmin/design/icn_wms'.$ext.'" alt="WMS" title="'.$L['WMSKartenwerk'].'" />';
			if($data['type']=='wfs' &&  $printFeatureType) $out[$name].='<img onclick="openclose3(this);" src="fileadmin/design/icn_wfs2.png" alt="WFS" title="'.$L['WFSKartenwerk'].'" />';
			if($data['type']=='wfs' && !$printFeatureType && $data['wfs_conf_modul']=='0') $out[$name].='<img onclick="openclose3(this);" src="fileadmin/design/icn_abfragemodul.png" alt="WFS" title="'.$L['WFSKartenwerk'].'" />';
			if($data['type']=='wfs' && !$printFeatureType && $data['wfs_conf_modul']!='0') $out[$name].='<img onclick="openclose3(this);" src="fileadmin/design/icn_suchmodul.png" alt="WFS" title="'.$L['WFSKartenwerk'].'" />';
			if($data['type']=='layer') 				$out[$name].='<img onclick="openclose3(this);" src="fileadmin/design/icn_'.$data['type'].$ext.'" alt="Layer" />';



			if($data['type']=='wfs') {
				if($printFeatureType) {
					$out[$name].='</div><div class="search-title"><a href="/mapbender/x_geoportal/mod_featuretypeMetadata.php?wfs_conf_id='.$data['wfs_conf_id'].'" target="_blank" onclick="openwindow(this.href); return false">'.$data['featuretype_title'].'</a></div>
					';
				} else {
					$out[$name].='</div><div class="search-title"><a href="/mapbender/x_geoportal/mod_featuretypeMetadata.php?wfs_conf_id='.$data['wfs_conf_id'].'" target="_blank" onclick="openwindow(this.href); return false">'.$data['wfs_conf_title'].'</a></div>
					';
				}
			} else {
				$out[$name].='</div><div class="search-title"><a href="/mapbender/x_geoportal/mod_layerMetadata.php?id='.$data['id'].'" target="_blank" onclick="openwindow(this.href); return false">'.$data['title'].'</a></div>
				';
			}

			if($data['type']!='layer' || ($data['type']=='layer' && $data['layername']!='') )
				printIconBlock($printFeatureType);

			$out[$name].='
				<div class="search-info-dep">'.$data['department'].'</div>
				<div class="search-info">'.$data['date'].'</div>
			';

			if($data['type']=='wfs' && $printFeatureType) {
				$Field='featuretype_abstract';
			} elseif($data['type']=='wfs' && !$printFeatureType) {
				$Field='wfs_conf_abstract';
			} else {
				$Field='abstract';
			}

			$out[$name].='
				<div class="search-text">'.textcut2($data[$Field],200).'</div>
			</div>
			';

			++$counter;
		}
	}
}

function printIconBlock($printFeatureType) {
	global $output, $out, $name, $data, $counter, $startpage, $count, $page, $items_page, $ebene, $DoPrint;
	global $L, $xml_cat, $xml_max;
	global $Wappen;


	if($data['type']=='wfs') {
		$parameter_name='portal_services_wfs';
		$id_field='wfs_conf_id';
		$Type='wfs';
	} else {
		$parameter_name='portal_services';
		$id_field='id';
		$Type='wms';
	}

	$out[$name].='<br class="clr" /><div class="search-icons">';
	if($data['type']!='wfs') {
#	$out[$name].='<img src="fileadmin/quadrat.jpg" title="Vorschau" alt="Fehler in Vorschau">';
	$out[$name].='<img class="search-icons-preview" src="/mapbender/x_geoportal/mod_layerPreview.php?id='.$data['id'].'" title="Vorschau" alt="Fehler in Vorschau">';
	//$out[$name].='<img src="/mapbender/x_geoportal/layer_preview/'.$data['id'].'_layer_map_preview.png"  width="100" height="100"  title="Vorschau" alt="Vorschau nicht verf&uuml;gbar">';
	}
	if($data['federalstate']!='' && in_array($data['federalstate'],$Wappen)) {
		$out[$name].='<img src="fileadmin/design/wappen_'.$data['federalstate'].'.png" title="'.$L[$data['federalstate']].'" />';
	} else {
		$out[$name].='<img src="fileadmin/design/icn_wappen_grau.png" title="Länderkennung fehlt" />';
	}

	if($data['termsofuse']!='') {
		$values='id='.$data['termsofuse'].'ÿac='.$data['accessconstraints'].'ÿwms_id='.$data[$Type.'_id'].'ÿtype='.$Type;
		$code=CodeParameter($values);

		if(strstr($_SERVER['HTTP_HOST'],'q4u.de')===false)
			$image=file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/mapbender/php/mod_termsofuse_service.php?id='.$data['termsofuse'].'&type=symbollink');
		$out[$name].='<a href="javascript:opentou(\''.$code.'\');"><img src="'.$image.'" title="'.$L['TermsOfUse'].'" /></a>';
	} else {
		if($data['accessconstraints']=='' || strtolower($data['accessconstraints'])=='none') {
			$out[$name].='<img src="fileadmin/design/icn_ok.png" title="'.$L['TermsOfUseOK'].'" />';
		} else {
			$values='id='.$data['termsofuse'].'ÿac='.$data['accessconstraints'].'ÿwms_id='.$data[$Type.'_id'].'ÿtype='.$Type;
			$code=CodeParameter($values);

			$out[$name].='<a href="javascript:opentou(\''.$code.'\');"><img src="fileadmin/design/icn_warn.png" title="'.$L['TermsOfUse'].'" /></a>';
//			$out[$name].='<img src="fileadmin/design/warn.png" title="'.$L['TermsOfUse'].'" />';
		}
	}


	switch($data['last_monitoring']) {
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
			$out[$name].='<img src="fileadmin/design/icn_ampel_grau.png" title="Monitoring nicht aktiv" />';
			break;
	}

	if($data['availability']=='') {
		$out[$name].='<span class="search-icons-availabilty"><span title="Monitoring nicht aktiv">? %</span></span>';
	} else {
		$out[$name].='<span class="search-icons-availabilty">'.$data['availability'].' %</span>';
	}


	switch($data['geomtype']) {
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
			if($data['type']=='wfs') {
				$out[$name].='<img src="fileadmin/design/icn_geo_unbekannt.png" title="Geometrietyp unbekannt" />';
			} else {
				if($data['queryable']=='true') {
					$out[$name].='<img src="fileadmin/design/icn_info.png" title="'.$L['Queryable'].'" />';
				} else {
					$out[$name].='<img src="fileadmin/design/icn_info_grau.png" title="'.$L['NotQueryable'].'" />';
				}
			}
			break;
	}


	if($data['epsg']=='false') {  // KlÃ¤ren, was hier drin steht!
		$out[$name].='<img src="fileadmin/design/icn_epsg.png" title="'.$L['NotEPSG'].'" />';
	} else {
		$out[$name].='<img src="fileadmin/design/icn_epsg_grau.png" title="'.$L['EPSG'].'" />';
	}

	$out[$name].='</div>';

	$out[$name].='<div class="search-mapicons">';
	if(!$printFeatureType) {
		if($data['type']!='wfs') {
			if($data['permission']=='true') {
				if($data['termsofuse']!='') {
					$values='id='.$data['termsofuse'].'ÿel=c'.$counter.'ÿac='.$data['accessconstraints'].'ÿwms_id='.$data[$Type.'_id'].'ÿtype='.$Type.'ÿurl=karten.html?zoomToLayer=1&'.$parameter_name.'[]='.$data[$id_field];
					$code=CodeParameter($values);
					$out[$name].='<a onclick="return tou2(document.getElementById(\'c'.$counter.'\'),'.$data[$Type.'_id'].',\''.$Type.'\',\''.$code.'\');" href="karten.html?zoomToLayer=1&'.$parameter_name.'[]='.$data[$id_field].'"><img src="fileadmin/design/icn_zoommap.png" title="Auf Ebenenausdehnung zoomen" /></a>';
				} elseif($data['accessconstraints']!='' && strtolower($data['accessconstraints'])!='none') {
					$values='id='.$data['termsofuse'].'ÿel=c'.$counter.'ÿac='.$data['accessconstraints'].'ÿwms_id='.$data[$Type.'_id'].'ÿtype='.$Type.'ÿurl=karten.html?zoomToLayer=1&'.$parameter_name.'[]='.$data[$id_field];
					$code=CodeParameter($values);
					$out[$name].='<a onclick="return tou2(document.getElementById(\'c'.$counter.'\'),'.$data[$Type.'_id'].',\''.$Type.'\',\''.$code.'\');" href="karten.html?zoomToLayer=1&'.$parameter_name.'[]='.$data[$id_field].'"><img src="fileadmin/design/icn_zoommap.png" title="Auf Ebenenausdehnung zoomen" /></a>';
				} else {
					$out[$name].='<a href="karten.html?zoomToLayer=1&'.$parameter_name.'[]='.$data[$id_field].'"><img src="fileadmin/design/icn_zoommap.png" title="Auf Ebenenausdehnung zoomen" /></a>';
				}
			}
		}


		if($data['wfs_conf_modul']=='0'){
			$image='download';
		}elseif($data['wfs_conf_modul']=='1'){
			$image='suche';
		} else {
			$image='map';
		}
		if($data['permission']=='true') {
			if($data['termsofuse']!='') {
				$values='id='.$data['termsofuse'].'ÿel=c'.$counter.'ÿac='.$data['accessconstraints'].'ÿwms_id='.$data[$Type.'_id'].'ÿtype='.$Type;
				$code=CodeParameter($values);
				$out[$name].='<input type="image" onclick="return tou3(document.getElementById(\'c'.$counter.'\'),'.$data[$Type.'_id'].',\''.$Type.'\',\''.$code.'\');" src="fileadmin/design/icn_'.$image.'.png" title="'.$L['In Karte aufnehmen'].'" />';
			} elseif($data['accessconstraints']!='' && $data['accessconstraints']!='none') {
				$values='id='.$data['termsofuse'].'ÿel=c'.$counter.'ÿac='.$data['accessconstraints'].'ÿwms_id='.$data[$Type.'_id'].'ÿtype='.$Type;
				$code=CodeParameter($values);
				$out[$name].='<input type="image" onclick="return tou3(document.getElementById(\'c'.$counter.'\'),'.$data[$Type.'_id'].',\''.$Type.'\',\''.$code.'\');" src="fileadmin/design/icn_'.$image.'.png" title="'.$L['In Karte aufnehmen'].'" />';
			} else {
				$out[$name].='<input type="image" onclick="document.getElementById(\'c'.$counter.'\').checked=true;" src="fileadmin/design/icn_'.$image.'.png" title="'.$L['In Karte aufnehmen'].'" />';
			}
		}
	}

	$out[$name].='</div><br class="clr" />';
}

?>