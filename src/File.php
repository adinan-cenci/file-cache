<?php
namespace AdinanCenci\FileCache;

class File 
{
    protected $path     = null;

    public function __construct($path) 
    {
        $this->path = $path;
    }

    public function __get($var) 
    {
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
     * @param int timestamp
     */
    public function setExpiration($time) 
    {
        return touch($this->path, $time);
    }

    public function delete() 
    {
        if (file_exists($this->path)) {
            return unlink($this->path);
        }

        return true;
    }

    public function read() 
    {
        $file     = fopen($this->path, 'r');
        $contents = fread($file, filesize($this->path));
        fclose($file);

        return $contents;
    }

    /**
     * @param string
     * @return bool
     */
    public function write($content) 
    {
        $file     = fopen($this->path, 'w');
        $this->lock($file);
        $bytes    = fwrite($file, $content);
        $this->unlock($file);
        fclose($file);

        return $bytes > 0;
    }

    protected function lock($file) 
    {
        $locked = flock($file, \LOCK_EX | \LOCK_NB, $eWouldBlock);

        if ($file == false || $locked == false || $eWouldBlock) {
            return false;
        }

        return true;
    }


    protected function unlock($file) 
    {
        return flock($file, \LOCK_UN);        
    }
}
