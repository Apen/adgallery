<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY . '_pi1'] = 'layout,select_key';
t3lib_extMgm::addPlugin(array('LLL:EXT:adgallery/locallang.xml:pi1_title', $_EXTKEY . '_pi1'), 'list_type');

// pi1 flexform
$TCA["tt_content"]["types"]["list"]["subtypes_addlist"][$_EXTKEY . "_pi1"] = "pi_flexform";
t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi1', 'FILE:EXT:' . $_EXTKEY . '/pi1/flexform_ds_pi1.xml');
// pi1 flexform

if (TYPO3_MODE == "BE") $TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_adgallery_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY) . 'pi1/class.tx_adgallery_pi1_wizicon.php';

// Add the tt_news compatibility
if (t3lib_extMgm::isLoaded('tt_news')) {
	$tempColumns = Array(
		'tx_adgallery_gallery' => Array(
			'exclude' => 1,
			'label' => 'LLL:EXT:adgallery/locallang.xml:selectgallery',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'tt_content',
				'size' => 1,
				'minitems' => 0,
				'maxitems' => 1,
			)
		)
	);
	t3lib_div::loadTCA("tt_news");
	t3lib_extMgm::addTCAcolumns('tt_news', $tempColumns, 1);
	t3lib_extMgm::addToAllTCAtypes("tt_news", "--div--;LLL:EXT:adgallery/locallang.xml:pi1_title,tx_adgallery_gallery");
}

?>