<?php

namespace Running\tests\Fs\Dir;

use Running\Core\Collection;
use Running\Fs\Exception;
use Running\Fs\Dir;
use Running\Fs\File;

class DirTest extends \PHPUnit_Framework_TestCase
{

    const TMP_PATH = __DIR__ . '/tmp';

    protected function setUp()
    {
        mkdir(self::TMP_PATH, 0777);
        mkdir(self::TMP_PATH . '/test.dir', 0777);
        file_put_contents(self::TMP_PATH . '/test1.txt', 'Hello, world!');
        file_put_contents(self::TMP_PATH . '/test.dir/test2.txt', 'Hello, world!');
    }

    public function testConstruct()
    {
        $dir = new Dir(self::TMP_PATH);

        $this->assertInstanceOf(Dir::class, $dir);
        $this->assertEquals(self::TMP_PATH, $dir->getPath());
    }

    public function testSetPath()
    {
        $dir = new Dir(self::TMP_PATH);
        $dir->setPath(self::TMP_PATH . '/test.dir');

        $this->assertEquals(self::TMP_PATH . '/test.dir', $dir->getPath());
        $this->assertTrue($dir->isDir());
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 11
     */
    public function testConstructPathIsDir()
    {
        $dir = new Dir(self::TMP_PATH . '/test1.txt');
        $this->assertFalse($dir->isDir());
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 11
     */
    public function testSetPathIsDir()
    {
        $dir = new Dir(self::TMP_PATH);

        $dir->setPath(self::TMP_PATH . '/test1.txt');
        $this->assertFalse($dir->isDir());
    }

    public function testList()
    {
        $dir = new Dir(self::TMP_PATH);

        $list = new Collection([
            new File(self::TMP_PATH . DIRECTORY_SEPARATOR . '.'),
            new File(self::TMP_PATH . DIRECTORY_SEPARATOR . '..'),
            new File(self::TMP_PATH . DIRECTORY_SEPARATOR . 'test.dir'),
            new File(self::TMP_PATH . DIRECTORY_SEPARATOR . 'test1.txt'),
        ]);

        $this->assertEquals($dir->list(), $list);
    }

    public function testListDescending()
    {
        $dir = new Dir(self::TMP_PATH);

        $list = new Collection([
            new File(self::TMP_PATH . DIRECTORY_SEPARATOR . 'test1.txt'),
            new File(self::TMP_PATH . DIRECTORY_SEPARATOR . 'test.dir'),
            new File(self::TMP_PATH . DIRECTORY_SEPARATOR . '..'),
            new File(self::TMP_PATH . DIRECTORY_SEPARATOR . '.'),
        ]);

        $this->assertEquals($dir->list(\SCANDIR_SORT_DESCENDING), $list);
    }

    public function testListRecursive()
    {
        $dir = new Dir(self::TMP_PATH);

        $list = new Collection([
            new File(self::TMP_PATH . DIRECTORY_SEPARATOR . '.'),
            new File(self::TMP_PATH . DIRECTORY_SEPARATOR . '..'),
            new File(self::TMP_PATH . DIRECTORY_SEPARATOR . 'test.dir' . DIRECTORY_SEPARATOR . '.'),
            new File(self::TMP_PATH . DIRECTORY_SEPARATOR . 'test.dir' . DIRECTORY_SEPARATOR . '..'),
            new File(self::TMP_PATH . DIRECTORY_SEPARATOR . 'test.dir' . DIRECTORY_SEPARATOR . 'test2.txt'),
            new File(self::TMP_PATH . DIRECTORY_SEPARATOR . 'test1.txt'),
        ]);

        $this->assertEquals($dir->listRecursive(), $list);
    }

    protected function tearDown()
    {
        unlink(self::TMP_PATH . '/test1.txt');
        unlink(self::TMP_PATH . '/test.dir/test2.txt');
        rmdir(self::TMP_PATH . '/test.dir');
        rmdir(self::TMP_PATH);
    }

}
