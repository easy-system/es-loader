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

use Es\Loader\ClassLoader;
use Es\Loader\Normalizer;
use ReflectionProperty;

class ClassLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testRegister()
    {
        $loader = new ClassLoader();
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
        $loader = new ClassLoader();
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

    public function testRegisterPathAppend()
    {
        $config = [
            'Test\\Foo'   => 'Path/To/Foo',
            'Test\\Foo\\' => 'OtherPath/To/Foo',
            'Test\\Bar'   => 'Path/To/Bar',
            'Test\\Baz'   => 'Path/To/Baz',
        ];
        $loader = new ClassLoader();
        foreach ($config as $namespace => $path) {
            $return = $loader->registerPath($namespace, $path);
            $this->assertSame($return, $loader);
        }

        $expected = [];
        foreach ($config as $namespace => $path) {
            $namespace = Normalizer::ns($namespace, true);
            $path      = Normalizer::path($path, true);
            if (! isset($expected[$namespace])) {
                $expected[$namespace] = [];
            }
            array_push($expected[$namespace], $path);
        }

        $this->assertEquals($expected, $loader->getPaths());
    }

    public function testRegisterPathPrepend()
    {
        $config = [
            'Test\\Foo'   => 'Path/To/Foo',
            'Test\\Foo\\' => 'OtherPath/To/Foo',
            'Test\\Bar'   => 'Path/To/Bar',
            'Test\\Baz'   => 'Path/To/Baz',
        ];
        $loader = new ClassLoader();
        foreach ($config as $namespace => $path) {
            $return = $loader->registerPath($namespace, $path, true);
            $this->assertSame($return, $loader);
        }

        $expected = [];
        foreach ($config as $namespace => $path) {
            $namespace = Normalizer::ns($namespace, true);
            $path      = Normalizer::path($path, true);
            if (! isset($expected[$namespace])) {
                $expected[$namespace] = [];
            }
            array_unshift($expected[$namespace], $path);
        }

        $this->assertEquals($expected, $loader->getPaths());
    }

    public function testFindFileReturnPathToFileIfFileFound()
    {
        $loader = \Es\Loader\AutoloaderFactory::make();
        $prefix = 'Es\\Loader\\Test\\Files\\Foo';
        $class  = 'Es\\Loader\\Test\\Files\\Foo\\SomeClass';

        $path = __DIR__ . PHP_DS . 'files' . PHP_DS . 'Foo' . PHP_DS . 'src' . PHP_DS;

        $file = $path . 'SomeClass.php';

        $loader->registerPath($prefix, $path);
        $this->assertEquals($loader->findFile($class), $file);
    }

    public function testFindFileReturnFalseIfFileNotFound()
    {
        $loader = new ClassLoader();
        $prefix = 'Es\\Loader\\Test\\Files\\Foo';
        $class  = 'Es\\Loader\\Test\\Files\\Foo\\SomeelseClass';

        $path = __DIR__ . PHP_DS . 'files' . PHP_DS . 'Foo' . PHP_DS . 'src' . PHP_DS;

        $loader->registerPath($prefix, $path);
        $this->assertFalse($loader->findFile($class));
    }

    public function testFindFileFindsFileInDirectories()
    {
        $loader = new ClassLoader();
        $prefix = 'Es\\Loader\\Test\\Files\\Bar';
        $class  = 'Es\\Loader\\Test\\Files\\Bar\\HideawayClass';

        $pathToBar = __DIR__ . PHP_DS . 'files' . PHP_DS . 'Bar' . PHP_DS;

        $file = $pathToBar . 'src' . PHP_DS . 'HideawayClass.php';

        $barDirectories = [
            'app',
            'lib',
            'src',
            'web',
        ];

        foreach ($barDirectories as $dir) {
            $loader->registerPath($prefix, $pathToBar . $dir);
        }

        $this->assertEquals($file, $loader->findFile($class));
    }

    public function testFindFileFindsSubnamespacedClass()
    {
        $loader = new ClassLoader();
        $prefix = 'Es\\Loader\\Test\\Files\\Baz';
        $class  = 'Es\\Loader\\Test\\Files\\Baz\\Bat\\SubnamespacedClass';

        $path = __DIR__ . PHP_DS . 'files' . PHP_DS . 'Baz' . PHP_DS . 'src' . PHP_DS;

        $file = $path . 'Bat' . PHP_DS . 'SubnamespacedClass.php';

        $loader->registerPath($prefix, $path);

        $this->assertEquals($file, $loader->findFile($class));
    }

    public function testAddClassMapGetClassMap()
    {
        $first = [
            'FooClass' => 'FooPath',
            'BarClass' => 'BarPath',
        ];
        $loader = new ClassLoader();
        $loader->addClassMap($first);
        $this->assertEquals($first, $loader->getClassMap());
        //
        $second = [
            'BazClass' => 'BazPath',
            'BatClass' => 'BatPath',
            'FooClass' => 'OtherFooPath',
        ];
        $loader->addClassMap($second);
        $expected = array_merge($first, $second);
        $this->assertEquals($expected, $loader->getClassMap());
    }

    public function testFindFileFindsFileIfItPresentsInClassMap()
    {
        $class = 'Es\\Loader\\Test\\Files\\Foo\\SomeClass';
        $file  = __DIR__ . PHP_DS . 'files' . PHP_DS . 'Foo' . PHP_DS
               . 'src' . PHP_DS . 'SomeClass.php';


        $classMap = [
            $class => $file,
        ];
        $loader = new ClassLoader();
        $loader->addClassMap($classMap);
        $this->assertEquals($loader->findFile($class), $file);
    }

    public function testSetClassRegistrationEnabledIsClassRegistrationEnabled()
    {
        $loader = new ClassLoader();
        $this->assertFalse($loader->isClassRegistrationEnabled());
        //
        $loader->setClassRegistrationEnabled();
        $this->assertTrue($loader->isClassRegistrationEnabled());
        //
        $loader->setClassRegistrationEnabled(false);
        $this->assertFalse($loader->isClassRegistrationEnabled());
    }

    public function testFindFileAutoregisterClassIfClassRegistrationEnabled()
    {
        $namespace = 'Es\\Loader\\Test\\Files\\Foo\\';
        $class     = 'Es\\Loader\\Test\\Files\\Foo\\SomeClass';

        $path = __DIR__ . PHP_DS . 'files' . PHP_DS . 'Foo' . PHP_DS . 'src' . PHP_DS;

        $file = $path . 'SomeClass.php';

        $loader = new ClassLoader();
        $loader->registerPath($namespace, $path);
        $loader->setClassRegistrationEnabled();

        $this->assertSame($loader->findFile($class), $file);
        $this->assertFalse($loader->findFile('Foo'));

        $expected = [
            $class => $file,
            'Foo'  => false,
        ];
        $this->assertEquals($expected, $loader->getClassMap());
    }

    public function testLoadReturnFalseIfFileNotFound()
    {
        $loader = new ClassLoader();
        $this->assertFalse($loader->load('Foo'));
    }

    public function testLoadReturnTrueIfFoundFile()
    {
        $namespace = 'Es\\Loader\\Test\\Files\\Foo\\';
        $class     = 'Es\\Loader\\Test\\Files\\Foo\\SomeClass';

        $path = __DIR__ . PHP_DS . 'files' . PHP_DS . 'Foo' . PHP_DS . 'src' . PHP_DS;

        $file = $path . 'SomeClass.php';

        $loader = new ClassLoader();
        $loader->registerPath($namespace, $path);
        $this->assertEquals($loader->findFile($class), $file);
        $this->assertTrue($loader->load($class));
    }
}
