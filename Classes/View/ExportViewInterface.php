<?php

declare(strict_types=1);

namespace Localizationteam\L10nmgr\View;

/***************************************************************
 * Copyright notice
 * (c) 2018 B13
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
interface ExportViewInterface
{
    /**
     * @param int $forceLanguage
     * @return mixed
     */
    public function setForcedSourceLanguage(int $forceLanguage);

    /**
     * @return mixed
     */
    public function setModeOnlyChanged();

    /**
     * @return mixed
     */
    public function setModeNoHidden();

    /**
     * @return mixed
     */
    public function saveExportInformation();

    /**
     * @return mixed
     */
    public function render();

    /**
     * @return mixed
     */
    public function checkExports();

    /**
     * @return mixed
     */
    public function renderExportsCli();

    /**
     * @return mixed
     */
    public function getFileName();
}
