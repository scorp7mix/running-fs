<?php

namespace Running\Fs;

/**
 * File mapper
 *
 * Class File
 * @package Running\Fs
 */
class File
{

    /**
     * Error codes
     */
    const ERRORS = [
        'EMPTY_PATH'            => 1,
        'FILE_NOT_EXISTS'       => 2,
        'FILE_NOT_READABLE'     => 3,
        'FILE_NOT_WRITEABLE'    => 4,
        'FILE_IS_DIR'           => 5,
    ];

    /** @var string|null $path */
    protected $path = null;

    /** @var string|null $contents */
    protected $contents = null;

    /**
     * @param string|null $path
     */
    public function __construct($path = null)
    {
        if (!empty($path)) {
            $this->setPath($path);
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
     * @return string|null
     */
    public function getContents()/* : string|null */
    {
        return $this->contents;
    }

    /**
     * @param string|null $contents
     * @return $this
     */
    public function setContents(string $contents = null) {
        $this->contents = $contents;
        return $this;
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
        if ($this->isDir()) {
            throw new Exception('Path is dir instead of file', self::ERRORS['FILE_IS_DIR']);
        }
        if (!is_readable($this->path)) {
            throw new Exception('File is not readable', self::ERRORS['FILE_NOT_READABLE']);
        }
        $this->contents = file_get_contents($this->path);
        return $this;
    }

    /**
     * @return mixed
     * @throws \Running\Fs\Exception
     */
    public function return()
    {
        if (empty($this->path)) {
            throw new Exception('Empty file path', self::ERRORS['EMPTY_PATH']);
        }
        if (!file_exists($this->path)) {
            throw new Exception('File does not exists', self::ERRORS['FILE_NOT_EXISTS']);
        }
        if ($this->isDir()) {
            throw new Exception('Path is dir instead of file', self::ERRORS['FILE_IS_DIR']);
        }
        if (!is_readable($this->path)) {
            throw new Exception('File is not readable', self::ERRORS['FILE_NOT_READABLE']);
        }
        return include $this->path;
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
        if (!file_exists($this->path)) {
            throw new Exception('File does not exists', self::ERRORS['FILE_NOT_EXISTS']);
        }
        if ($this->isDir()) {
            throw new Exception('Path is dir instead of file', self::ERRORS['FILE_IS_DIR']);
        }
        if (!is_writable($this->path)) {
            throw new Exception('File is not writeable', self::ERRORS['FILE_NOT_WRITEABLE']);
        }
        file_put_contents($this->path, $this->contents);
        return $this;
    }

}