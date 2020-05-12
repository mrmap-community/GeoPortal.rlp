<?php
/***************************************************************/
/**
 * Contains class with layout/output function for TYPO3 Backend Scripts
 *
 * $Id: template.php,v 1.51.2.8 2006/03/22 01:11:04 typo3 Exp $
 * Revised for TYPO3 3.6 2/2003 by Kasper Skaarhoj
 * XHTML-trans compliant
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */



class ux_template extends template {


	/*****************************************
	 *
	 * EVALUATION FUNCTIONS
	 * Various centralized processing
	 *
	 *****************************************/
/*	
	function wrapClickMenuOnIcon($str,$table,$uid='',$listFr=1,$addParams='',$enDisItems='', $returnOnClick=FALSE)	{
		
		if (t3lib_div::int_from_ver(TYPO3_version) >= 4000000) {
			$Typo4=1;
			} 
	
		if(!$Typo4){
			$backPath = '&backPath='.rawurlencode($this->backPath).'|'.t3lib_div::shortMD5($this->backPath.'|'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']);
			$onClick = 'top.loadTopMenu(\''.$this->backPath.'alt_clickmenu.php?item='.rawurlencode($table.'|'.$uid.'|'.$listFr.'|'.$enDisItems).$backPath.$addParams.'\');'.$this->thisBlur().'return false;';
			return $returnOnClick ? $onClick : '<a class="c-recLink" href="#" onclick="'.htmlspecialchars($onClick).'"'.($GLOBALS['TYPO3_CONF_VARS']['BE']['useOnContextMenuHandler'] ? ' oncontextmenu="'.htmlspecialchars($onClick).'"' : '').'>'.$str.'</a>';
			}
		else	{	
			$backPath = rawurlencode($this->backPath).'|'.t3lib_div::shortMD5($this->backPath.'|'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']);
			$onClick = 'showClickmenu("'.$table.'","'.$uid.'","'.$listFr.'","'.$enDisItems.'","'.str_replace('&','&amp;',addcslashes($backPath,'"')).'","'.str_replace('&','&amp;',addcslashes($addParams,'"')).'");return false;';
			return $returnOnClick ? $onClick : '<a class="c-recLink Typo4" href="#" onclick="'.htmlspecialchars($onClick).'"'.($GLOBALS['TYPO3_CONF_VARS']['BE']['useOnContextMenuHandler'] ? ' oncontextmenu="'.htmlspecialchars($onClick).'"' : '').'>'.$str.'</a>';
			}

		}

*/

	/*****************************************
	 *
	 *	PAGE BUILDING FUNCTIONS.
	 *	Use this to build the HTML of your backend modules
	 *
	 *****************************************/

	/**
	 * Returns page start
	 * This includes the proper header with charset, title, meta tag and beginning body-tag.
	 *
	 * @param	string		HTML Page title for the header
	 * @return	string		Returns the whole header section of a HTML-document based on settings in internal variables (like styles, javascript code, charset, generator and docType)
	 * @see endPage()
	 */
	function startPage($title)	{
	
	$confProperties = t3lib_BEfunc::getModTSconfig($this->pageinfo['uid'],'mod.skin_grey_2');
	#print $confProperties['properties']['styleSheetFile_post'];
	#print $confProperties['properties']['ModuleIconSize'];
	$extraCSS = '';		
	if (!empty($confProperties['properties']['styleSheetFile_post']))
		$extraCSS .= '<link rel="stylesheet" type="text/css" href="'.$confProperties['properties']['styleSheetFile_post'].'">';
	if(!empty($confProperties['properties']['ModuleIconSize']))
	{
	$extraCSS .= '
	<style type="text/css">		
	#typo3-alt-menu-php .c-mainitem img,
	#typo3-alt-intro-php .c-subitem-row img,
	#typo3-alt-intro-php .c-mainitem img,
	TABLE#typo3-topMenu TR TD.c-menu A IMG, 
	TABLE#typo3-vmenu TR.c-subitem-row TD IMG,
	TABLE#typo3-vmenu TR.c-subitem-row-HL TD IMG {
		height:'.$confProperties['properties']['ModuleIconSize'].'px !important; 
		width:'.$confProperties['properties']['ModuleIconSize'].'px !importantpx; 
	}
	.acm_spacer { height:'.($confProperties['properties']['ModuleIconSize']-2).'px !important; } 
	</style>';
	}
			
			// Get META tag containing the currently selected charset for backend output. The function sets $this->charSet.
		$charSet = $this->initCharset();
		$generator = $this->generator();

			// For debugging: If this outputs "QuirksMode"/"BackCompat" (IE) the browser runs in quirks-mode. Otherwise the value is "CSS1Compat"
#		$this->JScodeArray[]='alert(document.compatMode);';

			// Send HTTP header for selected charset. Added by Robert Lemke 23.10.2003
		header ('Content-Type:text/html;charset='.$this->charset);

		switch($this->docType)	{
			case 'xhtml_strict':
				$headerStart= '<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?xml version="1.0" encoding="'.$this->charset.'"?>
<?xml-stylesheet href="#internalStyle" type="text/css"?>
';
			break;
			case 'xhtml_trans':
				$headerStart= '<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?xml version="1.0" encoding="'.$this->charset.'"?>
<?xml-stylesheet href="#internalStyle" type="text/css"?>
';
			break;
			case 'xhtml_frames':
				$headerStart= '<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<?xml version="1.0" encoding="'.$this->charset.'"?>
';
			break;
			default:
				$headerStart='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">';
			break;
		}

			// Construct page header.
		$str = $headerStart.'
<html>
<head>
	<!-- TYPO3 Script ID: '.htmlspecialchars($this->scriptID).' -->
	'.$charSet.'
	'.$generator.'
	<title>'.htmlspecialchars($title).'</title>
	'.$this->docStyle().'
	'.$this->JScode.'
	'.$this->wrapScriptTags(implode("\n", $this->JScodeArray)).'
	<!--###POSTJSMARKER###-->
	'.$extraCSS.'
</head>
';
		$this->JScode='';
		$this->JScodeArray=array();

		if ($this->docType=='xhtml_frames')	{
			return $str;
		} else
$str.=$this->docBodyTagBegin().
($this->divClass?'

<!-- Wrapping DIV-section for whole page BEGIN -->
<div class="'.$this->divClass.'">
':'').trim($this->form);
		return $str;
	}
	
	/**
	 * Returns the header-bar in the top of most backend modules
	 * Closes section if open.
	 *
	 * @param	string		The text string for the header
	 * @return	string		HTML content
	 */
	function header($text)	{
		$str='

	<!-- MAIN Header in page top -->
	<div class="mainHeader"><h2 class="mainHeader">'.htmlspecialchars($text).'</h2></div>
';
		return $this->sectionEnd().$str;
	}

	/**
	 * Inserts a divider image
	 * Ends a section (if open) before inserting the image
	 *
	 * @param	integer		The margin-top/-bottom of the <hr> ruler.
	 * @return	string		HTML content
	 */
	function divider($dist)	{
		$dist = intval($dist);
		$str='

	<!-- DIVIDER -->
	<hr class="dividerHr" style="margin-top: '.$dist.'px; margin-bottom: '.$dist.'px;" />
';
		return $this->sectionEnd().$str;
	}

	/**
	 * Returns a blank <div>-section with a height
	 *
	 * @param	integer		Padding-top for the div-section (should be margin-top but konqueror (3.1) doesn't like it :-(
	 * @return	string		HTML content
	 */
	function spacer($dist)	{
		if ($dist>0)	{
			return '

	<!-- Spacer element -->
	<div class="spacerDiv spacerDiv'.intval($dist).'" style="padding-top: '.intval($dist).'px;"></div>
';
		}
	}

	/**
	 * Make a section header.
	 * Begins a section if not already open.
	 *
	 * @param	string		The label between the <h3> or <h4> tags. (Allows HTML)
	 * @param	boolean		If set, <h3> is used, otherwise <h4>
	 * @param	string		Additional attributes to h-tag, eg. ' class=""'
	 * @return	string		HTML content
	 */
	function sectionHeader($label,$sH=FALSE,$addAttrib='')	{
		$tag = ($sH?'h3':'h4');
		$str='

	<!-- Section header -->
	<div class="sectionHeader"><'.$tag.$addAttrib.'>'.$label.'</'.$tag.'></div>
';
		return $this->sectionBegin().$str;
	}



	/*****************************************
	 *
	 * OTHER ELEMENTS
	 * Tables, buttons, formatting dimmed/red strings
	 *
	 ******************************************/


	/**
	 * Returns an <input> button with the $onClick action and $label
	 *
	 * @param	string		The value of the onclick attribute of the input tag (submit type)
	 * @param	string		The label for the button (which will be htmlspecialchar'ed)
	 * @return	string		A <input> tag of the type "submit"
	 */
	function t3Button($onClick,$label)	{
		$button = '<input class="submit t3button" type="submit" onclick="'.htmlspecialchars($onClick).'; return false;" value="'.htmlspecialchars($label).'" />';
		return $button;
	}

	/**
	 * Constructs a table with content from the $arr1, $arr2 and $arr3.
	 * Used in eg. ext/belog/mod/index.php - refer to that for examples
	 *
	 * @param	array		Menu elements on first level
	 * @param	array		Secondary items
	 * @param	array		Third-level items
	 * @return	string		HTML content, <table>...</table>
	 */
	function menuTable($arr1,$arr2=array(), $arr3=array())	{
		$rows = max(array(count($arr1),count($arr2),count($arr3)));

		$menu='
		<table border="0" cellpadding="0" cellspacing="0" id="typo3-tablemenu">';
		for($a=0;$a<$rows;$a++)	{
			$menu.='<tr>';
			$cls=array();
			$valign='middle';
			$cls[]='<td class="firstCell" valign="'.$valign.'">'.$arr1[$a][0].'</td><td>'.$arr1[$a][1].'</td>';
			if (count($arr2))	{
				$cls[]='<td valign="'.$valign.'">'.$arr2[$a][0].'</td><td>'.$arr2[$a][1].'</td>';
				if (count($arr3))	{
					$cls[]='<td valign="'.$valign.'">'.$arr3[$a][0].'</td><td>'.$arr3[$a][1].'</td>';
				}
			}
			$menu.=implode($cls,'<td class="lastCell"><span class="lastCell">&nbsp;&nbsp;</span></td>');
			$menu.='</tr>';
		}
		$menu.='
		</table>
		';
		return $menu;
	}

	/**
	 * Returns a one-row/two-celled table with $content and $menu side by side.
	 * The table is a 100% width table and each cell is aligned left / right
	 *
	 * @param	string		Content cell content (left)
	 * @param	string		Menu cell content (right)
	 * @return	string		HTML output
	 */
	function funcMenu($content,$menu)	{
		return '
			<table border="0" cellpadding="0" cellspacing="0" width="100%" id="typo3-funcmenu">
				<tr>
					<td valign="top" class="nowrap content" nowrap="nowrap">'.$content.'</td>
					<td valign="top" class="nowrap alignRight func" align="right" nowrap="nowrap">'.$menu.'</td>
				</tr>
			</table>';
	}

	/**
	 * Creates the HTML content for the tab menu
	 *
	 * @param	array		Menu items for tabs
	 * @return	string		Table HTML
	 * @access private
	 */
	function getTabMenuRaw($menuItems)	{
		$content='';

		if (is_array($menuItems))	{
			$options='';

			$count = count($menuItems);
			$widthLeft = 1;
			$addToAct = 5;

			$widthRight = max (1,floor(30-pow($count,1.72)));
			$widthTabs = 100 - $widthRight - $widthLeft;
			$widthNo = floor(($widthTabs - $addToAct)/$count);
			$addToAct = max ($addToAct,$widthTabs-($widthNo*$count));
			$widthAct = $widthNo + $addToAct;
			$widthRight = 100 - ($widthLeft + ($count*$widthNo) + $addToAct);

			$first=true;
			$count=1;
			foreach($menuItems as $id => $def) {
				$isActive = $def['isActive'];
				$class = $isActive ? 'tabact' : 'tab';
				$width = $isActive ? $widthAct : $widthNo;

					// @rene: Here you should probably wrap $label and $url in htmlspecialchars() in order to make sure its XHTML compatible! I did it for $url already since that is VERY likely to break.
				$label = $def['label'];
				$url = htmlspecialchars($def['url']);
				$params = $def['addParams'];

				if($first) {
					$options.= '
							<td width="'.$width.'%" class="'.$class.' otherCell" id="cell'.$count.'"><table class="tabTable" cellspacing="0" cellpadding="0" border="0"><tr><td class="left">&nbsp;</td><td class="middle"><a href="'.$url.'" '.$params.'>'.$label.'</a></td><td class="right">&nbsp;</td></tr></table></td>';
				} else {
					$options.='
							<td width="'.$width.'%" class="'.$class.' otherCell" id="cell'.$count.'"><table class="tabTable" cellspacing="0" cellpadding="0" border="0"><tr><td class="left">&nbsp;</td><td class="middle"><a href="'.$url.'" '.$params.'>'.$label.'</a></td><td class="right">&nbsp;</td></tr></table></td>';
				}
				$first=false;
				$count++;
			}

			if ($options)	{
				$content .= '
				<!-- Tab menu -->
				<table cellpadding="0" cellspacing="0" border="0" width="100%" id="typo3-tabmenu">
					<tr>
							<td class="firstCell" width="'.$widthLeft.'%">&nbsp;</td>
							'.$options.'
						<td class="lastCell" width="'.$widthRight.'%"><span class="lastCell">&nbsp;</span></td>
					</tr>
				</table>
				<div class="hr" style="margin:0px"></div>';
			}

		}
		return $content;
	}

	/**
	 * Creates a DYNAMIC tab-menu where the tabs are switched between with DHTML.
	 * Should work in MSIE, Mozilla, Opera and Konqueror. On Konqueror I did find a serious problem: <textarea> fields loose their content when you switch tabs!
	 *
	 * @param	array		Numeric array where each entry is an array in itself with associative keys: "label" contains the label for the TAB, "content" contains the HTML content that goes into the div-layer of the tabs content. "description" contains description text to be shown in the layer. "linkTitle" is short text for the title attribute of the tab-menu link (mouse-over text of tab). "stateIcon" indicates a standard status icon (see ->icon(), values: -1, 1, 2, 3). "icon" is an image tag placed before the text.
	 * @param	string		Identification string. This should be unique for every instance of a dynamic menu!
	 * @param	integer		If "1", then enabling one tab does not hide the others - they simply toggles each sheet on/off. This makes most sense together with the $foldout option. If "-1" then it acts normally where only one tab can be active at a time BUT you can click a tab and it will close so you have no active tabs.
	 * @param	boolean		If set, the tabs are rendered as headers instead over each sheet. Effectively this means there is no tab menu, but rather a foldout/foldin menu. Make sure to set $toggle as well for this option.
	 * @param	integer		Character limit for a new row.
	 * @param	boolean		If set, tab table cells are not allowed to wrap their content
	 * @param	boolean		If set, the tabs will span the full width of their position
	 * @param	integer		Default tab to open (for toggle <=0). Value corresponds to integer-array index + 1 (index zero is "1", index "1" is 2 etc.). A value of zero (or something non-existing) will result in no default tab open.
	 * @return	string		JavaScript section for the HTML header.
	 */
	
	function getDynTabMenu($menuItems,$identString,$toggle=0,$foldout=FALSE,$newRowCharLimit=50,$noWrap=1,$fullWidth=FALSE,$defaultTabIndex=1)	{
		$content = '';

		if (is_array($menuItems))	{

				// Init:
			$options = array(array());
			$divs = array();
			$JSinit = array();
			$id = 'DTM-'.t3lib_div::shortMD5($identString);
			$noWrap = $noWrap ? ' nowrap="nowrap"' : '';

				// Traverse menu items
			$c=0;
			$tabRows=0;
			$titleLenCount = 0;
			foreach($menuItems as $index => $def) {
				$index+=1;	// Need to add one so checking for first index in JavaScript is different than if it is not set at all.

					// Switch to next tab row if needed
				if (!$foldout && $titleLenCount>$newRowCharLimit)	{	// 50 characters is probably a reasonable count of characters before switching to next row of tabs.
					$titleLenCount=0;
					$tabRows++;
					$options[$tabRows] = array();
				}

				if ($toggle==1)	{
					$onclick = 'this.blur(); DTM_toggle("'.$id.'","'.$index.'"); return false;';
				} else {
					$onclick = 'this.blur(); DTM_activate("'.$id.'","'.$index.'", '.($toggle<0?1:0).'); return false;';
				}

				$isActive = strcmp($def['content'],'');
				
				/* styles are controlled only using CSS - then layout control using onmousover/out must disable*/
				$mouseOverOut = '';//'onmouseover="DTM_mouseOver(this);" onmouseout="DTM_mouseOut(this);"';
				
				if (!$foldout)	{
						// Create TAB cell:
					$options[$tabRows][] = '
							<td class="'.($isActive ? 'tab' : 'disabled').'" id="'.$id.'-'.$index.'-MENU"'.$noWrap.$mouseOverOut.'>'.
							($isActive ? '<table class="tabTable" cellspacing="0" cellpadding="0" border="0"><tr><td class="left">&nbsp;</td><td class="middle"><a href="#" onclick="'.htmlspecialchars($onclick).'"'.($def['linkTitle'] ? ' title="'.htmlspecialchars($def['linkTitle']).'"':'').'>' : '<table class="tabTable" cellspacing="0" cellpadding="0" border="0"><tr><td class="left">&nbsp;</td><td class="middle"><span class="disabled">').
							$def['icon'].
							($def['label'] ? htmlspecialchars($def['label']) : '<span class="space"></span>').
							$this->icons($def['stateIcon'],'').
							($isActive ? '</a></td><td class="right">&nbsp;</td></tr></table>' :'</span></td><td class="right">&nbsp;</td></tr></table>').
							'</td>';
					$titleLenCount+= strlen($def['label']);
					} 
				else 	{
					if(empty($divs)) $extraClass=" firstItem";
					$divs[] = '
						<div class="'.($isActive ? 'tab' : 'disabled').$extraClass.'" id="'.$id.'-'.$index.'-MENU"'.$mouseOverOut.'>'.
							($isActive ? '<table class="tabTable" cellspacing="0" cellpadding="0" border="0"><tr><td class="left">&nbsp;</td><td class="middle"><a href="#" onclick="'.htmlspecialchars($onclick).'"'.($def['linkTitle'] ? ' title="'.htmlspecialchars($def['linkTitle']).'"':'').'>' : '').
							$def['icon'].
							($def['label'] ? htmlspecialchars($def['label']) : '<span class="space"></span>').
							($isActive ? '</a></td><td class="right">&nbsp;</td></tr></table>' :'</span></td><td class="right">&nbsp;</td></tr></table>').
							'</div>';
				
					}

				if ($isActive)	{
						// Create DIV layer for content:print "foor1";
						// Create DIV layer for content:
					$divs[] = '
							<div style="display: none;" id="'.$id.'-'.$index.'-DIV" class="c-tablayer'.$extraClass.'">'.
								($def['description'] ? '<p class="c-descr">'.nl2br(htmlspecialchars($def['description'])).'</p>' : '').
								$def['content'].
								'</div>';
						// Create initialization string:
					$JSinit[] = '
							DTM_array["'.$id.'"]['.$c.'] = "'.$id.'-'.$index.'";
					';
					if ($toggle==1)	{
						$JSinit[] = '
							if (top.DTM_currentTabs["'.$id.'-'.$index.'"]) { DTM_toggle("'.$id.'","'.$index.'",1); }
						';
					}

					$c++;
					$counItems++;
				}
			}

				// Render menu:
			if (count($options))	{

					// Tab menu is compiled:
				if (!$foldout)	{
					$tabContent = '';
					for($a=0;$a<=$tabRows;$a++)	{
						$tabContent.= '

					<!-- Tab menu -->
					<table cellpadding="0" cellspacing="0" border="0"'.($fullWidth ? ' width="100%"' : '').' class="typo3-dyntabmenu">
						<tr>';
					#if($a==0) $extraClass = ' first';
					$tabContent .= 	implode('',$options[$a]);
					$tabContent .= '
						</tr>
					</table>';
					}
					$content.= '<div class="typo3-dyntabmenu-tabs">'.$tabContent.'</div>';
				}

					// Div layers are added:
				$countDivs=count($divs);
								
				$content .= '
				<!-- Div layers for tab menu: -->
				<div class="typo3-dyntabmenu-divs'.($foldout?'-foldout':'').'">';
				
				for($i=0;$i<$countDivs;$i++){
					if($i==0) 
						$content .= '<div class="firstOptions">'.$divs[$i].'</div>';
					else
						$content .= '<div class="otherOptions">'.$divs[$i].'</div>';
					}
				$content .= '</div>';
					// Java Script section added:
				$content.= '
				<!-- Initialization JavaScript for the menu -->
				<script type="text/javascript">
					DTM_array["'.$id.'"] = new Array();
					'.implode('',$JSinit).'
					'.($toggle<=0 ? 'DTM_activate("'.$id.'", top.DTM_currentTabs["'.$id.'"]?top.DTM_currentTabs["'.$id.'"]:'.intval($defaultTabIndex).', 0);' : '').'
				</script>

				';
			}

		}
		return $content;
	}
	

}
?>
