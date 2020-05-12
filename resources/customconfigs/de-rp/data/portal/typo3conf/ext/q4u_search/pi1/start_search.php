<?php
require_once("/data/mapbender/core/globalSettings.php");
require_once("/data/mapbender/http/classes/class_administration.php");
require_once("/data/mapbender/http/classes/class_connector.php");
global $admin;
$admin = new administration();

include_once(dirname(__FILE__).'/../../../../fileadmin/function/config.php');
include_once(dirname(__FILE__).'/../../../../fileadmin/function/util.php');
include_once(dirname(__FILE__).'/../../../../fileadmin/function/function.php');
include_once(dirname(__FILE__).'/../../../../fileadmin/function/crypt.php');

GLOBAL $output, $out, $name, $data, $counter, $unique, $startpage, $count, $page, $items_page, $in_ajax;
GLOBAL $GUESTID, $Language, $Lang, $L;
GLOBAL $check_array, $unique;
global $LinkURL;

include_once('search_functions.php');

$_REQUEST['uid']=($_REQUEST['uid']=='')?CreateUUID():$_REQUEST['uid'];

$NewSearch=false;

if($_REQUEST['searchfilter']!='') {
	parse_str($_REQUEST['searchfilter'],$URLParts);
	unset($URLParts['searchId']);
	$URLParts['hostName']=$_SERVER['HTTP_HOST'];
	//Test if searchResources are given if not initialize them
	if (!isset($URLParts['searchResources']) || $URLParts['searchResources'] =='') {
		$_REQUEST['searchResources'] = implode(',',$searchResources);
		$_REQUEST['searchfilter'] .= "&searchResources=".$_REQUEST['searchResources'];
	}
	if (in_array("dataset", $searchResources)) {
		$_REQUEST['searchfilter'] .= "&resolveCoupledResources=true";
	}
	$_REQUEST['searchfilter']=http_build_query($URLParts);
	$_REQUEST['searchtext']=str_replace(',',' ',$URLParts['searchText']);
	$_REQUEST['act']='search';
	$_REQUEST['selectsearch']=0;
	foreach($URLParts as $key=>$value) {
		$_REQUEST[$key]=$value;
	}
	$_REQUEST['searchText']=str_replace(',',' ',$URLParts['searchText']);
}

if($_REQUEST['act']=='change') $NewSearch=SearchChange();
$unique=$_REQUEST['uid'];

$searchtext=$_REQUEST['searchtext']=SearchText();

if($searchtext == ''){
	$searchtext ='*';
}

if($_REQUEST['searchid']!='') $searchtext=$_REQUEST['searchtext']=addslashes($searchtext);

$cat=$_REQUEST['cat'];
//$e = new mb_exception("start_search.php: cat: ".$cat);
$items_page=10;
$page=$_REQUEST['page'];
if(!$page) { $_REQUEST['page']=$page=0; }

$in_ajax=$ajax;
if(!$ajax) {
    if(t3lib_div::GPvar('L')==1) {
        $Lang='en';
    } else {
        $Lang='de';
    }
    $L=$Language[$Lang];
    $_SESSION["mb_user_spatial_suggest"] = ($_REQUEST['spatial']=='ja')?'ja':'nein';
}

if (!$ajax && $_REQUEST['spatial']=='ja') {
    $searchparts = preg_split('/( )+/', $searchtext);
    if(count($searchparts)>0) {
        foreach($searchparts as $key=>$part) {
            if(mb_strtolower($part, 'UTF-8')=='in') continue;
            $params = array(
                'outputFormat' => 'json',
                'resultTarget' => 'web',
                'searchEPSG' => 4326, //geographische Koordinaten
                'maxResults' => 15,
                'maxRows' => 15,
                'searchText' => $part,
            );
            try{
                $data[$key]=(array)json_decode(file_get_contents('http://localhost/mapbender/geoportal/gaz_geom_mobile.php?'.http_build_query($params)));
            }catch(Exception $e) {
            }
        }
        $vorschlag=array();
        foreach($searchparts as $i=>$part) {
            if(count($data[$i][geonames])>0) {
                $option='';
                foreach($searchparts as $k=>$wort) {
                    if(mb_strtolower($wort, 'UTF-8')=='in') continue;
                    if($i!=$k) {
                        $option.=' '.$wort;
                    }
                }
                $option=trim($option);
                foreach($data[$i][geonames] as $ort) {
                    $vorschlag[]=array(
                        'title' => '<i>'.((empty($option))?$L['allData']:$option).'</i> in <b>'.$ort->title.'</b>',
                        'size' => ($ort->maxx-$ort->minx) * ($ort->maxy-$ort->miny),
                        'searchtext' => $option,
                        'bbox' => urlencode(implode(',', array(
                            $ort->minx,
                            $ort->miny,
                            $ort->maxx,
                            $ort->maxy,
                        )))
                    );
                }
            }
        }
        usort($vorschlag,'sortBySize');

        $LinkURL=$L['SuchURL'];
        $LinkURLErw=$L['ErwSuchURL'];
        $output='<h2>'.$L['SearchSuggestion'].'</h2>';

        if(count($vorschlag)>0) {
            $output.='<ul class="searchsuggestion">';
            foreach($vorschlag as $tip) {
                $output.='<li><a href="'.$LinkURLErw.'?searchText='.$tip['searchtext'].'&searchBbox='.$tip['bbox'].'&searchTypeBbox=intersects">'.$tip['title'].'</a></li>';
            }
            $output.='</ul>';
        } else {
            $output.='<p>'.$L['noSearchSuggestion'].'</p>';
            $output.='<p><a href="'.$LinkURL.'?searchtext='.urlencode($searchtext).'&act=search">'.$searchtext.'</a></p>';
        }
    }

} elseif(!$ajax) { // Normale Suche - ohne suggest!
	$filterfile = '/data/mapbender/http/tmp/'.md5($unique).'_filter.json';
	$dienstefile = '/data/mapbender/http/tmp/'.md5($unique).'###SERVICE###.json';
	$adressfile = '/data/mapbender/http/tmp/'.$unique.'_geom.xml';
	$typo3file = dirname(__FILE__).'/temp/'.$unique.'.typo3.xml';
	$metafile = '/data/mapbender/http/tmp/'.$unique.'_os.xml';
	$wikifile = '/data/mapbender/http/tmp/'.$unique.'_wiki.xml';

/*
	$filterfile = dirname(__FILE__).'/temp/627bff9db837eb099b64f9dcde83f270_filter.json';
	$dienstefile = dirname(__FILE__).'/temp/627bff9db837eb099b64f9dcde83f270###SERVICE###.json';
	$adressfile = dirname(__FILE__).'/temp/__.geom.xml';
	$typo3file = dirname(__FILE__).'/temp/'.$unique.'.typo3.xml';
	$metafile = dirname(__FILE__).'/temp/__os.xml';
	$wikifile = dirname(__FILE__).'/temp/__.wiki.xml';
*/
	if(t3lib_div::GPvar('L')==1) {
		$Lang='en';
	} else {
		$Lang='de';
	}
	$L=$Language[$Lang];
	$LinkURL=$L['SuchURL'];

	if (($_REQUEST['act']=='search' && $searchtext!='') || ($NewSearch && $searchtext!='')) {
		if($_REQUEST['searchfilter']=='') {
			$_REQUEST['searchfilter']='searchText='.str_replace(' ',',',$searchtext).'&languageCode='.$Lang.'&searchEPSG='.$_SESSION['epsg'].'&userId='.$_SESSION['mb_user_id'].'&resultTarget=file&searchResources='.implode(",",$searchResources).'&outputFormat=json&hostName='.$_SERVER['HTTP_HOST'];
			if (in_array("dataset", $searchResources)) {
				$_REQUEST['searchfilter'] .= "&resolveCoupledResources=true";
			}
		}
		// Scriptparameter
		$shplus='';
		foreach(array(
		          'registratingDepartments',
		          'isoCategories',
		          'regTimeBegin',
		          'regTimeEnd',
		          'searchBbox',
		          'searchTypeBbox',
		          'searchResources',
		          'timeBegin',
		          'timeEnd',
		         ) as $key) {
			$shplus.='"'.(($_REQUEST[$key]!='')?$_REQUEST[$key]:'false').'" ';
		}
		//$e = new mb_exception("start_search.php: start distributed search from shell!");
		exec('php5 '.dirname(__FILE__).'/search.php "'.$unique.'" "'.$searchtext.'" "'.(int)t3lib_div::GPvar('L').'" "0" >/dev/null 2>&1 &');
#		exec("php5 ".dirname(__FILE__)."/search.php \"".$unique."\" \"".$searchtext."\" \"".(int)t3lib_div::GPvar('L')."\" \"1\" >/dev/null 2>&1 &");
		exec('php5 /data/mapbender/http/geoportal/gaz.php "'.$_SESSION['mb_user_id'].'" "'.$unique.'" "'.$searchtext.'" "'.$_SESSION['epsg'].'" '.$shplus.' >/dev/null 2>&1 &');
#		exec("php5 /data/mapbender/http/geoportal/gaz.php \"".$_SESSION["mb_user_id"]."\" \"".$unique."\" \"".$searchtext."\" \"".$_SESSION["epsg"]."\" \"".$_SESSION["SID"]."\">/dev/null 2>&1 &");
#		print "---"."php5 /data/mapbender/http/geoportal/gaz.php \"2\" \"".$unique."\" \"".$searchtext."\" >/dev/null 2>&1 &"."---";
		//file_get_contents('http://localhost/mapbender/php/mod_callMetadata.php?searchId='.$unique.'&'.$_REQUEST['searchfilter']);
		$connector = new connector();
		$connector->set("timeOut", "1");
		//$e = new mb_exception("start_search.php: start metadata search from shell with filter: searchId=".$unique."&".$_REQUEST['searchfilter']);
		$connector->load('http://localhost/mapbender/php/mod_callMetadata.php?searchId='.$unique.'&'.$_REQUEST['searchfilter']);
// Abspeichern als letzte Suche ä
		//$_REQUEST['searchid']=WriteLastSearch();

// Warten nach der ersten Suche
		$microsPerSecond = 250000;
		usleep(($microsPerSecond/2));
	}

	if($_REQUEST['act']=='save' && $_SESSION['mb_user_id']!=$GUESTID) {
		SearchSave();
	}
	if($_REQUEST['act']=='delete' && $_REQUEST['ok']=='1' && $_SESSION['mb_user_id']!=$GUESTID) {
		$_REQUEST['searchid']=SearchDelete();
	}

	$timer=false;

	$url=SearchURL();
	
	$name="filter";
	$ready[$name]=Filter($filterfile);
	if($ready[$name]==0) {
		$timer=true;
	}
	$output.='<div class="search-filter">
	'.$out[$name].'
	<div class="clr"></div>
	</div>';
			
	$output.='<ul class="search-cat">';

// Gesamt
	$output.=($cat=='')?'<li class="active">':'<li>';
	$output.='<a href="'.$url.'">'.$L['Gesamt'].'</a></li>';

// Adressen
	$content=$i='';
	$count=0;
	if(file_exists($adressfile)) {
		$content=file_get_contents($adressfile);
		$count=substr_count($content,'<member ');
		$i=$count;
	}
	$i='<span id="search-count-adressen">'.$i.'</span>';
	if(strpos($content,'<ready>true</ready>')===false && $searchtext!='') {
		$i.='<span id="search-indicator-adressen">'.$indicator.'</span>';
		$timer=true;
	}
	$output.=($cat=='adressen')?'<li class="active">':'<li>';
	$output.='<a href="'.$url.'&amp;cat=adressen">'.$L['Adressen'].$i.'</a></li>';

// Dienste
	$content=$i='';
	$Counter=CountDienste($dienstefile);
	$i=$Counter['count'];
	if($Counter['ready']==0 && $searchtext!='') {
		$i='<span id="search-count-dienste"></span><span id="search-indicator-dienste">'.$indicator.'</span>';
		$timer=true;
	} else {
		$i='<span id="search-count-dienste">'.$i.'</span>';
	}
	$output.=($cat=='dienste')?'<li class="active">':'<li>';
	$output.='<a href="'.$url.'&amp;cat=dienste">'.$L['Dienste'].$i.'</a></li>';

// Info
	$wcontent=$content=$i='';
	$count=0;
	//$e = new mb_exception("pi1/start_search.php: ".$typo3file);
	if(file_exists($typo3file)) {
		$content=file_get_contents($typo3file);
		$count=substr_count($content,'<content>');
		$i=$count;
	}
	//$e = new mb_exception("pi1/start_search.php: ".$typo3file);
	if(file_exists($wikifile)) {
		$wcontent=file_get_contents($wikifile);
		$count=substr_count($wcontent,'<member>');
		$i+=$count;
	}
	$i='<span id="search-count-info">'.$i.'</span>';
	if(strpos($content,'</searchresult>')===false && strpos($wcontent,'<ready>true</ready>')===false && $searchtext!='') {
		$i.='<span id="search-indicator-info">'.$indicator.'</span>';
		$timer=true;
	}
	$output.=($cat=='info')?'<li class="active">':'<li>';
	$output.='<a href="'.$url.'&amp;cat=info">'.$L['Info'].$i.'</a></li>';

// Meta
	$i='';
	$count=$count_meta=0;

	if($searchtext!='') {
		$allready=0;
		if(file_exists($metafile) && filesize($metafile)>0) {
			$indexfile = file($metafile);
			$allready=1;
			$key=0;
			foreach($indexfile as $folder) {
				if(preg_match('/<opensearchinterface>(.+)<\/opensearchinterface>/',$folder,$matches)==0 || trim($matches[1])=='') continue;
				$key++;
				$count_meta++;
				$parts=split('\.',$metafile);
				$filename='';
				for($j=0;$j<count($parts)-1;$j++) {
					if($j>0) $filename.='.';
					$filename.=$parts[$j];
				}
				$filename.=$key.'_1.'.$parts[$j];
				if(file_exists($filename)) {
					$content=file_get_contents($filename);
					if(preg_match('/<totalresults>(.+)<\/totalresults>/',$content,$matches)!=0)
						$count+=$matches[1];
					if(strpos($content,'</resultlist>')===false) {
						$allready=0;
					}
				} else {
					$allready=0;
				}
			}
		}
	} else {
		$allready=1;
	}
	if($count!=0) $i=$count;
	$i='<span id="search-count-meta">'.$i.'</span>';
	if($allready==0) {
		$i.='<span id="search-indicator-meta">'.$indicator.'</span>';
		$timer=true;
	}
	$output.=($cat=='meta')?'<li class="active">':'<li>';
	$output.='<a href="'.$url.'&amp;cat=meta">'.$L['Metadaten'].$i.'</a></li>
	         ';

	$output.='</ul>';

	$output.='<div class="search-container">
	         ';
	if($searchtext=='') { // Kein Suchbegriff eingetragen
		$output.='<p>'.$L['kein Ergbnis'].'</p></div>
		          <br class="clr" />
		         ';
		return;
	}

	switch($cat) {
		case '':
			$startpage=true;

// Adressen
			$name='adressen';
			$ready[$name]=Adressen($adressfile,5);
			if($ready[$name]==0) {
				$i='<span id="search-header-indicator-adressen">'.$indicator.'</span>';
				$timer=true;
			} else {
				$i='';
			}
			$output.='<div class="search-block">
				<img class="icon" src="fileadmin/design/s_ortssuche.png">
				<h2><a href="'.$url.'&amp;cat=adressen" title="'.str_replace('###AREA###',$L['Adressen'],$L['alleErgebnisse']).'">'.$i.''.$L['Adressen'].'</a></h2>
				<div id="search-container-'.$name.'">
				'.$out[$name].'
				</div>
				<div class="clr"></div></div>';

// Dienste
			$name='dienste';
			$ready[$name]=Dienste($dienstefile,5);
			if($ready[$name]==0) {
				$i='<span id="search-header-indicator-dienste">'.$indicator.'</span>';
				$timer=true;
			} else {
				$i='';
			}
			$output.='<div class="search-block">
				<img class="icon" src="fileadmin/design/s_interaktivedaten.png">
				<h2><a href="'.$url.'&amp;cat=dienste" title="'.str_replace('###AREA###',$L['Dienste'],$L['alleErgebnisse']).'">'.$i.''.$L['Dienste'].'</a></h2>
				<div id="search-container-'.$name.'">
				'.$out[$name].'
				</div>
				<div class="clr"></div></div>';

// Info
			$name='info';
			$ready[$name]=Info($typo3file,$wikifile,5);
			if($ready[$name]==0) {
				$i='<span id="search-header-indicator-info">'.$indicator.'</span>';
				$timer=true;
			} else {
				$i='';
			}
			$output.='<div class="search-block">
				<img class="icon" src="fileadmin/design/s_info.png">
				<h2><a href="'.$url.'&amp;cat=info" title="'.str_replace('###AREA###',$L['Info'],$L['alleErgebnisse']).'">'.$i.''.$L['Info'].'</a></h2>
				<div id="search-container-'.$name.'">
				'.$out[$name].'
				</div>
				<div class="clr"></div></div>';

// Metadaten
			$name='meta';
			$ready[$name]=Meta($metafile,5);
			if($ready[$name]==0) {
				$i='<span id="search-header-indicator-meta">'.$indicator.'</span>';
				$timer=true;
			} else {
				$i='';
			}
			$output.='<div class="search-block">
				<img class="icon" src="fileadmin/design/s_metadaten.png">
				<h2><a href="'.$url.'&amp;cat=meta" title="'.str_replace('###AREA###',$L['Metadaten'],$L['alleErgebnisse']).'">'.$i.''.$L['Metadaten'].'</a></h2>
				<div id="search-container-'.$name.'">
				'.$out[$name].'
				</div>
				<div class="clr"></div></div>';

			break;
		case 'dienste':
			$startpage=false;

			$name='dienste';
			$ready[$name]=Dienste($dienstefile);
			if($ready[$name]==0) $timer=true;
			$output.='
				<div id="search-container-'.$name.'">
				'.$out[$name].'
				</div>';

			break;
		case 'adressen':
			$startpage=false;

			$name='adressen';
			$ready[$name]=Adressen($adressfile);
			if($ready[$name]==0) $timer=true;
			$output.='
				<div id="search-container-'.$name.'">
				'.$out[$name].'
				</div>';

			break;
		case 'info':
			$startpage=false;

			$name='info';
			$ready[$name]=Info($typo3file,$wikifile);
			if($ready[$name]==0) $timer=true;
			$output.='
				<div id="search-container-'.$name.'">
				'.$out[$name].'
				</div>';
			break;

		case 'meta':
			$startpage=false;

			$name='meta';
			$ready[$name]=Meta($metafile);
			if($ready[$name]==0) $timer=true;

			for($i=0;$i<$count_meta;$i++) {
				$output.='<div id="metastyles'.$i.'" style="display:none">
									<style type="text/css">
									  .meta-search-container'.$i.' .search-header{background:url(fileadmin/design/search-header-plus.png) top right no-repeat;cursor:pointer;padding-right:25px !important}
									  .meta-search-container'.$i.' .search-item{display:none}
									  .meta-search-container'.$i.' .search-pagecounter-container{display:none}
									</style>
									</div>
									';
			}

			$output.='
				<div id="search-container-'.$name.'">
				'.$out[$name].'
				</div>';
			break;
	}
	$output.='</div>
	          <br class="clr" />
						<div id="searchresults"></div>
					';

	if($cat=='meta') {
		$jsoutput='
			window.onload=start;
			function start() {
				opencat=get_cookie("opencat");
				if(opencat!=null) {
					cats=opencat.split("|");
					for(i=0;i<cats.length;i++) {
						if(cats[i]=="open") openclose(i.toString());
					}
				}
			}
		';		
	} else {
		$jsoutput='';
	}

	$SCRIPTDIR='/portal';

	$output.='
		<script type="text/javascript">

		var cat=new Array();
		'.$jsoutput.'
		function openclose(id) {
			if(typeof cat[id]=="undefined" || cat[id]=="close") {
				cat[id]="open";
				var cssStr = ".meta-search-container"+id+" .search-header{background:url(fileadmin/design/search-header-minus.png) top right no-repeat;cursor:pointer;padding-right:25px !important} .meta-search-container"+id+" .search-item{display:block} .meta-search-container"+id+" .search-pagecounter-container{display:block}";
			} else {
				cat[id]="close";
				var cssStr = ".meta-search-container"+id+" .search-header{background:url(fileadmin/design/search-header-plus.png) top right no-repeat;cursor:pointer;padding-right:25px !important} .meta-search-container"+id+" .search-item{display:none} .meta-search-container"+id+" .search-pagecounter-container{display:none}";
			}
			set_cookie("opencat",cat.join("|"));
			var style = document.createElement("style");
			style.setAttribute("type", "text/css");

			if(style.styleSheet) { // IE
				style.styleSheet.cssText = cssStr;
			} else { // w3c
				var cssText = document.createTextNode(cssStr);
				style.appendChild(cssText);
			}

			document.getElementById("metastyles"+id).innerHTML="";
			document.getElementById("metastyles"+id).appendChild(style);
		}

		function openclose2(e) {
			ziel=e.parentNode;
			klasse=ziel.className;
			if(klasse=="search-cat closed") {
				ziel.className="search-cat opened";
			} else {
				ziel.className="search-cat closed";
			}
		}

		function openclose3(e) {
			img="";
			node=e;
			for(i=0;i<10;i++) {
				if(node.nodeName.toLowerCase()=="img" && img=="") {
					img=node;
				}
				node=node.parentNode;
				if(node.nodeName.toLowerCase()=="li") {
					for(j=0;j<node.childNodes.length;j++) {
						if(node.childNodes[j].nodeName.toLowerCase()=="ul") {
							if(node.childNodes[j].style.display=="none") {
								node.childNodes[j].style.display="block";
								if(img.src.indexOf("icn_wms_plus")!=-1) img.src="fileadmin/design/icn_wms2.png";
								if(img.src.indexOf("icn_wfs_plus")!=-1) img.src="fileadmin/design/icn_wfs2.png";
								if(img.src.indexOf("icn_layer_plus")!=-1) img.src="fileadmin/design/icn_layer2.png";
							} else {
								node.childNodes[j].style.display="none";
								if(img.src.indexOf("icn_wms2")!=-1) img.src="fileadmin/design/icn_wms_plus.png";
								if(img.src.indexOf("icn_wfs2")!=-1) img.src="fileadmin/design/icn_wfs_plus.png";
								if(img.src.indexOf("icn_layer2")!=-1) img.src="fileadmin/design/icn_layer_plus.png";
							}
						}
					}
					break;
				}
			}
		}

		function openwindow(Adresse) {
  		Fenster1 = window.open(Adresse, "Informationen", "width=500,height=600,left=100,top=100,scrollbars=yes,resizable=no");
  		Fenster1.focus();
		}


		function set_cookie(name,value,path) {
		  var cookie_string = name + "=" + escape(value);
		  if(path) { 
		  	cookie_string += "; path=" + escape(path);
		  }
		  document.cookie = cookie_string;
		}		

		function get_cookie(cookie_name) {
		  var results = document.cookie.match ( cookie_name + \'=(.*?)(;|$)\' );
		  if ( results ) {
		    return(unescape(results[1]));
		  } else {
		    return null;
		  }
		}		
		
		function tou(elem,id,type,tou) {
			if(elem.checked) {
				elem.checked=false;
				jQuery.post(
					"/mapbender/php/mod_acceptedTou_server.php",
					{
						method:"checkAcceptedTou",
						id:1,
						params: "{\"serviceType\":\""+type+"\",\"serviceId\":"+id+"}"
					},
					function(data) {
						if(data.result.success) {
							if(data.result.data==1) {
								elem.checked=true;
							} else {
								fenster=window.open("'.$SCRIPTDIR.'/fileadmin/scripts/termsofuse.php?tou="+tou, "TermsOfUse", "width=500,height=500,left=100,top=100,scrollbars=yes,resizable=no");
								fenster.focus();
							}
						}
					}
				);
				return false;
			}
			return true;
		}

		function tou2(thiselem,elem,id,type,tou) {
			if(elem.checked) {
				return true;
			}

			jQuery.post(
				"/mapbender/php/mod_acceptedTou_server.php",
				{
					method:"checkAcceptedTou",
					id:1,
					params: "{\"serviceType\":\""+type+"\",\"serviceId\":"+id+"}"
				},
				function(data) {
					if(data.result.success==true) {
						if(data.result.data==1) {
							window.location.href=thiselem.href;
						} else {
							fenster=window.open("'.$SCRIPTDIR.'/fileadmin/scripts/termsofuse.php?link=1&tou="+tou, "TermsOfUse", "width=500,height=500,left=100,top=100,scrollbars=yes,resizable=no");
							fenster.focus();
						}
					}
				}
			);

			return false;
		}

		function tou3(thiselem,elem,id,type,tou) {
			if(elem.checked) {
				return true;
			}

			jQuery.post(
				"/mapbender/php/mod_acceptedTou_server.php",
				{
					method:"checkAcceptedTou",
					id:1,
					params: "{\"serviceType\":\""+type+"\",\"serviceId\":"+id+"}"
				},
				function(data) {
					if(data.result.success==true) {
						if(data.result.data==1) {
							elem.checked=true;
							thiselem.form.submit();
							found=true;
						} else {
							fenster=window.open("'.$SCRIPTDIR.'/fileadmin/scripts/termsofuse.php?link=2&tou="+tou, "TermsOfUse", "width=500,height=500,left=100,top=100,scrollbars=yes,resizable=no");
							fenster.focus();
						}
					}
				}
			);
			return false;
		}

		function touokdirect(elemid,id,type) {
			document.getElementById(elemid).checked=true;
			jQuery.post(
				"/mapbender/php/mod_acceptedTou_server.php",
				{
					method:"setAcceptedTou",
					id:1,
					params: "{\"serviceType\":\""+type+"\",\"serviceId\":"+id+"}"
				}
			);
			document.formmaps.submit()
		}

		function touoklink(url,id,type) { 
			jQuery.post(
				"/mapbender/php/mod_acceptedTou_server.php",
				{
					method:"setAcceptedTou",
					id:1,
					params: "{\"serviceType\":\""+type+"\",\"serviceId\":"+id+"}"
				}
			);
			window.location.href=url;
		}

		function touok(elemid,id,type) {
			document.getElementById(elemid).checked=true;
			jQuery.post(
				"/mapbender/php/mod_acceptedTou_server.php",
				{
					method:"setAcceptedTou",
					id:1,
					params: "{\"serviceType\":\""+type+"\",\"serviceId\":"+id+"}"
				}
			);
		}

		function opentou(tou) {
			fenster=window.open("'.$SCRIPTDIR.'/fileadmin/scripts/termsofuse.php?tou="+tou, "TermsOfUse", "width=500,height=500,left=100,top=100,scrollbars=yes,resizable=no");
			fenster.focus();
		}

		</script>';

	if($timer) {
		$output.='
		<script type="text/javascript">
			var search_filter="'.$ready['filter'].'";
			var search_info="'.$ready['info'].'";
			var search_meta="'.$ready['meta'].'";
			var search_dienste="'.$ready['dienste'].'";
			var search_adressen="'.$ready['adressen'].'";

			function AjaxSearch () {
				xajax_search("'.PtH($unique).'","'.PtH($_REQUEST['searchid']).'", "'.PtH($cat).'", "'.PtH($page).'", "'.$_SESSION['mb_user_id'].'", "'.t3lib_div::GPvar('L').'", search_info,search_meta,search_dienste,search_adressen,search_filter);
			}
			
			window.setTimeout("AjaxSearch()", 500);
		</script>
		';
	}
} else {
	$typo3file = dirname(__FILE__).'/temp/'.$unique.'.typo3.xml';
	$filterfile = '/data/mapbender/http/tmp/'.md5($unique).'_filter.json';
	$dienstefile = '/data/mapbender/http/tmp/'.md5($unique).'###SERVICE###.json';
	$adressfile = '/data/mapbender/http/tmp/'.$unique.'_geom.xml';
	$metafile = '/data/mapbender/http/tmp/'.$unique.'_os.xml';
	$wikifile = '/data/mapbender/http/tmp/'.$unique.'_wiki.xml';

/*
	$filterfile = dirname(__FILE__).'/temp/627bff9db837eb099b64f9dcde83f270_filter.json';
	$dienstefile = dirname(__FILE__).'/temp/627bff9db837eb099b64f9dcde83f270###SERVICE###.json';
	$adressfile = dirname(__FILE__).'/temp/__.geom.xml';
	$typo3file = dirname(__FILE__).'/temp/'.$unique.'.typo3.xml';
	$metafile = dirname(__FILE__).'/temp/__os.xml';
	$wikifile = dirname(__FILE__).'/temp/__.wiki.xml';
*/

	if($language==1) $L=$Language['en'];
	else $L=$Language['de'];
	$LinkURL=$L['SuchURL'];

	$ready['filter']=1;
	if($search_filter==0) {
		$name='filter';
		$ready[$name]=Filter($filterfile);
	}

	switch($cat) {
		case '':
			$startpage=true;

			$ready['adressen']=1;
			if($search_adressen==0) {
				$name='adressen';
				$ready[$name]=Adressen($adressfile,5);
				if($ready[$name]) $out["adressen-filter"]=getAdrCat();
			}

			$ready['dienste']=1;
			if($search_dienste==0) {
				$name='dienste';
				$ready[$name]=Dienste($dienstefile,5);
				if($ready[$name]) $out["service-filter"]=getServiceCat();
			}

			$ready['info']=1;
			if($search_info==0) {
				$name='info';
				$ready[$name]=Info($typo3file,$wikifile,5);
			}

			$ready['meta']=1;
			if($search_meta==0) {
				$name='meta';
				$ready[$name]=Meta($metafile,5);
			}

			break;
		case 'adressen':
			$startpage=false;
			$name='adressen';
			$ready[$name]=Adressen($adressfile);
			if($ready[$name]) $out["adressen-filter"]=getAdrCat();
			break;
		case 'dienste':
			$startpage=false;
			$name='dienste';
			$ready[$name]=Dienste($dienstefile);
			if($ready[$name]) $out["service-filter"]=getServiceCat();
			break;
		case 'info':
			$startpage=false;
			$name='info';
			$ready[$name]=Info($typo3file,$wikifile);
			break;
		case 'meta':
			$startpage=false;
			$name='meta';
			$ready[$name]=Meta($metafile);
			break;
	}
}

?>
