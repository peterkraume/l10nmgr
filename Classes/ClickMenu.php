<?php
namespace Localizationteam\L10nmgr;

/***************************************************************
 * Copyright notice
 * (c) 2006 Kasper Skårhøj <kasperYYYY@typo3.com>
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
 * Addition of an item to the clickmenu
 *
 * @author Kasper Skårhøj <kasperYYYY@typo3.com>
 */

use TYPO3\CMS\Backend\ContextMenu\ItemProviders\AbstractProvider;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Context menu processing
 *
 * @authorKasper Skaarhoj <kasperYYYY@typo3.com>
 * @packageTYPO3
 * @subpackage tx_l10nmgr
 */
class ClickMenu extends AbstractProvider
{
    /**
     * Array of items the class is providing
     *
     * @var array
     */
    protected $itemsConfiguration = [
        'tx_l10nmgr' => [
            'type' => 'submenu',
            'label' => 'LLL:EXT:l10nmgr/Resources/Private/Language/locallang.xlf:cm1_title',
            'iconIdentifier' => 'module-LocalizationManager',
            'childItems' => [
                'tx_l10nmgr_create' => [
                    'type' => 'item',
                    'label' => 'Create priority',
                    'iconIdentifier' => 'actions-document-new',
                    'callbackAction' => 'openUrl'
                ],
                'tx_l10nmgr_manage' => [
                    'type' => 'item',
                    'label' => 'Manage priorities',
                    'iconIdentifier' => 'actions-document',
                    'callbackAction' => 'openUrl'
                ],
                'tx_l10nmgr_flush' => [
                    'type' => 'item',
                    'label' => 'Flush Translations',
                    'iconIdentifier' => 'actions-system-cache-clear',
                    'callbackAction' => 'openUrl'
                ],
            ]
        ]
    ];

    /**
     * This needs to be lower than priority of the RecordProvider
     *
     * @return int
     */
    public function getPriority(): int
    {
        return 10;
    }

    /**
     * Whether this provider can handle given request (usually a check based on table, uid and context)
     *
     * @return bool
     */
    public function canHandle(): bool
    {
        return $this->backendUser->isAdmin() &&
            ($this->table === 'pages' || BackendUtility::isTableLocalizable($this->table));
    }

    /**
     * Checks whether certain item can be rendered (e.g. check for disabled items or permissions)
     *
     * @param string $itemName
     * @param string $type
     * @return bool
     */
    protected function canRender(string $itemName, string $type): bool
    {
        return !in_array($itemName, $this->disabledItems, true);
    }

    /**
     * Add module url to attributes
     *
     * @param string $itemName
     * @return array
     * @throws \TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException
     */
    protected function getAdditionalAttributes(string $itemName): array
    {
        $attributes = [
            'data-callback-module' => 'TYPO3/CMS/L10nmgr/ContextMenuActions',
        ];
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $command = '';
        switch ($itemName) {
            case 'tx_l10nmgr_create':
                $command = 'createPriority';
                break;
            case 'tx_l10nmgr_manage':
                $command = 'managePriorities';
                break;
            case 'tx_l10nmgr_flush':
                $command = 'flushTranslations';
                break;
        }

        if ($command !== '') {
            $attributes['data-url'] = (string)$uriBuilder->buildUriFromRoute(
                'tx_l10nmgr_cm3',
                ['table' => $this->table, 'id' => $this->identifier, 'cmd' => $command],
                UriBuilder::ABSOLUTE_URL
            );
        }
        return $attributes;
    }
}
