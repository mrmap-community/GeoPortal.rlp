<?php

function Meta($file, $max=0) {
	global $output, $out, $name, $data, $counter, $counter2, $startpage, $count, $page, $in_ajax, $npages, $unique, $rssurl;
	global $xml_begin, $xml_end, $xml_content, $xml_data, $xml_typ, $L, $xml_cat, $xml_max;
	global $check_array;
	global $filename;

	$xml_max=$max;
	$counter=$meta_count=0;

	if($startpage) {
		$out[$name].='<div id="search-container-'.$name.'">
		';
	} else {
		$out[$name].='<div id="search-container-'.$name.'-single">
		';
	}

	$allready=0;
	if(file_exists($file) && filesize($file)>0) {
		$allready=1;
		$indexfile = file($file);
		$interfaces=array();
		
		foreach($indexfile as $folder) {
			if(preg_match('/<opensearchinterface>(.+)<\/opensearchinterface>/',$folder,$matches)==1 && trim($matches[1])!='') {
				$interfaces[]=trim($matches[1]);
			}
		}

		$catpages=explode('|',$page);
		for($i=0;$i<count($interfaces);$i++) {
			$catpages[$i]=intval($catpages[$i]);
		}
		
		$url=$L['SuchURL'].'?searchid='.intval($_REQUEST['searchid']).'&amp;selectsearch='.intval($_REQUEST['selectsearch']).'&amp;uid='.$_REQUEST['uid'];

		foreach($interfaces as $key => $xml_cat) {

			$currentpage=$catpages[$key];

			$parts=split('\.',$file);
			$filename='';
			for($j=0;$j<count($parts)-1;$j++) {
				if($j>0) $filename.='.';
				$filename.=$parts[$j];
			}
			$filename.=($key+1).'_'.($currentpage+1).'.'.$parts[$j];

			$npages=$counter2=0;
			$rssurl='';
			$xml_begin=$xml_end=$xml_content=$xml_data=false;
			$data=array();
			$out['temp']='';

			if(!file_exists($filename) && $currentpage!=0 && !$in_ajax) {
				$parts=split('\.',$file);
				$filename1=$parts[0].($key+1).'_1.'.$parts[1];
				if(file_exists($filename1)) {
					$content = file_get_contents($filename1);
					if(preg_match('/<totalresults>(.+)<\/totalresults>/',$content,$matches)!=0)
						$counter2=$matches[1];
					if(preg_match('/<npages>(.+)<\/npages>/',$content,$matches)!=0)
						$npages=$matches[1];
					if(preg_match('/<querystring>(.+)<\/querystring>/',$content,$matches)!=0)
						$query=$matches[1];
				}
				if($currentpage<$npages) {
					file_get_contents('http://localhost/mapbender/x_geoportal/mod_readOpenSearchResults.php?q='.$query.'&p='.($currentpage+1).'&cat='.$key.'&request_id='.$unique);
				}
			}

			if(file_exists($filename)) {
				$xmlFile = file($filename);

				$parser = xml_parser_create();

				xml_set_element_handler($parser, 'startMeta', 'endMeta');
				xml_set_character_data_handler($parser, 'cdataMeta');

				foreach($xmlFile as $elem) {
					xml_parse($parser, $elem);
					if($max!=0 && $counter>=$max) break;
				}

				if( (!$xml_end && !$startpage) || (!$xml_end && $startpage && $counter<$max) ) {
					$allready=0; // Suche noch nicht abgeschlossen
				}

				xml_parser_free($parser);
			} else {
				
				if( !$startpage || ($startpage && $counter<$max) ) {
					$allready=0; // Suche noch nicht abgeschlossen
				}
			}
			if(!$startpage) {
				if($npages==0 && $currentpage!=0) { // Anzahl der Treffer und Seiten aus dem Datensatz der 1. Seite lesen
					$parts=split('\.',$file);
					$filename1=$parts[0].($key+1).'_1.'.$parts[1];
					if(file_exists($filename1)) {
						$content = file_get_contents($filename1);
						if(preg_match('/<totalresults>(.+)<\/totalresults>/',$content,$matches)!=0)
							$counter2=$matches[1];
						if(preg_match('/<npages>(.+)<\/npages>/',$content,$matches)!=0)
							$npages=$matches[1];
					}
				}
				$out[$name].='<div class="search-cat meta-search-container'.$meta_count.'">
					<div class="search-header" onclick="openclose(\''.$meta_count++.'\');"><h2>'.$xml_cat.'</h2><p>'.$counter2.' '.$L['Trefferkurz'].'</p></div>';
				if($rssurl!='') {
					$out[$name].='<div class="search-item"><div class="search-text"><a href="'.$L['KarteURL'].'?georssURL='.$rssurl.'"><img src="fileadmin/design/icn_map_rss.png"  title="Auf Karte anzeigen" /></a></div></div>';
				}
			}
			$out[$name].=$out['temp'];
			if(!$startpage) {

				if($npages>1) {
					$out[$name].='<div class="search-pagecounter-container">Seiten: ';
					$pagecounter=$catpages;
					$pmin=$currentpage+1-5;
					if($pmin<1) $pmin=1;
					if($pmin>1) $out[$name].='&hellip;';
					$pmax=$currentpage+1+5;
					if($pmax>$npages) $pmax=$npages;
					$more=($pmax<$npages)?'&hellip;':'';
					for($i=$pmin;$i<=$pmax;$i++) {
						$pagecounter[$key]=$i-1;
						$pagearray=implode('|',$pagecounter);
						if($i==$currentpage+1) {
							$out[$name].='<a class="search-pagecounter-item active" href="'.$url.'&amp;cat=meta&amp;page='.$pagearray.'">'.$i.'</a>';
						} else {
							$out[$name].='<a class="search-pagecounter-item" href="'.$url.'&amp;cat=meta&amp;page='.$pagearray.'">'.$i.'</a>';
						}
					}
					$out[$name].=$more.'
						</div>
					';
				}


				$out[$name].='</div>
				';
			}

		}

		if($counter==0 && $allready==1) {
			$out[$name].='<p>'.$L['kein Ergbnis'].'</p>';
		}
		
	}
	$out[$name].='</div>
	';

	return $allready;
}




function startMeta($parser, $element_name, $element_attribute) {
	global $output, $out, $name, $data, $counter, $counter2, $startpage, $count, $page, $items_page, $npages, $rssurl;
	global $xml_begin, $xml_end, $xml_content, $xml_data, $xml_typ, $L, $xml_cat, $xml_max;
	global $check_array;

	$element_name = strtolower($element_name);

	switch($element_name) {
		case 'resultlist':
			$xml_begin=true;
			break;
		case 'result':
			$data=array();
			$xml_content=true;
			break;
		case 'totalresults':
		case 'npages':
		case 'rssurl':
			$xml_typ=$element_name;
			$xml_data=true;
			break;
		case 'catalogtitle':
		case 'catalogtitlelink':
		case 'title':
		case 'abstract':
		case 'urlmdorig':
		case 'wmscapurl':
		case 'mbaddurl':
		case 'iso19139url':
		case 'inspireurl':
		case 'georssurl':
			if($xml_content) {
				$xml_typ=$element_name;
				$xml_data=true;
			}
			break;
	}
}

function endMeta($parser, $element_name) {
	global $output, $out, $name, $data, $counter, $counter2, $startpage, $count, $page, $items_page, $npages, $rssurl;
	global $xml_begin, $xml_end, $xml_content, $xml_data, $xml_typ, $L, $xml_cat, $xml_max;
	global $check_array;
	global $filename;

	$element_name = strtolower($element_name);

	switch($element_name) {
		case 'resultlist':
			$xml_begin=false;
			$xml_end=true;
			break;
		case 'result':
			$xml_content=false;
			$counter++;

			if($startpage) {
				if($counter<=$xml_max) {
					$out['temp'].='
						<div class="search-item">
							<div class="search-nr">'.$counter.'</div>
							<div class="search-title"><a target="_blank" href="/mapbender/x_geoportal/'.urldecode($data['catalogtitlelink']).'" onclick="openwindow(this.href); return false">'.urldecode($data['title']).'</a></div>
						</div>
					';
				}
			} else {
				$out['temp'].='<div class="search-item">
				';

       	if($data['wmscapurl']!='') {
					$out['temp'].='<div class="search-mapicons"><a href="'.$L['KarteURL'].'?wms1='.$data['wmscapurl'].'"><img src="fileadmin/design/icn_map.png" title="Auf Karte anzeigen" /></a></div>';
				} elseif($data['georssurl']!='') {
					$out['temp'].='<div class="search-mapicons"><a href="'.$L['KarteURL'].'?georssURL='.$data['georssurl'].'"><img src="fileadmin/design/map_rss.png" title="Auf Karte anzeigen" /></a></div>';
				}

				$out['temp'].='
					<div class="search-title"><a target="_blank" href="/mapbender/x_geoportal/'.urldecode($data['catalogtitlelink']).'" onclick="openwindow(this.href); return false">'.urldecode($data['title']).'</a></div>
					<div class="search-metacat">'.urldecode($data['catalogtitle']).'</div>
				';
#       	$out['temp'].='<div class="search-metacat">'.urldecode($data['categorie']).'</div>\n';
       	$out['temp'].='
       		<div class="search-text">'.urldecode($data['abstract']).'</div>
       	';
       	if($data['urlmdorig']!='') {
	       	$out['temp'].='
	       	<div class="search-metacat"><a target="_blank" href="'.urldecode($data['urlmdorig']).'" onclick="openwindow(this.href); return false">Originäre Metadaten</a></div>
	       	';
       	}
       	if($data['wmscapurl']!='') {
	       	$out['temp'].='<div class="search-metacat"><a target="_blank" href="'.urldecode($data['wmscapurl']).'" onclick="openwindow(this.href); return false">WMS GetCapabilities</a></div>
	       	';
       	}
       	if($data['iso19139url']!='' || $data['inspireurl']!='') {
	       	$out['temp'].='<div class="search-text"><strong>Alternative Formate:</strong><br />';
	       	if($data['iso19139url']!='') {
		       	$out['temp'].='<a target="_blank" href="/mapbender/x_geoportal/'.urldecode($data['iso19139url']).'" onclick="openwindow(this.href); return false"><img src="fileadmin/design/icn_iso19139.png" title="ISO19139" /></a>
		       	';
		      }
	       	if($data['inspireurl']!='') {
		       	$out['temp'].='<a target="_blank" href="/mapbender/x_geoportal/'.urldecode($data['inspireurl']).'" onclick="openwindow(this.href); return false"><img src="fileadmin/design/icn_inspire.png" title="INSPIRE" /></a>
		       	';
		      }
	       	$out['temp'].='</div>
	       	';
       	}
       	
				$out['temp'].='</div>
				';
			}
			$xml_content=false;
			break;

		case 'totalresults':
			$xml_data=false;
			$counter2=$data['totalresults'];
			break;
		case 'npages':
			$xml_data=false;
			$npages=$data['npages'];
			break;

		case 'rssurl':
			$xml_data=false;
			$rssurl=$data['rssurl'];
			break;

		case 'catalogtitlelink':
		case 'iso19139url':
		case 'inspireurl':
			if(substr($data[$element_name],-3)=='%22') $data[$element_name]=substr($data[$element_name],0,-3);
		case 'catalogtitle':
		case 'title':
		case 'abstract':
		case 'urlmdorig':
		case 'wmscapurl':
		case 'mbaddurl':
		case 'georssurl':
			$xml_data=false;
			break;
	}
}

function cdataMeta($parser, $element_inhalt) {
	global $output, $out, $name, $data, $counter, $counter2, $startpage, $count, $page, $items_page, $npages, $rssurl;
	global $xml_begin, $xml_end, $xml_content, $xml_data, $xml_typ, $L, $xml_cat, $xml_max;

	if($xml_begin && $xml_data) {
		$data[$xml_typ].=$element_inhalt;
	}
}


?>