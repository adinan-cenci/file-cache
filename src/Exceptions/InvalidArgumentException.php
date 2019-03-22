<?php
namespace AdinanCenci\FileCache\Exceptions;

class InvalidArgumentException extends CacheException implements 
    \Psr\SimpleCache\InvalidArgumentException
{
}
