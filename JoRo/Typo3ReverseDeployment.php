<?php

namespace JoRo;

Class Typo3ReverseDeployment {

    /**
     * @var string
     */
    protected $user = "root";

    /**
     * @var string
     */
    protected $pubKey = "~/.ssh/id_rsa";

    /**
     * LocalConfiguration.php
     *
     * @var string
     */
    protected $typo3RootPath = "/var/www/typo3/";

    /**
     * Target path for sql file
     *
     * @var string
     */
    protected $sqlTarget = "./tmp/";

    /**
     * Tables to exclude during export
     *
     * @var array
     */
    protected $sqlExcludeTable = ["sys_log","sys_history","cf_cache_hash","cf_cache_hash_tags","cf_extbase_datamapfactory_datamap","cf_extbase_datamapfactory_datamap_tags","cf_extbase_object","cf_extbase_object_tags","cf_extbase_reflection","cf_extbase_reflection_tags"];

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
    public function getPubKey()
    {
        if(substr( $this->pubKey, 0, 2 ) == "~/") {
            $this->pubKey =  str_replace('~/', getenv("HOME") . '/', $this->pubKey);
        };

        return $this->pubKey;
    }

    /**
     * @param string $pubKey
     */
    public function setPubKey($pubKey)
    {
        $this->pubKey = $pubKey;
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
    public function ssh($host) {
        $key = new \phpseclib\Crypt\RSA();
        $key->loadKey(file_get_contents($this->getPubKey()));

        $ssh = new \phpseclib\Net\SSH2($host);
        if (!$ssh->login($this->getUser(), $key)) {
            exit("\033[31mLogin Failed\033[0m" . PHP_EOL);
        } else {
            echo "\033[32mSSH to ".$this->getUser()."@$host successful\033[0m" . PHP_EOL;
        }
        return $ssh;
    }

    /**
     * @param $ssh
     * @return string
     */
    public function getLocalConfiguration($ssh) {

        $path = $this->getTypo3RootPath();
        $remoteConf = $ssh->exec('cat ' . $path . '/typo3conf/LocalConfiguration.php');
        $phpConfig = str_replace('<?php', '', $remoteConf);
        $conf = eval($phpConfig);

        return $conf['DB']['Connections']['Default'];
    }

    /**
     * @param $ssh
     * @return string
     */
    public function getDatabase($ssh) {

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
        exec("rsync -avz ".$this->getUser()."@$ssh->host:$sqlRemoteTarget " . $this->getSqlTarget());
        $ssh->exec("rm -f $sqlRemoteTarget");

        return $sqlRemoteTarget;
    }
}