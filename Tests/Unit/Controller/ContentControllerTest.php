<?php
namespace FluidTYPO3\Fluidcontent\Tests\Unit\Controller;

/*
 * This file is part of the FluidTYPO3/Fluidcontent project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Fluidcontent\Controller\ContentController;
use FluidTYPO3\Development\AbstractTestCase;
use FluidTYPO3\Flux\Configuration\ConfigurationManager;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Reflection\PropertyReflection;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3\CMS\Fluid\View\TemplateView;

/**
 * Class ContentControllerTest
 */
class ContentControllerTest extends AbstractTestCase
{

    public function testInitializeView()
    {

        /** @var ContentController|\PHPUnit_Framework_MockObject_MockObject $instance */
        $instance = $this->getMockBuilder(ContentController::class)
            ->setMethods(
                [
                    'getRecord', 'initializeProvider', 'initializeSettings', 'initializeOverriddenSettings',
                    'initializeViewObject', 'initializeViewVariables', 'initializeViewHelperVariableContainer'
                ]
            )->getMock();
        $request = $this->getMockBuilder(Request::class)->getMock();
        $viewProperty = new PropertyReflection(ContentController::class, 'request');
        $viewProperty->setAccessible(true);
        $viewProperty->setValue($instance, $request);
        /** @var ConfigurationManager|\PHPUnit_Framework_MockObject_MockObject $configurationManager */
        $configurationManager = $this->getMockBuilder(ConfigurationManager::class)
            ->setMethods(['getContentObject', 'getConfiguration'])
            ->getMock();
        $contentObject = new \stdClass();
        $configurationManager->expects($this->once())->method('getContentObject')->willReturn($contentObject);
        $configurationManager->expects($this->once())->method('getConfiguration')->willReturn(['foo' => 'bar']);
        $instance->expects($this->atLeastOnce())->method('getRecord')->willReturn(['uid' => 0]);
        $GLOBALS['TSFE'] = (object) ['page' => 'page', 'fe_user' => (object) ['user' => 'user']];

        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->expects($this->once())->method('getControllerExtensionKeyFromRecord')->willReturn('test');

        $paths = $this->getMockBuilder(TemplatePaths::class)->setMethods(['fillDefaultsByPackageName'])->getMock();
        $controllerContext = $this->getMockBuilder(ControllerContext::class)->setMethods(['getRequest'])->getMock();
        $context = $this->getMockBuilder(RenderingContext::class)->setMethods(['getTemplatePaths', 'setControllerAction'])->getMock();

        $controllerContext->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);

        $context->expects($this->once())->method('setControllerAction');
        $context->expects($this->atLeastOnce())->method('getTemplatePaths')->willReturn($paths);

        /** @var ExposedTemplateView|\PHPUnit_Framework_MockObject_MockObject $view */
        $view = $this->getMockBuilder(TemplateView::class)->setMethods(['assign'])->getMock();
        $view->setRenderingContext($context);
        $view->expects($this->at(0))->method('assign')->with('page', 'page');
        $view->expects($this->at(1))->method('assign')->with('user', 'user');
        $view->expects($this->at(2))->method('assign')->with('record', ['uid' => 0]);
        $view->expects($this->at(3))->method('assign')->with('contentObject', $contentObject);
        $view->expects($this->at(4))->method('assign')->with('cookies', $_COOKIE);
        $view->expects($this->at(5))->method('assign')->with('session', $_SESSION);

        ObjectAccess::setProperty($instance, 'provider', $provider, true);
        ObjectAccess::setProperty($instance, 'controllerContext', $controllerContext, true);
        $instance->injectConfigurationManager($configurationManager);
        $instance->initializeView($view);
    }
}
