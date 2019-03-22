<?php
namespace AdinanCenci\FileCache;

class File 
{
    protected $path     = null;
    protected $file     = null;
    protected $locked   = false;

    public function __construct($path) 
    {
        return $this->path = $path;
    }

    public function __get($var) 
    {
        if ($var == 'locked') {
            return $this->locked;
        }

        if ($var == 'exists') {
            return $this->doesExists();
        }

        if ($var == 'expiration') {
            return $this->getExpiration();
        }

        if ($var == 'expired') {
            return $this->isExpired();
        }
    }

    public function doesExists() 
    {
        return file_exists($this->path);
    }

    /**
     * @return null|int timestamp
     */
    protected function getExpiration() 
    {
        if (! file_exists($this->path)) {
            return null;
        }

        return filemtime($this->path);
    }

    /**
     * @param string $key Unique identifier
     * @return bool
     */
    protected function isExpired() 
    {
        if ($this->expiration == null or $this->expiration == 1) {
            return false;
        }

        return time() >= $this->expiration;
    }

    /**
     * @param string $key Unique identifier
     * @param int timestamp
     */
    public function setExpiration($key, $time) 
    {
        return touch($this->path, $time);
    }

    public function delete() 
    {
        $this->close();
        if (file_exists($this->file)) {
            return unlink($this->file);
        }
        return true;
    }

    public function read() 
    {
        return fread($this->open(), filesize($this->path));
    }

    /**
     * @param string
     * @return bool
     */
    public function write($content) 
    {
        if (! $this->open()) {
            return false;
        }
        
        $success = fwrite($this->open(), $content);
        $this->close();

        return $success;
    }

    /**
     * @return bool
     */
    public function lock() 
    {
        $this->open();
        $locked = flock($this->file, LOCK_EX | LOCK_NB, $eWouldBlock);

        if ($this->file == false || $locked == false || $eWouldBlock) {
            return $this->locked = false;
        }

        return $this->locked = true;
    }

    /**
     * @return bool
     */
    public function unlock() 
    {
        if (! $this->locked) {
            return true;
        }

        $this->locked = false;
        return flock($this->file, LOCK_UN);        
    }
    
    /**
     * @return resource|false
     */
    public function open() 
    {
        if (! $this->file) {
            $mode       = file_exists($this->path) ? 'r+' : 'w+';
            $this->file = fopen($this->path, $mode);
        }

        return $this->file;
    }

    /**
     * @return bool
     */
    public function close() 
    {
        if (! $this->file) {
            return true;
        }

        $this->unlock();

        fclose($this->file);
        $this->file = null;

        return true;
    }
}
