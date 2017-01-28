<?php
namespace FluidTYPO3\Fluidcontent\Hooks;

/*
 * This file is part of the FluidTYPO3/Fluidcontent project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class GroupAccessListPostProcessor
 */
class GroupAccessListPostProcessor
{

    /**
     * @param array $parameters
     * @param BackendUserAuthentication $reference
     * @return void
     */
    public function addAccessLists(array $parameters, BackendUserAuthentication $reference)
    {
        $blacklist = [];
        $whitelist = [];
        foreach ($reference->userGroups as $groupData) {
            $blacklist = array_merge(
                $blacklist,
                GeneralUtility::trimExplode(',', $groupData['tx_fluidcontent_deniedfluidcontent'])
            );
            $whitelist = array_merge(
                $whitelist,
                GeneralUtility::trimExplode(',', $groupData['tx_fluidcontent_allowedfluidcontent'])
            );
        }
        $reference->dataLists['fluidcontentBlacklist'] = array_unique(array_diff($blacklist, ['']));
        $reference->dataLists['fluidcontentWhitelist'] = array_unique(array_diff($whitelist, ['']));
    }
}
