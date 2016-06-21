<?php
namespace FluidTYPO3\Fluidcontent\Backend;

/*
 * This file is part of the FluidTYPO3/Fluidcontent project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Service\RecordService;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class ContentTypeFilter
 */
class ContentTypeFilter
{

    /**
     * @var array
     */
    protected $items = [];

    /**
     * @param array $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @param Grid $grid
     * @param string $column
     * @return array
     */
    public function extractBlacklistAndWhitelistFromGridColumns(Grid $grid, $column)
    {
        $blacklist = [];
        $whitelist = [];
        foreach ($grid->getRows() as $gridRow) {
            foreach ($gridRow->getColumns() as $gridColumn) {
                $blacklistedTypes = $gridColumn->getVariable('Fluidcontent.deniedContentTypes');
                $whitelistedTypes = $gridColumn->getVariable('Fluidcontent.allowedContentTypes');
                if (!empty($blacklistedTypes)) {
                    $blacklist += GeneralUtility::trimExplode(',', $blacklistedTypes);
                }
                if (!empty($whitelistedTypes)) {
                    $whitelist += GeneralUtility::trimExplode(',', $whitelistedTypes);
                }
            }
        }
        $blacklist = array_unique($blacklist);
        $whitelist = array_unique($whitelist);
        return array($blacklist, $whitelist);
    }

    /**
     * @return array
     */
    public function extractBlacklistAndWhitelistFromCurrentBackendUser()
    {
        $user = $this->getCurrentBackendUser();
        if (!$user->isAdmin()) {
            return [$user->dataLists['fluidcontentBlacklist'], $user->dataLists['fluidcontentWhitelist']];
        }
        return [[], []];
    }

    /**
     * @param Grid $grid
     * @param string $columnName
     * @return array
     */
    public function filterItemsByGridColumn(Grid $grid, $columnName)
    {
        list ($blacklist, $whitelist) = $this->extractBlacklistAndWhitelistFromGridColumns($grid, $columnName);
        return $this->filterByBlacklistAndWhitelist($blacklist, $whitelist);
    }

    /**
     * @return array
     */
    public function filterItemsByGroupAccess()
    {
        list ($blacklist, $whitelist) = $this->extractBlacklistAndWhitelistFromCurrentBackendUser();
        return $this->filterByBlacklistAndWhitelist($blacklist, $whitelist);
    }

    /**
     * @param array $whitelist
     * @param array $blacklist
     * @return array
     */
    public function filterByBlacklistAndWhitelist(array $whitelist, array $blacklist)
    {
        $items = $this->items;
        if (!empty($whitelist) || !empty($blacklist)) {
            foreach ($items as $index => $item) {
                $whitelisted = in_array($item[1], $whitelist, true) || empty($whitelist);
                $blacklisted = in_array($item[1], $blacklist, true);
                if (!$blacklisted && $whitelisted) {
                    unset($items[$index]);
                }
            }
        }
        return $items;
    }

    /**
     * @return RecordService
     */
    protected function getRecordService()
    {
        return GeneralUtility::makeInstance(ObjectManager::class)->get(RecordService::class);
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getCurrentBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
}
