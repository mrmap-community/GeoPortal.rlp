<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 - 2008 Stefan Galinski (stefan.galinski@gmail.com)
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
 * JSCalendar Widget
 *
 * @author Stefan Galinski <stefan.galinski@gmail.com>
 */
class JSCalendar
{
	/** array date2cal configuration */
	var $extConfig = array();

	/** array calendar configuration */
	var $config = array();

	/** bool prevents the object to generate the initialization javascript twice */
	var $jsSent = false;

	/** object holds the language object */
	var $lang = null;

	/**
	 * Creates a singleton instance of JSCalendar. Its important to use only this funcion than
	 * a direct initialization of the class! You can use this method via a static call.
	 *
	 * @return object instance of JSCalendar
	 */
	function &getInstance() {
		static $instance;
		if (!isset($instance))
			$instance = new JSCalendar();
		return $instance;
	}

	/**
	 * Constructor
	 *
	 * @todo the natural language parser should be rewritten for i18n, time support and readability
	 * @todo the problem with nlp and irre needs to be fixed
	 *
	 * @return void
	 */
	function JSCalendar() {
		// add some paths
		$this->config['backPath'] = $GLOBALS['BACK_PATH'] . (TYPO3_MODE == 'BE' ? '../' : '');
		$this->config['relPath'] = $this->config['backPath'] . t3lib_extMgm::siteRelPath('date2cal');
		$this->config['absPath'] = t3lib_extMgm::extPath('date2cal');

		// set variable with the language object
		$this->lang = TYPO3_MODE == 'FE' ? $GLOBALS['TSFE'] : $GLOBALS['LANG'];

		// read global date2cal configuration
		$this->extConfig = $this->readGlobalConfig();

		// add some configuration
		$this->setNLP($this->extConfig['natLangParser']);
		$this->setCSS($this->extConfig['calendarCSS']);
		$this->setLanguage($this->extConfig['lang']);
		$this->setDateFormat();
		$this->setConfigOption('helpPage',  $this->config['relPath'] . 'res/helpPage.html');
		$this->setConfigOption('firstDay', $this->extConfig['firstDay'], true);
	}

	/**
	 * Reads and prepareas the global date2cal configuration.
	 *
	 * @return array global date2cal configuration
	 */
	function readGlobalConfig() {
		// unserialize configuration
		$extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['date2cal']);

		// get calendar image
		$extConfig['calImg'] = t3lib_div::getFileAbsFileName($extConfig['calImg']);
		$extConfig['calImg'] = $this->config['backPath'] .
			substr($extConfig['calImg'], strlen(PATH_site));

		// get help image
		$extConfig['helpImg'] = t3lib_div::getFileAbsFileName($extConfig['helpImg']);
		$extConfig['helpImg'] = $this->config['backPath'] .
			substr($extConfig['helpImg'], strlen(PATH_site));

		// user/group settings
		$userProps = t3lib_BEfunc::getModTSconfig($this->pageinfo['uid'], 'tx_date2cal');
		if (!is_array($userProps))
			$extConfig = array_merge($extConfig, $userProps['properties']);

		return $extConfig;
	}

	/**
	 * Renders an whole calendar input element. The name and id of the checkbox will be
	 * 	prefixed with _cb. The id of the input field will be prefixed with _hr. If you haven't defined
	 * the input field then it will be named "date". Please define the input field if you have
	 * multiple calendars on the page.
	 *
	 * @see renderImage()
	 * @param string $value default value of the input field element
	 * @param string $name name (default is inputField coniguration setting) of the input and checkbox field (prefixed with _cb)
	 * @param string $calImg calendar image (optional)
	 * @param string $helpImg help image (optional)
	 * @return string generated calendar images
	 */
	function render($value, $name = '', $calImg = '', $helpImg = '') {
		// generates the input field id/name if it not exists
		if (!isset($this->config['inputField']))
			$this->setInputField('date');

		// render input field
		$name = $name == '' ? $this->config['inputField'] : $name;
		$content = '<input type="checkbox" name="' . $name . '_cb" class="jscalendar_cb" ' .
			'id ="' . $this->config['inputField'] . '_cb" ' .
			'onclick="date2cal_setDatetime(\'' . $this->config['inputField'] . '_hr\', ' .
			strftime($this->config['calConfig']['ifFormat']) . ');" /> ';
		$size = $this->config['calConfig']['showsTime'] ? 16 : 10;
		$content .= '<input type="text" size="' . $size . '" maxlength="' . $size . '" ' .
			'name="' . $name . '" id="' .  $this->config['inputField'] . '_hr" class="jscalendar" ' .
			'onchange="date2cal_activeDateField(\'' . $this->config['inputField'] . '_cb\', \'' .
			$this->config['inputField'] . '_hr\');" value="' . $value . '" />';

		// render images
		$content .= $this->renderImages($calImg, $helpImg);

		return $content;
	}

	/**
	 * Renders the image buttons of the calendar (= trigger) and the help button. Both
	 * images values can fallback to the default ones if you don't want to define them.
	 *
	 * @param string $calImg calendar image (optional)
	 * @param string $helpImg help image (optional)
	 * @return string generated calendar images
	 */
	function renderImages($calImg = '', $helpImg = '') {
		// check images
		$calImg = ($calImg == '') ? $this->extConfig['calImg'] : $calImg;
		$helpImg = ($helpImg == '') ? $this->extConfig['helpImg'] : $helpImg;

		// vertical alignment
		$valign = TYPO3_MODE == 'FE' ? 'vertical-align: middle;' : '';

		// alt/title language labels for the images
		$calImgTitle = $this->lang->sL('LLL:EXT:date2cal/locallang.xml:calendar_wizard');
		$helpImgTitle = $this->lang->sL('LLL:EXT:date2cal/locallang.xml:help');

		// calendar trigger image
		$content .= ' <img class="date2cal_img_cal absMiddle" src="' . $calImg . '" ' .
			'id="' . $this->config['inputField'] . '_trigger" style="cursor: pointer; ' . $valign . '" ' .
			'title="' . $calImgTitle . '" alt="' . $calImgTitle . '" />' . "\n";

		// natural language parse help image
		if ($this->config['natLangParser']) {
			$content .= '<img class="date2cal_img_help absMiddle" src="' . $helpImg . '" ' .
				'id="' . $this->config['inputField'] . '_help" style="cursor: pointer; ' . $valign . '" ' .
				'title="' . $helpImgTitle . '" alt="' . $helpImgTitle . '" />' . "\n";
		}

		// calendar javascript configuration
		$content .= $this->getConfigJS();

		return $content;
	}

	/**
	 * Sets a config option of the calendar.
	 *
	 * Official documentation of the JSCalendar options:
	 * http://www.dynarch.com/demos/jscalendar/doc/html/reference.html#node_sec_2.3
	 *
	 * @param string $option name of the option
	 * @param string $value value of the option
	 * @param bool $nonString set this option if you want to set a boolean or integer
	 * @return void
	 */
	function setConfigOption($option, $value, $nonString=false) {
		$this->config['calConfig'][$option] = !$nonString ? '\'' . $value . '\'' : $value;
	}

	/**
	 * Sets the input field of the calendar. You doesn't need to set an input id if you want an
	 * automatic generation of the input field via the render function. Please don't try this for
	 * multiple instances on the same page!
	 *
	 * @param string $field special input field (will be prefixed with _hr)
	 * @return void
	 */
	function setInputField($field) {
		$this->config['inputField'] = $field;
		$this->setConfigOption('inputField', $field . '_hr');
		$this->setConfigOption('button', $field . '_trigger');
	}

	/**
	 * Returns the input field.
	 *
	 * @return string input field
	 */
	function getInputField() {
		return $this->config['inputField'];
	}

	/**
	 * Sets the language of the calendar. Includes availability checks and fallback modes
	 * for frontend and backend.
	 *
	 * @param string $lang language (let it empty for automatic detection)
	 * @return void
	 */
	function setLanguage($lang='') {
		// language detection
		if ($lang == '') {
			if (TYPO3_MODE == 'FE')
				$lang = $GLOBALS['TSFE']->config['config']['language'];
			else
				$lang = $GLOBALS['LANG']->lang;
		}

		// check availability of selected languages
		$this->config['lang'] = $this->languageCheck($lang);
	}

	/**
	 * Sets the calendar css. Includes an availability check with a fallback to the aqua theme.
	 *
	 * @param string $calendarCSS calendar css file (default: aqua)
	 * @return void
	 */
	function setCSS($calendarCSS = 'aqua') {
		$this->config['calendarCSS'] = $calendarCSS;
		if (!is_file($this->config['absPath'] . 'js/jscalendar/skins/' . $calendarCSS . '/theme.css'))
			$this->config['calendarCSS'] = 'aqua';
	}

	/**
	 * Sets the natural language parser mode. Additionaly it checks the TYPO3 version,
	 * because the feature can only be used with TYPO3 >= 4.1.
	 *
	 * @param bool $mode calendar with natural language parser mode (true)
	 * @return void
	 */
	function setNLP($mode) {
		$this->config['natLangParser'] = true;
		if (!$mode || t3lib_div::int_from_ver(TYPO3_version) < 4001000)
			$this->config['natLangParser'] = false;
	}

	/**
	 * Sets the date format of the calendar. You can't influence the format at the moment.
	 * It will be automatically created to the value "%d-%m-%Y" or "%m-%d-%y" with the US
	 * setting of TYPO3.
	 *
	 * @param bool $time set this option if you want to define the time
	 * @return void
	 */
	function setDateFormat($time=false) {
		$jsDate = $GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'] ?
			'%m-%d-%Y' : '%d-%m-%Y';
		$jsDate = ($time ? '%H:%M ' : '') . $jsDate;

		$value = $time ? 'true' : 'false';
		$this->setConfigOption('showsTime', $value, true);
		$this->setConfigOption('time24', $value, true);
		$this->setConfigOption('ifFormat', $jsDate);
	}

	/**
	 * Returns the javascript configuration code for a single calendar instance.
	 *
	 * @return string javascript code
	 */
	function getConfigJS()
	{
		// generates the calendar configuration string
		$tmp = array();
		foreach($this->config['calConfig'] as $label => $value)
			$tmp[] = $label . ': ' . $value;
		$config = implode(",\n", $tmp);

		// generates the javascript code for a single instance
		if ($this->config['natLangParser']) {
			$js = '
				<script type="text/javascript">
					new DatetimeToolbocks ({
						format: ' . $this->config['calConfig']['ifFormat'] . ',
						inputName: \'' . $this->config['inputField'] . '\',
						elementId: \'' . $this->config['inputField'] . '\',
						calendarOptions: {
							' . $config . '
						}
					});
				</script>';
		} else {
			$js = '
				<script type="text/javascript">
					Calendar.setup ({
						' . $config . '
					});
				</script>';
		}

		return $js;
	}

	/**
	 * Returns the shared javascript code for all calendar instances. The function can only be
	 * called once!
	 *
	 * @return string javascript code
	 */
	function getMainJS()
	{
		// can only be called once
		if ($this->jsSent)
			return '';
		$this->jsSent = true;

		// jscalendar inclusion (javascript, languages and css)
		$relPath = $this->config['relPath'] . 'js/';
		$js = '<!-- inclusion of JSCalendar -->
			<script type="text/javascript" src="' . $relPath . 'jscalendar/calendar.js"></script>
			<script type="text/javascript" src="' . $relPath .
				'jscalendar/lang/calendar-en.js"></script>' . "\n";
		if ($this->config['lang'] != 'en')
			$js .= '<script type="text/javascript" src="' . $relPath . 'jscalendar/lang/calendar-' .
				$this->config['lang'] . '.js"></script>' . "\n";
		$js .= '<script type="text/javascript" src="' . $relPath .
				'jscalendar/calendar-setup.js"></script>
			<link rel="stylesheet" type="text/css" href="' . $relPath . 'jscalendar/skins/' .
				$this->config['calendarCSS'] . '/theme.css" />
			<script type="text/javascript" src="' . $relPath . 'date2cal.js"></script>' . "\n";

		// natural language parser scripts
		if ($this->config['natLangParser']) {
			$js .= '<!-- inclusion of datetime_toolbocks.js -->
				<script type="text/javascript" src="' . $this->config['backPath'] .
					'typo3/contrib/prototype/prototype.js"></script>
				<script type="text/javascript" src="' . $relPath .
					'naturalLanguageParser.js"></script>' . "\n";
		}

		return $js;
	}

	/**
	 * Checks the availability of a language file. The functions attends utf8
	 * 	compatibility in the backend.
	 *
	 * Note that the language code would be transformed into an iso code if possible. If no
	 * translation file matches than the function returns english as fallback.
	 *
	 * @param string $lang language code
	 * @return string language (appended with -utf8, fallback or same as input)
	 */
	function languageCheck($lang)
	{
		// convert language into an iso code
		if (array_key_exists($lang, $this->lang->csConvObj->isoArray))
			$lang = $this->lang->csConvObj->isoArray[$lang];

		// check availability of utf8 encoding
		$absPath = $this->config['absPath'] . 'js/';
		if ($GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] == 'utf-8' &&
			is_file($absPath . 'jscalendar/lang/calendar-' . $lang . '-utf8.js'))
			return $lang . '-utf8';

		// check availability of iso encoding
		if (!is_file($absPath . 'jscalendar/lang/calendar-' . $lang . '.js'))
			return 'en';

		return $lang;
	}
}
?>
