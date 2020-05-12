var Marked=false;
var MarkedField;
var message = new Array();
var vorgabe = new Array();

var SingleWarning=false;
var Class="";
var ClassMissing="missing";
var ClassChange=false;

function chkFormular(form) {
	var Fehler="";
	var Jump=false;
	Felder=form.CHECK.value.split("|");

	if (ClassChange && Marked) {
		MarkedField.className=Class;
		Marked=false;
	}

	for(i=0;i<Felder.length;i++) {
		Optionen=Felder[i].split(",");
		ChkFeld=form.elements[Optionen[0]];
		ChkPflicht=Optionen[1];
		ChkArt=Optionen[2];
		Inhalt=true;
		Check=true;
		if(ChkArt=="box" || ChkArt=="radio") {
			Inhalt=isChecked(ChkFeld);
		} else if (ChkArt=="select") {
			Inhalt=isSelected(ChkFeld);
		} else {
			Inhalt=chkInhalt(ChkFeld);
			if(Inhalt && ChkFeld.value==vorgabe[Optionen[0]]) {
				Inhalt=false;
			}
		}
		Check=chkPflicht(ChkPflicht,Inhalt,form);  // prüfen, ob Pflichtfeld gefüllt

		if(Inhalt && Check) {  // nicht-Pflichtfelder nur prüfen, wenn sie gefüllt sind
			switch(ChkArt) {
				case "int":
					Check=chkInt(ChkFeld);
					break;
				case "float":
					Check=chkFloat(ChkFeld);
					break;
				case "sint":
					Check=chkSInt(ChkFeld);
					break;
				case "sfloat":
					Check=chkSFloat(ChkFeld);
					break;
				case "plz":
					Check=chkPLZ(ChkFeld);
					break;
				case "email":
					Check=chkEmail(ChkFeld);
					break;
				case "time":
					Check=chkTime(ChkFeld);
					break;
				case "date":
					Check=chkDate(ChkFeld);
					break;
			}
		}
		if(!Check) {
			Fehler+=meldung(Optionen[0]);
			if(ClassChange) ChkFeld.className=ClassMissing;
			if(SingleWarning) {
				alert(Fehler);
				if(ChkArt!="box" && ChkArt!="radio") {
					if(ClassChange) {
						Marked=true;
						MarkedField=ChkFeld;
					}
					ChkFeld.focus();
				}
				return false;
			} else {
				if(!Jump) {
					if(ChkArt!="box" && ChkArt!="radio") {
						ChkFeld.focus();
					}
					Jump=true;
				}
			}
		} else {
			if(ClassChange) ChkFeld.className=Class;
		}
	}
	if(Fehler!="") {
		if(message["ChkError"]=="") {
			alert("Formular unvollständig ausgefüllt!\n-----------------------------------------\n\n"+Fehler);
		} else {
			alert(message["ChkError"]+"\n-----------------------------------------\n\n"+Fehler);
		}
		return false;
	}
	
	for(var key in vorgabe) {
		ChkFeld=form.elements[key];
		if(ChkFeld) {
			if(ChkFeld.value==vorgabe[key]) {
				found=false;
				// Pflichtfelder auf sinnvolle Daten prüfen
				for(var i=0;i<Felder.length;i++) {
					var Optionen=Felder[i].split(",");
					if(Optionen[0]==key) {
						if(!chkPflicht(Optionen[1],false,form)) {
							found=true;
							Fehler += meldung(key);
							if(ClassChange) ChkFeld.className=ClassMissing;
							if(SingleWarning) {
								alert(Fehler);
								if(ClassChange) {
									Marked=true;
									MarkedField=ChkFeld;
								}
								ChkFeld.focus();
								return false;
							} else {
								if(!Jump) {
									ChkFeld.focus();
									Jump=true;
								}
							}
						} else {
							if(ClassChange) ChkFeld.className=Class;
						}
					}
				}
				// Nicht-Pflichtfelder mit unsinnigen Daten leeren
				if(!found) {
					ChkFeld.value="";
				}
			}
		}
	}
	if(Fehler!="") {
		if(message["ChkIncomplete"]=="") {
			alert("Bitte füllen Sie das Formular mit sinnvollen Daten aus.\n-----------------------------------------\n\n"+Fehler);
		} else {
			alert(message["ChkIncomplete"]+"\n-----------------------------------------\n\n"+Fehler);
		}
		return false;
	}
		
	return true;
}

function chkInhalt(text) {
	if(text.value!="") {
		return true;
	}
	return false;
}

function chkPflicht(ChkPflicht,Inhalt,Formular) {
	var Check;
	var Optionen;
	var Teile;
	if(ChkPflicht=="true") {
		return Inhalt;
	} else if (ChkPflicht=="false") {
		return true;
	} else {
		if(ChkPflicht.search(/^.+:.+=.*$/)>=0) {
			Optionen=ChkPflicht.split(":");		
			Teile=Optionen[1].split("=");
			if(Teile[1]=="true") {   // nur prüfen, ob Feld ausgewählt wurde
				switch(Optionen[0]) {
					case "box":
					case "radio":
						Check=isChecked(Formular.elements[Teile[0]]);
						break;
					case "select":
						Check=isSelected(Formular.elements[Teile[0]]);
						break;
					default:
						Check=chkInhalt(Formular.elements[Teile[0]]);
						break;
				}
			} else { // Prüfen, ob Feldinhalt der Prüfbedingung entspricht
				switch(Optionen[0]) {
					case "box":
					case "radio":
						Check=valueChecked(Formular.elements[Teile[0]],Teile[1]);
						break;
					case "select":
						Check=valueSelected(Formular.elements[Teile[0]],Teile[1]);
						break;
					default:
						Check=valueInhalt(Formular.elements[Teile[0]],Teile[1]);
						break;
				}
			}
			if(Check) {  // Bedingung erfüllt - Feld muss gefüllt sein
				return Inhalt;
			} else {
				return true; // Bedingung nicht erfüllt - somit ist dieses Feld kein Pflichtfeld - somit ist der Füllstatus egal
			}
		} else {
			return false; // ungültiger String
		}
	}
}

function chkInt(zahl) {
	if(zahl.value.search(/^[0-9]+$/)==0) {
		return true;
	}
	return false;
}

function chkFloat(zahl) {
	if(zahl.value.search(/^[0-9]+([\.,][0-9]+)?$/)==0) {
		return true;
	}
	return false;
}

function chkSInt(zahl) {
	if(zahl.value.search(/^[\+-]?[0-9]+$/)==0) {
		return true;
	}
	return false;
}

function chkSFloat(zahl) {
	if(zahl.value.search(/^[\+-]?[0-9]+([\.,][0-9]+)?$/)==0) {
		return true;
	}
	return false;
}

function chkPLZ(plz) {
	if(plz.value.search(/^[0-9]{5}$/)==0) {
		return true;
	}
	return false;
}

function chkEmail(email) {
	if(email.value.search(/^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,4}$/i)==0) {
		return true;
	}
	return false;
}

function isChecked(feld) {
	var wahl=false;
	if(feld.checked) { wahl=true; }
	for(var i=0;i<feld.length;i++) {
		if(feld[i].checked) {
			wahl=true;
		}
	}
	return wahl;
}

function isSelected(feld) {
	if(feld.selectedIndex>0) {
		return true;
	}
	return false;
}

function valueChecked(feld,wert) {
	var wahl=false;
	if(feld.checked) {
		if(feld.value==wert) {
			wahl=true; 
		}
	}
	for(var i=0;i<feld.length;i++) {
		if(feld[i].checked) {
			if(feld[i].value==wert) {
				wahl=true; 
			}
		}
	}
	return wahl;
}

function valueSelected(feld,wert) {
	if(feld.options[feld.selectedIndex].value==wert) {
		return true;
	}
	return false;
}

function valueInhalt(feld,wert) {
	if(feld.value==wert) {
		return true
	}
	return false;
}

function chkTime(zeit) {
	if(zeit.value.search(/^[0-9]{1,2}(:[0-9]{2}){1,2}$/)==0) {
		Teile=zeit.value.split(":");
		Hour=Teile[0];
		Minute=Teile[1];
		if(Teile.length==3) {
			Second=Teile[2];
		} else {
			Second=1;
		}
		if(Hour>23 || Minute>59 || Second>59) {
			return false;
		}
		return true;
	}
	return false;
}

function chkDate(datum) {
	if(datum.value.search(/^([0-9]{1,2}\.){2}([0-9]{2}){1,2}$/)==0) {
		Teile=datum.value.split(".");
		Day=Teile[0];
		Month=Teile[1];
		Year=Teile[2];

		if (Year<100) {
			Year+=2000;
		}
		if ((Day>=1) && (Day<=31) && (Month>=1) && (Month<=12)) {  //gültige Werte
			if((Month==1) || (Month==3) || (Month==5) || (Month==7) || (Month==8) || (Month==10) || (Month==12)) {  // lange Monate
				return true;
			}	else {
				if ((Day<=30) && ((Month==4) || (Month==6) || (Month==9) || (Month==11))) {  // kurze Monate
					return true;
				} else {
					if ((Day<=28)) { // Februar
						return true;
					} else {
						if ((Year%4)==0) {  // Schaltjahr
							if ((Year%100)!=0) {
								return true;
							} else {
								if ((Year%400)==0) {
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

function meldung(feld) {
	fehler="";
	if (message[feld]) {
		fehler=message[feld];
	} else {
		if(message["ChkGeneral"]=="") {
			fehler="Bitte geben Sie korrekte Daten in das Feld '"+feld+"' ein!";
		} else {
			fehler=message["ChkGeneral"].replace(/###FIELD###/,feld);
		}
	}
	return fehler+"\n";
}

function printerror() {
	if (ClassChange && Marked) {
		MarkedField.className=Class;
		Marked=false;
	}
	alert(printerror.arguments[0]);
	if(printerror.arguments.length==2) {
		if(ClassChange) {
			Marked=true;
			MarkedField=printerror.arguments;
			ChkFeld.className=ClassMissing;
		}
		ChkFeld.focus();
	}
}

function clearField(feld) {
  if (feld.value == vorgabe[feld.name]) { 
  	feld.value = ""
  }
}
