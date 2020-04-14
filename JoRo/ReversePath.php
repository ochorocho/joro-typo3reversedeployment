<?php

namespace JoRo;

use JoRo\Exception\InvalidConfigurationException;
use \Neos\Utility\Files;

Class ReversePath
{

    /**
     * Get reverse deployment path
     *
     * @param null $path
     * @return array
     * @throws InvalidConfigurationException
     */
    public function getDeploymentNames($path = null)
    {
        $path = $this->getDeploymentsBasePath($path);
        $files = glob(Files::concatenatePaths(array($path, '*.php')));
        return array_map(static function ($file) use ($path) {
            return substr($file, strlen($path) + 1, -4);
        }, $files);
    }

    /**
     * Get the root path of the reverse deployment declarations
     *
     * This defaults to ./.reverse if a NULL path is given.
     *
     * @param string $path An absolute path (optional)
     * @return string The configuration root path without a trailing slash.
     * @throws InvalidConfigurationException
     */
    public function getDeploymentsBasePath($path = null)
    {
        /** @var string $localDeploymentDescription */
        $localDeploymentDescription = @realpath('./.reverse');
        if (!$path && is_dir($localDeploymentDescription)) {
            $path = $localDeploymentDescription;
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
    protected function getHomeDir()
    {
        $home = Files::concatenatePaths(array(getenv('HOME'), '.reverse/'));
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
    protected function ensureDirectoryExists($dir)
    {
        if (!file_exists($dir) && !@mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new InvalidConfigurationException(sprintf('Directory "%s" cannot be created!', $dir), 1451862775);
        }
    }
}
