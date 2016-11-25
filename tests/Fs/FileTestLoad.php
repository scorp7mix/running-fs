<?php

namespace Running\tests\Fs\File;

use Running\Fs\Exception;
use Running\Fs\File;

trait FileTestLoad
{

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 1
     */
    public function testLoadEmpty()
    {
        $file = new File();
        $file->load();
        $this->fail();
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 2
     */
    public function testLoadNotExists()
    {
        $file = new File(self::TMP_PATH . '/not.exists');
        $file->load();
        $this->fail();
    }

    public function testLoadNotReadable()
    {
        if (PHP_OS != 'WINNT') {
            try {
                $file = new File(self::TMP_PATH . '/not.readable.txt');
                $file->load();
                $this->fail();
            } catch (Exception $e) {
                $this->assertEquals(File::ERRORS['FILE_NOT_READABLE'], $e->getCode());
            }
        }
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 6
     */
    public function testLoadIsDir()
    {
        $file = new File(self::TMP_PATH . '/test.dir');
        $file->load();
        $this->fail();
    }

    public function testLoad()
    {
        $file = new File(self::TMP_PATH . '/contents.txt');
        $file->load();

        $this->assertFalse($file->isNew());
        $this->assertFalse($file->wasNew());
        $this->assertFalse($file->isChanged());
        $this->assertFalse($file->isDeleted());

        $this->assertEquals('Hello, world!', $file->get());

        $file = new File(self::TMP_PATH . '/contents.serialized.txt');
        $file->load();

        $this->assertFalse($file->isNew());
        $this->assertFalse($file->wasNew());
        $this->assertFalse($file->isChanged());
        $this->assertFalse($file->isDeleted());

        $this->assertEquals([1, 2, 3], $file->get());
    }

    public function testReLoad()
    {
        $file = new File(self::TMP_PATH . '/contents.txt');
        $file->load();

        $this->assertFalse($file->isNew());
        $this->assertFalse($file->wasNew());
        $this->assertFalse($file->isChanged());
        $this->assertFalse($file->isDeleted());

        $file->set('Wrong contents');
        $file->reload();

        $this->assertFalse($file->isNew());
        $this->assertFalse($file->wasNew());
        $this->assertFalse($file->isChanged());
        $this->assertFalse($file->isDeleted());

        $this->assertEquals('Hello, world!', $file->get());
    }

}