<?php

namespace Localizationteam\L10nmgr\Traits;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

trait BackendUserTrait
{
    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
