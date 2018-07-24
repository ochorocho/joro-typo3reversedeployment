Reverse deploy TYPO3 DB and Folders
=====================================

Features:
* Export TYPO3 Database and download (exclude tables)
* Download only referenced files to your local fileadmin
* Download specific folders like ./uploads or download entire fileadmin

Requirements:
* [TYPO3 Console](https://packagist.org/packages/helhum/typo3-console) (>=4.6) on remote TYPO3 installation
* A SSH connection to the remote server
* `rsync` to download files
* Works with TYPO3 7.x/8.x



Security
--------

Created temporary files should be protected from public access

**Apache**

The code will check for `.htaccess` file within `typo3temp/joro_typo3reversedeployment` and creates it as needed.

Example content:

```
 deny from all
``` 

**NGINX**

Add this to your NGINX configuration to disabled public access of temp files

```
location ~ /\.(?!joro_typo3reversedeployment).* {
    deny all;
}
```

Usage:
------

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
$reverseDeploy->setUser('USERNAME');
// optional: $reverseDeploy->setPrivateKey(getenv('HOME') . '/.ssh/id_rsa');
// optional: $reverseDeploy->setSshPort(222);
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

Add/remove methods for files include/exclude
--------------------------------------------

Add/remove item to/from exludes array
```php
$reverseDeploy->addExclude(["uploads"]);
$reverseDeploy->removeExclude(["uploads"]);
```

Add/remove item to/from includes array
```php
$reverseDeploy->addInclude(["uploads"]);
$reverseDeploy->removeInclude(["uploads"]);
```

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

### Build phar file

Using [MacFJA/PharBuilder](https://github.com/MacFJA/PharBuilder) package to create PHAR file

For configuration see composer.json `extra -> phar-builder`

```bash
php -d phar.readonly=0 vendor/bin/phar-builder package
```