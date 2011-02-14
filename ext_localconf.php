<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY, 'editorcfg', '
	tt_content.CSS_editor.ch.tx_adgallery_pi1 = < plugin.tx_adgallery_pi1.CSS_editor
', 43);


t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_adgallery_pi1.php', '_pi1', 'list_type', 1);

// Hooks for tt_news
if (t3lib_extMgm::isLoaded('tt_news')) {
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_news']['extraItemMarkerHook'][$_EXTKEY] = 'EXT:adgallery/hooks/class.tx_adgallery_extraGlobalMarkerHook.php:tx_adgallery_extraGlobalMarkerHook';
}

?>