<?php
function Adressen($file, $max=0) {
	global $out, $name, $startpage, $L, $page, $items_page, $AdrCat, $AdrCatCount;
	
	$counter=0;
	$ready=0;

	if(file_exists($file) && filesize($file)>0) {
		try {
			$xmlObject = @simplexml_load_file($file);
			if($xmlObject) {
				foreach($xmlObject->member AS $member) {
					$memberclass="";
					
					if($_REQUEST["adrcat"]!='' && strpos($member->attributes()->id,$_REQUEST["adrcat"])!==0) continue;

					foreach($AdrCat as $key=>$Class) {
						if(strpos($member->attributes()->id,$key)===0) {
							$memberclass=$key;
							$AdrCatCount[$key]++;
							break;
						}
					}
					
					
					if(!$startpage) {
						if($counter>=($page*$items_page) && $counter<(($page+1)*$items_page)) {
							$out[$name].='
								<div class="search-item">
									<div class="search-nr">'.++$counter.'</div>
			      			<div class="search-title all"><a href="karten.html?geomuid='.PtH($_REQUEST['uid']).'&geomid='.$member->attributes()->id.'" alt="'.$Classes[$memberclass].'" title="'.$Classes[$memberclass].'">'.$member->FeatureCollection->featureMember->children()->children()->title.'</a></div>
								</div>
							';
						} else {
							++$counter;
						}
					} else {
						++$counter;
					}
				}
				$xml_end=($xmlObject->ready=="true");
				
				if($counter==0 && $xml_end) {
					if(!$startpage) {
						$out[$name].='<p>'.$L['kein Ergbnis'].'</p>';
					}
					$ready=1; // Suche abgeschlossen
				} elseif( (!$xml_end && !$startpage) || (!$xml_end && $startpage && $counter<$max) ) {
					$ready=0; // Suche noch nicht abgeschlossen
				} else {
					$ready=1; // Suche abgeschlossen
				}
			}

		} catch(Exception $e){};  // Bei nicht korrekter XML-Syntax oder nicht vollständigem XML-File

	} else {
		$ready=0; // Suche noch nicht abgeschlossen Datei nicht gefunden
	}
	
	$url=$L['SuchURL'].'?searchid='.intval($_REQUEST['searchid']).'&amp;selectsearch='.intval($_REQUEST['selectsearch']).'&amp;uid='.PtH($_REQUEST['uid']).'&amp;adrcat='.PtH($_REQUEST['adrcat']);

	if(!$startpage && $counter>0) $out[$name].=PageCounter($counter, $items_page, $url, $name);
	
	if($startpage && $ready==1) {
		if($counter==0) {
			$out[$name].='<p>'.$L['kein Ergbnis'].'</p>';
		} else {
			$out[$name].='<p>'.$counter.' '.$L['Trefferkurz'].'</p>';
		}
	}

	return $ready;
}
?>