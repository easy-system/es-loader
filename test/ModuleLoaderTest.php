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

use Es\Loader\ModuleLoader;
use Es\Loader\Normalizer;
use ReflectionProperty;

class ModuleLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testRegister()
    {
        $loader = new ModuleLoader();
        $return = $loader->register();
        $this->assertSame($return, $loader);

        $reflection = new ReflectionProperty($loader, 'isRegistered');
        $reflection->setAccessible(true);
        $this->assertTrue($reflection->getValue($loader));

        $found = false;
        foreach (spl_autoload_functions() as $item) {
            if (is_array($item) && isset($item[0])) {
                if ($item[0] === $loader) {
                    $found = true;
                    break;
                }
            }
        }
        $this->assertTrue($found);

        $loader->unregister();
    }

    public function testUnregister()
    {
        $loader = new ModuleLoader();
        $loader->register();

        $reflection = new ReflectionProperty($loader, 'isRegistered');
        $reflection->setAccessible(true);
        $this->assertTrue($reflection->getValue($loader));

        $return = $loader->unregister();
        $this->assertSame($return, $loader);
        $this->assertFalse($reflection->getValue($loader));

        $found = false;
        foreach (spl_autoload_functions() as $item) {
            if (is_array($item) && isset($item[0])) {
                if ($item[0] === $loader) {
                    $found = true;
                    break;
                }
            }
        }
        $this->assertFalse($found);
    }

    public function testRegisterPath()
    {
        $loader = new ModuleLoader();
        $return = $loader->registerPath('foo');
        $this->assertSame($return, $loader);

        $paths = $loader->getPaths();
        $path  = array_pop($paths);
        $this->assertEquals($path, Normalizer::path('foo'));
    }

    public function testRegisterPaths()
    {
        $paths = [
            'foo',
            'bar/baz',
            'bat/ban/banan',
        ];
        $loader = new ModuleLoader();
        $return = $loader->registerPaths($paths);
        $this->assertSame($return, $loader);

        $expected = [];
        foreach ($paths as $path) {
            $expected[] = Normalizer::path($path);
        }
        $this->assertEquals($expected, $loader->getPaths());
    }

    public function testFindFileReturnsPathIfModuleFound()
    {
        $path   = __DIR__ . PHP_DS . 'files' . PHP_DS;
        $file   = $path . 'FooModule' . PHP_DS . 'Module.php';
        $class  = 'FooModule\\Module';
        $loader = new ModuleLoader();
        $loader->registerPath($path);
        $this->assertEquals($loader->findFile($class), $file);
    }

    public function testFindFileReturnsFalseIfModuleNotFound()
    {
        $class  = 'Foo\\Module';
        $loader = new ModuleLoader();
        $this->assertFalse($loader->findFile($class));
    }

    public function testFindFileReturnsFalseIfClassIsNotModuleClass()
    {
        $path   = __DIR__ . PHP_DS . 'files' . PHP_DS;
        $class  = 'BarModule\\SomeClass';
        $loader = new ModuleLoader();
        $loader->registerPath($path);
        $this->assertFalse($loader->findFile($class));
    }

    public function testLoadReturnsFalseIfModuleClassNotFound()
    {
        $loader = new ModuleLoader();
        $this->assertFalse($loader->load('Baz\\Module'));
    }

    public function testLoadReturnsTrueIfFindModuleClass()
    {
        $path   = __DIR__ . PHP_DS . 'files' . PHP_DS;
        $file   = $path . 'FooModule' . PHP_DS . 'Module.php';
        $class  = 'FooModule\\Module';
        $loader = new ModuleLoader();
        $loader->registerPath($path);
        $this->assertEquals($loader->findFile($class), $file);
        $this->assertTrue($loader->load($class));
    }
}
