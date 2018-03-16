Reverse deploy TYPO3 DB and fileadmin
=====================================

Features:
* Export TYPO3 Database and download (exclude tables)
* Download only referenced files to your local fileadmin

Requires [TYPO3 Console](https://packagist.org/packages/helhum/typo3-console) on remote TYPO3 installation

Usage:

1) Create a new file in folder `.reverse`, e.g. `.reverse/remote.php`

```php
<?php

$reverseDeploy = new \JoRo\Typo3ReverseDeployment();

/**
 * Set server paths
 */
$reverseDeploy->setTypo3RootPath('/var/www/html/');
// optional: $reverseDeploy->setPhpPathAndBinary('/usr/local/bin/php_cli');

/**
 * Connect to Server
 */
$reverseDeploy->setPrivateKey(getenv("HOME") . '/.ssh/id_rsa');
$reverseDeploy->setUser('USERNAME');
$ssh = $reverseDeploy->ssh('example.org');

/**
 * Get database
 */
$reverseDeploy->setSqlExcludeTable(['sys_log']);
$reverseDeploy->setSqlTarget("./tmp/");
$reverseDeploy->getDatabase($ssh);

/**
 * Get fileadmin
 */
$reverseDeploy->setFileTarget("./fileadmin/");
$reverseDeploy->getFiles($ssh);
```

2) Run `vendor/bin/typo3reverse`

Use SSH-Key with passphrase
---------------------------

You can define your passphrase like shown in this example:

```php
/**
 * Connect to Server
 */
$reverseDeploy->setPrivateKeyPassphrase('mypassword');
$reverseDeploy->setUser('USERNAME');
$ssh = $reverseDeploy->ssh('example.org');
```

If you do not want to have your passphrase stored in a file, you can use an environment variable:

```php
/**
 * Connect to Server
 */
$reverseDeploy->setPrivateKeyPassphrase(getenv('PASSPHRASE'));
$reverseDeploy->setUser('USERNAME');
$ssh = $reverseDeploy->ssh('example.org');
```

Then you can start the reverse deployment with a command like this:

```
PASSPHRASE=mypassword vendor/bin/typo3reverse
```
