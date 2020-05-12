<?php
function Meta($file, $max=0) {
	global $out, $name, $startpage, $page, $L, $unique;
	global $LinkURL;

	$counter=$meta_count=0;
	$ready=0;
	$allcounter=0;

	$allready=0;
	$interfaces=array();
	if(file_exists($file) && filesize($file)>0) {
		$allready=1;
		try {
			$xmlObject = @simplexml_load_file($file);
			if($xmlObject) {
				foreach($xmlObject->opensearchinterface AS $interface) {
					$interfaces[]=trim($interface);
				}
			}

			$catpages=explode('|',$page);
			for($i=0;$i<count($interfaces);$i++) {
				$catpages[$i]=intval($catpages[$i]);
			}

			$url=$LinkURL.'?searchid='.$_REQUEST['searchid'].'&amp;selectsearch='.intval($_REQUEST['selectsearch']).'&amp;uid='.$_REQUEST['uid'];

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

				if(!file_exists($filename) || filesize($filename)==0 && $currentpage!=0 && !$in_ajax) {
					$parts=split('\.',$file);
					$filename1=$parts[0].($key+1).'_1.'.$parts[1];
					if(file_exists($filename1) && filesize($filename1)>0) {
						try {
							$xmlData = @simplexml_load_file($filename1);
							if($xmlData) {
								$counter2=$xmlData->totalresults;
								$npages=$xmlData->npages;
								$query=$xmlData->querystring;
							}
						} catch(Exception $e){};  // Bei nicht korrekter XML-Syntax oder nicht vollständigem XML-File
					}
					if($currentpage<$npages) {
						file_get_contents('http://localhost/mapbender/geoportal/mod_readOpenSearchResults.php?q='.$query.'&p='.($currentpage+1).'&cat='.$key.'&request_id='.$unique);
					}
				}

				if(file_exists($filename) && filesize($filename)>0) {
					try {
						$xmlData = @simplexml_load_file($filename);
						if($xmlData) {
							$xml_end=true;

							$counter2=$xmlData->totalresults;
							$npages=$xmlData->npages;
							$rssurl=$xmlData->rssurl;

							foreach($xmlData->result as $result) {
								$inspireurl=(substr($result->inspireurl,-3)=='%22')?substr($result->inspireurl,0,-3):$result->inspireurl;

								$counter++;

								if(!$startpage) {
									$out['temp'].='
										<div class="search-item">';

					       	if($result->wmscapurl!='') {
										$out['temp'].='
											<div class="search-mapicons">
												<a href="'.$L['KarteURL'].'?WMS='.$result->wmscapurl.'"><img src="fileadmin/design/icn_map.png" title="Auf Karte anzeigen" /></a>
											</div>';
									} elseif($result->georssurl!='') {
										$out['temp'].='
											<div class="search-mapicons">
												<a href="'.$L['KarteURL'].'?GEORSS='.$result->georssurl.'"><img src="fileadmin/design/icn_map_rss.png" title="Auf Karte anzeigen" /></a>
											</div>';
									}

									$out['temp'].='
											<div class="search-title">
												<a target="_blank" href="/mapbender/geoportal/'.urldecode($result->catalogtitlelink).'" onclick="openwindow(this.href); return false">'.urldecode($result->title).'</a>
											</div>
											<div class="search-metacat">
												'.urldecode($result->catalogtitle).'
											</div>';
					#       	$out['temp'].='<div class="search-metacat">'.urldecode($result->categorie).'</div>\n';
					       	$out['temp'].='
					       			<div class="search-text">
					       				'.urldecode($result->abstract).'
					       			</div>
					       	';
					       	if($result->urlmdorig!='') {
						       	$out['temp'].='
						       		<div class="search-metacat">
						       			<a target="_blank" href="'.urldecode($result->urlmdorig).'" onclick="openwindow(this.href); return false">Originäre Metadaten</a>
						       		</div>';
					       	}
					       	if($result->wmscapurl!='') {
						       	$out['temp'].='
						       		<div class="search-metacat">
						       			<a target="_blank" href="'.urldecode($result->wmscapurl).'" onclick="openwindow(this.href); return false">WMS GetCapabilities</a>
						       		</div>';
					       	}
					       	if($result->iso19139url!='' || $result->inspireurl!='') {
						       	$out['temp'].='
						       		<div class="search-text">
						       			<strong>Alternative Formate:</strong><br />';
						       	if($result->iso19139url!='') {
							       	$out['temp'].='
							       		<a target="_blank" href="/mapbender/geoportal/'.urldecode($result->iso19139url).'" onclick="openwindow(this.href); return false"><img src="fileadmin/design/icn_iso19139.png" title="ISO19139" /></a>';
							      }
						       	if($result->inspireurl!='') {
							       	$out['temp'].='
							       		<a target="_blank" href="/mapbender/geoportal/'.urldecode($result->inspireurl).'" onclick="openwindow(this.href); return false"><img src="fileadmin/design/icn_inspire.png" title="INSPIRE" /></a>';
							      }
						       	$out['temp'].='
						       		</div>';
					       	}
									$out['temp'].='
										</div>';
								}


							}

						}
					} catch(Exception $e){};  // Bei nicht korrekter XML-Syntax oder nicht vollständigem XML-File

					if( (!$xml_end && !$startpage) || (!$xml_end && $startpage && $counter<$max) ) {
						$allready=0; // Suche noch nicht abgeschlossen
					}
				} else {
					if( !$startpage || ($startpage && $counter<$max) ) {
						$allready=0; // Suche noch nicht abgeschlossen
					}
				}

				if($npages==0 && $currentpage!=0) { // Anzahl der Treffer und Seiten aus dem Datensatz der 1. Seite lesen
					$parts=split('\.',$file);
					$filename1=$parts[0].($key+1).'_1.'.$parts[1];
					if(file_exists($filename1) && filesize($filename1)>0) {
						try {
							$xmlData = @simplexml_load_file($filename1);
							if($xmlData) {
								$counter2=$xmlData->totalresults;
								$npages=$xmlData->npages;
								$query=$xmlData->querystring;
							}
						} catch(Exception $e){};  // Bei nicht korrekter XML-Syntax oder nicht vollständigem XML-File
					}
				}

				if(!$startpage) {
/*
					Informationen im Cookie hinterlegt
					$out[$name].='
						<div class="search-cat meta-search-container'.$meta_count.'">
							<div class="search-header" onclick="openclose(\''.$meta_count++.'\');">
								<h2>'.$xml_cat.'</h2>
								<p>'.$counter2.' '.$L['Trefferkurz'].'</p>
							</div>';
*/
					$out[$name].='
						<div class="search-cat '.((isset($_REQUEST['pos']) && $_REQUEST['pos']==$key)?'opened':'closed').'">
							<div class="search-header" onclick="openclose2(this);">
								<h2>'.$xml_cat.'</h2>
								<p>('.$counter2.' '.$L['Trefferkurz'].')</p>
							</div>';
					if($rssurl!='') {
						$out[$name].='
							<div class="search-item">
								<div class="search-text">
									<a href="'.$L['KarteURL'].'?GEORSS='.$rssurl.'"><img src="fileadmin/design/icn_map_rss.png"  title="Auf Karte anzeigen" /></a>
								</div>
							</div>';
					}

					$out[$name].=$out['temp'];

					$out[$name].=PageCounter($counter2, -$npages, $url, $name, count($interfaces), $key);

					$out[$name].='
						</div>';
				}
				$allcounter+=$counter2;
			}

			if($counter==0 && $allready==1) {
				$out[$name].='<p>'.$L['kein Ergbnis'].'</p>';
			}
		} catch(Exception $e){};  // Bei nicht korrekter XML-Syntax oder nicht vollständigem XML-File
	} else {
		$ready=0; // Suche noch nicht abgeschlossen Datei nicht gefunden
	}

	if($startpage && $allready==1) {
		if($allcounter==0) {
			$out[$name].='<p>'.$L['kein Ergbnis'].'</p>';
		} else {
			$out[$name].='<p>'.$allcounter.' '.$L['Trefferkurz'].'</p>';
		}
	}

	return $allready;
}
?>
