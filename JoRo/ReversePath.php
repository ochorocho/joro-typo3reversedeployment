<?php

namespace JoRo;

Class ReversePath
{

    /**
     * Get reverse deployment path
     *
     * @param null $path
     * @return array
     * @throws InvalidConfigurationException
     */
    public function getDeploymentNames($path = null): array
    {
        $path = $this->getDeploymentsBasePath($path);
        $files = glob(self::concatenatePaths([$path, '*.php']));
        return array_map(function ($file) use ($path) {
            return substr($file, strlen($path) + 1, -4);
        }, $files);
    }

    /**
     * Get the root path of the reverse deployment declarations
     *
     * This defaults to ./.reverse if a NULL path is given.
     *
     * @param string|null $path An absolute path (optional)
     * @return string The configuration root path without a trailing slash.
     * @throws InvalidConfigurationException
     */
    public function getDeploymentsBasePath(?string $path = null): string
    {
        $localDeploymentDescription = @realpath('./.reverse');
        if (!$path && is_dir($localDeploymentDescription)) {
            $path = (string)$localDeploymentDescription;
        }
        $path = $path ?: $this->getHomeDir();
        $this->ensureDirectoryExists($path);
        return $path;
    }

    /**
     * Get home directory
     *
     * @return string
     * @throws InvalidConfigurationException
     */
    protected function getHomeDir(): string
    {
        $home = self::concatenatePaths([getenv('HOME'), '.reverse/']);
        $this->ensureDirectoryExists($home);
        return $home;
    }

    /**
     * Check that the directory exists
     *
     * @param string $dir
     * @return void
     * @throws InvalidConfigurationException
     */
    protected function ensureDirectoryExists(string $dir): void
    {
        if (!file_exists($dir) && !@mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new InvalidConfigurationException(sprintf('Directory "%s" cannot be created!', $dir), 1451862775);
        }
    }

    /**
     * @param array $paths
     * @return string
     */
    protected  static function concatenatePaths(array $paths): string
    {
        $resultingPath = '';
        foreach ($paths as $index => $path) {
            $path = self::getUnixStylePath($path);
            if ($index === 0) {
                $path = rtrim($path, '/');
            } else {
                $path = trim($path, '/');
            }
            if ($path !== '') {
                $resultingPath .= $path . '/';
            }
        }
        return rtrim($resultingPath, '/');
    }

    /**
     * @param string $path
     * @return string
     */
    public static function getUnixStylePath(string $path): string
    {
        if (strpos($path, ':') === false) {
            return str_replace(['//', '\\'], '/', $path);
        }
        return preg_replace('/^([a-z]{2,}):\//', '$1://', str_replace(['//', '\\'], '/', $path));
    }
}
