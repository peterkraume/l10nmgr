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
 * l10nmgr module cm3
 *
 * @author Kasper Skårhøj <kasperYYYY@typo3.com>
 */

use Localizationteam\L10nmgr\Model\Tools\Tools;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Translation management tool
 *
 * @authorKasper Skaarhoj <kasperYYYY@typo3.com>
 * @packageTYPO3
 * @subpackage tx_l10nmgr
 */
class Cm3
{
    /**
     * @var ModuleTemplate
     */
    protected $module;
    /**
     * @var Tools
     */
    protected $l10nMgrTools;

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
    public function main()
    {
        // Draw the header.
        $this->module = GeneralUtility::makeInstance(ModuleTemplate::class);
        $this->module->setForm('<form action="" method="post" enctype="multipart/form-data">');
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
                (int)GeneralUtility::_GP('id'),
                GeneralUtility::_GP('cmd')
            ) . '</div>';
    }

    /**
     * [Describe function...]
     *
     * @param $table
     * @param $uid
     * @param $cmd
     * @return string [type]...
     * @internal param $ [type]$table: ...
     * @internal param $ [type]$uid: ...
     *
     */
    protected function moduleContent($table, $uid, $cmd)
    {
        $output = '';
        if ($GLOBALS['TCA'][$table]) {
            $inputRecord = BackendUtility::getRecord($table, $uid, 'pid');
            $this->module->getDocHeaderComponent()->setMetaInformation(BackendUtility::readPageAccess($table == 'pages' ? $uid : $inputRecord['pid'],
                ' 1=1'));

            $this->l10nMgrTools = GeneralUtility::makeInstance(Tools::class);
            $this->l10nMgrTools->verbose = false; // Otherwise it will show records which has fields but none editable.
            switch ((string)$cmd) {
                case 'updateIndex':
                    $output = $this->l10nMgrTools->updateIndexForRecord($table, $uid);
                    BackendUtility::setUpdateSignal('updatePageTree');
                    break;
                case 'flushTranslations':
                    if ($this->getBackendUser()->isAdmin()) {
                        $res = $this->l10nMgrTools->flushTranslations(
                            $table,
                            $uid,
                            GeneralUtility::_POST('_flush') ? true : false
                        );
                        if (!GeneralUtility::_POST('_flush')) {
                            $output .= 'To flush the translations shown below, press the "Flush" button below:<br /><input type="submit" name="_flush" value="FLUSH" /><br /><br />';
                        } else {
                            $output .= 'Translations below were flushed!';
                        }
                        $output .= DebugUtility::viewArray($res[0]);
                        if (GeneralUtility::_POST('_flush')) {
                            $output .= '<div class="alert alert-success">' . $this->l10nMgrTools->updateIndexForRecord($table,
                                    $uid) . '</div>';
                            BackendUtility::setUpdateSignal('updatePageTree');
                        }
                    }
                    break;
                case 'createPriority':
                    HttpUtility::redirect(
                        BackendUtility::getModuleUrl('record_edit', [
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
                            'returnUrl' => BackendUtility::getModuleUrl('web_list',
                                ['id' => 0, 'table' => 'tx_l10nmgr_priorities'])
                        ])
                    );
                    break;
                case 'managePriorities':
                    HttpUtility::redirect(
                        BackendUtility::getModuleUrl('web_list', ['id' => 0, 'table' => 'tx_l10nmgr_priorities'])
                    );
                    break;
            }
        }
        return $output;
    }

    /**
     * Printing output content
     *
     * @return void
     */
    public function printContent()
    {
        $this->module->setContent($this->content);
        echo $this->module->renderContent();
    }

    /**
     * Returns the Backend User
     *
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Returns the Language Service
     *
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
