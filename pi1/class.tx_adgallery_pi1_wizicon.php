<?php
/**
 * Copyright notice
 *
 *   (c) 2009 CERDAN Yohann <ycerdan@sodifrance.fr>
 *   All rights reserved
 *
 *   This script is part of the TYPO3 project. The TYPO3 project is
 *   free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   The GNU General Public License can be found at
 *   http://www.gnu.org/copyleft/gpl.html.
 *
 *   This script is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * Class that adds the wizard icon.
 *
 * @author     CERDAN Yohann <ycerdan@sodifrance.fr>
 * @package    TYPO3
 * @subpackage tx_adgallery
 */
class tx_adgallery_pi1_wizicon
{
	/**
	 * Processing the wizard items array
	 *
	 * @param array $wizardItems : The wizard items
	 * @return Modified array with wizard items
	 */
	function proc($wizardItems) {
		global $LANG;

		$LL = $this->includeLocalLang();

		$wizardItems['plugins_tx_adgallery_pi1'] = array(
			'icon'        => t3lib_extMgm::extRelPath('adgallery') . 'pi1/ce_wiz.gif',
			'title'       => $LANG->getLLL('pi1_title', $LL),
			'description' => $LANG->getLLL('pi1_plus_wiz_description', $LL),
			'params'      => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=adgallery_pi1'
		);

		return $wizardItems;
	}

	/**
	 * Reads the [extDir]/locallang.xml and returns the $LOCAL_LANG array found in that file.
	 *
	 * @return The array with language labels
	 */
	function includeLocalLang() {
		$llFile = t3lib_extMgm::extPath('adgallery') . 'locallang.xml';

		if (self::intFromVer(TYPO3_version) < 6000000) {
			$LOCAL_LANG = t3lib_div::readLLXMLfile($llFile, $GLOBALS['LANG']->lang);
		} else {
			$LOCAL_LANG = \TYPO3\CMS\Core\Utility\GeneralUtility::readLLfile($llFile, $GLOBALS['LANG']->lang);
		}

		return $LOCAL_LANG;
	}

	/**
	 * Returns an integer from a three part version number, eg '4.12.3' -> 4012003
	 *
	 * @param    string $verNumberStr  number on format x.x.x
	 * @return   integer   Integer version of version number (where each part can count to 999)
	 */
	public function intFromVer($verNumberStr) {
		$verParts = explode('.', $verNumberStr);
		return intval(
			(int)$verParts[0] . str_pad((int)$verParts[1], 3, '0', STR_PAD_LEFT) . str_pad(
				(int)$verParts[2], 3, '0', STR_PAD_LEFT
			)
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/adgallery/pi1/class.tx_adgallery_pi1_wizicon.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/adgallery/pi1/class.tx_adgallery_pi1_wizicon.php']);
}

?>