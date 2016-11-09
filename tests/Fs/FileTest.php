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
        file_put_contents(self::TMP_PATH . '/return.php', '<?php return 42;');
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

    public function testGetSetContents()
    {
        $file = new File();
        $this->assertNull($file->getContents());

        $file->setContents('Hello!');
        $this->assertEquals('Hello!', $file->getContents());
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
        $this->assertTrue(false);
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 2
     */
    public function testLoadNotExists()
    {
        $file = new File(self::TMP_PATH . '/not.exists');
        $file->load();
        $this->assertTrue(false);
    }

    public function testLoadNotReadable()
    {
        if (PHP_OS != 'WINNT') {
            try {
                $file = new File(self::TMP_PATH . '/not.readable.txt');
                $file->load();
                $this->assertTrue(false);
            } catch (Exception $e) {
                $this->assertEquals(3, $e->getCode());
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
        $this->assertTrue(false);
    }

    public function testLoad()
    {
        $file = new File(self::TMP_PATH . '/contents.txt');
        $file->load();
        $this->assertEquals('Hello, world!', $file->getContents());
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 1
     */
    public function testSaveEmpty()
    {
        $file = new File();
        $file->save();
        $this->assertTrue(false);
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 2
     */
    public function testSaveNotExists()
    {
        $file = new File(self::TMP_PATH . '/not.exists');
        $file->save();
        $this->assertTrue(false);
    }

    public function testSaveNotWriteable()
    {
        if (PHP_OS != 'WINNT') {
            try {
                $file = new File(self::TMP_PATH . '/not.writable.txt');
                $file->save();
                $this->assertTrue(false);
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
        $this->assertTrue(false);
    }

    public function testSave()
    {
        $file = new File(self::TMP_PATH . '/contents.txt');
        $file->setContents('Written!');
        $file->save();
        $this->assertEquals('Written!', file_get_contents(self::TMP_PATH . '/contents.txt'));
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 1
     */
    public function testReturnEmpty()
    {
        $file = new File();
        $file->return();
        $this->assertTrue(false);
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 2
     */
    public function testReturnNotExists()
    {
        $file = new File(self::TMP_PATH . '/not.exists');
        $file->return();
        $this->assertTrue(false);
    }

    public function testReturnNotReadable()
    {
        if (PHP_OS != 'WINNT') {
            try {
                $file = new File(self::TMP_PATH . '/not.readable.txt');
                $file->return();
                $this->assertTrue(false);
            } catch (Exception $e) {
                $this->assertEquals(3, $e->getCode());
            }
        }
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 5
     */
    public function testReturnIsDir()
    {
        $file = new File(self::TMP_PATH . '/test.dir');
        $file->return();
        $this->assertTrue(false);
    }

    public function testReturn()
    {
        $file = new File(self::TMP_PATH . '/return.php');
        $this->assertEquals(42, $file->return());
    }

    protected function tearDown()
    {
        unlink(self::TMP_PATH . '/return.php');
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