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

require_once 'Normalizer.php';

/**
 * Implementation of PSR-4 class loader.
 */
class ClassLoader
{
    /**
     * The registered prefixes and paths to their directories.
     *
     * @var array
     */
    protected $paths = [];

    /**
     * Indexes of the namespaces.
     *
     * @var array
     */
    protected $indexes = [];

    /**
     * The classmap.
     *
     * @var array
     */
    protected $classMap = [];

    /**
     * The flag of registration with spl_autoload.
     *
     * @var bool
     */
    protected $isRegistered = false;

    /**
     * The state of automatic registration of classes.
     *
     * @var bool
     */
    protected $classRegistration = false;

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
     * Sets the state of automatic registration of classes.
     *
     * @param bool $autoregistration The state of auto registration
     *
     * @return self
     */
    public function setClassRegistrationEnabled($autoregistration = true)
    {
        $this->classRegistration = (bool) $autoregistration;

        return $this;
    }

    /**
     * Is the automatic registration of classes enabled?
     *
     * @return bool The state of auto registration
     */
    public function isClassRegistrationEnabled()
    {
        return $this->classRegistration;
    }

    /**
     * Registers a PSR-4 directory for a given namespace.
     *
     * @param string $namespace The namespace
     * @param string $path      The PSR-4 root directory
     * @param bool   $prepend   Whether to prepend the directories
     *
     * @return self
     */
    public function registerPath($namespace, $path, $prepend = false)
    {
        $namespace = Normalizer::ns($namespace, true);
        $index     = substr($namespace, 0, 4);

        $this->indexes[$index] = true;

        $path = Normalizer::path($path, true);

        if (! isset($this->paths[$namespace])) {
            $this->paths[$namespace] = [];
        }
        if ($prepend) {
            array_unshift($this->paths[$namespace], $path);

            return $this;
        }
        array_push($this->paths[$namespace], $path);

        return $this;
    }

    /**
     * Gets the registered prefixes and paths to their directories.
     *
     * @return array The array of registered prefixes and paths to
     *               their directories
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Adds the map of classes.
     *
     * @param array $map The map of classes
     *
     * @return self
     */
    public function addClassMap(array $map)
    {
        if (empty($this->classMap)) {
            $this->classMap = $map;

            return $this;
        }
        $this->classMap = array_merge($this->classMap, $map);

        return $this;
    }

    /**
     * Gets the map of classes.
     *
     * @return array The map of classes
     */
    public function getClassMap()
    {
        return $this->classMap;
    }

    /**
     * Loads the class file for a given class name.
     *
     * @param string $class The fully-qualified class name
     *
     * @return bool Returns true on success, false otherwise
     */
    public function load($class)
    {
        if ($file = $this->findFile($class)) {
            include_once $file;

            return  true;
        }

        return false;
    }

    /**
     * Finds the path to the file, that contains the specified class.
     *
     * @param string $class The fully-qualified class name
     *
     * @return string|false The path if found, false otherwise
     */
    public function findFile($class)
    {
        $class = Normalizer::ns($class, false);
        if (isset($this->classMap[$class])) {
            return $this->classMap[$class];
        }
        $index = substr($class, 0, 4);
        if (isset($this->indexes[$index])) {
            $namespace = $class;
            while (false !== $pos = strrpos($namespace, '\\')) {
                $namespace = substr($class, 0, $pos + 1);
                if (! isset($this->paths[$namespace])) {
                    $namespace = rtrim($namespace, '\\');
                    continue;
                }
                $subclass = substr($class, $pos + 1);
                $subpath  = Normalizer::path($subclass, false) . '.php';
                foreach ($this->paths[$namespace] as $dir) {
                    $path = $dir . $subpath;
                    if (is_readable($path)) {
                        if ($this->classRegistration) {
                            $this->classMap[$class] = $path;
                        }

                        return $path;
                    }
                }
                $namespace = rtrim($namespace, '\\');
            }
        }

        if ($this->classRegistration) {
            $this->classMap[$class] = false;
        }

        return false;
    }
}
