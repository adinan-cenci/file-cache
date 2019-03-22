<?php
namespace AdinanCenci\FileCache\Exceptions;

class InvalidCacheId extends InvalidArgumentException implements 
    \Psr\SimpleCache\InvalidArgumentException
{
}
