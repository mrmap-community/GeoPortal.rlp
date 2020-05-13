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

/**
 * contains hooks for flexform manipulation (adding of calendar wizard)
 *
 * $Id$
 *
 * @author  Stefan Galinski <stefan.galinski@frm2.tum.de>
 */
class tx_date2cal_befunc
{
	/**
	 * Hook for manipulation of flexform fields
	 *
	 * Its needed to add the calendar wizard.
	 *
	 * @param array flexform value (array of xml file)
	 * @param array configuration of flexform fields (not needed)
	 * @param array flexform informations of special file (not needed)
	 * @param string table (not needed)
	 * @param string field (not needed)
	 * @return void
	 */
	function getFlexFormDS_postProcessDS(&$dataStructArray, $conf, $row, $table, $fieldName)
	{
		if(is_array($dataStructArray['ROOT']) && is_array($dataStructArray['ROOT']['el'])) { // single flexform (no tabs)
			foreach($dataStructArray['ROOT']['el'] as $field => $fConf) {
				if(($fConf['TCEforms']['config']['type'] == 'input') && 
					(t3lib_div::inList($fConf['TCEforms']['config']['eval'], 'date') ||
					t3lib_div::inList($fConf['TCEforms']['config']['eval'], 'datetime'))) {
						$dataStructArray['ROOT']['el'][$field]['TCEforms']
							['config']['wizards']['calendar']['type'] = 'userFunc';
						$dataStructArray['ROOT']['el'][$field]['TCEforms']
							['config']['wizards']['calendar']['userFunc'] =
							'EXT:date2cal/class.tx_date2cal_wizard.php:tx_date2cal_wizard->renderWizard';
						$dataStructArray['ROOT']['el'][$field]['TCEforms']
							['config']['wizards']['calendar']['evalValue'] =
							$fConf['TCEforms']['config']['eval'];
				}
			}
		} elseif(is_array($dataStructArray['sheets'])) { // tabbed flexform
			foreach($dataStructArray['sheets'] as $sheet => $sheetData) {
				list($sheetData, $sheet) = t3lib_div::resolveSheetDefInDS($dataStructArray,$sheet);
				foreach($sheetData['ROOT']['el'] as $field => $fConf) {
					if(($fConf['TCEforms']['config']['type'] == 'input') && 
						(t3lib_div::inList($fConf['TCEforms']['config']['eval'], 'date') ||
						t3lib_div::inList($fConf['TCEforms']['config']['eval'], 'datetime'))) {
							$dataStructArray['sheets'][$sheet]['ROOT']['el'][$field]['TCEforms']
								['config']['wizards']['calendar']['type'] = 'userFunc';
							$dataStructArray['sheets'][$sheet]['ROOT']['el'][$field]['TCEforms']
								['config']['wizards']['calendar']['userFunc'] =
								'EXT:date2cal/class.tx_date2cal_wizard.php:tx_date2cal_wizard->renderWizard';
							$dataStructArray['sheets'][$sheet]['ROOT']['el'][$field]['TCEforms']
								['config']['wizards']['calendar']['evalValue'] =
								$fConf['TCEforms']['config']['eval'];
					}
				}
			}
		}
	}
}

?>
