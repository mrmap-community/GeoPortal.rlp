/***************************************************************
*  Copyright notice
*
*  (c) 2008 Peter Klein (peter@umloud.dk)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/*
	SlimboxMagnify v1.2 - A companion script for Slimbox
	(c) 2007-2008 Peter Klein <peter@umloud.dk>
*/

var slimboxMagnify = {
	init: function(options){

		this.options = $extend({
			magnifyImg: 'magnify.png',					// Magnify icon image
			magnifyImgWidth: 24,						// Width of Magnify icon image
			magnifyImgHeight: 24,						// Height of Magnify icon image
			magnifyImgMarginB: 8,						// Bottom Margin for Magnify icon image
			magnifyImgMarginR: 8,						// Right Margin for Magnify icon image
			magnifyTitle: 'Click on image to magnify',	// Text for title attribute when hovering over magnifyicon
			magnifyDuration: 400,						// Duration of transition
			magnifyTransition: Fx.Transitions.Sine.easeInOut	// Transition for opacity
		}, options || {});
		
		this.magnifyWrapper = new Element('div').setProperty('title',this.options.magnifyTitle).setStyles({
			position: 'absolute',
			display: 'block',
			cursor: 'pointer',
			opacity: 0,
			background: 'transparent url('+this.options.magnifyImg+') no-repeat',
			width: this.options.magnifyImgWidth,
			height: this.options.magnifyImgHeight,
			top: 0,
			left: 0
		}).injectInside(document.body);
		this.magnifyWrapper.addEvent('mouseover', this.showMagnify.bindWithEvent(this.magnifyWrapper,{magnifyWrapper: this.magnifyWrapper,options: this.options}));
		this.magnifyWrapper.addEvent('mouseout', this.hideMagnify.bindWithEvent(this.magnifyWrapper,{magnifyWrapper: this.magnifyWrapper,options: this.options}));

		this.magnifyWrapper.fx = new Fx.Morph(this.magnifyWrapper, {duration:this.options.magnifyDuration, transition: this.options.magnifyTransition, wait:false});

		$each(document.links, function(el){
			if ( el.rel && el.rel.test(/^lightbox/i) && el.getElement('img') ) {
				el.addEvent('mouseover', this.showMagnify.bindWithEvent(el,{magnifyWrapper: this.magnifyWrapper,options: this.options}));
				el.addEvent('mouseout', this.hideMagnify.bindWithEvent(el,{magnifyWrapper: this.magnifyWrapper,options: this.options}));			
			}
		}, this);
	},
	showMagnify: function(event,args) {
		var image = this.getElement('img');	// Image Element. If present mouse is hovering over image, if not mouse is hovering over magnify icon
		if (image) {
			var dims = image.getCoordinates();
			args.magnifyWrapper.onclick = this.onclick;
			args.magnifyWrapper.setStyles({
				top: dims.top + dims.height - args.options.magnifyImgHeight - args.options.magnifyImgMarginB,
				left: dims.left + dims.width - args.options.magnifyImgWidth - args.options.magnifyImgMarginR
			});
		}
		args.magnifyWrapper.fx.start({opacity: [1]});
	},
	hideMagnify: function(event,args) {
		args.magnifyWrapper.fx.start({opacity: [0]});
	}
};


