<?php
/***************************************************************/

class ux_SC_rte_select_image extends SC_rte_select_image {

	function init()	{
		global $LANG,$BACK_PATH;

		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->JScode='
		<script language="javascript" type="text/javascript">
			function jumpToUrl(URL,anchor)	{	//
				var add_act = URL.indexOf("act=")==-1 ? "&act='.$this->act.'" : "";
				var RTEtsConfigParams = "&RTEtsConfigParams='.rawurlencode(t3lib_div::_GP('RTEtsConfigParams')).'";
		
				var cur_width = selectedImageRef ? "&cWidth="+selectedImageRef.width : "";
				var cur_height = selectedImageRef ? "&cHeight="+selectedImageRef.height : "";
		
				var theLocation = URL+add_act+RTEtsConfigParams+cur_width+cur_height+(anchor?anchor:"");
				document.location = theLocation;
				return false;
			}
			
			/*
			function insertImage(file,width,height)	{	//
				self.parent.parent.renderPopup_insertImage(\'<img src="\'+file+\'" width="\'+width+\'" height="\'+height+\'" border=0>\');
			}
			*/
			
			function insertImage(file,width,height)	{
				//self.parent.parent.renderPopup_insertImage(\'<img src="\'+file+\'" width="\'+width+\'" height="\'+height+\'" border=0>\');
				if(parent.parent.setTheValue) {
					parent.parent.setTheValue(\'image\',file+"|"+width+"|"+height,document.location.search);
					tinyMCEPopup.close();
					}
				else {
					window.opener.setTheValue(\'image\',file+"|"+width+"|"+height,document.location.search);
					self.close();
					}
				}
			
			function launchView(url)	{	//
				var thePreviewWindow="";
				thePreviewWindow = window.open("'.$this->siteUrl.TYPO3_mainDir.'show_item.php?table="+url,"ShowItem","height=300,width=410,status=0,menubar=0,resizable=0,location=0,directories=0,scrollbars=1,toolbar=0");	
				if (thePreviewWindow && thePreviewWindow.focus)	{
					thePreviewWindow.focus();
				}
			}
			function getCurrentImageRef()	{	//
				if (self.parent.parent 
				&& self.parent.parent.document.idPopup 
				&& self.parent.parent.document.idPopup.document 
				&& self.parent.parent.document.idPopup.document._selectedImage)	{
		//			self.parent.parent.debugObj(self.parent.parent.document.idPopup.document._selectedImage);
					return self.parent.parent.document.idPopup.document._selectedImage;
				}
				return "";
			}
			function printCurrentImageOptions()	{	//
		//		alert(selectedImageRef.href);
				var styleSelector=\'<select name="iClass" style="width:140px;"><option value=""></option><option value="TestClass">TestClass</option></select>\';
				var alignSelector=\'<select name="iAlign" style="width:60px;"><option value=""></option><option value="left">Left</option><option value="right">Right</option></select>\';
				var bgColor=\' class="bgColor4"\';
				var sz="";
				sz+=\'<table border=0 cellpadding=1 cellspacing=1><form action="" name="imageData">\';
				sz+=\'<tr><td\'+bgColor+\'>'.$LANG->getLL("width").': <input type="text" name="iWidth" value=""'.$GLOBALS["TBE_TEMPLATE"]->formWidth(4).'>&nbsp;&nbsp;'.$LANG->getLL("height").': <input type="text" name="iHeight" value=""'.$GLOBALS["TBE_TEMPLATE"]->formWidth(4).'>&nbsp;&nbsp;'.$LANG->getLL("border").': <input type="checkbox" name="iBorder" value="1"></td></tr>\';
				sz+=\'<tr><td\'+bgColor+\'>'.$LANG->getLL("margin_lr").': <input type="text" name="iHspace" value=""'.$GLOBALS["TBE_TEMPLATE"]->formWidth(4).'>&nbsp;&nbsp;'.$LANG->getLL("margin_tb").': <input type="text" name="iVspace" value=""'.$GLOBALS["TBE_TEMPLATE"]->formWidth(4).'></td></tr>\';
		//		sz+=\'<tr><td\'+bgColor+\'>Textwrapping: \'+alignSelector+\'&nbsp;&nbsp;Style: \'+styleSelector+\'</td></tr>\';
				sz+=\'<tr><td\'+bgColor+\'>'.$LANG->getLL("title").': <input type="text" name="iTitle"'.$GLOBALS["TBE_TEMPLATE"]->formWidth(20).'></td></tr>\';
				sz+=\'<tr><td><input type="submit" value="'.$LANG->getLL("update").'" onClick="return setImageProperties();"></td></tr>\';
				sz+=\'</form></table>\';
				return sz;
			}
			function setImageProperties()	{	//
				if (selectedImageRef)	{
					selectedImageRef.width=document.imageData.iWidth.value;
					selectedImageRef.height=document.imageData.iHeight.value;
					selectedImageRef.vspace=document.imageData.iVspace.value;
					selectedImageRef.hspace=document.imageData.iHspace.value;
					selectedImageRef.title=document.imageData.iTitle.value;
					selectedImageRef.alt=document.imageData.iTitle.value;
		
					selectedImageRef.border= (document.imageData.iBorder.checked ? 1 : 0);
		
		/*			
					var iAlign = document.imageData.iAlign.options[document.imageData.iAlign.selectedIndex].value;
					if (iAlign || selectedImageRef.align)	{
						selectedImageRef.align=iAlign;
					}
		
					selectedImageRef.style.cssText="";
		
					var iClass = document.imageData.iClass.options[document.imageData.iClass.selectedIndex].value;
					if (iClass || (selectedImageRef.attributes["class"] && selectedImageRef.attributes["class"].value))	{
						selectedImageRef["class"]=iClass;
						selectedImageRef.attributes["class"].value=iClass;
					}
		*/
		//			selectedImageRef.style="";
					self.parent.parent.edHidePopup();
				}
				return false;
			}
			function insertImagePropertiesInForm()	{	//
				if (selectedImageRef)	{
					document.imageData.iWidth.value = selectedImageRef.width;
					document.imageData.iHeight.value = selectedImageRef.height;
					document.imageData.iVspace.value = selectedImageRef.vspace;
					document.imageData.iHspace.value = selectedImageRef.hspace;
					document.imageData.iTitle.value = selectedImageRef.title;
					if (parseInt(selectedImageRef.border))	{
						document.imageData.iBorder.checked = 1;
					}
		/*
						// Update align
					var fObj=document.imageData.iAlign;
					var value=selectedImageRef.align;
					var l=fObj.length;
					for (a=0;a<l;a++)	{
						if (fObj.options[a].value == value)	{
							fObj.selectedIndex = a;
						}
					}
						// Update class
							// selectedImageRef.className ??
					var fObj=document.imageData.iClass;
					var value=selectedImageRef.attributes["class"].value;
					var l=fObj.length;
					for (a=0;a<l;a++)	{
						if (fObj.options[a].value == value)	{
							fObj.selectedIndex = a;
						}
					}
					*/
					
				}
			//	alert(document.imageData);
				return false;
			}
			
			function openDragDrop()	{	//
				var url = "'.$this->doc->backPath.'browse_links.php?mode=filedrag&bparams=|||"+escape("gif,jpg,jpeg,png");
				browserWin = window.open(url,"Typo3WinBrowser","height=350,width=600,status=0,menubar=0,resizable=1,scrollbars=1");
				browserWin.focus();
				self.parent.parent.edHidePopup(1);
			}
		
			var selectedImageRef = getCurrentImageRef();	// Setting this to a reference to the image object.
		
			'.($this->act=="dragdrop"?"openDragDrop();":"").'
			
		//	alert(selectedImageRef.href);
		</script>
		';
		
			// Starting content:
		$this->content="";
		$this->content.=$this->doc->startPage("RTE image insert");
	}
	
	function main()	{
		global $LANG, $TYPO3_CONF_VARS, $FILEMOUNTS;
		
		$menu='<table border=0 cellpadding="0" cellspacing="0" class="typo3-imageMenu"><tr>';
		$bgcolor=' class="bgColor4"';
		$bgcolorA=' class="bgColor5"';
		if ($this->act=="image" || t3lib_div::_GP("cWidth"))	{	// If $this->act is specifically set to "image" or if cWidth is passed around...
			$menu.='<td align=center nowrap="nowrap" width="25%"'.($this->act=="image"?$bgcolorA:$bgcolor).'><a href="#" onClick="jumpToUrl(\'?act=image\');return false;"><strong>'.$LANG->getLL("currentImage").'</strong></a></td>';
		}
		// extra table in order to create tab-style links like in the default pop-up dialog windows of TinyMCE
			if (in_array("magic",$this->allowedItems))	$menu.='<td id="magic" align=center nowrap="nowrap" width="25%"'.($this->act=="magic"?$bgcolorA:$bgcolor).'><table class="tabTable" cellspacing="0" cellpadding="0" border="0"><tr><td class="left"></td><td class="middle"><a href="#" onClick="jumpToUrl(\'?act=magic\');return false;">'.$LANG->getLL("magicImage").'</a></td><td class="right"></td></tr></table></td>';
			if (in_array("plain",$this->allowedItems))	$menu.='<td id="plain" align=center nowrap="nowrap" width="25%"'.($this->act=="plain"?$bgcolorA:$bgcolor).'><table class="tabTable" cellspacing="0" cellpadding="0" border="0"><tr><td class="left"></td><td class="middle"><a href="#" onClick="jumpToUrl(\'?act=plain\');return false;">'.$LANG->getLL("plainImage").'</a></td><td class="right"></td></tr></table></td>';
			if (in_array("dragdrop",$this->allowedItems))	$menu.='<td id="dragdrop" align=center nowrap="nowrap" width="25%"'.$bgcolor.'><table class="tabTable" cellspacing="0" cellpadding="0" border="0"><tr><td class="left"></td><td class="middle"><a href="#" onClick="openDragDrop();return false;">'.$LANG->getLL("dragDropImage").'</a></td><td class="right"></td></tr></table></td>';
		$menu.='</tr></table>';
		
		$this->content.=$menu;
	
		if ($this->act!="image")	{

				// Getting flag for showing/not showing thumbnails:
			$noThumbs = $GLOBALS["BE_USER"]->getTSConfigVal("options.noThumbsInRTEimageSelect");
		
			if (!$noThumbs)	{
					// MENU-ITEMS, fetching the setting for thumbnails from File>List module:
				$_MOD_MENU = array('displayThumbs' => '');
				$_MCONF['name']='file_list';
				$_MOD_SETTINGS = t3lib_BEfunc::getModuleData($_MOD_MENU, t3lib_div::_GP('SET'), $_MCONF['name']);
				$addParams = '&act='.$this->act.'&expandFolder='.rawurlencode($this->modData["expandFolder"]);
				$thumbNailCheck = t3lib_BEfunc::getFuncCheck('','SET[displayThumbs]',$_MOD_SETTINGS['displayThumbs'],'rte_select_image.php',$addParams).' '.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_file_list.php:displayThumbs',1);
			} else {
				$thumbNailCheck='';
			}

				// File-folders:	
			$foldertree = t3lib_div::makeInstance("localFolderTree");
			$tree=$foldertree->getBrowsableTree();
			list(,,$specUid) = explode("_",t3lib_div::_GP("PM"));
			$files = $this->expandFolder($foldertree->specUIDmap[$specUid],$this->act=="plain",$noThumbs?$noThumbs:!$_MOD_SETTINGS['displayThumbs']);
			
			$this->content.= '<table border=0 cellpadding=0 cellspacing=0 class="folderTree">
			<tr>
				<td valign=top class="headerCell">'.$this->barheader($LANG->getLL("folderTree").':').$tree.'</td>
				<td class="spacerTd">&nbsp;</td>
				<td valign=top class="files">'.$files.'</td>
			</tr>
			</table>'.$thumbNailCheck;
			
			
			
			/*
				// Target:
			if ($this->act!="mail")	{
				$ltarget='<table border=0 cellpadding=2 cellspacing=1><form name="ltargetform" id="ltargetform"><tr>';
				$ltarget.='<td width=90>Target:</td>';
				$ltarget.='<td><input type="text" name="ltarget" onChange="setTarget(this.value);" value="'.htmlspecialchars($curUrlArray["target"]).'"></td>';
				$ltarget.='<td><select name="ltarget_type" onChange="setTarget(this.options[this.selectedIndex].value);document.ltargetform.ltarget.value=this.options[this.selectedIndex].value;this.selectedIndex=0;">
				<option></option>
				<option value="_top">Top</option>
				<option value="_blank">New window</option>
				</select></td>';
				if (($curUrlInfo["act"]=="page" || $curUrlInfo["act"]=="file") && $curUrlArray["href"])	{
					$ltarget.='<td><input type="submit" value="Update" onClick="return link_current();"></td>';
				}
				$ltarget.='</tr></form></table>';
				
				$this->content.=$ltarget;
			}
			*/
			
			
			
			
			// ***************************
			// Upload
			// ***************************

			$fileProcessor = t3lib_div::makeInstance("t3lib_basicFileFunctions");
			$fileProcessor->init($FILEMOUNTS, $TYPO3_CONF_VARS["BE"]["fileExtensions"]);
			$path=t3lib_div::_GP("expandFolder");
			if (!$path || $path=="/" || !@is_dir($path))	{
				$path = $fileProcessor->findTempFolder();	// The closest TEMP-path is found
				if ($path)	$path.="/";
			}
			if ($path && @is_dir($path))	{
				$this->content.=$this->uploadForm($path).'<br class="uploadForm">';
			}
		
			// ***************************
			// Help
			// ***************************
			
			if ($this->act=="magic")	{
				$this->content.='<img src="'.$this->doc->backPath.'gfx/icon_note.gif" width="18" height="16" align=top>'.$LANG->getLL("magicImage_msg").'<br class="message">';
			}
			if ($this->act=="plain")	{
				$this->content.='<img src="'.$this->doc->backPath.'gfx/icon_note.gif" width="18" height="16" align=top>'.$LANG->getLL("plainImage_msg").'<br class="message">';
			}
		} else {

			$this->content.='
			<script language="javascript" type="text/javascript">
		document.write(printCurrentImageOptions());
		insertImagePropertiesInForm();
			</script>
			';
		}

	}

	/***************************
	 *
	 * OTHER FUNCTIONS:	
	 *
	 ***************************/

	/**
	 * [Describe function...]
	 * 
	 * @param	[type]		$path: ...
	 * @return	[type]		...
	 */
	function uploadForm($path)	{
		global $LANG,$SOBE;

	//	debug($path);
		$count=1;
		$header = t3lib_div::isFirstPartOfStr($path,PATH_site)?substr($path,strlen(PATH_site)):$path;
		$code=$this->barheader($LANG->getLL("uploadImage").":");
		$code.='<table border=0 cellpadding=0 cellspacing=3 class="uploadForm"><FORM action="'.$this->doc->backPath.'tce_file.php" method="post" name="editform" enctype="'.$GLOBALS["TYPO3_CONF_VARS"]["SYS"]["form_enctype"].'"><tr><td>';
		$code.="<strong>".$LANG->getLL("path").":</strong> ".$header."</td></tr><tr><td>";
		for ($a=1;$a<=$count;$a++)	{
			$code.='<input class="file" type="File" name="upload_'.$a.'"'.$this->doc->formWidth(35).' size="50">
				<input type="Hidden" name="file[upload]['.$a.'][target]" value="'.$path.'">
				<input type="Hidden" name="file[upload]['.$a.'][data]" value="'.$a.'"><br class="beforeSelectImage">';
		}
		$code.='
			<input type="Hidden" name="redirect" value="'.t3lib_extMgm::extRelPath('tinyrte').'rte_select_image.php?act='.$this->act.'&expandFolder='.rawurlencode($path).'&RTEtsConfigParams='.rawurlencode(t3lib_div::_GP("RTEtsConfigParams")).'">
			<input class="submit" type="Submit" name="submit" value="'.$LANG->sL("LLL:EXT:lang/locallang_core.php:file_upload.php.submit").'">
			<div id="c-override">
				<input class="checkbox" type="checkbox" name="overwriteExistingFiles" value="1" /> '.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_misc.php:overwriteExistingFiles',1).'
			</div>
			
		</td>
		</tr>
		</FORM>
		</table>';

		return $code;
	}
}
?>
