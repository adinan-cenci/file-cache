<?php
namespace AdinanCenci\FileCache\Exceptions;

class DirectoryIsNotWritable extends InvalidArgumentException implements 
    \Psr\SimpleCache\InvalidArgumentException
{
}
