<?php

#Script to call this class: http://www.geoportal.rlp.de/mapbender/php/mod_callMetadata.php
#Class for getting results out of the mapbender service registry 
#Resulttypes: WMS, WMS-Layer, (WFS), WFS-Featurtyps, WFS-Conf, WMC, GeoRSS-Feeds, ...
#Possible filters: registrating organizations, time, bbox (fully inside, intersects, fully outside), ISO Topic Categories, INSPIRE themes, INSPIRE: keywords, classification of data/service ... - maybe relevant for the german broker not for one instance, quality and actuality (maybe spatial and temporal), bbox, deegree of conformity with ir, access and use constraints, responsible parties - maybe one is enough? We must have a look at the INSPIRE Metadata IR
#Metadata we need to fullfil the demands of INSPIRE:
#1. INSPIRE conformity classification for WMS/WFS/WCS
#2. Temporal Extents at WMS/WMS-Layer/WFS/WFS-Featuretype levels - for datasets if demanded - til now there is no demand defined in the guidance-paper for metadata ir
#3. Classified access and use contraints - which classes? - Check IR Data Sharing and IR Metadata
#4. 
#Every ressource which should be send to INSPIRE can be filtered - but is not neccessary for a standardized approach
#Another problem is the ranking of the different ressources. The ranking should be homogeneus. 
#Till now we rank the using of WMS Layers when Caps are requested and when s.o. load one layer into the geoportal.
#The same things have to be done for the wfs-conf, wmc and georssfeeds
#The searching for metadata should be parallel done. We need different classes for doing the search. They should be requested by one central class (class_metadata.php).
#Classes for filtering after the results have been send to the portal:
#1. ISO Topic Categories
#2. INSPIRE Themes
#3. Access and use classification
#4. departments which provides the ressources - we need the new concept for the administration of this departments - store the addresses in the group table and give the relation - originating group in table mb_user_mb_group 
#Cause we have a authorization layer, we need the id of the requesting user which is defined in the session. If no session is set, it should be the anonymous user of the portal.
#We need a parameter for internationalization - it should be send with the search request! Some of the Classes can be provided with different languages.
#WMC and GeoRSS-Feeds have no or a to complex authorization info - maybe we need to test if wmc consists of info which is fully or only partually available to the anonymous user. 

require_once(dirname(__FILE__) . "/../../core/globalSettings.php");
require_once(dirname(__FILE__) . "/class_administration.php");
require_once(dirname(__FILE__) . "/class_mb_exception.php");
require_once(dirname(__FILE__) . "/class_json.php");
require_once(dirname(__FILE__) . "/../php/mod_getDownloadOptions.php");

//definition for the things which are common to all kind of metadata ressources

class searchMetadata
{

    var $userId;
    var $searchId;
    var $searchText;
    var $registratingDepartments;
    var $isoCategories;
    var $inspireThemes;
    var $customCategories;
    var $timeBegin;
    var $timeEnd;
    var $regTimeBegin;
    var $regTimeEnd;
    var $maxResults;
    var $searchBbox;
    var $searchTypeBbox;
    var $accessRestrictions;
    var $languageCode;
    var $searchStartTime;
    var $searchView;
    var $searchURL;
    var $searchEPSG;
    var $searchResources;
    var $searchPages;
    var $outputFormat;
    var $resultTarget;
    var $tempFolder;
    var $orderBy;
    var $hostName;
    var $resourceIds;
    var $restrictToOpenData;
    var $originFromHeader;

    function __construct($userId, $searchId, $searchText, $registratingDepartments, $isoCategories, $inspireThemes, $timeBegin, $timeEnd, $regTimeBegin, $regTimeEnd, $maxResults, $searchBbox, $searchTypeBbox, $accessRestrictions, $languageCode, $searchEPSG, $searchResources, $searchPages, $outputFormat, $resultTarget, $searchURL, $customCategories, $hostName, $orderBy, $resourceIds, $restrictToOpenData, $originFromHeader)
    {
        $this->userId = (integer) $userId;
        $this->searchId = $searchId;
        $this->searchText = $searchText;
        $this->registratingDepartments = $registratingDepartments; //array with ids of the registrating groups in the mb database
        $this->registratingDepartmentsArray = explode(",", $this->registratingDepartments);
        $this->isoCategories = $isoCategories;
        $this->inspireThemes = $inspireThemes;
        $this->customCategories = $customCategories;
        $this->timeBegin = $timeBegin;
        $this->timeEnd = $timeEnd;
        $this->regTimeBegin = $regTimeBegin;
        $this->regTimeEnd = $regTimeEnd;
        $this->maxResults = (integer) $maxResults;
        $this->searchBbox = $searchBbox;
        $this->searchTypeBbox = $searchTypeBbox;
        $this->accessRestrictions = $accessRestrictions;
        $this->languageCode = $languageCode;
        $this->searchEPSG = $searchEPSG;
        $this->searchResources = $searchResources;
        $this->searchPages = $searchPages;
        $this->outputFormat = $outputFormat;
        $this->resultTarget = $resultTarget;
        $this->searchURL = $searchURL;
        $this->hostName = $hostName;
        $this->orderBy = $orderBy;
        $this->resourceIds = $resourceIds;
        if ($restrictToOpenData === "true") {
            $this->restrictToOpenData = true;
        } else {
            $this->restrictToOpenData = false;
        }
        $this->originFromHeader = $originFromHeader;
        //definitions for generating tagClouds
        $this->maxObjects = 15;
        $this->maxFontSize = 30;
        $this->maxWeight = 0;
        $this->scale = 'linear';
        $this->minFontSize = 10;
        $this->tempFolder = TMPDIR; //TODO define another path - maybe the one which is given in mapbender.conf
        if ($this->outputFormat == 'json') {
            $this->json = new Mapbender_JSON;
        }
        $this->accessableLayers = NULL;
        //set a time to find time consumers
        $this->searchStartTime = $this->microtime_float();
        //Defining of the different database categories		
        $this->resourceClassifications = array();
        $this->resourceClassifications[0]['title'] = "ISO 19115"; //TODO: define the translations somewhere? - This is done in call_metadata.php before. Maybe we can get them from there? - It will be shown in the rightside categories table
        $this->resourceClassifications[0]['tablename'] = 'md_topic_category';
        $this->resourceClassifications[0]['requestName'] = 'isoCategories';
        $this->resourceClassifications[0]['id_wms'] = 'layer_id';
        $this->resourceClassifications[0]['id_wfs'] = 'featuretype_id';
        $this->resourceClassifications[0]['id_wmc'] = 'wmc_serial_id';
        $this->resourceClassifications[0]['id_dataset'] = 'metadata_id';
        $this->resourceClassifications[0]['relation_wms'] = 'layer_md_topic_category';
        $this->resourceClassifications[0]['relation_wfs'] = 'featuretype_md_topic_category';
        $this->resourceClassifications[0]['relation_wmc'] = 'wmc_md_topic_category';
        $this->resourceClassifications[0]['relation_dataset'] = 'mb_metadata_md_topic_category';
//TODO: define this in mapbender

        $this->resourceClassifications[1]['title'] = "INSPIRE"; //TODO: define the translations somewhere? - This is done in call_metadata.php before. Maybe we can get them from there? - It will be shown in the rightside categories table
        $this->resourceClassifications[1]['tablename'] = 'inspire_category';
        $this->resourceClassifications[1]['requestName'] = 'inspireThemes';
        $this->resourceClassifications[1]['id_wms'] = 'layer_id';
        $this->resourceClassifications[1]['id_wfs'] = 'featuretype_id';
        $this->resourceClassifications[1]['id_wmc'] = 'wmc_serial_id';
        $this->resourceClassifications[1]['id_dataset'] = 'metadata_id';
        $this->resourceClassifications[1]['relation_wms'] = 'layer_inspire_category';
        $this->resourceClassifications[1]['relation_wfs'] = 'featuretype_inspire_category';
        $this->resourceClassifications[1]['relation_wmc'] = 'wmc_inspire_category';
        $this->resourceClassifications[1]['relation_dataset'] = 'mb_metadata_inspire_category';
//TODO: define this in mapbender
        switch ($this->languageCode) {
            case "de":
                $this->resourceClassifications[2]['title'] = "Sonstige"; //TODO: define the translations somewhere? - This is done in call_metadata.php before. Maybe we can get them from there? - It will be shown in the rightside categories table
                break;
            case "en":
                $this->resourceClassifications[2]['title'] = "Custom";
                break;
            case "fr":
                $this->resourceClassifications[2]['title'] = "Personnaliser";
                break;
            default:
                $this->resourceClassifications[2]['title'] = "Custom";
                break;
        }
        $this->resourceClassifications[2]['tablename'] = 'custom_category';
        $this->resourceClassifications[2]['requestName'] = 'customCategories';
        $this->resourceClassifications[2]['id_wms'] = 'layer_id';
        $this->resourceClassifications[2]['id_wfs'] = 'featuretype_id';
        $this->resourceClassifications[2]['id_wmc'] = 'wmc_serial_id';
        $this->resourceClassifications[2]['id_dataset'] = 'metadata_id';
        $this->resourceClassifications[2]['relation_wms'] = 'layer_custom_category';
        $this->resourceClassifications[2]['relation_wfs'] = 'featuretype_custom_category';
        $this->resourceClassifications[2]['relation_wmc'] = 'wmc_custom_category';
        $this->resourceClassifications[1]['relation_dataset'] = 'mb_metadata_custom_category';
        //TODO: define this in mapbender
        //Defining of the different result categories		
        $this->resourceCategories = array();
        $this->resourceCategories[0]['name'] = 'WMS';
        $this->resourceCategories[1]['name'] = 'WFS';
        $this->resourceCategories[2]['name'] = 'WMC';
        $this->resourceCategories[3]['name'] = 'DAD';
        $this->resourceCategories[4]['name'] = 'DATASET';
        switch ($this->languageCode) {
            case 'de':
                $this->resourceCategories[0]['name2show'] = 'Darstellungsdienste';
                $this->resourceCategories[1]['name2show'] = 'Such- und Download- und Erfassungsmodule';
                $this->resourceCategories[2]['name2show'] = 'Kartenzusammenstellungen';
                $this->resourceCategories[3]['name2show'] = 'KML/Newsfeeds';
                $this->resourceCategories[4]['name2show'] = 'Datensätze';
                $this->keywordTitle = 'Schlagwortliste';
                break;
            case 'en':
                $this->resourceCategories[0]['name2show'] = 'Viewingservices';
                $this->resourceCategories[1]['name2show'] = 'Search- and Downloadservices';
                $this->resourceCategories[2]['name2show'] = 'Combined Maps';
                $this->resourceCategories[3]['name2show'] = 'KML/Newsfeeds';
                $this->resourceCategories[4]['name2show'] = 'Datasets';
                $this->keywordTitle = 'Keywordlist';
                break;
            case 'fr':
                $this->resourceCategories[0]['name2show'] = 'Services de visualisation';
                $this->resourceCategories[1]['name2show'] = 'Services de recherche et de téléchargement';
                $this->resourceCategories[2]['name2show'] = 'Cartes composées';
                $this->resourceCategories[3]['name2show'] = 'KML/Newsfeeds';
                $this->resourceCategories[4]['name2show'] = 'Datasets';
                $this->keywordTitle = 'Keywordlist';
                break;
            default:
                $this->resourceCategories[0]['name2show'] = 'Darstellungsdienste';
                $this->resourceCategories[1]['name2show'] = 'Such- und Download- und Erfassungsmodule';
                $this->resourceCategories[2]['name2show'] = 'Kartenzusammenstellungen';
                $this->resourceCategories[3]['name2show'] = 'KML/Newsfeeds';
                $this->resourceCategories[4]['name2show'] = 'Datensätze';
                $this->keywordTitle = 'Schlagwortliste';
        }
        //not needed til now - maybe usefull for georss output
        if ($this->outputFormat == "xml") {
            //Initialize XML documents
            if (isset($this->searchResources) & strtolower($this->searchResources) === "wms") {
                $this->wmsDoc = new DOMDocument('1.0');
            }
            if (isset($this->searchResources) & strtolower($this->searchResources) === "wfs") {
                $this->wfsDoc = new DOMDocument('1.0');
                $this->generateWFSMetadata($this->wfsDoc);
            }
            if (isset($this->searchResources) & strtolower($this->searchResources) === "wmc") {
                $this->wmcDoc = new DOMDocument('1.0');
                $this->generateWMCMetadata($this->wmcDoc);
            }
            if (isset($this->searchResources) & strtolower($this->searchResources) === "georss") {
                $this->georssDoc = new DOMDocument('1.0');
            }
            if (isset($this->searchResources) & strtolower($this->searchResources) === "dataset") {
                $this->datasetDoc = new DOMDocument('1.0');
                $this->generateDatasetMetadata($this->datasetDoc);
            }
        }

        if ($this->outputFormat === "json") {
            $this->e = new mb_notice("orderBy old: " . $this->orderBy);
            if (isset($this->searchResources) & strtolower($this->searchResources) === "wfs") {
                $this->databaseIdColumnName = 'featuretype_id';
                $this->databaseTableName = 'wfs_featuretype';
                //$this->keywordRelation = 'wfs_featuretype_keyword';
                $this->searchView = 'search_wfs_view';
                $this->whereStrCatExtension = " AND custom_category.custom_category_hidden = 0";
                switch ($this->orderBy) {
                    case "rank":
                        $this->orderBy = " ORDER BY wfs_id,featuretype_id,wfs_conf_id ";
                        break;
                    case "id":
                        $this->orderBy = " ORDER BY wfs_id,featuretype_id,wfs_conf_id ";
                        break;
                    case "title":
                        $this->orderBy = " ORDER BY featuretype_title ";
                        break;
                    case "date":
                        $this->orderBy = " ORDER BY wfs_timestamp DESC ";
                        break;
                    default:
                        $this->orderBy = " ORDER BY wfs_id,featuretype_id,wfs_conf_id ";
                }

                $this->resourceClasses = NULL;
                $this->generateWFSMetadata($this->wfsDoc);
            }
            if (isset($this->searchResources) & strtolower($this->searchResources) === "wms") {
                $this->databaseIdColumnName = 'layer_id';
                $this->databaseTableName = 'layer';
                //$this->keywordRelation = 'layer_keyword';
                $this->searchView = 'wms_search_table';
                //$this->searchView = 'search_wms_view';
                $this->whereStrCatExtension = " AND custom_category.custom_category_hidden = 0";
                switch ($this->orderBy) {
                    case "rank":
                        $this->orderBy = " ORDER BY load_count DESC";
                        break;
                    case "id":
                        $this->orderBy = " ORDER BY wms_id,layer_pos ASC";
                        break;
                    case "title":
                        $this->orderBy = " ORDER BY layer_title ";
                        break;
                    case "date":
                        $this->orderBy = " ORDER BY wms_timestamp DESC ";
                        break;
                    default:
                        $this->orderBy = " ORDER BY load_count DESC";
                }

                $this->resourceClasses = array(0, 1, 2);
                $this->generateWMSMetadata($this->wmsDoc);
            }
            if (isset($this->searchResources) & strtolower($this->searchResources) === "wmc") {
                $this->searchView = 'search_wmc_view';
                $this->databaseIdColumnName = 'wmc_serial_id';
                $this->databaseTableName = 'wmc';
                //the following is needed to give a special filter to the custom cat table!
                $this->whereStrCatExtension = " AND custom_category.custom_category_hidden = 0";

                switch ($this->orderBy) {
                    case "rank":
                        $this->orderBy = " ORDER BY load_count DESC ";
                        break;
                    case "id":
                        $this->orderBy = " ORDER BY wmc_id";
                        break;
                    case "title":
                        $this->orderBy = " ORDER BY wmc_title ";
                        break;
                    case "date":
                        $this->orderBy = " ORDER BY wmc_timestamp DESC ";
                        break;
                    default:
                        $this->orderBy = " ORDER BY wmc_title ";
                }

                $this->resourceClasses = array(0, 1, 2); #TODO adopt to count classifications
                #$this->resourceClasses = array();		
                $this->generateWMCMetadata($this->wmcDoc);
            }
            if (isset($this->searchResources) & strtolower($this->searchResources) === "dataset") {
                $this->databaseIdColumnName = 'metadata_id';
                $this->databaseTableName = 'mb_metadata';
                //$this->keywordRelation = 'layer_keyword';
                //$this->searchView = 'wms_search_table';
                $this->searchView = 'search_dataset_view';
                $this->whereStrCatExtension = " AND custom_category.custom_category_hidden = 0";
                switch ($this->orderBy) {
                    /* case "rank":
                      $this->orderBy = " ORDER BY load_count DESC";
                      break; */
                    case "id":
                        $this->orderBy = " ORDER BY metadata_id ASC";
                        break;
                    case "title":
                        $this->orderBy = " ORDER BY title ";
                        break;
                    case "date":
                        $this->orderBy = " ORDER BY last_changed DESC ";
                        break;
                    default:
                        //$this->orderBy = " ORDER BY load_count DESC";
                        $this->orderBy = " ORDER BY title DESC";
                }

                $this->resourceClasses = array(0, 1, 2);
                $this->generateDatasetMetadata($this->datasetDoc);
            }
        }
        $this->e = new mb_notice("orderBy new: " . $this->orderBy);
    }

    private function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }

    private function generateXMLHead($xmlDoc)
    {
        $xmlDoc->encoding = CHARSET;
        $result = $xmlDoc->createElement("result");
        $xmlDoc->appendChild($result);
        //Result Count
        $overLimit = $xmlDoc->createElement("overLimit");
        $result->appendChild($overLimit);
        //$tr_text = $xmlDoc->createTextNode($this->isOverLimit);
        $tr_text = $xmlDoc->createTextNode("really?");
        $overLimit->appendChild($tr_text);
        $rd = $xmlDoc->createElement("redirect");
        $result->appendChild($rd);
        $trd = $xmlDoc->createTextNode("not yet ready...");
        $rd->appendChild($trd);
    }

    private function generateXMLFoot($xmlDoc)
    {
        $results = $xmlDoc->getElementsByTagName("result");
        foreach ($results as $result) {
            $result->appendChild($ready);
        }
    }

    private function flipDiagonally($arr)
    {
        $out = array();
        foreach ($arr as $key => $subarr) {
            foreach ($subarr as $subkey => $subvalue) {
                $out[$subkey][$key] = $subvalue;
            }
        }
        return $out;
    }

    private function generateWFSMetadataJSON($res, $n)
    {
        //initialize object
        $this->wfsJSON = new stdClass;
        $this->wfsJSON->wfs = (object) array(
                    'md' => (object) array(
                        'nresults' => $n,
                        'p' => $this->searchPages,
                        'rpp' => $this->maxResults
                    ),
                    'srv' => array()
        );

        //read out records
        $serverCount = 0;
        $wfsMatrix = db_fetch_all($res);
        //sort result for accessing the right services
        $wfsMatrix = $this->flipDiagonally($wfsMatrix);
        //TODO check if order by db or order by php is faster! 
        #array_multisort($wfsMatrix['wfs_id'], SORT_ASC,$wfsMatrix['featuretype_id'], SORT_ASC,$wfsMatrix['wfs_conf_id'], SORT_ASC); //have some problems - the database version is more stable
        #print_r($wfsMatrix);
        $wfsMatrix = $this->flipDiagonally($wfsMatrix);
        //read out first server entry - maybe this a little bit timeconsuming TODO
        $j = 0; //count identical wfs_id => double featuretype
        $l = 0; //index featuretype and or modul per wfs
        $m = 0; //index modul per featuretype
        for ($i = 0; $i < count($wfsMatrix); $i++) {
            $this->wfsJSON->wfs->srv[$i - $j]->id = $wfsMatrix[$i]['wfs_id'];
            $this->wfsJSON->wfs->srv[$i - $j]->title = $wfsMatrix[$i]['wfs_title'];
            $this->wfsJSON->wfs->srv[$i - $j]->abstract = $wfsMatrix[$i]['wfs_abstract'];
            $this->wfsJSON->wfs->srv[$i - $j]->date = date("d.m.Y", $wfsMatrix[$i]['wfs_timestamp']);
            $this->wfsJSON->wfs->srv[$i - $j]->respOrg = $wfsMatrix[$i]['mb_group_name'];
            $this->wfsJSON->wfs->srv[$i - $j]->logoUrl = $wfsMatrix[$i]['mb_group_logo_path'];
            $this->wfsJSON->wfs->srv[$i - $j]->mdLink = "http://" . $this->hostName . "/mapbender/geoportal/showWFSMetadata.php?id=" . $wfsMatrix[$i]['wfs_id'];
            $spatialSource = "";
            $stateOrProvince = $wfsMatrix[$i]['administrativearea'];
            if ($stateOrProvince == "NULL" || $stateOrProvince == "") {
                $spatialSource = $wfsMatrix[$i]['country'];
            } else {
                $spatialSource = $wfsMatrix[$i]['administrativearea'];
            }
            $this->wfsJSON->wfs->srv[$i - $j]->iso3166 = $spatialSource;
            //check if a disclaimer has to be shown and give the relevant symbol
            //$this->wfsJSON->wfs->srv[$i-$j]->tou = $wfsMatrix[$i]['termsofuse'];
            list($hasConstraints, $symbolLink) = $this->hasConstraints("wfs", $wfsMatrix[$i]['wfs_id']);
            $this->wfsJSON->wfs->srv[$i - $j]->hasConstraints = $hasConstraints;
            $this->wfsJSON->wfs->srv[$i - $j]->symbolLink = $symbolLink;
            //TODO check the field accessconstraints - which should be presented?
            $this->wfsJSON->wfs->srv[$i - $j]->status = NULL; //$wfsMatrix[$i][''];
            $this->wfsJSON->wfs->srv[$i - $j]->avail = NULL; //$wfsMatrix[$i][''];
            $this->wfsJSON->wfs->srv[$i - $j]->logged = NULL; //$wfsMatrix[$i][''];
            $this->wfsJSON->wfs->srv[$i - $j]->price = NULL; //$wfsMatrix[$i][''];
            $this->wfsJSON->wfs->srv[$i - $j]->nwaccess = NULL; //$wfsMatrix[$i][''];
            $this->wfsJSON->wfs->srv[$i - $j]->bbox = array(-180.0, -90.0, 180.0, 90.0); //$wfsMatrix[$i][''];
            //if featuretype hasn't been created - do it
            if (!isset($this->wfsJSON->wfs->srv[$i - $j]->ftype)) {
                $this->wfsJSON->wfs->srv[$i - $j]->ftype = array();
            }
            //fill in featuretype infos
            $this->wfsJSON->wfs->srv[$i - $j]->ftype[$l - $m]->id = (integer) $wfsMatrix[$i]['featuretype_id'];
            $this->wfsJSON->wfs->srv[$i - $j]->ftype[$l - $m]->title = $wfsMatrix[$i]['featuretype_title'];
            $this->wfsJSON->wfs->srv[$i - $j]->ftype[$l - $m]->abstract = $wfsMatrix[$i]['featuretype_abstract'];
            $this->wfsJSON->wfs->srv[$i - $j]->ftype[$l - $m]->mdLink = "http://" . $this->hostName . "/mapbender/geoportal/showWFeatureTypeMetadata.php?id=" . $wfsMatrix[$i]['featuretype_id'];
            $this->wfsJSON->wfs->srv[$i - $j]->ftype[$l - $m]->geomtype = $wfsMatrix[$i]['element_type'];
            $this->wfsJSON->wfs->srv[$i - $j]->ftype[$l - $m]->bbox = array(-180.0, -90.0, 180.0, 90.0); //TODO: $wfsMatrix[$i]['bbox'];
            //give info for inspire categories - not relevant for other services or instances of mapbender TODO: comment it if the mapbender installation is not used to generate inspire output
            if (isset($wfsMatrix[$i]['md_inspire_cats']) & ($wfsMatrix[$i]['md_inspire_cats'] != '')) {
                $this->wfsJSON->wfs->srv[$i - $j]->ftype[$l - $m]->inspire = 1;
            } else {
                $this->wfsJSON->wfs->srv[$i - $j]->ftype[$l - $m]->inspire = 0;
            }
            //fill in categories
            #if (!isset($this->wfsJSON->wfs->srv[$i-$j]->ftype[$l-$m]->cat)) {
            #	$this->wfsJSON->wfs->srv[$i-$j]->ftype[$l-$m]->cat = array();
            #}
            #if (isset($wfsMatrix[$i]['iso_categories'])) {
            #}
            //if (isset($wfsMatrix[$i]['inspire_category'])) {
            //TODO write the categories as JSON into Database!
            #$this->wfsJSON->wfs->srv[$i-$j]->ftype[$l-$m]->cat[0]->type = 'INSPIRE Kategorie';
            #$this->wfsJSON->wfs->srv[$i-$j]->ftype[$l-$m]->cat[0]->value = 'Umwelt';
            #$this->wfsJSON->wfs->srv[$i-$j]->ftype[$l-$m]->cat[0]->symbol = 'umwelt.png';
            //}
            #if (isset($wfsMatrix[$i]['custom_categories'])) {
            #}
            //if modul hasn't been created - do it
            if (!isset($this->wfsJSON->wfs->srv[$i - $j]->ftype[$l - $m]->modul)) {
                $this->wfsJSON->wfs->srv[$i - $j]->ftype[$l - $m]->modul = array();
            }
            //fill in modul infos
            $this->wfsJSON->wfs->srv[$i - $j]->ftype[$l - $m]->modul[$m]->id = $wfsMatrix[$i]['wfs_conf_id'];
            $this->wfsJSON->wfs->srv[$i - $j]->ftype[$l - $m]->modul[$m]->title = $wfsMatrix[$i]['wfs_conf_description'];
            $this->wfsJSON->wfs->srv[$i - $j]->ftype[$l - $m]->modul[$m]->abstract = $wfsMatrix[$i]['wfs_conf_abstract'];
            $this->wfsJSON->wfs->srv[$i - $j]->ftype[$l - $m]->modul[$m]->type = $wfsMatrix[$i]['modultype'];
            $equalEPSG = $wfsMatrix[$i]['featuretype_srs'];
            $isEqual = true;
            //control if EPSG is supported by Client
            if ($equalEPSG == $this->searchEPSG) {
                $isEqual = false;
            }
            $this->wfsJSON->wfs->srv[$i - $j]->ftype[$l - $m]->modul[$m]->srsProblem = $isEqual;
            //generate Link to show metadata
            $this->wfsJSON->wfs->srv[$i - $j]->ftype[$l - $m]->modul[$m]->mdLink = "http://" . $this->hostName . "/mapbender/php/mod_showMetadata.php?resource=featuretype&id=" . $wfsMatrix[$i]['featuretype_id'];
            $perText = $this->getPermissionValueForWFS($wfsMatrix[$i]['wfs_id'], $wfsMatrix[$i]['wfs_conf_id']);
            $this->wfsJSON->wfs->srv[$i - $j]->ftype[$l - $m]->modul[$m]->permission = $perText;
            if ($wfsMatrix[$i]['wfs_id'] == $wfsMatrix[$i + 1]['wfs_id']) {
                $j++; //next record is the same service
                $l++;
            } else {
                $l = 0;
            }

            if ($wfsMatrix[$i]['featuretype_id'] == $wfsMatrix[$i + 1]['featuretype_id']) {
                $m++;
            } else {
                $m = 0;
            }
        }
    }

    private function generateWMCMetadataJSON($res, $n)
    {
        //initialize object
        $this->wmcJSON = new stdClass;
        $this->wmcJSON->wmc = (object) array(
                    'md' => (object) array(
                        'nresults' => $n,
                        'p' => $this->searchPages,
                        'rpp' => $this->maxResults
                    ),
                    'srv' => array()
        );

        //read out records
        $serverCount = 0;
        $wmcMatrix = db_fetch_all($res);
        //sort result for accessing the right services
        $wmcMatrix = $this->flipDiagonally($wmcMatrix);
        //TODO check if order by db or order by php is faster! 
        #array_multisort($wfsMatrix['wfs_id'], SORT_ASC,$wfsMatrix['featuretype_id'], SORT_ASC,$wfsMatrix['wfs_conf_id'], SORT_ASC); //have some problems - the database version is more stable
        #print_r($wfsMatrix);
        $wmcMatrix = $this->flipDiagonally($wmcMatrix);
        //read out first server entry - maybe this a little bit timeconsuming TODO
        for ($i = 0; $i < count($wmcMatrix); $i++) {
            $this->wmcJSON->wmc->srv[$i]->id = $wmcMatrix[$i]['wmc_id'];
            $this->wmcJSON->wmc->srv[$i]->title = $wmcMatrix[$i]['wmc_title'];
            $this->wmcJSON->wmc->srv[$i]->abstract = $wmcMatrix[$i]['wmc_abstract'];
            $this->wmcJSON->wmc->srv[$i]->date = date("d.m.Y", $wmcMatrix[$i]['wmc_timestamp']);
            $this->wmcJSON->wmc->srv[$i]->respOrg = $wmcMatrix[$i]['mb_group_name'];
            $this->wmcJSON->wmc->srv[$i]->logoUrl = $wmcMatrix[$i]['mb_group_logo_path'];
            $this->wmcJSON->wmc->srv[$i]->mdLink = "http://" . $this->hostName . "/mapbender/php/mod_showMetadata.php?languageCode=" . $this->languageCode . "&resource=wmc&layout=tabs&id=" . $wmcMatrix[$i]['wmc_id'];
            $this->wmcJSON->wmc->srv[$i]->previewURL = "http://" . $this->hostName . "/mapbender/geoportal/mod_showPreview.php?resource=wmc&id=" . $wmcMatrix[$i]['wmc_id'];
            $spatialSource = "";
            $stateOrProvince = $wmcMatrix[$i]['mb_group_stateorprovince'];
            if ($stateOrProvince == "NULL" || $stateOrProvince == "") {
                $spatialSource = $wmcMatrix[$i]['mb_group_country'];
            } else {
                $spatialSource = $wmcMatrix[$i]['mb_group_stateorprovince'];
            }
            $this->wmcJSON->wmc->srv[$i - $j]->iso3166 = $spatialSource;

            $this->wmcJSON->wmc->srv[$i - $j]->bbox = array($wmcMatrix[$i]['bbox']); //TODO: read out bbox from wmc $wmcMatrix[$i][''];
            #$equalEPSG = $wmcMatrix[$i]['srs'];
            #$isEqual = true;
            //control if EPSG is supported by Client
            #if ($equalEPSG == $this->searchEPSG){
            #	$isEqual = false;		
            #}	
        }
    }

    private function generateDatasetMetadataJSON($res, $n)
    {
        //initialize object
        $this->datasetJSON = new stdClass;
        $this->datasetJSON->dataset = (object) array(
                    'md' => (object) array(
                        'nresults' => $n,
                        'p' => $this->searchPages,
                        'rpp' => $this->maxResults
                    ),
                    'srv' => array()
        );

        //read out records
        $serverCount = 0;
        $datasetMatrix = db_fetch_all($res);
        //sort result for accessing the right services
        $datasetMatrix = $this->flipDiagonally($datasetMatrix);
        //TODO check if order by db or order by php is faster! 
        #array_multisort($wfsMatrix['wfs_id'], SORT_ASC,$wfsMatrix['featuretype_id'], SORT_ASC,$wfsMatrix['wfs_conf_id'], SORT_ASC); //have some problems - the database version is more stable
        #print_r($wfsMatrix);
        $datasetMatrix = $this->flipDiagonally($datasetMatrix);
        //read out first server entry - maybe this a little bit timeconsuming TODO
        for ($i = 0; $i < count($datasetMatrix); $i++) {
            $this->datasetJSON->dataset->srv[$i]->id = $datasetMatrix[$i]['metadata_id'];
            $this->datasetJSON->dataset->srv[$i]->title = $datasetMatrix[$i]['title'];
            $this->datasetJSON->dataset->srv[$i]->abstract = $datasetMatrix[$i]['abstract'];
            $this->datasetJSON->dataset->srv[$i]->date = date("d.m.Y", $datasetMatrix[$i]['last_changed']);
            $this->datasetJSON->dataset->srv[$i]->respOrg = $datasetMatrix[$i]['mb_group_name'];
            $this->datasetJSON->dataset->srv[$i]->logoUrl = $datasetMatrix[$i]['mb_group_logo_path'];
            //TODO: other url - to metadata uuid!
            $this->datasetJSON->dataset->srv[$i]->mdLink = "http://" . $this->hostName . "/mapbender/php/mod_showMetadata.php?languageCode=" . $this->languageCode . "&resource=wmc&layout=tabs&id=" . $datasetMatrix[$i]['metadata_id'];
            //TODO: preview?
            $this->datasetJSON->dataset->srv[$i]->previewURL = "http://" . $this->hostName . "/mapbender/geoportal/mod_showPreview.php?resource=wmc&id=" . $datasetMatrix[$i]['wmc_id'];
            $spatialSource = "";
            $stateOrProvince = $datasetMatrix[$i]['mb_group_stateorprovince'];
            if ($stateOrProvince == "NULL" || $stateOrProvince == "") {
                $spatialSource = $datasetMatrix[$i]['mb_group_country'];
            } else {
                $spatialSource = $datasetMatrix[$i]['mb_group_stateorprovince'];
            }
            $this->datasetJSON->dataset->srv[$i - $j]->iso3166 = $spatialSource;

            $this->datasetJSON->dataset->srv[$i - $j]->bbox = array($datasetMatrix[$i]['bbox']); //TODO: read out bbox from wmc $datasetMatrix[$i][''];
            #$equalEPSG = $wmcMatrix[$i]['srs'];
            #$isEqual = true;
            //control if EPSG is supported by Client
            #if ($equalEPSG == $this->searchEPSG){
            #	$isEqual = false;		
            #}	
        }
    }

    private function generateWMSMetadataJSON($res, $n)
    {
        //initialize object
        $this->wmsJSON = new stdClass;
        $this->wmsJSON->wms = (object) array(
                    'md' => (object) array(
                        'nresults' => $n,
                        'p' => $this->searchPages,
                        'rpp' => $this->maxResults
                    ),
                    'srv' => array()
        );
        //read out records
        $serverCount = 0;
        $wmsMatrix = db_fetch_all($res);
        $layerIdArray = array();
        //read out array with unique wms_ids in wmsMatrix
        $wmsIdArray = array();
        //initialize root layer id;
        $rootLayerId = -1;
        $j = 0;
        //get array with all available layer_id for this user:
        $admin = new administration();
        $this->accessableLayers = $admin->getLayersByPermission($this->userId);
        #echo "<br>user_id: ".$this->userId."<br><br>";
        #var_dump($this->accessableLayers);
        #echo "<br>";
        #$countWmsMatrix = count($wmsMatrix);
        #echo $countWmsMatrix;
        if ($n != 0) {
            for ($i = 0; $i < count($wmsMatrix); $i++) {
                $layerID = $wmsMatrix[$i]['layer_id'];
                #echo "<br>LayerID: ".$layerID."<br>";	
                #$wmsID = $wmsMatrix[$i]['wms_id']; //get first wms id - in the next loop - dont get second, but some else!
                if (!in_array($layerID, $layerIdArray) or ! in_array($rootLayerId, $layerIdArray)) {
                    $wmsID = $wmsMatrix[$i]['wms_id']; //get first wms id - in the next loop - dont get second, but some else!
                    //Select all layers of with this wms_id into new array per WMS - the grouping should be done by wms!
                    $subLayers = $this->filter_by_value($wmsMatrix, 'wms_id', $wmsID);
                    #echo "<br>wms_id: ".$wmsID."<br>";
                    #echo "<br>Number of sublayers: <br>";
                    #print(count($subLayers));
                    //Sort array by load_count - problem: maybe there are some groups between where count is to low (they have no load count because you cannot load them by name)? - Therefor we need some ideas - or pull them out of the database and show them greyed out. Another way will be to define a new group (or wms with the same id) for those layers which are more than one integer away from their parents
                    $subLayersFlip = $this->flipDiagonally($subLayers);
                    #var_dump($subLayers['layer_pos']);
                    #$subLayers = $this->flipDiagonally($subLayers);
                    //go backwards through the layerTree to get the layer with the highest position without gaps in between
                    #var_dump($subLayers['layer_id']);
                    #echo "<br>";
                    $index = array_search($layerID, $subLayersFlip['layer_id']);
                    #echo "<br>found layer_id= ".$layerID." at index: ".$index." in sublayerstable layer_pos=".$subLayers[$index]['layer_pos']." <br>";
                    #echo "<br>sublayers: ";
                    #var_dump($subLayersFlip);
                    #echo "<br>";
                    $rootIndex = $this->getLayerParent($subLayersFlip, $index);
                    $rootLayerPos = $subLayers[$rootIndex]['layer_pos'];
                    $rootLayerId = $subLayers[$rootIndex]['layer_id'];
                    #echo "<br>root layer for this layer: <br>";
                    #echo "<br>id= "..""
                    #echo "<br>";
                    #echo "<br>LayerId:<br>";
                    #echo "<br>".$layerID."<br>";
                    #echo "<br>rootLayerPos:<br>";
                    #echo "<br>".$rootLayerPos."<br>";
                    #echo "<br>rootLayerId:<br>";
                    #echo "<br>".$rootLayerId."<br>";
                    //push root layer id in array
                    array_push($layerIdArray, $rootLayerId);
                    #echo "<br>root Layer ID: ".$rootLayerId."<br>";
                    #array_multisort($subLayers['layer_pos'], SORT_ASC);
                    #print_r($subLayers);
                    #$subLayers = $this->flipDiagonally($subLayers);
                    #print_r("<br>rootIndex: ".$rootIndex."<br>");
                    //Create object for wms service level
                    $this->wmsJSON->wms->srv[$j]->uuid = $subLayers[$rootIndex]['uuid'];
                    $this->wmsJSON->wms->srv[$j]->id = (integer) $subLayers[$rootIndex]['wms_id'];
                    $this->wmsJSON->wms->srv[$j]->title = $subLayers[$rootIndex]['wms_title'];
                    $this->wmsJSON->wms->srv[$j]->abstract = $subLayers[$rootIndex]['wms_abstract'];
                    $this->wmsJSON->wms->srv[$j]->date = date("d.m.Y", $subLayers[$rootIndex]['wms_timestamp']);
                    #$this->wmsJSON->wms->srv[$j]->respOrg = "test";
                    $this->wmsJSON->wms->srv[$j]->loadCount = (integer) $subLayers[$rootIndex]['load_count'];
                    #$this->wmsJSON->wms->srv[$j]->mdLink = "http://".$_SERVER['HTTP_HOST']."/mapbender/geoportal/mod_layerMetadata.php?id=".(integer)$subLayers[$rootIndex]['layer_id'];
                    $this->wmsJSON->wms->srv[$j]->getMapUrl = $this->getMapUrlfromWMSId((integer) $subLayers[$rootIndex]['wms_id']);
                    $this->wmsJSON->wms->srv[$j]->security = $this->getPermissionValueForLayer($subLayers[$rootIndex]['layer_id'], $subLayers[$rootIndex]['wms_id']);
                    $spatialSource = "";
                    $stateOrProvince = $subLayers[$rootIndex]['stateorprovince'];
                    #echo $stateOrProvince."<br>";
                    if ($stateOrProvince == "NULL" || $stateOrProvince == "") {
                        $spatialSource = $subLayers[$rootIndex]['country'];
                    } else {
                        $spatialSource = $subLayers[$rootIndex]['stateorprovince'];
                    }
                    $this->wmsJSON->wms->srv[$j]->iso3166 = $spatialSource;
                    $this->wmsJSON->wms->srv[$j]->respOrg = $subLayers[$rootIndex]['mb_group_name'];
                    $this->wmsJSON->wms->srv[$j]->logoUrl = $subLayers[$rootIndex]['mb_group_logo_path'];
                    #$this->wmsJSON->wms->srv[$j]->tou = $subLayers[$rootIndex]['termsofuse'];
                    //check if a disclaimer has to be shown and give the relevant symbol
                    list($hasConstraints, $symbolLink) = $this->hasConstraints("wms", $subLayers[$rootIndex]['wms_id']);
                    $this->wmsJSON->wms->srv[$j]->hasConstraints = $hasConstraints;
                    $this->wmsJSON->wms->srv[$j]->isopen = $subLayers[$rootIndex]['isopen'];
                    $this->wmsJSON->wms->srv[$j]->symbolLink = $symbolLink;
                    //TODO check the field accessconstraints - which should be presented?
                    $this->wmsJSON->wms->srv[$j]->status = $subLayers[$rootIndex]['status']; //$wmsMatrix[$i][''];
                    $this->wmsJSON->wms->srv[$j]->avail = $subLayers[$rootIndex]['availability']; //$wmsMatrix[$i][''];
                    //get info about defined price
                    if ($subLayers[$rootIndex]['wms_pricevolume'] == '' OR $subLayers[$rootIndex]['wms_pricevolume'] == 0) {
                        $this->wmsJSON->wms->srv[$j]->price = NULL;
                    } else {
                        $this->wmsJSON->wms->srv[$j]->price = $subLayers[$rootIndex]['wms_pricevolume'];
                    }
                    //get info about logging of resource
                    if ($subLayers[$rootIndex]['wms_proxylog'] == NULL OR $subLayers[$rootIndex]['wms_proxylog'] == 0) {
                        $this->wmsJSON->wms->srv[$j]->logged = false;
                    } else {
                        $this->wmsJSON->wms->srv[$j]->logged = true;
                    }
                    //get info about network_accessability
                    if ($subLayers[$rootIndex]['wms_network_access'] == NULL OR $subLayers[$rootIndex]['wms_network_access'] == 0) {
                        $this->wmsJSON->wms->srv[$j]->nwaccess = false;
                    } else {
                        $this->wmsJSON->wms->srv[$j]->nwaccess = true;
                    }
                    #$this->wmsJSON->wms->srv[$j]->logged = NULL; //$wmsMatrix[$i][''];
                    #$this->wmsJSON->wms->srv[$j]->price = NULL; //$wmsMatrix[$i][''];
                    #$this->wmsJSON->wms->srv[$j]->nwaccess = NULL; //$wmsMatrix[$i][''];
                    $this->wmsJSON->wms->srv[$j]->bbox = $subLayers[$rootIndex]['bbox']; //$wmsMatrix[$i][''];
                    //Call recursively the child elements, give and pull $layerIdArray to push the done elements in the array to avoid double results
                    #print_r($subLayers);
                    //generate the layer-entry for the so called root layer - maybe this is only a group layer if there is a gap in the layer hierachy
                    $this->wmsJSON->wms->srv[$j]->layer = array();
                    $this->wmsJSON->wms->srv[$j]->layer[0]->id = (integer) $subLayers[$rootIndex]['layer_id'];
                    $this->wmsJSON->wms->srv[$j]->layer[0]->title = $subLayers[$rootIndex]['layer_title'];
                    $this->wmsJSON->wms->srv[$j]->layer[0]->name = $subLayers[$rootIndex]['layer_name'];
                    $this->wmsJSON->wms->srv[$j]->layer[0]->abstract = $subLayers[$rootIndex]['layer_abstract'];
                    $this->wmsJSON->wms->srv[$j]->layer[0]->mdLink = "http://" . $this->hostName . "/mapbender/php/mod_showMetadata.php?languageCode=" . $this->languageCode . "&resource=layer&layout=tabs&id=" . (integer) $subLayers[$rootIndex]['layer_id'];
                    $this->wmsJSON->wms->srv[$j]->layer[0]->previewURL = "http://" . $this->hostName . "/mapbender/geoportal/mod_showPreview.php?resource=layer&id=" . (integer) $subLayers[$rootIndex]['layer_id'];
                    $legendInfo = $this->getInfofromLayerId($this->wmsJSON->wms->srv[$j]->layer[0]->id);
                    $this->wmsJSON->wms->srv[$j]->layer[0]->getLegendGraphicUrl = $legendInfo['getLegendGraphicUrl'];
                    $this->wmsJSON->wms->srv[$j]->layer[0]->getLegendGraphicUrlFormat = $legendInfo['getLegendGraphicUrlFormat'];
                    $this->wmsJSON->wms->srv[$j]->layer[0]->legendUrl = $legendInfo['legendUrl'];
                    $this->wmsJSON->wms->srv[$j]->layer[0]->minScale = $legendInfo['minScale'];
                    $this->wmsJSON->wms->srv[$j]->layer[0]->maxScale = $legendInfo['maxScale'];
                    //pull downloadOptions as json with function from other script: php/mod_getDownloadOptions.php
                    $downloadOptionsCs = str_replace("{", "", str_replace("}", "", str_replace("}{", ",", $legendInfo['downloadOptions'])));
                    $downloadOptions = json_decode(getDownloadOptions(explode(',', $downloadOptionsCs)));
                    $this->wmsJSON->wms->srv[$j]->layer[0]->downloadOptions = $downloadOptions;

                    if ($subLayers[$rootIndex]['layer_name'] == '') {
                        $this->wmsJSON->wms->srv[$j]->layer[0]->loadable = 0;
                    } else {
                        $this->wmsJSON->wms->srv[$j]->layer[0]->loadable = 1;
                    }
                    if ($subLayers[$rootIndex]['layer_pos'] == '0') {
                        $this->wmsJSON->wms->srv[$j]->layer[0]->isRoot = true;
                    } else {
                        $this->wmsJSON->wms->srv[$j]->layer[0]->isRoot = false;
                    }
                    //give info for inspire categories - not relevant for other services or instances of mapbender TODO: comment it if the mapbender installation is not used to generate inspire output
                    if ($subLayers[$rootIndex]['md_inspire_cats'] == '') {
                        $this->wmsJSON->wms->srv[$j]->layer[0]->inspire = 0;
                    } else {
                        $this->wmsJSON->wms->srv[$j]->layer[0]->inspire = 1;
                    }
                    //get info about queryable or not
                    if ($subLayers[$rootIndex]['layer_queryable'] == 1) {
                        $this->wmsJSON->wms->srv[$j]->layer[0]->queryable = 1;
                    } else {
                        $this->wmsJSON->wms->srv[$j]->layer[0]->queryable = 0;
                    }

                    #if ($subLayers[$rootIndex]['layer_name'] == ''){
                    #	$this->wmsJSON->wms->srv[$i-$j]->layer[0]->loadable = 0;
                    #}
                    #else {
                    #	$this->wmsJSON->wms->srv[$i-$j]->layer[0]->loadable = 1;
                    #}
                    $this->wmsJSON->wms->srv[$j]->layer[0]->loadCount = $subLayers[$rootIndex]['load_count'];
                    #$servObject->layer[$countsublayer]->mdLink = $_SERVER['HOST']."/mapbender/geoportal/showWFeatureTypeMetadata.php?id=".$wfsMatrix[$i]['featuretype_id'];
                    #$servObject->layer[$countsublayer]->geomtype = $wfsMatrix[$i]['element_type'];
                    $this->wmsJSON->wms->srv[$j]->layer[0]->bbox = $subLayers[$rootIndex]['bbox'];
                    $this->wmsJSON->wms->srv[$j]->layer[0]->permission = $this->getPermissionValueForLayer($subLayers[$rootIndex]['layer_id'], $subLayers[$rootIndex]['wms_id']); //TODO: Make this much more faster
                    $this->wmsJSON->wms->srv[$j]->layer[0]->logged = $subLayers[$rootIndex]['wms_proxylog'];
                    //when the entry for the first server has been written, the server entry is fixed and the next one will be a new server or a part of the old one.
                    //increment server (highest object id)

                    $layerIdArray = $this->writeWMSChilds($layerIdArray, $rootLayerPos, $subLayers, $this->wmsJSON->wms->srv[$j]->layer[0]);
                    $j++;
                    //generate php object - if root layer was found - > layer_parent='' give hint to visualize folder symbol. 		
                }
            }
        }
    }

    private function generateWMSMetadata($xmlDoc)
    {
        $starttime = $this->microtime_float();
        list($sql, $v, $t, $n) = $this->generateSearchSQL();
        //call database search in limits
        $res = db_prep_query($sql, $v, $t);
        if ($this->outputFormat == 'json') {
            //generate json
            $this->generateWMSMetadataJSON($res, $n);
            $usedTime = $this->microtime_float() - $starttime;
            //put in the time to generate the data
            $this->wmsJSON->wms->md->genTime = $usedTime;
            $this->wmsJSON = $this->json->encode($this->wmsJSON);
            if ($this->resultTarget == 'file') {
                if ($wmsFileHandle = fopen($this->tempFolder . "/" . $this->searchId . "_" . $this->searchResources . "_" . $this->searchPages . ".json", "w")) {
                    fwrite($wmsFileHandle, $this->wmsJSON);
                    fclose($wmsFileHandle);
                }
            }
            if ($this->resultTarget == 'web' or $this->resultTarget == 'debug') {
                echo $this->wmsJSON;
            }
            if ($this->resultTarget == 'webclient') {
                $this->allJSON = new stdClass;
                $this->allJSON->categories = $this->json->decode($this->keyJSON);
                $this->allJSON->keywords = $this->json->decode($this->catJSON);
                //load filter from file 
                $filename = $this->tempFolder . "/" . $this->searchId . "_filter.json";
                if (!file_exists($filename)) {
                    $e = new mb_exception("class_metadata_new.php: No filter json exists!");
                } else {
                    $filterJSON = file_get_contents($filename);
                    $filterJSON = $this->json->decode($filterJSON);
                    $this->allJSON->filter = $filterJSON;
                }
                $this->allJSON->wms = $this->json->decode($this->wmsJSON);
                //$e = new mb_exception("originFromHeader: ".$this->originFromHeader);
                if ($this->originFromHeader != false) {
                    header('Access-Control-Allow-Origin: ' . $this->originFromHeader);
                }
                //if (defined("CORS_WHITELIST") && CORS_WHITELIST != "") {
                //	header('Access-Control-Allow-Origin: '.CORS_WHITELIST);
                //}
                echo $this->json->encode($this->allJSON);
                //echo "test";
            }
        }
        $usedTime2 = $this->microtime_float() - $starttime;
        //echo "<br>used time: ".$usedTime."<br>";
        $e = new mb_notice("Time to generate WMS-Metadata: " . $usedTime2);
        $e = new mb_notice("Wrote the MD_WMS-File");
    }

    private function generateWFSMetadata($xmlDoc)
    {
        $starttime = $this->microtime_float();
        list($sql, $v, $t, $n) = $this->generateSearchSQL();
        //call database search
        $res = db_prep_query($sql, $v, $t);
        if ($this->outputFormat == 'json') {
            //generate json
            $this->generateWFSMetadataJSON($res, $n);
            $usedTime = $this->microtime_float() - $starttime;
            //put in the time to generate the data
            $this->wfsJSON->wfs->md->genTime = $usedTime;
            $this->wfsJSON = $this->json->encode($this->wfsJSON);
            if ($this->resultTarget == 'file') {
                if ($wfsFileHandle = fopen($this->tempFolder . "/" . $this->searchId . "_" . $this->searchResources . "_" . $this->searchPages . ".json", "w")) {
                    fwrite($wfsFileHandle, $this->wfsJSON);
                    fclose($wfsFileHandle);
                }
            }
            if ($this->resultTarget == 'web'or $this->resultTarget == 'debug') {
                echo $this->wfsJSON;
            }
        }

        $e = new mb_notice("Time to generate WFS-Metadata: " . $usedTime);
        $e = new mb_notice("Wrote the MD_WFS-File");
    }

    private function generateWMCMetadata($xmlDoc)
    {
        $starttime = $this->microtime_float();
        list($sql, $v, $t, $n) = $this->generateSearchSQL();
        //call database search in limits
        $res = db_prep_query($sql, $v, $t);
        if ($this->outputFormat == 'json') {
            //generate json
            $this->generateWMCMetadataJSON($res, $n);
            $usedTime = $this->microtime_float() - $starttime;
            //put in the time to generate the data
            $this->wmcJSON->wmc->md->genTime = $usedTime;
            $this->wmcJSON = $this->json->encode($this->wmcJSON);
            if ($this->resultTarget == 'file') {
                if ($wmcFileHandle = fopen($this->tempFolder . "/" . $this->searchId . "_" . $this->searchResources . "_" . $this->searchPages . ".json", "w")) {
                    fwrite($wmcFileHandle, $this->wmcJSON);
                    fclose($wmcFileHandle);
                }
            }
            if ($this->resultTarget == 'web' or $this->resultTarget == 'debug') {
                echo $this->wmcJSON;
            }
        }
        $usedTime2 = $this->microtime_float() - $starttime;
        //echo "<br>used time: ".$usedTime."<br>";
        $e = new mb_notice("Time to generate WMC-Metadata: " . $usedTime2);
        $e = new mb_notice("Wrote the MD_WMC-File");
    }

    private function generateDatasetMetadata($xmlDoc)
    {
        $starttime = $this->microtime_float();
        list($sql, $v, $t, $n) = $this->generateSearchSQL();
        //call database search in limits
        $res = db_prep_query($sql, $v, $t);
        if ($this->outputFormat == 'json') {
            //generate json
            $this->generateDatasetMetadataJSON($res, $n);
            $usedTime = $this->microtime_float() - $starttime;
            //put in the time to generate the data
            $this->datasetJSON->dataset->md->genTime = $usedTime;
            $this->datasetJSON = $this->json->encode($this->datasetJSON);
            if ($this->resultTarget == 'file') {
                if ($datasetFileHandle = fopen($this->tempFolder . "/" . $this->searchId . "_" . $this->searchResources . "_" . $this->searchPages . ".json", "w")) {
                    fwrite($datasetFileHandle, $this->datasetJSON);
                    fclose($datasetFileHandle);
                }
            }
            if ($this->resultTarget == 'web' or $this->resultTarget == 'debug') {
                echo $this->datasetJSON;
            }
        }
        $usedTime2 = $this->microtime_float() - $starttime;
        //echo "<br>used time: ".$usedTime."<br>";
        $e = new mb_notice("Time to generate Dataset-Metadata: " . $usedTime2);
        $e = new mb_notice("Wrote the MD_Dataset-File");
    }

    private function replaceChars_all($text)
    {
        $search = array("ä", "ö", "ü", "Ä", "Ö", "Ü", "ß");
        $repWith = array("ae", "oe", "ue", "AE", "OE", "UE", "ss");
        $replaced = str_replace($search, $repWith, $text);
        return $replaced;
    }

    private function generateSearchSQL()
    {
        //elements needed to exist in mb wfs,wms,wmc view or table:
        //1. textfield - all texts - searchText
        //2. responsible organisations - given id 
        //3. bbox - is not explicit given in the wfs metadata? Since WFS 1.1.0 a latlonbbox is present
        //4. isoTopicCategory - is not been saved til now
        //5. ...
        //parse searchText into different array elements to allow an AND search
        $searchStringArray = $this->generateSearchStringArray();
        $v = array();
        $t = array();
        $sql = "SELECT * from " . $this->searchView . " where ";
        #$sqlN = "SELECT count(".$this->searchResources."_id) from ".$this->searchView." where ";
        $whereStr = "";
        $whereCondArray = array();
        $isTextSearch = "false";
        $e = new mb_notice("Number of used searchstrings: " . count($searchStringArray));
        //textsearch

        if ($this->searchText != NULL) {
            for ($i = 0; $i < count($searchStringArray); $i++) {
                $isTextSearch = "true";
                if ($i > 0) {
                    $whereStr .= " AND ";
                }
                $whereStr .= "searchtext LIKE $" . ($i + 1);
                //output for debugging
                $e = new mb_notice("Part of string" . $i . ": " . $searchStringArray[$i]);
                $e = new mb_notice("converted: " . $this->replaceChars_all($searchStringArray[$i]));
                $va = "%" . trim(strtoupper($this->replaceChars_all($searchStringArray[$i]))) . "%";
                $e = new mb_notice($this->searchResources . " Searchtext in SQL: " . $va);
                array_push($v, $va);
                array_push($t, "s");
            }
        }


        // This is only for the later postgis versions. The within and disjoint is to slow, cause there is no usage of the geometrical index in the old versions!
        //check for postgis version
        //sql for get version string
        //get version number
        if ((strtolower($this->searchResources) === "wms" or strtolower($this->searchResources) === "wmc" or strtolower($this->searchResources) === "dataset") & $this->searchBbox != NULL) {
            //decide which type of search should be done
            //check for postgis version cause postgis versions < 1.4 have problems when doing disjoint and inside
            //
			$sqlPostgisVersion = "SELECT postgis_version();";
            $vPostgisVersion = array();
            $tPostgisVersion = array();
            $resPostgisVersion = db_prep_query($sqlPostgisVersion, $vPostgisVersion, $tPostgisVersion);
            // get version string
            while ($row = db_fetch_array($resPostgisVersion)) {
                $postgisVersion = $row['postgis_version'];
                $postgisVersionArray = explode(" ", $postgisVersion);
                $postgisVersionSmall = explode(".", $postgisVersionArray[0]);
                $postgisSubNumber = $postgisVersionSmall[1];
                $e = new mb_notice("class_metadata.php: postgis sub number = " . $postgisSubNumber);
            }
            //
            //
			$e = new mb_notice("class_metadata.php: spatial operator: " . $this->searchTypeBbox);
            if ((integer) $postgisSubNumber >= 3) {
                #$spatialFilter = "(the_geom ";	
                $e = new mb_notice("class_metadata.php: spatial operator: " . $this->searchTypeBbox);
                if ($this->searchTypeBbox == 'outside') {
                    $spatialFilter = ' disjoint(';
                } elseif ($this->searchTypeBbox == 'inside') {
                    $spatialFilter = ' within(';
                } else {
                    $spatialFilter = ' intersects(';
                }
                //define spatial filter
                if (count(explode(',', $this->searchBbox)) == 4) {   //if searchBbox has 4 entries
                    $spatialFilterCoords = explode(',', $this->searchBbox); //read out searchBbox
                    //definition of the spatial filter
                    $spatialFilter .= 'the_geom,GeomFromText(\'POLYGON((' . $spatialFilterCoords[0]; //minx
                    $spatialFilter .= ' ' . $spatialFilterCoords[1] . ','; //miny
                    $spatialFilter .= $spatialFilterCoords[0]; //minx
                    $spatialFilter .= ' ' . $spatialFilterCoords[3] . ','; //maxy
                    $spatialFilter .= $spatialFilterCoords[2]; //maxx
                    $spatialFilter .= ' ' . $spatialFilterCoords[3] . ','; //maxy
                    $spatialFilter .= $spatialFilterCoords[2]; //maxx
                    $spatialFilter .= ' ' . $spatialFilterCoords[1] . ','; //miny
                    $spatialFilter .= $spatialFilterCoords[0]; //minx
                    $spatialFilter .= ' ' . $spatialFilterCoords[1] . '))\',4326)'; //miny
                    $spatialFilter .= ")";
                    array_push($whereCondArray, $spatialFilter);
                }
            } else {

                $spatialFilter = ' the_geom && ';
                //define spatial filter
                if (count(explode(',', $this->searchBbox)) == 4) {   //if searchBbox has 4 entries
                    $spatialFilterCoords = explode(',', $this->searchBbox); //read out searchBbox
                    //definition of the spatial filter
                    $spatialFilter .= 'GeomFromText(\'POLYGON((' . $spatialFilterCoords[0]; //minx
                    $spatialFilter .= ' ' . $spatialFilterCoords[1] . ','; //miny
                    $spatialFilter .= $spatialFilterCoords[0]; //minx
                    $spatialFilter .= ' ' . $spatialFilterCoords[3] . ','; //maxy
                    $spatialFilter .= $spatialFilterCoords[2]; //maxx
                    $spatialFilter .= ' ' . $spatialFilterCoords[3] . ','; //maxy
                    $spatialFilter .= $spatialFilterCoords[2]; //maxx
                    $spatialFilter .= ' ' . $spatialFilterCoords[1] . ','; //miny
                    $spatialFilter .= $spatialFilterCoords[0]; //minx
                    $spatialFilter .= ' ' . $spatialFilterCoords[1] . '))\',4326)'; //miny
                    #$spatialFilter .= ",the_geom)";
                    array_push($whereCondArray, $spatialFilter);
                }
            }
        }
        //search filter for isopen - open data classification of the managed termsofuse
        //
		if ((strtolower($this->searchResources) === "wms" or strtolower($this->searchResources) === "wfs") & $this->restrictToOpenData) {
            array_push($whereCondArray, '(isopen = 1)');
        }
        //search filter for md_topic_categories
        //
		if ((strtolower($this->searchResources) === "wms" or strtolower($this->searchResources) === "wmc" or strtolower($this->searchResources) === "dataset") & $this->isoCategories != NULL) {
            $isoArray = explode(',', $this->isoCategories);
            $topicCond = "(";
            for ($i = 0; $i < count($isoArray); $i++) {
                if ($i == 0) {
                    $topicCond .= "(md_topic_cats LIKE '%{" . $isoArray[$i] . "}%') ";
                } else {
                    $topicCond .= "AND (md_topic_cats LIKE '%{" . $isoArray[$i] . "}%') ";
                }
            }
            $topicCond .= ")";
            array_push($whereCondArray, $topicCond);
        }
        //search filter for inspire_categories
        //
		if ((strtolower($this->searchResources) === "wms" or strtolower($this->searchResources) === "wmc" or strtolower($this->searchResources) === "dataset") & $this->inspireThemes != NULL) {

            $inspireArray = explode(',', $this->inspireThemes);
            $inspireCond = "(";
            for ($i = 0; $i < count($inspireArray); $i++) {
                if ($i == 0) {
                    $inspireCond .= "(md_inspire_cats LIKE '%{" . $inspireArray[$i] . "}%') ";
                } else {
                    $inspireCond .= "AND (md_inspire_cats LIKE '%{" . $inspireArray[$i] . "}%') ";
                }
            }
            $inspireCond .= ")";
            array_push($whereCondArray, $inspireCond);
        }
        //search filter for custom_categories
        //
		if ((strtolower($this->searchResources) === "wms" or strtolower($this->searchResources) === "wmc" or strtolower($this->searchResources) === "dataset") & $this->customCategories != NULL) {

            $customArray = explode(',', $this->customCategories);
            $customCond = "(";
            for ($i = 0; $i < count($customArray); $i++) {
                if ($i == 0) {
                    $customCond .= "(md_custom_cats LIKE '%{" . $customArray[$i] . "}%') ";
                } else {
                    $customCond .= "AND (md_custom_cats LIKE '%{" . $customArray[$i] . "}%') ";
                }
            }
            $customCond .= ")";
            array_push($whereCondArray, $customCond);
        }



        //date condition
        //if begin and end are set
        //echo "<br> regTimeBegin: ".$this-> regTimeBegin." regTimeEnd: ".$this-> regTimeEnd."<br>";

        if ($this->regTimeBegin != NULL && $this->regTimeEnd != NULL) {
            $time = "(TO_TIMESTAMP(" . $this->searchResources . "_timestamp) BETWEEN '" . $this->regTimeBegin . "' AND '" . $this->regTimeEnd . "')";
            array_push($whereCondArray, $time);
            //only begin is set		
        }
        if ($this->regTimeBegin != NULL && $this->regTimeEnd == NULL) {
            $time = "(TO_TIMESTAMP(" . $this->searchResources . "_timestamp) > '" . $this->regTimeBegin . "')";
            array_push($whereCondArray, $time);
        }
        if ($this->regTimeBegin == NULL && $this->regTimeEnd != NULL) {
            $time = "(TO_TIMESTAMP(" . $this->searchResources . "_timestamp) < '" . $this->regTimeEnd . "')";
            array_push($whereCondArray, $time);
        }


        //department condition
        //TODO: generate filter for new sql check if at least some department is requested
        //generate array
        //$this->registratingDepartments = explode(',',$this->registratingDepartments);
        #if(count($this->registratingDepartments) > 0 & $this->registratingDepartments){
        if ($this->registratingDepartments != NULL) {
            $dep = " department IN (" . $this->registratingDepartments . ") ";
            array_push($whereCondArray, $dep);
        }
        //resourceId conditions
        if ($this->resourceIds != NULL) {
            $resourceCondition = " " . $this->databaseIdColumnName . " IN (" . $this->resourceIds . ") ";
            array_push($whereCondArray, $resourceCondition);
        }

        // Creating the WHERE clause, based on a array
        if (count($whereCondArray) > 0) {
            $txt_whereCond = "";
            for ($index = 0; $index < sizeof($whereCondArray); $index++) {
                $array_element = $whereCondArray[$index];
                if ($isTextSearch == "true") {
                    $txt_whereCond .= " AND " . $array_element;
                } else {
                    if ($index > 0) {
                        $txt_whereCond .= " AND " . $array_element;
                    } else {
                        $txt_whereCond .= " " . $array_element;
                    }
                }
            }
            $whereStr .= $txt_whereCond;
        }
        //Add WHERE condition to search
        $sql .= $whereStr;
        //TODO ORDER BY in SQL - not necessary for counting things:
        $sql .= $this->orderBy;
        //Calculate Paging for OFFSET and LIMIT values:
        $offset = ((integer) $this->maxResults) * ((integer) $this->searchPages - 1);
        $limit = (integer) $this->maxResults;
        //defining range for paging
        $sql .= " LIMIT " . $limit . " OFFSET " . $offset . "";
        //Print out search SQL term
        $e = new mb_notice("class_metadata.php: Search => SQL-Request of " . $this->searchResources . " service metadata: " . $sql . "");
        //parameter: searchId -> can be used global, searchResources -> is only one type per instance!!-> global,which categories -> can be defined global! $whereStr
        $n = $this->writeCategories($whereStr, $v, $t);
        //write counts to filesystem to avoid to many database connections
        //only write them, if searchId is given - problem: searches with same searchId's maybe get wrong information
        return array($sql, $v, $t, $n);
    }

    /** Function to write a json file which includes the categories of the search result for each searchResource - wms/wfs/wmc/georss, new: it should also count the keyword distribution of the searchResource ans save it as a special json file!

     * */
    private function writeCategories($whereStr, $v, $t)
    {
        //generate count sql
        //generate count of all entries	
        $sqlN = "SELECT count(" . $this->searchResources . "_id) from " . $this->searchView . " where ";
        $sqlN .= $whereStr;
        //Get total number of results 
        $count = db_prep_query($sqlN, $v, $t);
        $n = db_fetch_all($count);
        #echo "<br>N: ".var_dump($n)."<br>";
        $n = $n[0]['count'];
        $e = new mb_notice("class_metadata.php: Search => SQL-Request of " . $this->searchResources . " service metadata N: " . $sqlN . " Number of found objects: " . $n);
        if ($this->searchId != 'dummysearch') { //searchId is not the default id! - it has been explicitly defined 
            //check if cat file already exists:
            //filename to search for:
            $filename = $this->tempFolder . "/" . $this->searchId . "_" . $this->searchResources . "_cat.json";
            $keyFilename = $this->tempFolder . "/" . $this->searchId . "_" . $this->searchResources . "_keywords.json";
            if (!file_exists($filename) or $this->resultTarget == 'debug') { //TODO at the moment the cat file will be overwritten - change this in production system
                //open category file for results
                $this->catJSON = new stdClass;
                $this->catJSON->searchMD = (object) array(
                            'searchId' => $this->searchId,
                            'n' => $n
                );
                //new: also generate a json object for the keyword distribution
                $this->keyJSON = new stdClass;
                $this->keyJSON->tagCloud = (object) array(
                            'searchId' => $this->searchId,
                            'maxFontSize' => $this->maxFontSize,
                            'maxObjects' => $this->maxObjects,
                            'title' => $this->keywordTitle,
                            'tags' => array()
                );
                $this->inc = ($this->maxFontSize - $this->minFontSize) / $this->maxObjects; //maybe 10 or 5 or ...
                //generate the list of category counts
                $sqlCat = array();
                //generate the sql for the keyword count
                $sqlKeyword = "select keyword.keyword, COUNT(*) ";
                $sqlKeyword .= "FROM (select ";
                $sqlKeyword .= $this->databaseIdColumnName;
                $sqlKeyword .= " FROM " . $this->searchView . " WHERE " . $whereStr . ") as a";
                $sqlKeyword .= " INNER JOIN " . $this->databaseTableName . "_keyword ON (";
                $sqlKeyword .= $this->databaseTableName . "_keyword.fkey_" . $this->databaseIdColumnName . " = a.";
                $sqlKeyword .= $this->databaseIdColumnName . ") ";
                $sqlKeyword .= "INNER JOIN keyword ON (keyword.keyword_id=" . $this->databaseTableName . "_keyword.fkey_keyword_id) WHERE (keyword.keyword NOTNULL AND keyword.keyword <> '')";
                $sqlKeyword .= "GROUP BY keyword.keyword  ORDER BY COUNT DESC LIMIT  " . $this->maxObjects;
                //do sql select for keyword cloud
                $resKeyword = db_prep_query($sqlKeyword, $v, $t);
                $keywordCounts = db_fetch_all($resKeyword);

                if (count($keywordCounts) > 0) {
                    $this->maxWeight = $keywordCounts[0]['count'];
                    for ($j = 0; $j < count($keywordCounts); $j++) {
                        if ($this->scale == 'linear') {
                            //order in a linear scale desc
                            $keywordCounts[$j]['count'] = $this->maxFontSize - ($j * $this->inc);
                        } else {
                            //set weight prop to count 
                            $keywordCounts[$j]['count'] = $keywordCounts[$j]['count'] * $this->maxFontSize / $this->maxWeight;
                        }
                        /* if ($scale == 'linear'){
                          $tags[$i]['weight'] = $maxFontSize-($i*$inc);
                          } else {
                          $tags[$i]['weight'] = $tags[$i]['weight']*$maxFontSize/$maxWeight;
                          } */
                    }
                    shuffle($keywordCounts);
                    for ($j = 0; $j < count($keywordCounts); $j++) {
                        $this->keyJSON->tagCloud->tags[$j]->title = $keywordCounts[$j]['keyword'];
                        $this->keyJSON->tagCloud->tags[$j]->weight = $keywordCounts[$j]['count'];
                        $paramValue = $this->getValueForParam('searchText', $this->searchURL);
                        //delete resources part from query and set some new one
                        $searchUrlKeywords = $this->delTotalFromQuery('searchResources', $this->searchURL);
                        //append the resource parameter:
                        $searchUrlKeywords .= '&searchResources=' . $this->searchResources;
                        $e = new mb_notice("class_metadata_new: value " . $paramValue . " for searchText param found");
                        $paramValue = urldecode($paramValue);
                        if ($paramValue == false || $paramValue == '*') {
                            $this->keyJSON->tagCloud->tags[$j]->url = $searchUrlKeywords . "&searchText=" . $keywordCounts[$j]['keyword'];
                        } else {
                            $this->keyJSON->tagCloud->tags[$j]->url = $this->addToQuery('searchText', $searchUrlKeywords, $keywordCounts[$j]['keyword'], $paramValue);
                        }
                    }
                }
                //encode json!
                $this->keyJSON = $this->json->encode($this->keyJSON);
                //write clouds to file
                if ($keyFileHandle = fopen($keyFilename, "w")) {
                    fwrite($keyFileHandle, $this->keyJSON);
                    fclose($keyFileHandle);
                    $e = new mb_notice("class_metadata: new " . $this->searchResources . "_keyword_file created!");
                } else {
                    $e = new mb_notice("class_metadata: cannot create " . $this->searchResources . "_keyword_file!");
                }
                if ($this->resultTarget == 'debug') {
                    echo "<br>DEBUG: show keywords: <br>" . $this->keyJSON . "<br><br>";
                }

                /** "$resourceId." from ".$wms_view." where ".$whereClause.") as a";
                  $sqlKeyword .= "(select keyword, count(*) from keyword INNER JOIN  layer_keyword  ON (layer_keyword.fkey_keyword_id = keyword.keyword_id) GROUP BY keyword.keyword) ";
                  $sqlKeyword .= " GROUP BY keyword.keyword)) as a WHERE a.keyword <> '' GROUP BY a.keyword ORDER BY sum DESC LIMIT $1";
                 * */
                //check if categories are defined for the resource
                if ($this->resourceClasses != NULL) {
                    $this->catJSON->searchMD->category = array();
                    for ($i = 0; $i < count($this->resourceClasses); $i++) {
                        //TODO: not to set the classification?
                        $this->catJSON->searchMD->category[$i]->title = $this->resourceClassifications[$i]['title'];
                        $sqlCat[$i] = "SELECT " . $this->resourceClassifications[$i]['tablename'];
                        $sqlCat[$i] .= "." . $this->resourceClassifications[$i]['tablename'] . "_id, ";
                        $sqlCat[$i] .= " " . $this->resourceClassifications[$i]['tablename'] . ".";
                        $sqlCat[$i] .= $this->resourceClassifications[$i]['tablename'] . "_code_";
                        $sqlCat[$i] .= $this->languageCode . ", COUNT(*) FROM " . $this->searchView;

                        //first join for connection table
                        $sqlCat[$i] .= " INNER JOIN " . $this->resourceClassifications[$i]['relation_' . $this->searchResources];
                        $sqlCat[$i] .= " ON (";
                        $sqlCat[$i] .= $this->resourceClassifications[$i]['relation_' . $this->searchResources] . ".fkey_";
                        $sqlCat[$i] .= $this->resourceClassifications[$i]['id_' . $this->searchResources] . "=" . $this->searchView;
                        $sqlCat[$i] .= "." . $this->resourceClassifications[$i]['id_' . $this->searchResources];
                        $sqlCat[$i] .= ") INNER JOIN ";
                        $sqlCat[$i] .= $this->resourceClassifications[$i]['tablename'] . " ON (";
                        $sqlCat[$i] .= $this->resourceClassifications[$i]['tablename'] . ".";
                        $sqlCat[$i] .= $this->resourceClassifications[$i]['tablename'] . "_id=";
                        $sqlCat[$i] .= $this->resourceClassifications[$i]['relation_' . $this->searchResources] . ".fkey_";
                        $sqlCat[$i] .= $this->resourceClassifications[$i]['tablename'] . "_id)";
                        //the following is needed to filter the custom cats for those which should not be seen in the classification
                        if ($this->resourceClassifications[$i]['title'] != $this->resourceClassifications[2]['title']) {
                            $sqlCat[$i] .= " WHERE " . $whereStr . " GROUP BY ";
                        } else {
                            $sqlCat[$i] .= " WHERE " . $whereStr . $this->whereStrCatExtension . " GROUP BY ";
                        }
                        $sqlCat[$i] .= $this->resourceClassifications[$i]['tablename'] . ".";
                        $sqlCat[$i] .= $this->resourceClassifications[$i]['tablename'] . "_id,";
                        $sqlCat[$i] .= $this->resourceClassifications[$i]['tablename'] . ".";
                        $sqlCat[$i] .= $this->resourceClassifications[$i]['tablename'] . "_code_" . $this->languageCode . " ORDER BY ";
                        $sqlCat[$i] .= $this->resourceClassifications[$i]['tablename'] . "_id";
                        $sqlCategory = $sqlCat[$i];

                        //call sql for count of category
                        $res = db_prep_query($sqlCategory, $v, $t);
                        $e = new mb_notice("class_metadata: countCatsql: " . $sqlCategory);
                        $categoryCounts = db_fetch_all($res);
                        //if none found: $categoryCounts=false
                        #echo "<br>count sub categories :".$categoryCounts."<br>";
                        if ($categoryCounts) {
                            //write results in json object
                            if (count($categoryCounts) > 0) {
                                #echo "<br>count main categories".count($categoryCounts)."<br>";
                                #echo "<br>vardump main categories".var_dump($categoryCounts)."<br>";
                                $this->catJSON->searchMD->category[$i]->subcat = array();
                                for ($j = 0; $j < count($categoryCounts); $j++) {
                                    $this->catJSON->searchMD->category[$i]->subcat[$j]->id = $categoryCounts[$j][$this->resourceClassifications[$i]['tablename'] . "_id"];
                                    $this->catJSON->searchMD->category[$i]->subcat[$j]->title = $categoryCounts[$j][$this->resourceClassifications[$i]['tablename'] . "_code_" . $this->languageCode];
                                    $this->catJSON->searchMD->category[$i]->subcat[$j]->count = $categoryCounts[$j]['count'];
                                    //delete requestParam for this category and for id - cause a new search is started from searchURL
                                    $filteredSearchString = $this->delTotalFromQuery('searchId', $this->searchURL);
                                    //uncomment the following line if a or category search is intended
                                    //$filteredSearchString = $this->delTotalFromQuery($this->resourceClassifications[$i]['requestName'],$filteredSearchString);
                                    //TODO: maybe adopt this to do a and search and not a or like it is done now
                                    //check if category search was requested and rewrite the search url
                                    //get the value of the param as string or false if not set!
                                    $paramValue = $this->getValueForParam($this->resourceClassifications[$i]['requestName'], $filteredSearchString);
                                    $paramValue = urldecode($paramValue);
                                    if ($paramValue == false) {
                                        //add new category to search
                                        //set filter for this categoryid
                                        $filteredSearchString .= "&" . $this->resourceClassifications[$i]['requestName'] . "=" . $categoryCounts[$j][$this->resourceClassifications[$i]['tablename'] . "_id"];
                                    } else {
                                        //rewrite the searchUrl
                                        $filteredSearchString = $this->addToQuery($this->resourceClassifications[$i]['requestName'], $filteredSearchString, $categoryCounts[$j][$this->resourceClassifications[$i]['tablename'] . "_id"], $paramValue);
                                    }

                                    $this->catJSON->searchMD->category[$i]->subcat[$j]->filterLink = $filteredSearchString;
                                }
                            }
                        } else {
                            #$this->catJSON->searchMD->category[$i]->subcat = array();
                        }
                        $e = new mb_notice("class_metadata: countsql: " . $sqlCat[$i]);
                    }
                }

                $this->catJSON = $this->json->encode($this->catJSON);
                //write categories files only when file is requested and the searchid was not used before!
                if ($this->resultTarget == 'file') {
                    if ($catFileHandle = fopen($filename, "w")) {
                        fwrite($catFileHandle, $this->catJSON);
                        fclose($catFileHandle);
                        $e = new mb_notice("class_metadata: new " . $this->searchResources . "_class_file created!");
                    } else {
                        $e = new mb_notice("class_metadata: cannot create " . $this->searchResources . "_cat_file!");
                    }
                }
                if ($this->resultTarget == 'debug') {
                    echo "<br>DEBUG: show categories: <br>" . $this->catJSON . "<br><br>";
                }
            } else {
                $e = new mb_notice("class_metadata: " . $this->searchResources . "_class_file: " . $filename . " already exists - no new one is generated!");
            }
        } else {
            if ($this->resultTarget == 'debug') {
                echo "<br>DEBUG: Standard ID dummysearch was invoked - classifications won't be counted!<br>";
            }
            $e = new mb_notice("class_metadata: standard dummysearch was invoked - classifications won't be counted!");
        }
        return $n;
    }

    private function getPermissionValueForWFS($wfs_id, $wfs_conf_id)
    {
        //TODO: Set Email of owner into view for ressource - so it don't have to be searched?
        $return_permission = "";
        //get permission
        $admin = new administration();
        $myWFSconfs = $admin->getWfsConfByPermission($this->userId);
        $this->myWFSConfs = $myWFSconfs;
        for ($index = 0; $index < sizeof($this->myWFSConfs); $index++) {
            $array_element = $this->myWFSConfs[$index];
        }
        if (in_array($wfs_conf_id, $this->myWFSConfs)) {
            $return_permission = "true";
        } else {
            $sql = "SELECT wfs.wfs_id, mb_user.mb_user_email as email FROM wfs, mb_user where wfs.wfs_owner=mb_user.mb_user_id " . "and wfs.wfs_id=$1";
            $v = array($wfs_id);
            $t = array('i');
            $res = db_prep_query($sql, $v, $t);
            // get email
            $mail = "";
            while ($row = db_fetch_array($res)) {
                $mail = $row['email'];
                $return_permission = $mail;
            }
        }
        return $return_permission;
    }

    private function getMapUrlfromWMSId($wmsId)
    {
        $sql = "SELECT wms_getmap, wms_owsproxy FROM wms WHERE wms_id = $1";
        $v = array($wmsId);
        $t = array('i');
        $res = db_prep_query($sql, $v, $t);
        while ($row = db_fetch_array($res)) {
            $getMap = $row['wms_getmap'];
            $owsProxy = $row['wms_owsproxy'];
        }
        //hostname does not exist! - use hostname from parameter instead
        if ($owsProxy != null && $owsProxy != '') {
            //create dummy session - no one knows the user which requests this metadata!
            $sessionId = "00000000000000000000000000000000";
            $getMap = "http://" . $this->hostName . "/owsproxy/" . $sessionId . "/" . $owsProxy . "?";
        }
        return $getMap;
    }

    private function getInfofromLayerId($layerId)
    {
        $sql = "SELECT layer_wms.*, layer_style.legendurl, layer_style.legendurlformat FROM (SELECT layer_id, f_get_download_options_for_layer(layer_id) as layer_metadata, layer_minscale, layer_maxscale, wms_getlegendurl, wms_owsproxy FROM layer INNER JOIN wms ON layer.fkey_wms_id = wms.wms_id WHERE layer.layer_id = $1) as layer_wms LEFT OUTER JOIN layer_style ON layer_style.fkey_layer_id = layer_wms.layer_id";
        $v = array($layerId);
        $t = array('i');
        $res = db_prep_query($sql, $v, $t);
        while ($row = db_fetch_array($res)) {
            $getLegendUrl = $row['wms_getlegendurl'];
            $legendUrl = $row['legendurl'];
            $legendUrlFormat = $row['legendurlformat'];
            $owsProxy = $row['wms_owsproxy'];
            $minScale = $row['layer_minscale'];
            $maxScale = $row['layer_maxscale'];
            $downloadOptions = $row['layer_metadata'];
        }
        //hostname does not exist! - use hostname from parameter instead
        if ($owsProxy != null && $owsProxy != '' && $getLegendUrl != '' && $getLegendUrl != null) {
            $sessionId = "00000000000000000000000000000000";
            $getLegendUrlNew = "http://" . $this->hostName . "/owsproxy/" . $sessionId . "/" . $owsProxy . "?";
            //also let go legendurl thru owsproxy exchange first legendurl part with owsproxy part!
            $legendUrl = str_replace($getLegendUrl, $getLegendUrlNew, $legendUrl);
            $getLegendUrl = $getLegendUrlNew;
        }
        $returnArray['legendUrl'] = $legendUrl;
        $returnArray['getLegendGraphicUrl'] = $getLegendUrl;
        $returnArray['getLegendGraphicUrlFormat'] = $legendUrlFormat;
        $returnArray['minScale'] = $minScale;
        $returnArray['maxScale'] = $maxScale;
        $returnArray['downloadOptions'] = $downloadOptions;
        return $returnArray;
    }

    private function getPermissionValueForLayer($layerId, $wmsId)
    {
        //TODO: Set Email of owner into view for ressource - so it don't have to be searched?
        $return_permission = "";
        #$admin = new administration();
        #$permission = $admin->getLayerPermission($wms_id, $layer_name, $this->userId);
        #echo "<br>wms_id: ".$wms_id."<br>";
        #echo "<br>layer_name: ".$layer_name."<br>";
        #echo "<br>user_id: ".$this->userId."<br>";
        #echo "<br>Permission: ".$permission."<br>";
        # var_dump($this->accessableLayers);
        if (in_array($layerId, $this->accessableLayers)) {
            $return_permission = "true";
            return $return_permission;
        } else {
            $sql = "SELECT mb_user.mb_user_email as email FROM wms, mb_user WHERE wms.wms_owner=mb_user.mb_user_id";
            $sql .= " AND wms.wms_id=$1";
            $v = array($wmsId);
            $t = array('i');
            $res = db_prep_query($sql, $v, $t);
            // get email
            $mail = "";
            while ($row = db_fetch_array($res)) {
                $mail = $row['email'];
                $return_permission = $mail;
            }
            return $return_permission;
        }
    }

    private function generateSearchStringArray()
    {
        //'wfs test array' -> ('wfs' 'test' 'array')
        $asstr = array();

        if ($this->searchText != "false") {
            $asstr = explode(",", $this->searchText);
            //delete left and right whitespaces
            for ($i = 0; $i < count($asstr); $i++) {
                $asstr[$i] = ltrim($asstr[$i]);
                $asstr[$i] = rtrim($asstr[$i]);
            }
        } else {
            $asstr[0] = '%';
        }
        //check for single wildcard search
        $e = new mb_notice('class_metadata_new: searchText: ' . $this->searchText);
        if ((count($asstr) == 1) && (($asstr[0] == '*') || ($asstr[0] === 'false'))) {
            $asstr[0] = '%';
        }
        $e = new mb_notice('class_metadata_new: asstr[0]: ' . $asstr[0]);
        return $asstr;
    }

    //out of php doc - test if it is faster than normal array_search	
    private function fast_in_array($elem, $array)
    {
        $top = sizeof($array) - 1;
        $bot = 0;
        while ($top >= $bot) {
            $p = floor(($top + $bot) / 2);
            if ($array[$p] < $elem)
                $bot = $p + 1;
            elseif ($array[$p] > $elem)
                $top = $p - 1;
            else
                return TRUE;
        }
        return FALSE;
    }

    /*
     * filtering an array
     */

    private function filter_by_value($array, $index, $value)
    {
        if (is_array($array) && count($array) > 0) {
            foreach (array_keys($array) as $key) {
                $temp[$key] = $array[$key][$index];
                if ($temp[$key] == $value) {
                    $newarray[$key] = $array[$key];
                }
            }
        }
        return $newarray;
    }

    //function to get the parent of the given layer by crawling the layertree upwards
    private function getLayerParent($layerArray, $index)
    {
        //only layers of one service should be in $layerArray
        #$parentExists = false;
        #var_dump($layerArray);
        $layerIDKey = $layerArray['layer_id'][$index];
        #echo ("layerIDKey= ".$layerIDKey."<br>");
        $layerParentPos = $layerArray['layer_parent'][$index]; //get first parent object position
        #echo ("layerParentPos= ".$layerParentPos."<br>");
        #echo("<br>number of sublayers ".count(flipDiagonally($layerArray))."<br>");
        #echo("<br>size of layerArray['layer_pos']: ".count($layerArray['layer_pos'])."<br>");
        #var_dump($layerArray['layer_pos']);
        #echo "<br>flipped layerArray: <br> ";
        #var_dump(flipDiagonally($layerArray));
        #echo "<br>";		

        if ($layerParentPos == '') {
            //root layer directly found
            return $index;
        }
        #echo ("layerParentPos= ".$layerParentPos."<br>");
        //Initialize index of layer parent - first it references the layer itself
        $layerParentIndex = $index;
        //loop to search higher parent objects - maybe this can be faster if the loop is not used over all sublayer elements! Do a while loop instead!
        $highestParentLayerNotFound = true;
        while ($highestParentLayerNotFound) {
            #echo("<br>i= ".$i."<br>");
            #echo("<br>layerParentPosNew= ".$layerParentPos."<br>");
            $layerParentIndexNew = array_search((string) $layerParentPos, $layerArray['layer_pos']);
            #echo("<br>layerParentIndexNew= ".$layerParentIndexNew."<br>");
            if ($layerParentIndexNew != false) {
                //some parent has been found
                $layerParentIndex = $layerParentIndexNew;
                $layerParentPos = $layerArray['layer_parent'][$layerParentIndex];
                if ($layerParentPos == '') {
                    $highestParentLayerNotFound = false;
                    return $layerParentIndex; //a real root layer was found!
                }

                #$layerParentIndex = array_search($layerParentPos, $layerArray['layer_pos']);
            } else {
                $highestParentLayerNotFound = false; //no higher layer could be found
                return $layerParentIndex;
            }
        }
        return $layerParentIndex;
    }

    //function to write the child elements to the resulting wms object -> object is given by reference
    private function writeWMSChilds($layerIdArray, $rootLayerPos, $subLayers, &$servObject)
    {
        #echo "test";
        #echo "<br>subLayers:<br>";
        #var_dump($subLayers);
        #echo "<br>";
        $childLayers = $this->filter_by_value($subLayers, 'layer_parent', $rootLayerPos); //the root layer position in the sublayer array was located before. In this step, all layers will be pulled out of sublayer, where root layer position is parent object
        #echo "<br<childLayers:<br>";
        #var_dump($childLayers);
        #echo "<br>";
        #echo "test";
        #print_r($childLayers);
        #print_r($childLayers);
        $countsublayer = 0;
        //if child exists create a new layer array for these 
        if (count($childLayers) != 0) {
            $servObject->layer = array();
        }
        foreach ($childLayers as $child) {
            #echo "<br>countsublayer: ".$countsublayer."<br>";
            #echo "<br>Child id: ".$child['layer_id']."<br>";
            #echo "<br>Child pos: ".$child['layer_pos']."<br>";
            $servObject->layer[$countsublayer]->id = $child['layer_id'];
            $servObject->layer[$countsublayer]->title = $child['layer_title'];
            $servObject->layer[$countsublayer]->name = $child['layer_name'];
            $servObject->layer[$countsublayer]->abstract = $child['layer_abstract'];
            $servObject->layer[$countsublayer]->previewURL = "http://" . $this->hostName . "/mapbender/geoportal/mod_showPreview.php?resource=layer&id=" . $child['layer_id'];
            $legendInfo = $this->getInfofromLayerId($servObject->layer[$countsublayer]->id);
            $servObject->layer[$countsublayer]->getLegendGraphicUrl = $legendInfo['getLegendGraphicUrl'];
            $servObject->layer[$countsublayer]->getLegendGraphicUrlFormat = $legendInfo['getLegendGraphicUrlFormat'];
            $servObject->layer[$countsublayer]->legendUrl = $legendInfo['legendUrl'];
            $servObject->layer[$countsublayer]->minScale = $legendInfo['minScale'];
            $servObject->layer[$countsublayer]->maxScale = $legendInfo['maxScale'];
            $downloadOptionsCs = str_replace("{", "", str_replace("}", "", str_replace("}{", ",", $legendInfo['downloadOptions'])));
            //$e = new mb_exception("class_metadata_new.php: legendInfo[downloadOptions]: ".$legendInfo['downloadOptions']);
            $downloadOptions = json_decode(getDownloadOptions(explode(',', $downloadOptionsCs)));
            //$e = new mb_exception("class_metadata_new.php: downloadOptions as string: ".json_encode($downloadOptions));
            $servObject->layer[$countsublayer]->downloadOptions = $downloadOptions;
            $servObject->layer[$countsublayer]->mdLink = "http://" . $this->hostName . "/mapbender/php/mod_showMetadata.php?languageCode=" . $this->languageCode . "&resource=layer&layout=tabs&id=" . $child['layer_id'];
            if ($child['layer_name'] == '') {
                $servObject->layer[$countsublayer]->loadable = 0;
            } else {
                $servObject->layer[$countsublayer]->loadable = 1;
            }
            //give info for inspire categories - not relevant for other services or instances of mapbender TODO: comment it if the mapbender installation is not used to generate inspire output
            if ($child['md_inspire_cats'] == '') {
                $servObject->layer[$countsublayer]->inspire = 0;
            } else {
                $servObject->layer[$countsublayer]->inspire = 1;
            }
            //get info about queryable or not
            if ($child['layer_queryable'] == 1) {
                $servObject->layer[$countsublayer]->queryable = 1;
            } else {
                $servObject->layer[$countsublayer]->queryable = 0;
            }

            $servObject->layer[$countsublayer]->loadCount = $child['load_count'];
            $servObject->layer[$countsublayer]->bbox = $child['bbox'];
            $servObject->layer[$countsublayer]->permission = $this->getPermissionValueForLayer($child['layer_id'], $child['wms_id']); //TODO: make this much faster!!!! - is done by collecting all accessable resources once. Maybe this has to be adopted if the count of the resources become higher
            //call this function itself - search sublayers in the layer object.
            $layerIdArray = $this->writeWMSChilds($layerIdArray, $child['layer_pos'], $subLayers, $servObject->layer[$countsublayer]); //TODO create a timeout condition !
            array_push($layerIdArray, $child['layer_id']); //child have been identified and recursively written 
            #var_dump($layerIdArray);#
            #echo "<br>";
            $countsublayer ++;
        }
        return $layerIdArray;
    }

    private function hasConstraints($type, $id)
    {
        if ($type == "wms") {
            $sql = "SELECT wms.accessconstraints, wms.fees, wms.wms_network_access , wms.wms_pricevolume, wms.wms_proxylog, termsofuse.name,";
            $sql .= " termsofuse.termsofuse_id, termsofuse.symbollink, termsofuse.description,termsofuse.descriptionlink from wms LEFT OUTER JOIN";
            $sql .= "  wms_termsofuse ON  (wms.wms_id = wms_termsofuse.fkey_wms_id) LEFT OUTER JOIN termsofuse ON";
            $sql .= " (wms_termsofuse.fkey_termsofuse_id=termsofuse.termsofuse_id) where wms.wms_id = $1";
        }
        if ($type == "wfs") {
            $sql = "SELECT accessconstraints, fees, wfs_network_access , termsofuse.name,";
            $sql .= " termsofuse.termsofuse_id ,termsofuse.symbollink, termsofuse.description,termsofuse.descriptionlink from wfs LEFT OUTER JOIN";
            $sql .= "  wfs_termsofuse ON  (wfs.wfs_id = wfs_termsofuse.fkey_wfs_id) LEFT OUTER JOIN termsofuse ON";
            $sql .= " (wfs_termsofuse.fkey_termsofuse_id=termsofuse.termsofuse_id) where wfs.wfs_id = $1";
        }
        $v = array();
        $t = array();
        array_push($t, "i");
        array_push($v, $id);
        $res = db_prep_query($sql, $v, $t);
        $row = db_fetch_array($res);
        if ((isset($row[$type . '_proxylog']) & $row[$type . '_proxylog'] != 0) or strtoupper($row['accessconstraints']) != "NONE" or strtoupper($row['fees']) != "NONE" or isset($row['termsofuse_id'])) {
            //service has some constraints defined!
            //give symbol and true
            //termsofuse symbol or exclamation mark
            if (isset($row['termsofuse_id']) & $row['symbollink'] != "") {
                $symbolLink = $row['symbollink'];
            } else {
                $symbolLink = "../img/search/icn_warn.png";
            }
            $hasConstraints = true;
            #$disclaimerLink = $_SERVER['HTTP_HOST']."/mapbender/php/mod_getServiceDisclaimer.php?type=".$type."&id=".$id;
            #$symbolMouseOver = "Nutzungsbedingungen"; //TODO internationalize it
        } else {
            //give symbol and false
            //green symbol
            $symbolLink = "../img/search/icn_ok.png";
            #$disclaimerLink = "";
            $hasConstraints = false;
            #$symbolMouseOver = "Frei zugänglich"; //TODO internationalize it
        }

        //generate json output:
        #$json = new Mapbender_JSON;
        #$returnJSON = new stdClass;
        #$returnJSON->serviceConstraints = (object) array(
        #'hasConstraints' => $hasConstraints,
        #'disclaimerLink' => $disclaimerLink, 
        #'symbolLink' => $symbolLink,
        #'symbolMouseOver' => $symbolMouseOver
        #	);
        #$returnJSON = $json->encode($returnJSON);
        #echo $returnJSON;
        return array($hasConstraints, $symbolLink);
    }

    //function to delete one of the comma separated values from a HTTP-GET request
    //
	//
	//
	//
	private function delFromQuery($paramName, $queryString, $string, $queryArray, $queryList)
    {
        //check if if count searchArray = 1
        if (count($queryArray) == 1) {
            //remove request parameter from url by regexpr or replace
            $str2search = $paramName . "=" . $queryList;
            if ($paramName == "searchText") {
                $str2exchange = "searchText=*&";
            } else {
                $str2exchange = "";
            }
            $queryStringNew = str_replace($str2search, $str2exchange, $queryString);
            $queryStringNew = str_replace("&&", "&", $queryStringNew);
        } else {
            //there are more than one filter - reduce the filter  
            $objectList = "";
            for ($i = 0; $i < count($queryArray); $i++) {
                if ($queryArray[$i] != $string) {
                    $objectList .= $queryArray[$i] . ",";
                }
            }
            //remove last comma
            $objectList = rtrim($objectList, ",");
            $str2search = $paramName . "=" . $queryList;
            $str2exchange = $paramName . "=" . $objectList;
            $queryStringNew = str_replace($str2search, $str2exchange, $queryString);
        }
        return $queryStringNew;
    }

    private function getValueForParam($paramName, $queryString)
    {
        #another approach:
        parse_str($queryString, $allQueries);
        if (isset($allQueries[$paramName]) & $allQueries[$paramName] != '') {
            return $allQueries[$paramName];
        } else {
            return false;
        }


#		old version

        /* 		#TODO: check if last and first ampersand was set before
          $queryString = "&".$queryString."&";
          #$pattern = '/\b'.$paramName.'=([a-z0-9-]+)\&?/';
          $pattern = '/\b&'.$paramName.'\=[^&]+&?/';
          #$pattern = '/^&'.$paramName.'=[a-zA-ZöäüÖÄÜß,]*&$/';
          $e = new mb_notice("class_metadata_new.php: look for pattern: ".$pattern."  in ".$queryString);
          if (!preg_match($pattern, $queryString, $matches)){

          return false;
          } else {
          //some param found
          //delete $paramName= and the last ampersand!
          if (count($matches) == 1) {
          $requestString = $matches[0];
          $requestString = ltrim($requestString,"&".$paramName."=");
          $requestString = rtrim($requestString,'&');

          return $requestString;
          } else {
          $e = new mb_notice("class_metadata_new.php: There are parameter ambiguities!");
          #echo "Parameter Ambiguities found!";
          die();
          }
          } */
    }

// function to add a new variable or complete parameter to a GET parameter query url 
    private function addToQuery($paramName, $queryString, $string, $queryList)
    {
        //test if string was part of query before, if so, don't extent the query
        //TODO: the strings come from json and so they are urlencoded! maybe we have to decode them to find the commata
        $queryListComma = urldecode($queryList);
        $queryListC = "," . $queryListComma . ",";
        $pattern = ',' . $string . ',';
        if (!preg_match($pattern, $queryListC)) {
            //append the new element
            $queryListNew = $queryListC . $string;
            //echo "query string new: ".$queryListNew."<br>";
            //delete the commatas
            $queryListNew = ltrim($queryListNew, ',');
            //generate the new query string
            $queryStringNew = str_replace($paramName . '=' . $queryList, $paramName . '=' . $queryListNew, $queryString);
            //echo "query string new: ".$queryListNew."<br>";
            //dump old and new querystring for debugging
            //$this->logit("class_metadata_new: queryString_old:".$queryString);
            //$this->logit("class_metadata_new: queryString_new:".$queryStringNew);
            return $queryStringNew;
        } else {
            //$this->logit("class_metadata_new: queryString unchanged:".$queryString);
            //return the old one!
            return $queryString;
        }
    }

//for debugging purposes only
    private function logit($text)
    {
        if ($h = fopen("/tmp/class_metadata_new.log", "a")) {
            $content = $text . chr(13) . chr(10);
            if (!fwrite($h, $content)) {
                #exit;
            }
            fclose($h);
        }
    }

// function to delete one GET parameter totally from a query url 
    private function delTotalFromQuery($paramName, $queryString)
    {
        $queryString = "&" . $queryString;
        //only delete totally if not searchText itself
        if ($paramName == "searchText") {
            $str2exchange = "searchText=*&";
        } else {
            $str2exchange = "";
        }
        #echo "<br>queryString: ".$queryString."<br>";
        $queryStringNew = preg_replace('/\b' . $paramName . '\=[^&]+&?/', $str2exchange, $queryString);
        $queryStringNew = ltrim($queryStringNew, '&');
        $queryStringNew = rtrim($queryStringNew, '&');
        return $queryStringNew;
    }

}

?>
