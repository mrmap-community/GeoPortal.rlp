<?php
# $Id: mod_stickyLogos.php 6673 2010-08-02 13:52:19Z christoph $
# http://www.mapbender.org/index.php/mod_featureInfoRedirect.php
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
?>

/*
* sticky IFRAME, right from the main mapframe "mapframe1"
*/

eventAfterMapRequest.register(function () {
	mod_stickyLogos_position();
});

function mod_stickyLogos_position(){
	var leftOffset = 25;
	var borderOffset = 10;
	
	var logoImg = document.getElementById("logos").style;
	var mapframe = document.getElementById("mapframe1").style;

	logoImg.left = (parseInt(mapframe.left, 10) + parseInt(mapframe.width, 10) + leftOffset) + "px";
	logoImg.top = (parseInt(mapframe.top, 10) - borderOffset) + "px";
	//logoImg.height = (parseInt(mapframe.height, 10) + 2 * borderOffset) + "px";
}