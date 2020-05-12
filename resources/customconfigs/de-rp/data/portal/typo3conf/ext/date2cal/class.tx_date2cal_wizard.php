<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Stefan Galinski (stefan.galinski@frm2.tum.de)
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

/** jscalendar class */
require_once('class.jscalendar.php');

/**
 * Calendar Integration Wizard Class
 *
 * $Id$
 *
 * @author Stefan Galinski <stefan.galinski@frm2.tum.de>
 */
class tx_date2cal_wizard
{
	/** @var object holds the javascript object */
	var $jsObj = null;

	/** @var boolean type of jsObj (true = tceforms; false = doc) */
	var $jsObjType = true;

	/** @var array contains extension configuration */
	var $extConfig = array();

	/** @var boolean TYPO3 4.1 detection flag */
	var $typo41 = false;

	/**
	 * renders date/datetime fields
	 *
	 * @param array TCA informations about date/datetime field
	 * @param object TCE forms object (not needed)
	 * @return empty string
	 */
	function renderWizard($params, &$pObj)
	{
		// get and prepare extension configuration
		$this->extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['date2cal']);

		$this->extConfig['calendarImg'] = t3lib_div::getFileAbsFileName($this->extConfig['calendarImg']);
		if(!is_file($this->extConfig['calendarImg']))
			$this->extConfig['calendarImg'] =
				t3lib_div::getFileAbsFileName('EXT:date2cal/res/calendar.png');
		$this->extConfig['calendarImg'] = $GLOBALS['BACK_PATH'] . '../' .
			str_replace(PATH_site, '', $this->extConfig['calendarImg']);

		if(!is_file($this->extConfig['helpImg']))
			$this->extConfig['helpImg'] =
				t3lib_div::getFileAbsFileName('EXT:date2cal/res/helpIcon.gif');
		$this->extConfig['helpImg'] = $GLOBALS['BACK_PATH'] . '../' .
			str_replace(PATH_site, '', $this->extConfig['helpImg']);

		if(t3lib_div::int_from_ver(TYPO3_version) >= 4001000 && $this->extConfig['natLangParser'])
			$this->typo41 = true;

		// enabling of secondary options
		$groupOrUserProps = t3lib_BEfunc::getModTSconfig('', 'tx_date2cal');
		if($groupOrUserProps['properties']['secOptionsAlwaysOn'])
			$GLOBALS['BE_USER']->pushModuleData('xMOD_alt_doc.php', array('showPalettes' => 1));

		// get correct object
		$script = substr(PATH_thisScript, strrpos(PATH_thisScript, '/') + 1);
		if(t3lib_div::int_from_ver(TYPO3_version) >= 4000000 || $script == 'db_layout.php') {
			$this->jsObj = &$GLOBALS['SOBE']->doc; // common for typo3 4.x and quick edit
			$this->jsObjType = false;
		}
		elseif(is_object($GLOBALS['SOBE']->tceforms))
			$this->jsObj = &$GLOBALS['SOBE']->tceforms; // common for typo3 3.x
		else
			$this->jsObj = $pObj; // palette (doesnt work with php4)

		// add id attributes
		$checkboxId = 'data[' . $params['table'] . '][' . $params['uid'] . '][' . $params['field'] . ']_cb';
		$inputId = $params['itemName'];

		$params['item'] = str_replace('<input type="checkbox"', '<input type="checkbox" ' .
			'id="' . $checkboxId . '"', $params['item']);
		$params['item'] = str_replace('<input type="text"', '<input type="text" ' .
			'id="' . $inputId . '"', $params['item']);

		// format definition
		if(!$this->typo41) {
			if(t3lib_div::inList($params['wConf']['evalValue'], 'datetime')) {
				$jsDate = '%H:%M %d-%m-%Y';
				if($GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'])
					$jsDate = '%H:%M %m-%d-%Y';
			} else {
				$jsDate = '%d-%m-%Y';
				if($GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'])
					$jsDate = '%m-%d-%Y';
			}
		}
		else {
			$format = 'typo3';
			if($GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'])
				$format = 'typo3US';
		}

		// build "jscalendar with datetime_toolbocks ext" options
		$options = array(
			'inputField' => '\'' . $inputId . '\'',
			'checkboxField' => '\'' . $checkboxId . '\'',
			'ifFormat' => '\'' . $jsDate . '\'',
			'button' => '\'' . $inputId . '_trigger\'',
			'helpPage' => '\'' . $GLOBALS['BACK_PATH'] . t3lib_extMgm::extRelPath('date2cal') . 'res/helpPage.html\'',
			'format' => '\'' . $format . '\''
		);
		if(t3lib_div::inList($params['wConf']['evalValue'], 'datetime')) {
			$options['showsTime'] = true;
			$options['time24'] = true;
		}

		// prefered language
		$lang = '';
		$groupOrUserProps = t3lib_BEfunc::getModTSconfig($this->pageinfo['uid'], 'tx_date2cal');
		if(!empty($groupOrUserProps['properties']['prefLang']))
			$lang = $groupOrUserProps['properties']['prefLang'];

		// init jscalendar class
		$jscalendar = new jscalendar($options, $this->extConfig['calendarCSS'], $lang, '', $this->typo41);

		// image title labels
		$calImgTitle = $GLOBALS['LANG']->sL('LLL:EXT:date2cal/locallang.xml:calendar_wizard');
		$helpImgTitle = $GLOBALS['LANG']->sL('LLL:EXT:date2cal/locallang.xml:help');

		// generate calendar code
		$params['item'] .= '<img class="calendarImg" src="' . $this->extConfig['calendarImg'] . '" ' .
			'id="' . $inputId . '_trigger" style="cursor: pointer;" ' .
			'title="' . $calImgTitle . '" alt="' . $calImgTitle . '" />' . "\n";
		if($this->typo41)
			$params['item'] .= '<img class="helpImg" src="' . $this->extConfig['helpImg'] . '" ' .
				'id="' . $inputId . '_help" style="cursor: pointer;" ' .
				'title="' . $helpImgTitle . '" alt="' . $helpImgTitle . '" />' . "\n";
		$params['item'] .= $jscalendar->getItemJS();	

		// initialisation code of jscalendar
		$tmp = $jscalendar->getJS();
		if(empty($tmp))
			return '';

		if(!$this->jsObjType)
			$this->jsObj->JScode .= $tmp;
		else
			$this->jsObj->additionalCode_pre['date2cal'] = $tmp;

		return '';
	}
}
?>
