<?php

namespace JoRo;

Class Typo3ReverseDeployment
{

    /**
     * @var string
     */
    protected $user = "root";

    /**
     * @var string
     */
    protected $privateKey = "~/.ssh/id_rsa";

    /**
     * @var int
     */
    protected $sshPort = 22;

    /**
     * Optional private key passphrase
     * @var string
     */
    protected $privateKeyPassphrase = '';

    /**
     * LocalConfiguration.php
     *
     * @var string
     */
    protected $typo3RootPath = "/var/www/typo3/";

    /**
     * @var string
     */
    protected $connectionPool = "Default";

    /**
     * Target path for sql file
     *
     * @var string
     */
    protected $fileadminTarget = "./fileadmin/";

    /**
     * Target path for sql file
     *
     * @var string
     */
    protected $sqlTarget = "./sql/";

    /**
     * Tables to exclude during export
     *
     * @var array
     */
    protected $sqlExcludeTable = ["sys_log", "sys_history", "cf_cache_hash", "cf_cache_hash_tags", "cf_extbase_datamapfactory_datamap", "cf_extbase_datamapfactory_datamap_tags", "cf_extbase_object", "cf_extbase_object_tags", "cf_extbase_reflection", "cf_extbase_reflection_tags"];

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getPrivateKey()
    {
        if (substr($this->privateKey, 0, 2) == "~/") {
            $this->privateKey = str_replace('~/', getenv("HOME") . '/', $this->privateKey);
        };

        return $this->privateKey;
    }

    /**
     * @param string $privateKey
     */
    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;
    }

    /**
     * @return string
     */
    public function getPrivateKeyPassphrase() {
        return $this->privateKeyPassphrase;
    }

    /**
     * @return int
     */
    public function getSshPort()
    {
        return $this->sshPort;
    }

    /**
     * @param int $sshPort
     */
    public function setSshPort($sshPort)
    {
        $this->sshPort = $sshPort;
    }

    /**
     * @param string $privateKeyPassphrase
     */
    public function setPrivateKeyPassphrase($privateKeyPassphrase) {
        $this->privateKeyPassphrase = $privateKeyPassphrase;
    }

    /**
     * @return string
     */
    public function getTypo3RootPath()
    {
        return $this->typo3RootPath;
    }

    /**
     * @param string $typo3RootPath
     */
    public function setTypo3RootPath($typo3RootPath)
    {
        $this->typo3RootPath = $typo3RootPath;
    }

    /**
     * @return string
     */
    public function getConnectionPool()
    {
        return $this->connectionPool;
    }

    /**
     * @param string $connectionPool
     */
    public function setConnectionPool($connectionPool)
    {
        $this->connectionPool = $connectionPool;
    }

    /**
     * @return string
     */
    public function getFileadminTarget()
    {
        return $this->fileadminTarget;
    }

    /**
     * @param string $fileadminTarget
     */
    public function setFileadminTarget($fileadminTarget)
    {
        $this->fileadminTarget = $fileadminTarget;
    }

    /**
     * @return string
     */
    public function getSqlTarget()
    {
        return $this->sqlTarget;
    }

    /**
     * @param string $sqlTarget
     */
    public function setSqlTarget($sqlTarget)
    {
        $this->sqlTarget = $sqlTarget;
    }

    /**
     * @return array
     */
    public function getSqlExcludeTable()
    {
        return $this->sqlExcludeTable;
    }

    /**
     * @param array $sqlExcludeTable
     */
    public function setSqlExcludeTable($sqlExcludeTable)
    {
        $this->sqlExcludeTable = $sqlExcludeTable;
    }

    /**
     * Connect to Server via SSH
     *
     * @param $host
     * @param $user
     * @return \phpseclib\Net\SSH2
     */
    public function ssh($host)
    {
        $key = new \phpseclib\Crypt\RSA();
        if($passphrase = $this->getPrivateKeyPassphrase()) {
            $key->setPassword($passphrase);
        }

        $key->loadKey(file_get_contents($this->getPrivateKey()));
        $ssh = new \phpseclib\Net\SSH2($host, $this->getSshPort());

        if (!$ssh->login($this->getUser(), $key)) {
            exit("\033[31mLogin Failed\033[0m" . PHP_EOL);
        } else {
            echo "\033[32mSSH to " . $this->getUser() . "@$host successful\033[0m" . PHP_EOL;
        }
        return $ssh;
    }

    /**
     * @param $ssh
     * @return string
     */
    public function getLocalConfiguration($ssh)
    {

        $path = $this->getTypo3RootPath();
        $remoteConf = $ssh->exec('cat ' . $path . '/typo3conf/LocalConfiguration.php');
        $phpConfig = str_replace('<?php', '', $remoteConf);
        $conf = eval($phpConfig);

        return $conf['DB']['Connections'][$this->getConnectionPool()];
    }

    /**
     * @param $ssh
     * @return string
     */
    public function getDatabase($ssh)
    {

        $conf = $this->getLocalConfiguration($ssh);

        /**
         * Build --exclude-tables for `typo3cms database:export` command
         */
        $ignoredTables = count($this->sqlExcludeTable) > 0 ? " --exclude-tables " : "";
        foreach ($this->sqlExcludeTable as $exclude) {
            $ignoredTables .= end($this->sqlExcludeTable) == $exclude ? $exclude . '' : $exclude . ',';
        }

        /**
         * Export and download database
         */
        $sqlRemoteTarget = $this->getTypo3RootPath() . 'typo3temp/' . date("Ymds") . "-" . $conf['dbname'] . ".sql";
        $sqlExport = "cd " . $this->getTypo3RootPath() . " && ../vendor/bin/typo3cms database:export";

        echo "\033[32mExport DB: $sqlExport\033[0m" . PHP_EOL;
        $ssh->exec($sqlExport . " $ignoredTables > $sqlRemoteTarget");

        $sshPortParam = '';
        if($ssh->port != 22) {
            $sshPortParam = '-e "ssh -p ' . $ssh->port . '"';
        }

        exec("rsync -avz $sshPortParam " . $this->getUser() . "@$ssh->host:$sqlRemoteTarget " . $this->getSqlTarget());
        $ssh->exec("rm -f $sqlRemoteTarget");

        return $sqlRemoteTarget;
    }

    public function getFileadmin($ssh)
    {
        $conf = $this->getLocalConfiguration($ssh);

        if ($conf['driver'] == 'mysqli') {

            $fileadminRemote = $this->getTypo3RootPath() . 'fileadmin/';
            $tempPhp = sys_get_temp_dir() . '.rsync_files';

            /**
             * Select only files with references (only used files)
             * @query SELECT * FROM sys_file AS t1 INNER JOIN sys_file_reference AS t2 ON t1.uid = t2.uid_local WHERE t1.uid = t1.uid
             */
            $files = $ssh->exec("mysql " . $conf['dbname'] . " -u " . $conf['user'] . " -p" . $conf['password'] . " -h" . $conf['host'] . " -se \"SELECT identifier FROM sys_file AS t1 INNER JOIN sys_file_reference AS t2 ON t1.uid = t2.uid_local WHERE t1.uid = t1.uid\"");

            /**
             * Create .rsync_files containing a list of files to download
             */
            file_put_contents($tempPhp, $files);

            /**
             * Download files in list
             */
            echo "\033[32mDownload fileadmin: " . $this->getFileadminTarget() . "\033[0m" . PHP_EOL;
            exec('rsync -avz --files-from=' . $tempPhp . ' ' . $this->getUser() . '@' . $ssh->host . ':' . $fileadminRemote . " " . $this->getFileadminTarget());

        } else {
            exit("\e[31mDatabase Driver " . $conf['driver'] . " not supported!\e[0m" . PHP_EOL);
        }

        return true;
    }
}