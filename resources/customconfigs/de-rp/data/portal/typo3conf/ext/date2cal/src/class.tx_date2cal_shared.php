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
 * Contains commonly used functions...
 *
 * @author Stefan Galinski <stefan.galinski@gmail.com>
 */
class tx_date2cal_shared {
	/**
	 * Checks the given field if its a date or datetime field...
	 *
	 * @param array $field TCEforms field (only the part with type and eval as keys)
	 * @return mixed "date", "datetime" or boolean false if its not a date or datetime field
	 */
	function isDateOrDateTime($field) {
		if ($field['type'] != 'input' || $field['eval'] == '')
			return false;

		// check type
		$eval = explode(',', $field['eval']);
		if (in_array('date', $eval))
			return 'date';

		if (in_array('datetime', $eval))
			return 'datetime';

		return false;
	}

	/**
	 * Adds the wizard to a TCEforms field...
	 *
	 * @param array $arrStruct config part of the field
	 * @param string $type eval type
	 * @return void
	 */
	function addWizard(&$arrStruct, $type) {
		$arrStruct['config']['wizards']['calendar']['type'] = 'userFunc';
		$arrStruct['config']['wizards']['calendar']['userFunc'] =
			'EXT:date2cal/src/class.tx_date2cal_wizard.php:tx_date2cal_wizard->renderWizard';
		$arrStruct['config']['wizards']['calendar']['evalValue'] = $type;
	}
}

?>
