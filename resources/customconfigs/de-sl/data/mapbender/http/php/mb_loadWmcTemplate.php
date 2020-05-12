<?php 
/**
 * Package: loadTemplate
 *
 * Description:
 * 
 * Files:
 *  - http/plugins/mb_loadTemplate.php
 *
 * SQL:
 * > INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, 
 * > e_title, e_element, e_src, e_attributes, e_left, e_top, e_width, 
 * > e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, 
 * > e_mb_mod, e_target, e_requires, e_url) VALUES('<appId>','loadTemplate',
 * > 2,1,'','loadTemplate','div','','',1,1,2,2,5,'','',
 * > 'div','../plugins/mb_loadTemplate.php','',
 * > 'mapframe1','','');
 * >
 *
 * 
 * Help:
 *
 * License:
 * Copyright (c) 2009, Open Source Geospatial Foundation
 * This program is dual licensed under the GNU General Public License 
 * and Simplified BSD license.  
 * http://svn.osgeo.org/mapbender/trunk/mapbender/license/license.txt
 */
require_once(dirname(__FILE__)."/mb_validateSession.php");
require_once(dirname(__FILE__)."/../../core/globalSettings.php");
require_once(dirname(__FILE__)."/../../conf/geoportal.conf");
require_once(dirname(__FILE__)."/../classes/class_mb_exception.php");

//new mb_notice("########### SCHABLONE:");
$loadWithTemplate = Mapbender::session()->get("loadWithTemplate");

if(isset($_REQUEST["WMC"]) && $_REQUEST["WMC"] != ""){
    $wmc_id = $_REQUEST["WMC"];
}

echo "var loadWithTemplate = '" . $loadWithTemplate . "';";

?>
Mapbender.events.afterInit.register(function () {
<!--	if(loadWithTemplate != '' && loadWithTemplate == '1') {-->
	if(true) {
    <?php
        $v = array($wmc_id);
        $t = array('i');

        $sql  = "SELECT target,type,key,value";
        $sql .= " FROM mb_user_wmc_template WHERE fkey_wmc_id = $1";
        $result = db_prep_query($sql,$v,$t);
        $elements = array();
        while($row = db_fetch_array($result)){
            if($row["type"]=="text") {
                $elements[$row["target"]][$row["type"]] = $row["value"];
            }else if($row["type"]=="attr") {
                $elements[$row["target"]][$row["type"]][] = $row["key"].':"'.$row["value"].'"';
            }
        }
        foreach ($elements as $target => $value) {
//            echo '$("#'.$target.'").css({visibility: "visible"});';
            echo (isset($value["attr"]) ? '$("#'.$target.'").attr({'.implode(",",$value["attr"]).'});' : '');
            echo (isset($value["text"]) ? '$("#'.$target.'").text("'.$value["text"].'");' : '');
        }
    ?>
	}
});
