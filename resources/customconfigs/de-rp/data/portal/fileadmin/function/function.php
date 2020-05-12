<?PHP
global $URL;
$URL=(($_SERVER['REDIRECT_URL']!="")?$_SERVER['REDIRECT_URL']:$_SERVER['PHP_SELF']);

function UserID() {
	GLOBAL $GUESTID;

	if($_SESSION["mb_user_id"]!=$GUESTID) return($_SESSION["mb_user_id"]);
	else return($_REQUEST[session_name()]);
}

function search_text2() {
	global $search;

	$num=func_num_args();

	for($i=0;$i<$num/2;$i++) {
		$text[$i]=func_get_arg($i*2);
		$val[$i]=func_get_arg($i*2+1);

	  $text[$i]=str_replace("<br", " <br", $text[$i]);
		$text[$i]=strip_tags(mb_strtolower($text[$i]));
		$text[$i]=html_entity_decode($text[$i],ENT_QUOTES);
	}

	$rating=0;

	for($i=0;$i<count($search);$i++) {
		$foundword=0;

		for($j=0;$j<$num/2;$j++) {
			$found=substr_count($text[$j], $search[$i]['VALUE']);
			$foundword+=$found;
			$rating+=($found*$val[$j]);
			if($foundword!=0 && $search[$i]['MUSTHAVE']==2) {
				return(0);
			}
		}

		if($foundword==0 && $search[$i]['MUSTHAVE']==1) {
			return(0);
		}
	}
	return($rating);
}

function search_text($text) {
	global $search;

  $text=str_replace("<br", " <br", $text);
	$text=strip_tags(mb_strtolower($text));
	$count=0;

	for($i=0;$i<count($search);$i++) {
		if($search[$i]['MUSTHAVE']==1) {
			if(strpos($text,$search[$i]['VALUE']) !== false) {
				$count++;
			} else {
				return(0);
			}
		} else {
			if(strpos($text,$search[$i]['VALUE']) !== false) {
				$count++;
				$search[$i]['FOUND']++;
			}
		}
	}
	return($count);
}

function count_search($searchtext) {
	global $search, $sword;

	$searchtext=str_replace(',',' ',$searchtext);
	$array=split(" ",$searchtext);
	$musthave=0;
	$slash=0;
	$count=0;

	foreach ($array as $value) {
		if($value=="") {continue;}
		if($value=="+") {$musthave=1; continue;}
		if($value=="-") {$musthave=2; continue;}
		if(substr($value,0,1)=="+") {
			$musthave=1;
			$value=substr($value,1);
		}
		if(substr($value,0,1)=="-") {
			$musthave=2;
			$value=substr($value,1);
		}

		if(substr($value,0,1)=="\"" && $slash==0) {
			$slash=1;
			$value=substr($value,1);
		}

		if(substr($value,-1)=="\"" && $slash==1) {
			$slash=0;
			$value=substr($value,0,-1);
		}

		$search[$count]['MUSTHAVE']=$musthave;

		switch($slash) {
			case 0:
				$search[$count]['VALUE'].=$value;
				break;
			case 1:
				$search[$count]['VALUE'].=$value." ";
				break;
		}

		if($slash==0) {
			$count++;
			$musthave=0;
		}
	}
	foreach($search as $value) {
		if($value['MUSTHAVE']!=2) {
			if($sword=="") {
				$sword="sword_list[]=".htmlentities($value['VALUE'], ENT_QUOTES);
			} else {
				$sword.="&amp;sword_list[]=".htmlentities($value['VALUE'], ENT_QUOTES);
			}
		}
	}

	$sword.="&amp;no_cache=1";
}

function cmp_search($a, $b) {
	if ($a['MUSTHAVE'] == $b['MUSTHAVE']) {
		return 0;
	}
	return ($a['MUSTHAVE'] > $b['MUSTHAVE']) ? -1 : 1;
}

function textcut($text,$size=200) {
  $shorttext=str_replace("<br", " <br", $text);
  $shorttext=substr(strip_tags($shorttext),0,$size);

	if(strlen($text) >= $size) {
		$shorttext=substr($shorttext,0,strrpos($shorttext," "))."&hellip;";
	}
	return($shorttext);
}

function realurl_link($id,$parameter=false,$language=0) {
	$db=new DB_MYSQL;

	$origparams=($language==0)?"id=".$id:"id=".$id."&L=".$language;
	$sql="SELECT * FROM tx_realurl_urlencodecache WHERE page_id=".$id." AND origparams='".$origparams."'";
	$db->query($sql);
	if($db->num_rows()) {
	  $db->next_record();

	  $url=$db->f("content");
		if($parameter) $url.="?";
	} else {
		$url="index.php?id=".$id;
		if($language!=0) $url.="&L=".$language;
		if($parameter) $url.="&";
	}
	return $url;
}

function realurl_news($id,$parameter=false) {
	$url="";
	$db=new DB_MYSQL;
	$sql="SELECT * FROM tx_realurl_urlencodecache WHERE origparams LIKE \"".$id."%\"";

	$db->query($sql);
	if($db->num_rows()) {
	  $db->next_record();
	  $url=$db->f("content");
		if($parameter) { $url.="&"; }
	} else {
		$url="index.php?".$id;
		if($parameter) { $url.="&"; }
	}
	return $url;
}


function resizeImage($filename, $dest, $width, $height="999999", $pictype = "")
{
  $format = strtolower(substr(strrchr($filename,"."),1));
  switch($format)
  {
   case 'gif' :
   $type ="gif";
   $img = imagecreatefromgif($filename);
   break;
   case 'png' :
   $type ="png";
   $img = imagecreatefrompng($filename);
   break;
   case 'jpg' :
   $type ="jpg";
   $img = imagecreatefromjpeg($filename);
   break;
   case 'jpeg' :
   $type ="jpg";
   $img = imagecreatefromjpeg($filename);
   break;
   default :
   die ("ERROR; UNSUPPORTED IMAGE TYPE");
   break;
  }

  list($org_width, $org_height) = getimagesize($filename);
  $xoffset = 0;
  $yoffset = 0;
  if ($pictype == "thumb") // To minimize destortion
  {
   if ($org_width / $width > $org_height/ $height)
   {
     $xtmp = $org_width;
     $xratio = 1-((($org_width/$org_height)-($width/$height))/2);
     $org_width = $org_width * $xratio;
     $xoffset = ($xtmp - $org_width)/2;
   }
   elseif ($org_height/ $height > $org_width / $width)
   {
     $ytmp = $org_height;
     $yratio = 1-((($width/$height)-($org_width/$org_height))/2);
     $org_height = $org_height * $yratio;
     $yoffset = ($ytmp - $org_height)/2;
   }
  } else {
     $xtmp = $org_width/$width;
     $new_width = $width;
     $new_height = $org_height/$xtmp;
     if ($new_height > $height){
       $ytmp = $org_height/$height;
       $new_height = $height;
       $new_width = $org_width/$ytmp;
     }
     $width = round($new_width);
     $height = round($new_height);
  }


  $img_n=imagecreatetruecolor ($width, $height);
  imagecopyresampled($img_n, $img, 0, 0, $xoffset, $yoffset, $width, $height, $org_width, $org_height);

  if($type=="gif") {
   imagegif($img_n, $dest);
  } elseif($type=="jpg") {
   imagejpeg($img_n, $dest);
  } elseif($type=="png") {
   imagepng($img_n, $dest);
  } elseif($type=="bmp") {
   imagewbmp($img_n, $dest);
  }
#  print $width."---".$height;
#  Return true;
  return array($width,$height);
}

function Ueberschrift($Text,$Art=1,$Farbe=1,$Transparent=0) {
	if($Farbe==1) { $color=array(255, 86, 9); }
	if($Farbe==2) { $color=array(8, 32, 74); }

	switch($Art) {
		case 1:  // Fette Überschrift
/*
			list($image,$width,$height)=Create_Headline($Text,"headline_big_".$Transparent,"big",$color,$Transparent);
			print "<img src='".$image."' width='".$width."' height='".$height."' alt='".$Text."' />";
*/
			print "<h1>".$Text."</h1>";
			break;
		case 2:  // Normale Überschrift
/*
			list($image,$width,$height)=Create_Headline($Text,"headline_small_".$Transparent,"small",$color,$Transparent);
			print "<span class='underline'><img src='".$image."' width='".$width."' height='".$height."' alt='".$Text."' /></span>";
*/
			print "<h2>".$Text."</h2>";
			break;
		case 3:  // Normale Überschrift ohne Unterstrich
/*
			list($image,$width,$height)=Create_Headline($Text,"headline_small_".$Transparent,"small",$color,$Transparent);
			print "<img src='".$image."' width='".$width."' height='".$height."' alt='".$Text."' />";
*/
			print "<h3>".$Text."</h3>";
			break;
		case 4:  // Normale Überschrift ohne Unterstrich
/*
			list($image,$width,$height)=Create_Headline($Text,"headline_bold_".$Transparent,"bold",$color,$Transparent);
			print "<img src='".$image."' width='".$width."' height='".$height."' alt='".$Text."' />";
*/
			print "<h4>".$Text."</h4>";
			break;
	}
}

function Create_Headline($title,$name,$size="big",$color=array(255, 86, 9),$transparent=0) {
	GLOBAL $typo3_temp_pics;

	$checksum=sha1($title);
	$headline=$typo3_temp_pics.$name.$checksum.".png";

	if( ! file_exists ($headline)) {
		if($size=="big") {
			$size_x=437;
			$size_y=25;

			$text_x=0;
			$text_y=20;
			$font_size=17;

			$sf=5;
			$font = "typo3/t3lib/fonts/ftc.ttf";
		} elseif($size=="small") {
			$size_x=437;
			$size_y=29;

			$text_x=0;
			$text_y=21;
			$font_size=12;

			$sf=5;
			$font = "typo3/t3lib/fonts/ftc.ttf";
		} elseif($size=="bold") {
			$size_x=437;
			$size_y=29;

			$text_x=0;
			$text_y=21;
			$font_size=12;

			$sf=5;
			$font = "typo3/t3lib/fonts/ftbc.ttf";
		}

		do {
			$split=SplitTitle($title,$font,$font_size,$size_x,$sf);
			$titles[]=$split[0];
			$title=$split[1];
		} while($title!="");

		$size_max=count($titles)*$size_y;

		$maskImg=imagecreate($size_x*$sf, $size_max*$sf);
		$background_color=ImageColorAllocate($maskImg, 255,255,255);
		$text_color = ImageColorAllocate($maskImg, $color[0], $color[1], $color[2]);

		for($i=0;$i<count($titles);$i++) {
			ImageTTFText($maskImg, $font_size*$sf, 0, $text_x*$sf, ((($i*$size_y)+$text_y)*$sf), $text_color, $font, $titles[$i]);
		}
		if($transparent==1) ImageColorTransparent($maskImg,$background_color);

		imagepng($maskImg, $headline);
		imagedestroy($maskImg);

		$cmd="/usr/bin/convert -geometry ".$size_x."x".$size_max."! $headline $headline";
		$ret = exec($cmd);
		return array($headline,$size_x,$size_max);
	} else {
		list($width, $height) = getimagesize($headline);
		return array($headline,$width,$height);
	}
}

function SplitTitle($title,$font,$font_size,$size_x,$sf) {
	$shorttitle=$title;
	$size=imagettfbbox($font_size*$sf, 0, $font, $shorttitle);

	while(($size[2]) > ($size_x*$sf)) {
		$pos=strrpos($shorttitle," ");
		if($pos===false) {
			$shorttitle=substr($shorttitle,0,-1);
		} else {
			$shorttitle=substr($shorttitle,0,$pos);
		}

		$size=imagettfbbox($font_size*$sf, 0, $font, $shorttitle);
	}
	$rest=trim(substr($title,strlen($shorttitle)));

	return(array($shorttitle,$rest));
}

function resizeImageIM($filename, $dest, $width, $height="999999", $pictype = "", $enlarge=false) {

#	print "--".$filename."--".$dest."--";

  $format = strtolower(substr(strrchr($filename,"."),1));
	if(!$enlarge) $param=">";
  switch($format)	{
		case 'bmp' :
		case 'gif' :
		case 'jpg' :
		case 'jpeg' :
		case 'pcx' :
		case 'png' :
		case 'tif' :
		case 'tiff' :
			break;
		case 'pdf' :
			break;
		default:
			die ("ERROR; UNSUPPORTED IMAGE TYPE");
			break;
	}
	shell_exec("convert -antialias -fx 'u' -thumbnail '".$width."x".$height.$param."' ".$filename." ".$dest);
	list($width,$height)=GetImageSizeIM($dest);
  return array($width,$height);

}
function GetImageSizeIM($filename) {
	$size=trim(shell_exec("identify -format \"%w %h\" ".$filename));
	$parts=explode(" ",$size);
	return array($parts[0],$parts[1]);
}

function Create_Thumbnail($image,$imagepath,$name,$width,$height="999999",$pictype="", $enlarge=false) {
	GLOBAL $typo3_temp_pics;

	$ext=strtolower(strrchr($image,"."));
	if($ext==".bmp" || $ext==".pcx" || $ext==".pdf" || $ext==".tif" || $ext==".tiff") $ext=".jpg";
	$checksum=sha1($image);
	$thumb=$typo3_temp_pics.$name.$checksum.$ext;

	if( ! file_exists ($thumb)) {
		$size=resizeImageIM($imagepath.$image,$thumb,$width,$height,$pictype,$enlarge);
		$width=$size[0];
		$height=$size[1];
	} else {
		list($width, $height) = getimagesize($thumb);
	}
	return array($thumb,$width,$height);
}

function formatFilesize($size) {
	$UNITS=array("","K","M","G");
	$UseUnit=0;

	while($size>1024) {
		$size/=1024;
		$UseUnit++;
	}
	return round($size,1)." ".$UNITS[$UseUnit]."Bytes";
}

function sendMail($myname, $myemail, $contactname, $contactemail, $subject, $message) {
  $headers = "MIME-Version: 1.0\n";
  $headers .= "Content-type: text/plain; charset=utf-8\n";
  $headers .= "X-Mailer: php\n";
  $headers .= "From: \"".$myname."\" <".$myemail.">\n";
  return(mail("\"".$contactname."\" <".$contactemail.">", $subject, $message, $headers, "-f <".$myemail.">"));
 }


function HtD($wert) {
  return $wert;
}

function DtH($wert) {
//$wert=htmlspecialchars($wert, ENT_QUOTES);
  $wert=htmlentities($wert, ENT_QUOTES);
  return $wert;
}

function PtH($wert) { // Program to HTML
  $wert=htmlentities($wert, ENT_QUOTES, "UTF-8");
  return $wert;
}

function PtM($wert) {  // Program to Mail
  $wert=stripslashes($wert);
  return $wert;
}

function HtP($wert) {
  $wert=html_entity_decode($wert, ENT_QUOTES, "UTF-8");
  return $wert;
}

function trimer($wert) {
	return strip_tags(trim($wert));
}


?>