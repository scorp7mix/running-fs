<?php

namespace Running\tests\Fs\File;

use Running\Fs\Exception;
use Running\Fs\File;

require_once __DIR__ . '/FileTestLoad.php';
require_once __DIR__ . '/FileTestSave.php';
require_once __DIR__ . '/FileTestDelete.php';

class FileTest extends \PHPUnit_Framework_TestCase
{

    const TMP_PATH = __DIR__ . '/tmp';

    use FileTestLoad;
    use FileTestSave;
    use FileTestDelete;

    protected function setUp()
    {
        mkdir(self::TMP_PATH, 0777);
        mkdir(self::TMP_PATH . '/test.dir', 0777);
        file_put_contents(self::TMP_PATH . '/contents.txt', 'Hello, world!');
        file_put_contents(self::TMP_PATH . '/contents.serialized.txt', serialize([1, 2, 3]));
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
        $this->assertTrue($file->isNew());
        $this->assertTrue($file->wasNew());
        $this->assertFalse($file->isChanged());
        $this->assertFalse($file->isDeleted());

        $file = new File(self::TMP_PATH . '/test.txt');

        $this->assertInstanceOf(File::class, $file);
        $this->assertEquals(self::TMP_PATH . '/test.txt', $file->getPath());
        $this->assertTrue($file->isNew());
        $this->assertTrue($file->wasNew());
        $this->assertFalse($file->isChanged());
        $this->assertFalse($file->isDeleted());

        $file = new File(self::TMP_PATH . '/contents.txt');
        $this->assertEquals(self::TMP_PATH . '/contents.txt', $file->getPath());
        $this->assertFalse($file->isNew());
        $this->assertFalse($file->wasNew());
        $this->assertFalse($file->isChanged());
        $this->assertFalse($file->isDeleted());
    }

    public function testGetSetPath()
    {
        $file = new File();
        $file->setPath(self::TMP_PATH . '/test.txt');

        $this->assertEquals(self::TMP_PATH . '/test.txt', $file->getPath());
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 1
     */
    public function testExistsEmpty()
    {
        $file = new File();
        $this->assertFalse($file->exists());
    }

    public function testExists()
    {
        $file = new File(self::TMP_PATH . '/test.txt');
        $this->assertFalse($file->exists());

        $file = new File(self::TMP_PATH . '/contents.txt');
        $this->assertTrue($file->exists());
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
    
    protected function tearDown()
    {
        unlink(self::TMP_PATH . '/contents.lnk');
        if (PHP_OS != 'WINNT') {
            chmod(self::TMP_PATH . '/not.writable.txt', 0777);
            unlink(self::TMP_PATH . '/not.writable.txt');
            chmod(self::TMP_PATH . '/not.readable.txt', 0777);
            unlink(self::TMP_PATH . '/not.readable.txt');
        }
        unlink(self::TMP_PATH . '/contents.serialized.txt');
        unlink(self::TMP_PATH . '/contents.txt');
        rmdir(self::TMP_PATH . '/test.dir');
        rmdir(self::TMP_PATH);
    }

}