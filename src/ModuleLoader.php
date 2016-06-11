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
 * The Loader of "Module" classes.
 */
class ModuleLoader
{
    /**
     * An array of module paths to scan.
     *
     * @var array
     */
    protected $paths = [];

    /**
     * The flag of registration with spl_autoload.
     *
     * @var bool
     */
    protected $isRegistered = false;

    /**
     * Register the autoloader with spl_autoload.
     *
     * @return self
     */
    public function register()
    {
        if (! $this->isRegistered) {
            spl_autoload_register([$this, 'load'], true, true);
            $this->isRegistered = true;
        }

        return $this;
    }

    /**
     * Unregister the autoloader with spl_autoload.
     *
     * @return self
     */
    public function unregister()
    {
        if ($this->isRegistered) {
            spl_autoload_unregister([$this, 'load']);
            $this->isRegistered = false;
        }

        return $this;
    }

    /**
     * Register paths for search of modules.
     *
     * @param array $paths An array of module paths to scan
     *
     * @return self
     */
    public function registerPaths(array $paths)
    {
        foreach ($paths as $path) {
            $this->registerPath($path);
        }

        return $this;
    }

    /**
     * Register an path for search of modules.
     *
     * @param string $path The module path to scan
     *
     * @return self
     */
    public function registerPath($path)
    {
        $this->paths[] = Normalizer::path((string) $path, true);

        return $this;
    }

    /**
     * Gets of module paths to scan.
     *
     * @return array An array of module paths to scan
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Loads a Module class.
     *
     * @param string $class The name of the Module class
     *
     * @return bool True on success, false otherwise
     */
    public function load($class)
    {
        if ($file = $this->findFile($class)) {
            include_once $file;

            return true;
        }

        return false;
    }

    /**
     * Finds the path to the file where the Module class is defined.
     *
     * @param string $class The name of the Module class
     *
     * @return string|false The path if found, false otherwise
     */
    public function findFile($class)
    {
        if (substr($class, -7) !== '\\Module') {
            return false;
        }

        $path = Normalizer::path($class, false) . '.php';
        foreach ($this->paths as $dir) {
            $file = $dir . $path;
            if (is_readable($file)) {
                return $file;
            }
        }

        return false;
    }
}
