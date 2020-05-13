<?php
$FILES=array();
$DIR="fileadmin/design/images/start/";

include_once("fileadmin/function/function.php");

if($dh=opendir($DIR)) {
	while( ($file=readdir($dh)) !== false) {
		if($file!="." && $file!="..") {
			$parts=split("\.",$file);
			if(count($parts)>1) {
				switch(strtolower($parts[count($parts)-1])) {
					case "jpg":
					case "jpeg":
					case "gif":
					case "png":
						$FILES[]=$file;
						break;
				}
			}
		}
	}
	closedir($dh);
}

srand ((double)microtime()*1000000);
$Nr=rand(0,count($FILES)-1);
print $FILES[$Nr];
?>