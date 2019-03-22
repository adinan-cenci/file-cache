<?php
namespace AdinanCenci\FileCache\Exceptions;

class InvalidTimeToLive extends InvalidArgumentException implements 
    \Psr\SimpleCache\InvalidArgumentException
{
}
