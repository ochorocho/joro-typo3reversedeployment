Reverse deploy TYPO3 DB and fileadmin
=====================================

Features:
* Export TYPO3 Database and download (exclude tables)
* Download only referenced files to your local fileadmin

Requires [TYPO3 Console](https://packagist.org/packages/helhum/typo3-console) on remote TYPO3 installation

Usage:

```php
include __DIR__ . '/vendor/autoload.php';

$reverseDeploy = new \JoRo\Typo3ReverseDeployment();

/**
 * Set TYPO3 root path
 */
$reverseDeploy->setTypo3RootPath('/var/www/typo3.knallimall.org/web/releases/current/html/');

/**
 * Connect to Server
 */
$reverseDeploy->setPrivateKey(getenv("HOME") . '/.ssh/id_rsa');
$reverseDeploy->setUser('jochen');
$ssh = $reverseDeploy->ssh('knallimall.org');

/**
 * Get database
 */
$reverseDeploy->setSqlExcludeTable(['sys_log']);
$reverseDeploy->setSqlTarget("./tmp/");
$reverseDeploy->getDatabase($ssh);

/**
 * Get fileadmin
 */
$reverseDeploy->setFileadminTarget("./fileadmin/");
$reverseDeploy->getFileadmin($ssh);
```