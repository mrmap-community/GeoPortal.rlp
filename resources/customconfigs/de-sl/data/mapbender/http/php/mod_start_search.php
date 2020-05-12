<?php
function mss_debug($text) {
    if($fh = fopen("/var/log/php/mod_start_search.txt", "w")){
        fwrite($fh, "\n\r".$text);
        fclose($fh);
    }
}
mss_debug("mss");
//include_once(dirname(__FILE__).'/../../../../fileadmin/function/config.php');
//include_once(dirname(__FILE__).'/../../../../fileadmin/function/util.php');
//include_once(dirname(__FILE__).'/../../../../fileadmin/function/function.php');
//include_once(dirname(__FILE__).'/../../../../fileadmin/function/crypt.php');


require_once(dirname(__FILE__)."/../../core/globalSettings.php");
require_once(dirname(__FILE__)."/../../conf/geoportal.conf");#???
require_once(dirname(__FILE__)."/../classes/class_metadata_new.php");
require_once(dirname(__FILE__)."/../classes/class_json.php");
require_once(dirname(__FILE__)."/../classes/class_connector.php"); 

include_once(dirname(__FILE__).'/../template/php/search_functions.php');

$tempFolder = TMPDIR;

GLOBAL $output, $out, $name, $data, $counter, $unique, $startpage, $count, $page, $items_page, $in_ajax;
GLOBAL $GUESTID;//, $Language, $Lang, $L;
GLOBAL $check_array, $unique;
//global $LinkURL;
$pageULR = 'http://'.$_SERVER["HTTP_HOST"].'/'; // URL der Seite
mss_debug("pageULR:".$pageULR);
$languageCode = "de";

$isCategory = (!isset($_REQUEST['categorySearch']))? false : true;

$_REQUEST['uid']=(!isset($_REQUEST['uid']) || $_REQUEST['uid']=='')?md5(microtime(true).$_SESSION["mb_user_id"]):$_REQUEST['uid'];
//Read out request Parameter:
if (isset($_REQUEST["searchId"]) && $_REQUEST["searchId"] != "") {
	//gernerate md5 representation, cause the id is used as a filename later on! - no validation needed
	$searchId = $_REQUEST["searchId"];
} else {
//	$_REQUEST["searchId"] = md5(session_id());
//	$_REQUEST["searchId"] = md5($_REQUEST['uid']);
	$_REQUEST["searchId"] = $_REQUEST['uid'];
	$searchId = $_REQUEST["searchId"];
}

if (isset($_SESSION['mb_user_id']) && $_SESSION['mb_user_id'] != "") {
    $mb_user_id = $_SESSION['mb_user_id'];
} else if (Mapbender::session()->get("mb_user_id")!="") {
    $mb_user_id = Mapbender::session()->get("mb_user_id");
} else {
//    $mb_user_id = CreateUUID();
	$mb_user_id = PUBLIC_USER;;
}
$mb_user_name = Mapbender::session()->get("mb_user_name");
$mb_user_guis = Mapbender::session()->get("mb_user_guis");

$_REQUEST['outputFormat'] = (!isset($_REQUEST['outputFormat']) || $_REQUEST['outputFormat'] == "") ? "json" : $_REQUEST['outputFormat'];
//$NewSearch=false;

if(isset($_REQUEST['searchfilter']) && $_REQUEST['searchfilter']!='') {
	parse_str($_REQUEST['searchfilter'],$URLParts);
	unset($URLParts['searchId']);
	$URLParts['hostName']=$_SERVER['HTTP_HOST'];
	$_REQUEST['searchfilter']=http_build_query($URLParts);
	$_REQUEST['searchtext']=str_replace(',',' ',$URLParts['searchText']);
	
	foreach($URLParts as $key=>$value) {
		$_REQUEST[$key]=$value;
	}
	$_REQUEST['searchText']=str_replace(',',' ',$URLParts['searchText']);
}

$unique=$_REQUEST['uid'];
#Mapbender::session()->set("searchId",$unique);


$searchtext=$_REQUEST['searchText'];//.$_REQUEST['registratingDepartments'].$_REQUEST['isoCategories'].$_REQUEST['regTimeBegin'].$_REQUEST['regTimeEnd'].$_REQUEST['searchBbox'].$_REQUEST['searchTypeBbox'].$_REQUEST['searchResources'].$_REQUEST['timeBegin'].$_REQUEST['timeEnd'];
//$searchtext=$_REQUEST['searchtext']=addslashes($searchtext);

$cat = (isset($_SESSION['cat'])) ? $_REQUEST['cat'] :"";

$items_page=100;
$page=(isset($_REQUEST['page']))?$_REQUEST['page']:$_REQUEST['page']=$page=0;

//$filterfile = '/data/mapbender/http/tmp/'.md5($unique).'_filter.json';
//$dienstefile = '/data/mapbender/http/tmp/'.md5($unique).'###SERVICE###.json';
$filterfile = '/data/mapbender/http/tmp/'.$unique.'_filter.json';
$dienstefile = '/data/mapbender/http/tmp/'.$unique.'###SERVICE###.json';
$metafile = '/data/mapbender/http/tmp/'.$unique.'_os.xml';

//$in_ajax=$ajax;
//if(!$ajax) { // Normale Suche
//	$filterfile = '/data/mapbender/http/tmp/'.md5($unique).'_filter.json';
//	$dienstefile = '/data/mapbender/http/tmp/'.md5($unique).'###SERVICE###.json';
//	$metafile = '/data/mapbender/http/tmp/'.$unique.'_os.xml';

//	if(t3lib_div::GPvar('L')==1) {
//		$Lang='en';
//	} else {
//		$Lang='de';
//	}
//	$L=$Language[$Lang];
//        $L=$Language[$languageCode];
//	$LinkURL=$L['ErwSuchURL'];
	if ($searchtext!='' || $_REQUEST['searchfilter']!='') {
		if(!isset($_REQUEST['searchfilter']) || $_REQUEST['searchfilter']=='') {
			$_REQUEST['searchfilter']='languageCode='.$languageCode.'&searchEPSG=EPSG:31466&userId='.$mb_user_id.'&resultTarget=file&outputFormat='.$_REQUEST['outputFormat'].'&hostName='.$_SERVER['HTTP_HOST'];
			$_REQUEST['searchfilter']='&searchText='.str_replace(' ',',',$_REQUEST['searchText']);
			// Filter für die Dienst-Such-RRL
			foreach(array(
			          'registratingDepartments',
			          'isoCategories',
			          'inspireThemes',
			          'customCategories',
			          'regTimeBegin',
			          'regTimeEnd',
			          'timeBegin',
			          'timeEnd',
			          'searchBbox',
			          'searchTypeBbox',
			          'searchResources',
			          'orderBy',
			         ) as $key) {
				if(isset($_REQUEST[$key]) && $_REQUEST[$key]!='') $_REQUEST['searchfilter'].='&'.$key.'='.$_REQUEST[$key];
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
			$shplus.='"'.((isset($_REQUEST[$key]) && $_REQUEST[$key]!='')?$_REQUEST[$key]:'false').'" ';
		}
//		debug("HTTP_HOST:".$_SERVER['HTTP_HOST']);
                    exec('php5 /data/mapbender/http/geoportal/gaz.php "'.$mb_user_id.'" "'.$unique.'" "'.$_REQUEST['searchText'].'" "'.$_SESSION['epsg'].'"  '.$shplus.' >/dev/null 2>&1 &');
                    $url = 'http://'.$_SERVER['HTTP_HOST'].'/mapbender/php/mod_callMetadata.php?searchId='.$searchId.'&'.$_REQUEST['searchfilter'].'&userId='.$mb_user_id;
                    $x = new connector($url);
                    // Warten nach der ersten Suche

                    $i = 10;
                    $isJsonWMS = false;
                    $isJsonWFS = false;
                    $isJsonWMC = false;
                    $isJsonFilter = false;
                    $isOS = false;
                    $isWiki = false;
                    $isGeom = false;
                    $isCategoryXml = false;

                    if ($isCategory) {
                        $i = 20;
                        $isJsonWMS = false;
                        $isJsonWFS = true;
                        $isJsonWMC = true;
                        $isJsonFilter = true;
                        $isOS = false;
                        $isWiki = true;
                        $isGeom = true;
                        $isCategoryXml = false;
                    } else if ((isset($_REQUEST['searchResources']) && $_REQUEST['searchResources']!='')){
                        $searchResources_ = $_REQUEST['searchResources'];

                        $isJsonWFS = true;
                        $isJsonWMC = true;
                        $isJsonWMS = true;
                        
                        $isOS = true;
                        $isWiki = true;
                        $isGeom = true;
                        $isCategoryXml = true;
                        $isJsonFilter = false;

                        if (stripos($searchResources_, "wfs") !== false) {
                            $isJsonWFS = false;
                        }
                        if (stripos($searchResources_, "wmc") !== false) {
                            $isJsonWMC = false;
                        }
                        if (stripos($searchResources_, "wms") !== false) {
                            $isJsonWMS = false;
                        }
                    }
                $num = 0;
                mss_debug("Warte:".microtime());
                while ($i > 0) {
                    $num++;
                    mss_debug("Warte in while:".$num." ".microtime());
                    usleep(500000);
                    if ($isJsonWFS && $isJsonWMC && $isJsonWMS && $isJsonFilter && $isOS && $isWiki && $isGeom && $isCategoryXml) {
                        usleep(200000);
                        $i = 0;
                    } else {
                        if(!$isCategoryXml) {
                            mss_debug("Warte in isCategoryXml:".$num);
                            if (count(glob($tempFolder.DIRECTORY_SEPARATOR.$searchId."_wms*.xml")) > 0) {
                                $isCategoryXml = true;
                            }
                        }
                        if(!$isOS) {
                            mss_debug("Warte in isOS:".$num);
                            if (count(glob($tempFolder.DIRECTORY_SEPARATOR.$unique."_os.xml")) == 1){
                                $osxml = file_get_contents($tempFolder.DIRECTORY_SEPARATOR.$unique."_os.xml");
                                $os = new DOMDocument('1.0');
                                $os->load($tempFolder.DIRECTORY_SEPARATOR.$unique."_os.xml");
                                $xpath = new DOMXPath($os);
                                $countElts = $xpath->query("/interfaces/opensearchinterface")->length;
                                if (count(glob($tempFolder.DIRECTORY_SEPARATOR.$unique."_os*.xml")) == ($countElts +1)){
                                    $isOS = true;
                                }
                            }
                        }
                        if(!$isWiki) {
                            mss_debug("Warte in isWiki:".$num);
                            if (count(glob($tempFolder.DIRECTORY_SEPARATOR.$unique."_wiki.xml")) == 1){
                                $isWiki = true;
                            }
                        }
                        if(!$isGeom) {
                            mss_debug("Warte in isGeom:".$num);
                            if (count(glob($tempFolder.DIRECTORY_SEPARATOR.$unique."_geom.xml")) == 1){
                                $isGeom = true;
                            }
                        }
                        if(!$isJsonWFS) {
                            mss_debug("Warte in isJsonWFS".$num);
                            if (count(glob($tempFolder.DIRECTORY_SEPARATOR.$searchId."_wfs_*.json")) == 3) {
                                $isJsonWFS = true;
                            }
                        }
                        if(!$isJsonWMS) {
                            mss_debug("Warte in isJsonWMS:".$num);
                            if (count(glob($tempFolder.DIRECTORY_SEPARATOR.$searchId."_wms_*.json")) == 3) {
                                $isJsonWMS = true;
                            }
                        }
                        if(!$isJsonWMC) {
                            $count = count(glob($tempFolder.DIRECTORY_SEPARATOR.$searchId."_wmc_*.json"));
                            mss_debug("Warte in isJsonWMC:".$num." ".$count.$tempFolder.DIRECTORY_SEPARATOR.$searchId."_wmc_*.json");
                            if (count(glob($tempFolder.DIRECTORY_SEPARATOR.$searchId."_wmc_*.json")) == 3) {
                                $isJsonWMC = true;
                            }
                        }
                        if(!$isJsonFilter) {
                            mss_debug("Warte in isJsonFilter:".$num);
                            if (count(glob($tempFolder.DIRECTORY_SEPARATOR.$searchId."_filter.json")) == 1) {
                                $isJsonFilter = true;
                            }
                        }
                    }
                    $i--;
                }
//		$microsPerSecond = 1000000;
//		usleep(($microsPerSecond/2));
	}
mss_debug("echo json_encode:".microtime());
echo json_encode(array(
	"searchId" => $searchId,
	"mb_user_id" => $mb_user_id,
	"mb_user_name" => $mb_user_name,
	"mb_user_guis" => $mb_user_guis,
	"uid" => $_REQUEST["uid"],
	"searchText" => $_REQUEST["searchText"]

));
	
////die("");
//
//	$timer=false;
//
//	$url=SearchURL();
//
//	$name="filter";
//	$ready[$name]=Filter($filterfile);
//	if($ready[$name]==0) {
//		$timer=true;
//	}
//	$output.='<div class="search-filter">
//	'.$out[$name].'
//	<div class="clr"></div>
//	</div>';
//
//	$output.='<ul class="search-cat">';
//
//// Gesamt
//	$output.=($cat=='')?'<li class="active">':'<li>';
//	$output.='<a href="'.$url.'">'.$L['Gesamt'].'</a></li>';
//
//// Dienste
//	$content=$i='';
//	$Counter=CountDienste($dienstefile);
//	$i=$Counter['count'];
//	if($Counter['ready']==0 && $searchtext!='') {
//		$i='<span id="search-count-dienste"></span><span id="search-indicator-dienste">'.$indicator.'</span>';
//		$timer=true;
//	} else {
//		$i='<span id="search-count-dienste">'.$i.'</span>';
//	}
//	$output.=($cat=='dienste')?'<li class="active">':'<li>';
//	$output.='<a href="'.$url.'&amp;cat=dienste">'.$L['Dienste'].$i.'</a></li>';
//
//// Meta
//	$i='';
//	$count=$count_meta=0;
//
//	$allready=0;
//	if(file_exists($metafile) && filesize($metafile)>0) {
//		$indexfile = file($metafile);
//		$allready=1;
//		$key=0;
//		foreach($indexfile as $folder) {
//			if(preg_match('/<opensearchinterface>(.+)<\/opensearchinterface>/',$folder,$matches)==0 || trim($matches[1])=='') continue;
//			$key++;
//			$count_meta++;
//			$parts=split('\.',$metafile);
//			$filename=$parts[0].$key.'_1.'.$parts[1];
//			if(file_exists($filename)) {
//				$content=file_get_contents($filename);
//				if(preg_match('/<totalresults>(.+)<\/totalresults>/',$content,$matches)!=0)
//					$count+=$matches[1];
//				if(strpos($content,'</resultlist>')===false) {
//					$allready=0;
//				}
//			} else {
//				$allready=0;
//			}
//		}
//	}
//	if($count!=0) $i=$count;
//	$i='<span id="search-count-meta">'.$i.'</span>';
//	if($allready==0) {
//		$i.='<span id="search-indicator-meta">'.$indicator.'</span>';
//		$timer=true;
//	}
//	$output.=($cat=='meta')?'<li class="active">':'<li>';
//	$output.='<a href="'.$url.'&amp;cat=meta">'.$L['Metadaten'].$i.'</a></li>';
//
//	$output.='</ul>';
//
//	$output.='<div class="search-container">';
//
//	switch($cat) {
//		case '':
//			$startpage=true;
//
//// Dienste
//			$name='dienste';
//			$ready[$name]=Dienste($dienstefile,5);
//			if($ready[$name]==0) {
//				$i='<span id="search-header-indicator-dienste">'.$indicator.'</span>';
//				$timer=true;
//			} else {
//				$i='';
//			}
//			$output.='<h2><a href="'.$url.'&amp;cat=dienste" title="'.str_replace('###AREA###',$L['Dienste'],$L['alleErgebnisse']).'">'.$i.''.$L['Dienste'].'</a></h2>';
//			$output.='
//				<div id="search-container-'.$name.'">
//				'.$out[$name].'
//				</div>';
//
//// Metadaten
//			$name='meta';
//			$ready[$name]=Meta($metafile,5);
//			if($ready[$name]==0) {
//				$i='<span id="search-header-indicator-meta">'.$indicator.'</span>';
//				$timer=true;
//			} else {
//				$i='';
//			}
//			$output.='<h2><a href="'.$url.'&amp;cat=meta" title="'.str_replace('###AREA###',$L['Metadaten'],$L['alleErgebnisse']).'">'.$i.''.$L['Metadaten'].'</a></h2>';
//			$output.='
//				<div id="search-container-'.$name.'">
//				'.$out[$name].'
//				</div>';
//
//			break;
//
//		case 'dienste':
//			$startpage=false;
//
//			$name='dienste';
//			$ready[$name]=Dienste($dienstefile);
//			if($ready[$name]==0) $timer=true;
//			$output.='
//				<div id="search-container-'.$name.'">
//				'.$out[$name].'
//				</div>';
//
//			break;
//
//		case 'meta':
//			$startpage=false;
//
//			$name='meta';
//			$ready[$name]=Meta($metafile);
//			if($ready[$name]==0) $timer=true;
//
//			for($i=0;$i<$count_meta;$i++) {
//				$output.='
//				<div id="metastyles'.$i.'" style="display:none">
//					<style type="text/css">
//						.meta-search-container'.$i.' .search-header{background:#DFDFDF url(fileadmin/design/plus.png) 99.75% 3px no-repeat;cursor:pointer;padding-right:25px !important}
//						.meta-search-container'.$i.' .search-item{display:none}
//						.meta-search-container'.$i.' .search-pagecounter-container{display:none}
//					</style>
//				</div>';
//			}
//			$output.='
//				<div id="search-container-'.$name.'">
//				'.$out[$name].'
//				</div>';
//			break;
//	}
//	$output.='
//			</div>
//			<br class="clr" />
//			<div id="searchresults"></div>';
//
//	if($cat=='meta') {
//		$jsoutput='
//			window.onload=start;
//			function start() {
//				opencat=get_cookie("opencat");
//				if(opencat!=null) {
//					cats=opencat.split("|");
//					for(i=0;i<cats.length;i++) {
//						if(cats[i]=="open") openclose(i.toString());
//					}
//				}
//			}
//		';
//	} else {
//		$jsoutput='';
//	}
//
//	$output.='
//		<script type="text/javascript">
//
//		var cat=new Array();
//		'.$jsoutput.'
//		function openclose(id) {
//			if(typeof cat[id]=="undefined" || cat[id]=="close") {
//				cat[id]="open";
//				var cssStr = ".meta-search-container"+id+" .search-header{background:#DFDFDF url(fileadmin/design/minus.png) 99.75% 3px no-repeat;cursor:pointer;padding-right:25px !important} .meta-search-container"+id+" .search-item{display:block} .meta-search-container"+id+" .search-pagecounter-container{display:block}";
//			} else {
//				cat[id]="close";
//				var cssStr = ".meta-search-container"+id+" .search-header{background:#DFDFDF url(fileadmin/design/plus.png) 99.75% 3px no-repeat;cursor:pointer;padding-right:25px !important} .meta-search-container"+id+" .search-item{display:none} .meta-search-container"+id+" .search-pagecounter-container{display:none}";
//			}
//			set_cookie("opencat",cat.join("|"));
//			var style = document.createElement("style");
//			style.setAttribute("type", "text/css");
//
//			if(style.styleSheet) { // IE
//				style.styleSheet.cssText = cssStr;
//			} else { // w3c
//				var cssText = document.createTextNode(cssStr);
//				style.appendChild(cssText);
//			}
//
//			document.getElementById("metastyles"+id).innerHTML="";
//			document.getElementById("metastyles"+id).appendChild(style);
//		}
//
//		function openclose2(e) {
//			ziel=e.parentNode;
//			klasse=ziel.className;
//			if(klasse=="search-cat closed") {
//				ziel.className="search-cat opened";
//			} else {
//				ziel.className="search-cat closed";
//			}
//		}
//
//		function openclose3(e) {
//			img="";
//			node=e;
//			for(i=0;i<10;i++) {
//				if(node.nodeName.toLowerCase()=="img" && img=="") {
//					img=node;
//				}
//				node=node.parentNode;
//				if(node.nodeName.toLowerCase()=="li") {
//					for(j=0;j<node.childNodes.length;j++) {
//						if(node.childNodes[j].nodeName.toLowerCase()=="ul") {
//							if(node.childNodes[j].style.display=="none") {
//								node.childNodes[j].style.display="block";
//								if(img.src.indexOf("icn_wms_plus")!=-1) img.src="fileadmin/design/icn_wms2.png";
//								if(img.src.indexOf("icn_wfs_plus")!=-1) img.src="fileadmin/design/icn_wfs2.png";
//								if(img.src.indexOf("icn_layer_plus")!=-1) img.src="fileadmin/design/icn_layer2.png";
//							} else {
//								node.childNodes[j].style.display="none";
//								if(img.src.indexOf("icn_wms2")!=-1) img.src="fileadmin/design/icn_wms_plus.png";
//								if(img.src.indexOf("icn_wfs2")!=-1) img.src="fileadmin/design/icn_wfs_plus.png";
//								if(img.src.indexOf("icn_layer2")!=-1) img.src="fileadmin/design/icn_layer_plus.png";
//							}
//						}
//					}
//					break;
//				}
//			}
//		}
//
//		function openwindow(Adresse) {
//  		Fenster1 = window.open(Adresse, "Informationen", "width=500,height=600,left=100,top=100,scrollbars=yes,resizable=no");
//  		Fenster1.focus();
//		}
//
//
//		function set_cookie(name,value,path) {
//		  var cookie_string = name + "=" + escape(value);
//		  if(path) {
//		  	cookie_string += "; path=" + escape(path);
//		  }
//		  document.cookie = cookie_string;
//		}
//
//		function get_cookie(cookie_name) {
//		  var results = document.cookie.match ( cookie_name + \'=(.*?)(;|$)\' );
//		  if ( results ) {
//		    return(unescape(results[1]));
//		  } else {
//		    return null;
//		  }
//		}
//
//		function tou(elem,id,type,tou) {
//			if(elem.checked) {
//				elem.checked=false;
//				jQuery.post(
//					"/mapbender/php/mod_acceptedTou_server.php",
//					{
//						method:"checkAcceptedTou",
//						id:1,
//						params: "{\"serviceType\":\""+type+"\",\"serviceId\":"+id+"}"
//					},
//					function(data) {
//						if(data.result.success) {
//							if(data.result.data==1) {
//								elem.checked=true;
//							} else {
//								fenster=window.open("'.$SCRIPTDIR.'/fileadmin/scripts/termsofuse.php?tou="+tou, "TermsOfUse", "width=500,height=500,left=100,top=100,scrollbars=yes,resizable=no");
//								fenster.focus();
//							}
//						}
//					}
//				);
//				return false;
//			}
//			return true;
//		}
//
//		function tou2(thiselem,elem,id,type,tou) {
//			if(elem.checked) {
//				return true;
//			}
//
//			jQuery.post(
//				"/mapbender/php/mod_acceptedTou_server.php",
//				{
//					method:"checkAcceptedTou",
//					id:1,
//					params: "{\"serviceType\":\""+type+"\",\"serviceId\":"+id+"}"
//				},
//				function(data) {
//					if(data.result.success==true) {
//						if(data.result.data==1) {
//							window.location.href=thiselem.href;
//						} else {
//							fenster=window.open("'.$SCRIPTDIR.'/fileadmin/scripts/termsofuse.php?link=1&tou="+tou, "TermsOfUse", "width=500,height=500,left=100,top=100,scrollbars=yes,resizable=no");
//							fenster.focus();
//						}
//					}
//				}
//			);
//
//			return false;
//		}
//
//		function tou3(thiselem,elem,id,type,tou) {
//			if(elem.checked) {
//				return true;
//			}
//
//			jQuery.post(
//				"/mapbender/php/mod_acceptedTou_server.php",
//				{
//					method:"checkAcceptedTou",
//					id:1,
//					params: "{\"serviceType\":\""+type+"\",\"serviceId\":"+id+"}"
//				},
//				function(data) {
//					if(data.result.success==true) {
//						if(data.result.data==1) {
//							elem.checked=true;
//							thiselem.form.submit();
//							found=true;
//						} else {
//							fenster=window.open("'.$SCRIPTDIR.'/fileadmin/scripts/termsofuse.php?link=2&tou="+tou, "TermsOfUse", "width=500,height=500,left=100,top=100,scrollbars=yes,resizable=no");
//							fenster.focus();
//						}
//					}
//				}
//			);
//			return false;
//		}
//
//		function touokdirect(elemid,id,type) {
//			document.getElementById(elemid).checked=true;
//			jQuery.post(
//				"/mapbender/php/mod_acceptedTou_server.php",
//				{
//					method:"setAcceptedTou",
//					id:1,
//					params: "{\"serviceType\":\""+type+"\",\"serviceId\":"+id+"}"
//				}
//			);
//			document.formmaps.submit()
//		}
//
//		function touoklink(url,id,type) {
//			jQuery.post(
//				"/mapbender/php/mod_acceptedTou_server.php",
//				{
//					method:"setAcceptedTou",
//					id:1,
//					params: "{\"serviceType\":\""+type+"\",\"serviceId\":"+id+"}"
//				}
//			);
//			window.location.href=url;
//		}
//
//		function touok(elemid,id,type) {
//			document.getElementById(elemid).checked=true;
//			jQuery.post(
//				"/mapbender/php/mod_acceptedTou_server.php",
//				{
//					method:"setAcceptedTou",
//					id:1,
//					params: "{\"serviceType\":\""+type+"\",\"serviceId\":"+id+"}"
//				}
//			);
//		}
//		function opentou(tou) {
//			fenster=window.open("/portal/fileadmin/scripts/termsofuse.php?tou="+tou, "TermsOfUse", "width=500,height=500,left=100,top=100,scrollbars=yes,resizable=no");
//			fenster.focus();
//		}
//
//		</script>';
//
//	if($timer) {
//		$output.='
//		<script type="text/javascript">
//			var search_filter="'.$ready['filter'].'";
//			var search_meta="'.$ready['meta'].'";
//			var search_dienste="'.$ready['dienste'].'";
//
//			function AjaxSearch () {
//				xajax_search("'.$unique.'","'.$_REQUEST['searchid'].'", "'.$cat.'", "'.$page.'", "'.$md_user_id.'", "'.t3lib_div::GPvar('L').'", search_meta,search_dienste,search_filter);
//			}
//
//			window.setTimeout("AjaxSearch()", 500);
//		</script>
//		';
//	}
////} else {
////	$filterfile = '/data/mapbender/http/tmp/'.md5($unique).'_filter.json';
////	$dienstefile = '/data/mapbender/http/tmp/'.md5($unique).'###SERVICE###.json';
////	$metafile = '/data/mapbender/http/tmp/'.$unique.'_os.xml';
////
////	if($language==1) $L=$Language['en'];
////	else $L=$Language['de'];
////	$LinkURL=$L['ErwSuchURL'];
////
////	switch($cat) {
////		case '':
////			$startpage=true;
////
////			$ready['dienste']=1;
////			if($search_dienste==0) {
////				$name='dienste';
////				$ready[$name]=Dienste($dienstefile,5);
////				if($ready[$name]) $out["service-filter"]=getServiceCat();
////			}
////
////			$ready['meta']=1;
////			if($search_meta==0) {
////				$name='meta';
////				$ready[$name]=Meta($metafile,5);
////			}
////
////			break;
////		case 'dienste':
////			$startpage=false;
////			$name='dienste';
////			$ready[$name]=Dienste($dienstefile);
////			if($ready[$name]) $out["service-filter"]=getServiceCat();
////			break;
////		case 'meta':
////			$startpage=false;
////			$name='meta';
////			$ready[$name]=Meta($metafile);
////			break;
////	}
////}

?>
