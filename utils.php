<?php

namespace PHP_CodeSniffer_Yii2_GitHook;

class Utils
{
    protected $standardMap = array(
        'Yii2' => '#VENDOR#/yiisoft/yii2-coding-standards/Yii2',
        'Yii2Ext' => '#SELF#/Yii2Ext',
        'PSR2Ext' => '#SELF#/PSR2Ext',
    );

    protected $defaultConfigParams = array(
        'STANDARD' => 'Yii2',
        'ENCODING' => 'utf-8',
        'IGNORE_WARNINGS' => false,
        'PROGRESS' => true,
        'COLORS' => true,
        'FILTER_NO_ABORT' => false,
        'EXTENSIONS' => 'js,css,php,phtml',
        'PHPEXTENSIONS' => 'php,phtml',
    );

    protected $configParams = array();
    protected $cache = array();

    public function __construct($readProjectConfig = true)
    {
        if ($readProjectConfig) {
            $this->readProjectConfig();
        }
    }

    public function getConfigParam($name)
    {
        if (array_key_exists($name, $this->configParams)) {
            return $this->configParams[$name];
        } elseif (array_key_exists($name, $this->defaultConfigParams)) {
            return $this->defaultConfigParams[$name];
        }
        return null;
    }

    public function setConfigParam($name, $value)
    {
        if (is_null($value)) {
            if (array_key_exists($name, $this->configParams)) {
                unset($this->configParams[$name]);
            }
        } else {
            $this->configParams[$name] = $value;
        }
    }

    public function readProjectConfig($configName = '.phpcsgit')
    {
        $simpleFileName = "{$this->getSelfDir()}/.phpcsgit";
        $customFileName = "{$this->getProjectDir()}/{$configName}";
        $simpleData = $this->readProjectConfigToArray($simpleFileName);
        $customData = $this->readProjectConfigToArray($customFileName);
        if ($simpleData && array_key_exists('VERSION', $simpleData)) {
            if (!$customData) {
                $this->installProjectConfig($customFileName, $simpleFileName);
                $customData = $this->readProjectConfigToArray($customFileName);
            } elseif (!array_key_exists('VERSION', $customData)) {
                if ($this->updateProjectConfig($customFileName, $simpleFileName, $customData, $simpleData)) {
                    $customData = $this->readProjectConfigToArray($customFileName);
                }
            } elseif (version_compare($customData['VERSION'], $simpleData['VERSION'], '<')) {
                if ($this->updateProjectConfig($customFileName, $simpleFileName, $customData, $simpleData)) {
                    $customData = $this->readProjectConfigToArray($customFileName);
                }
            }
        }
        $customData = array_merge($simpleData, $customData);
        foreach ($customData as $key => $value) {
            switch ($key) {
                case 'IGNORE_WARNINGS':
                case 'PROGRESS':
                case 'COLORS':
                case 'FILTER_NO_ABORT':
                    $value = !in_array(strtoupper($value), array('', 'N', 'FALSE', '0'), true);
                    $this->setConfigParam($key, $value);
                    break;
                default:
                    $this->setConfigParam($key, $value);
            }
        }
    }

    protected function installProjectConfig($customFileName, $simpleFileName)
    {
        echo "Copy config from \"{$simpleFileName}\" to \"{$customFileName}\"\n";
        @mkdir(dirname($customFileName), 0777, true);
        @copy($simpleFileName, $customFileName);
    }

    protected function updateProjectConfig($customFileName, $simpleFileName, $customData, $simpleData)
    {
        if (!is_file($customFileName) || !is_readable($customFileName) || !is_writable($customFileName)) {
            return;
        }
        $customFile = @file($customFileName);
        $simpleFile = @file($simpleFileName);
        if (!is_array($customFile) || !is_array($simpleFile)) {
            return false;
        }
        foreach ($customFile as &$line) {
            $line = rtrim($line, "\r\n");
        }
        foreach ($simpleFile as &$line) {
            $line = rtrim($line, "\r\n");
        }
        $newFile = false;
        if (!array_key_exists('VERSION', $customData)) {
            $newFile = $this->updateProjectConfigTo20160915($customFile, $simpleFile, $customData, $simpleData);
        } elseif (version_compare($customData['VERSION'], '2016.09.20', '<')) {
            $newFile = $this->updateProjectConfigTo20160920($customFile, $simpleFile, $customData, $simpleData);
        } elseif (version_compare($customData['VERSION'], '2016.10.17', '<')) {
            $newFile = $this->updateProjectConfigTo20161017($customFile, $simpleFile, $customData, $simpleData);
        } elseif (version_compare($customData['VERSION'], '2016.10.19', '<')) {
            $newFile = $this->updateProjectConfigTo20161019($customFile, $simpleFile, $customData, $simpleData);
        }
        if ($newFile) {
            echo "Update config \"{$customFileName}\"\n";
            $newFile[] = '';
            if (@file_put_contents($customFileName, implode("\n", $newFile))) {
                return true;
            }
        }
        return false;
    }

    protected function updateProjectConfigTo20160915($customFile, $simpleFile, $customData, $simpleData)
    {
        $newFile = array();
        foreach ($simpleFile as $line) {
            if ($line && $line{0} == '#') {
                $newFile[] = $line;
            }
            if (substr($line, 0, 3) == 'VER') {
                $newFile[] = $line;
            }
        }
        $fna = false;
        foreach ($customFile as $line) {
            if ($line && $line{0} != '#') {
                $newFile[] = $line;
            }
            if (substr($line, 0, 16) == 'FILTER_NO_ABORT=') {
                $fna = true;
            }
        }
        if (!$fna) {
            $newFile[] = "FILTER_NO_ABORT=N";
        }
        return $this->updateProjectConfigTo20160920($newFile, $simpleFile, $customData, $simpleData);
    }

    protected function updateProjectConfigTo20160920($customFile, $simpleFile, $customData, $simpleData)
    {
        $newFile = $customFile;
        foreach ($newFile as &$line) {
            if (substr($line, 0, 8) == 'VERSION=') {
                $line = "VERSION=2016.09.20";
            }
        }
        $extensionsValue = $simpleData['EXTENSIONS'];
        $phpextensionsValue = $simpleData['PHPEXTENSIONS'];
        $newFile[] = "EXTENSIONS={$extensionsValue}";
        $newFile[] = "PHPEXTENSIONS={$phpextensionsValue}";
        return $this->updateProjectConfigTo20161017($newFile, $simpleFile, $customData, $simpleData);
    }

    protected function updateProjectConfigTo20161017($customFile, $simpleFile, $customData, $simpleData)
    {
        $newFile = $customFile;
        foreach ($newFile as &$line) {
            if (substr($line, 0, 8) == 'VERSION=') {
                $line = "VERSION=2016.10.17";
            }
        }
        $ignoreValue = $simpleData['IGNORE'];
        $newFile[] = "IGNORE={$ignoreValue}";
        return $this->updateProjectConfigTo20161019($newFile, $simpleFile, $customData, $simpleData);
    }

    protected function updateProjectConfigTo20161019($customFile, $simpleFile, $customData, $simpleData)
    {
        $newFile = $customFile;
        foreach ($newFile as &$line) {
            if (substr($line, 0, 8) == 'VERSION=') {
                $line = "VERSION=2016.10.19";
            }
        }
        $jshintValue = $simpleData['JSHINT'];
        $newFile[] = "JSHINT={$jshintValue}";
        return $newFile;
    }

    protected function readProjectConfigToArray($fileName, $raw = false)
    {
        if (!is_file($fileName) || !is_readable($fileName)) {
            return;
        }
        $file = @file($fileName);
        if (!is_array($file)) {
            return false;
        }
        $params = array();
        foreach ($file as $line) {
            if ($line && $line{0} == '#') {
                continue;
            }
            $lineSplit = explode('=', $line, 2);
            if (count($lineSplit) != 2) {
                continue;
            }
            $key = trim($lineSplit[0]);
            $value = trim($lineSplit[1]);
            if (strlen($value) > 1 && in_array($value{0}, array('"', "'")) && substr($value, -1) ==  $value{0}) {
                $value = stripslashes(substr($value, 1, -1));
            }
            $params[$key] = $value;
        }
        return $params;
    }

    public function replaceArgv($params)
    {
        $this->setArgv($this->createArgv($params));
    }

    public function createArgv($params, $noFirsArg = false)
    {
        $oldArgv = $_SERVER['argv'];
        $argv = array(array_shift($oldArgv));
        if ($noFirsArg) {
            $argv = array();
        }
        if (is_string($params)) {
            $params = explode(' ', $params);
        }
        foreach ($params as $param) {
            switch ($param) {
                case 'standard':
                    $value = $this->prepareParamStandard($this->getConfigParam('STANDARD'));
                    if ($value) {
                        $argv[] = "--standard={$value}";
                    }
                    break;
                case 'encoding':
                    $value = $this->getConfigParam('ENCODING');
                    if ($value) {
                        $argv[] = "--encoding={$value}";
                    }
                    break;
                case 'colors':
                        $argv[] = "--runtime-set";
                        $argv[] = "colors";
                        $argv[] = $this->getConfigParam('COLORS') ? '1' : '0';
                    break;
                case 'ignore_warnings':
                    if ($this->getConfigParam('IGNORE_WARNINGS')) {
                        $argv[] = '-n';
                    }
                    break;
                case 'progress':
                    if ($this->getConfigParam('PROGRESS')) {
                        $argv[] = '-p';
                    }
                    break;
                    $argv[] = $param;
                case 'extensions':
                    $value = $this->getExtensions();
                    if ($value) {
                        $argv[] = "--extensions={$value}";
                    }
                    break;
                case 'stdin_path':
                    $value = $this->getConfigParam('STDIN_PATH');
                    if ($value) {
                        $argv[] = "--stdin-path={$value}";
                    }
                    break;
                case 'filename':
                    $value = $this->getConfigParam('STDIN_PATH');
                    if ($value) {
                        $argv[] = "--filename";
                        $argv[] = $value;
                    }
                    break;
                case 'ignore':
                    $value = $this->getIgnore();
                    if ($value) {
                        $argv[] = "--ignore={$value}";
                    }
                    break;
                case '*':
                    $argv = array_merge($argv, $oldArgv);
                    break;
                default:
                    $argv[] = $param;
            }
        }
        return $argv;
    }

    public function createParamStr($params, $noFirsArg = false)
    {
        $argv = $this->createArgv($params, $noFirsArg);
        foreach ($argv as &$arg) {
            $arg = escapeshellarg($arg);
        }
        return implode(' ', $argv);
    }

    public function setArgv($newArgv)
    {
        $_SERVER['argv'] = $newArgv;
        $_SERVER['argc'] = count($newArgv);
        $GLOBALS['argv'] = $_SERVER['argv'];
        $GLOBALS['argc'] = $_SERVER['argc'];
    }

    public function getProjectDir()
    {
        if (!isset($this->cache['getProjectDir'])) {
            $cache = $this->getGitProjectDir();
            if (!$cache) {
                $cache = dirname($this->getVendorDir());
            }
            $this->cache['getProjectDir'] = $cache;
        }
        return $this->cache['getProjectDir'];
    }

    public function getGitProjectDir()
    {
        if (!isset($this->cache['getGitProjectDir'])) {
            $cache = false;
            $dir = dirname($this->getVendorDir());
            for ($i = 0; $i < 5; $i++) {
                if (is_file("{$dir}/.git/config")) {
                    $cache = $dir;
                    break;
                }
                $dir = dirname($dir);
            }
            $this->cache['getGitProjectDir'] = $cache;
        }
        return $this->cache['getGitProjectDir'];
    }

    public function getVendorDir()
    {
        if (!isset($this->cache['getVendorDir'])) {
            $this->cache['getVendorDir'] = dirname(dirname($this->getSelfDir()));
        }
        return $this->cache['getVendorDir'];
    }

    public function getSelfDir()
    {
        return __DIR__;
    }

    public function getExtensions($php = false)
    {
        return str_replace(' ', '', $this->getConfigParam($php ? 'PHPEXTENSIONS' : 'EXTENSIONS'));
    }

    public function getExtensionsAsArray($php = false)
    {
        static $cache = array();
        $extStr = $this->getExtensions($php);
        if (!array_key_exists($extStr, $cache)) {
            $extList = explode(',', $extStr);
            foreach ($extList as &$ext) {
                if (strpos($ext, '/') !== false) {
                    $extArr = explode('/', $ext);
                    $ext = $extArr[0];
                }
            }
            $cache[$extStr] = $extList;
        }
        return $cache[$extStr];
    }

    public function getIgnore($asArray = false)
    {
        if (!isset($this->cache['getIgnore'])) {
            $ignore = $this->getConfigParam('IGNORE');
            if (!$ignore) {
                return false;
            }
            $projectDir = $this->getProjectDir();
            $ignoreArray = explode(',', str_replace(';', ',', $ignore));
            foreach ($ignoreArray as &$ignoreItem) {
                $ignoreItem = ltrim($ignoreItem, '\\/');
                $ignoreItem = str_replace('\\*', '*', preg_quote("{$projectDir}/{$ignoreItem}"));
                $ignoreItem = "^{$ignoreItem}$";
            }
            $this->cache['getIgnore'] = $ignoreArray;
        }
        return $asArray ? $this->cache['getIgnore'] : implode(',', $this->cache['getIgnore']);
    }

    public function isIgnoreFile($fileName)
    {
        $ignore = $this->getIgnore(true);
        if (!is_array($ignore)) {
            return false;
        }
        foreach ($ignore as $pattern) {
            $replacements = array(
                '\\,' => ',',
                '*'   => '.*',
            );
            if (DIRECTORY_SEPARATOR === '\\') {
                $replacements['/'] = '\\\\';
            }
            $pattern = strtr($pattern, $replacements);
            $pattern = "`{$pattern}`i";
            if (preg_match($pattern, $fileName) === 1) {
                return true;
            }
        }
        return false;
    }

    public function isCheckFile($fileName, $php = false)
    {
        foreach ($this->getExtensionsAsArray($php) as $ext) {
            if (substr_compare($fileName, ".{$ext}", 0 - (strlen($ext) + 1)) == 0) {
                return true;
            }
        }
        return false;
    }

    public function isJshintFile($fileName, $php = false)
    {
        $extStr = str_replace(' ', '', $this->getConfigParam('JSHINT'));
        $extList = explode(',', $extStr);
        foreach ($extList as $ext) {
            if (substr_compare($fileName, ".{$ext}", 0 - (strlen($ext) + 1)) == 0) {
                return true;
            }
        }
        return false;
    }

    protected function prepareParamStandard($standard)
    {
        if (array_key_exists($standard, $this->standardMap)) {
            $standard = $this->standardMap[$standard];
        }
        if (!is_string($standard) || !$standard) {
            return false;
        }
        $standard = str_ireplace('#VENDOR#', $this->getVendorDir(), $standard);
        $standard = str_ireplace('#SELF#', $this->getSelfDir(), $standard);
        return $standard;
    }

    public function fileStrReplace($fileName, $search, $replace)
    {
        $fileContent = @file_get_contents($fileName);
        if (!is_string($fileContent)) {
            return false;
        }
        $newFileContent = str_replace($search, $replace, $fileContent);
        if ($fileContent == $newFileContent) {
            return false;
        }
        return boolval(@file_put_contents($fileName, $newFileContent));
    }

    public  function exec($cmd, $stdin)
    {
        $result = array('exitcode' => -1, 'stdout' => '', 'stderr' => 'PHP: proc_open error');
        $descriptorspec = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
        $process = proc_open($cmd, $descriptorspec, $pipes);
        if (!is_resource($process)) {
            return $result;
        }
        fwrite($pipes[0], $stdin);
        fclose($pipes[0]);
        $result['stdout'] = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $result['stderr'] = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $result['exitcode'] = proc_close($process);
        return $result;
    }
}
