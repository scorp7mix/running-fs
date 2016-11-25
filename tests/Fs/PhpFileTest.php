<?php

namespace Running\tests\Fs\PhpFile;

use Running\Core\Std;
use Running\Fs\PhpFile;

class PhpFileTest extends \PHPUnit_Framework_TestCase
{

    const TMP_PATH = __DIR__ . '/tmp';

    protected function setUp()
    {
        mkdir(self::TMP_PATH, 0777);
    }

    public function testSave()
    {
        $filename = self::TMP_PATH . '/test.php';
        $file = new PhpFile($filename);

        $file->set(42)->save();
        $this->assertEquals("<?php" . PHP_EOL . PHP_EOL . "return 42;", file_get_contents(self::TMP_PATH . '/test.php'));

        $file->set([1, 2, 'foo'])->save();
        $expected = <<<'SAVED'
<?php

return [
  0 => 1,
  1 => 2,
  2 => 'foo',
];
SAVED;
        $this->assertEquals(
            str_replace("\r\n", "\n", $expected),
            str_replace("\r\n", "\n", file_get_contents(self::TMP_PATH . '/test.php'))
        );

        $file->set(new Std(['foo' => 'bar', 'baz' => 12]))->save();
        $expected = <<<'SAVED'
<?php

return Running\Core\Std::__set_state(array(
   '__data' =>
  [
    'foo' => 'bar',
    'baz' => 12,
  ],
));
SAVED;
        $this->assertEquals(
            str_replace("\r\n", "\n", $expected),
            str_replace("\r\n", "\n", file_get_contents(self::TMP_PATH . '/test.php'))
        );

        $file->delete();
    }

    protected function tearDown()
    {
        rmdir(self::TMP_PATH);
    }

}