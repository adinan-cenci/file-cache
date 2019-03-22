<?php
namespace AdinanCenci\FileCache;

class Cache implements \Psr\SimpleCache\CacheInterface 
{
    /** @property string    $directory  Path to directory to store the cached files */
    protected $directory    = null;
    
    /** @property array     $files      File class array */
    protected $files        = array();    

    public function __construct(/*string*/ $directory) 
    {
        $directory          = $this->sanitizeDir($directory);        
        $this->validateDir($directory);        
        $this->directory    = $directory;
    }

    /**
     * @param string $key unique identifier
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
        $this->closeAllFiles();

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
     * @param array $values name, value pairs
     * @param null|int|DateInterval $ttl 
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

    public function lock($key) 
    {
        return $this->getFile($key)->lock();
    }

    public function unlock($key) 
    {
        return $this->getFile($key)->unlock();
    }

    /*----------------------------------------------------*/

    /**
     * @param string $key Unique identifier
     * @return bool
     */
    protected function expired($key) 
    {
        $this->getFile($key)->expired;
    }

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

        return $file->setExpiration($key, $expiration);
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
        if (! $this->isFileRelatedToCache($file)) {
            $file = $this->getCachePath($file);
        }

        if (! isset($this->files[$file])) {
            $this->files[$file] = new File($file);
        }

        return $this->files[$file];
    }

    protected function closeAllFiles() 
    {
        foreach ($this->files as $file) {
            $file->close();
        }

        $this->files = array();
    }

    protected function getAllCacheRelatedFiles() 
    {
        $dir    = $this->directory;
        $files  = array_map(function($file) use ($dir) 
        {
            return $dir.$file;
        }, scandir($this->directory));

        return array_filter($files, [$this, 'isFileRelatedToCache']);
    }

    protected function validateKey($key) 
    {
        if (! $this->validKey($key)) {
            throw new InvalidArgumentException('Invalid key');
        }
    }

    protected function validateTtl($ttl) 
    {
        if (! $this->validTtl($ttl)) {
            throw new InvalidArgumentException('Invalid ttl');
        }        
    }

    protected function validateDir($directory) 
    {
        if (! $this->valideDir($directory)) {
            throw new InvalidArgumentException('The directory is either unwritable, unreadable or doesn\'t exist');
        }
    }

    /**
     * Evaluates if $key is a valid psr-16 id
     * @param string $key
     * @return bool
     */
    protected function validKey($key) 
    {
        # valid:                A-Za-z0-9_.
        # valid by extension:   çãâéõ ... etc
        # invalid:              {}()/\@:

        return !preg_match('/[\{\}\(\)\/\\\@]/', $key);
    }

    protected function isFileRelatedToCache($fileName) 
    {
        return preg_match('/cache-[A-Za-z0-9_.]*\.php$/', $fileName);
    }

    protected function validTtl($ttl) 
    {
        return is_int($ttl) or $ttl instanceof \DateInterval or $ttl == null;  
    }

    protected function valideDir($directory) 
    {
        return 
        file_exists($directory) and 
        is_dir($directory)      and 
        is_writable($directory) and 
        is_readable($directory);
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
