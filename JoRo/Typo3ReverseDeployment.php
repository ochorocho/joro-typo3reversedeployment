<?php /** @noinspection PhpUnused */

namespace JoRo;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Typo3ReverseDeployment
{
    /**
     * @var string
     */
    protected $tempRemotePath = '/typo3temp/joro_typo3reversedeployment/';

    /**
     * @var string
     */
    protected $tempLocalPath = '';

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
    protected $fileadminOnlyUsed = 0;

    /**
     * @var array $exclude
     */
    protected $exclude = ["_processed_", "_temp_", "typo3conf", "typo3", "typo3temp", "index.php"];

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
    protected $sqlExcludeTable = [
        "sys_log",
        "sys_history",
        "cf_cache_hash",
        "cf_cache_hash_tags",
        "cf_extbase_datamapfactory_datamap",
        "cf_extbase_datamapfactory_datamap_tags",
        "cf_extbase_object",
        "cf_extbase_object_tags",
        "cf_extbase_reflection",
        "cf_extbase_reflection_tags"
    ];

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
     * Relative path to typo3_console executable
     *
     * @var string $pathToConsoleExecutable
     */
    protected $pathToConsoleExecutable = "../vendor/bin/typo3cms";

    /**
     * @var string
     */
    protected $remoteServer;

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * Set username for ssh connection
     *
     * @param string $user
     */
    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    /**
     * Get path to Private Key file
     *
     * @return string
     */
    public function getPrivateKey(): string
    {
        if (strpos($this->privateKey, "~/") === 0) {
            $this->privateKey = str_replace('~/', getenv("HOME") . '/', $this->privateKey);
        }

        return $this->privateKey;
    }

    /**
     * Set path to Private Key file
     *
     * @param string $privateKey
     */
    public function setPrivateKey(string $privateKey): void
    {
        $this->privateKey = $privateKey;
    }

    /**
     * Get Passphrase for Private Key file
     *
     * @return string
     */
    public function getPrivateKeyPassphrase(): string
    {
        return $this->privateKeyPassphrase;
    }

    /**
     * Get ssh port (default: 22)
     *
     * @return int
     */
    public function getSshPort(): int
    {
        return $this->sshPort;
    }

    /**
     * Set custom ssh port (default: 22)
     *
     * @param int $sshPort
     */
    public function setSshPort(int $sshPort): void
    {
        $this->sshPort = $sshPort;
    }

    /**
     * Build and return ssh port parameter
     */
    public function getSshPortParam(): string
    {
        return '-e "ssh -p ' . $this->getSshPort() . '"';
    }

    /**
     * Set Passphrase for your Private Key
     *
     * @param string $privateKeyPassphrase
     */
    public function setPrivateKeyPassphrase(string $privateKeyPassphrase): void
    {
        $this->privateKeyPassphrase = $privateKeyPassphrase;
    }

    /**
     * Get root path of TYPO3 installation
     *
     * @return string
     */
    public function getTypo3RootPath(): string
    {
        return $this->typo3RootPath;
    }

    /**
     * Get path of TYPO3 installation
     *
     * @param string $typo3RootPath
     */
    public function setTypo3RootPath(string $typo3RootPath): void
    {
        $this->typo3RootPath = $typo3RootPath;
    }

    /**
     * Define Database Credentials to use on remote Server (read from LocalConfiguration.php)
     *
     * @return string
     */
    public function getConnectionPool(): string
    {
        return $this->connectionPool;
    }

    /**
     * @param string $connectionPool
     */
    public function setConnectionPool(string $connectionPool): void
    {
        $this->connectionPool = $connectionPool;
    }

    /**
     * @return string
     */
    public function getFileTarget(): string
    {
        return $this->fileTarget;
    }

    /**
     * @param string $fileTarget
     */
    public function setFileTarget(string $fileTarget): void
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
    public function setFileadminOnlyUsed(int $fileadminOnlyUsed): void
    {
        $this->fileadminOnlyUsed = $fileadminOnlyUsed;
    }

    /**
     * @return array
     */
    public function getExclude(): array
    {
        return $this->exclude;
    }

    /**
     * @param array $exclude
     */
    public function setExclude(array $exclude): void
    {
        $this->exclude = $exclude;
    }

    /**
     * Add item to $exclude
     * Use after 'setExclude' otherwise it will be overwritten
     *
     * @param array $exclude
     */
    public function addExclude(array $exclude): void
    {
        $this->exclude = array_merge($this->getExclude(), $exclude);
    }

    /**
     * Remove item from $exclude
     *
     * @param array $exclude
     */
    public function removeExclude(array $exclude): void
    {
        $this->exclude = array_diff($this->getExclude(), $exclude);
    }

    /**
     * @return array
     */
    public function getInclude(): array
    {
        return $this->include;
    }

    /**
     * @param array $include
     */
    public function setInclude(array $include): void
    {
        $this->include = $include;
    }

    /**
     * Add item to $include
     * Use after 'setInclude' otherwise it will be overwritten
     *
     * @param array $include
     */
    public function addInclude(array $include): void
    {
        $this->include = array_merge($this->getInclude(), $include);
    }

    /**
     * Remove item from $include
     *
     * @param array $include
     */
    public function removeInclude(array $include): void
    {
        $this->include = array_diff($this->getInclude(), $include);
    }

    /**
     * @return string
     */
    public function getSqlTarget(): string
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
    public function setSqlTarget(string $sqlTarget): void
    {
        $this->sqlTarget = $sqlTarget;
    }

    /**
     * Get array of excluded database tables
     *
     * @return array
     */
    public function getSqlExcludeTable(): array
    {
        return $this->sqlExcludeTable;
    }

    /**
     * Set array of tables to exclude in SQL dump
     *
     * @param array $sqlExcludeTable
     */
    public function setSqlExcludeTable(array $sqlExcludeTable): void
    {
        $this->sqlExcludeTable = $sqlExcludeTable;
    }

    /**
     * Add item to $sqlExcludeTable
     * Use after 'setSqlExcludeTable' otherwise it will be overwritten
     *
     * @param array $sqlExcludeTable
     */
    public function addSqlExcludeTable(array $sqlExcludeTable): void
    {
        $this->sqlExcludeTable = array_merge($this->getSqlExcludeTable(), $sqlExcludeTable);
    }

    /**
     * Get custom php binary path
     *
     * @return string
     */
    public function getPhpPathAndBinary(): string
    {
        return $this->phpPathAndBinary;
    }

    /**
     * Set custom php binary path
     *
     * @param string $phpPathAndBinary
     */
    public function setPhpPathAndBinary(string $phpPathAndBinary): void
    {
        $this->phpPathAndBinary = $phpPathAndBinary;
    }

    /**
     * Get rsync binary path
     *
     * @return string
     */
    public function getRsyncPathAndBinary(): string
    {
        return $this->rsyncPathAndBinary;
    }

    /**
     * Set custom rsync binary path
     *
     * @param string $rsyncPathAndBinary
     */
    public function setRsyncPathAndBinary(string $rsyncPathAndBinary): void
    {
        $this->rsyncPathAndBinary = $rsyncPathAndBinary;
    }

    /**
     * Get path to typo3_console executable
     *
     * @return string
     */
    public function getPathToConsoleExecutable(): string
    {
        return $this->pathToConsoleExecutable;
    }

    /**
     * Set path to typo3_console executable
     *
     * @param string $pathToConsoleExecutable
     */
    public function setPathToConsoleExecutable(string $pathToConsoleExecutable): void
    {
        $this->pathToConsoleExecutable = $pathToConsoleExecutable;
    }

    /**
     * Set remote server uri or IP-Address
     * @param string $remoteServer
     */
    public function setRemoteServer(string $remoteServer): void
    {
        $this->remoteServer = $remoteServer;
    }

    /**
     * Get remote server uri or IP-Address
     * @return string
     */
    public function getRemoteServer(): string
    {
        return $this->remoteServer;
    }

    /**
     * Get the temporary remote path, relative to web root
     * @return string
     */
    public function getTempRemotePath(): string
    {
        return $this->tempRemotePath;
    }

    /**
     * Set the temporary remote path, relative to web root
     * @param string $remotePath
     */
    public function setTempRemotePath(string $remotePath): void
    {
        $this->tempRemotePath = $remotePath;
    }

    /**
     * Set a local temporary directory path
     * @return string
     */
    public function getLocalTempPath(): string
    {
        return $this->tempLocalPath;
    }

    /**
     * Path to a local directory
     * @param string $localTemporaryPath
     */
    public function setLocalTempPath(string $localTemporaryPath): void
    {
        $this->tempLocalPath = rtrim($localTemporaryPath, '/') . '/';
    }

    /**
     * Execute a command on remote server
     *
     * @param string $command Command to execute on remote server
     * @return array Array with exit code as first index and the command output as second index
     */
    public function executeSshCommand(string $command): array
    {
        $callback = null;

        $sshCommand = ['ssh'];
        $sshCommand[] = '-A';
        if ($this->getSshPort()) {
            $sshCommand[] = '-p ' . $this->getSshPort();
        }

        if ($this->getPrivateKey()) {
            $sshCommand[] = '-i ' . $this->getPrivateKey();
        }

        $sshCommand[] = $this->getUser() . '@' . $this->getRemoteServer();
        $sshCommand[] = $command;

        $process = new Process($sshCommand);
        $process->setTimeout(null);

        $exitCode = $process->run($callback);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return [$exitCode, trim($process->getOutput())];
    }

    /**
     * Load LocalConfiguration.php and return connection details as array
     *
     * @return array
     * @throws ProcessException
     */
    public function getLocalConfiguration(): array
    {
        $remoteCommand =
            'cd ' . $this->getTypo3RootPath() . ' && '
            . $this->getPhpPathAndBinary() . ' ' . $this->getPathToConsoleExecutable() . ' configuration:showactive DB --json';
        $remoteCommandResult = $this->executeSshCommand($remoteCommand);

        if ($remoteCommandResult[0] !== 0) {
            throw new ProcessException(
                'Could not get database connection on remote server, ' . $remoteCommandResult[0],
                1549053038
            );
        }

        /** @noinspection PhpComposerExtensionStubsInspection */
        $conf = json_decode($remoteCommandResult[1], true);

        if (isset($conf['Connections'])) { // current TYPO3 versions
            return $conf['Connections'][$this->getConnectionPool()];
        }

        // simple fallback for TYPO3 7
        return [
            'driver' => 'mysqli',
            'host' => $conf['host'],
            'user' => $conf['username'],
            'password' => $conf['password'],
            'dbname' => $conf['database']
        ];
    }

    /**
     * Export and download database from remote TYPO3
     *
     * @return string
     * @throws ProcessException
     */
    public function getDatabase(): string
    {
        $this->ensureRemoteDirectoryExists();
        $conf = $this->getLocalConfiguration();

        $ignoredTables = count($this->sqlExcludeTable) > 0 ? '-e ' : '';
        foreach ($this->sqlExcludeTable as $exclude) {
            $ignoredTables .= end($this->sqlExcludeTable) === $exclude ? $exclude . '' : $exclude . ',';
        }

        // Export and download database
        $sqlRemoteTarget = $this->getTypo3RootPath() . $this->getTempRemotePath() . date('YmdHis') . '-' . $conf['dbname'] . '.sql';
        $sqlExport = 'cd ' . $this->getTypo3RootPath() . ' && ' . $this->getPhpPathAndBinary() . ' ' . $this->getPathToConsoleExecutable() . ' database:export -c ' . $this->getConnectionPool();

        echo sprintf("\033[32mExport %s DB \033[0m", $this->getConnectionPool()) . PHP_EOL;
        $this->executeSshCommand($sqlExport . ' ' . $ignoredTables . ' > ' . $sqlRemoteTarget);

        echo "\033[32mDownload DB\033[0m" . PHP_EOL;
        echo $this->getRsyncPathAndBinary() . ' -avz ' . $this->getSshPortParam() . ' ' .
            $this->getUser() . '@' . $this->getRemoteServer() . ':' . $sqlRemoteTarget . ' ' .
            $this->getSqlTarget() . "\n";
        exec(
            $this->getRsyncPathAndBinary() . ' -avz ' . $this->getSshPortParam() . ' ' .
            $this->getUser() . '@' . $this->getRemoteServer() . ':' . $sqlRemoteTarget . ' ' .
            $this->getSqlTarget()
        );

        echo "\033[32mDelete DB-Dump\033[0m" . PHP_EOL;
        $this->executeSshCommand($this->getPhpPathAndBinary() . " -r 'unlink(\"$sqlRemoteTarget\");'");

        return $sqlRemoteTarget;
    }

    /**
     * Get files from remote TYPO3
     * - Download only used
     * - Download all
     * - Download additional folders like ./uploads
     *
     * @return bool
     * @throws ProcessException
     */
    public function getFiles(): bool
    {
        $tempPhp = $this->getCreateTempFileForDownload();
        $filesFrom = ' --files-from=' . $tempPhp . ' ';
        $exclude = ' --exclude={' . implode(',', $this->getExclude()) . '} ';
        $fileadminRemote = $this->getTypo3RootPath();

        /**
         * Download files in list
         */
        echo "\033[32mDownload files to " . $this->getFileTarget() . "\033[0m" . PHP_EOL;
        exec(
            $this->getRsyncPathAndBinary() . ' -h --progress -avz -r -L '
            . $this->getSshPortParam() . $filesFrom . $exclude
            . $this->getUser() . '@' . $this->getRemoteServer() . ':' . $fileadminRemote . " " . $this->getFileTarget(),
            $output,
            $return
        );

        $output = array_reverse($output);

        echo $output[1] . PHP_EOL;
        echo $output[0] . PHP_EOL;

        echo "\033[32mDelete temporary file for sync\033[0m" . PHP_EOL;
        unlink($tempPhp);

        return $return;
    }

    /**
     * Get all files referenced/used in this TYPO3 instance
     * @return string
     * @throws ProcessException
     */
    private function getCreateTempFileForDownload(): string
    {
        $this->ensureRemoteDirectoryExists();
        $conf = $this->getLocalConfiguration();
        $tempPhp = !empty($this->getLocalTempPath()) ?: sys_get_temp_dir();
        $tempPhp = rtrim($tempPhp, '/') . '/.rsync_files';
        $i = 0;

        $files = [];

        // Download only used files
        if (($conf['driver'] !== 'mysqli') && $this->getFileadminOnlyUsed()) {
            exit("\033[31mDatabase Driver " . $conf['driver'] . " not supported!\033[0m" . PHP_EOL);
        }
        if ($conf['driver'] === 'mysqli' && $this->getFileadminOnlyUsed()) {

            /**
             * Select only files with references (only used files)
             * query SELECT * FROM sys_file AS t1 INNER JOIN sys_file_reference AS t2 ON t1.uid = t2.uid_local WHERE t1.uid = t1.uid
             */
            $filesUsed = $this->executeSshCommand($this->getPhpPathAndBinary() . " -r '
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
            $files = explode("\n", $filesUsed[1]);
            $files = array_filter($files);
            foreach ($files as $file) {
                $files[$i] = '/fileadmin' . $file;
                $i++;
            }
        }

        foreach ($this->getInclude() as $include) {
            $files[$i] = $include;
            $i++;
        }

        file_put_contents($tempPhp, implode("\n", $files));

        return $tempPhp;
    }

    /**
     * Ensure all required files and folders exist on remote
     * - Folder for temporary files typo3temp/joro_typo3reversedeployment/
     * - For Security .htaccess
     *
     * @todo fetch exception
     */
    protected function ensureRemoteDirectoryExists(): void
    {
        $tempFolder = $this->getTypo3RootPath() . $this->getTempRemotePath();
        $htaccess = $tempFolder . '.htaccess';

        $command = 'mkdir -p ' . $tempFolder . ' && '
            . $this->getPhpPathAndBinary() . ' \'-r file_put_contents("' . $htaccess . '", "deny from all");\'';
        $this->executeSshCommand($command);
    }

    /**
     * Catch undefined method calls and show hint to update to the latest version.
     *
     * @param string $name
     * @param array $args
     */
    public function __call(string $name, array $args)
    {
        exit("\033[31mThe method '$name', which is used in your reverse deployment configuration, cannot be found in the installed version of joro/reversedeployment. Please install the latest version of joro/reversedeployment.\033[0m" . PHP_EOL);
    }
}
