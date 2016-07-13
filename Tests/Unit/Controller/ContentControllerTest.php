<?php
namespace FluidTYPO3\Fluidcontent\Tests\Unit\Controller;

/*
 * This file is part of the FluidTYPO3/Fluidcontent project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Fluidcontent\Controller\ContentController;
use FluidTYPO3\Flux\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class ContentControllerTest
 */
class ContentControllerTest extends UnitTestCase
{

    public function testInitializeView()
    {

        /** @var ContentController|\PHPUnit_Framework_MockObject_MockObject $instance */
        $instance = $this->getMockBuilder(ContentController::class)
            ->setMethods(
                [
                    'getRecord', 'initializeProvider', 'initializeSettings', 'initializeOverriddenSettings',
                    'initializeViewObject', 'initializeViewVariables'
                ]
            )->getMock();
        /** @var ConfigurationManager|\PHPUnit_Framework_MockObject_MockObject $configurationManager */
        $configurationManager = $this->getMockBuilder(ConfigurationManager::class)
            ->setMethods(['getContentObject', 'getConfiguration'])
            ->getMock();
        $contentObject = new \stdClass();
        $configurationManager->expects($this->once())->method('getContentObject')->willReturn($contentObject);
        $configurationManager->expects($this->once())->method('getConfiguration')->willReturn(['foo' => 'bar']);
        $instance->expects($this->once())->method('getRecord')->willReturn(['uid' => 0]);
        $GLOBALS['TSFE'] = (object) ['page' => 'page', 'fe_user' => (object) ['user' => 'user']];
        /** @var StandaloneView|\PHPUnit_Framework_MockObject_MockObject $view */
        $view = $this->getMockBuilder(StandaloneView::class)->setMethods(['assign'])->getMock();
        $instance->injectConfigurationManager($configurationManager);
        $view->expects($this->at(0))->method('assign')->with('page', 'page');
        $view->expects($this->at(1))->method('assign')->with('user', 'user');
        $view->expects($this->at(2))->method('assign')->with('record', ['uid' => 0]);
        $view->expects($this->at(3))->method('assign')->with('contentObject', $contentObject);
        $view->expects($this->at(4))->method('assign')->with('cookies', $_COOKIE);
        $view->expects($this->at(5))->method('assign')->with('session', $_SESSION);
        $instance->initializeView($view);
    }
}
