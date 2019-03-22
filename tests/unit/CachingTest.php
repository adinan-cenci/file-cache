<?php

require __DIR__.'/Reflections.php';

use \AdinanCenci\Cache\Cache;

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

        $this->assertEquals($sanitized, $expected);
    }

    public function testGeneratingCacheFilename() 
    {
        $cache      = $this->newCache();

        $expected   = 'cache-myItemToBeCached.php';

        $filename   = $this->invokeProtectedMethod($cache, 'getCachedFileName', array(
            'key' => 'myItemToBeCached'
        ));

        $this->assertEquals($filename, $expected);        
    }

    public function testGeneratingExpirationFilename() 
    {
        $cache      = $this->newCache();

        $expected   = 'cache-myItemToBeCached.txt';

        $filename   = $this->invokeProtectedMethod($cache, 'getExpirationFileName', array(
            'key' => 'myItemToBeCached'
        ));

        $this->assertEquals($filename, $expected);        
    }

    public function testCachingData() 
    {
        $cache      = $this->newCache();

        $expected   = true;

        $cache->set('myData', 'value');

        $exists     = $this->invokeProtectedMethod($cache, 'has', array(
            'key' => 'myData'
        ));

        $this->assertEquals($exists, $expected);   
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

        $this->assertEquals($exists, $expected);   
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

        $this->assertEquals($expiration, $expected);   
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

        $this->assertEquals($expired, $expected);   
    }
    


    protected function newCache() 
    {        
        return new Cache(__DIR__.'/cache-directory');
    }    
}