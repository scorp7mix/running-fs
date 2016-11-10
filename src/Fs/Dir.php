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

    /** Error code */
    const PATH_NOT_DIR = 11;

    /**
     * @param string|null $path
     * @throws \Running\Fs\Exception
     */
    public function __construct($path = null)
    {
        parent::__construct($path);

        if (!$this->isDir()) {
            throw new Exception('No such dir: ' . $path, self::PATH_NOT_DIR);
        }
    }

    /**
     * @param string $path
     * @throws \Running\Fs\Exception
     * @return $this
     */
    public function setPath(string $path)
    {
        if (!is_dir($path)) {
            throw new Exception('No such dir: ' . $path, self::PATH_NOT_DIR);
        }

        $this->path = $path;
        return $this;
    }

    /**
     * @param int $order
     * @return \Running\Core\Collection
     */
    public function list($order = \SCANDIR_SORT_NONE)
    {
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
        $list = self::list();
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
