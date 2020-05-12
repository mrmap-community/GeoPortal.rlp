<?php
require_once(dirname(__FILE__)."/../php/mb_validateSession.php");

if($_REQUEST["calltype"] && $_REQUEST["calltype"] == "category") {
    echo 'var callId = "'.$_REQUEST["callId"].'";';
    echo 'var jsonFilePath = "";';
    echo 'var callString = decodeURIComponent("'.$_REQUEST["pathname"].'")+"?callIdCat='.$_REQUEST["callId"].'&page='.$_REQUEST["page"].'";';
} else {
    $jsonFilePath = explode("-",$_REQUEST["callId"]);
    echo 'var callId = "'.$_REQUEST["callId"].'";';
    echo 'var jsonFilePath = "'.$jsonFilePath[2].'";';
    $cat_id = explode("|",$jsonFilePath[1]);
    //echo 'var callString = "<a href=\"/index.php/de/suchergebnis?callId='.$jsonFilePath[0].'&lastFile='.$jsonFilePath[2].'&mb_user_id='. Mapbender::session()->get("mb_user_id")  .'\" />";';
    echo 'var callString = "/index.php/de/suchergebnis?callId='.$jsonFilePath[0].'&lastFile='.$jsonFilePath[2].'&mb_user_id='. Mapbender::session()->get("mb_user_id").(count($cat_id)==2 ? '&catid='.$cat_id[0].'&num='.$cat_id[1] : "").'";';
}
?>
var $backToSearchButton = $(this);

var BackToSearchButtonApi = function (o) {
    var that = this;
	
    var goBackToSearch = function () {
		button.stop();
	};

	var button = new Mapbender.Button({
		domElement: $backToSearchButton.get(0),
		over: o.src.replace(/_off/, "_over"),
		on: o.src.replace(/_off/, "_on"),
		off: o.src,
		name: o.id,
		go: goBackToSearch
	});
	
	if(callId && callId != "") {
		$("#backToSearchButton").show();
		
		//$("#backToSearchButton").wrap(callString);
		$("#back2res").bind("click",function(){
			window.location.href =  callString;
		});
		
	}
	else {
		$("#backToSearchButton").hide();
	}
};

$backToSearchButton.mapbender(new BackToSearchButtonApi(options));