<?php
namespace AdinanCenci\FileCache;

use \AdinanCenci\FileCache\Exceptions\DirectoryDoesNotExist;
use \AdinanCenci\FileCache\Exceptions\IsNotADirectory;
use \AdinanCenci\FileCache\Exceptions\DirectoryIsNotReadable;
use \AdinanCenci\FileCache\Exceptions\DirectoryIsNotWritable;
use \AdinanCenci\FileCache\Exceptions\InvalidCacheId;
use \AdinanCenci\FileCache\Exceptions\InvalidTimeToLive;

class Cache implements \Psr\SimpleCache\CacheInterface 
{
    protected $directory    = null;
    protected $files        = array();    

    public function __construct($directory) 
    {
        $directory          = $this->sanitizeDir($directory);        
        $this->validateDir($directory);        
        $this->directory    = $directory;
    }

    /**
     * @param string $key Unique identifier
     * @param mixed $default Fallback value
     * @return mixed
     */
    public function get($key, $default = null) 
    {
        $this->validateKey($key);
    
        if (! $this->setted($key)) {
            return $default;
        }

        if ($this->expired($key)) {
            $this->delete($key);
            return $default;
        }

        return $this->load($key);
    }

    /**
     * @param string $key Unique identifier
     * @param mixed $value
     * @param null|int|\DateInterval $ttl Time to live
     * @return bool
     */
    public function set($key, $value, $ttl = null) 
    {
        $this->validateKey($key);
        $this->validateTtl($ttl);

        $expiration = 1;
        
        if ($ttl) {
            $expiration = $this->properTimestamp($ttl);
        }
        
        return $this->save($key, $value, $expiration);
    }

    /**
     * @param string $key Unique identifier
     * @return bool
     */
    public function delete($key) 
    {
        $this->validateKey($key);
        return $this->getFile($key)->delete();
    }

    /**
     * Clear the entire cache
     * @return bool
     */
    public function clear() 
    {
        $files = $this->getAllCacheRelatedFiles();
        foreach ($files as $file) {
            $this->getFile($file)->delete();
        }

        return count(array_filter($files, 'file_exists')) == 0;
    }

    /**
     * @param array $keys
     * @param mixed $default
     * @return array|\Iterator
     */
    public function getMultiple($keys, $default = null) 
    {
        $return = array();
        foreach ($keys as $key) {
            $return[] = $this->get($key, $default);
        }

        return $return;
    }

    /**
     * @param array $values key-value pairs
     * @param null|int|DateInterval $ttl Time to live
     * @param boolean
     */
    public function setMultiple($values, $ttl = null) 
    {
        $success = array();
        foreach ($values as $key => $value) {
            $success[] = $this->set($key, $value, $ttl);
        }

        return in_array(false, $success) ? false : true;
    }

    /**
     * @param array $keys
     * @param boolean
     */
    public function deleteMultiple($keys) 
    {
        $success = array();
        foreach ($keys as $key) {
            $success[] = $this->delete($key);
        }

        return in_array(false, $success) ? false : true;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key) 
    {
        $this->validateKey($key);
        return $this->setted($key);
    }

    /**
     * Evaluates if $key is a valid PSR-16 id
     * @param string $key
     * @return bool
     */
    public function isValidKey($key) 
    {
        # valid:                A-Za-z0-9_.
        # valid by extension:   çãâéõ ... etc
        # invalid:              {}()/\@:

        return !preg_match('/[\{\}\(\)\/\\\@]/', $key);
    }

    /**
     * @param string $key Unique identifier
     * @return bool
     */
    public function expired($key) 
    {
        return $this->getFile($key)->expired;
    }

    /*----------------------------------------------------*/

    /**
     * @param string $key Unique identifier
     * @return bool
     */
    protected function setted($key) 
    {
        return $this->getFile($key)->exists;
    }
    
    /**
     * Encode and saves content
     * @param string $key Unique identifier
     * @param mixed $content
     * @return bool
     */
    protected function save($key, $content, $expiration = 1) 
    {
        $file = $this->getFile($key);

        if (! $file->write($this->encode($content))) {
            return false;
        }

        return $file->setExpiration($expiration);
    }

    /**
     * Loads and decode content
     * @param string $key Unique identifier
     * @param mixed
     */
    protected function load($key) 
    {
        return $this->decode(
            $this->getFile($key)->read()
        );
    }

    /**
     * Returns a timestamp based on the current time plus a an interval
     * @param   int|\DateInterval $timeToLive 
     * @return  int|\DateInterval $seconds timestamp
     */
    protected function properTimestamp($timeToLive) 
    {
        if ($timeToLive instanceof \DateInterval) {
            $timeToLive = $timeToLive->format('s');
        }

        return time() + (int) $timeToLive;
    }

    /**
     * @param mixed $content
     * @return string
     */
    protected function encode($content) 
    {
        $encoded = base64_encode(serialize($content));
        return addslashes($encoded);
    }

    /**
     * @param string $content
     * @return mixed
     */
    protected function decode($content) 
    {
        return unserialize(base64_decode($content));
    }

    protected function getFile($file) 
    {
        if (! $this->isValidCacheFileName($file)) {
            $file = $this->getCachePath($file);
        }

        if (! isset($this->files[$file])) {
            $this->files[$file] = new File($file);
        }

        return $this->files[$file];
    }

    protected function getAllCacheRelatedFiles() 
    {
        $dir    = $this->directory;
        $files  = array_map(function($file) use ($dir) 
        {
            return $dir.$file;
        }, scandir($this->directory));

        return array_filter($files, [$this, 'isValidCacheFileName']);
    }

    protected function validateKey($key) 
    {
        if (! $this->isValidKey($key)) {
            throw new InvalidCacheId('"'.$key.'"" is an invalid cache ID');
        }
    }

    protected function validateTtl($ttl) 
    {
        if (! $this->validTtl($ttl)) {
            throw new InvalidTimeToLive('Invalid time to live');
        }
    }

    protected function validateDir($directory) 
    {
        if (! file_exists($directory)) {
            throw new DirectoryDoesNotExist('Directory '.$directory.' doesn\'t exists', 1);
            return false;
        }

        if (! is_dir($directory)) {
            throw new IsNotADirectory($directory.' is not a directory', 1);
            return false;
        }

        if (! is_writable($directory)) {
            throw new DirectoryIsNotWritable($directory.' is not writable', 1);
            return false;
        }

        if (! is_readable($directory)) {
            throw new DirectoryIsNotReadable($directory.' is not readable', 1);
            return false;
        }

        return true;
    }

    protected function isValidCacheFileName($string) 
    {
        return preg_match('/cache-[A-Za-z0-9_.]*\.php$/', $string);
    }

    protected function validTtl($ttl) 
    {
        return is_int($ttl) or $ttl instanceof \DateInterval or $ttl == null;  
    }

    protected function sanitizeDir($directory) 
    {
        return rtrim(str_replace('\\', '/', $directory), '/').'/';
    }

    protected function getCachePath($key) 
    {
        return $this->directory.$this->getCachedFileName($key);
    }

    protected function getCachedFileName($key) 
    {
        return 'cache-'.$key.'.php';
    }
}
