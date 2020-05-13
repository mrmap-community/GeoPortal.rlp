<?php
# $Id: mod_deleteWMS.php 8491 2012-09-18 15:11:04Z verenadiewald $
# http://www.mapbender.org/index.php/DeleteWMS
# Copyright (C) 2002 CCGIS 
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2, or (at your option)
# any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.


$e_id="deleteWMS";
require_once(dirname(__FILE__)."/../php/mb_validatePermission.php");

/*  
 * @security_patch irv done
 */ 
//security_patch_log(__FILE__,__LINE__);


$wmsList = $_POST["wmsList"];
$del = $_POST["del"];

require_once(dirname(__FILE__)."/../classes/class_administration.php");
require_once(dirname(__FILE__)."/../classes/class_georss_factory.php");


function getWmsMetadataUrl ($wmsId) {
	return LOGIN."/../../mapbender/php/mod_showMetadata.php?resource=wms&id=".$wmsId;
}


?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="expires" content="0">
<?php
echo '<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'">';	
?>
<title>deleteWMS</title>
<?php
include '../include/dyn_css.php';
?>
<script type="text/javascript">

function validate()
{
   var ind = document.form1.wmsList.selectedIndex;
   if(ind > -1) {
     var permission =  confirm("delete: " + document.form1.wmsList.options[ind].text + " ?");
     if(permission === true) {
        document.form1.del.value = 1;
        document.form1.submit();
     }
   }
}





function suggest_deletion(email_str) 
{
   var ind = document.form1.wmsList.selectedIndex;
   if(ind > -1)
	 {
     var permission =  confirm("A mail will be sent to the owners of '" + document.form1.wmsList.options[ind].text + "', suggesting its deletion.");
     if(permission === true) {
        document.form2.suggest.value = 1;
        document.form2.wms_name.value = document.form1.wmsList.options[ind].text;
        document.form2.owners.value = email_str;
        document.form2.submit();
     }
   }
}
-->
</script>
</head>
<body>
<?php
require_once(dirname(__FILE__)."/../classes/class_administration.php");
$admin = new administration();

$error_msg='';

//if mail form has been filled in and sent
if ($_POST["mail"]) {
	if (!$admin->isValidEmail($_POST["replyto"])) {
		$error_msg .= "The reply-to address is not valid! Please correct it.";
	}
	else {
		$toAddr = array();
		$toName = array();	
		$namesAndAddresses = explode(":::" , $_POST["owners"]);
		for ($i=0; $i<count($namesAndAddresses)-1; $i++) {
			$nameAndAddress = explode(";;;", $namesAndAddresses[$i]);
			$toAddr[$i] = $nameAndAddress[0]; 	
			$toName[$i] = $nameAndAddress[1]; 	
		}

		$error = '';
		for ($i=0; $i<count($toAddr); $i++) {
			if (!$admin->sendEmail($_POST["replyto"], $_POST["from"], $toAddr[$i], $toName[$i], "[Mapbender] A user has suggested a WMS for deletion", $_POST["comment"], $error)) {
				if ($error) {
					$error_msg .= $error . " ";
				}
			}
		}
		
	   if (!$error_msg) {
	      echo "<script language='javascript'>";
	      echo "alert('Other owners have been informed!');";
	      echo "</script>";
	   }
	}
}


// if deletion has been suggested, or there's an error in the form, display mail form (again)
if ($_POST["suggest"] || $error_msg){

	if ($error_msg) {
      echo "<script language='javascript'>";
      echo "alert('$error_msg');";
      echo "</script>";
	}

	$wms = $_POST["wms_name"];

	if (!$_POST["from"]) 
		$fromName = Mapbender::session()->get("mb_user_name");
	else
		$fromName = $_POST["from"];
		
	if (!$_POST["replyto"]) 
		$email = $admin->getEmailByUserId(Mapbender::session()->get("mb_user_id"));
	else
		$email = $_POST["replyto"];
		
	if (!$_POST["comment"]) 
		$text = "The WMS " . $wms . " has been suggested for deletion. If you agree, remove it from your GUIs. If not, you can contact the user who suggested the deletion and discuss it.";
	else
		$text = $_POST["comment"];
		
	echo "<form name='form3' action='" . $self ."' method='post'>";
	echo "<table><tr>";
	echo "<td>Your name:</td>";
	echo "<td><input type='text' name='from' size=50 value = '".$fromName."'></td>";
	echo "</tr><tr>";
	echo "<td>Reply-To:</td>";
	echo "<td><input type='text' name='replyto' size=50 value = '" . $email . "'></td>";
	echo "</tr><tr>";
	echo "<td valign=top>Comment:</td>";
	echo "<td><textarea name='comment' cols=38 rows=10>" . $text . "</textarea></td>";
	echo "</tr><tr>";
	echo "<td></td><td><input type='submit' name='mail' value='send email'></td>";
	echo "</tr></table>";
	echo "<input type='hidden' name='owners' value='" . $_POST["owners"] . "'>";
	echo "</form>";

}
else {	
	// delete WMS
	if($del){
	    /*
		//on delete wms: unlink the application cache file in cache directory
    	$sql = "SELECT fkey_gui_id FROM gui_wms WHERE fkey_wms_id = $1";
    	$v = array($wmsList);
    	$t = array('i');
    	$res = db_prep_query($sql,$v,$t);
    	while($row = db_fetch_array($res)){
    		$admin->clearJsCacheFile($row["fkey_gui_id"]);
    	}
    	*/
        	
		$sql = "select * from gui_wms where fkey_wms_id = $1 ";
		$v = array($wmsList);
		$t = array('i');
		$res = db_prep_query($sql,$v,$t);
		$cnt = 0;
	 	 while($row = db_fetch_array($res))
	  	 {
	  	 		 $sql = "UPDATE gui_wms set gui_wms_position = (gui_wms_position -1) ";
	  			 $sql .= "WHERE fkey_gui_id = $1 ";
	  			 $sql .= " AND gui_wms_position > $2 ";
	  			 $v = array($row["fkey_gui_id"],$row["gui_wms_position"]);
	  			 $t = array('s','i');
	  			 $res1 = db_prep_query($sql,$v,$t);			
	    		 $cnt++;				
	 	 }
		$sql = "SELECT wms_title, wms_abstract FROM wms WHERE wms_id = $1";
	   $v = array($wmsList);
	   $t = array('i');
	   $res = db_prep_query($sql,$v,$t);
	   if ($res) {
	   		$row = db_fetch_array($res);
			$wms_title = $row["wms_title"];
			$wms_abstract = $row["wms_abstract"];
	   }
		
	//Before the wms will be deleted, the metadataUrls and dataUrls from the Layers must be deleted!
	//The other things will be done by class_wms!
	//***
	/*$sql = "DELETE FROM mb_metadata WHERE metadata_id IN (SELECT metadata_id FROM mb_metadata INNER JOIN";
	$sql .= " (SELECT * from ows_relation_metadata WHERE fkey_layer_id IN ";
	$sql .= " (SELECT layer_id FROM layer WHERE fkey_wms_id = $1) )";
 	$sql .= " as relation ON ";
	$sql .= " mb_metadata.metadata_id = relation.fkey_metadata_id AND mb_metadata.origin = 'capabilities')";
	
	$v = array($wmsList);
	$t = array('i');
	$res = db_prep_query($sql,$v,$t);*/
	//***
	$sql = "DELETE FROM datalink WHERE datalink_id IN (SELECT datalink_id FROM datalink INNER JOIN";
	$sql .= " (SELECT * from ows_relation_data WHERE fkey_layer_id IN ";
	$sql .= " (SELECT layer_id FROM layer WHERE fkey_wms_id = $1) )";
 	$sql .= " as relation ON ";
	$sql .= " datalink.datalink_id = relation.fkey_datalink_id AND datalink.datalink_origin = 'capabilities')";
	
	$v = array($wmsList);
	$t = array('i');
	$res = db_prep_query($sql,$v,$t);
	//***
	   $sql = "DELETE FROM wms WHERE wms_id = $1";
	   $v = array($wmsList);
	   $t = array('i');
	   $res = db_prep_query($sql,$v,$t);
	   
	   if ($res) {
			//
			// update GeoRSS feed
			//
			$geoRssFactory = new GeoRssFactory();
			$geoRss = $geoRssFactory->loadOrCreate(GEO_RSS_FILE);
			$geoRssItem = new GeoRssItem();
			$geoRssItem->setTitle("DELETED WMS: ".$wms_title." (".$wmsList.")");
			$geoRssItem->setDescription($wms_abstract);
			$geoRssItem->setUrl(getWMSMetadataUrl($wmsList));
			//$timestamp = ($timestamp==null) ? time() : $timestamp;
			$timestamp = date(DATE_RSS,time());
			$geoRssItem->setPubDate($timestamp);
			$geoRss->appendTop($geoRssItem);
			$geoRss->saveAsFile();	   
		}
	}
	// display WMS List
	
	$wms_id_own = $admin->getWmsByOwner(Mapbender::session()->get("mb_user_id"),true);
	
	if (count($wms_id_own)>0){
		$v = array();
		$t = array();
		$sql = "Select * from wms WHERE wms_id IN (";
		for($i=0; $i<count($wms_id_own); $i++){
		 if($i>0){ $sql .= ",";}
		 $sql .= "$".($i+1);
		 array_push($v,$wms_id_own[$i]);
		 array_push($t,'i');
		}
		$sql .= ") ORDER BY wms_title";
		$res = db_prep_query($sql,$v,$t);
		$cnt = 0;
		
		
		echo "<form name='form1' action='" . $self ."' method='post'>";
		echo "<select class='wmsList' size='20' name='wmsList' onchange='document.form1.wmsList.value = this.value;submit()'>";
		while($row = db_fetch_array($res))
		{
			$wmsvalue = $row["wms_id"];
			//mark previously selected WMS <==> text = " selected" 
			if ($wmsvalue == $wmsList) {
				$text = " selected";
			}
			else {
				$text = "";
			}
		   echo "<option value='".$wmsvalue."'" . $text . ">".$row["wms_title"]."</option>";
		   $cnt++;
		}
		echo "</select><br>";
	
	
		//
		//
		// If WMS is selected, show more info
		//
		//
		if($wmsList)
		{   
		    $sql = "SELECT layer_id FROM layer WHERE fkey_wms_id = $1 AND layer_pos=0";
			$v = array($wmsList);
			$t = array('i');
			$res = db_prep_query($sql,$v,$t);
			$row = db_fetch_array($res);
		    echo "view wms metadata: <a id='metadatalink' href='mod_layerMetadata.php?id=".$row["layer_id"]."' onclick='window.open(this.href,\"Metadaten\",\"width=500,height=600,left=100,top=200,scrollbars=yes ,dependent=yes\"); return false' target='_blank'><span id='metadatatext'>WMS ID: ".$wmsList."</span></a><br><br>";
		
			echo "<p class = 'guiList'>";
			// Show GUIs using chosen WMS
			$sql = "SELECT fkey_gui_id FROM gui_wms WHERE fkey_wms_id = $1";
			$v = array($wmsList);
			$t = array('i');
			$res = db_prep_query($sql,$v,$t);

			// show WMS-ID for better identifiability
			echo "<b>WMS-ID " . $wmsList . " is used in the following applications:</b><br><br>";
			
			$cnt = 0;
			while($row = db_fetch_array($res))
			{
				echo "- " . $row["fkey_gui_id"]."<br>";
				$cnt++;
			}
			if ($cnt == 0) {
				echo "<i>- none -</i><br>";
			}
			
			// Show GetCapabilities of chosen WMS
			$sql = "SELECT wms_getcapabilities FROM wms WHERE wms_id = $1";
			$v = array($wmsList);
			$t = array('i');
			$res = db_prep_query($sql,$v,$t);
			
			echo "<br><br><b>GetCapabilities</b><br><br>";
		
			$cnt = 0;
			while($row = db_fetch_array($res))
			{
				echo $row["wms_getcapabilities"]."<br>";
				$cnt++;
			}
			
			// Show Abstract of Chosen WMS
			$sql = "SELECT wms_abstract FROM wms WHERE wms_id = $1";
			$v = array($wmsList);
			$t = array('i');
			$res = db_prep_query($sql,$v,$t);
			
			echo "<br><br><b>Abstract</b><br><br>";
		
			$cnt = 0;
			while($row = db_fetch_array($res))
			{
				echo $row["wms_abstract"]."<br>";
				$cnt++;
			}
			echo "<br><br><b>Owner:</b><br><br>";
			$owner = $admin->getOwnerByWms($wmsList);
			if ($owner && count($owner)>0) {
				for($i=0; $i<count($owner); $i++){
					echo "- ".$admin->getUserNameByUserId($owner[$i])."<br>";	
				}
			}
			else echo "<i>- none -</i>";
			
				
			echo "</p>";
	
			//previously, a WMS could only be deleted if it was owned by a single owner
			//if(count($owner)==1 && $owner[0] == Mapbender::session()->get("mb_user_name")){
			
			//now, any owner can delete, any non-owner can suggest deletions
			//if a wms has no owner, anyone can delete
	    		if($owner && in_array(Mapbender::session()->get("mb_user_id"), $owner) && count($owner) == 1) {
	    			echo "<input class='button_del' type='button' value='delete' onclick='validate()'>";
	    		}
	    		elseif ($owner && in_array(Mapbender::session()->get("mb_user_id"), $owner) && count($owner) > 1) {
	    			
	    			// delete suggestion button only appears when mailing is enabled in mapbender.conf
	    			if ($use_php_mailing) {
	    			
	    				// prepare email-addresses and usernames of all owners
	    				$owner_ids = $owner;
	    				$owner_mail_addresses = array();
	    				$email_str = '';
	    				$j=0;
	    				for ($i=0; $i<count($owner_ids); $i++) {
	    					$adr_tmp = $admin->getEmailByUserId($owner_ids[$i]);
	    					if (!in_array($adr_tmp, $owner_mail_addresses) && $adr_tmp) {
	    						$owner_mail_addresses[$j] = $adr_tmp;
	    						$email_str .= $owner_mail_addresses[$j] . ";;;" . $owner[$i] . ":::";
	    						$j++;
	    					} 
	    				}
	    				print_r($owner_ids);
	    				print_r($owner_mail_addresses);
	    				echo "<input class='button_del' type='button' value='suggest deletion' onclick='suggest_deletion(\"" . $email_str . "\")'>";
	    			}
	    			else {
	    				echo "<script language='javascript'>";
	    				echo "alert('You are not allowed to delete this WMS!');";
	    				echo "</script>";
	    			}
				}
	
		}
	}else{
		echo "There are no wms available for this user.<br>";
	}
}
?>
<input type='hidden' name='del'>
</form>
<?php 
echo "<form name='form2' action='" . $self ."' method='post'>";
?>
<input type='hidden' name='suggest' value='0'>
<input type='hidden' name='wms_name' value=''>
<input type='hidden' name='owners' value=''>
</form>
</body>
</html>
