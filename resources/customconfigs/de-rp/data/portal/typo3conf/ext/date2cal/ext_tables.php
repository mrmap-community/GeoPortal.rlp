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

if (!defined('TYPO3_MODE'))
	die('Access denied.');

if (!class_exists('tx_date2cal_extTables'))
{

require_once(t3lib_extMgm::extPath('date2cal') . 'src/class.tx_date2cal_shared.php');

/**
 * Initialization class for tx_date2cal
 *
 * @author Stefan Galinski <stefan.galinski@gmail.com>
 */
class tx_date2cal_extTables
{
	/** array holds configuration */
	var $extConf = array();

	/** string content of cache file */
	var $cache = '';

	/** array used tca tables (needed as cache information) */
	var $tcaTables = array();

	/**
	 * Constructor
	 * :: initializes tx_date2cal
	 * :: loads the extension configuration
	 *
	 * @return void
	 */
	function tx_date2cal_extTables()
	{
		// init variables and configuration
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['date2cal']);
		$this->extConf['extCache'] = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extCache'];

		// convert end/start fields to evaluate also times
		if ($this->extConf['datetime'])
			$this->tx_date2cal_toDatetime();

		// add wizards to all date and datetime fields
		$this->tx_date2cal_setWizard();

		// write cache file (only if the cache file was loaded already and caching is enabled)
		if ($this->extConf['extCache'] && $this->extConf['doCache'])
			$this->tx_date2cal_writeCacheFile();
	}

	/**
	 * Writes the cache file
	 *
	 * @return void
	 */
	function tx_date2cal_writeCacheFile() {
		$tca = '';
		foreach($this->tcaTables as $tcaTable)
			$tca .= 't3lib_div::loadTCA(\'' . $tcaTable . '\');' . "\n";

		$this->cache = "&lt;?php\n" . $tca . $this->cache . "?&gt;\n";
		t3lib_div::writeFile(PATH_site . 'typo3temp/date2cal_cache.php',
			html_entity_decode($this->cache));
	}

	/**
	 * Sets date2cal wizard to each date/datetime field
	 *
	 * @return array tca tables
	 */
	function tx_date2cal_setWizard()
	{
		// iterate tca tables
		foreach($GLOBALS['TCA'] as $tcaTable => $tConf)
		{
			$changed = false;
			t3lib_div::loadTCA($tcaTable);

			// iterate table columns
			foreach($GLOBALS['TCA'][$tcaTable]['columns'] as $field => $fConf)
			{
				// type check
				$type = tx_date2cal_shared::isDateOrDateTime($fConf['config']);
				if ($type === false)
					continue;

				// add calendar wizard
				tx_date2cal_shared::addWizard($GLOBALS['TCA'][$tcaTable]['columns'][$field], $type);
				$changed = true;

				// write into the cache file
				$this->cache .=
					'$TCA[\'' . $tcaTable . '\'][\'columns\'][\'' . $field . '\']' .
					'[\'config\'][\'wizards\'][\'calendar\'][\'type\'] = \'userFunc\';
					$TCA[\'' . $tcaTable . '\'][\'columns\'][\'' . $field . '\']' .
					'[\'config\'][\'wizards\'][\'calendar\'][\'userFunc\'] = ' .
					'\'EXT:date2cal/src/class.tx_date2cal_wizard.php:tx_date2cal_wizard->renderWizard\';
					$TCA[\'' . $tcaTable . '\'][\'columns\'][\'' . $field . '\']' .
					'[\'config\'][\'wizards\'][\'calendar\'][\'evalValue\'] = ' .
					'\'' . $type . '\';' . "\n";
			}

			if ($changed)
				$this->tcaTables[] = $tcaTable;
		}
	}

	/**
	 * Forces start and end fields to add a time selector (just overrides default values)
	 * Note: this is only done for tt_content and pages table
	 *
	 * @return void
	 */
	function tx_date2cal_toDatetime()
	{
		t3lib_div::loadTCA('tt_content');
		$GLOBALS['TCA']['tt_content']['columns']['starttime']['config']['eval'] = 'datetime';
		$GLOBALS['TCA']['tt_content']['columns']['starttime']['config']['size'] = 12;
		$GLOBALS['TCA']['tt_content']['columns']['endtime']['config']['eval'] = 'datetime';
		$GLOBALS['TCA']['tt_content']['columns']['endtime']['config']['size'] = 12;

		t3lib_div::loadTCA('pages');
		$GLOBALS['TCA']['pages']['columns']['starttime']['config']['eval'] = 'datetime';
		$GLOBALS['TCA']['pages']['columns']['starttime']['config']['size'] = 12;
		$GLOBALS['TCA']['pages']['columns']['endtime']['config']['eval'] = 'datetime';
		$GLOBALS['TCA']['pages']['columns']['endtime']['config']['size'] = 12;

		$this->cache =
			'$TCA[\'tt_content\'][\'columns\'][\'starttime\'][\'config\'][\'eval\'] = \'datetime\';
			$TCA[\'tt_content\'][\'columns\'][\'starttime\'][\'config\'][\'size\'] = 12;
			$TCA[\'tt_content\'][\'columns\'][\'endtime\'][\'config\'][\'eval\'] = \'datetime\';
			$TCA[\'tt_content\'][\'columns\'][\'endtime\'][\'config\'][\'size\'] = 12;
			$TCA[\'pages\'][\'columns\'][\'starttime\'][\'config\'][\'eval\'] = \'datetime\';
			$TCA[\'pages\'][\'columns\'][\'starttime\'][\'config\'][\'size\'] = 12;
			$TCA[\'pages\'][\'columns\'][\'endtime\'][\'config\'][\'eval\'] = \'datetime\';
			$TCA[\'pages\'][\'columns\'][\'endtime\'][\'config\'][\'size\'] = 12;' . "\n";
	}
}

// check if a call is needed
$call = true;
if ($TYPO3_LOADED_EXT['_CACHEFILE'] != '' &&
	is_file(PATH_site . 'typo3temp/date2cal_cache.php')) {
	$t1 = filemtime(PATH_typo3conf . $TYPO3_LOADED_EXT['_CACHEFILE'] . '_ext_tables.php');
	$t2 = @filemtime(PATH_site . 'typo3temp/date2cal_cache.php');
	if (($t2 + 30) > $t1)
		$call = false;
}

// exec class
if ($call) {
	$date2cal = new tx_date2cal_extTables();
	unset($date2cal);
} else {
	include_once(PATH_site . 'typo3temp/date2cal_cache.php');
}

}
?>
