/***************************************************************
*
*  PMK Textarea Widgets
*
*  Copyright notice
*
*  (c) 2006 Peter Klein  (peter@umloud.dk)
*  All rights reserved
*
*  Released under the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*
*  This copyright notice MUST APPEAR in all copies of this script
*
***************************************************************
*
* Description:
*
* - Textarea can be resized in both horizontal and vertical directions..
*   Min. & Max. sizes can be set for both horizontal and vertical.
*
* - Automatic linenumbering (IE and Mozilla)
*   Linenumber column can be toggled on off either by checkbox or
*   by pressing the Ctrl-L key combination.
*
* - Tabchars can be inserted in textareas.
*   Both regular Tab char and spaces can be used as tabchar.
*   Can either be inserted as single character, or indent blocks
*   of text.
*   De-indenting is also available by pressing Shift-Tab or Ctrl-Tab
*
* - Textarea remembers the tab/indent position when presing return
*
* - Ability to change fontsizes (IE and Mozilla) either by buttons
*   or by pressing the Ctrl+ or Ctrl- keycombination on the numeric
*   keyboard.
*
* - Jump to line function (IE and Mozilla) press Jumpbutton or
*   keycombination Ctrl-G to bring up jump prompt.
*
* - Search function (IE and Mozilla) Opera has same function as default.
*   Press Findbutton or keycombination Ctrl-F to bring up searchprompt.
*   Pressing the F3 key after the initial search will find the
*   next occurrences of searchstring.
*
* - Comments can be toggled on/off by pressing the Ctrl/ or Ctrl*
*   keycombination on the numeric keyboard.
*
***************************************************************/

function textarea_init() {
	var textareas = document.getElementsByTagName('textarea');
	for (z = 0; z < textareas.length; z++) {
//		if ((' '+textareas[z].className+' ').indexOf(' enable-tab ')>-1) {
		if ((' '+textareas[z].className+' ').indexOf(' enable-tab ')>-1 || (' '+textareas[z].className+' ').indexOf(' formField3 ')>-1) {
			if (textareas[z].parentNode.className!='pmktextarea-wrapper') {
		        new textArea(textareas[z],ta_init);
			}
		}
	}
}

if (typeof schedule != 'function') {
	function pmk_addEvent(obj, evType, fn, useCapture) {
		if (obj.addEventListener) {
			obj.addEventListener(evType, fn, useCapture);
			return true;
		} else if (obj.attachEvent) {
			var r = obj.attachEvent("on"+evType, fn);
			return r;
		} else {
			alert('Handler could not be attached');
		}
	}

	function pmk_removeEvent(obj, evType, fn, useCapture){
		if (obj.removeEventListener){
			obj.removeEventListener(evType, fn, useCapture);
			return true;
		} else if (obj.detachEvent){
			var r = obj.detachEvent('on' + evType, fn);
			return r;
		} else {
			alert('Handler could not be removed');
		}
	}
	pmk_addEvent(window, 'load', function() { textarea_init(); }, false);
} else schedule('textarea_init()');


// Misc functions

/**
 * Get absolute position of element
 */
function absolutePosition(el) {
	var sLeft = 0, sTop = 0;
	var isDiv = /^div$/i.test(el.tagName);
	if (isDiv && el.scrollLeft) sLeft = el.scrollLeft;
	if (isDiv && el.scrollTop) sTop = el.scrollTop;
	var r = { x: el.offsetLeft - sLeft, y: el.offsetTop - sTop };
	if (el.offsetParent) {
		var tmp = absolutePosition(el.offsetParent);
		r.x += tmp.x;
		r.y += tmp.y;
	}
	return r;
};
/**
 * Scroll to line, based on linenumber
 */
function scrollToLine(el,linenumber){
	el.focus();
	var content = new String(el.value);
	var splitstr = content.split('\n');
	if (splitstr.length>=linenumber) {
		var lineheight = el.scrollHeight / splitstr.length;
		el.scrollTop = (linenumber - 1) * lineheight;
		var caretposition = 0;
		for (var i = 0;i < linenumber-1; i++){
			caretposition +=  splitstr[i].length + 1;
		}
		if (document.all) {
			// IE
			var range = el.createTextRange();
			range.move('character', caretposition-i); // Compensating for hidden \r chars
			range.select();
		}
		else {
			// Moz
			el.selectionStart = caretposition;
			el.selectionEnd = caretposition;
		}
		el.focus();
	}
}
/**
 * Scroll to line, based on char pos
 */
function scrollToPos(el, pos) {
 	var content = new String(el.value);
	var splitstr = content.split('\n');
	var lines = splitstr.length;
	var lineheight = el.scrollHeight / lines;
	var cpos = 0;
	for (i = 0;i < lines; i++){
		cpos +=  splitstr[i].length + 1; // Add 1 for LF char
		if (cpos>pos) break;
	}
	el.scrollTop = (i - 1) * lineheight;
	// Not 100% really accurate, but there's no other way of knowing the width of the font
	var lpos = (parseInt(el.style.fontSize) - 2) * (pos - cpos + splitstr[i].length);// + 1;
	if (lpos<el.scrollWidth) el.scrollLeft = lpos;
	el.focus();
}
/**
 * Returns element dimensions
 */
function dimensions(el) {
	return { width: el.offsetWidth, height: el.offsetHeight };
}

/**
 * Removes an element from the page
 */
function removeNode(node) {
	if (typeof node == 'string') node = $(node);
	if (node && node.parentNode) return node.parentNode.removeChild(node);
	else return false;
}

/**
 * Create an element on the page
 * with optional classname and css styles
 */
function createNode(nodeType,nodeClassName,nodeStyle) {
	var el = document.createElement(nodeType);
	if (nodeClassName) el.className = nodeClassName;
	if (nodeStyle) {
 		if (el.style.cssText!='undefined') el.style.cssText = nodeStyle;
		else el.setAttribute('style',nodeStyle);	// Opera
	}
	return el;
}
/**
 * Prevents an event from propagating.
 */
function stopEvent(event) {
	if (event.preventDefault) {
		event.preventDefault();
		event.stopPropagation();
	}
	else {
		event.returnValue = false;
		event.cancelBubble = true;
	}
}
/**
 * Inserts text at position in element
 * (Non-IE function)
 */
function insertText(el,txt) {
	if(el.selectionStart != null) {
		var savedScrollTop = el.scrollTop;
		var begin = el.selectionStart;
		var end = el.selectionEnd;
		if(end > begin + 1) el.value = el.value.substr(0, begin) + txt + el.value.substr(end);
		else el.value = el.value.substr(0, begin) + txt + el.value.substr(begin);
		el.selectionStart  = begin + txt.length;
		el.selectionEnd	= begin + txt.length;
		el.scrollTop = savedScrollTop;
	}
	else(el.value+= txt);
	el.focus();
}


/**
 * Main function
 */
function textArea(element,conf) {

	var ta = this;
	this.element = element;
	this.parent = this.element.parentNode;
	this.dimensions = dimensions(element);

	// Set this.baseURL
	this.setBaseURL();

	// Default configuration values
	// Note: This is an Object, not an Array, so array functions does not apply here!
	//       But it can be accessed in a similar way as Arrays. i.e. alert( this.conf['backColor'] );
	//       Specially important is that numeric values should ALWAYS be run through the Numbers() function.
	//       Otherwise they might be treated as strings!
	this.conf = {
		buttonPath: this.baseURL+'res/',
		languageKey: 'default',
		tabChar: 'Tabchar',
		backColor: 'ButtonFace',
		borderColor: 'Gray',
		backColorLn: '#f0f0f0',
		borderColorLn: 'GrayText',
		textColorLn: 'Gray',
		defaultFontSize: 9,
		linenumColWidth: 47,
		wrapState: false,
		linenumState: true,
		typo3Colors: false,
		showButtons: true,
		showMinMaxButton: true,
		showWrapButton: true,
		showLinenumButton: true,
		showFindButton: true,
		showJumpButton: true,
		showFontButtons: true,
		lockH: false,
		lockW: false,
		defaultHeight: null,
		defaultWidth: null,
		minHeight: null,
		maxHeight: null,
		minWidth: null,
		maxWidth: null
	};
	// Merge the user conf with the default config.
	// Note: There's no checking if the user entered illegal values in the config object!
	this.conf = this.objMerge(this.conf,conf);
	//var out='';for (var i in this.conf) {out+=i+' = '+this.conf[i]+'\n';};alert(out);

	// Use real Tabchar or spaces when pressing Tabkey?
	this.tabChar = parseInt(this.conf['tabChar']);
	this.tabChar = (this.tabChar)?'        '.substring(0,this.tabChar):String.fromCharCode(9);
	
	// Load language from XML file
	this.lang = new loadLocalLang(this.conf['languageKey'], this.baseURL+'locallang.xml')

	// Override textarea size if defaultHeight or defaultWidth is set.
	if (Number(this.conf['defaultHeight'])>30)
		this.dimensions.height = Number(this.conf['defaultHeight']);
	if (Number(this.conf['defaultWidth'])>Number(this.conf['linenumColWidth'])+33)
		this.dimensions.width = Number(this.conf['defaultWidth']);
        
	this.lockH = Number(this.conf['lockH']);
	this.lockW = Number(this.conf['lockW']);
	
	// Check to see if IE/Opera is in quirks or standard compliant mode
	if (self.innerHeight && !window.opera) this.quirksMode = 0; // all except Explorer/Opera
	else if (document.compatMode && document.compatMode == 'CSS1Compat') this.quirksMode = 0;	// IE/Opera in standard compliant mode
	else this.quirksMode = 1; // IE/Opera in quirks mode

	this.catchtab = true;

	// Prepare wrapper
	if (Number(this.conf['typo3Colors']))
		this.wrapper = createNode('div','pmk-textarea bgColor4','border: 1px solid '+this.conf['borderColor']+';');
	else
		this.wrapper = createNode('div','pmk-textarea','background: '+this.conf['backColor']+';border: 1px solid '+this.conf['borderColor']+';');
	this.parent.insertBefore(this.wrapper, this.element);

	// Create topbar for buttons etc.
	if (Number(this.conf['typo3Colors']))
		this.topbar = createNode('div','topbar bgColor4','height: '+Number(this.conf['showButtons'])*26+'px;width: 100%;border-bottom: 1px solid '+this.conf['borderColor']+';overflow: hidden;');
	else
		this.topbar = createNode('div','topbar','background: '+this.conf['backColor']+';height: '+Number(this.conf['showButtons'])*26+'px;width: 100%;border-bottom: 1px solid '+this.conf['borderColor']+';overflow: hidden;');
	this.wrapper.appendChild(this.topbar);
	this.topbar.dimensions = dimensions(this.topbar);

	if (Number(this.conf['showButtons'])) {
		// Create buttonbar
		this.buttonbar = createNode('div','buttonbar','float:left;overflow: hidden;height: '+Number(this.conf['showButtons'])*25+'px;padding-left:'+Number(this.conf['linenumColWidth'])+'px;');
		this.topbar.appendChild(this.buttonbar);
		this.buttonbar.dimensions = dimensions(this.buttonbar);
	}

	if (Number(this.conf['showWrapButton']) && Number(this.conf['showButtons'])) {
		//  Create wrap toggle button
		this.wraptoggle = new this.createButton(this.conf['buttonPath']+'wrap_off.gif', this.conf['buttonPath']+'wrap_on.gif', this.lang.getLL('wrapOff','Wrap Off'), this.lang.getLL('wrapOn','Wrap On'));
		this.buttonbar.appendChild(this.wraptoggle);
		pmk_addEvent(this.wraptoggle, 'click', function(e) { ta.toggleWrap(e); }, false)
	}

	// Unfortunatly Opera doesn't support "scrollTop" on textareas or changing fontsize on textareas
	// so Opera users will just have to live without Linenumbers, Jump2Line, Find etc. :/
	if (!window.opera) {
		// Add Column for linenumbers
		//this.lineNumColWidth = 46; // Width of linenumber column in px.
		this.lineNumColWidth = Number(this.conf['linenumColWidth']); // Width of linenumber column in px.
		if (Number(this.conf['typo3Colors']))
			this.numwrapper = createNode('div','linenum-wrapper bgColor3','border: 0;border-right: 1px solid '+this.conf['borderColorLn']+';overflow: hidden;color: '+this.conf['textColorLn']+';text-align: right;float: left;');
		else
			this.numwrapper = createNode('div','linenum-wrapper','border: 0;border-right: 1px solid '+this.conf['borderColorLn']+';overflow: hidden;color: '+this.conf['textColorLn']+';background: '+this.conf['backColorLn']+';text-align: right;float: left;');
		this.numwrapper.style.width = this.lineNumColWidth + this.quirksMode + 'px';
		this.numwrapper.style.height = this.dimensions.height -16 +'px';
		this.wrapper.appendChild(this.numwrapper);

		// Create DL/DT list used for displaying linenumbers
		this.numList = createNode('dl','lines','margin:0;padding: 0 .15em 0 0;font: normal normal normal '+ Number(this.conf['defaultFontSize']) +'pt/normal "Courier New", Courier, monospace;');
		this.numList.lineCount = 0;	// Initial linecount
		this.numwrapper.appendChild(this.numList);
		// Disable selecting, as we don't want the user to accidently select anything from the linenum column
		//this.numList.unselectable = true; // IE - This has to be done on the child element
		this.numList.style.MozUserSelect = "none"; // Moz
		this.numList.style.KhtmlUserSelect = "none";  // Konqueror/Safari

		if (Number(this.conf['showLinenumButton']) && Number(this.conf['showButtons'])) {
			// Create linenumber column toggle button image
			this.linenumtoggle = new this.createButton(this.conf['buttonPath']+'lnum_on.gif', this.conf['buttonPath']+'lnum_off.gif', this.lang.getLL('lnumOn','Linenumbers On'), this.lang.getLL('lnumOff','Linenumbers Off'));
			this.buttonbar.appendChild(this.linenumtoggle);
			pmk_addEvent(this.linenumtoggle, 'click', function(e) { ta.toggleLineNum(e); }, false);
		}

		if (Number(this.conf['showFindButton']) && Number(this.conf['showButtons'])) {
			// Create search button image
			this.findbutton = new this.createButton(this.conf['buttonPath']+'find.gif', '', this.lang.getLL('find','Find'), '');
			this.buttonbar.appendChild(this.findbutton);
			pmk_addEvent(this.findbutton, 'click', function(e) { var ss = prompt(ta.lang.getLL('findPrompt','Enter string to find'),ta.searchString);if (ss!=null) {ta.searchString = ss;ta.find(e);} else ta.element.focus(); }, false);
		}

		if (Number(this.conf['showJumpButton']) && Number(this.conf['showButtons'])) {
			// Create jump button image
			this.jumpbutton = new this.createButton(this.conf['buttonPath']+'jump.gif', '', this.lang.getLL('jump','Jump to line'), '');
			this.buttonbar.appendChild(this.jumpbutton);
			pmk_addEvent(this.jumpbutton, 'click', function(e) { ta.jumpToLine(); }, false);
		}
	}
	else {
		// Opera
		this.lineNumColWidth = 0;
	}

	if (Number(this.conf['showFontButtons']) && Number(this.conf['showButtons'])) {
		// Create Increase fontsize button image
		this.fsupbutton = new this.createButton(this.conf['buttonPath']+'fsize_up.gif', '', this.lang.getLL('fontSizeInc','Increase fontsize'), '');
		this.buttonbar.appendChild(this.fsupbutton);
		pmk_addEvent(this.fsupbutton, 'click', function(e) { ta.changeFontSize(1); }, false);

		// Create Decrease fontsize button image
		this.fsdownbutton = new this.createButton(this.conf['buttonPath']+'fsize_down.gif', '', this.lang.getLL('fontSizeDec','Decrease fontsize'), '');
		this.buttonbar.appendChild(this.fsdownbutton);
		pmk_addEvent(this.fsdownbutton, 'click', function(e) { ta.changeFontSize(-1); }, false);
	}

	if (Number(this.conf['showMinMaxButton']) && Number(this.conf['showButtons'])) {
		// Create min/max toggle button image
		this.minmaxtoggle = new this.createButton(this.conf['buttonPath']+'minimize.gif', this.conf['buttonPath']+'maximize.gif', this.lang.getLL('minimize','Minimize'), this.lang.getLL('maximize','Maximize'));
		// Override standard button CSS settings
		this.minmaxtoggle.style.styleFloat = this.minmaxtoggle.style.cssFloat = 'right';
		//this.minmaxtoggle.style.margin = '4px 4px';
		this.topbar.appendChild(this.minmaxtoggle);
		pmk_addEvent(this.minmaxtoggle, 'click', function(e) { ta.minMaximize(e); }, false);
	}

	// State of toggle buttons. Must be set even if the buttons are disabled!
	this.wraptoggleState = !Number(this.conf['wrapState']);
	this.linenumtoggleState = (window.opera) ? false : Number(this.conf['linenumState']) ? true : false;
	this.minmaxtoggleState = true;

	// Add Tabkey catching
	if (this.catchtab) {
		pmk_addEvent(this.element, 'keydown', function(e) { ta.catchTab(e); }, false);
		pmk_addEvent(this.element, 'blur', function(e) { ta.blurCallback(e); }, false);
	}

	// Add Column for textarea
	this.tawrapper = createNode('div','pmktextarea-wrapper','float: left;background: '+this.conf['backColor']+';');
	this.tawrapper.style.width = this.dimensions.width - this.lineNumColWidth  - 3 +'px';
	this.tawrapper.style.height = this.dimensions.height +'px';
	this.wrapper.appendChild(this.tawrapper);

	// Add resizebar
	if (Number(this.conf['typo3Colors']))
		this.resizebar = createNode('div','bar bgColor4','clear:left;float: left;height: 13px;width:100%;border: none;');
	else
		this.resizebar = createNode('div','bar','clear:left;float: left;height: 13px;width:100%;background: '+this.conf['backColor']+';border: none;');
	this.wrapper.appendChild(this.resizebar);

	// Add grip image
	this.grip = createNode('img','grip','float: right;cursor: nw-resize;width:11px;height:13px;border: none;');
	this.grip.src = this.baseURL+'res/statusbar_resize.gif';
	this.grip.title = this.grip.alt = this.lang.getLL('grip','Click & Drag to resize text box');
	this.resizebar.appendChild(this.grip);
	this.grip.dimensions = dimensions(this.grip);
	pmk_addEvent(this.grip, 'mousedown', function(e) { ta.beginDrag(e); }, false);

	// Measure resizebar (Must be done AFTER grip image has been inserted, otherwise IE reports wrong height value!)
	this.resizebar.dimensions = dimensions(this.resizebar);

	// Add Focus/Blur event to textarea for testing purpose..
	pmk_addEvent(this.element,'focus',function(e) { element.style.color='#000000'; }, false);
	pmk_addEvent(this.element,'blur',function(e) { element.style.color='#555555'; }, false);

	if (!window.opera) {
		if (document.all) {
			// The following adds syncronization between the textarea and the linenumber list
			this.element.attachEvent("onscroll", this.getOnScrollFunction(this.numwrapper,ta));
			// IE Has a couple extra events: oncut & onpaste, so we can also use those to trigger update of linenumbers
			this.element.attachEvent("oncopy", this.getOnCutPasteFunction(this.numwrapper,ta));
			this.element.attachEvent("oncut", this.getOnCutPasteFunction(this.numwrapper,ta));
			this.element.attachEvent("onpaste", this.getOnCutPasteFunction(this.numwrapper,ta));

			// If user has pressed either Return, Backspace or Delete, we update the linenumbers
			pmk_addEvent(this.element,'keydown', function (e) { if (event.keyCode==13||event.keyCode==8||event.keyCode==46) {window.setTimeout(function() { ta.updateNum(); }, 1); }; },false);
			//pmk_addEvent(this.element,'cut', function (e) { window.setTimeout(function() { ta.updateNum();numwrapper.scrollTop = element.scrollTop; }, 1); },false);
			//pmk_addEvent(this.element,'paste', function (e) { window.setTimeout(function() { ta.updateNum();numwrapper.scrollTop = element.scrollTop; }, 1); },false);
		}
		else if (this.element.addEventListener) {
			// Onscroll does not fire an event correctly in Mozilla so we use a MutationEvents instead
			pmk_addEvent(this.element,'DOMAttrModified', function (e) {ta.mutationEvent(e)}, false);
			// Update 13-Jan-08
			// Since now the mutation event no longer fires correctly for Mozilla, 
			// we have to 'invent' a new method to get the synchronizing to work in Mozilla.
			// Unfortunatly it doesn't work as smooth as with the mutation event. :(
			pmk_addEvent(this.element,'mousemove', function (e) { ta.mozScroll(e)}, false);
			pmk_addEvent(this.element,'mousedown', function (e) { ta.mozScroll(e)}, false);
			pmk_addEvent(this.element,'mouseup', function (e) { ta.mozScroll(e)}, false);
			pmk_addEvent(this.element,'mousewheel', function (e) { ta.mozScroll(e)}, false);
			pmk_addEvent(this.element,'keyup', function (e) { ta.mozScroll(e)}, false);
			pmk_addEvent(this.element,'resize', function (e) { ta.mozScroll(e)}, false);
			// We also add the onscroll event, just in case Mozilla decides to fix the bug. ;)
			pmk_addEvent(this.element,'scroll', function (e) { ta.mozScroll(e)}, false);

			// Since Mozilla doesn't have oncut/onpaste events, we will have to resort to the "input" event,
			// which is triggered whenever something is entered in the textarea. (So we don't need a keydown handler.)
			pmk_addEvent(this.element,'textInput', function (e) {ta.updateNum();}, false);
			pmk_addEvent(this.element,'overflow', function (e) {ta.updateNum();}, false);
		}
	}
	// Set wrapper and textarea dimensions
	this.wrapper.style.height = this.dimensions.height + this.topbar.dimensions.height + this.resizebar.dimensions.height + 'px';
	this.wrapper.style.width = this.dimensions.width - ((!this.quirksMode)*2)+ 'px';

	// Set additional Textarea styles
	this.element.dimensions = this.dimensions;
	this.element.style.cssText ='font: normal normal normal '+ Number(this.conf['defaultFontSize']) +'pt/normal "Courier New", Courier, monospace;margin: 0px;border: 0px;overflow: scroll;padding:0px;color:#555555;';
	this.element.style.width = '100%';
	this.element.style.height = this.dimensions.height +'px';
	this.element.wrap = 'off';
	this.element.setAttribute('wrap','off'); // Moz

	// Wrap textarea
	removeNode(this.element);
	this.tawrapper.appendChild(this.element);

	//this.element.style.height = '100%'; // Doesn't work in IE standard compliant mode

	// Measure difference between desired and actual textarea dimensions to account for padding/borders
	this.widthOffset = dimensions(this.wrapper).width - this.dimensions.width;
	this.heightOffset = dimensions(this.wrapper).height - this.dimensions.height - this.topbar.dimensions.height - this.resizebar.dimensions.height;

	// Min and Max sizes for resizing.
	this.minh = parseInt(Number(this.conf['minHeight']));this.minh = (this.minh)?this.minh:(window.opera?32:30)+this.topbar.dimensions.height;
	this.maxh = parseInt(Number(this.conf['maxHeight']));this.maxh = this.maxh?this.maxh:99999;
	this.minw = parseInt(Number(this.conf['minWidth']));this.minw = this.minw?this.minw:33+this.lineNumColWidth;
	this.maxw = parseInt(Number(this.conf['maxWidth']));this.maxw = this.maxw?this.maxw:99999;

	// Default searchstring
	this.searchString = '';

	// Make the divs line up in various browsers
	if (window.opera) {
		// Opera
		//this.element.style.width = this.dimensions.width -2 +'px';
		//this.tawrapper.style.width = this.dimensions.width - 2 +'px';
		this.element.style.width = this.dimensions.width -1 +'px';
		this.tawrapper.style.width = this.dimensions.width - 1 +'px';
	}
	else {
		if (document.all && !window.opera) {
			// IE
			this.grip.style.marginRight = -(this.quirksMode*3) + 'px';
			this.heightOffset-=2;
			this.element.style.margin = '-1px 0px';
		}
		else {
			// Mozilla
			this.element.style.MozBoxSizing = 'border-box';
		}

		// turn linenum/wrap off if config is set to false
		if (this.wraptoggleState) {
			this.wraptoggleState = !this.wraptoggleState;
			this.toggleWrap();
		}
		if (!this.linenumtoggleState) {
			this.linenumtoggleState = !this.linenumtoggleState;
			this.toggleLineNum();
		}
		// Init Linenumbers
		ta.updateNum();
		//scrollToPos(this.element,0);
		//this.element.blur();
	}
}

textArea.prototype.minMaximize = function(event) {
	this.toggleButton(this.minmaxtoggle);
	if (this.minmaxtoggleState = !this.minmaxtoggleState) {
		// Maximize Textarea
		if (!this.savedHeight) return;
		var height = this.savedHeight;
	}
	else {
		// Minimize Textarea
		this.savedHeight = dimensions(this.wrapper).height;
		var height = this.minh;
	}
	this.wrapper.style.height = height + 'px';
	this.tawrapper.style.height = height - this.topbar.dimensions.height - this.resizebar.dimensions.height + 'px';
	this.element.style.height = height - this.topbar.dimensions.height - this.resizebar.dimensions.height + 'px';
	if (!window.opera) this.numwrapper.style.height = height - this.topbar.dimensions.height - this.resizebar.dimensions.height - 16 + 'px';
	this.element.focus();
}

/**
 * Synchronize scrolling of textarea and linenumbers
 * (IE function)
 */
textArea.prototype.getOnScrollFunction = function(oElement,that) {
   return function () {
		 //that.updateNum();
         oElement.scrollTop = event.srcElement.scrollTop;
   };
}
/**
 * Synchronize scrolling of textarea and linenumbers
 * and update linenumbers after cut/paste
 * (IE function)
 */
 textArea.prototype.getOnCutPasteFunction = function(oElement,that) {
   return function () {
   		// NOT Working!!
		 window.setTimeout(function() { that.updateNum();that.getOnScrollFunction(oElement,that); },1);
   };
}
/**
 * Synchronize scrolling of textarea and linenumbers
 * (Mozilla function)
 */
textArea.prototype.mutationEvent = function (event) {
	event = event || window.event;
	if (event['attrName']=='curpos' && !(event.relatedNode.ownerElement.orient=='horizontal'))
		this.numwrapper.scrollTop = event['newValue'];
	else if (event['attrName']=='maxpos')
		this.updateNum();
}
/**
 * Synchronize scrolling of textarea and linenumbers
 * (Alternate Mozilla function)
 */
textArea.prototype.mozScroll = function (event) {
	event = event || window.event;
		this.numwrapper.scrollTop = event.target.scrollTop;
		this.updateNum();
}
textArea.prototype.createButton= function(img, altimg, title, alttitle){
		var button = createNode('img','button');
		button.src = img;
		button.title = title;
		button.alt = title;
		button.altTitle = alttitle;
		button.altSrc = altimg;
		with (button.style) {
			if (button.style.styleFloat) styleFloat = 'left';
			else {
				// Moz
				cssFloat = 'left';
				MozUserSelect = KhtmlUserSelect = "none";
			}
			cursor = document.all?'hand':'pointer';
			background = 'red';
			width = '16px';
			height = '16px';
			border = '1px outset';
			margin = '4px 2px';
		}
		pmk_addEvent(button, 'mouseover', function(e) { button.style.border='2px outset';button.style.margin='3px 1px'; }, false);
		pmk_addEvent(button, 'mouseout', function(e) { button.style.border='1px outset';button.style.margin='4px 2px'; }, false);
		pmk_addEvent(button, 'mousedown', function(e) { button.style.border='2px inset';button.style.margin='3px 1px'; }, false);
		pmk_addEvent(button, 'mouseup', function(e) { button.style.border='2px outset';button.style.margin='3px 1px'; }, false);
		return button;
}

textArea.prototype.toggleButton = function (el) {
	var tmp = el.src;
	el.src = el.altSrc;
	el.altSrc = tmp;
	tmp = el.title;
	el.title = el.alt = el.altTitle;
	el.altTitle = tmp;
	//if (typeof(tooltip != 'undefined')) tooltip.init(['img']);// dom-tooltips.js update fix
}

textArea.prototype.toggleLineNum = function (event) {
	event = event || window.event;
	this.linenumtoggleState = !this.linenumtoggleState;	// Update button state
	if (Number(this.conf['showLinenumButton']) && Number(this.conf['showButtons']))
		this.toggleButton(this.linenumtoggle);

	if (this.linenumtoggleState) {
		// Make sure Wrap is off
		if (this.wraptoggleState) {
			this.wraptoggleState = true;
			this.toggleWrap(event)
		}
		// Turn Linenumbers on
		this.tawrapper.style.width = dimensions(this.wrapper).width - this.lineNumColWidth - 3 +'px';
		this.element.style.width = dimensions(this.wrapper).width - this.lineNumColWidth - 3 +'px';
		this.numwrapper.style.display ='';
		this.numwrapper.scrollTop = this.element.scrollTop;

		this.updateNum();
	}
	else {
		// Turn Linenumbers off
		this.tawrapper.style.width = dimensions(this.wrapper).width - 2 +'px';
		this.element.style.width = dimensions(this.wrapper).width - 2 +'px';
		this.numwrapper.style.display ='none';
	}
	this.element.focus();
	return;
}
textArea.prototype.toggleWrap = function (event) {
	event = event || window.event;
	this.wraptoggleState = !this.wraptoggleState;	// Update button state
	if (Number(this.conf['showWrapButton']) && Number(this.conf['showButtons']))
		this.toggleButton(this.wraptoggle);

	if (this.wraptoggleState) {
		// Make sure Linenum is off
		if (this.linenumtoggle) {
			this.lnState = this.linenumtoggleState;	// Save Linenum state
			if (this.lnState) this.toggleLineNum(event);
		}
		// Turn Wrap off
		this.element.setAttribute('wrap','soft');
	}
	else {
		if (this.linenumtoggle) {
			if (this.lnState) this.toggleLineNum(event);
		}
		// Turn Wrap on
		this.element.setAttribute('wrap','off');
	}
	// Wrap textarea
	if (!document.all || window.opera) {
		// Mozilla & Opera needs the element to be redrawn in order to switch wrap method.
		// Drawback to this is that posision in textarea is lost, so we have to save the position and restore it later.
		var selectionStart = this.element.selectionStart;
		var selectionEnd = this.element.selectionEnd;
		removeNode(this.element);
		this.tawrapper.appendChild(this.element);
		this.element.selectionStart = selectionStart;
		this.element.selectionEnd = selectionEnd;
		if (!window.opera) scrollToPos(this.element,selectionStart); // Opera can't scroll a textarea :(
	}
	this.element.focus();
	return;
}

textArea.prototype.updateNum = function (event) {
	event = event || window.event;
	var val = this.element.value;
	var lines = val.split(/\n/).length;
	// IE fix
	if (document.all && val.charCodeAt(val.length-1)==10) lines++;

	// current linenumber is different from stored linenumber
	while (this.numList.lineCount!=lines) {
		// Add or remove lines until stored linenumber matches current linenumber
		if (this.numList.lineCount>lines) {
			// Remove one line
			this.numList.removeChild(this.numList.lastChild);
			this.numList.lineCount--;
		}
		else if (this.numList.lineCount<lines) {
			// Create linenumber object
			var ln = createNode('dt');
			// Disable selecting, as we don't want the user to accidently select anything from the linenum column
			ln.unselectable = true; // IE. In other browsers disabling is done by CSS on parent element
			ln.appendChild(document.createTextNode(this.numList.lineCount+1));
			// Add one line
			this.numList.appendChild(ln);
			this.numList.lineCount++;
		}
	}
	return false;
}

textArea.prototype.getSelection = function () {
	var selectionText = "";
	if (document.selection && !window.opera) {
		// IE
		var range = document.selection.createRange();
		selectionText = range.text;
	}
	else if (this.element.setSelectionRange){
		if (this.element.selectionEnd != this.element.selectionStart) {
			this.begin = this.element.selectionStart;
			var selectionText = this.element.value.substring(this.element.selectionStart, this.element.selectionEnd);
		}
	}
	return selectionText;
}

textArea.prototype.setSelection = function (selectionText) {
	if (document.selection && !window.opera) {
		// IE
		var range = document.selection.createRange();
		range.text = selectionText;
		// Select the textblock again, compensating for the invisible \r chars in IE
		range.moveStart('character',selectionText.split("\r").length-selectionText.length-1);
		range.select();
	}
	else if (this.element.setSelectionRange){
		insertText(this.element, selectionText);
		this.element.selectionStart = this.begin;
		// Select the textblock again, compensating for the invisible \r chars in Opera
		if (window.opera) this.element.selectionEnd = selectionText.split("\r").length-selectionText.length-1;
		this.element.setSelectionRange(this.element.selectionStart, this.element.selectionEnd);
	}
}

textArea.prototype.catchTab = function (event) {
	event = event || window.event;
	var keycode = event.keyCode ? event.keyCode : event.which;
//alert(keycode)
	switch(keycode) {
		case 106: // Ctrl + numeric * key
			if (event.ctrlKey) {
				if (selectionText = this.getSelection()) {
					// Block Indent
					var jn = new Date().getTime(); // Unique replacement value for linefeeds
					var selectionTextClean = selectionText.replace(/\n/g, jn) // Convert linefeeds to replacement value
					selectionTextClean = selectionTextClean.replace(/(\/\*)(.*)(\*\/)/g,"$2"); // Remove comments
					selectionTextClean = selectionTextClean.replace(new RegExp(jn,"g"), "\n") // Convert replacement value back to linefeeds
					selectionText = (selectionTextClean == selectionText) ? '/*' + selectionText + '*/' : selectionTextClean;
					this.setSelection(selectionText);
				}
				stopEvent(event);
			}
			break;

		// Ctrl + numeric / key
		case 111:
			if(event.ctrlKey) {
				if (selectionText = this.getSelection()) {
					// Block Indent
					var jn = new Date().getTime(); // Unique replacement value for linefeeds
					var selectionTextClean = ("\n"+selectionText).replace(/\n/g, jn).replace(new RegExp("("+jn+")([\t ]*)(\/\/|#)","g"), "$1$2").replace(new RegExp(jn,"g"), "\n").replace(/^\n/g,'');
					selectionText = (selectionTextClean == selectionText) ? '//'+selectionText.split("\n").join("\n//") : selectionTextClean;
					this.setSelection(selectionText);
				}
				stopEvent(event);
			}
			break;
		// Ctrl-g key
		case 71:
			if(event.ctrlKey && !window.opera) {
				this.jumpToLine();
			}
			break;
		// Ctrl-f key
		case 70:
			if(event.ctrlKey && !window.opera) {
				var ss = prompt(this.lang.getLL('findPrompt','Enter string to find'),this.searchString);
				if (ss!=null) {
					this.searchString = ss;
					this.find(event);
				}
				else {
					this.element.focus();
				}
				if (document.all) event.keyCode=''; // IE also needs the keyCode reset to prevent system events
				stopEvent(event);
			}
		break;
		// F3 key
		case 114:
			if (this.searchString!='' && !window.opera) {
				this.find(event);
			}
			if (document.all) event.keyCode=''; // IE also needs the keyCode reset to prevent system events
			stopEvent(event);
		break;
		// Tabkey
		case 9:
			tabChar = String.fromCharCode(9);
			this.element.preventBlur = true;
			if (selectionText = this.getSelection()) {
				// Block indent
				if(event.shiftKey || event.ctrlKey) selectionText = selectionText.replace(/\n[ |\t]/g, "\n").replace(/^[ |\t]/, "");
				else selectionText = this.tabChar + selectionText.split("\n").join("\n" + this.tabChar);
				this.setSelection(selectionText);
			}
			else {
				// Single tab insert
				if (document.selection && !window.opera) {
					// IE
					var range = document.selection.createRange();
					if(event.shiftKey || event.ctrlKey) {
						range.moveStart('character',-1);
						range.select();
						selectionText = this.getSelection();
						if (selectionText==tabChar || selectionText==' ') this.setSelection('');
						else {
							range.collapse();
							range.setEndPoint("EndToStart", selectionText);
						}
					}
					else range.text += this.tabChar;
				}
				else if (this.element.setSelectionRange) {
					// Moz
					if(event.shiftKey || event.ctrlKey) {
						var val = this.element.value;
						var selectionStart = this.element.selectionStart;
						selectionText = val.substring(selectionStart-1,selectionStart)
						if (selectionText==tabChar || selectionText==' ') {
							this.element.value = val.substr(0, selectionStart-1) + val.substr(selectionStart);
							this.element.selectionStart = this.element.selectionEnd = selectionStart-1;
						}
					}
					else insertText(this.element, this.tabChar);
				}
			}
			stopEvent(event);
			break;

		// Enter
		case 13:
			if (document.selection && !window.opera) {
				// IE
				var range = document.selection.createRange();
				var range_obj = this.element.createTextRange();
				var len = this.element.value.length;
				range_obj.moveToBookmark(range.getBookmark());
				range_obj.moveEnd('character',len);
				currPos = len - range_obj.text.length;
				for(i=currPos-1;i>=0;i--) {
					if(this.element.value.substring(i, i + 1) == '\n') break;
				}
				lastLine = this.element.value.substring(i + 1, currPos);
				whiteSpace = "";
				for(i=0;i<lastLine.length;i++) {
					if(lastLine.substring(i, i + 1) == '\t') whiteSpace += "\t";
					else if(lastLine.substring(i, i + 1) == ' ') whiteSpace += " ";
					else break;
				}
				window.setTimeout(function() { range.text += "\r\n"+whiteSpace; }, 1);
				break;
			}
			else {
				// Moz + Opera
				currPos = this.element.selectionStart;
				lastLine = "";
				startPos = currPos - (window.opera?2:1)
				endPos = currPos - (window.opera?2:0)
				for(i=startPos;i>=0;i--) {
					if(this.element.value.substring(i, i + 1) == '\n') break;
				}
				lastLine = this.element.value.substring(i + 1, endPos);
				whiteSpace = "";
				for(i=0;i<lastLine.length;i++) {
					if(lastLine.substring(i, i + 1) == '\t') whiteSpace += "\t";
					else if(lastLine.substring(i, i + 1) == ' ') whiteSpace += " ";
					else break;
				}
				currentArea = this.element;
				window.setTimeout(function() { insertText(currentArea, whiteSpace); }, 1);
				break;
			}
		// Ctrl-l key
		case 76:
			if(event.ctrlKey && !window.opera) {
				// Toggle linenumber column on/off
				this.toggleLineNum(event);
				this.element.focus();
				if (document.all) event.keyCode=''; // IE also needs the keyCode reset to prevent system events
				stopEvent(event);
			}
			break;
		// Numeric keyboard Ctrl+ key
		case 107:
			if(event.ctrlKey && !window.opera) {
				// Increase font size
				this.changeFontSize(1);
				stopEvent(event);
			}
			break;
		// Numeric keyboard Ctrl- key
		case 109:
			if(event.ctrlKey && !window.opera) {
				// Decrease font size
				this.changeFontSize(-1);
				stopEvent(event);
			}
			break;
	}

	return true;
}

textArea.prototype.changeFontSize = function (val) {
	var fs = parseInt(this.element.style.fontSize)
	if ((val==-1 && fs>8) || (val==1 && fs<12)) {
		if (window.opera) {
			// Opera needs the element to be redrawn in order to change font size.
			// Drawback to this is that posision in textarea is lost.
			removeNode(this.element);
			this.element.style.fontSize = fs+val +'pt';
			this.tawrapper.appendChild(this.element);
		}
		else {
			this.element.style.fontSize = this.numList.style.fontSize = fs+val +'pt';
		}
	}

	this.element.focus();
}

/**
 * Subfunction to handle the "JumpToLine" feature
 */
textArea.prototype.jumpToLine = function () {
	var ss = parseInt(prompt(this.lang.getLL('jumpPrompt','Enter linenumber to goto'),''));
	if (!isNaN(ss) && ss>0) scrollToLine(this.element,ss);
	this.element.focus();
	stopEvent(event);
}

/**
 * Subfunction to handle the "Search" feature
 */
textArea.prototype.find = function (event) {
	event = event || window.event;
	this.element.focus();
	if (this.element.createTextRange) {
		// Internet Explorer
		var rc = document.selection.createRange().duplicate();
		var r = document.selection.createRange();
		r.setEndPoint("StartToEnd", rc);
		if (r.findText(this.searchString) && r.parentElement() == this.element) r.select();
		else if (confirm(this.lang.getLL('noMatch','No match found for %s').replace('%s','"'+this.searchString+'"')+'\n' + this.lang.getLL('continueSearch','Continue search at beginning of document?'))) {
			scrollToLine(this.element,1);
			this.find(event);
		}
	}
	else if (this.element.setSelectionRange){
		// Mozilla
		var startPos = Math.max(this.element.selectionStart,this.element.selectionEnd);
		startPos = startPos * (startPos<this.element.value.length);
		pos = this.element.value.indexOf(this.searchString,startPos);
		if (pos!=-1) {
			this.element.setSelectionRange(pos, pos + this.searchString.length);
			scrollToPos(this.element, pos);
		}
		else if (confirm(this.lang.getLL('noMatch','No match found for %s').replace('%s','"'+this.searchString+'"') + '\n' + this.lang.getLL('continueSearch','Continue search at beginning of document?'))) {
			scrollToLine(this.element,1);
			this.find(event);
		}
	}
	else {
		// Other browser
		alert('Unsupported browser!');
	}
}

/**
 * Prevent element from blur'ing
 */
textArea.prototype.blurCallback = function (event) {
	event = event || window.event;
	if(!this.element.preventBlur) return;
	this.element.preventBlur = null;
	currentArea = this.element;
	window.setTimeout(function() { currentArea.focus(); }, 1);
	return false;
}

textArea.prototype.beginDrag = function (event) {
	event = event || window.event;
	// Capture mouse
	var cp = this;
	this.oldMoveHandler = document.onmousemove;
	document.onmousemove = function(e) { cp.handleDrag(e); };
	this.oldUpHandler = document.onmouseup;
	document.onmouseup = function(e) { cp.endDrag(e); };
	
	// lock cursor shape
	document.getElementsByTagName('body')[0].style.cursor = 'nw-resize';

	// Store drag offset from resizebar top
	var pos = absolutePosition(this.wrapper);
	this.dragOffsetH = event.clientY - pos.y - dimensions(this.wrapper).height;
	this.dragOffsetV = event.clientX - pos.x - dimensions(this.wrapper).width;

	// If textarea is minimized, set it's state to maximized.
	if (!this.minmaxtoggleState) {
		this.minmaxtoggleState = !this.minmaxtoggleState;
		this.toggleButton(this.minmaxtoggle);
	}

	// Process
	this.handleDrag(event);
}

/**
 * Subfunction to handle the "Resize" feature
 */
textArea.prototype.handleDrag = function (event) {
	event = event || window.event;
	// Get coordinates relative to wrapper
	var pos = absolutePosition(this.wrapper);
	var y = event.clientY - pos.y;
	var x = event.clientX - pos.x;

  // Set new height
	var height = Math.max(this.minh, y - this.dragOffsetH - this.heightOffset);
	height = Math.min(this.maxh,height) // Max height

	// Set new width
	var width = Math.max(this.minw, x - this.dragOffsetV - this.widthOffset);
	width = Math.min(this.maxw,width) // Max width

	if (!this.lockH) {
		this.wrapper.style.height = height + 'px';
		this.tawrapper.style.height = height - this.topbar.dimensions.height - this.resizebar.dimensions.height + 'px';
		this.element.style.height = height - this.topbar.dimensions.height - this.resizebar.dimensions.height + 'px';
		if (!window.opera) this.numwrapper.style.height = height - this.topbar.dimensions.height - this.resizebar.dimensions.height - 16 + 'px';
	}
	if (!this.lockW) {
		// Moz/IE Quirks & Standard Complinant mode
		this.wrapper.style.width = width -(!this.quirksMode*2) + 'px';
		this.tawrapper.style.width = width + (window.opera?1:0) + this.quirksMode - this.lineNumColWidth -4 + 'px';
		this.element.style.width = width + (window.opera?1:0) + this.quirksMode - (!this.quirksMode) - (this.lineNumColWidth*this.linenumtoggleState) + (!this.linenumtoggleState) + (!this.quirksMode*2)-4 + 'px';
	}
	// Avoid text selection
	stopEvent(event);
}

/**
 * Subfunction to end the "Resize" feature
 */
textArea.prototype.endDrag = function (event) {
	// Uncapture mouse
	document.onmousemove = this.oldMoveHandler;
	document.onmouseup = this.oldUpHandler;
	//pmk_removeEvent(document, 'mousemove', null, false)
	//pmk_removeEvent(document, 'mouseup', null, false)

	// restore default cursor shape
	document.getElementsByTagName('body')[0].style.cursor = '';
}

/**
 * Sets the var "this.baseURL" to location of this script.
 */
textArea.prototype.setBaseURL= function(){
	if (!this.baseURL) {
		this.baseURL='';
		var elements = document.getElementsByTagName('script');
		for (var i=0; i<elements.length; i++) {
			if (elements[i].src && (elements[i].src.indexOf("pmk_textarea.js") != -1)) {
				var src = elements[i].src;
				src = src.substring(0, src.lastIndexOf('/'));
				if (src) this.baseURL = src+'/';
				//this.file_name= elements[i].src.substr(elements[i].src.lastIndexOf("/")+1);
				break;
			}
		}
	}
}

/**
 * Merge properties of two object. Properties of obj2 overrides those of obj1
 */
textArea.prototype.objMerge = function(obj1,obj2) {
	var merged = new Object();
	for (var i in obj1) {
		if (isNaN(i)) merged[i] = obj1[i];
	}
	for (var i in obj2) {
		if (isNaN(i)) merged[i] = obj2[i];
	}
	return merged;
}

/**
 * JavaScript 'class' which loads TYPO3 language files in XML format (localang.xml)
 */

// Init function
function loadLocalLang(langKey,xmlFile) {
	if (langKey) {
		this.local_lang = this.loadXMLDoc(langKey, (xmlFile ? xmlFile : 'locallang.xml'));
	}
}
loadLocalLang.prototype.getLL = function (label,defVal) {
	return (this.local_lang[label]) ? this.local_lang[label] : (defVal) ? defVal : label;
}
loadLocalLang.prototype.findLangKey = function (el,key) {
	var found = false;
	for (var i=0;i<el.length;i++) {
		if(found = (el[i].getAttribute('index')==key)) break;
	}
	return found ? el[i] : false;
}
loadLocalLang.prototype.loadXMLDoc=function(langKey, xmlFile){
	var xmlDoc = false;
	// branch for native XMLHttpRequest object
	if(window.XMLHttpRequest) {
		try {
			xmlDoc = new XMLHttpRequest();
		} catch(e) {
			xmlDoc = false;
		}
	// branch for IE/Windows ActiveX version
	} else if(window.ActiveXObject) {
		try {
			xmlDoc = new ActiveXObject("Msxml2.XMLHTTP");
		} catch(e) {
			try {
				xmlDoc = new ActiveXObject("Microsoft.XMLHTTP");
			} catch(e) {
				xmlDoc = false;
			}
		}
	}
	if(xmlDoc) {
		xmlDoc.open("GET", xmlFile, false);
		xmlDoc.send("");
		if (xmlDoc.readyState == 4) {
			if (xmlDoc.status == 200 || xmlDoc.status == 304) {	// 304 in Opera?
				var lk = xmlDoc.responseXML.getElementsByTagName('languageKey');
				if (!(langNode=this.findLangKey(lk,langKey))) {
					if (!(langNode=this.findLangKey(lk,'default'))) {
						return '';
					}
				}
				var llang = new Array();
				for (var i=0;i<langNode.childNodes.length;i++) {
					if (langNode.childNodes[i].nodeType != 1) continue;
					var key = langNode.childNodes[i].getAttribute('index');
					var value = langNode.childNodes[i].firstChild.nodeValue;
					llang[key] = value;
				}
				return llang;
			} else {
				alert('Error #' + xmlDoc.status + ' while retrieving the XML data:\n' + xmlDoc.statusText);
			}
		}
	}
	return '';
}
