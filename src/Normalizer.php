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
 * Normalizes paths and namespaces.
 */
class Normalizer
{
    /**
     * Normalize the path.
     *
     * @param string $path          The path in the filesystem
     * @param bool   $trailingSlash Whether trailing slash should be included
     *
     * @return string Normalized path
     */
    public static function path($path, $trailingSlash = true)
    {
        $path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        if ($trailingSlash) {
            $path .= DIRECTORY_SEPARATOR;
        }

        return $path;
    }

    /**
     * Normalize the namespace.
     *
     * @param string $namespace         The namespace
     * @param bool   $trailingBackslach Whether trailing backslash should be included
     *
     * @return string Normalized namespace
     */
    public static function ns($namespace, $trailingBackslach = false)
    {
        $namespace = trim($namespace, '\\');
        if ($trailingBackslach) {
            $namespace = $namespace . '\\';
        }

        return $namespace;
    }
}
