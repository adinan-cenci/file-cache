<?php
namespace AdinanCenci\FileCache\Exceptions;

class DirectoryIsNotReadable extends InvalidArgumentException implements 
    \Psr\SimpleCache\InvalidArgumentException
{
}
