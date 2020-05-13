<?php
# $Id: mb_listGUIs.php 6555 2010-07-04 09:56:09Z verenadiewald $
# http://www.mapbender.org/index.php/mb_listGUIs.php
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

function mb_listGUIs($arrayGUIs){
	if(count($arrayGUIs) === 0) {
		echo "<h1>Error</h1>";
		echo "<p>There are no GUIs available for this user.</p>";
		printf("<p><a href=\"../php/mod_logout.php?%s\"><img src=\"../img/button_gray/logout_off.png\" onmouseover=\"this.src='../img/button_gray/logout_over.png'\" onmouseout=\"this.src='../img/button_gray/logout_off.png'\" title=\"Logout\"></a></p>",SID);
		
		return;
	}
	
	$v = array();
	$t = array();

	$sql  = "SELECT DISTINCT gui_id,gui_name,gui_description, ggc.*,gc.category_name,gc.category_description ";
	$sql .= "FROM gui g ";
	$sql .= "LEFT JOIN gui_gui_category ggc ON g.gui_id = ggc.fkey_gui_id ";
	$sql .= "LEFT JOIN gui_category gc ON (ggc.fkey_gui_category_id = gc.category_id) ";
	$sql .= "WHERE gui_id IN (";

	for($i = 0; $i < count($arrayGUIs); $i++) {
		if($i > 0) {
			$sql .= ",";
		}
		
		$sql .= "$".($i + 1);
		
		array_push($v,$arrayGUIs[$i]);
		array_push($t,'s');
	}
	
	$sql .= ") ORDER BY gc.category_name, gui_name";

	$result = db_prep_query($sql,$v,$t);
	
	$category = NULL;
	
	echo "<span style='color:#000000;font-size:24px;font-weight:bold;'>Ma</span>".
		 "<span style='color:#0000CE;font-size:24px;font-weight:bold;'>p</span>".
		 "<span style='color:#C00000;font-size:24px;font-weight:bold;'>b</span>".
		 "<span style='color:#000000;font-size:24px;font-weight:bold;'>ender</span>".
		 "<span style='font-size:24px;font-weight:bold;'> - "._mb('available applications')." </span>";
		
	printf("<span style='float:right;'><a href=\"../php/mod_logout.php?%s\"><img src=\"../img/button_gray/logout_off.png\" onmouseover=\"this.src='../img/button_gray/logout_over.png'\" onmouseout=\"this.src='../img/button_gray/logout_off.png'\" title=\"Logout\"></a></span>",SID);
	
	echo "<div id='guiListTabs' class='guiListTabs'>";
	
	echo "<ul>";
	
	$total_guis = 0;
	$totalCategories = 0;
	$divHtml = "";
	while($row = db_fetch_array($result)){
		
		if($category !== $row["category_name"]) {
			if($divHtml != "") {
				$divHtml .= '</ul></div>';
			}
			$category = $row["category_name"];
			
			if(strlen($row["category_name"]) > 0) {
				echo '<li><a href="#guiListTab-'.$totalCategories.'">' . $row["category_name"] . '</a></li>';
			}
			else {
				echo '<li><a href="#guiListTab-'.$totalCategories.'">'._mb('Others').'</a></li>';
			}
			
			
			if($row["category_description"] == '') {
				$row["category_description"] = '&nbsp;&nbsp;&nbsp;&nbsp;';
			}
			$divHtml .= '<div id="guiListTab-' . $totalCategories . '">';
			$divHtml .= '<div class="categoryDescription">';
			$divHtml .= "<p><em>".$row["category_description"]."</em></p>";
			$divHtml .= '</div>';
			$divHtml .=	'<ul class="gui_list">';
			
			$totalCategories++;
		}
		
		if(count($row["category_name"]) === 0 && !$dummyCategorySet && $divHtml == "")  {
			echo '<li><a href="#guiListTab-0">'._mb('Others').'</a></li>';
		
			$divHtml .= '<div id="guiListTab-0">';
			$divHtml .= '<div class="categoryDescription">';
			$divHtml .= "<p><em>&nbsp;&nbsp;&nbsp;&nbsp;</em></p>";
			$divHtml .= '</div>';
			$divHtml .=	'<ul class="gui_list">';
			
			$dummyCategorySet = true;
		} 
				
		$url   = "index.php?".strip_tags(SID)."&gui_id=".$row["gui_id"];
		$divHtml .= "<li><div>".
					"<a class='guiLink' href='".$url."'>".$row['gui_name']."</a></div>".
					"<div class='guiDescription'><em>".$row["gui_description"]."</em></div></li>";
		
		$total_guis++;
	}
	
	echo "</ul>";
	
	if($divHtml != "") {
		$divHtml .= '</ul></div>';
	}
	
	echo $divHtml;
	
	echo "</div>";	
}
?>