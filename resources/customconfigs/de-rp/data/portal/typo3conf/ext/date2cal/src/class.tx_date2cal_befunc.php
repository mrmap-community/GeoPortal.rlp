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

require_once(t3lib_extMgm::extPath('date2cal') . 'src/class.tx_date2cal_shared.php');

/**
 * Contains a hook for flexform manipulation (adding of calendar wizard)
 *
 * @author  Stefan Galinski <stefan.galinski@gmail.com>
 */
class tx_date2cal_befunc
{
	/**
	 * Hook for manipulating flexform fields
	 * Its needed to add the calendar wizard.
	 *
	 * @param array $dataStructArray flexform value (array of xml file)
	 * @param array $conf configuration of flexform fields (not needed)
	 * @param array $row flexform informations of special file (not needed)
	 * @param string $table table (not needed)
	 * @param string $fieldName field (not needed)
	 * @return void
	 */
	function getFlexFormDS_postProcessDS(&$dataStructArray, $conf, $row, $table, $fieldName) {
		if (is_array($dataStructArray['ROOT']) && is_array($dataStructArray['ROOT']['el']))
			$this->flexformNoTabs($dataStructArray);
		elseif (is_array($dataStructArray['sheets']))
			$this->flexformTabbed($dataStructArray);
	}

	/**
	 * Manipulates flexforms without tabs...
	 *
	 * @param array $dataStructArray flexform value (array of xml file)
	 * @return void
	 */
	function flexformNoTabs(&$dataStructArray) {
		foreach($dataStructArray['ROOT']['el'] as $field => $fConf) {
			// type check
			$type = tx_date2cal_shared::isDateOrDateTime($fConf['TCEforms']['config']);
			if ($type === false)
				continue;

			// add wizard
			tx_date2cal_shared::addWizard($dataStructArray['ROOT']['el'][$field]['TCEforms'], $type);
		}
	}

	/**
	 * Manipulates flexforms with tabs...
	 *
	 * @param array $dataStructArray flexform value (array of xml file)
	 * @return void
	 */
	function flexformTabbed(&$dataStructArray) {
		foreach($dataStructArray['sheets'] as $sheet => $sheetData) {
			list($sheetData, $sheet) = t3lib_div::resolveSheetDefInDS($dataStructArray, $sheet);
			foreach($sheetData['ROOT']['el'] as $field => $fConf)
			{
				// type check
				$type = tx_date2cal_shared::isDateOrDateTime($fConf['TCEforms']['config']);
				if ($type === false)
					continue;

				// add wizard
				tx_date2cal_shared::addWizard(
					$dataStructArray['sheets'][$sheet]['ROOT']['el'][$field]['TCEforms'], $type);
			}
		}
	}
}

?>
