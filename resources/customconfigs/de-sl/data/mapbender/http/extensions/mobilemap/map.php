<?php
//Basic configuration of mapserver client
require_once(dirname(__FILE__)."/../../../conf/mobilemap.conf");
require_once(dirname(__FILE__)."/../../classes/class_connector.php");

//Fixes IPhone, Android 2.x 
if(strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'], 'iPod') || strstr($_SERVER['HTTP_USER_AGENT'], 'Android 2.')) {
 $scaleselect = "false";
}
else {
 $scaleselect = "true";
}

//WMC Anfrage Mapbendermodul
if (isset($_GET['wmcid'])) {
	$wmcid = htmlspecialchars($_GET["wmcid"]);
}
//Validate parameters for zooming to special extent for WMC
if(isset($_REQUEST["mb_myBBOX"]) && $_REQUEST["mb_myBBOX"] != ""){
	//Check for numerical values for BBOX
	$array_bbox = explode(',',$_REQUEST["mb_myBBOX"]);
	if ((is_numeric($array_bbox[0])) and (is_numeric($array_bbox[1])) and (is_numeric($array_bbox[2])) and (is_numeric($array_bbox[3])) ) {
		$mb_myBBOX = $_REQUEST["mb_myBBOX"];
		if(isset($_REQUEST["mb_myBBOXEpsg"])){
			//Check epsg
			$targetEpsg=intval($_REQUEST["mb_myBBOXEpsg"]);
			if (($targetEpsg >= 1) and ($targetEpsg <= 50001)) {
				#echo "is in the codespace of the epsg registry\n";
				$mb_myBBOXEpsg = $targetEpsg;
				
			} else {
				#echo "is outside\n";
				echo 'The REQUEST parameter mb_myBBOXEpsg is not in the epsg realm - please define another EPSG Code.';
				die();
			}
		}	
	} else {
		echo "The REQUEST parameters for mb_myBBOX are not numeric - please give numeric values!";
		die();
	} 
}

//SP: Content data from url TODO - exchange with mapbender class!
function get_data($url)
{
	$connector = new connector($url);
	return $connector->file;
} 

//SP: Feature Url Validierung
function feature_valid($feature_url)
{
	$myhtml = get_data($feature_url);                                              

	// get body von featureinfo
	$DOM = new DOMDocument;
	@$DOM->loadHTML($myhtml);
	$items = $DOM->getElementsByTagName('body');
	$controlstring = "false";
	for ($i = 0; $i < $items->length; $i++)
	{
		$controlstring = $items->item($i)->nodeValue;
	}
	$controlstring = trim($controlstring);                   
	$controlstring = str_replace("\t", '', $controlstring);                                                                                                    

	if (strlen($controlstring) > 80)
	{
		exit("true\n");	
	}
	exit("false\n");
}

//SP: JS Interface
if (isset($_POST['feature_url']))
{
	feature_valid($_POST['feature_url']);
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?php echo $apptitle; ?></title>
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0;">
<meta name="apple-mobile-web-app-capable" content="yes">
<link rel="apple-touch-icon" href="<?php echo $iPhoneIcon; ?>"/>
<link rel="shortcut icon" href="<?php echo $favIcon; ?>"/>
<link rel="stylesheet" href="../mobilemap/css/jquery.mobile.min.css" >
<link href="<?php echo $style_1; ?>" rel="stylesheet" >
<link href="<?php echo $style_2; ?>" rel="stylesheet" >
<?php if($googleapi){ ?>
<script src="http://maps.google.com/maps/api/js?sensor=false&region=DE"></script>
<?php } ?>
<script src="../mobilemap/js/proj4js.min.js" ></script>
<script src="../mobilemap/js/OpenLayers.mobile.min.js"></script>
<script src="../mobilemap/js/jquery.min.js"></script>
<script src="../mobilemap/js/jquery.mobile.min.js"></script>
<script src="../mobilemap/js/jquery-lang.js"></script>
<script src="../mobilemap/js/langpack/en.js" charset="utf-8" type="text/javascript"></script>
<script src="../mobilemap/backgroundlayer.php" charset="utf-8" type="text/javascript"></script>
<?php if($mapbendermod){ ?>
<!-- Mod Mapbender -->
<link href="../mobilemap/mod_mapbender/search.css" rel="stylesheet" >
<!-- /Mod Mapbedner -->
<?php } ?>
<?php if($devmode){ ?>
<script src="../mobilemap/js/dev/1_ngms_olextent.js" ></script>
<script src="../mobilemap/js/dev/2_ngms_global.js" ></script>
<?php if($mapbendermod){ ?>
<!-- Mod Mapbender -->
<script src="../mobilemap/mod_mapbender/searchobjects.js"></script>
<script src="../mobilemap/mod_mapbender/search.js"></script>
<!-- /Mod Mapbedner -->
<?php } else { ?>
<script src="../mobilemap/js/dev/3_ngms_layer.js" ></script>
<?php } ?>
<script src="../mobilemap/js/dev/4_ngms_base.js" ></script>
<script src="../mobilemap/js/dev/5_ngms_jq.js" ></script>
<?php } else{ ?>
<script src="../mobilemap/js/ngms_event.min.js" ></script>
<?php } ?>
<script type="text/javascript">
	window.lang = new jquery_lang_js();
	$().ready(function () {
		window.lang.run();
		changeLanguage('<?php echo $mylang; ?>',false);
		<?php if($wmcid != ''){ ?>
		var wmcurl = '<?php echo $mapbender_wmcurl.$wmcid; ?>';
		//add user defined bbox values if given
		<?php if(isset($mb_myBBOX) && isset($mb_myBBOXEpsg)){ ?>
			var wmcurl = wmcurl+"&mb_myBBOX="+<?php echo "'".$mb_myBBOX."'"; ?>+"&mb_myBBOXEpsg="+<?php echo "'".$mb_myBBOXEpsg."'"; ?>;
		<?php } ?>
		searchWmc(wmcurl);
		<?php } ?>
	});
</script>
</head>
<body>

<!-- Map Seite Start-->
<div data-role="page" id="mappage">

<!-- Popup GPS -->
  <div data-role="popup" id="popupMenu_gps" data-theme="a" class="ui-corner-all">
	<a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
    	<div data-role="header" data-theme="a" class="ui-corner-top">
      		<h1>Position</h1>
    	</div>
    	<div data-role="content" data-theme="a" class="ui-corner-bottom ui-content">
      		<div style="font-size:14px; padding: 12px;">Positionierung aktivieren<br>
        		<select id="gpsstatus" data-role="slider" data-mini="true" >
          			<option value="off" selected>Off</option>
          			<option value="on">On</option>
        		</select>
        		<br>Karte automatisch auf<br>
			Position zentrieren<br>
        		<select data-role="slider" id="gpscenter" data-mini="true" >
          			<option value="off" selected>Off</option>
          			<option value="on">On</option>
        		</select>
        		<div id="gpsmessage"></div>
      		</div>
    	</div>
  </div>
  <!-- Ende Popup GPS -->

  <div data-role="content">
	<div id="logo"><a href="map.php?lang=<?php echo $mylang; ?>" target="_self"><img src="img/logo.png" ></a></div>
    	<div id="map"></div>
	<div id="gpsinfo"></div>
   	<div id="navbutgroup">
      		<div id="ovbut" class="navbuttons" style="margin-top:2px"></div>
      		<div id="zoominbut" class="navbuttons" style="margin-top:2px"></div>
      		<div id="zoomoutbut" class="navbuttons" style="margin-top:2px"></div>
    	</div>

	<div id="navbutgrouptop">
		<?php if($mapbendermod){ ?>
	  	<!-- Mapbender -->
	  	<div id="mapbenderbut" class="navbuttons" style="float:left; margin-left:2px;"  ></div>
	  	<!-- Mapbender -->
	  	<?php } else { ?>
      		<div id="layerbut" class="navbuttons" style="float:left; margin-left:2px;"  ></div>
	  	<?php } ?>
      		<div id="searchbut" class="navbuttons" style="float:left; margin-left:2px;"  ></div>
      		<div id="locatebut" class="navbuttons" style="float:left; margin-left:2px;"  ></div>
      		<div id="menubut" class="navbuttons" style="float:left; margin-left:2px;"  ></div>
	
    	</div>

    	<div id="markerhint">
      		<div id="xheader"></div>
      		<div id="xcontent"></div>
    	</div>

   	<div id="measurehint" >
      		<div id="mheader"><span lang="de">Messen</span></div>
      		<div id="measureoutput"></div>
    	</div>
    
    	<div id="scaleline"></div>
    	<div id="copyright"><span lang="de"><?php echo $copyright;  ?></span></div>
    	<div id="LoadingPanel"></div>
    	<div id="zoomscale">
      		<select name="selectzoom" id="selectzoom" onChange="changeScale(this.value);" data-inline="true" data-mini="true" data-native-menu="<?php echo $scaleselect;  ?>" >
      		</select>
      		<label for="test"></label>
    	</div>

	<div data-role="popup" id="popupMenu" data-overlay-theme="a" data-theme="a" style="max-width:2280px;" class="ui-corner-all">
		<a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
		<div data-role="header" data-theme="a" class="ui-corner-top">
			<h1>Tools</h1>
		</div>
		<div data-role="content" data-theme="d" class="ui-corner-bottom ui-content"><br>
			<table border="0" cellspacing="0" cellpadding="14">
  				<tr>
    					<td><div id="helpbut" class="navbuttons" ></div></td>
    					<td><div id="gearbut" class="navbuttons" ></div></td>
  				</tr>
  				<tr>
    					<td><div id="measurelinebut" class="navbuttons" ></div></td>
    					<td><div id="measurepolybut" class="navbuttons" ></div></td>
  				</tr>
			</table>
		</div>
	</div>

  </div>
  <!-- /content --> 
</div>
<!-- /Map Seite Ende--> 


<?php if($mapbendermod){ ?>
<!-- Mapbender Seite Start-->
<div data-role="page" id="mod_mapbender" >
  <div data-role="header" data-position="fixed" data-theme="d"> <a href="#" class="addToMapBut" data-icon="arrow-l"><span lang="de">Karte</span></a>
    <h1><span lang="de">Katalogsuche</span></h1>
  </div>
  <!-- /header -->
  <div data-role="content" style="padding: 10px; overflow:hidden;" >

    <div data-role="collapsible" >
      <h3><span lang="de">Hintergrundkarte</span></h3>
      <div id="baselayers"></div>
    </div>
    <label for="mapbendersearchfield"><span lang="de">Layer suchen:</span> </label>
    <input type="search" name="mapbendersearchfield" id="mapbendersearchfield" value="" />
    <input type="submit" value="Suchen" lang="de"  id="mapbendersearchformbut" data-icon="arrow-r" data-iconpos="right" />
	
	<ul data-role="listview" data-inset="true"> 
		<li data-theme="b">Ihre Auswahl</li>
		<li id="ownlist"></li>
	</ul>

	<div data-role="popup" id="preview" data-overlay-theme="a" data-theme="a" style="max-width:2280px;" class="ui-corner-all">
		<a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
		<div data-role="header" data-theme="a" class="ui-corner-top">
			<a href="#" id="preview_zoom" data-icon="search"><span lang="de">Karte</span></a>
			<h3>&nbsp;</h3>
		</div>
		<div data-role="content" data-theme="d" class="ui-corner-bottom ui-content"></div>
	</div>

	<div data-role="popup" id="info" data-overlay-theme="a" data-theme="a" style="max-width:2280px;" class="ui-corner-all">
		<a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
		<div data-role="header" data-theme="a" class="ui-corner-top"><h3><span lang="de">Meldung</span></h3></div>
		<div data-role="content" data-theme="d" class="ui-corner-bottom ui-content">
			<table border="0" cellspacing="0" cellpadding="14">
 			 	<tr><td id="info_content"></td></tr>
			</table>	
		</div>
	</div>

	<ul data-role="listview" data-inset="true" id="resultlist"></ul>
	
  </div>
</div>
<!-- /Mapbender Seite Ende--> 
<?php } else { ?>
<!-- Layer Seite nicht dynamisch (Beispieleinträge vgl. 3_ngms_layer.js) Start-->
<div data-role="page" id="layerpage" >
  <div data-role="header" data-position="fixed" > <a href="#" class="mapbackbut" data-icon="arrow-l"><span lang="de">Karte</span></a>
    <h1><span lang="de">Ebenen</span></h1>
  </div>
  <!-- /header -->
  <div data-role="content" style="padding: 10px" >
    <div data-role="collapsible" >
      <h3><span lang="de">Hintergrundkarte</span></h3>
	 <div class="checkrow" >
        <div class="baselayer_check" id="atkis_praes_tms" ><span lang="de">Saarland Zusammenstellung</span></div>
      </div>
      <div class="clearfix"></div>
      <div class="checkrow" >
        <div class="baselayer_check" id="luftbilder" ><span lang="de">Luftbilder</span></div>
      </div>
      <div class="clearfix"></div>
      <div class="checkrow" >
        <div class="baselayer_check" id="grenze_leer" ><span lang="de">keine Hintergrundkarte</span></div>
      </div>
      <div class="clearfix"></div>
    </div>
    <div data-role="collapsible" >
      <h3><span lang="de">Overlays</span></h3>
      <div class="checkrow" >
        <div class="layer_check" id="likar" ><span lang="de">Liegenschaftskarte</span></div>
      </div>
      <div class="clearfix"></div>
      <div class="checkrow" >
        <div class="query_check" id="naturschutzgebiet_query" >&nbsp;</div>
        <div class="layer_check" id="naturschutzgebiet" ><span lang="de">Naturschutzgebiete</span></div>
      </div>
      <div class="clearfix"></div>
    </div>
    <div class="infobox" ><span lang="de">Hinweis: Um eine Ebene abzufragen aktivieren Sie die Info-Option und tapen danach in der Karte auf das Objekt der Ebene.</span> </div>
    <div class="query_check" id="dhm_query" >&nbsp;</div>
    <div class="layer_nocheck" ><span lang="de">Abfrage Höhe + GPS</span></div>
    <div class="clearfix"></div>
    <div class="clearfix"></div>
    <input name="queryselect" id="queryselect" type="hidden" value="dhm">
    <br>
    <div class="infobox" ><span lang="de">Änderung der Ebenen wechselt direkt zur Kartenansicht</span></div>
    <div data-role="fieldcontain" style="margin:1px; padding:1px;">
      <select name="slider" id="autolayerchange" data-role="slider">
        <option value="off" selected >off</option>
        <option value="on">on</option>
      </select>
    </div>
  </div>
</div>
<!-- /Layer Seite Ende--> 
<?php } ?>

<!-- Search Seite Start-->
<div data-role="page" id="searchpage" >
  <div data-role="header" data-position="fixed" > <a href="#" class="mapbackbut" data-icon="arrow-l"><span lang="de">Karte</span></a>
    <h1><span lang="de">Suche</span></h1>
  </div>
  <!-- /header -->
  <div data-role="content" style="padding: 10px" >
    <div>
      <label for="searchfield"><span lang="de">Ort suchen:</span> </label>
      <input type="search" name="searchfield" id="searchfield" value="" />
      <input type="submit" lang="de" value="Suchen" id="searchformbut" data-icon="arrow-r" data-iconpos="right" />
    </div>
    <div id="mygooglelink" > </div>
    <div id="searchdbresult" style="margin-top:20px;" >
      <ul data-role="listview" id= "search_results" data-theme="a" data-divider-theme="a" >
      </ul>
    </div>
    <div id="mygooglemap" > </div>
  </div>
</div>
<!-- /Search Seite Ende--> 

<!-- FeatureInfo Seite Start-->
<div data-role="page" id="featureinforesult" style="height: 100%">
  <div data-role="header" data-position="fixed" > <a href="#" class="mapbackbut" data-icon="arrow-l" onclick='$("#ficontentdiv").empty();'><span lang="de">Karte</span></a>
    <h1><span lang="de">Sachdatenanzeige</span></h1>
  </div>
  <!-- /header -->
  <!-- //SP: Feature Info Liste -->
	<div data-role="content" style="padding: 10px; overflow:hidden;">
		<ul data-role="listview" data-inset="true"> 
			<li data-theme="b">Objekte</li>
			<li id="featurelist"></li>
		</ul>
	</div>
 <!-- <div data-role="content" style="padding: 10px" >-->
 <!--	<div id="ficontentdiv" style="height: 500px"> </div>-->
 <!-- </div>-->
</div>
<!-- /FeatureInfo Seite Ende--> 

<!-- Hilfe Seite Start-->
<div data-role="page" id="helppage">
  <div data-role="header" data-position="fixed" > <a href="#" class="mapbackbut" data-icon="arrow-l" ><span lang="de">Karte</span></a>
    <h1><span lang="de">Hilfe / Info</span></h1>
  </div>
  <div data-role="content" style="padding: 10px">

    <div id="helpdiv"> </div>
    </div>
  <!-- /content --> 
</div>
<!-- /Hilfe Seite Ende--> 


<!-- Einstellungen Start-->
<div data-role="page" id="gearpage">
  <div data-role="header" data-position="fixed" > <a href="#" class="mapbackbut" data-icon="arrow-l"><span lang="de">Karte</span></a>
    <h1><span lang="de">Einstellungen</span></h1>
  </div>
  <div data-role="content" style="padding: 10px">
        <strong><span lang="de">App Einstellungen</span></strong> 
      <br>
      <label for="select-lang" class="select"><span lang="de">Sprache:</span></label>
      <select name="select-lang" id="select-lang" onChange="changeLanguage(this.value,true);" data-icon="gear" data-inline="true" data-native-menu="false">
        <option value="de" lang="de">Deutsch</option>
        <option value="en" lang="de">English</option>
      </select>
      <br>
      <label for="select-hand" class="select"><span lang="de">Händigkeit:</span></label>
      <select name="select-hand" id="select-hand" onChange="changeHand(this.value);" data-icon="gear" data-inline="true" data-native-menu="false">
        <option value="r" lang="de">Rechtshänder</option>
        <option value="l" lang="de">Linkshänder</option>
      </select>
      <br>
      <label for="select-feature-info" class="select"><span lang="de">Sachdatenanzeige:</span></label>
      <select name="select-feature-info" id="select-feature-info" onChange="changeFeatureInfo(this.value);" data-icon="gear" data-inline="true" data-native-menu="false">
        <option value="n" lang="de">Neues Fenster</option>
        <option value="p" lang="de">Popup</option>
      </select>
      <br>
      <div class="apptools"> 
      Browserinfo:<br>
    <?php echo $_SERVER['HTTP_USER_AGENT']; ?> <br>
		<a href="#" onClick="checkZindex();">Testfunktion</a><br>
    </div>
    </div>
  </div>
  <!-- /content --> 
</div>
<!-- /Loading Seite Ende-->
</body>
</html>
