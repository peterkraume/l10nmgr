<?php

use Localizationteam\L10nmgr\Hooks\Tcemain;
use Localizationteam\L10nmgr\LanguageRestriction\LanguageRestrictionRegistry;
use Localizationteam\L10nmgr\Task\L10nmgrAdditionalFieldProvider;
use Localizationteam\L10nmgr\Task\L10nmgrFileGarbageCollection;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

ExtensionManagementUtility::addUserTSConfig(
    '
	options.saveDocNew.tx_l10nmgr_cfg=1
	options.saveDocNew.tx_l10nmgr_priorities=1
'
);

//! increase with every change to XML Format
define('L10NMGR_FILEVERSION', '1.2');
define('L10NMGR_VERSION', '9.2.0');
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['tx_l10nmgr'] = Tcemain::class;

// Enable stats
$enableStatHook = GeneralUtility::makeInstance(
    ExtensionConfiguration::class
)->get('l10nmgr', 'enable_stat_hook');
if ($enableStatHook) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['recStatInfoHooks']['tx_l10nmgr'] = Tcemain::class . '->stat';
}

// Add file cleanup task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][L10nmgrFileGarbageCollection::class] = [
    'extension' => 'l10nmgr',
    'title' => 'LLL:EXT:l10nmgr/Resources/Private/Language/Task/locallang.xlf:fileGarbageCollection.name',
    'description' => 'LLL:EXT:l10nmgr/Resources/Private/Language/Task/locallang.xlf:fileGarbageCollection.description',
    'additionalFields' => L10nmgrAdditionalFieldProvider::class,
];

$GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] .= ',l10nmgr_configuration,l10nmgr_configuration_next_level';

$signalSlotDispatcher = GeneralUtility::makeInstance(
    Dispatcher::class
);
$signalSlotDispatcher->connect(
    'TYPO3\\CMS\\Install\\Service\\SqlExpectedSchemaService',
    'tablesDefinitionIsBeingBuilt',
    LanguageRestrictionRegistry::class,
    'addLanguageRestrictionDatabaseSchemaToTablesDefinition'
);
unset($signalSlotDispatcher);

