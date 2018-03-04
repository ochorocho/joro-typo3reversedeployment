<?php

namespace JoRo;

Class Typo3ReverseDeployment {

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
    protected $sqlTarget = ".data/db/db-dump";

    /**
     * Tables to exclude during export
     *
     * @var array
     */
    protected $sqlExcludeTable = ["sys_log","sys_history","cf_cache_hash","cf_cache_hash_tags","cf_extbase_datamapfactory_datamap","cf_extbase_datamapfactory_datamap_tags","cf_extbase_object","cf_extbase_object_tags","cf_extbase_reflection","cf_extbase_reflection_tags"];

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
    public function ssh($host, $user) {
        $key = new \phpseclib\Crypt\RSA();
        $key->loadKey(file_get_contents($this->getPubKey()));

        $ssh = new \phpseclib\Net\SSH2($host);
        if (!$ssh->login($user, $key)) {
            exit("\033[31mLogin Failed\033[0m" . PHP_EOL);
        } else {
            echo "\033[32mSSH to $user@$host successful\033[0m" . PHP_EOL;
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
         * Build --ignore-table option
         */
        $ignoredTables = count($this->sqlExcludeTable) > 0 ? " --ignore-table={" : "";
        foreach ($this->sqlExcludeTable as $exclude) {
            if(end($this->sqlExcludeTable) == $exclude) {
                $space = "";
            } else {
                $space = ",";
            }
            $ignoredTables .= $conf['dbname']. "." .$exclude . $space;
        }
        $ignoredTables .= count($this->sqlExcludeTable) > 0 ? "}" : "";;

        /**
         * Export and download database
         */
        $dbRemoteTarget = $this->getTypo3RootPath() . 'typo3temp/' . date("Ymds") . "-" . $conf['dbname'] . ".sql";
        $ssh->exec("mysqldump " . $conf['dbname'] ." > " . $dbRemoteTarget . " -u" . $conf['user'] . " -p" . $conf['password'] . " -h" . $conf['host'] . $ignoredTables);
        exec("rsync -avz jochen@knallimall.org:$dbRemoteTarget ./tmp/");
        return $dbRemoteTarget . PHP_EOL;
    }
}