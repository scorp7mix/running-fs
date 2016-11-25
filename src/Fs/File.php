<?php

namespace Running\Fs;

use Running\Core\ICanStoreSelf;

/**
 * File mapper
 *
 * Class File
 * @package Running\Fs
 */
class File
    implements ICanStoreSelf
{

    /**
     * Error codes
     */
    const ERRORS = [
        'EMPTY_PATH'             => 1,
        'FILE_NOT_EXISTS'        => 2,
        'FILE_NOT_READABLE'      => 3,
        'FILE_NOT_WRITEABLE'     => 4,
        'FILE_NOT_DELETABLE'     => 5,
        'FILE_IS_DIR'            => 6,
        'FILE_DESERIALIZE_ERROR' => 7,
    ];

    /** @var string|null $path */
    protected $path = null;

    /** @var mixed $contents */
    protected $contents = null;

    protected $isNew = true;
    protected $wasNew = true;
    protected $isChanged = false;
    protected $isDeleted = false;

    /**
     * @param string|null $path
     */
    public function __construct($path = null)
    {
        if (!empty($path)) {
            $this->setPath($path);
            if ($this->exists()) {
                $this->isNew = false;
                $this->wasNew = false;
            }
        }
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath(string $path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return bool
     * @throws \Running\Fs\Exception
     */
    public function exists(): bool
    {
        if (empty($this->path)) {
            throw new Exception('Empty file path', self::ERRORS['EMPTY_PATH']);
        }
        return file_exists($this->path);
    }

    /**
     * @return bool
     * @throws \Running\Fs\Exception
     */
    public function isFile(): bool
    {
        if (empty($this->path)) {
            throw new Exception('Empty file path', self::ERRORS['EMPTY_PATH']);
        }
        if (!file_exists($this->path)) {
            throw new Exception('File does not exists', self::ERRORS['FILE_NOT_EXISTS']);
        }
        return is_file($this->path);
    }

    /**
     * @return bool
     * @throws \Running\Fs\Exception
     */
    public function isDir(): bool
    {
        if (empty($this->path)) {
            throw new Exception('Empty file path', self::ERRORS['EMPTY_PATH']);
        }
        if (!file_exists($this->path)) {
            throw new Exception('File does not exists', self::ERRORS['FILE_NOT_EXISTS']);
        }
        return is_dir($this->path);
    }

    /**
     * @return bool
     * @throws \Running\Fs\Exception
     */
    public function isLink(): bool
    {
        if (empty($this->path)) {
            throw new Exception('Empty file path', self::ERRORS['EMPTY_PATH']);
        }
        if (!file_exists($this->path)) {
            throw new Exception('File does not exists', self::ERRORS['FILE_NOT_EXISTS']);
        }
        return is_link($this->path);
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function set($value)
    {
        $this->contents = $value;
        $this->isChanged = true;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->contents;
    }

    /**
     * @return $this
     * @throws \Running\Fs\Exception
     */
    public function load()
    {
        if (empty($this->path)) {
            throw new Exception('Empty file path', self::ERRORS['EMPTY_PATH']);
        }
        if (!file_exists($this->path)) {
            throw new Exception('File does not exists', self::ERRORS['FILE_NOT_EXISTS']);
        }
        if (is_dir($this->path)) {
            throw new Exception('Path is dir instead of file', self::ERRORS['FILE_IS_DIR']);
        }
        if (!is_readable($this->path)) {
            throw new Exception('File is not readable', self::ERRORS['FILE_NOT_READABLE']);
        }

        $contents = file_get_contents($this->path);
        if (serialize(false) == $contents) {
            $this->contents = false;
        } elseif (false !== ($data = @unserialize($contents))) {
            $this->contents = $data;
        } else {
            $this->contents = $contents;
        }

        $this->isNew = false;
        $this->wasNew = false;
        $this->isChanged = false;
        $this->isDeleted = false;

        return $this;
    }

    /**
     * @return $this
     * @throws \Running\Fs\Exception
     */
    public function reload()
    {
        return $this->load();
    }

    /**
     * @return $this
     * @throws \Running\Fs\Exception
     */
    public function save()
    {
        if (empty($this->path)) {
            throw new Exception('Empty file path', self::ERRORS['EMPTY_PATH']);
        }
        if (file_exists($this->path) && is_dir($this->path)) {
            throw new Exception('Path is dir instead of file', self::ERRORS['FILE_IS_DIR']);
        }

        $res = @file_put_contents($this->path, is_string($this->contents) ? $this->contents : serialize($this->contents));
        if (false === $res) {
            throw new Exception('File is not writeable', self::ERRORS['FILE_NOT_WRITEABLE']);
        }

        $this->isChanged = false;
        if ($this->isNew) {
            $this->isNew = false;
            $this->wasNew = true;
        }

        return $this;
    }

    public function delete()
    {
        if (empty($this->path)) {
            throw new Exception('Empty file path', self::ERRORS['EMPTY_PATH']);
        }
        if (!file_exists($this->path)) {
            throw new Exception('File does not exists', self::ERRORS['FILE_NOT_EXISTS']);
        }
        if (is_dir($this->path)) {
            throw new Exception('Path is dir instead of file', self::ERRORS['FILE_IS_DIR']);
        }

        $res = @unlink($this->path);
        if (false === $res) {
            throw new Exception('File is not deletable', self::ERRORS['FILE_NOT_DELETABLE']);
        }

        $this->isDeleted = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->isNew;
    }

    /**
     * @return bool
     */
    public function wasNew(): bool
    {
        return $this->wasNew;
    }

    /**
     * @return bool
     */
    public function isChanged(): bool
    {
        return $this->isChanged;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

}