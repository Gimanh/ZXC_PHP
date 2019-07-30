<?php

namespace ZXC\Native;

use InvalidArgumentException;

class CLI
{
    protected $params = [];
    protected $rootPath = '';
    protected $projectName = 'build';
    protected $rootPathWebFolder = '';
    protected $rootPathServerFolder = '';
    protected $rootPathConfigFolder = '';

    protected $destPathWebFolder = '';
    protected $destPathServerFolder = '';
    protected $destPathConfigFolder = '';

    public function __construct()
    {
        $argv = $_SERVER['argv'];
        $this->params = Helper::getArgs($argv);

        $this->rootPath = $this->getRootPath();
        $this->rootPathServerFolder = $this->createRootFolderPath('server');
        $this->rootPathWebFolder = $this->createRootFolderPath('web');
        $this->rootPathConfigFolder = $this->createRootFolderPath('config');

        if (isset($this->params['build'])) {
            $this->projectName = $this->params['build'] . '-build';

            $this->prepareDestinationFolder();
            $this->destPathServerFolder = $this->createDestFolder('server');
            $this->destPathWebFolder = $this->createDestFolder('web');
            $this->destPathConfigFolder = $this->createDestFolder('config');
            $this->createDestFolder('log');

            $this->cmdBuild();
        } else {
            throw new InvalidArgumentException('Parameter build is required "Example --build=MyProject"');
        }
    }

    public function cmdBuild()
    {
        $this->prepareServerFolder();
        $this->prepareWebFolder();
        $this->prepareConfigFolder();
    }

    private function prepareDestinationFolder()
    {
        $dest = $this->rootPath . $this->projectName . DIRECTORY_SEPARATOR;

        if (is_dir($dest)) {
//            Helper::rRmdir($dest);
            throw new InvalidArgumentException('Please remove directory ' . $dest . ' then run this command again');
        }

        if (!mkdir($dest, 0777, true)) {
            throw new InvalidArgumentException('Can not create ' . $dest);
        }
    }

    private function getRootPath()
    {
        return $this->normalizePath(ZXC_ROOT .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR);
    }

    private function normalizePath($path)
    {
        return str_replace("\\", "/", realpath($path)) . '/';
    }

    private function createRootFolderPath($folderName)
    {
        return $this->normalizePath($this->rootPath . $folderName . DIRECTORY_SEPARATOR);
    }

    private function createDestFolder($folderName)
    {
        $path = $this->rootPath . $this->projectName . DIRECTORY_SEPARATOR . $folderName . DIRECTORY_SEPARATOR;
        if (!mkdir($path, 0777, true)) {
            throw new InvalidArgumentException('Can not create folder ' . $path);
        }
        return $this->normalizePath($path);
    }

    protected function prepareServerFolder()
    {
        $this->echoMessage('server');
        $files = Helper::getFilesList($this->rootPathServerFolder . '*.php', 0, ['test']);

        $wasSize = 0;
        $isSize = 0;
        foreach ($files as $key => $file) {
            $wasSize += filesize($file);
            $minifiedCode = Helper::minifyPHPCode($file);
            if (!$minifiedCode) {
                throw new InvalidArgumentException('Can not minify code from ' . $file);
            }
            $newFileDestination = str_replace($this->rootPathServerFolder, $this->destPathServerFolder, $file);
            $pathInfo = pathinfo($newFileDestination);
            if (!is_dir($pathInfo['dirname'])) {
                if (!mkdir($pathInfo['dirname'], 0777, true)) {
                    throw new InvalidArgumentException("Can not create directory " . $newFileDestination);
                }
            }
            $result = file_put_contents($newFileDestination, $minifiedCode);
            if (!$result) {
                throw new InvalidArgumentException('Can not create file ' . $newFileDestination);
            }
            $isSize += filesize($newFileDestination);
            $this->show_status($key + 1, count($files), 'server');
        }
        $this->show_status($isSize, $wasSize, 'Compression ratio of server scripts', false);
        echo PHP_EOL;
    }

    protected function prepareWebFolder()
    {
        $this->echoMessage('web');
        $copyResult = Helper::rCopy($this->rootPathWebFolder . 'application/dist/', $this->destPathWebFolder);
        $this->show_status(1, 2, 'web');
        if (!$copyResult) {
            throw new InvalidArgumentException("Can not copy files from " . $this->rootPathWebFolder . 'application/dist/' . ' to ' . $this->destPathWebFolder);
        }
        $copyIndexFile = copy($this->rootPathWebFolder . 'index.php', $this->destPathWebFolder . 'index.php');
        $this->show_status(2, 2, 'web');
        if (!$copyIndexFile) {
            throw new InvalidArgumentException('Can not copy index file to ' . $this->destPathWebFolder);
        }
    }

    protected function prepareConfigFolder()
    {
        $this->echoMessage('config');
        $copyConfigResult = Helper::rCopy($this->rootPathConfigFolder, $this->destPathConfigFolder);
        $this->show_status(1, 1, 'config');
        if (!$copyConfigResult) {
            throw new InvalidArgumentException('Can not copy files from ' . $copyConfigResult);
        }
    }

    protected function echoMessage($folder, $status = 0)
    {
        echo 'Folder "' . $folder . '" build ' . ($status === 0 ? 'start' : 'done') . PHP_EOL;
    }

    /**
     * @param $done
     * @param $total
     * @param $info
     * @param bool $loadString
     * @method show_status
     * @link https://gist.github.com/mayconbordin/2860547
     */
    function show_status($done, $total, $info, $loadString = true)
    {
        $size = 30;
        $perc = round(($done * 100) / $total);
        $bar = round(($size * $perc) / 100);
        if ($perc == 100) {
            $info .= ' Complete';
        } else {
            if ($loadString) {
                $info .= ' Loading';
            }
        }
        echo sprintf(($perc < 100 ? "\t %s%% [%s%s] %s\r"  : "\t%s%% [%s%s] %s\r"), $perc, str_repeat("=", $bar), str_repeat(" ", $size - $bar), $info);
        if ($perc == 100) {
            echo PHP_EOL;
        }
    }
}