<?php

namespace Running\tests\Fs\File;

use Running\Fs\Exception;
use Running\Fs\File;

trait FileTestSave
{

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 1
     */
    public function testSaveEmpty()
    {
        $file = new File();
        $file->save();
        $this->fail();
    }

    public function testSaveNotWriteable()
    {
        if (PHP_OS != 'WINNT') {
            try {
                $file = new File(self::TMP_PATH . '/not.writable.txt');
                $file->save();
                $this->fail();
            } catch (Exception $e) {
                $this->assertEquals(4, $e->getCode());
            }
        }
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 6
     */
    public function testSaveIsDir()
    {
        $file = new File(self::TMP_PATH . '/test.dir');
        $file->save();
        $this->fail();
    }

    public function testSave()
    {
        $file = new File(self::TMP_PATH . '/contents.txt');
        $file->set('Written!');

        $this->assertFalse($file->isNew());
        $this->assertFalse($file->wasNew());
        $this->assertTrue($file->isChanged());
        $this->assertFalse($file->isDeleted());

        $file->save();

        $this->assertFalse($file->isNew());
        $this->assertFalse($file->wasNew());
        $this->assertFalse($file->isChanged());
        $this->assertFalse($file->isDeleted());

        $this->assertEquals('Written!', file_get_contents(self::TMP_PATH . '/contents.txt'));
        $this->assertEquals('Written!', (new File(self::TMP_PATH . '/contents.txt'))->load()->get());

        $file = new File(self::TMP_PATH . '/contents.txt');
        $file->set([1, 2, 3]);

        $this->assertFalse($file->isNew());
        $this->assertFalse($file->wasNew());
        $this->assertTrue($file->isChanged());
        $this->assertFalse($file->isDeleted());

        $file->save();

        $this->assertFalse($file->isNew());
        $this->assertFalse($file->wasNew());
        $this->assertFalse($file->isChanged());
        $this->assertFalse($file->isDeleted());

        $this->assertEquals(serialize([1, 2, 3]), file_get_contents(self::TMP_PATH . '/contents.txt'));
        $this->assertEquals([1, 2, 3], (new File(self::TMP_PATH . '/contents.txt'))->load()->get());
    }

    public function testSaveNew()
    {
        $file = new File(self::TMP_PATH . '/new.contents.txt');

        $this->assertTrue($file->isNew());
        $this->assertTrue($file->wasNew());
        $this->assertFalse($file->isChanged());
        $this->assertFalse($file->isDeleted());

        $file->set(false);

        $this->assertTrue($file->isNew());
        $this->assertTrue($file->wasNew());
        $this->assertTrue($file->isChanged());
        $this->assertFalse($file->isDeleted());

        $file->save();

        $this->assertFalse($file->isNew());
        $this->assertTrue($file->wasNew());
        $this->assertFalse($file->isChanged());
        $this->assertFalse($file->isDeleted());

        $this->assertEquals(serialize(false), file_get_contents(self::TMP_PATH . '/new.contents.txt'));
        $this->assertEquals(false, (new File(self::TMP_PATH . '/new.contents.txt'))->load()->get());

        unlink(self::TMP_PATH . '/new.contents.txt');

        $file = new File(self::TMP_PATH . '/new.contents.txt');

        $this->assertTrue($file->isNew());
        $this->assertTrue($file->wasNew());
        $this->assertFalse($file->isChanged());
        $this->assertFalse($file->isDeleted());

        $file->set('Written!');

        $this->assertTrue($file->isNew());
        $this->assertTrue($file->wasNew());
        $this->assertTrue($file->isChanged());
        $this->assertFalse($file->isDeleted());

        $file->save();

        $this->assertFalse($file->isNew());
        $this->assertTrue($file->wasNew());
        $this->assertFalse($file->isChanged());
        $this->assertFalse($file->isDeleted());

        $this->assertEquals('Written!', file_get_contents(self::TMP_PATH . '/new.contents.txt'));
        $this->assertEquals('Written!', (new File(self::TMP_PATH . '/new.contents.txt'))->load()->get());

        unlink(self::TMP_PATH . '/new.contents.txt');

        $file = new File(self::TMP_PATH . '/new.contents.txt');

        $this->assertTrue($file->isNew());
        $this->assertTrue($file->wasNew());
        $this->assertFalse($file->isChanged());
        $this->assertFalse($file->isDeleted());

        $file->set([1, 2, 3]);

        $this->assertTrue($file->isNew());
        $this->assertTrue($file->wasNew());
        $this->assertTrue($file->isChanged());
        $this->assertFalse($file->isDeleted());

        $file->save();

        $this->assertFalse($file->isNew());
        $this->assertTrue($file->wasNew());
        $this->assertFalse($file->isChanged());
        $this->assertFalse($file->isDeleted());

        $this->assertEquals(serialize([1, 2, 3]), file_get_contents(self::TMP_PATH . '/new.contents.txt'));
        $this->assertEquals([1, 2, 3], (new File(self::TMP_PATH . '/new.contents.txt'))->load()->get());

        unlink(self::TMP_PATH . '/new.contents.txt');
    }

}