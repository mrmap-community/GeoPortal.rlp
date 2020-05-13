<?php
include_once(dirname(__FILE__).'/../../../../fileadmin/function/config.php');
include_once(dirname(__FILE__).'/../../../../fileadmin/function/util.php');
include_once(dirname(__FILE__).'/../../../../fileadmin/function/function.php');
include_once(dirname(__FILE__).'/../../../../fileadmin/function/crypt.php');
include_once(dirname(__FILE__).'/../pi1/search_functions.php');

GLOBAL $output, $out, $name, $data, $counter, $unique, $startpage, $count, $page, $items_page, $in_ajax;
GLOBAL $GUESTID, $Language, $Lang, $L;
GLOBAL $check_array, $unique;
global $LinkURL;

$_REQUEST['uid']=($_REQUEST['uid']=='')?CreateUUID():$_REQUEST['uid'];

$NewSearch=false;

if($_REQUEST['searchfilter']!='') {
	parse_str($_REQUEST['searchfilter'],$URLParts);
	unset($URLParts['searchId']);
	$URLParts['hostName']=$_SERVER['HTTP_HOST'];
	$_REQUEST['searchfilter']=http_build_query($URLParts);

	foreach($URLParts as $key=>$value) {
		$_REQUEST[$key]=$value;
	}
	$_REQUEST['searchText']=str_replace(',',' ',$URLParts['searchText']);
}

$unique=$_REQUEST['uid'];

$searchtext=$_REQUEST['searchText'].$_REQUEST['registratingDepartments'].$_REQUEST['isoCategories'].$_REQUEST['regTimeBegin'].$_REQUEST['regTimeEnd'].$_REQUEST['searchBbox'].$_REQUEST['searchTypeBbox'].$_REQUEST['searchResources'].$_REQUEST['timeBegin'].$_REQUEST['timeEnd'];

$cat=$_REQUEST['cat'];

$items_page=10;
$page=$_REQUEST['page'];
if(!$page) { $_REQUEST['page']=$page=0; }

$in_ajax=$ajax;
if(!$ajax) { // Normale Suche
	$filterfile = '/data/mapbender/http/tmp/'.md5($unique).'_filter.json';
	$dienstefile = '/data/mapbender/http/tmp/'.md5($unique).'###SERVICE###.json';
	$metafile = '/data/mapbender/http/tmp/'.$unique.'_os.xml';

	if(t3lib_div::GPvar('L')==1) {
		$Lang='en';
	} else {
		$Lang='de';
	}
	$L=$Language[$Lang];
	$LinkURL=$L['ErwSuchURL'];

	if ($searchtext!='' || $_REQUEST['searchfilter']!='') {
		if($_REQUEST['searchfilter']=='') {
			$_REQUEST['searchfilter']='languageCode='.$Lang.'&searchEPSG='.$_SESSION['epsg'].'&userId='.$_SESSION['mb_user_id'].'&resultTarget=file&outputFormat=json&hostName='.$_SERVER['HTTP_HOST'];
			$_REQUEST['searchfilter'].='&searchText='.str_replace(' ',',',$_REQUEST['searchText']);
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
				if($_REQUEST[$key]!='') $_REQUEST['searchfilter'].='&'.$key.'='.$_REQUEST[$key];
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

		exec('php5 /data/mapbender/http/geoportal/gaz.php "'.$_SESSION['mb_user_id'].'" "'.$unique.'" "'.$_REQUEST['searchText'].'" "'.$_SESSION['epsg'].'"  '.$shplus.' >/dev/null 2>&1 &');
		file_get_contents('http://localhost/mapbender/php/mod_callMetadata.php?searchId='.$unique.'&'.$_REQUEST['searchfilter']);
// Warten nach der ersten Suche
		$microsPerSecond = 1000000;
		usleep(($microsPerSecond/2));
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

// Meta
	$i='';
	$count=$count_meta=0;

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
			$filename=$parts[0].$key.'_1.'.$parts[1];
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
	if($count!=0) $i=$count;
	$i='<span id="search-count-meta">'.$i.'</span>';
	if($allready==0) {
		$i.='<span id="search-indicator-meta">'.$indicator.'</span>';
		$timer=true;
	}
	$output.=($cat=='meta')?'<li class="active">':'<li>';
	$output.='<a href="'.$url.'&amp;cat=meta">'.$L['Metadaten'].$i.'</a></li>';

	$output.='</ul>';

	$output.='<div class="search-container">';

	switch($cat) {
		case '':
			$startpage=true;

// Dienste
			$name='dienste';
			$ready[$name]=Dienste($dienstefile,5);
			if($ready[$name]==0) {
				$i='<span id="search-header-indicator-dienste">'.$indicator.'</span>';
				$timer=true;
			} else {
				$i='';
			}
			$output.='<h2><a href="'.$url.'&amp;cat=dienste" title="'.str_replace('###AREA###',$L['Dienste'],$L['alleErgebnisse']).'">'.$i.''.$L['Dienste'].'</a></h2>';
			$output.='
				<div id="search-container-'.$name.'">
				'.$out[$name].'
				</div>';

// Metadaten
			$name='meta';
			$ready[$name]=Meta($metafile,5);
			if($ready[$name]==0) {
				$i='<span id="search-header-indicator-meta">'.$indicator.'</span>';
				$timer=true;
			} else {
				$i='';
			}
			$output.='<h2><a href="'.$url.'&amp;cat=meta" title="'.str_replace('###AREA###',$L['Metadaten'],$L['alleErgebnisse']).'">'.$i.''.$L['Metadaten'].'</a></h2>';
			$output.='
				<div id="search-container-'.$name.'">
				'.$out[$name].'
				</div>';

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

		case 'meta':
			$startpage=false;

			$name='meta';
			$ready[$name]=Meta($metafile);
			if($ready[$name]==0) $timer=true;

			for($i=0;$i<$count_meta;$i++) {
				$output.='
				<div id="metastyles'.$i.'" style="display:none">
					<style type="text/css">
						.meta-search-container'.$i.' .search-header{background:#DFDFDF url(fileadmin/design/plus.png) 99.75% 3px no-repeat;cursor:pointer;padding-right:25px !important}
						.meta-search-container'.$i.' .search-item{display:none}
						.meta-search-container'.$i.' .search-pagecounter-container{display:none}
					</style>
				</div>';
			}
			$output.='
				<div id="search-container-'.$name.'">
				'.$out[$name].'
				</div>';
			break;
	}
	$output.='
			</div>
			<br class="clr" />
			<div id="searchresults"></div>';

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

	$output.='
		<script type="text/javascript">

		var cat=new Array();
		'.$jsoutput.'
		function openclose(id) {
			if(typeof cat[id]=="undefined" || cat[id]=="close") {
				cat[id]="open";
				var cssStr = ".meta-search-container"+id+" .search-header{background:#DFDFDF url(fileadmin/design/minus.png) 99.75% 3px no-repeat;cursor:pointer;padding-right:25px !important} .meta-search-container"+id+" .search-item{display:block} .meta-search-container"+id+" .search-pagecounter-container{display:block}";
			} else {
				cat[id]="close";
				var cssStr = ".meta-search-container"+id+" .search-header{background:#DFDFDF url(fileadmin/design/plus.png) 99.75% 3px no-repeat;cursor:pointer;padding-right:25px !important} .meta-search-container"+id+" .search-item{display:none} .meta-search-container"+id+" .search-pagecounter-container{display:none}";
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
			fenster=window.open("/portal/fileadmin/scripts/termsofuse.php?tou="+tou, "TermsOfUse", "width=500,height=500,left=100,top=100,scrollbars=yes,resizable=no");
			fenster.focus();
		}

		</script>';

	if($timer) {
		$output.='
		<script type="text/javascript">
			var search_filter="'.$ready['filter'].'";
			var search_meta="'.$ready['meta'].'";
			var search_dienste="'.$ready['dienste'].'";

			function AjaxSearch () {
				xajax_search("'.$unique.'","'.$_REQUEST['searchid'].'", "'.$cat.'", "'.$page.'", "'.$_SESSION['mb_user_id'].'", "'.t3lib_div::GPvar('L').'", search_meta,search_dienste,search_filter);
			}

			window.setTimeout("AjaxSearch()", 500);
		</script>
		';
	}
} else {
	$filterfile = '/data/mapbender/http/tmp/'.md5($unique).'_filter.json';
	$dienstefile = '/data/mapbender/http/tmp/'.md5($unique).'###SERVICE###.json';
	$metafile = '/data/mapbender/http/tmp/'.$unique.'_os.xml';

	if($language==1) $L=$Language['en'];
	else $L=$Language['de'];
	$LinkURL=$L['ErwSuchURL'];

	switch($cat) {
		case '':
			$startpage=true;

			$ready['dienste']=1;
			if($search_dienste==0) {
				$name='dienste';
				$ready[$name]=Dienste($dienstefile,5);
				if($ready[$name]) $out["service-filter"]=getServiceCat();
			}

			$ready['meta']=1;
			if($search_meta==0) {
				$name='meta';
				$ready[$name]=Meta($metafile,5);
			}

			break;
		case 'dienste':
			$startpage=false;
			$name='dienste';
			$ready[$name]=Dienste($dienstefile);
			if($ready[$name]) $out["service-filter"]=getServiceCat();
			break;
		case 'meta':
			$startpage=false;
			$name='meta';
			$ready[$name]=Meta($metafile);
			break;
	}
}

?>
