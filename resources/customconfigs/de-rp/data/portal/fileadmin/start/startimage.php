<?php
$FILES=array();
$DIR="fileadmin/design/images/help/";

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
?>
<img id="startimage" src="fileadmin/design/images/help/<?php print $FILES[$Nr]; ?>" width="450" height="150" alt="" />