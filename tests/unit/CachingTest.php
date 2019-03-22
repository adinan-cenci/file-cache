<?php
require __DIR__.'/Reflections.php';

use \AdinanCenci\FileCache\Cache;

class CachingTest extends Reflections
{
    public function __destruct() 
    {
        $this->newCache()->clear();
    }

    public function testSanitizingDirectoryPath() 
    {
        $cache      = $this->newCache();

        $expected   = 'path/to/cache/';

        $sanitized  = $this->invokeProtectedMethod($cache, 'sanitizeDir', array(
            'directory' => 'path\to\cache'
        ));

        $this->assertEquals($expected, $sanitized);
    }

    public function testGeneratingCacheFilename() 
    {
        $cache      = $this->newCache();

        $expected   = 'cache-myItemToBeCached.php';

        $filename   = $this->invokeProtectedMethod($cache, 'getCachedFileName', array(
            'key' => 'myItemToBeCached'
        ));

        $this->assertEquals($expected, $filename);
    }

    public function testGeneratingExpirationFilename() 
    {
        $cache      = $this->newCache();

        $expected   = 'cache-myItemToBeCached.txt';

        $filename   = $this->invokeProtectedMethod($cache, 'getExpirationFileName', array(
            'key' => 'myItemToBeCached'
        ));

        $this->assertEquals($expected, $filename);
    }

    public function testCachingData() 
    {
        $cache      = $this->newCache();

        $expected   = true;

        $cache->set('myData', 'value');

        $exists     = $this->invokeProtectedMethod($cache, 'has', array(
            'key' => 'myData'
        ));

        $this->assertEquals($expected, $exists);
    }

    public function testUnsettingCachedData() 
    {
        $cache      = $this->newCache();

        $expected   = false;

        $cache->set('myData', 'value');
        $cache->delete('myData');

        $exists     = $this->invokeProtectedMethod($cache, 'has', array(
            'key' => 'myData'
        ));

        $this->assertEquals($expected, $exists);
    }

    public function testExpirationDate() 
    {
        $cache      = $this->newCache();

        $ttl        = 60 * 60 * 5;
        $expected   = time() + $ttl;

        $cache->set('something', 'value', $ttl);

        $expiration = $this->invokeProtectedMethod($cache, 'getExpiration', array(
            'key' => 'something'
        ));

        $this->assertEquals($expected, $expiration);
    }

    public function testExpiration() 
    {
        $cache      = $this->newCache();

        $ttl        = 60 * 60 * 5;
        $expected   = false;

        $cache->set('something', 'value', $ttl);

        $expired    = $this->invokeProtectedMethod($cache, 'expired', array(
            'key' => 'something'
        ));

        $this->assertEquals($expected, $expired);
    }

    protected function newCache() 
    {        
        return new Cache(__DIR__.'/cache-directory/');
    }    
}