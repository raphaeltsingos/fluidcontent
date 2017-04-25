<?php
namespace FluidTYPO3\Fluidcontent\Tests\Unit\Provider;

/*
 * This file is part of the FluidTYPO3/Fluidcontent project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Fluidcontent\Hooks\WizardItemsHookSubscriber;
use FluidTYPO3\Fluidcontent\Service\ConfigurationService;
use FluidTYPO3\Development\AbstractTestCase;
use FluidTYPO3\Flux\Form\Container\Column;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form\Container\Row;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Database\PreparedStatement;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class WizardItemsHookSubscriberTest
 */
class WizardItemsHookSubscriberTest extends AbstractTestCase
{

    public function testCreatesInstance()
    {
        $GLOBALS['TYPO3_DB'] = $this->getMockBuilder(DatabaseConnection::class)
            ->setMethods(['prepare_SELECTquery'])
            ->disableOriginalConstructor()
            ->getMock();
        $preparedStatementMock = $this->getMockBuilder(PreparedStatement::class)
            ->setMethods(['execute', 'fetch', 'free'])
            ->disableOriginalConstructor()
            ->getMock();
        $preparedStatementMock->expects($this->any())->method('execute')->willReturn(false);
        $preparedStatementMock->expects($this->any())->method('free');
        $preparedStatementMock->expects($this->any())->method('fetch')->willReturn(false);
        ;
        $GLOBALS['TYPO3_DB']->expects($this->any())->method('prepare_SELECTquery')->willReturn($preparedStatementMock);
        $instance = GeneralUtility::makeInstance(ObjectManager::class)->get(WizardItemsHookSubscriber::class);
        $this->assertInstanceOf(WizardItemsHookSubscriber::class, $instance);
    }

    /**
     * @dataProvider getTestElementsWhiteAndBlackListsAndExpectedList
     * @test
     * @param array $items
     * @param string $whitelist
     * @param string $blacklist
     * @param array $expectedList
     */
    public function processesWizardItems($items, $whitelist, $blacklist, $expectedList)
    {
        $GLOBALS['LOCAL_LANG'] = new \stdClass();
        $GLOBALS['BE_USER'] = new BackendUserAuthentication();
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var WizardItemsHookSubscriber $instance */
        $instance = $objectManager->get(WizardItemsHookSubscriber::class);
        $emulatedPageAndContentRecord = ['uid' => 1, 'tx_flux_column' => 'name'];
        $controller = $this->getMockBuilder(NewContentElementController::class)->setMethods(['init'])->disableOriginalConstructor()->getMock();
        $controller->colPos = 0;
        $controller->uid_pid = -1;
        $grid = new Grid();
        $row = new Row();
        $column = new Column();
        $column->setColumnPosition(0);
        $column->setName('name');
        $column->setVariable('Fluidcontent', [
            'allowedContentTypes' => $whitelist,
            'deniedContentTypes' => $blacklist
        ]);
        $row->add($column);
        $grid->add($row);
        /** @var Provider $provider1 */
        $provider1 = $objectManager->get(Provider::class);
        $provider1->setTemplatePaths([]);
        $provider1->setTemplateVariables([]);
        $provider1->setGrid($grid);
        $provider2 = $this->getMockBuilder(Provider::class)->setMethods(['getGrid'])->getMock();
        $provider2->expects($this->exactly(1))->method('getGrid')->will($this->returnValue(null));
        /** @var ConfigurationService|\PHPUnit_Framework_MockObject_MockObject $configurationService */
        $configurationService = $this->getMockBuilder(ConfigurationService::class)
            ->setMethods(['resolveConfigurationProviders', 'writeCachedConfigurationIfMissing'])
            ->getMock();
        $configurationService->expects($this->exactly(1))->method('resolveConfigurationProviders')
            ->will($this->returnValue([$provider1, $provider2]));
        /** @var WorkspacesAwareRecordService|\PHPUnit_Framework_MockObject_MockObject $recordService */
        $recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)->setMethods(['getSingle'])->getMock();
        $recordService->expects($this->exactly(2))->method('getSingle')->will($this->returnValue($emulatedPageAndContentRecord));
        $instance->injectConfigurationService($configurationService);
        $instance->injectRecordService($recordService);
        $instance->manipulateWizardItems($items, $controller);
        $this->assertEquals($expectedList, $items);
    }

    /**
     * @return array
     */
    public function getTestElementsWhiteAndBlackListsAndExpectedList()
    {
        $items = [
            'plugins' => ['title' => 'Nice header'],
            'plugins_test1' => [
                'tt_content_defValues' => ['CType' => 'fluidcontent_content', 'tx_fed_fcefile' => 'test1:test1']
            ],
            'plugins_test2' => [
                'tt_content_defValues' => ['CType' => 'fluidcontent_content', 'tx_fed_fcefile' => 'test2:test2']
            ]
        ];
        return [
            [
                $items,
                null,
                null,
                $items,
            ],
            [
                $items,
                'test1:test1',
                null,
                [
                    'plugins' => ['title' => 'Nice header'],
                    'plugins_test1' => $items['plugins_test1']
                ],
            ],
            [
                $items,
                null,
                'test1:test1',
                [
                    'plugins' => ['title' => 'Nice header'],
                    'plugins_test2' => $items['plugins_test2']
                ],
            ],
            [
                $items,
                'test1:test1',
                'test1:test1',
                [],
            ],
        ];
    }

    public function testManipulateWizardItemsCallsExpectedMethodSequenceWithoutProviders()
    {
        $GLOBALS['BE_USER'] = new BackendUserAuthentication();
        /** @var WizardItemsHookSubscriber $instance */
        $instance = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(WizardItemsHookSubscriber::class);
        /** @var ConfigurationService|\PHPUnit_Framework_MockObject_MockObject $configurationService */
        $configurationService = $this->getMockBuilder(ConfigurationService::class)
            ->setMethods(['writeCachedConfigurationIfMissing', 'resolveConfigurationProviders'])
            ->getMock();
        /** @var WorkspacesAwareRecordService|\PHPUnit_Framework_MockObject_MockObject $recordService */
        $recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)->setMethods(['getSingle'])->getMock();
        $configurationService->expects($this->once())->method('writeCachedConfigurationIfMissing');
        $configurationService->expects($this->once())->method('resolveConfigurationProviders')->willReturn([]);
        $recordService->expects($this->once())->method('getSingle')->willReturn(null);
        $instance->injectConfigurationService($configurationService);
        $instance->injectRecordService($recordService);
        $parent = $this->getMockBuilder(NewContentElementController::class)
            ->setMethods(['init'])
            ->disableOriginalConstructor()
            ->getMock();
        $items = [];
        $instance->manipulateWizardItems($items, $parent);
    }

    public function testManipulateWizardItemsCallsExpectedMethodSequenceWithProvidersWithColPosWithoutRelativeElement()
    {
        $GLOBALS['BE_USER'] = new BackendUserAuthentication();
        /** @var WizardItemsHookSubscriber $instance */
        $instance = GeneralUtility::makeInstance(ObjectManager::class)->get(WizardItemsHookSubscriber::class);
        /** @var ConfigurationService|\PHPUnit_Framework_MockObject_MockObject $configurationService */
        $configurationService = $this->getMockBuilder(ConfigurationService::class)
            ->setMethods(['writeCachedConfigurationIfMissing', 'resolveConfigurationProviders'])
            ->getMock();
        /** @var WorkspacesAwareRecordService|\PHPUnit_Framework_MockObject_MockObject $recordService */
        $recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->setMethods(['getSingle'])
            ->getMock();
        $record = ['uid' => 0];
        $provider1 = $this->getMockProvider($record);
        $provider2 = $this->getMockProvider($record);
        $provider3 = $this->getMockProvider($record, false);
        $configurationService->expects($this->once())->method('writeCachedConfigurationIfMissing');
        $configurationService->expects($this->once())->method('resolveConfigurationProviders')->willReturn([
            $provider1, $provider2, $provider3
        ]);
        $recordService->expects($this->once())->method('getSingle')->willReturn($record);
        $instance->injectConfigurationService($configurationService);
        $instance->injectRecordService($recordService);
        $parent = $this->getMockBuilder(NewContentElementController::class)
            ->setMethods(['init'])
            ->disableOriginalConstructor()
            ->getMock();
        $parent->colPos = 1;
        $items = [];
        $instance->manipulateWizardItems($items, $parent);
    }

    /**
     * @param array $record
     * @param boolean $withGrid
     * @return Provider
     */
    protected function getMockProvider(array $record, $withGrid = true)
    {
        $instance = $this->getMockBuilder(Provider::class)->setMethods(['getViewVariables', 'getGrid'])->getMock();
        if (false === $withGrid) {
            $instance->expects($this->any())->method('getGrid')->willReturn(null);
        } else {
            $grid = Grid::create();
            $grid->createContainer('Row', 'row')->createContainer('Column', 'column')->setColumnPosition(1)
                ->setVariable('Fluidcontent', ['deniedContentTypes' => 'html', 'allowedContentTypes' => 'text']);
            $instance->expects($this->any())->method('getGrid')->willReturn($grid);
        }
        return $instance;
    }
}
