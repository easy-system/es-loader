<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Loader;

/**
 * Factory of autoloader.
 */
class AutoloaderFactory
{
    /**
     * The class loader.
     *
     * @var \Es\Loader\ClassLoader
     */
    protected static $loader;

    /**
     * Makes autoloader.
     *
     * @return ClassLoader The class loader
     */
    public static function make()
    {
        if (! static::$loader) {
            $loader = new ClassLoader();
            $loader->register();
            static::$loader = $loader;
        }

        return static::$loader;
    }
}
