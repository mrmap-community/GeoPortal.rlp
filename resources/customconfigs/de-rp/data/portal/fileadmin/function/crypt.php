<?php
define("KEY","GEOPortal.rlp");

function bytexor($a,$b,$l) {
	$c="";
	for($i=0;$i<$l && $i<strlen($a);$i++) {
		$c.=$a{$i}^$b{$i};
	}
	return($c);
}

function binmd5($val) {
	return(pack("H*",md5($val)));
}

function decrypt_md5($msg,$heslo) {
	$key=$heslo;$sifra="";
	$key1=binmd5($key);
	while($msg!='') {
		$m=substr($msg,0,16);
		$msg=substr($msg,16);
		$sifra.=$m=bytexor($m,$key1,16);
		$key1=binmd5($key.$key1.$m);
	}
	return($sifra);
}

function crypt_md5($msg,$heslo) {
	$key=$heslo;$sifra="";
	$key1=binmd5($key);
	while($msg!='') {
		$m=substr($msg,0,16);
		$msg=substr($msg,16);
		$sifra.=bytexor($m,$key1,16);
		$key1=binmd5($key.$key1.$m);
	}
	return($sifra);
}

function HexToStr($Hexcode) {
	$String="";
	for($i=0;$i<strlen($Hexcode);$i+=2) {
		$String.=sprintf("%c",hexdec(substr($Hexcode,$i,2)));
	}
	return $String;
}


function CodeParameter($parameter) {
	$Save=HexToStr(sprintf("%08x",crc32($parameter)));
	$code=base64_encode(crypt_md5($parameter.$Save,KEY));
	while(substr($code,-1)=="=") $code=substr($code,0,strlen($code)-1);
	return $code;
}

function DecodeParameter($code) {
	$code=str_replace(" ","+",$code);
	while(strlen($code)%4!=0) $code.="=";
	$code=decrypt_md5(base64_decode($code),KEY);
	$Save=substr($code,-4);
	$Inhalt=substr($code,0,strlen($code)-4);
	if($Save==HexToStr(sprintf("%08x",crc32($Inhalt)))) {
		$parameter=$Inhalt;
		$all=split("ÿ",$parameter);
		for($i=0;$i<count($all);$i++) {
			$single=split("=",$all[$i],2);
			$_REQUEST[$single[0]]=$single[1];
		}
	} else {
		return false;
	}
	return true;
}
?>
