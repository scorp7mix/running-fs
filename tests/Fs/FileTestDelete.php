<?php

namespace Running\tests\Fs\File;

use Running\Fs\Exception;
use Running\Fs\File;

trait FileTestDelete
{

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 1
     */
    public function testDeleteEmpty()
    {
        $file = new File();
        $file->delete();
        $this->fail();
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 2
     */
    public function testDeleteNotExists()
    {
        $file = new File(self::TMP_PATH . '/not.exists');
        $file->delete();
        $this->fail();
    }

    public function testDeleteNotDeleteable()
    {
        if (PHP_OS != 'WINNT') {
            try {
                $file = new File(self::TMP_PATH . '/not.writable.txt');
                $file->delete();
                $this->fail();
            } catch (Exception $e) {
                $this->assertEquals(File::ERRORS['FILE_NOT_DELETABLE'], $e->getCode());
            }
        }
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 6
     */
    public function testDeleteIsDir()
    {
        $file = new File(self::TMP_PATH . '/test.dir');
        $file->delete();
        $this->fail();
    }

    public function testDelete()
    {
        $file = (new File(self::TMP_PATH . '/new.contents.txt'))->save();

        $this->assertTrue($file->exists());

        $this->assertFalse($file->isNew());
        $this->assertTrue($file->wasNew());
        $this->assertFalse($file->isChanged());
        $this->assertFalse($file->isDeleted());

        $file->delete();

        $this->assertFalse($file->exists());

        $this->assertFalse($file->isNew());
        $this->assertTrue($file->wasNew());
        $this->assertFalse($file->isChanged());
        $this->assertTrue($file->isDeleted());

        $file->save();

        $this->assertTrue($file->exists());

        $this->assertFalse($file->isNew());
        $this->assertTrue($file->wasNew());
        $this->assertFalse($file->isChanged());
        $this->assertFalse($file->isDeleted());

        unlink(self::TMP_PATH . '/new.contents.txt');
    }

}