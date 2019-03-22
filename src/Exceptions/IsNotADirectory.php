<?php
namespace AdinanCenci\FileCache\Exceptions;

class IsNotADirectory extends InvalidArgumentException implements 
    \Psr\SimpleCache\InvalidArgumentException
{
}
