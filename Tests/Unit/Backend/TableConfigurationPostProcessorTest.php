<?php
namespace FluidTYPO3\Fluidcontent\Tests\Unit\Backend;

/*
 * This file is part of the FluidTYPO3/Fluidcontent project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Fluidcontent\Backend\TableConfigurationPostProcessor;
use FluidTYPO3\Fluidcontent\Service\ConfigurationService;
use FluidTYPO3\Development\AbstractTestCase;

/**
 * Class TableConfigurationPostProcessorTest
 */
class TableConfigurationPostProcessorTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function testGetConfigurationServiceReturnsConfigurationService()
    {
        $instance = new TableConfigurationPostProcessor();
        $result = $this->callInaccessibleMethod($instance, 'getConfigurationService');
        $this->assertInstanceOf(ConfigurationService::class, $result);
    }

    /**
     * @test
     */
    public function testProcessData()
    {
        $service = $this->getMockBuilder(ConfigurationService::class)->setMethods(['getPageTsConfig'])->getMock();
        $service->expects($this->once())->method('getPageTsConfig')->willReturn('');
        $instance = $this->getMockBuilder(TableConfigurationPostProcessor::class)->setMethods(['getConfigurationService'])->getMock();
        $instance->expects($this->once())->method('getConfigurationService')->willReturn($service);
        $instance->processData();
    }
}
