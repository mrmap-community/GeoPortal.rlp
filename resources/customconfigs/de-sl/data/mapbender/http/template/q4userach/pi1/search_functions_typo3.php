<?php
function Info($t3file, $wikifile, $max=0) {
	global $out, $name, $startpage, $L, $allcounter;

	$allcounter=0;

	$t3ready=T3($t3file, $max);
	$metaready=Wiki($wikifile, $max);

	if($startpage && $t3ready==1 && $metaready==1) {
		if($allcounter==0) {
			$out[$name].='<p>'.$L['kein Ergbnis'].'</p>';
		} else {
			$out[$name].='<p>'.$allcounter.' '.$L['Trefferkurz'].'</p>';
		}
	}

	return ($t3ready==1 && $metaready==1)?1:0;
}

function T3($file, $max=0) {
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
				$currentpage=$catpages[0];
								
				$count=$xmlObject->count;
				$allcounter+=$count;
				foreach($xmlObject->content AS $content) {
					if(!$startpage) {

						if($counter>=($currentpage*$items_page) && $counter<(($currentpage+1)*$items_page)) {
							$out['temp'].='
								<div class="search-item">
									<div class="search-nr">'.++$counter.'</div>
			        		<div class="search-title"><a href="'.urldecode($content->link).'">'.urldecode($content->title).'</a></div>
									<div class="search-rating" title="'.urldecode($content->rating).'%"><div style="width:'.(urldecode($content->rating)/20.83).'em"></div></div>
									<div class="search-breadcrumb"><div>'.urldecode($content->breadcrumb).'</div></div>
									<div class="search-text">'.urldecode($content->shorttext).'</div>
								</div>
							';
						} else {
							++$counter;
						}
					}
					$xml_end=true;
				}
				
				if($counter==0 && $xml_end) {
					if(!$startpage) {
						$out['temp'].='<p>'.$L['kein Ergbnis'].'</p>';
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
	
		$url=$L['SuchURL'].'?searchid='.$_REQUEST['searchid'].'&amp;selectsearch='.intval($_REQUEST['selectsearch']).'&amp;uid='.$_REQUEST['uid'];

		$out[$name].='
			<div class="search-cat closed">
				<div class="search-header" onclick="openclose2(this);">
					<h2>Content</h2>
					<p>('.$count.' '.$L['Trefferkurz'].')</p>
				</div>
				'.$out['temp'].'
				'.PageCounter($count, $items_page, $url, $name, 2, 0).'
			</div>';
	}

	return $ready;
}

?>