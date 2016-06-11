<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Loader\Test;

use Es\Loader\AutoloaderFactory;
use Es\Loader\ClassLoader;
use ReflectionProperty;

class AutoloaderFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMake()
    {
        $loader = AutoloaderFactory::make();
        $this->assertInstanceOf(ClassLoader::CLASS, $loader);

        $reflection = new ReflectionProperty($loader, 'isRegistered');
        $reflection->setAccessible(true);
        $this->assertTrue($reflection->getValue($loader));

        $second = AutoloaderFactory::make();
        $this->assertSame($loader, $second);
    }
}
