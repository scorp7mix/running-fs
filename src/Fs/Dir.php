<?php

namespace Running\Fs;

use Running\Core\Collection;

/**
 * Directory mapper
 *
 * Class Dir
 * @package Running\Fs
 */
class Dir
    extends File
{

    /** Error codes */
    const ERRORS = parent::ERRORS + [
        'PATH_NOT_DIR'     => 11,
        'SAVE_NOT_FOR_DIR' => 12,
        'MAKE_ERROR'       => 13,
    ];

    public function save()
    {
        throw new Exception('Directory cannot be saved', self::ERRORS['SAVE_NOT_FOR_DIR']);
    }

    /**
     * @param int $mode
     * @throws \Running\Fs\Exception
     * @return $this
     */
    public function make(int $mode = 0777)
    {
        if (empty($this->path)) {
            throw new Exception('Empty directory path', self::ERRORS['EMPTY_PATH']);
        }

        try {
            $result = mkdir($this->path, $mode);
        } catch (\Exception $e) {
            $result = false;
        }
        if (false === $result) {
            throw new Exception('Cannot make directory: ' . $this->path, self::ERRORS['MAKE_ERROR']);
        }
        return $this;
    }

    /**
     * @param int $order
     * @throws \Running\Fs\Exception
     * @return \Running\Core\Collection
     */
    public function list($order = \SCANDIR_SORT_NONE)
    {
        if (empty($this->path)) {
            throw new Exception('Empty directory path', self::ERRORS['EMPTY_PATH']);
        }
        if (!$this->isDir()) {
            throw new Exception('No such directory: ' . $this->path, self::ERRORS['PATH_NOT_DIR']);
        }

        $list = new Collection();
        foreach (scandir($this->path, $order) as $file) {
            $list->add(new File($this->path . DIRECTORY_SEPARATOR . $file));
        }

        return $list;
    }

    /**
     * @return \Running\Core\Collection
     */
    public function listRecursive()
    {
        $list = static::list();
        $ret = new Collection();
        foreach ($list as $file) {
            /** @var \Running\Fs\File $file */
            if ('.' == basename($file->getPath()) || '..' == basename($file->getPath())) {
                $ret->add($file);
                continue;
            }
            if ($file->isDir()) {
                $ret->merge((new self($file->getPath()))->list());
            } else {
                $ret->add($file);
            }
        }
        return $ret;
    }

}
