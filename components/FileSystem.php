<?php

namespace deadmantfa\mm\components;

use creocoder\flysystem\LocalFilesystem;
use deadmantfa\mm\models\Thumb;
use yii\base\Component;
use yii\helpers\FileHelper;

class FileSystem extends Component
{

    /**
     * @var \League\Flysystem\Filesystem
     */
    public $fs;

    /**
     * @var string
     */
    public $directorySeparator = '/';

    /**
     * @var boolean
     */
    public $local;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->local = ($this->fs instanceof LocalFilesystem);
    }

    /**
     * @param string $path
     * @return
     */
    public function normalizePath($path)
    {
        return FileHelper::normalizePath($path, $this->directorySeparator);
    }

    /**
     * @param string $path
     * @param boolean $recursive
     * @return array
     */
    public function listContents($path = '', $recursive = false)
    {
        $contents = $this->fs->listContents($path, $recursive);
        return $this->filterContents($contents, $recursive);
    }

    /**
     * @param array $contents
     * @return array
     */
    protected function filterContents($contents, $recursive = false)
    {
        $new = [];
        foreach ($contents as $f) {
            if ($recursive && isset($f['type']) && $f['type'] === 'dir') {
                continue;
            }
            if (isset($f['basename'])) {
                if (preg_match('#^\.#', $f['basename']))
                    continue;
            }
            if (isset($f['extension'])) {
                if (in_array($f['extension'], array_keys(Thumb::$extensions))) {
                    $f['thumb'] = Thumb::getThumbSrc($f['path']);
                }
            }
            $new[] = $f;
        }
        return $new;
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->fs, $method], $parameters);
    }

}
