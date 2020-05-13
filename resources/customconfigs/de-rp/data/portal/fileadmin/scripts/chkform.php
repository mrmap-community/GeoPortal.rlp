<script type="text/javascript" src="fileadmin/scripts/chkform.js"></script>
<?php
function chkFormular() {
	global $Message;
	global $Default;
	global $Checkfields;

	$Felder=explode("|",$Checkfields);

	$Fehler="";

    for($i=0;$i<count($Felder);$i++) {
		$Check=true;
		$Optionen=explode(",",$Felder[$i]);
		$ChkFeld=$_REQUEST[$Optionen[0]];
		$ChkPflicht=$Optionen[1];
		$ChkArt=$Optionen[2];
		
		$Inhalt=chkInhalt($Optionen[0]);
		
		$Check=chkPflicht($ChkPflicht,$Inhalt);
		
		if($Inhalt && $Check) {
			switch($ChkArt) {
				case "int":
					$Check=chkInt($ChkFeld);
					break;
				case "float":
					$Check=chkFloat($ChkFeld);
					break;
				case "sint":
					$Check=chkSInt($ChkFeld);
					break;
				case "sfloat":
					$Check=chkSFloat($ChkFeld);
					break;
				case "plz":
					$Check=chkPLZ($ChkFeld);
					break;
				case "email":
					$Check=chkEmail($ChkFeld);
					break;
				case "time":
					$Check=chkTime($ChkFeld);
					break;
				case "date":
					$Check=chkDate($ChkFeld);
					break;
			}
		}

		if(!$Check) {
			$Fehler .= meldung($Optionen[0]);
		}
	}

	if($Fehler!="") {

		if($Message["ChkError"]=="") $Message["ChkError"]="Formular fehlerhaft ausgefüllt!";
		print '
			<div class="highlight">
				<p>
					<strong>'.$Message["ChkError"].'</strong><br />
					'.$Fehler.'
				</p>
			</div>';
		return false;
	}
	
	$Fehler="";
	foreach($Default as $key => $value) {
		if($_REQUEST[$key]==$value) {
			$found=false;
			// Pflichtfelder auf sinnvolle Daten prüfen
			for($i=0;$i<count($Felder);$i++) {
				$Optionen=explode(",",$Felder[$i]);
				if($Optionen[0]==$key) {
					if(!chkPflicht($Optionen[1],false)) {
						$found=true;
						$Fehler .= meldung($key);
					}
				}
			}
			// Nicht-Pflichtfelder mit unsinnigen Daten leeren
			if(!$found) {
				$_REQUEST[$key]="";
			}
		}
	}
	
	if($Fehler!="") {
		if($Message["ChkIncomplete"]=="") $Message["ChkIncomplete"]="Bitte füllen Sie das Formular mit sinnvollen Daten aus.";
		print '
			<div class="highlight">
				<p>
					<strong>'.$Message["ChkIncomplete"].'</strong><br />
					'.$Fehler.'
				</p>
			</div>';
		return false;
	}
	return true;
	
	
}

function chkInhalt($feld) {
	$Check=false;
	if(substr($feld,-2)=="[]") {  //array
		if(count($_REQUEST[substr($feld,0,-2)])>0) {
			$Check=true;
		}
	} else {
		if($_REQUEST[$feld]!="") {
			$Check=true;
		}
	}
	return $Check;
}

function chkPflicht($ChkPflicht,$Inhalt) {
	if($ChkPflicht=="true") {
		return $Inhalt;
	} elseif ($ChkPflicht=="false") {
		return true;
	} else {
		$regex = '.+:.+=.*';
		if(eregi($regex,$ChkPflicht)) {
			$Optionen=explode(":",$ChkPflichtht);
			$Teile=explode("=",$Optionen[1]);
			if($Teile[1]=="true") {
				$Check=($_REQUEST[$Teile[0]]!="");
			} else {
				$Check=($_REQUEST[$Teile[0]]==$Teile[1]);
			}
			if($Check) {  // Bedingung erfüllt - Feld muss gefüllt sein
				return $Inhalt;
			} else {
				return true; // Bedingung nicht erfüllt - somit ist dieses Feld kein Pflichtfeld - somit ist der Füllstatus egal
			}
		} else {
			return false; // ungültiger String
		}
	}
}

function chkInt($zahl) {
	$regex = '^[0-9]+$';
	return (eregi($regex, $zahl));
}

function chkFloat($zahl) {
	$regex = '^[0-9]+([\.,][0-9]+)?$';
	return (eregi($regex, $zahl));
}

function chkSInt($zahl) {
	$regex = '^[\+-]?[0-9]+$';
	return (eregi($regex, $zahl));
}

function chkSFloat($zahl) {
	$regex = '^[\+-]?[0-9]+([\.,][0-9]+)?$';
	return (eregi($regex, $zahl));
}

function chkPLZ($plz) {
	$regex = '^[0-9]{5}$';
	return (eregi($regex, $plz));
}

function chkEmail($email) {
	$regex = '^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+@(([-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.)([-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$))';
	return (eregi($regex, $email));
}

function chkTime($zeit) {
	$regex = '^[0-9]{1,2}(:[0-9]{2}){1,2}$';
	if(eregi($regex, $zeit)) {
		$Teile=explode(":",$zeit);
		$Hour=$Teile[0];
		$Minute=$Teile[1];
		if(count($Teile)==3) {
			$Second=$Teile[2];
		} else {
			$Second=1;
		}
		if($Hour>23 || $Minute>59 || $Second>59) {
			return false;
		}
		return true;
	}
	return false;
}

function chkDate($datum) {
	$regex = '^([0-9]{1,2}\.){2}([0-9]{2}){1,2}$';
	if(eregi($regex, $datum)) {
		$Teile=explode(".",$datum);
		$Day=$Teile[0];
		$Month=$Teile[1];
		$Year=$Teile[2];

		if ($Year<100) {
			$Year+=2000;
		}
		if (($Day>=1) && ($Day<=31) && ($Month>=1) && ($Month<=12)) {  //gültige Werte
			if(($Month==1) || ($Month==3) || ($Month==5) || ($Month==7) || ($Month==8) || ($Month==10) || ($Month==12)) {  // lange Monate
				return true;
			}	else {
				if (($Day<=30) && (($Month==4) || ($Month==6) || ($Month==9) || ($Month==11))) {  // kurze Monate
					return true;
				} else {
					if (($Day<=28)) { // Februar
						return true;
					} else {
						if (($Year%4)==0) {  // Schaltjahr
							if (($Year%100)!=0) {
								return true;
							} else {
								if (($Year%400)==0) {
									return true;
								}
							}
						}
					}
				}
			}
		}
	}
	return false;
}

function meldung($feld) {
	global $Message;
	
	$fehler="";
	if ($Message[$feld]!="") {
		$fehler=$Message[$feld];
	} else {
		if($Message["ChkGeneral"]=="") $Message["ChkGeneral"]="Bitte geben Sie korrekte Daten in das Feld ###FIELD### ein!";
		$fehler=str_replace("###FIELD###",$feld,$Message["ChkGeneral"]);
	}
	return $fehler."<br />";
}


?>