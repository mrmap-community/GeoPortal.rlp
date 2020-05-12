function zoomOrt(x,y){
 myCoords[0] = x;
 myCoords[1] = y;
 parent.mb_repaintScale("mapframe1",x,y,scaleCity);
 setMarker();
}

function zoom_me(x,y,zoom) {
 parent.mb_repaintScale('mapframe1',x,y,zoom);
}

function delMarker() {
 myCoords[0] = 0;
 myCoords[1] = 0;
 parent.mb_arrangeElement('mapframe1','marker',-20,-20);
 parent.zoom("mapframe1", true, 1.001);
}

/*
function setMarker(){
 if (myCoords[0] > 0) {
  x = myCoords[0];
	y = myCoords[1];
  var scale = parent.mb_getScale(parent.mod_scaleSelect_target);

	if (scale < 5001){
	 var width  = 30;
	 var height = 30;
	}
	if (scale>=5001 && scale<25001){
	 var width  = 20;
	 var height = 20;
	}
	if (scale > 25001) {
	 var width  = 10;
	 var height = 10;
	}
  var temp_str = "<img src='../img/button_gray/marker_fett.gif' width='" + width + "' height='" + height + "'>";
  var pos = parent.makeRealWorld2mapPos('mapframe1',x,y);
	parent.writeTag('mapframe1','marker',temp_str);
	parent.mb_arrangeElement('mapframe1','marker',(pos[0] - (width/2)),(pos[1]- (height/2)));
 }
}
*/

function setChar(character){
   parent.StreetResultFrame.document.location.href='streets.php?char=' + character;
}

function highlight(x,y){
      parent.mb_showHighlight("mapframe1",x,y);
      parent.mb_showHighlight("overview",x,y);
}

function hideHighlight(){
   parent.mb_hideHighlight("mapframe1");
   parent.mb_hideHighlight("overview");
}

function sendCharGem(chr) {
  document.forms[0].char_gem.value = chr;
  document.forms[0].gem.value = "";
  document.forms[0].char_str.value = "";
  document.forms[0].str.value = "";
  document.forms[0].submit();
}

function sendGem(chr) {
//  parent.mb_hideSelect();
  document.forms[0].gem.value = chr;
  document.forms[0].submit();
}

function sendCharStr(chr) {
  document.forms[0].char_str.value = chr;
  document.forms[0].str.value = "";
  document.forms[0].submit();
}

function sendStr(chr,chrkey) {
//  parent.mb_hideSelect();
  document.forms[0].str.value = chr;
  document.forms[0].strschl.value = chrkey;
  document.forms[0].submit();
}

function sendgemeinde(chr) {
  document.forms[0].char_gem.value = chr;
  document.forms[0].submit();
}

function setcity() {
  document.forms[0].submit();
}
