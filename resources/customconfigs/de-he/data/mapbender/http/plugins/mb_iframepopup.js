/**
 * Package: mb_iframepopup
 *
 * Description:
 * 
 * 
 * 
 * 
 * 
 *
 * Files:
 *  - ../plugins/mb_iframepopup.js
 *
 * SQL:
 * > INSERT INTO gui_element(fkey_gui_id, e_id, e_pos, e_public, e_comment, e_title, e_element, e_src, e_attributes,
 * >  e_left, e_top, e_width, e_height, e_z_index, e_more_styles, e_content, e_closetag, e_js_file, e_mb_mod,
 * > e_target, e_requires, e_url) VALUES('Administration','mb_iframepopup',7,1,'iframepopup','',
 * > 'div','','',NULL ,NULL ,NULL ,NULL ,NULL ,'','','div','../plugins/mb_iframepopup.js','','','','');
 *
 * Maintainer:
 * http://www.mapbender.org/User:Karim_Malhas
 *
 * Parameters:
 *
 * License:
 * Copyright (c) 2009, Open Source Geospatial Foundation
 * This program is dual licensed under the GNU General Public License
 * and Simplified BSD license.
 * http://svn.osgeo.org/mapbender/trunk/mapbender/license/license.txt
 */

var $iframepopup = $(this);


var IframePopup = function(o) {
		$('a').click(function(e) {
			e.preventDefault();
			var $this = $(this);
			var horizontalPadding = 30;
			var verticalPadding = 30;
	        $('<iframe src="' + this.href + '" />').dialog({
	            title: ($this.attr('title')) ? $this.attr('title') : 'External Site',
	            autoOpen: true,
	            width: 910,
	            height: 750,
	            modal: true,
	            resizable: true,
				autoResize: true,
	            overlay: {
	                opacity: 0.5,
	                background: "black"
	            }
	        }).width(910 - horizontalPadding).height(750 - verticalPadding);	        
		});
	};


Mapbender.events.init.register(function(){
     $iframepopup.mapbender(new IframePopup(options));
});

