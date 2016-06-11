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

use Es\Loader\Normalizer;

class NormalizerTest extends \PHPUnit_Framework_TestCase
{
    public function testPathWithTrailingSlash()
    {
        $path = 'foo/bar\\baz//';

        $expected = 'foo' . DIRECTORY_SEPARATOR
                  . 'bar' . DIRECTORY_SEPARATOR
                  . 'baz' . DIRECTORY_SEPARATOR;

        $this->assertEquals($expected, Normalizer::path($path));
    }

    public function testPathWithoutTrailingSlash()
    {
        $path = 'foo/bar\\baz//';

        $expected = 'foo' . DIRECTORY_SEPARATOR
                  . 'bar' . DIRECTORY_SEPARATOR
                  . 'baz';

        $this->assertEquals($expected, Normalizer::path($path, false));
    }

    public function testNsWithTrailingBackslash()
    {
        $namespace = '\\Foo\\Bar\\Baz';
        $expected  = 'Foo\\Bar\\Baz\\';
        $this->assertEquals($expected, Normalizer::ns($namespace, true));
    }

    public function testNsWithoutTrailingBackslash()
    {
        $class    = '\\Foo\\Bar\\Baz\\';
        $expected = 'Foo\\Bar\\Baz';
        $this->assertEquals($expected, Normalizer::ns($class));
    }
}
