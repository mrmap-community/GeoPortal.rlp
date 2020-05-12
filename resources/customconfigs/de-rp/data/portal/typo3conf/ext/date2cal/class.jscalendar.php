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

/** @var boolean initialisation flag to prevent multiple init code */
$JSCalendarInitFlag = false;

/**
 * JSCalendar Initialisation Class
 *
 * $Id$
 *
 * @author Stefan Galinski <stefan.galinski@frm2.tum.de>
 */
class jscalendar
{
	/** @var array configuration */
	var $config = array();

	/** @var boolean set to true if you want the datetime_toolbocks ext loaded (TYPO3 4.1 required) */
	var $datetimeToolbocks = false;

	/**
	 * Constructor
	 *
	 * Structure of item configuration (attend the slashes!)
	 * e.g. $itemConfig['inputField'] = '\'bla\'';
	 * e.g. $itemConfig['time24'] = true;
	 *
	 * @param array item configuration ... see the official jscalendar documentation
	 * @param string calendar css (e.g. aqua/theme.css [default], calendar-win2k-cold-1.css, ...)
	 * @param string default language code if you dont want automatic language detection
	 * @param string alternative language code
	 * @param boolean set to true to enable natural language parsing
	 * @return void
	 */
	function jscalendar($itemConfig, $calendarCSS, $lang='', $altLang='', $datetimeToolbocks = true)
	{
		// item configuration
		$this->config['itemConfig'] = (is_array($itemConfig)) ? $itemConfig: array();
		$this->datetimeToolbocks = $datetimeToolbocks;

		// transform language
		if(empty($lang)) {
			if(TYPO3_MODE == 'FE') {
				$lang = $GLOBALS['TSFE']->config['config']['language'];
				if(empty($altLang))
	 				$altLang = $GLOBALS['TSFE']->config['config']['language_alt'];
			} else
				$lang = $GLOBALS['LANG']->lang;
		}
		if(!$this->languageCheck($lang)) {
			$this->languageCheck($altLang);
			$lang = $altLang;
		}
		$this->config['lang'] = $lang;

		// css file check
		$this->config['calendarCSS'] = $calendarCSS;
		if(!is_file(PATH_site . t3lib_extMgm::siteRelPath('date2cal') .
			'jscalendar/skins/' . $calendarCSS))
			$this->config['calendarCSS'] = 'aqua/theme.css';
	}

	/**
	 * generates and returns the needed javascript of a calendar object
	 *
	 * @return string javascript code
	 */
	function getItemJS()
	{
		$tmp = array();
		foreach($this->config['itemConfig'] as $label=>$value) {
			if($label == 'format')
				continue;

			$tmp[] = $label . ': ' . $value;
		}

		if($this->datetimeToolbocks) {
			$js = '
				<script type="text/javascript">
				new DatetimeToolbocks({ 
					format: ' . (empty($this->config['itemConfig']['format']) ? '"dd-mm-yyyy"' : $this->config['itemConfig']['format']) . ',
					elementId: ' . $this->config['itemConfig']['inputField'] . ',
					inputName: ' . $this->config['itemConfig']['inputField'] . ',
					calendarOptions: {
						' . implode(",\n", $tmp) . '
					}
				});
				</script>';
		} else {
			$js = '
				<script type="text/javascript">
				Calendar.setup({
					' . implode(",\n", $tmp) . '
				});
				</script>';
		}

		return $js;
	}

	/**
	 * generates and returns the needed main javascript inclusion code
	 *
	 * Note: this function can only be called one time
	 *
	 * @return string generated javascript inclusion code
	 */
	function getJS()
	{
		// multiple init check
		if($GLOBALS['JSCalendarInitFlag'])
			return '';

		$GLOBALS['JSCalendarInitFlag'] = true;

		// additional language (non english)
		$adLang = '';
		if($this->config['lang'] != 'en')
			$adLang = '<script type="text/javascript" src="' . $GLOBALS['BACK_PATH'] .
				t3lib_extMgm::extRelPath('date2cal') . 'jscalendar/lang/calendar-' .
				$this->config['lang'] . '.js"></script>';

		// build inclusion code
		$js = '
			<!-- inclusion of JSCalendar -->
			<script type="text/javascript" src="' . $GLOBALS['BACK_PATH'] .
				t3lib_extMgm::extRelPath('date2cal') . 'jscalendar/calendar.js"></script>
			<script type="text/javascript" src="' . $GLOBALS['BACK_PATH'] .
				t3lib_extMgm::extRelPath('date2cal') . 'jscalendar/lang/calendar-en.js"></script> ' .
				$adLang . '
			<script type="text/javascript" src="' . $GLOBALS['BACK_PATH'] .
				t3lib_extMgm::extRelPath('date2cal') . 'jscalendar/calendar-setup.js"></script>
			<link rel="stylesheet" type="text/css" href="' . $GLOBALS['BACK_PATH'] .
				t3lib_extMgm::extRelPath('date2cal') . 'jscalendar/skins/' .
				$this->config['calendarCSS'] .'" />';

		if($this->datetimeToolbocks) {
			$js .= '
				<!-- inclusion of datetime_toolbocks.js -->
				<script type="text/javascript" src="' . $GLOBALS['BACK_PATH'] .
					'contrib/prototype/prototype.js"></script>
				<script type="text/javascript" src="' . $GLOBALS['BACK_PATH'] .
					t3lib_extMgm::extRelPath('date2cal') . 'res/datetime_toolbocks.js"></script>';
		}

		return $js;
    }

	/**
	 * checks if jscalendar contains an utf8 or normal translation in the given language
	 *
	 * Note that the language code would be transformed into an iso code if possible. If no
	 * translation file matches the language code than the language variable gets "en"
	 * as default value.
	 *
	 * @param string language code (reference)
	 * @return boolean false if no file was found; otherwise true
	 */
	function languageCheck(&$lang)
	{
		// convert language into an iso code
		if(array_key_exists($lang, $GLOBALS['LANG']->csConvObj->isoArray))
			$lang = $GLOBALS['LANG']->csConvObj->isoArray[$lang];

		// check existence of file and utf8 compatibility
		if(is_file(PATH_site . t3lib_extMgm::siteRelPath('date2cal') .
			'jscalendar/lang/calendar-' . $lang . '-utf8.js') &&
			$GLOBALS['TYPO3_CONF_VARS']['BE']['forceCharset'] == 'utf-8')
			$lang .= '-utf8';
		elseif(!is_file(PATH_site . t3lib_extMgm::siteRelPath('date2cal') .
			'jscalendar/lang/calendar-' . $lang . '.js')) {
			$lang = 'en';
			return false;
		}

		return true;
	}
}
?>
