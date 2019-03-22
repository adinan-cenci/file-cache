<?php
namespace AdinanCenci\FileCache\Exceptions;

class DirectoryDoesNotExist extends InvalidArgumentException implements 
    \Psr\SimpleCache\InvalidArgumentException
{
}
