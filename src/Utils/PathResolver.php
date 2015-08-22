<?php

namespace Utils;

use Phine\Path\Path;

class PathResolver
{
    public function __construct(Path $path)
    {
        $this->path = $path;
    }
    public function canonical($path)
    {
        return $this->path->canonical($path);
    }
    public function relative($from, $to, $addDot = false)
    {
        $fromParts = array_values(array_filter($this->path->split($from)));
        $toParts = array_values(array_filter($this->path->split($to)));
        $i = 0;
        $count = min(count($fromParts), count($toParts));
        while ($fromParts[$i] === $toParts[$i] && $i < $count) {
            ++$i;
        }
        $pathToCommon = array_slice($toParts, $i);
        $upsNumber = count($fromParts) - $i;
        return $this->join(array_merge(
            $addDot ? ['.'] : [],
            $upsNumber ? array_fill(0, $upsNumber, '..') : [],
            $pathToCommon
        ));
    }
    public function join($elements)
    {
        return $this->path->join($elements);
    }
    public function read($filePath)
    {
        if ($this->exists($filePath)) {
            return file_get_contents($filePath);
        }
        if ($this->create($filePath)) {
            return "";
        } else {
            throw new \Exception("Unable to create file");
        }
    }
    public function create($filePath, $isDir=false)
    {
        $dirPath = $filePath;
        if (!$isDir) {
            $dirPath = dirname($dirPath);
        }
        if (!empty($dirPath) && !$this->exists($dirPath) && !$this->isDir($dirPath)) {
            mkdir($dirPath);
        } elseif (!$this->isDir($dirPath)) {
            throw new \Exception("Not a directory");
        }
        if (!$isDir) {
            if (!$this->exists($filePath)) {
                return touch($filePath);
            }
        }
    }
    public function getWorkingDirectory()
    {
        return $this->canonical(getcwd());
    }
    public function getAbsolutePath($path, $cwd = null)
    {
        if (!$cwd) {
            $cwd = $this->getWorkingDirectory();
        }
        return $this->join([$cwd,$path]);
    }
    public function remove($path)
    {
        $this->path->remove($path);
    }
    public function exists($filePath)
    {
        return file_exists($filePath);
    }
    public function write($filePath, $content = "")
    {
        if (!$this->exists($filePath)) {
            $this->create($filePath);
        }
        if (!$this->isFile($filePath)) {
            throw new \Exception("Unable to write to non-file");
        }
        return file_put_contents($filePath, $content);
    }
    public function isDir($path)
    {
        return is_dir($path);
    }
    public function isFile($path)
    {
        return is_file($path);
    }
    public function getDirFiles($dir)
    {
        return array_filter(scandir($dir), function ($file) {
            return $file !== '.' && $file !== '..' && $file !== '.git';
        });
    }
    public function getDirFilesRecursive($dir)
    {
        $files = [];
        foreach ($this->getDirFiles($dir) as $file) {
            $path = $this->join([$dir, $file]);
            if ($this->isFile($path)) {
                $files[] = $path;
            } elseif ($this->isDir($path)) {
                $files = array_merge(
                    $files,
                    $this->getDirFilesRecursive(
                        $path
                    )
                );
            }
        }
        return $files;
    }

    /** @var Path */
    private $path;
}
