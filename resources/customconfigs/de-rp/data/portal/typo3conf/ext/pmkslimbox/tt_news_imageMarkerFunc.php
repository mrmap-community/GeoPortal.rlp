<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2007 Peter Klein (peter@umloud.dk)
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * This is an example for processing the news images by a user function.
 *
 * @author    Peter Klein <peter@umloud.dk>
 */

/**
 * Example function that sets the register var "IMAGE_NUM_CURRENT" to the the current image number.
 *
 * @param    array        $paramArray: $markerArray and $config of the current news item in an array
 * @param    [type]        $conf: ...
 * @return    array        the processed markerArray
 */
function user_imageMarkerFunc($paramArray,$conf){

	$markerArray = $paramArray[0];
	$lConf = $paramArray[1];
	$pObj = &$conf['parentObj']; // make a reference to the parent-object
	$row = $pObj->local_cObj->data;

	$imageNum = isset($lConf['imageCount']) ? $lConf['imageCount']:1;
	$imageNum = t3lib_div::intInRange($imageNum, 0, 100);
	$theImgCode = '';
	$imgs = t3lib_div::trimExplode(',', $row['image'], 1);
	$imgsCaptions = explode(chr(10), $row['imagecaption']);
	reset($imgs);
	$cc = 0;

	while (list(, $val) = each($imgs)) {
		if ($cc == $imageNum) break;
		if ($val) {
			 $lConf['image.']['altText'] = ''; // reset altText
			$lConf['image.']['altText'] = $lConf['image.']['altText']; // set altText to value from TS
			$lConf['image.']['file'] = 'uploads/pics/'.$val;
			switch($lConf['imgAltTextField']) {
				case 'image':
					$lConf['image.']['altText'] .= $val;
				break;
				case 'imagecaption':
					$lConf['image.']['altText'] .= $imgsCaptions[$cc];
				break;
				default:
					$lConf['image.']['altText'] .= $row[$lConf['imgAltTextField']];
			}
		}
		$GLOBALS['TSFE']->register['IMAGE_NUM_CURRENT'] = $cc;
		//$theImgCode .= $pObj->local_cObj->wrap($pObj->local_cObj->IMAGE($lConf['image.']).$pObj->local_cObj->stdWrap($imgsCaptions[$cc], $lConf['caption_stdWrap.']),$lConf['imageWrapIfAny']);
		$theImgCode .= $pObj->local_cObj->IMAGE($lConf['image.']).$pObj->local_cObj->stdWrap($imgsCaptions[$cc], $lConf['caption_stdWrap.']);
		$cc++;
	}
	$markerArray['###NEWS_IMAGE###'] = '';
	if ($cc) {
		//$markerArray['###NEWS_IMAGE###'] = trim($theImgCode);
		$markerArray['###NEWS_IMAGE###'] = $pObj->local_cObj->wrap(trim($theImgCode),$lConf['imageWrapIfAny']);
	}
	return $markerArray;
}

?>