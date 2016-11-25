<?php

namespace Running\Fs;

/**
 * PHP "return-file" mapper
 *
 * Class PhpFile
 * @package Running\Fs
 */
class PhpFile
    extends File
{

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

        $contents = @include $this->path;
        if (false === $contents) {
            throw new Exception('PHP file may contains error', self::ERRORS['FILE_DESERIALIZE_ERROR']);
        }
        $this->contents = $contents;

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
    public function save()
    {
        if (empty($this->path)) {
            throw new Exception('Empty file path', self::ERRORS['EMPTY_PATH']);
        }
        if (file_exists($this->path) && is_dir($this->path)) {
            throw new Exception('Path is dir instead of file', self::ERRORS['FILE_IS_DIR']);
        }

        $str = preg_replace(['~^(\s*)array\s*\($~im', '~^(\s*)\)(\,?)$~im', '~\s+$~im'], ['$1[', '$1]$2', ''], var_export($this->get(), true));
        $res = @file_put_contents($this->path, '<?php' . PHP_EOL . PHP_EOL . 'return ' . $str . ';');

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

}