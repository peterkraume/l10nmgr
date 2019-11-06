<?php
namespace Localizationteam\L10nmgr\Controller;

/***************************************************************
 * Copyright notice
 * (c) 2007 Kasper Skårhøj <kasperYYYY@typo3.com>
 * All rights reserved
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * l10nmgr module cm2
 *
 * @author Kasper Skårhøj <kasperYYYY@typo3.com>
 */

use Localizationteam\L10nmgr\Model\Tools\Tools;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Translation management tool
 *
 * @authorKasper Skaarhoj <kasperYYYY@typo3.com>
 * @packageTYPO3
 * @subpackage tx_l10nmgr
 */
class Cm2
{
    /**
     * @var LanguageService
     */
    protected $languageService;
    /**
     * @var ModuleTemplate
     */
    protected $module;
    /**
     * @var Tools
     */
    protected $l10nMgrTools;
    /**
     * @var array
     */
    protected $sysLanguages;

    /**
     * Generally used for accumulating the output content of backend modules
     *
     * @var string
     */
    protected $content = '';

    /**
     * main action to be registered in "Configuration/Backend/Routes.php"
     */
    public function mainAction()
    {
        $this->main();
        $this->printContent();
    }

    /**
     * Main function of the module. Write the content to
     *
     * @return void
     */
    protected function main()
    {
        // Draw the header.
        $this->module = GeneralUtility::makeInstance(ModuleTemplate::class);
        $this->module->setForm('<form action="" id="tx_l10nmgr_cm2" method="post" enctype="multipart/form-data">');
        // JavaScript
        $this->module->addJavaScriptCode('
	script_ended = 0;
	function jumpToUrl(URL)	{
	document.location = URL;
	}
	');
        // Header:
        $this->content .= $this->module->header($this->getLanguageService()->getLL('title'));
        $this->content .= '<hr />';
        // Render the module content (for all modes):
        $this->content .= '<div class="bottomspace10">' . $this->moduleContent(
                (string)GeneralUtility::_GP('table'),
                (int)GeneralUtility::_GP('uid')
            ) . '</div>';
    }

    /**
     * [Describe function...]
     *
     * @param $table
     * @param $uid
     * @return string [type]...
     * @internal param $ [type]$table: ...
     * @internal param $ [type]$uid: ...
     *
     */
    protected function moduleContent($table, $uid)
    {
        $output = '';
        if ($GLOBALS['TCA'][$table]) {
            $this->l10nMgrTools = GeneralUtility::makeInstance(Tools::class);
            $this->l10nMgrTools->verbose = false; // Otherwise it will show records which has fields but none editable.
            if (GeneralUtility::_POST('_updateIndex')) {
                $output .= '<div class="alert alert-success">' . $this->l10nMgrTools->updateIndexForRecord($table, $uid) . '</div>';
                BackendUtility::setUpdateSignal('updatePageTree');
            }
            $inputRecord = BackendUtility::getRecord($table, $uid, 'pid');
            $this->module->getDocHeaderComponent()->setMetaInformation(BackendUtility::readPageAccess($table == 'pages' ? $uid : $inputRecord['pid'], ' 1=1'));
            $this->sysLanguages = $this->l10nMgrTools->t8Tools->getSystemLanguages($table == 'pages' ? $uid : $inputRecord['pid']);
            $languageListArray = explode(',',
                $this->getBackendUser()->groupData['allowed_languages'] ? $this->getBackendUser()->groupData['allowed_languages'] : implode(',',
                    array_keys($this->sysLanguages)));
            $limitLanguageList = trim(GeneralUtility::_GP('languageList'));
            foreach ($languageListArray as $kkk => $val) {
                if ($limitLanguageList && !GeneralUtility::inList($limitLanguageList, $val)) {
                    unset($languageListArray[$kkk]);
                }
            }
            if (!count($languageListArray)) {
                $languageListArray[] = 0;
            }
            $languageList = implode(',', $languageListArray);
            // Fetch translation index records:
            if ($table != 'pages') {
                $records = $this->getDatabaseConnection()->exec_SELECTgetRows('*', 'tx_l10nmgr_index',
                    'tablename=' . $this->getDatabaseConnection()->fullQuoteStr($table,
                        'tx_l10nmgr_index') . ' AND recuid=' . (int)$uid . ' AND translation_lang IN (' . $this->getDatabaseConnection()->cleanIntList($languageList) . ')' . ' AND workspace=' . (int)$this->getBackendUser()->workspace . ' AND (flag_new>0 OR flag_update>0 OR flag_noChange>0 OR flag_unknown>0)',
                    '', 'translation_lang, tablename, recuid');
            } else {
                $records = $this->getDatabaseConnection()->exec_SELECTgetRows('*', 'tx_l10nmgr_index',
                    'recpid=' . (int)$uid . ' AND translation_lang IN (' . $this->getDatabaseConnection()->cleanIntList($languageList) . ')' . ' AND workspace=' . (int)$this->getBackendUser()->workspace . ' AND (flag_new>0 OR flag_update>0 OR flag_noChange>0 OR flag_unknown>0)',
                    '', 'translation_lang, tablename, recuid');
            }
            //	\TYPO3\CMS\Core\Utility\GeneralUtility::debugRows($records,'Index entries for '.$table.':'.$uid);
            $tRows = [];
            $tRows[] = '<tr class="bgColor2 tableheader">
	<th colspan="2">Base element:</th>
	<th colspan="2">Translation:</th>
	<th>Action:</th>
	<th><img src="../' . ExtensionManagementUtility::siteRelPath('l10nmgr') . '/Resources/Public/Images/flags_new.png" width="10" height="16" alt="New" title="New" /></th>
	<th><img src="../' . ExtensionManagementUtility::siteRelPath('l10nmgr') . '/Resources/Public/Images/flags_unknown.png" width="10" height="16" alt="Unknown" title="Unknown" /></th>
	<th><img src="../' . ExtensionManagementUtility::siteRelPath('l10nmgr') . '/Resources/Public/Images/flags_update.png" width="10" height="16" alt="Update" title="Update" /></th>
	<th><img src="../' . ExtensionManagementUtility::siteRelPath('l10nmgr') . '/Resources/Public/Images/flags_ok.png" width="10" height="16" alt="OK" title="OK" /></th>
	<th>Diff:</th>
	</tr>';
            //\TYPO3\CMS\Core\Utility\GeneralUtility::debugRows($records);
            foreach ($records as $rec) {
                if ($rec['tablename'] == 'pages') {
                    $tRows[] = $this->makeTableRow($rec);
                }
            }
            if (count($tRows) > 1) {
                $tRows[] = '<tr><td colspan="10">&nbsp;</td></tr>';
            }
            foreach ($records as $rec) {
                if ($rec['tablename'] != 'pages') {
                    $tRows[] = $this->makeTableRow($rec);
                }
            }
            $output .= '<div class="table-fit"><table class="table table-striped table-hover">' . implode('',
                    $tRows) . '</table></div>';
            // Updating index
            if ($this->getBackendUser()->isAdmin()) {
                $this->addDocHeaderButtons($table, $uid);
            }
        }
        return $output;
    }

    /**
     * Add doc header button "Update index", "Flush translations" and "Create priority"
     *
     * @param string $table
     * @param int $uid
     * @return void
     */
    protected function addDocHeaderButtons($table, $uid)
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $buttonBar = $this->module->getDocHeaderComponent()->getButtonBar();
        $buttonBar->addButton(
            $buttonBar->makeInputButton()->setForm('tx_l10nmgr_cm2')
                ->setName('_updateIndex')
                ->setTitle('Update Index')
                ->setValue('Update Index')
                ->setShowLabelText(true)
                ->setIcon($this->module->getIconFactory()->getIcon('actions-refresh', Icon::SIZE_SMALL))
        );
        $buttonBar->addButton(
            $buttonBar->makeLinkButton()->setTitle('Flush Translations')
                ->setShowLabelText(true)
                ->setHref($uriBuilder->buildUriFromRoute(
                    'tx_l10nmgr_cm3',
                    ['table' => $table, 'id' => $uid, 'cmd' => 'flushTranslations']
                ))
                ->setIcon($this->module->getIconFactory()->getIcon('actions-system-cache-clear', Icon::SIZE_SMALL))
        );
        $buttonBar->addButton(
            $buttonBar->makeLinkButton()->setTitle('Create priority')
                ->setShowLabelText(true)
                ->setHref(BackendUtility::getModuleUrl('record_edit', [
                    'edit' => [
                        'tx_l10nmgr_priorities' => [
                            0 => 'new'
                        ]
                    ],
                    'defVals' => [
                        'tx_l10nmgr_priorities' => [
                            'element' => $table . '_' . $uid
                        ]
                    ],
                    'returnUrl' => BackendUtility::getModuleUrl('web_list', ['id' => 0, 'table' => 'tx_l10nmgr_priorities'])
                ]))
                ->setIcon($this->module->getIconFactory()->getIcon('actions-document-new', Icon::SIZE_SMALL))
        );
    }

    /**
     * [Describe function...]
     *
     * @param $rec
     * @return string [type]...
     * @internal param $ [type]$rec: ...
     *
     */
    protected function makeTableRow($rec)
    {
        //Render information for base record:
        $baseRecord = BackendUtility::getRecordWSOL($rec['tablename'], $rec['recuid']);
        if (!is_array($baseRecord)) {
            // Base record not exist. Return empty row
            return '';
        }
        $icon = $this->module->getIconFactory()->getIconForRecord($rec['tablename'], $baseRecord);
        $title = BackendUtility::getRecordTitle($rec['tablename'], $baseRecord, 1);
        $baseRecordFlag = $this->module->getIconFactory()->getIcon($this->sysLanguages[$rec['sys_language_uid']]['flagIcon'], Icon::SIZE_SMALL)->render();
        $tFlag = $this->module->getIconFactory()->getIcon($this->sysLanguages[$rec['translation_lang']]['flagIcon'], Icon::SIZE_SMALL)->render();
        $baseRecordStr = '<a href="#" onclick="' . htmlspecialchars(BackendUtility::editOnClick('&edit[' . $rec['tablename'] . '][' . $rec['recuid'] . ']=edit')) . '">' . $icon . $title . '</a>';
        // Render for translation if any:
        $translationTable = '';
        $translationRecord = false;
        if ($rec['translation_recuid']) {
            $translationTable = $this->l10nMgrTools->t8Tools->getTranslationTable($rec['tablename']);
            $translationRecord = BackendUtility::getRecordWSOL($translationTable, $rec['translation_recuid']);
            $icon = GeneralUtility::makeInstance(IconFactory::class)->getIconForRecord($translationTable, $translationRecord);
            $title = BackendUtility::getRecordTitle($translationTable, $translationRecord, 1);
            $translationRecStr = '<a href="#" onclick="' . htmlspecialchars(BackendUtility::editOnClick('&edit[' . $translationTable . '][' . $translationRecord['uid'] . ']=edit')) . '">' . $icon . $title . '</a>';
        } else {
            $translationRecStr = '';
        }
        // Action:
        if (is_array($translationRecord)) {
            $action = '<a href="#" onclick="' . htmlspecialchars(BackendUtility::editOnClick('&edit[' . $translationTable . '][' . $translationRecord['uid'] . ']=edit')) . '"><em>[Edit]</em></a>';
        } elseif ($rec['sys_language_uid'] == -1) {
            $action = '<a href="#" onclick="' . htmlspecialchars(BackendUtility::editOnClick('&edit[' . $rec['tablename'] . '][' . $rec['recuid'] . ']=edit')) . '"><em>[Edit]</em></a>';
        } else {
            $action = '<a href="' . htmlspecialchars(BackendUtility::getLinkToDataHandlerAction('&cmd[' . $rec['tablename'] . '][' . $rec['recuid'] . '][localize]=' . $rec['translation_lang'])) . '"><em>[Localize]</em></a>';
        }
        return '<tr>
	<td valign="top">' . $baseRecordFlag . '</td>
	<td valign="top" nowrap="nowrap">' . $baseRecordStr . '</td>
	<td valign="top">' . $tFlag . '</td>
	<td valign="top" nowrap="nowrap">' . $translationRecStr . '</td>
	<td valign="top">' . $action . '</td>
	<td align="center"' . ($rec['flag_new'] ? ' bgcolor="#91B5FF"' : '') . '>' . ($rec['flag_new'] ? $rec['flag_new'] : '') . '</td>
	<td align="center"' . ($rec['flag_unknown'] ? ' bgcolor="#FEFF5A"' : '') . '>' . ($rec['flag_unknown'] ? $rec['flag_unknown'] : '') . '</td>
	<td align="center"' . ($rec['flag_update'] ? ' bgcolor="#FF7161"' : '') . '>' . ($rec['flag_update'] ? $rec['flag_update'] : '') . '</td>
	<td align="center"' . ($rec['flag_noChange'] ? ' bgcolor="#78FF82"' : '') . '>' . ($rec['flag_noChange'] ? $rec['flag_noChange'] : '') . '</td>
	<td>' . implode('<br />', unserialize($rec['serializedDiff'])) . '</td>
	</tr>';
    }

    /**
     * Printing output content
     *
     * @return void
     */
    protected function printContent()
    {
        $this->module->setContent($this->content);
        echo $this->module->renderContent();
    }

    /**
     * Returns the Language Service
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Returns the Backend User
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @return DatabaseConnection
     * @deprecated since TYPO3 v8, will be removed in TYPO3 v9, use the Doctrine DBAL layer via the ConnectionPool class
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
