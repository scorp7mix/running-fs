<?php

namespace Running\tests\Fs\File;

use Running\Fs\Exception;
use Running\Fs\File;

class FileTest extends \PHPUnit_Framework_TestCase
{

    const TMP_PATH = __DIR__ . '/tmp';

    protected function setUp()
    {
        mkdir(self::TMP_PATH, 0777);
        mkdir(self::TMP_PATH . '/test.dir', 0777);
        file_put_contents(self::TMP_PATH . '/contents.txt', 'Hello, world!');
        if (PHP_OS != 'WINNT') {
            file_put_contents(self::TMP_PATH . '/not.readable.txt', 'Hello, not readable file!');
            chmod(self::TMP_PATH . '/not.readable.txt', 0000);
            file_put_contents(self::TMP_PATH . '/not.writable.txt', 'Hello, not writable file!');
            chmod(self::TMP_PATH . '/not.writable.txt', 0000);
        }
        symlink(self::TMP_PATH . '/contents.txt', self::TMP_PATH . '/contents.lnk');
    }

    public function testConstruct()
    {
        $file = new File();

        $this->assertInstanceOf(File::class, $file);

        $file = new File(self::TMP_PATH . '/test.txt');

        $this->assertInstanceOf(File::class, $file);
        $this->assertEquals(self::TMP_PATH . '/test.txt', $file->getPath());
    }

    public function testGetSetPath()
    {
        $file = new File();
        $file->setPath(self::TMP_PATH . '/test.txt');

        $this->assertEquals(self::TMP_PATH . '/test.txt', $file->getPath());
    }

    public function testGetSet()
    {
        $file = new File();
        $this->assertNull($file->get());

        $file->set('Hello!');
        $this->assertEquals('Hello!', $file->get());

        $file->set([1, 2, 3]);
        $this->assertEquals([1, 2, 3], $file->get());
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 1
     */
    public function testIsFileEmpty()
    {
        $file = new File();
        $this->assertFalse($file->isFile());
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 2
     */
    public function testIsFileNotExists()
    {
        $file = new File(self::TMP_PATH . '/not.exists');
        $this->assertFalse($file->isFile());
    }

    public function testIsFile()
    {
        $file = new File(self::TMP_PATH . '/contents.txt');
        $this->assertTrue($file->isFile());

        $file = new File(self::TMP_PATH . '/test.dir');
        $this->assertFalse($file->isFile());
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 1
     */
    public function testIsDirEmpty()
    {
        $file = new File();
        $this->assertFalse($file->isDir());
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 2
     */
    public function testIsDirNotExists()
    {
        $file = new File(self::TMP_PATH . '/not.exists');
        $this->assertFalse($file->isDir());
    }

    public function testIsDir()
    {
        $file = new File(self::TMP_PATH . '/test.dir');
        $this->assertTrue($file->isDir());

        $file = new File(self::TMP_PATH . '/contents.txt');
        $this->assertFalse($file->isDir());
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 1
     */
    public function testIsLinkEmpty()
    {
        $file = new File();
        $this->assertFalse($file->isLink());
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 2
     */
    public function testIsLinkNotExists()
    {
        $file = new File(self::TMP_PATH . '/not.exists');
        $this->assertFalse($file->isLink());
    }

    public function testIsLink()
    {
        $file = new File(self::TMP_PATH . '/contents.lnk');
        $this->assertTrue($file->isLink());

        $file = new File(self::TMP_PATH . '/contents.txt');
        $this->assertFalse($file->isLink());
    }

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
     * @expectedExceptionCode 5
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
        $this->assertEquals('Hello, world!', $file->get());
    }

    public function testReLoad()
    {
        $file = new File(self::TMP_PATH . '/contents.txt');
        $file->load();

        $file->set('Wrong contents');
        $file->reload();

        $this->assertEquals('Hello, world!', $file->get());
    }

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
     * @expectedExceptionCode 5
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
        $file->set('Written!')->save();

        $this->assertEquals('Written!', file_get_contents(self::TMP_PATH . '/contents.txt'));
        $this->assertEquals('Written!', (new File(self::TMP_PATH . '/contents.txt'))->load()->get());

        $file = new File(self::TMP_PATH . '/contents.txt');
        $file->set([1, 2, 3])->save();

        $this->assertEquals(serialize([1, 2, 3]), file_get_contents(self::TMP_PATH . '/contents.txt'));
        $this->assertEquals([1, 2, 3], (new File(self::TMP_PATH . '/contents.txt'))->load()->get());
    }

    public function testSaveNew()
    {
        $file = new File(self::TMP_PATH . '/new.contents.txt');
        $file->set(false)->save();

        $this->assertEquals(serialize(false), file_get_contents(self::TMP_PATH . '/new.contents.txt'));
        $this->assertEquals(false, (new File(self::TMP_PATH . '/new.contents.txt'))->load()->get());

        unlink(self::TMP_PATH . '/new.contents.txt');

        $file = new File(self::TMP_PATH . '/new.contents.txt');
        $file->set('Written!')->save();

        $this->assertEquals('Written!', file_get_contents(self::TMP_PATH . '/new.contents.txt'));
        $this->assertEquals('Written!', (new File(self::TMP_PATH . '/new.contents.txt'))->load()->get());

        unlink(self::TMP_PATH . '/new.contents.txt');

        $file = new File(self::TMP_PATH . '/new.contents.txt');
        $file->set([1, 2, 3])->save();

        $this->assertEquals(serialize([1, 2, 3]), file_get_contents(self::TMP_PATH . '/new.contents.txt'));
        $this->assertEquals([1, 2, 3], (new File(self::TMP_PATH . '/new.contents.txt'))->load()->get());

        unlink(self::TMP_PATH . '/new.contents.txt');
    }

    protected function tearDown()
    {
        unlink(self::TMP_PATH . '/contents.lnk');
        if (PHP_OS != 'WINNT') {
            chmod(self::TMP_PATH . '/not.writable.txt', 0777);
            unlink(self::TMP_PATH . '/not.writable.txt');
            chmod(self::TMP_PATH . '/not.readable.txt', 0777);
            unlink(self::TMP_PATH . '/not.readable.txt');
        }
        unlink(self::TMP_PATH . '/contents.txt');
        rmdir(self::TMP_PATH . '/test.dir');
        rmdir(self::TMP_PATH);
    }

}