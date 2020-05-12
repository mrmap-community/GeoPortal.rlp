<?php

function Wiki($file, $max=0) {
	global $out, $name, $startpage, $L, $page, $items_page, $allcounter;
	
	$counter=0;
	$ready=0;
	$xml_end=false;
	$out['temp']='';
	
	if(file_exists($file) && filesize($file)>0) {
		try {
			$xmlObject = @simplexml_load_file($file);
			if($xmlObject) {

				$catpages=explode('|',$page);
				for($i=0;$i<count($interfaces);$i++) {
					$catpages[$i]=intval($catpages[$i]);
				}
				$currentpage=$catpages[1];
				$allcounter+=count($xmlObject->member);
				if ($xmlObject->ready == 'true') {
					$xml_end=true;
					#$ready=1;
				}
				foreach($xmlObject->member AS $member) {
					if(!$startpage) {

						if($counter>=($currentpage*$items_page) && $counter<(($currentpage+1)*$items_page)) {

							$out['temp'].='
								<div class="search-item">
									<div class="search-nr">'.++$counter.'</div>
			      			<div class="search-title"><a href="http://'.$_SERVER['HTTP_HOST'].'/'.$member->url.'" target="_blank">'.$member->title.'</a></div>
			      			<div class="search-text">'.textcut2($member->abstract,200).'</div>
								</div>
							';
						} else {
							++$counter;
						}
					} else {
						++$counter;
					}
					//$xml_end=true; //is set before!
				}
				if($counter==0 && $xml_end) {
					if(!$startpage) {
						$out['temp'].='<div class="search-item">'.$L['kein Ergbnis'].'</div>';
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

	if(!$startpage) {

		$url=$L['SuchURL'].'?searchid='.intval($_REQUEST['searchid']).'&amp;selectsearch='.intval($_REQUEST['selectsearch']).'&amp;uid='.PtH($_REQUEST['uid']);

		$out[$name].='
			<div class="search-cat closed">
				<div class="search-header" onclick="openclose2(this);">
					<h2>Wiki</h2>
					<p>('.$counter.' '.$L['Trefferkurz'].')</p>
				</div>
				'.$out['temp'].'
				'.PageCounter($counter, $items_page, $url, $name, 2, 1).'
			</div>';
	}
	return $ready;
}

?>
