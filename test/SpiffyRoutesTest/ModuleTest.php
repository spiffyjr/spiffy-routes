<?php

namespace SpiffyRoutesTest;

use Zend\Console\ColorInterface;
use SpiffyRoutes\Module;
use SpiffyTest\Framework\TestCase;

class ModuleTest extends TestCase
{
    public function testCliOutput()
    {
        $console  = $this->getServiceManager()->get('Console');
        $expected = array(
            $console->colorize('Usage:', ColorInterface::YELLOW),
            '  [options] command [arguments]',
            '',
            $console->colorize('Available Commands:', ColorInterface::YELLOW),
            array($console->colorize('  spiffyroutes build', ColorInterface::GREEN), 'build, or rebuild if present, the cache'),
            array($console->colorize('  spiffyroutes clear', ColorInterface::GREEN), 'clear the cache'),
        );

        $module = new Module();
        $result = $module->getConsoleUsage($console);

        $this->assertEquals($expected, $result);
    }
}