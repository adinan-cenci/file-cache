<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*--------------------------------*/

require '../vendor/autoload.php';

use AdinanCenci\FileCache\Cache;

/*--------------------------------*/

$cache = new Cache(__DIR__.'/cache/');

/*--------------------------------*/

require 'resources/header.html';
echo 
'<div class="foreground">
    <h1>Locking</h1>
    <p>In order of see this example working, try opening this file in a second tab withing 3 seconds from opening the first one.</p>';

    $locked = $cache->lock('foobar');
    $sleep = $locked ? 3 : 0;

    echo 
    $locked ? 'locked succesfuly' : 'failure, another proccess holds the lock', '<br>';

    if ($sleep) {
        sleep($sleep);
    }
    
    echo 
    $cache->set('foobar', 'test') ? 'sucessfully writen' : 'failed';

echo         
'</div>';

require 'resources/footer.html';