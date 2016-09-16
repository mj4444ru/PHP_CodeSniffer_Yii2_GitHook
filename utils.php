<?php

namespace PHP_CodeSniffer_Yii2_GitHook;

class Utils
{
    protected static $standardMap = array(
        'Yii2' => '#VENDOR#/yiisoft/yii2-coding-standards/Yii2',
        'Yii2Ext' => '#SELF#/Yii2Ext',
        'PSR2Ext' => '#SELF#/PSR2Ext',
    );

    protected static $defaultConfigParams = array(
        'STANDARD' => 'Yii2',
        'ENCODING' => 'utf-8',
        'COLORS' => true,
        'IGNORE_WARNINGS' => false,
        'PROGRESS' => true,
        'EXTENSIONS' => 'js,php,phtml',
        'PHPEXTENSIONS' => 'php,phtml',
    );

    protected static $configParams = array(
    );

    public static function getConfigParam($name)
    {
        if (array_key_exists($name, static::$configParams)) {
            return static::$configParams[$name];
        } elseif (array_key_exists($name, static::$defaultConfigParams)) {
            return static::$defaultConfigParams[$name];
        }
        return null;
    }

    public static function setConfigParam($name, $value)
    {
        if (is_null($value)) {
            if (array_key_exists($name, static::$configParams)) {
                unset(static::$configParams[$name]);
            }
        } else {
            static::$configParams[$name] = $value;
        }
    }

    public static function readProjectConfig($configName = '.phpcsgit')
    {
        $selfDir = static::getSelfDir();
        $projectDir = static::getProjectDir();
        $simpleFileName = "{$selfDir}/.phpcsgit";
        $customFileName = "{$projectDir}/{$configName}";
        $simpleData = static::readProjectConfigToArray($simpleFileName);
        $customData = static::readProjectConfigToArray($customFileName);
        if ($simpleData && array_key_exists('VERSION', $simpleData)) {
            if (!$customData) {
                static::installProjectConfig($customFileName, $simpleFileName);
                $customData = static::readProjectConfigToArray($customFileName);
            } elseif (!array_key_exists('VERSION', $customData)) {
                if (static::updateProjectConfig($customFileName, $simpleFileName, $customData, $simpleData)) {
                    $customData = static::readProjectConfigToArray($customFileName);
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
                    static::setConfigParam($key, $value);
                    break;
                default:
                    static::setConfigParam($key, $value);
            }
        }
    }

    protected static function installProjectConfig($customFileName, $simpleFileName)
    {
        fwrite(STDERR, "Copy config from \"{$simpleFileName}\" to \"{$customFileName}\"\n");
        @mkdir(dirname($customFileName), 0777, true);
        @copy($simpleFileName, $customFileName);
    }

    protected static function updateProjectConfig($customFileName, $simpleFileName, $customData, $simpleData)
    {
        if (!is_file($customFileName) || !is_readable($customFileName) || !is_writable($customFileName)) {
            return;
        }
        $customFile = @file($customFileName);
        $simpleFile = @file($simpleFileName);
        if (!is_array($customFile) || !is_array($simpleFile)) {
            return false;
        }
        $newFile = false;
        if (!array_key_exists('VERSION', $customData)) {
            $newFile = static::updateProjectConfigTo20160915($customFile, $simpleFile);
            if (!$newFile) {
                return false;
            }
        }
        if ($newFile) {
            fwrite(STDERR, "Update config \"{$customFileName}\"\n");
            if (@!file_put_contents($customFileName, implode('', $newFile))) {
                return false;
            }
        }
        return true;
    }

    protected static function updateProjectConfigTo20160915($customFile, $simpleFile)
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
            $newFile[] = "FILTER_NO_ABORT=N\n";
        }
        return $newFile;
    }

    protected static function readProjectConfigToArray($fileName, $raw = false)
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

    public static function replaceArgv($params)
    {
        static::setArgv(static::createArgv($params));
    }

    public static function createArgv($params)
    {
        $oldArgv = $_SERVER['argv'];
        $argv = array(array_shift($oldArgv));
        if (is_string($params)) {
            $params = explode(' ', $params);
        }
        foreach ($params as $param) {
            switch ($param) {
                case 'standard':
                    $value = static::prepareParamStandard(static::getConfigParam('STANDARD'));
                    if ($value) {
                        $argv[] = "--standard={$value}";
                    }
                    break;
                case 'encoding':
                    $value = static::getConfigParam('ENCODING');
                    if ($value) {
                        $argv[] = "--encoding={$value}";
                    }
                    break;
                case 'colors':
                        $argv[] = "--runtime-set";
                        $argv[] = "colors";
                        $argv[] = static::getConfigParam('COLORS') ? '1' : '0';
                    break;
                case 'ignore_warnings':
                    if (static::getConfigParam('IGNORE_WARNINGS')) {
                        $argv[] = '-n';
                    }
                    break;
                case 'progress':
                    if (static::getConfigParam('PROGRESS')) {
                        $argv[] = '-p';
                    }
                    break;
                    $argv[] = $param;
                case 'extensions':
                    $value = str_replace(' ', '', static::getExtensions());
                    if ($value) {
                        $argv[] = "--extensions={$value}";
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

    public static function setArgv($newArgv)
    {
        $_SERVER['argv'] = $newArgv;
        $_SERVER['argc'] = count($newArgv);
        $GLOBALS['argv'] = $_SERVER['argv'];
        $GLOBALS['argc'] = $_SERVER['argc'];
    }

    public static function getProjectDir()
    {
        static $cache;
        if (is_null($cache)) {
            $cache = static::getGitProjectDir();
            if (!$cache) {
                $cache = dirname(static::getVendorDir());
            }
        }
        return $cache;
    }

    public static function getGitProjectDir()
    {
        static $cache;
        if (is_null($cache)) {
            $cache = false;
            $dir = dirname(static::getVendorDir());
            for ($i = 0; $i < 5; $i++) {
                if (is_file("{$dir}/.git/config")) {
                    $cache = $dir;
                    break;
                }
                $dir = dirname($dir);
            }
        }
        return $cache;
    }

    public static function getVendorDir()
    {
        static $cache;
        if (is_null($cache)) {
            $cache = dirname(dirname(static::getSelfDir()));
        }
        return $cache;
    }

    public static function getSelfDir()
    {
        return __DIR__;
    }

    public static function getExtensions()
    {
        return static::getConfigParam('EXTENSIONS');
    }

    protected static function prepareParamStandard($standard)
    {
        if (array_key_exists($standard, static::$standardMap)) {
            $standard = static::$standardMap[$standard];
        }
        if (!is_string($standard) || !$standard) {
            return false;
        }
        $standard = str_ireplace('#VENDOR#', static::getVendorDir(), $standard);
        $standard = str_ireplace('#SELF#', static::getSelfDir(), $standard);
        return $standard;
    }

    public static function fileStrReplace($fileName, $search, $replace)
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
}
