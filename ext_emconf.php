<?php
/***************************************************************
 * Extension Manager/Repository config file for ext "l10nmgr".
 * Auto generated 10-03-2015 18:54
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/
$EM_CONF['l10nmgr'] = [
    'title'            => 'Localization Manager',
    'description'      => 'Module for managing localization import and export',
    'category'         => 'module',
    'version'          => '10.2.0',
    'state'            => 'stable',
    'uploadfolder'     => false,
    'clearCacheOnLoad' => true,
    'author'           => 'Kasper Skaarhoej, Daniel Zielinski, Daniel Poetzinger, Fabian Seltmann, Andreas Otto, Jo Hasenau, Peter Russ',
    'author_email'     => 'kasperYYYY@typo3.com, info@loctimize.com, info@cybercraft.de, pruss@uon.li',
    'author_company'   => 'Localization Manager Team',
    'constraints'      => [
        'depends'   => [
            'typo3'              => '10.0.0-10.99.99',
            'scheduler'          => '10.0.0-10.99.99',
        ],
        'conflicts' => [],
        'suggests'  => [],
    ],
];
