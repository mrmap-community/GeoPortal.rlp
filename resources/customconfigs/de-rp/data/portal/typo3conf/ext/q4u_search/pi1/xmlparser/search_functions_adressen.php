<?php

function Adressen($file, $max=0) {
	global $output, $out, $name, $data, $counter, $startpage, $count;
	global $xml_begin, $xml_end, $xml_content, $xml_data, $xml_typ, $L, $xml_cat, $xml_max;
	global $check_array;

	$xml_begin=$xml_end=$xml_content=$xml_data=false;
	$xml_max=$max;
	$counter=0;

	$out[$name].='<div id="search-container-'.$name.'">
	';
	if(file_exists($file)) {
		$xmlFile = file($file);

		$parser=xml_parser_create();

		xml_set_element_handler($parser, 'startAdressen', 'endAdressen');
		xml_set_character_data_handler($parser, 'cdataAdressen');

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

function startAdressen($parser, $element_name, $element_attribute) {
	global $output, $out, $name, $data, $counter, $startpage, $count, $page, $items_page;
	global $xml_begin, $xml_end, $xml_content, $xml_data, $xml_typ, $L, $xml_cat, $xml_max;
	global $check_array;

	$element_name = strtolower($element_name);

	switch($element_name) {
		case 'result':
			$xml_begin=true;
			break;
		case 'member':
			$data=array();
			$xml_cat=$element_attribute['ID'];
			$xml_content=true;
			break;
		case 'ready':
			$xml_content=true;
			$xml_data=true;
			$xml_typ=$element_name;
			break;
		case 'title':
			if($xml_content) {
				$xml_typ=$element_name;
				$xml_data=true;
			}
			break;
	}
}

function endAdressen($parser, $element_name) {
	global $output, $out, $name, $data, $counter, $startpage, $count, $page, $items_page;
	global $xml_begin, $xml_end, $xml_content, $xml_data, $xml_typ, $L, $xml_cat, $xml_max;
	global $check_array;

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
		case 'member':
			$xml_content=false;
			if($startpage) {
				if($counter<$xml_max) {
					$out[$name].='
						<div class="search-item">
							<div class="search-nr">'.++$counter.'</div>
        			<div class="search-title all"><a href="karten.html?geomuid='.$_REQUEST['uid'].'&geomid='.$xml_cat.'">'.$data['title'].'</a></div>
						</div>
					';
				}
			} else {
				$out[$name].='
					<div class="search-item">
						<div class="search-nr">'.++$counter.'</div>
      			<div class="search-title all"><a href="karten.html?geomuid='.$_REQUEST['uid'].'&geomid='.$xml_cat.'">'.$data['title'].'</a></div>
					</div>
				';
			}
			$xml_content=false;
			break;
		case 'title':
			$xml_data=false;
			break;
	}
}

function cdataAdressen($parser, $element_inhalt) {
	global $output, $out, $name, $data, $counter, $startpage, $count, $page, $items_page;
	global $xml_begin, $xml_end, $xml_content, $xml_data, $xml_typ, $L, $xml_cat, $xml_max;

	if($xml_begin && $xml_data) {
		$data[$xml_typ].=$element_inhalt;
	}
}

?>