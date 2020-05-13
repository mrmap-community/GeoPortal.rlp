<?php

function Info($file, $max=0) {
	global $output, $out, $name, $data, $counter, $startpage, $count;
	global $xml_begin, $xml_end, $xml_content, $xml_data, $xml_typ, $L;

	$xml_begin=$xml_end=$xml_content=$xml_data=false;
	$counter=0;

	$out[$name].='<div id="search-container-'.$name.'">
	';
	if(file_exists($file)) {
		$xmlFile = file($file);
		$parser = xml_parser_create();
		xml_set_element_handler($parser, 'startInfo', 'endInfo');
		xml_set_character_data_handler($parser, 'cdataInfo');

		foreach($xmlFile as $elem) {
			xml_parse($parser, $elem);
			if($max!=0 && $counter>=$max) break;
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
		$ready=0; // Suche noch nicht abgeschlossen Datei nicht gefunden
	}
	$out[$name].='</div>
	';
	return $ready;
}


function startInfo($parser, $element_name, $element_attribute) {
	global $output, $data;
	global $xml_begin, $xml_end, $xml_content, $xml_data, $xml_typ;

	$element_name = strtolower($element_name);

	switch($element_name) {
		case 'searchresult':
			$xml_begin=true;
			break;
		case 'content':
			$xml_content=true;
			break;
		case 'count':
			if($xml_begin) {
				$xml_typ=$element_name;
				$xml_data=true;
			}
			break;
		case 'typ':
		case 'uid':
		case 'title':
		case 'link':
		case 'breadcrumb':
		case 'shorttext':
		case 'datetime':
		case 'rating':
			if($xml_content) {
				$xml_typ=$element_name;
				$xml_data=true;
			}
			break;
	}
}

function endInfo($parser, $element_name) {
	global $output, $out, $name, $data, $counter, $startpage, $count, $page, $items_page;
	global $xml_begin, $xml_end, $xml_content, $xml_data, $xml_typ, $L;

	$element_name = strtolower($element_name);

	switch($element_name) {
		case 'searchresult':
			$xml_begin=false;
			$xml_end=true;
			break;
		case 'content':
			if($startpage) {
				$out[$name].='
					<div class="search-item">
						<div class="search-nr">'.++$counter.'</div>
						<div class="search-title all"><a href="'.$data['link'].'">'.$data['title'].'</a></div>
					</div>
				';
			} else {
				if($counter>=($page*$items_page) && $counter<(($page+1)*$items_page)) {
					$out[$name].='
						<div class="search-item">
							<div class="search-nr">'.++$counter.'</div>
	        		<div class="search-title"><a href="'.$data['link'].'">'.$data['title'].'</a></div>
							<div class="search-rating" title="'.$data['rating'].'%"><div style="width:'.($data['rating']/20.83).'em"></div></div>
							<div class="search-breadcrumb"><div>'.$data['breadcrumb'].'</div></div>
							<div class="search-text">'.$data['shorttext'].'</div>
						</div>
					';
				} else {
					++$counter;
				}
			}
			$xml_content=false;
			break;
		case 'count':
		if(!$startpage) {
			$count=$data['count'];
		}
			$xml_data=false;
			break;
		case 'typ':
		case 'uid':
		case 'title':
		case 'link':
		case 'breadcrumb':
		case 'shorttext':
		case 'datetime':
		case 'rating':
			$xml_data=false;
			break;
	}
}

function cdataInfo($parser, $element_inhalt) {
	global $output, $data;
	global $xml_begin, $xml_end, $xml_content, $xml_data, $xml_typ;

	if($xml_begin && $xml_data) {
		$data[$xml_typ]=urldecode($element_inhalt);
	}
}

?>