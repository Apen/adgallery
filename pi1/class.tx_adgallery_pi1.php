<?php
/**
 *    Copyright notice
 *
 *    (c) 2009 CERDAN Yohann <cerdanyohann@yahoo.fr>
 *    All rights reserved
 *
 *    This script is part of the TYPO3 project. The TYPO3 project is
 *    free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 *
 *    The GNU General Public License can be found at
 *    http://www.gnu.org/copyleft/gpl.html.
 *
 *    This script is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    This copyright notice MUST APPEAR in all copies of the script!
 */

if (tx_adgallery_pi1::intFromVer(TYPO3_version) < 6002000) {
	require_once(PATH_tslib . 'class.tslib_pibase.php');
}

/**
 * Plugin 'Affichage d'une galerie multimédia' for the 'adgallery' extension.
 *
 * @author     CERDAN Yohann <cerdanyohann@yahoo.fr>
 * @package    TYPO3
 * @subpackage tx_adgallery
 */
class tx_adgallery_pi1 extends tslib_pibase {
	public $prefixId = 'tx_adgallery_pi1'; // Same as class name
	public $scriptRelPath = 'pi1/class.tx_adgallery_pi1.php'; // Path to this script relative to the extension dir.
	public $extKey = 'adgallery'; // The extension key.
	public $conf = NULL;
	protected $template = NULL;
	protected $misc = NULL;
	protected $imageDir = 'uploads/tx_adgallery/';

	/**
	 * The main method of the PlugIn
	 *
	 * @param string $content : The PlugIn content
	 * @param array  $conf    : The PlugIn configuration
	 * @return The content that is displayed on the website
	 */

	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->init();
		$content = $this->displayItemsList();
		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * tx_adgallery_pi1::init()
	 *
	 * @return
	 */

	function init() {
		// Instanciation
		$this->template = new tx_t3devapi_templating($this);
		$this->conf = tx_t3devapi_config::getArrayConfig();
		$this->misc = new tx_t3devapi_miscellaneous($this);

		// List & single UID
		$this->conf['singleView'] = ($this->conf['singleView'] != '') ? $this->conf['singleView'] : $GLOBALS['TSFE']->id;
		$this->conf['listView'] = ($this->conf['listView'] != '') ? $this->conf['listView'] : $GLOBALS['TSFE']->id;

		// Size of the global object
		$taille = explode('x', $this->conf['taille']);
		$this->conf['width'] = (isset($taille[0]) && ($taille[0] != '')) ? $taille[0] : 400;
		$this->conf['height'] = (isset($taille[1]) && ($taille[1] != '')) ? $taille[1] : 400;

		// Size of the list thumbs
		$tailleVignette = explode('x', $this->conf['tailleVignette']);
		$this->conf['vignetteWidth'] = (isset($tailleVignette[0]) && ($tailleVignette[0] != '')) ? $tailleVignette[0] : '70c';
		$this->conf['vignetteHeight'] = (isset($tailleVignette[1]) && ($tailleVignette[1] != '')) ? $tailleVignette[1] : '70';

		// Size of the full thumbs
		$tailleVignette = explode('x', $this->conf['tailleVignetteSingle']);
		$this->conf['vignetteSingleWidth'] = (isset($tailleVignette[0]) && ($tailleVignette[0] != '')) ? $tailleVignette[0] : '400c';
		$this->conf['vignetteSingleHeight'] = (isset($tailleVignette[1]) && ($tailleVignette[1] != '')) ? $tailleVignette[1] : '300';

		// Path to the HTML template
		if ((isset($this->conf['template_file'])) && ($this->conf['template_file'] != '')) {
			$this->template->initTemplate(trim($this->conf['template_file']));
		} else {
			$this->template->initTemplate('typo3conf/ext/' . $this->extKey . '/res/template.html');
		}

		// CSS
		if ((isset($this->conf['csspath'])) && ($this->conf['csspath'] != '')) {
			$GLOBALS['TSFE']->pSetup['includeCSS.'][$this->extKey] = trim($this->conf['csspath']);
		} else {
			$GLOBALS['TSFE']->pSetup['includeCSS.'][$this->extKey] = 'typo3conf/ext/adgallery/libs/jquery.ad-gallery.css';
		}

		// JS in footer ?
		if ($this->conf['jsfooter'] == 1) {
			$includeJS = 'includeJSFooter.';
		} else {
			$includeJS = 'includeJS.';
		}

		// JS
		if ((isset($this->conf['jspath'])) && ($this->conf['jspath'] != '')) {
			$GLOBALS['TSFE']->pSetup[$includeJS][$this->extKey] = trim($this->conf['jspath']);
		} else {
			$GLOBALS['TSFE']->pSetup[$includeJS][$this->extKey] = 'typo3conf/ext/adgallery/libs/jquery.ad-gallery.js';
		}

		// Debug SQL
		// $this->misc->debugQueryInit();
	}

	/**
	 * tx_adgallery_pi1::getAllImagesFromDirectory()
	 *
	 * @param mixed $directory
	 * @return
	 */

	function getAllImagesFromDirectory($directory) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'tx_dam.uid,tx_dam.pid,tx_dam.file_path,tx_dam.file_name,tx_dam.title,tx_dam.description,tx_dam.alt_text',
			'tx_dam',
			'1=1 ' . $this->cObj->enableFields('tx_dam') . ' AND tx_dam.file_path=\'' . mysql_real_escape_string($directory) . '\'',
			'',
			'sorting,uid'
		);
		// $this->misc->debugQuery();
		return $res;
	}

	/**
	 * tx_adgallery_pi1::getAllImagesFromCategory()
	 *
	 * @param mixed $category
	 * @return
	 */

	function getAllImagesFromCategory($category) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'tx_dam.uid,tx_dam.pid,tx_dam.file_path,tx_dam.file_name,tx_dam.title,tx_dam.description,tx_dam.alt_text',
			'tx_dam,tx_dam_mm_cat',
			'tx_dam.uid=tx_dam_mm_cat.uid_local ' . $this->cObj->enableFields('tx_dam') . ' AND find_in_set(\'' . intval($category) . '\',tx_dam_mm_cat.uid_foreign)',
			'',
			'tx_dam.sorting,tx_dam.uid'
		);
		// $this->misc->debugQuery();
		return $res;
	}

	/**
	 * tx_adgallery_pi1::getAllItems()
	 *
	 * @return
	 */

	function getAllItems() {
		$res = NULL;

		switch ($this->conf['displayType']) {
			case '1' :
				$res = $this->getAllImagesFromDirectory($this->conf['imagespath']);
				break;
			case '2' :
				$res = $this->getAllImagesFromCategory($this->conf['damcategory']);
				break;
		}

		return $res;
	}

	/**
	 * tx_adgallery_pi1::displayItemsList()
	 *
	 * @return
	 */

	function displayItemsList() {
		$content = '';
		$contentList = '';

		$res = $this->getAllItems();

		$markerArrayGlobal = array();

		$iItem = 1;

		$markerArrayTemp = array();

		// Normal mode with selected files
		if ($this->conf['displayType'] == 0) {
			$files = t3lib_div::trimExplode(',', $this->conf['classicimages']);
			$titles = t3lib_div::trimExplode(chr(10), $this->conf['classicimagestitles']);
			$alt_texts = t3lib_div::trimExplode(chr(10), $this->conf['classicimagesalttexts']);
			foreach ($files as $file) {
				$markerArray = array();
				$item['file_path'] = trim($this->imageDir);
				$item['file_name'] = $file;
				$item['title'] = (count($titles) >= $iItem) ? $titles[$iItem - 1] : '';
				$item['alt_text'] = (count($alt_texts) >= $iItem) ? $alt_texts[$iItem - 1] : '';
				$item['description'] = $item['file_path'] . $file;
				$item = $this->processItemList($item);
				$item['i'] = $iItem++;
				$markerArray = array_merge($markerArray, $this->misc->convertToMarkerArray($item));
				$markerArrayTemp [] = $markerArray;
				unset($markerArray);
			}

		} else if ($this->conf['displayType'] == 3) { // Normal mode without DAM
			$path = rtrim(trim(PATH_site . $this->conf['classicimagespath']), '/');
			$files = t3lib_div::getFilesInDir($path, 'png,gif,jpg,jpeg', 0, 1);
			foreach ($files as $file) {
				$markerArray = array();
				$item['file_path'] = trim($this->conf['classicimagespath']);
				$item['file_name'] = $file;
				$item['title'] = $file;
				$item['alt_text'] = '';
				$item['description'] = '';
				$item = $this->processItemList($item);
				$item['i'] = $iItem++;
				$markerArray = array_merge($markerArray, $this->misc->convertToMarkerArray($item));
				$markerArrayTemp [] = $markerArray;
				unset($markerArray);
			}
		} else { // Mode with DAM directory or DAM category
			while ($item = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$markerArray = array();
				$item = $this->processItemList($item);
				$item['i'] = $iItem++;
				$markerArray = array_merge($markerArray, $this->misc->convertToMarkerArray($item));
				$markerArrayTemp [] = $markerArray;
				unset($markerArray);
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}
		// 0 item
		if (count($markerArrayTemp) == 0) {
			return $content;
		}

		$contentList = $this->template->renderAllTemplate($markerArrayTemp, '###ADGALLERY_LIST###');
		unset($markerArrayTemp);

		$markerArrayGlobal['###ADGALLERY_LIST###'] = $contentList;
		$markerArrayGlobal = array_merge($markerArrayGlobal, $this->extraGlobalMarker());

		$content .= $this->template->renderAllTemplate($markerArrayGlobal, '###ADGALLERY_GLOBAL###');

		return $content;
	}

	/**
	 * tx_adgallery_pi1::processItemList()
	 *
	 * @param mixed $item
	 * @return
	 */

	function processItemList($item) {
		// Path to the image
		$item['href'] = $item['file_path'] . $item['file_name'];

		// Path to the original image
		$item['thumbfull'] = $item['href'];

		// Path to the single thumb
		if (($this->conf['vignetteSingleWidth'] != '') && ($this->conf['vignetteSingleHeight'] != '')) {
			$thumbFull = $this->misc->cImage($item['file_path'] . $item['file_name'], $item['title'], $item['alt_text'], $this->conf['vignetteSingleWidth'], $this->conf['vignetteSingleHeight'],
			                                 $item['description']
			);
			if (preg_match('/src="(.*?)"/', $thumbFull, $matches)) {
				$item['thumbfull'] = $matches[1];
			}
		}

		// Path to the list thumb
		$item['thumb'] = $this->misc->cImage($item['file_path'] . $item['file_name'], $item['title'], $item['alt_text'], $this->conf['vignetteWidth'], $this->conf['vignetteHeight'],
		                                     $item['description']
		);

		return $item;
	}

	/**
	 * tx_adgallery_pi1::extraGlobalMarker()
	 *
	 * @return
	 */
	function extraGlobalMarker() {
		$extra = array();
		$extra['width'] = $this->conf['width'];
		$extra['height'] = $this->conf['height'];
		$extra['effect'] = $this->conf['effect'];
		return $this->misc->convertToMarkerArray($extra);
	}

	/**
	 * Returns an integer from a three part version number, eg '4.12.3' -> 4012003
	 *
	 * @param    string $verNumberStr number on format x.x.x
	 * @return   integer   Integer version of version number (where each part can count to 999)
	 */
	public static function intFromVer($verNumberStr) {
		$verParts = explode('.', $verNumberStr);
		return intval((int)$verParts[0] . str_pad((int)$verParts[1], 3, '0', STR_PAD_LEFT) . str_pad((int)$verParts[2], 3, '0', STR_PAD_LEFT));
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/adgallery/pi1/class.tx_adgallery_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/adgallery/pi1/class.tx_adgallery_pi1.php']);
}

?>