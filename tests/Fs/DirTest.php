<?php

namespace Running\tests\Fs\Dir;

use Running\Core\Collection;
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

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 12
     */
    public function testSave()
    {
        $dir = new Dir(self::TMP_PATH);
        $dir->save();
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 1
     */
    public function testMakeWithEmptyPath()
    {
        $dir = new Dir();
        $dir->make();
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 13
     */
    public function testMakeWithIncorrectPath()
    {
        $dir = new Dir(self::TMP_PATH . '/test1.txt');
        $dir->make();
    }

    public function testMake()
    {
        $dir = new Dir(self::TMP_PATH . '/new.dir');
        $dir->make();
        $this->assertTrue($dir->isDir());
        rmdir(self::TMP_PATH . '/new.dir');
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 1
     */
    public function testListWithEmptyPath()
    {
        $dir = new Dir();
        $dir->list();
    }

    /**
     * @expectedException \Running\Fs\Exception
     * @expectedExceptionCode 11
     */
    public function testListWithIncorrectPath()
    {
        $dir = new Dir(self::TMP_PATH . '/test1.txt');
        $dir->list();
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
