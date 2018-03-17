<?php

namespace JoRo;

use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;

Class Typo3ReverseDeployment
{

    /**
     * @var string
     */
    protected $user = "root";

    /**
     * @var string $privateKey
     */
    protected $privateKey = "~/.ssh/id_rsa";

    /**
     * @var int $sshPort
     */
    protected $sshPort = 22;

    /**
     * Optional private key passphrase
     *
     * @var string $privateKeyPassphrase
     */
    protected $privateKeyPassphrase = '';

    /**
     * LocalConfiguration.php
     *
     * @var string $typo3RootPath
     */
    protected $typo3RootPath = "/var/www/typo3/";

    /**
     * @var string $connectionPool
     */
    protected $connectionPool = "Default";

    /**
     * Target path for fileadmin
     *
     * @var string $fileTarget
     */
    protected $fileTarget = "./fileadmin/";

    /**
     * Target path for fileadmin
     *
     * @var int $fileadminOnlyUsed
     */
    protected $fileadminOnlyUsed = false;

    /**
     * @var array $exclude
     */
    protected $exclude = ["_processed_","_temp_","typo3conf","typo3","typo3temp","index.php"];

    /**
     * @var array $include
     */
    protected $include = ["fileadmin"];

    /**
     * Target path for sql file
     *
     * @var string $sqlTarget
     */
    protected $sqlTarget = "./sql/";

    /**
     * Tables to exclude during export
     *
     * @var array $sqlExcludeTable
     */
    protected $sqlExcludeTable = ["sys_log", "sys_history", "cf_cache_hash", "cf_cache_hash_tags", "cf_extbase_datamapfactory_datamap", "cf_extbase_datamapfactory_datamap_tags", "cf_extbase_object", "cf_extbase_object_tags", "cf_extbase_reflection", "cf_extbase_reflection_tags"];

    /**
     * Full path to PHP binary
     *
     * @var string $phpPathAndBinary
     */
    protected $phpPathAndBinary = "php";

    /**
     * Full path to rsync binary
     *
     * @var string $rsyncPathAndBinary
     */
    protected $rsyncPathAndBinary = "rsync";

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set username for ssh connection
     *
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Get path to Private Key file
     *
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
     * Set path to Private Key file
     *
     * @param string $privateKey
     */
    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;
    }

    /**
     * Get Passphrase for Private Key file
     *
     * @return string
     */
    public function getPrivateKeyPassphrase() {
        return $this->privateKeyPassphrase;
    }

    /**
     * Get ssh port (default: 22)
     *
     * @return int
     */
    public function getSshPort()
    {
        return $this->sshPort;
    }

    /**
     * Set custom ssh port (default: 22)
     *
     * @param int $sshPort
     */
    public function setSshPort($sshPort)
    {
        $this->sshPort = $sshPort;
    }

    /**
     * Build ssh port parameter
     */
    public function getSshPortParam()
    {
        $this->sshPortParam = '-e "ssh -p ' . $this->sshPort . '"';
    }

    /**
     * Set Passphrase for your Private Key
     *
     * @param string $privateKeyPassphrase
     */
    public function setPrivateKeyPassphrase($privateKeyPassphrase) {
        $this->privateKeyPassphrase = $privateKeyPassphrase;
    }

    /**
     * Get root path of TYPO3 installation
     *
     * @return string
     */
    public function getTypo3RootPath()
    {
        return $this->typo3RootPath;
    }

    /**
     * Get path of TYPO3 installation
     *
     * @param string $typo3RootPath
     */
    public function setTypo3RootPath($typo3RootPath)
    {
        $this->typo3RootPath = $typo3RootPath;
    }

    /**
     * Define Database Credentials to use on remote Server (read from LocalConfiguration.php)
     *
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
    public function getFileTarget()
    {
        return $this->fileTarget;
    }

    /**
     * @param string $fileTarget
     */
    public function setFileTarget($fileTarget)
    {
        $this->fileTarget = $fileTarget;
    }

    /**
     * @return int
     */
    public function getFileadminOnlyUsed()
    {
        return $this->fileadminOnlyUsed;
    }

    /**
     * @param int $fileadminOnlyUsed
     */
    public function setFileadminOnlyUsed($fileadminOnlyUsed)
    {
        $this->fileadminOnlyUsed = $fileadminOnlyUsed;
    }

    /**
     * @return array
     */
    public function getExclude()
    {
        return $this->exclude;
    }

    /**
     * @param array $exclude
     */
    public function setExclude($exclude)
    {
        $this->exclude = $exclude;
    }

    /**
     * @return array
     */
    public function getInclude()
    {
        return $this->include;
    }

    /**
     * @param array $include
     */
    public function setInclude($include)
    {
        $this->include = $include;
    }

    /**
     * @return string
     */
    public function getSqlTarget()
    {
        return $this->sqlTarget;
    }

    /**
     * Set Target Filename/Folder
     * - When path and filename given it will override existing file e.g. ./folder/dump.sql
     * - When only path to folder given a filename based on date and dbname will be created e.g. ./folder/2018031336-dbname.sql
     *
     * @param string $sqlTarget
     */
    public function setSqlTarget($sqlTarget)
    {
        $this->sqlTarget = $sqlTarget;
    }

    /**
     * Get array of excluded database tables
     *
     * @return array
     */
    public function getSqlExcludeTable()
    {
        return $this->sqlExcludeTable;
    }

    /**
     * Set array of tables to exclude in SQL dump
     *
     * @param array $sqlExcludeTable
     */
    public function setSqlExcludeTable($sqlExcludeTable)
    {
        $this->sqlExcludeTable = $sqlExcludeTable;
    }

    /**
     * Get custom php binary path
     *
     * @return string
     */
    public function getPhpPathAndBinary() {
        return $this->phpPathAndBinary;
    }

    /**
     * Set custom php binary path
     *
     * @param string $phpPathAndBinary
     */
    public function setPhpPathAndBinary($phpPathAndBinary) {
        $this->phpPathAndBinary = $phpPathAndBinary;
    }

    /**
     * Get rsync binary path
     *
     * @return string
     */
    public function getRsyncPathAndBinary()
    {
        return $this->rsyncPathAndBinary;
    }

    /**
     * Set custom rsync binary path
     *
     * @param string $rsyncPathAndBinary
     */
    public function setRsyncPathAndBinary($rsyncPathAndBinary)
    {
        $this->rsyncPathAndBinary = $rsyncPathAndBinary;
    }

    /**
     * Connect to Server via SSH
     *
     * @param $host
     * @return SSH2
     */
    public function ssh($host)
    {
        $key = new RSA();
        if($passphrase = $this->getPrivateKeyPassphrase()) {
            $key->setPassword($passphrase);
        }

        $key->loadKey(file_get_contents($this->getPrivateKey()));
        $ssh = new SSH2($host, $this->getSshPort());

        if (!$ssh->login($this->getUser(), $key)) {
            exit("\033[31mLogin Failed\033[0m" . PHP_EOL);
        } else {
            echo "\033[32mSSH to " . $this->getUser() . "@$host successful\033[0m" . PHP_EOL;
        }

        $this->ensureRemoteDirectoryExists($ssh);

        return $ssh;
    }

    /**
     * Load LocalConfiguration.php and return connection details as array
     *
     * @param $ssh
     * @return mixed
     */
    public function getLocalConfiguration($ssh)
    {
        $path = $this->getTypo3RootPath();
        $remoteConf = $ssh->exec($this->getPhpPathAndBinary() . " -r 'echo file_get_contents(\"$path/typo3conf/LocalConfiguration.php\");'");

        $phpConfig = str_replace('<?php', '', $remoteConf);
        $conf = eval($phpConfig);

        return $conf['DB']['Connections'][$this->getConnectionPool()];
    }

    /**
     * Export and download database from remote TYPO3
     *
     * @param $ssh
     * @return string
     */
    public function getDatabase($ssh)
    {

        $conf = $this->getLocalConfiguration($ssh);

        /**
         * Build --exclude-tables for `typo3cms database:export` command
         * @return string $ignoredTables
         */
        $ignoredTables = count($this->sqlExcludeTable) > 0 ? " --exclude-tables " : "";
        foreach ($this->sqlExcludeTable as $exclude) {
            $ignoredTables .= end($this->sqlExcludeTable) == $exclude ? $exclude . '' : $exclude . ',';
        }

        /**
         * Export and download database
         */
        $sqlRemoteTarget = $this->getTypo3RootPath() . 'typo3temp/joro_typo3reversedeployment/' . date("Ymds") . "-" . $conf['dbname'] . ".sql";
        $sqlExport = "cd " . $this->getTypo3RootPath() . " && " . $this->getPhpPathAndBinary() . " ../vendor/bin/typo3cms database:export";

        echo "\033[32mExport DB: $sqlExport\033[0m" . PHP_EOL;
        $ssh->exec($sqlExport . " $ignoredTables > $sqlRemoteTarget");

        exec($this->getRsyncPathAndBinary() . " -avz " . $this->getSshPortParam() . ' ' . $this->getUser() . "@$ssh->host:$sqlRemoteTarget " . $this->getSqlTarget());
        $ssh->exec($this->getPhpPathAndBinary() . " -r 'unlink(\"$sqlRemoteTarget\");'");

        return $sqlRemoteTarget;
    }

    /**
     * Get files from remote TYPO3
     * - Download only used
     * - Download all
     * - Download additional folders like ./uploads
     *
     * @param $ssh
     * @return bool
     */
    public function getFiles($ssh)
    {
        $filesFrom = '';
        if($this->getFileadminOnlyUsed()) {
            $tempPhp = $this->getUsedFiles($ssh);
            $filesFrom = ' --files-from=' . $tempPhp . ' ';
        }

        $exlude = " --exclude={" . implode(",", $this->getExclude()) . "} ";
        $include = " --include={" . implode(",", $this->getInclude()) . "} ";

        $fileadminRemote = $this->getTypo3RootPath();

        /**
         * Download files in list
         */
        echo "\033[32mDownload files to " . $this->getFileTarget() . "\033[0m" . PHP_EOL;
        exec($this->getRsyncPathAndBinary() . ' -avz -L ' . $this->getSshPortParam() . $filesFrom . $include . $exlude . $this->getUser() . '@' . $ssh->host . ':' . $fileadminRemote . " " . $this->getFileTarget());

        return true;
    }

    /**
     * Get all files referenced/used in this TYPO3 instance
     *
     * @param $ssh
     * @return string
     */
    private function getUsedFiles($ssh) {
        $conf = $this->getLocalConfiguration($ssh);
        if ($conf['driver'] == 'mysqli') {

            $tempPhp = sys_get_temp_dir() . '.rsync_files';

            /**
             * Select only files with references (only used files)
             * query SELECT * FROM sys_file AS t1 INNER JOIN sys_file_reference AS t2 ON t1.uid = t2.uid_local WHERE t1.uid = t1.uid
             */
            $files = $ssh->exec("php -r '
                \$mysqli = new \mysqli(\"" . $conf['host'] . "\", \"" . $conf['user'] . "\", \"" . $conf['password'] . "\", \"" . $conf['dbname'] . "\");
                if (\$mysqli->connect_errno) {
                    printf(\"Connect failed on TYPO3 Remote: %s\n\", \$mysqli->connect_error);
                    exit();
                }
                \$result = \$mysqli->query(\"SELECT identifier FROM sys_file AS t1 INNER JOIN sys_file_reference AS t2 ON t1 . uid = t2 . uid_local WHERE t1 . uid = t1 . uid\", MYSQLI_STORE_RESULT);

                if(\$result){
                    while (\$row = \$result->fetch_object()){
                        \$files[] = \$row->identifier;
                    }
                }
                echo implode(\"\n\",\$files) . \"\n\";

                \$result->close();
                '");

            /**
             * Create .rsync_files containing a list of files to download
             * prefix with ./fileadmin
             */
            $i = 0;
            $files = explode("\n", $files);
            $files = array_filter($files);
            foreach($files as $file) {
                $files[$i] =  '/fileadmin' . $file;
                $i++;
            };
            file_put_contents($tempPhp, implode("\n", $files));

        } else {
            exit("\033[31mDatabase Driver " . $conf['driver'] . " not supported!\033[0m" . PHP_EOL);
        }
        return $tempPhp;
    }

    /**
     * Ensure all required files and folders exist on remote
     * - Folder for temporary files typo3temp/joro_typo3reversedeployment/
     * - For Security .htaccess
     *
     * @param $ssh
     */
    protected function ensureRemoteDirectoryExists($ssh)
    {
        $tempFolder = $this->getTypo3RootPath() . "typo3temp/joro_typo3reversedeployment/";
        $htaccess = $tempFolder . '.htaccess';
        $ssh->exec($this->getPhpPathAndBinary() . " -r 'mkdir(\"$tempFolder\", 0777, true);'");
        $ssh->exec($this->getPhpPathAndBinary() . " -r 'file_put_contents(\"$htaccess\",\"deny from all\");'");
    }
}
