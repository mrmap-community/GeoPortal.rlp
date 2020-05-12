<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Andreas Kapp <ak@q4u.de>
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
 * Plugin 'Q4U Search' for the 'q4u_search' extension.
 *
 * @author	Andreas Kapp <ak@q4u.de>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_q4usearch_pi1 extends tslib_pibase {
	var $prefixId = 'tx_q4usearch_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_q4usearch_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'q4u_search';	// The extension key.
	var $pi_checkCHash = TRUE;

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		global $output;

		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		if($_REQUEST["searchid"]=="") {
    		include_once("search_stats.php");
    	}

		include_once("start_search.php");

		return $this->pi_wrapInBaseClass($output);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/q4u_search/pi1/class.tx_q4usearch_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/q4u_search/pi1/class.tx_q4usearch_pi1.php']);
}

?>