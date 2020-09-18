<?php

use Localizationteam\L10nmgr\Command\Export;
use Localizationteam\L10nmgr\Command\Import;

return [
    'l10nmanager:export' => [
        'class' => Export::class,
    ],
    'l10nmanager:import' => [
        'class' => Import::class,
    ],
];
