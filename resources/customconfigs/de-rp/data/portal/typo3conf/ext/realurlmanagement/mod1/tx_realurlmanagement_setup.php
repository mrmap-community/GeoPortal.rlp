<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005-2006 Juraj Sulek (juraj@sulek.sk)
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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Plugin tx_realurlmanagement_setup.
 *
 * @author	Juraj Sulek <juraj@sulek.sk>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   53: class tx_realurlmanagement_setup extends t3lib_div
 *   78:     function init()
 *  127:     function showModule()
 *  322:     function submit_editform(elementid,action)
 *  364:     function resolve_TYPO3_SITE_URL($confArray,&$treeArray)
 *  476:     function resolve_TYPO3_SITE_URL_conf($confArray,&$treeArray,$livePath)
 *  552:     function resolve_init_conf($confArray,&$treeArray,$livePath)
 *  667:     function processSetupArray($array,$livePath)
 *  830:     function addContentMenuTable($array)
 *  843:     function addContentMenuTableOnce($row)
 *
 * TOTAL FUNCTIONS: 9
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
require_once('tx_realurlmanagement_setupbrowsetree.php');
require_once(PATH_t3lib.'class.t3lib_ajax.php');
$LANG->includeLLFile("EXT:realurlmanagement/mod1/locallang_setup.php");

class tx_realurlmanagement_setup extends t3lib_div {
	var $helpfunc;
	var $SETUPCONFARR=array();
	var $pObj;
	var $hostnames=array();
	var $myArray=array();
	var $icons=array();
	var $contentMenuHtml=array();
	var $contentMenuReturn='';

	var $action='';
	var $actionId='';
	var $actionHash='';
	var $actionLivePath='';
	var $actValue='';
	var $actionError='';

	/*var $scriptText='';*/

	var $setupTreeArr;
	var $gfxBackPath;
	var $myId=1;
	var $walkId=0;


	function init(){
		global $LANG;
		$this->scriptText='';

		$this->gfxBackPath=t3lib_div::resolveBackPath($GLOBALS['BACK_PATH'].TYPO3_MOD_PATH.'../');
		/* prepare icons begin */
		$this->icons['domain']='<img'.t3lib_iconWorks::skinImg($this->gfxBackPath,'gfx/setup/domain.gif','width="18" height="16"').' alt="" />';
		$this->icons['domain_copy']='<img'.t3lib_iconWorks::skinImg($this->gfxBackPath,'gfx/setup/domain_copy.gif','width="18" height="16"').' alt="" />';
		$this->icons['domain_copy_notexist']='<img'.t3lib_iconWorks::skinImg($this->gfxBackPath,'gfx/setup/domain_copy_notexist.gif','width="18" height="16"').' alt="" />';
		$this->icons['domain_copy_subdomain']='<img'.t3lib_iconWorks::skinImg($this->gfxBackPath,'gfx/setup/domain.gif','width="18" height="16"').' alt="" />';
		$this->icons['domain_copy_subdomain_notexist']='<img'.t3lib_iconWorks::skinImg($this->gfxBackPath,'gfx/setup/domain_notexist.gif','width="18" height="16"').' alt="" />';
		$this->icons['defaultIcon']='<img'.t3lib_iconWorks::skinImg($this->gfxBackPath,'gfx/setup/others.gif','width="13" height="12"').' alt="" />';
		/* prepare icons end */


		/* contentMenuHtml begin */
		$this->contentMenuHtml['tableBegin']='<table border="0" cellpadding="0" cellspacing="0" class="typo3-CSM bgColor4">';
		$this->contentMenuHtml['tableEnd']='</table>';
		$this->contentMenuHtml['tableHr']='<tr class="bgColor2"><td colspan="2"><img src="clear.gif" width="1" height="1" alt="" /></td></tr>';
		$this->contentMenuHtml['tempTRBegin']='<tr class="typo3-CSM-itemRow" onclick="'.htmlspecialchars($onClick).'" onmouseover="this.bgColor=\''.$GLOBALS['TBE_TEMPLATE']->bgColor5.'\';" onmouseout="this.bgColor=\'\';" oncontextmenu="javascript:return false;">';
		$this->contentMenuHtml['tempTREnd']='</tr>';

		$this->contentMenuHtml['edit']='<td class="typo3-CSM-item">'.$LANG->getLL('context_menu_edit',1).'</td><td align="center"><img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/edit2.gif','width="11" height="12"').' border="0" title="'.$LANG->getLL('context_menu_edit',1).'" alt="'.$LANG->getLL('context_menu_edit',1).'" /></td>';
		$this->contentMenuHtml['newBefore']='<td class="typo3-CSM-item">'.$LANG->getLL('context_menu_newBefore',1).'</td><td align="center"><img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/new_el.gif','width="11" height="12"').' border="0" title="'.$LANG->getLL('context_menu_newBefore',1).'" alt="'.$LANG->getLL('context_menu_newBefore',1).'" /></td>';
		$this->contentMenuHtml['newAfter']='<td class="typo3-CSM-item">'.$LANG->getLL('context_menu_newAfter',1).'</td><td align="center"><img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/new_el.gif','width="11" height="12"').' border="0" title="'.$LANG->getLL('context_menu_newAfter',1).'" alt="'.$LANG->getLL('context_menu_newAfter',1).'" /></td>';
		$this->contentMenuHtml['newInside']='<td class="typo3-CSM-item">'.$LANG->getLL('context_menu_newInside',1).'</td><td align="center"><img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/new_el.gif','width="11" height="12"').' border="0" title="'.$LANG->getLL('context_menu_newInside',1).'" alt="'.$LANG->getLL('context_menu_newInside',1).'" /></td>';
		$this->contentMenuHtml['delete']='<td class="typo3-CSM-item">'.$LANG->getLL('context_menu_delete',1).'</td><td align="center"><img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif','width="11" height="12"').' border="0" title="'.$LANG->getLL('context_menu_delete',1).'" alt="'.$LANG->getLL('context_menu_delete',1).'" /></td>';
		$this->contentMenuHtml['moveTop']='<td class="typo3-CSM-item">'.$LANG->getLL('context_menu_moveUp',1).'</td><td align="center"><img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/button_up.gif','width="11" height="10"').' border="0" title="'.$LANG->getLL('context_menu_moveUp',1).'" alt="'.$LANG->getLL('context_menu_moveUp',1).'" /></td>';
		$this->contentMenuHtml['moveBottom']='<td class="typo3-CSM-item">'.$LANG->getLL('context_menu_moveDown',1).'</td><td align="center"><img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/button_down.gif','width="11" height="10"').' border="0" title="'.$LANG->getLL('context_menu_moveDown',1).'" alt="'.$LANG->getLL('context_menu_moveDown',1).'" /></td>';
		/* contentMenuHtml end */

		$this->action=t3lib_div::_GP('act');
		$this->actionId=t3lib_div::_GP('actid');
		$this->actionHash=t3lib_div::_GP('acthash');
		$this->actionLivePath=$this->helpfunc->getArrayFrom_GP(t3lib_div::_GP('actlivepath'));
		$this->actValue=t3lib_div::_GP('actValue');
		if((intval(t3lib_div::_GP('closedok_x'))!=0)&&(intval(t3lib_div::_GP('closedok_y'))!=0)){
			$this->action='none';
		};
		if((intval(t3lib_div::_GP('editsave_x'))!=0)&&(intval(t3lib_div::_GP('editsave_y'))!=0)){
			$this->action.='save';
		};
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function showModule(){
		global $LANG,$BEUSER;
		$this->init();

		$this->myArray=array(
			'_DEFAULT'=>array(
				'init'=>array(
					'doNotRawUrlEncodeParameterNames'=>1,'enableCHashCache'=>1,'respectSimulateStaticURLs'=>1,'appendMissingSlash'=>1,'adminJumpToBackend'=>1,'postVarSet_failureMode'=>1,'disableErrorLog'=>1,'enableUrlDecodeCache'=>1,'enableUrlEncodeCache'=>1,'emptyUrlReturnValue'=>1,'rootPageID'=>1,'enableDomainLookup'=>1
				),
				'redirects'=>array(),
				'preVars'=>array(),
				'redirects_regex'=>array(),
				'pagePath'=>array(),
				'fixedPostVars'=>array(),
				'postVarSets'=>array(),
				'fileName'=>array(),
			),
			'www.test.sk'=>array(),
			'www.sme.sk'=>'_DEFAULT',
			'www.aaa.sk'=>'test.sk',
		);


		$content.='<div id="contentMenu0" style="z-index:1; position:absolute;visibility:hidden"></div><div id="contentMenu1" style="z-index:2; position:absolute;visibility:hidden"></div><div id="dragIcon" style="z-index:1;position:absolute;visibility:hidden;filter:alpha(opacity=50);-moz-opacity:0.5;opacity:0.5;"><img src="" width="18" height="16"></div>';
		$content.='<input type="hidden" name="id" value="'.$this->pObj->pageuid.'" />';
		$content.='<input type="hidden" name="act" id="formField_act" value="'.$this->action.'" />';
		$content.='<input type="hidden" name="actid" id="formField_actid" value="" />';
		$content.='<input type="hidden" name="acthash" id="formField_acthash" value="'.$this->actionHash.'" />';
		$content.='<input type="hidden" name="actlivepath" id="formField_actlivepath" value="'.$this->helpfunc->setArrayTo_GP($this->actionLivePath).'" />';
		$content.='<input type="hidden" name="actTstamp" value="'.time().'" />';

		$this->myArray=$this->processSetupArray($this->myArray, $this->actionLivePath); //getall
		$this->resolve_TYPO3_SITE_URL($this->myArray,$this->setupTreeArr);

		//print_r($this->setupTreeArr);

		$this->addContentMenuTable($this->setupTreeArr);
		$content.=$this->contentMenuReturn;
		$this->pObj->doc->postCode.='

<script type="text/javascript">
	var GLV_gap=10;
	var GLV_curLayerX=new Array(0,0);
	var GLV_curLayerY=new Array(0,0);
	var GLV_curLayerWidth=new Array(0,0);
	var GLV_curLayerHeight=new Array(0,0);
	var GLV_isVisible=new Array(0,0);
	var GLV_x=0;
	var GLV_y=0;
	var GLV_xRel=0;
	var GLV_yRel=0;
	var layerObj=new Array();
	var layerObjCss=new Array();
	function GL_checkBrowser(){	//
		this.dom= (document.getElementById);
		this.op=  (navigator.userAgent.indexOf("Opera")>-1);
		this.op7=  this.op && (navigator.appVersion.indexOf("7")>-1);  // check for Opera version 7
		this.konq=  (navigator.userAgent.indexOf("Konq")>-1);
		this.ie4= (document.all && !this.dom && !this.op && !this.konq);
		this.ie5= (document.all && this.dom && !this.op && !this.konq);
		this.ns4= (document.layers && !this.dom && !this.konq);
		this.ns5= (!document.all && this.dom && !this.op && !this.konq);
		this.ns6= (this.ns5);
		this.bw=  (this.ie4 || this.ie5 || this.ns4 || this.ns6 || this.op || this.konq);
		return this;
	}
	bw= new GL_checkBrowser();

	function GL_getObj(obj){	//
		nest="";
		this.el= (bw.ie4||bw.op7)?document.all[obj]:bw.ns4?eval(nest+"document."+obj):document.getElementById(obj);
	   	this.css= bw.ns4?this.el:this.el.style;
		this.ref= bw.ns4?this.el.document:document;
		this.x= (bw.ns4||bw.op)?this.css.left:this.el.offsetLeft;
		this.y= (bw.ns4||bw.op)?this.css.top:this.el.offsetTop;
		this.height= (bw.ie4||bw.dom)?this.el.offsetHeight:bw.ns4?this.ref.height:0;
		this.width= (bw.ie4||bw.dom)?this.el.offsetWidth:bw.ns4?this.ref.width:0;
		return this;
	}

	function GL_getObjCss(obj){	//
		return bw.dom? document.getElementById(obj).style:bw.ie4?document.all[obj].style:bw.ns4?document.layers[obj]:0;
	}


	function GL_getMouse(event) {	//
		if (layerObj)	{
			GLV_xRel = event.clientX-2;
			GLV_yRel = event.clientY-2;
			GLV_x = GLV_xRel + (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
			GLV_y = GLV_yRel + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);

			if (GLV_isVisible[1])	{
				if (outsideLayer(1))	hideSpecific(1);
			} else if (GLV_isVisible[0])	{
				if (outsideLayer(0))	hideSpecific(0);
			}
		}
	}
		// outsideLayer(level)
	function outsideLayer(level)	{	//
		return GLV_x+GLV_gap-GLV_curLayerX[level] <0 ||
				GLV_y+GLV_gap-GLV_curLayerY[level] <0 ||
				GLV_curLayerX[level]+GLV_curLayerWidth[level]+GLV_gap-GLV_x <0 ||
				GLV_curLayerY[level]+GLV_curLayerHeight[level]+GLV_gap-GLV_y <0;
	}
		// setLayerObj(html,level)
	function setLayerObj(html,level)	{	//
		//GL_getMouse(event);
		var winHeight = document.documentElement.clientHeight && !bw.op7 ? document.documentElement.clientHeight : document.body.clientHeight;
		var winWidth = document.documentElement.clientWidth && !bw.op7 ? document.documentElement.clientWidth : document.body.clientWidth;
		var tempLayerObj = GL_getObj("contentMenu"+level);
		var tempLayerObjCss = GL_getObjCss("contentMenu"+level);

		if (tempLayerObj && (level==0 || GLV_isVisible[level-1]))	{
			tempLayerObj.el.innerHTML = html;
			tempLayerObj.width= (bw.ie4||bw.dom)?this.el.offsetWidth:bw.ns4?this.ref.width:0;
			tempLayerObj.height= (bw.ie4||bw.dom)?this.el.offsetHeight:bw.ns4?this.ref.height:0;

				// konqueror (3.2.2) workaround
			winHeight = (bw.konq)?window.innerHeight:winHeight;
			winWidth = (bw.konq)?window.innerWidth:winWidth;

				// Adjusting the Y-height of the layer to fit it into the window frame if it goes under the window frame in the bottom:
			if (winHeight-tempLayerObj.height < GLV_yRel)	{
				if (GLV_yRel < tempLayerObj.height) {
					GLV_y+= (winHeight-tempLayerObj.height-GLV_yRel); 		// Setting it so bottom is just above window height.
				} else {
					GLV_y-= tempLayerObj.height-8; 		// Showing the menu upwards
				}
			}
				// Adjusting the X position like Y above
			if (winWidth-tempLayerObj.width < GLV_xRel)	{
				if (GLV_xRel < tempLayerObj.width) {
					GLV_x+= (winWidth-tempLayerObj.width-GLV_xRel);
				} else {
					GLV_x-= tempLayerObj.width-8;
				}
			}
			GLV_x = Math.max(GLV_x,1);
			GLV_y = Math.max(GLV_y,1);

			GLV_curLayerX[level] = GLV_x;
			GLV_curLayerY[level] = GLV_y;
			tempLayerObjCss.left = GLV_x+"px";
			tempLayerObjCss.top = GLV_y+"px";
			tempLayerObjCss.visibility = "visible";
			if (bw.ie5)	showHideSelectorBoxes("hidden");

			GLV_isVisible[level]=1;
			GLV_curLayerWidth[level] = tempLayerObj.width;
			GLV_curLayerHeight[level] = tempLayerObj.height;
		}
	}
		// hideEmpty()
	function hideEmpty()	{	//
		hideSpecific(0);
		hideSpecific(1);
		return false;
	}
		// hideSpecific(level)
	function hideSpecific(level)	{	//
		GL_getObjCss("contentMenu"+level).visibility = "hidden";
		GL_getObj("contentMenu"+level).el.innerHTML = "";
		GLV_isVisible[level]=0;

		if (bw.ie5 && level==0)	showHideSelectorBoxes("visible");
	}
		// debugObj(obj,name)
	function debugObj(obj,name)	{	//
		var acc;
		for (i in obj) {if (obj[i])	{acc+=i+":  "+obj[i]+"\n";}}
		alert("Object: "+name+"\n\n"+acc);
	}
		// initLayer()
	function initLayer(){	//
		if (document.all)   {
			window.onmousemove=GL_getMouse;
		}
		layerObj = GL_getObj("contentMenu1");
		layerObjCss = GL_getObjCss("contentMenu1");
	}
	function showHideSelectorBoxes(action)	{	// This function by Michiel van Leening
		for (i=0;i<document.forms.length;i++) {
			for (j=0;j<document.forms[i].elements.length;j++) {
				if(document.forms[i].elements[j].type=="select-one") {
					document.forms[i].elements[j].style.visibility=action;
				}
			}
		}
	}

</script>';

/*<script type="text/javascript">
	function submit_editform(elementid,action){
		switch(elementid){
			'.$this->scriptText.'
		}
		document.getElementById(\'formField_actid\').value=elementid;
		document.getElementById(\'formField_act\').value=action;
		document.getElementById(\'editform\').submit();
	}
</script>';*/

		/* build the tree begin */
		$setupTree=t3lib_div::makeInstance('tx_realurlmanagement_setupbrowsetree');
		$setupTree->pObj=&$this->pObj;
		$setupTree->helpfunc=t3lib_div::makeInstance('tx_realurlmanagement_helpfunc');
		$setupTree->helpfunc->pObj=&$this;
		$setupTree->action=$this->action;
		$setupTree->actionId=$this->actionId;
		$setupTree->actionHash=$this->actionHash;
		$setupTree->actionLivePath=$this->actionLivePath;
		$setupTree->actionError=$this->actionError;

		/*$setupTree->expandAll=1;*/
		$setupTree->thisScript='index.php';
		$setupTree->title='RealUrl configuration';

		$setupTree->init();
		$setupTree->setDataFromArray($this->setupTreeArr);
		$setupTree->initializePositionSaving();

		$content.=$setupTree->getBrowsableTree();
		/* build the tree end */

		return $this->pObj->doc->section($LANG->getLL("setup_title"),$content,0,1);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$confArray: ...
	 * @param	[type]		$treeArray: ...
	 * @return	[type]		...
	 */
	function resolve_TYPO3_SITE_URL($confArray,&$treeArray){
		$domains=",";
		foreach($confArray as $key=>$val){
			$domains.=$key.",";
		};
		$elPos=1;
		$elCount=count($confArray);
		foreach($confArray as $key=>$val){
			if(is_string($val) && !is_array($val)){
				if(strpos($domains,",".$val.",")===false){
					$icon=$this->icons['domain_copy_notexist'];
					$icons_sub=$this->icons['domain_copy_subdomain_notexist'];
				}else{
					$icon=$this->icons['domain_copy'];
					$icons_sub=$this->icons['domain_copy_subdomain'];
				};
			}else{
				$icon=$this->icons['domain'];
			};

			$treeArray[$this->myId]=array(
				'title'=>$key,
				'id' => 'id'.$this->myId,
				'icon' => $icon,
				'TCA' => array(
					'help'=>'realurl--hostname--siteCfg',
					'type' => 'text',
					'size' => '30',
					'contextMenu'=>array(
						'edit'=>1,
						'newAfter'=>1,
						'newBefore'=>1,
						'newInside'=>0,
						'delete'=>1,
						'moveUp'=>1,
						'moveDown'=>1,
					),
					'hash' => md5('id'.$this->myId.'*'.$key.'*'.$icon.'*'.serialize(array($key)).$GLOBALS['TYPO3_CONF_VARS']['BE']['installToolPassword']),
					'pid' => '0',
					'livePath'=> array($key)
				)
			);
			if($key=='_DEFAULT'){
				$treeArray[$this->myId]['TCA']['help']='realurl--_default--siteCfg';
			};
			if($elPos==1){
				$treeArray[$this->myId]['TCA']['contextMenu']['moveUp']=0;
			};
			if($elPos==$elCount){
				$treeArray[$this->myId]['TCA']['contextMenu']['moveDown']=0;
			}
			$elPos++;
			/*$this->addContentMenuTable($treeArray[$this->myId]);*/
			$this->myId++;
			$treeArray[$this->myId-1]['_SUB_LEVEL'][$this->myId]=array(
				'title'=>$val,
				'id' => 'id'.$this->myId,
				'icon' => $icons_sub,
				'TCA' => array(
					'help'=>'test1',
					'type' => 'data',
					'dataPlus'=>'<option value="###___###newArray###___###">'.$GLOBALS['LANG']->getLL('selfDefinedHosts',1).'</option>',
					'path' => '',
					'contextMenu'=>array(
						'edit'=>1,
						'newAfter'=>0,
						'newBefore'=>0,
						'newInside'=>0,
						'delete'=>0,
						'moveUp'=>0,
						'moveDown'=>0,
					),
					'hash' => md5('id'.$this->myId.'*'.$val.'*'.$icons_sub.'*'.serialize(array($key,$val)).$GLOBALS['TYPO3_CONF_VARS']['BE']['installToolPassword']),
					'pid' => ($this->myId-1),
					'livePath'=> array($key,$val)
				)
			);
			if(is_array($val)){
				$treeArray[$this->myId-1]['_SUB_LEVEL'][$this->myId]['title']=$GLOBALS['LANG']->getLL('selfDefinedHosts',1);
				$treeArray[$this->myId-1]['_SUB_LEVEL'][$this->myId]['icon']=$icon;
				$treeArray[$this->myId-1]['_SUB_LEVEL'][$this->myId]['TCA']['livePath']=array($key,"###___###redefineArray###___###");
				$treeArray[$this->myId-1]['_SUB_LEVEL'][$this->myId]['TCA']['hash']=md5('id'.$this->myId.'*'.$GLOBALS['LANG']->getLL('selfDefinedHosts',1).'*'.$icon.'*'.serialize($treeArray[$this->myId-1]['_SUB_LEVEL'][$this->myId]['TCA']['livePath']).$GLOBALS['TYPO3_CONF_VARS']['BE']['installToolPassword']);
				$treeArray[$this->myId-1]['_SUB_LEVEL'][$this->myId]['TCA']['contextMenu']['newInside']=1;
				if(count($val)>0){
					$treeArray[$this->myId-1]['_SUB_LEVEL'][$this->myId]['TCA']['contextMenu']['edit']=0;
				};
				/*$this->addContentMenuTable($treeArray[$this->myId-1]['_SUB_LEVEL'][$this->myId]);*/
				$this->myId++;
				$this->resolve_TYPO3_SITE_URL_conf($val,$treeArray[$this->myId-2]['_SUB_LEVEL'],array($key));
			}else{
				/*$this->addContentMenuTable($treeArray[$this->myId-1]['_SUB_LEVEL'][$this->myId]);*/
			};
			$this->myId++;

		};


	}

	/*function addScriptText($row){
		$this->scriptText.="
			case('".$row['id']."'):{
				document.getElementById('formField_acthash').value='".$row['TCA']['hash']."';
				document.getElementById('formField_actlivepath').value='".$this->helpfunc->setArrayTo_GP($row['TCA']['livePath'])."';
				break;
			}
		";

	}*/



	function resolve_TYPO3_SITE_URL_conf($confArray,&$treeArray,$livePath){
		$allPossible=array('init'=>1,'redirects'=>1,'redirects_regex'=>1,'preVars'=>1,'pagePath'=>1,'fixedPostVars'=>1,'postVarSets'=>1,'fileName'=>1);
		foreach($confArray as $key=>$val){
			$allPossible[$key]=2;
		}
		$elPos=1;
		$setMyId=$this->myId-1;
		$elCount=count($confArray);
		$icon=$this->icons['defaultIcon'];
		$countPossible=1;
		foreach($confArray as $key=>$val){
			$tempLivePath=$livePath;
			$tempLivePath[]=$key;
			/* get all possible values for selectbox begin */
			$tempAllPossibleArray=$allPossible;
			$tempAllPossible='';
			if($tempAllPossibleArray[$key]==2){$tempAllPossibleArray[$key]=1;$countPossible--;}
			foreach($tempAllPossibleArray as $keyAllPossible=>$valAllPossible){
				if($valAllPossible==1){
					$countPossible++;
					$tempAllPossible.=$keyAllPossible.',';
				};
			};
			$tempAllPossible=trim($tempAllPossible,",");
			/* get all possible values for selectbox end */
			$treeArray[$setMyId]['_SUB_LEVEL'][$this->myId]=array(
				'title'=>$key,
				'id' => 'id'.$this->myId,
				'icon' => $icon,
				'TCA' => array(
					'type' => 'select',
					'values' => $tempAllPossible,
					'pid' => '',
					'contextMenu'=>array(
						'edit'=>1,
						'newAfter'=>1,
						'newBefore'=>1,
						'newInside'=>1,
						'delete'=>1,
						'moveUp'=>1,
						'moveDown'=>1,
					),
					'hash' => md5('id'.$this->myId.'*'.$key.'*'.$icon.'*'.serialize($tempLivePath).$GLOBALS['TYPO3_CONF_VARS']['BE']['installToolPassword']),
					'livePath'=> $tempLivePath
				),
			);
			if($countPossible==1){
				$treeArray[$setMyId]['_SUB_LEVEL'][$this->myId]['TCA']['contextMenu']['newBefore']=0;
				$treeArray[$setMyId]['_SUB_LEVEL'][$this->myId]['TCA']['contextMenu']['newAfter']=0;
				$treeArray[$setMyId]['TCA']['contextMenu']['newInside']=0;
			};
			if($elPos==1){
				$treeArray[$setMyId]['_SUB_LEVEL'][$this->myId]['TCA']['contextMenu']['moveUp']=0;
			};
			if($elCount==$elPos){
				$treeArray[$setMyId]['_SUB_LEVEL'][$this->myId]['TCA']['contextMenu']['moveDown']=0;
			};
			$elPos++;
			/*$this->addContentMenuTable($treeArray[$setMyId]['_SUB_LEVEL'][$this->myId]);*/
			$this->myId++;
			if($key=='init'){
				$this->resolve_init_conf($val,$treeArray[$setMyId]['_SUB_LEVEL'],$tempLivePath);
			};
		};
		//print_r($treeArray);

	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$confArray: ...
	 * @param	[type]		$treeArray: ...
	 * @param	[type]		$livePath: ...
	 * @return	[type]		...
	 */
	function resolve_init_conf($confArray,&$treeArray,$livePath){
		$icon=$this->icons['defaultIcon'];
		$allPossible=array('doNotRawUrlEncodeParameterNames'=>1,'enableCHashCache'=>1,'respectSimulateStaticURLs'=>1,'appendMissingSlash'=>1,'adminJumpToBackend'=>1,'postVarSet_failureMode'=>1,'disableErrorLog'=>1,'enableUrlDecodeCache'=>1,'enableUrlEncodeCache'=>1,'emptyUrlReturnValue'=>1,'rootPageID'=>1,'enableDomainLookup'=>1);
		foreach($confArray as $key=>$val){
			$allPossible[$key]=2;
		}
		$setMyId=$this->myId-1;
		$countPossible=1;
		$elPos=1;
		$elCount=count($confArray);
		foreach($confArray as $key=>$val){
			$tempLivePath=$livePath;
			$tempLivePath[]=$key;
			/* get all possible values for selectbox begin */
			$tempAllPossibleArray=$allPossible;
			$tempAllPossible='';
			if($tempAllPossibleArray[$key]==2){$tempAllPossibleArray[$key]=1; $countPossible--;}
			foreach($tempAllPossibleArray as $keyAllPossible=>$valAllPossible){
				if($valAllPossible==1){
					$tempAllPossible.=$keyAllPossible.',';
					$countPossible++;
				};
			};
			$tempAllPossible=trim($tempAllPossible,",");
			/* get all possible values for selectbox end */
			$treeArray[$setMyId]['_SUB_LEVEL'][$this->myId]=array(
				'title'=>$key,
				'id' => 'id'.$this->myId,
				'icon' => $icon,
				'TCA' => array(
					'type' => 'select',
					'values'=>$tempAllPossible,
					'contextMenu'=>array(
						'help'=>'test3',
						'edit'=>1,
						'newAfter'=>1,
						'newBefore'=>1,
						'newInside'=>0,
						'delete'=>1,
						'moveUp'=>1,
						'moveDown'=>1,
					),
					'hash' => md5('id'.$this->myId.'*'.$key.'*'.$icon.'*'.serialize($tempLivePath).$GLOBALS['TYPO3_CONF_VARS']['BE']['installToolPassword']),
					'pid' => '0',
					'livePath'=> $tempLivePath
				)
			);
			if($countPossible==1){
				$treeArray[$setMyId]['_SUB_LEVEL'][$this->myId]['TCA']['contextMenu']['newBefore']=0;
				$treeArray[$setMyId]['_SUB_LEVEL'][$this->myId]['TCA']['contextMenu']['newAfter']=0;
				$treeArray[$setMyId]['TCA']['contextMenu']['newInside']=0;
			};
			if($elPos==1){
				$treeArray[$setMyId]['_SUB_LEVEL'][$this->myId]['TCA']['contextMenu']['moveUp']=0;
			};
			if($elCount==$elPos){
				$treeArray[$setMyId]['_SUB_LEVEL'][$this->myId]['TCA']['contextMenu']['moveDown']=0;
			};
			$elPos++;
			/*$this->addContentMenuTable($treeArray[$setMyId]['_SUB_LEVEL'][$this->myId]);*/
			$tempLivePath[]=$val;
			$this->myId++;
			$treeArray[$setMyId]['_SUB_LEVEL'][$this->myId-1]['_SUB_LEVEL'][$this->myId]=array(
				'title'=>$val,
				'id' => 'id'.$this->myId,
				'icon' => $icon,
				'TCA' => array(
					'help'=>'help--init--'.$key,
					'type' => 'select',
					'values'=>'0,1',
					'contextMenu'=>array(
						'edit'=>1,
						'newAfter'=>0,
						'newBefore'=>0,
						'newInside'=>0,
						'delete'=>0,
						'moveUp'=>0,
						'moveDown'=>0,
					),
					'hash' => md5('id'.$this->myId.'*'.$val.'*'.$icon.'*'.serialize($tempLivePath).$GLOBALS['TYPO3_CONF_VARS']['BE']['installToolPassword']),
					'pid' => '0',
					'livePath'=> $tempLivePath
				)
			);
			if($key=='appendMissingSlash'){
				$treeArray[$setMyId]['_SUB_LEVEL'][$this->myId-1]['_SUB_LEVEL'][$this->myId]['TCA']['values']='0,1,ifNotFile';
			};
			if($key=='postVarSet_failureMode'){
				$treeArray[$setMyId]['_SUB_LEVEL'][$this->myId-1]['_SUB_LEVEL'][$this->myId]['TCA']['values']=',redirect_goodUpperDir,ignore';
			}
			if($key=='emptyUrlReturnValue'||$key=='rootPageID'){
				$treeArray[$setMyId]['_SUB_LEVEL'][$this->myId-1]['_SUB_LEVEL'][$this->myId]['TCA']['type']='text';
				$treeArray[$setMyId]['_SUB_LEVEL'][$this->myId-1]['_SUB_LEVEL'][$this->myId]['TCA']['size']='8';
			}
			if($key=='rootPageID'){
				$treeArray[$setMyId]['_SUB_LEVEL'][$this->myId-1]['_SUB_LEVEL'][$this->myId]['TCA']['eval']='integer';
			}

			$this->myId++;




		};


	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$array: ...
	 * @param	[type]		$livePath: ...
	 * @return	[type]		...
	 */
	function processSetupArray($array,$livePath){
		$tempArray=array();
		$livePath;
		$lpVal='';
		if(count($livePath)>0){
			$lpVal=$livePath[$this->walkId];
			unset($livePath[$this->walkId]);
		};
		$this->walkId++;

		$tempKeys=",";
		foreach($array as $key=>$val){
			$tempKeys.=$key.",";
		}
		$tempMove=array();
		foreach($array as $key=>$val){
			$tempKey=$key;
			if($key==$lpVal){$setLivePath=$livePath;}else{$setLivePath=array();};
			switch($this->action){
				/* editsave begin */
				case('editsave'):{
					if(($key==$lpVal) && (count($livePath)==0)&&($key!=$this->actValue)){
						if(strpos($tempKeys,$this->actValue)===false){
							$tempKey=$this->actValue;
						}else{
							$this->actionError='aaaaaaa';
							$this->action='edit';
						};
					};
					if(is_array($val)){
						if(($key==$lpVal)&&($livePath[$this->walkId]=='###___###redefineArray###___###')&&($this->actValue!='###___###newArray###___###')){
							$tempArray[$tempKey]=$this->actValue;
						}else{
							$tempArray[$tempKey]=$this->processSetupArray($val,$setLivePath);
						}
					}else{
						$tempVal=$val;
						if(($key==$lpVal) && (count($livePath)==1) &&($val==end($livePath))){
							$tempVal=$this->actValue;
							if($this->actValue=='###___###newArray###___###'){
								unset($tempVal);
								$tempVal=array();
							};
						};
						$tempArray[$tempKey]=$tempVal;
					};
					break;
				};
				/* editsave end */
				/* movebottom begin */
				case('moveDown'):{
					if(($key==$lpVal)&&(count($livePath)==0)){
						if(is_array($val)){
							$tempMove[$key]=$this->processSetupArray($val,$setLivePath);
						}else{
							$tempMove[$key]=$val;
						};
					}else{
						if(is_array($val)){
							$tempArray[$key]=$this->processSetupArray($val,$setLivePath);
						}else{
							$tempArray[$key]=$val;
						};
					};
					if(($key!=$lpVal)&&(count($tempMove)>0)){
						$getTempKey=key($tempMove);
						$tempArray[$getTempKey]=$tempMove[$getTempKey];
						unset($tempMove);
						$tempMove=array();
					};
					break;
				}
				/* movebottom end */
				/* movetop begin */
				case('moveUp'):{
					/* if we are on the record that should be pushed up begin */
					if(($key==$lpVal)&&(count($livePath)==0)){
						if(is_array($val)){
							$tempArray[$key]=$this->processSetupArray($val,$setLivePath);
						}else{
							$tempArray[$key]=$val;
						};
					};
					/* if we are on the record that should be pushed up end */
					/* put the previous obtained record begin */
					if(count($tempMove)>0){
						$getTempKey=key($tempMove);
						$tempArray[$getTempKey]=$tempMove[$getTempKey];
					};
					/* put the previous obtained record end */
					unset($tempMove);
					if(($key==$lpVal)&&(count($livePath)==0)){
						$tempMove=array();
					}else{
						if(is_array($val)){
							$tempMove[$key]=$this->processSetupArray($val,$setLivePath);
						}else{
							$tempMove[$key]=$val;
						};
					};
					break;
				}
				/* movetop end */
				/* delete begin */
				case('delete'):{
					if(($key==$lpVal)&&(count($livePath)==0)){
						//ak su rovnake tak nekopirovat
					}else{
						if(is_array($val)){
							$tempArray[$key]=$this->processSetupArray($val,$setLivePath);
						}else{
							$tempArray[$key]=$val;
						};
					};
					break;
				}
				/* delete end */



				default:{

				 	if(is_array($val)){
				 		$tempArray[$key]=$this->processSetupArray($val,$setLivePath);
				 	}else{
						$tempArray[$key]=$val;
					}
					break;
				}
			}
		};
		/* this must be for the moveTop action begin */
		if(count($tempMove)>0){
			$getTempKey=key($tempMove);
			$tempArray[$getTempKey]=$tempMove[$getTempKey];
		};
		/* end */
		return $tempArray;
	}


















	/**
	 * recursive function that store all contextmenus for the tree
	 *
	 * @param	array		array with tree
	 * @return	void
	 */
	function addContentMenuTable($array){
		foreach($array as $key=>$val){
			if(t3lib_div::testInt($key)){ $this->addContentMenuTableOnce($val);};
			if(is_array($val['TCA'])&&(is_array($val['_SUB_LEVEL']))){$this->addContentMenuTable($val['_SUB_LEVEL']);};
		};
	}

	/**
	 * intern function used by addContentMenuTable function
	 *
	 * @param	array		array with one treelement
	 * @return	void
	 */
	function addContentMenuTableOnce($row){
		$editHref="javascript:window.location.href='index.php?id=".$this->pObj->pageuid."&amp;act=edit&amp;actid=".$row['id']."&amp;acthash=".$row['TCA']['hash']."&actlivepath=".$this->helpfunc->setArrayTo_GP($row['TCA']['livePath'])."&actTstamp=".time()."'";
		$moveDownHref="javascript:window.location.href='index.php?id=".$this->pObj->pageuid."&amp;act=moveDown&amp;actid=".$row['id']."&amp;acthash=".$row['TCA']['hash']."&actlivepath=".$this->helpfunc->setArrayTo_GP($row['TCA']['livePath'])."&actTstamp=".time()."'";
		$moveUpHref="javascript:window.location.href='index.php?id=".$this->pObj->pageuid."&amp;act=moveUp&amp;actid=".$row['id']."&amp;acthash=".$row['TCA']['hash']."&actlivepath=".$this->helpfunc->setArrayTo_GP($row['TCA']['livePath'])."&actTstamp=".time()."'";
		$deleteHref="javascript:window.location.href='index.php?id=".$this->pObj->pageuid."&amp;act=delete&amp;actid=".$row['id']."&amp;acthash=".$row['TCA']['hash']."&actlivepath=".$this->helpfunc->setArrayTo_GP($row['TCA']['livePath'])."&actTstamp=".time()."'";
		$newBeforeHref="javascript:window.location.href='index.php?id=".$this->pObj->pageuid."&amp;act=newBefore&amp;actid=".$row['id']."&amp;acthash=".$row['TCA']['hash']."&actlivepath=".$this->helpfunc->setArrayTo_GP($row['TCA']['livePath'])."&actTstamp=".time()."'";
		$newAfterHref="javascript:window.location.href='index.php?id=".$this->pObj->pageuid."&amp;act=newAfter&amp;actid=".$row['id']."&amp;acthash=".$row['TCA']['hash']."&actlivepath=".$this->helpfunc->setArrayTo_GP($row['TCA']['livePath'])."&actTstamp=".time()."'";
		$newInsideHref="javascript:window.location.href='index.php?id=".$this->pObj->pageuid."&amp;act=newInside&amp;actid=".$row['id']."&amp;acthash=".$row['TCA']['hash']."&actlivepath=".$this->helpfunc->setArrayTo_GP($row['TCA']['livePath'])."&actTstamp=".time()."'";

		$this->contentMenuReturn.='<div id="contentMenuTable_'.$row['id'].'" style="display:none;">';
		$this->contentMenuReturn.=$this->contentMenuHtml['tableBegin'];

		$insertLine=0;

		if($row['TCA']['contextMenu']['edit']==1){
			$this->contentMenuReturn.='<tr class="typo3-CSM-itemRow" onclick="'.htmlspecialchars($editHref).'" onmouseover="this.bgColor=\''.$GLOBALS['TBE_TEMPLATE']->bgColor5.'\';" onmouseout="this.bgColor=\'\';" oncontextmenu="javascript:return false;">'.
			$this->contentMenuHtml['edit'].$this->contentMenuHtml['tempTREnd'];
			$insertLine=1;
		};

		if($insertLine==1 && ($row['TCA']['contextMenu']['newBefore']==1 || $row['TCA']['contextMenu']['newAfter']==1 || $row['TCA']['contextMenu']['newInside']==1)){
			$this->contentMenuReturn.=$this->contentMenuHtml['tableHr'];
		}

		if($row['TCA']['contextMenu']['newBefore']==1){
			$this->contentMenuReturn.='<tr class="typo3-CSM-itemRow" onclick="'.htmlspecialchars($newBeforeHref).'" onmouseover="this.bgColor=\''.$GLOBALS['TBE_TEMPLATE']->bgColor5.'\';" onmouseout="this.bgColor=\'\';" oncontextmenu="javascript:return false;">'.
			$this->contentMenuHtml['newBefore'].$this->contentMenuHtml['tempTREnd'];
			$insertLine=1;
		};
		if($row['TCA']['contextMenu']['newAfter']==1){
			$this->contentMenuReturn.='<tr class="typo3-CSM-itemRow" onclick="'.htmlspecialchars($newAfterHref).'" onmouseover="this.bgColor=\''.$GLOBALS['TBE_TEMPLATE']->bgColor5.'\';" onmouseout="this.bgColor=\'\';" oncontextmenu="javascript:return false;">'.
			$this->contentMenuHtml['newAfter'].$this->contentMenuHtml['tempTREnd'];
			$insertLine=1;
		};
		if($row['TCA']['contextMenu']['newInside']==1){
			$this->contentMenuReturn.='<tr class="typo3-CSM-itemRow" onclick="'.htmlspecialchars($newInsideHref).'" onmouseover="this.bgColor=\''.$GLOBALS['TBE_TEMPLATE']->bgColor5.'\';" onmouseout="this.bgColor=\'\';" oncontextmenu="javascript:return false;">'.
			$this->contentMenuHtml['newInside'].$this->contentMenuHtml['tempTREnd'];
			$insertLine=1;
		};

		if($insertLine==1 && $row['TCA']['contextMenu']['delete']==1){
			$this->contentMenuReturn.=$this->contentMenuHtml['tableHr'];
		}

		if($row['TCA']['contextMenu']['delete']==1){
			$this->contentMenuReturn.='<tr class="typo3-CSM-itemRow" onclick="'.htmlspecialchars($deleteHref).'" onmouseover="this.bgColor=\''.$GLOBALS['TBE_TEMPLATE']->bgColor5.'\';" onmouseout="this.bgColor=\'\';" oncontextmenu="javascript:return false;">'.
			$this->contentMenuHtml['delete'].$this->contentMenuHtml['tempTREnd'];
			$insertLine=1;
		};

		if($insertLine==1 && ($row['TCA']['contextMenu']['moveUp']==1 || $row['TCA']['contextMenu']['moveDown']==1)){
			$this->contentMenuReturn.=$this->contentMenuHtml['tableHr'];
		}

		if($row['TCA']['contextMenu']['moveUp']==1){
			$this->contentMenuReturn.='<tr class="typo3-CSM-itemRow" onclick="'.htmlspecialchars($moveUpHref).'" onmouseover="this.bgColor=\''.$GLOBALS['TBE_TEMPLATE']->bgColor5.'\';" onmouseout="this.bgColor=\'\';" oncontextmenu="javascript:return false;">'.
			$this->contentMenuHtml['moveTop'].$this->contentMenuHtml['tempTREnd'];
		};

		if($row['TCA']['contextMenu']['moveDown']==1){
			$this->contentMenuReturn.='<tr class="typo3-CSM-itemRow" onclick="'.htmlspecialchars($moveDownHref).'" onmouseover="this.bgColor=\''.$GLOBALS['TBE_TEMPLATE']->bgColor5.'\';" onmouseout="this.bgColor=\'\';" oncontextmenu="javascript:return false;">'.
			$this->contentMenuHtml['moveBottom'].$this->contentMenuHtml['tempTREnd'];
		};

		$this->contentMenuReturn.=$this->contentMenuHtml['tableEnd'].'</div>';
	}








}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_setup.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realurlmanagement/mod1/tx_realurlmanagement_setup.php']);
}

?>