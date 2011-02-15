<?php

class tx_adgallery_extraGlobalMarkerHook
{
	function extraItemMarkerProcessor($markerArray, $row, $lConf, $caller)
	{
		if ($row['tx_adgallery_gallery'] != '') { // si une gallerie est spécifiée
			$cObjTemp = t3lib_div::makeInstance("tslib_cObj");
			$objContent = array('tables' => 'tt_content', 'source' => 'tt_content_' . $row['tx_adgallery_gallery']);
			$content = $cObjTemp->RECORDS($objContent);
			if (preg_match('/###ADGALLERY_GLOBAL###/', $markerArray['###NEWS_CONTENT###'], $matches)) { // si le marqueur est dans le RTE
				$markerArray['###NEWS_CONTENT###'] = preg_replace('/###ADGALLERY_GLOBAL###/', $content, $markerArray['###NEWS_CONTENT###']);
			} else {
				$markerArray['###NEWS_ADGALLERY###'] = $content;
			}
		} else {
			$markerArray['###NEWS_ADGALLERY###'] = '';
		}
		return $markerArray;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/adgallery/hooks/class.tx_adgallery_extraGlobalMarkerHook.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/adgallery/hooks/class.tx_adgallery_extraGlobalMarkerHook.php']);
}

?>