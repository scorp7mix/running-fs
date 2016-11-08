<?php

namespace Running\tests\Fs\File;

use Running\Fs\File;

class FileTest extends \PHPUnit_Framework_TestCase
{

    const TMP_PATH = __DIR__ . '/tmp';

    protected function setUp()
    {
        mkdir(self::TMP_PATH, 0777);
        mkdir(self::TMP_PATH . '/test.dir', 0777);
        file_put_contents(self::TMP_PATH . '/contents.txt', 'Hello, world!');
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

    protected function tearDown()
    {
        unlink(self::TMP_PATH . '/contents.lnk');
        unlink(self::TMP_PATH . '/contents.txt');
        rmdir(self::TMP_PATH . '/test.dir');
        rmdir(self::TMP_PATH);
    }

}