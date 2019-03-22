<?php 
use AdinanCenci\FileCache\Cache;
use AdinanCenci\FileCache\Exceptions\DirectoryDoesNotExist;
use AdinanCenci\FileCache\Exceptions\IsNotADirectory;
use AdinanCenci\FileCache\Exceptions\DirectoryIsNotReadable;
use AdinanCenci\FileCache\Exceptions\DirectoryIsNotWritable;
use AdinanCenci\FileCache\Exceptions\InvalidCacheId;
use AdinanCenci\FileCache\Exceptions\InvalidTimeToLive;

class CachingTest extends \PHPUnit\Framework\TestCase 
{
    protected $cacheDir = null;

    public function __construct($name = null, $data = [], $dataName = '') 
    {
        $this->cacheDir = __DIR__.'/cache-directory/';
        parent::__construct($name, $data, $dataName);
    }

    public function testExceptionDirDoesntExist() 
    {
        $this->assertEquals(1, 1);
        $dir = __DIR__.'/non-existing-directory/';

        try {
            new Cache($dir);
        } catch (DirectoryDoesNotExist $e) {
            $exception = $e->getMessage();
        }

        $expecting = 
        'Directory '.$dir.' doesn\'t exists';
        $this->assertEquals($expecting, $exception);
    }

    public function testExceptionDirIsNotWritable() 
    {
        chmod($this->cacheDir, 0100);

        try {
            new Cache($this->cacheDir);
        } catch (DirectoryIsNotWritable $e) {
            $exception = $e->getMessage();
        }

        chmod($this->cacheDir, 0777);

        $expecting = 
        $this->cacheDir.' is not writable';
        $this->assertEquals($expecting, $exception);
    }
    
    public function testExceptionDirIsNotReadable() 
    {
        chmod($this->cacheDir, 0333);

        try {
            new Cache($this->cacheDir);
        } catch (DirectoryIsNotReadable $e) {
            $exception = $e->getMessage();
        }

        chmod($this->cacheDir, 0777);

        $expecting = 
        $this->cacheDir.' is not readable';
        $this->assertEquals($expecting, $exception);
    }

    public function testValidCacheIdentifier() 
    {
        $cache      = new Cache($this->cacheDir);

        $this->assertTrue($cache->isValidKey('thisShouldBeValid'));
        $this->assertFalse($cache->isValidKey('@this{Should}Not'));
    }

    public function testCachingValue() 
    {
        $cache      = new Cache($this->cacheDir);
        $success    = $cache->set('key', 'value');

        $this->assertTrue($success);
        $this->assertTrue(file_exists($this->cacheDir.'cache-key.php'));
    }

    public function testRetrievingCachedValue() 
    {
        $cache      = new Cache($this->cacheDir);
        $value      = 'foo bar';
        $success    = $cache->set('key2', $value);

        $this->assertEquals($value, $cache->get('key2'));
    }

    public function testDeleteCachedValue() 
    {
        $cache      = new Cache($this->cacheDir);
        $success    = $cache->delete('key3');

        $this->assertTrue($success);
        $this->assertFalse(file_exists($this->cacheDir.'cache-key3.php'));
    }

    public function testTimeToLive() 
    {
        $cache      = new Cache($this->cacheDir);
        $success    = $cache->set('key4', 'value', 2);

        sleep(3);

        $this->assertTrue($cache->expired('key4'));
    }
}
