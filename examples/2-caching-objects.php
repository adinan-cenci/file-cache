<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*--------------------------------*/

require '../vendor/autoload.php';

use AdinanCenci\FileCache\Cache;

/*--------------------------------*/

$cache = new Cache(__DIR__.'/cache/');

/*--------------------------------*/

class Hero 
{
    protected $name;

    public function __construct($name) 
    {
        $this->name = $name;
    }

    public function getName() 
    {
        return $this->name;
    }
}

/*--------------------------------*/

$hero       = new Hero('Achilles');

/*--------------------------------*/

require 'resources/header.html';
echo 
'<div class="foreground">
    <h1>Objects</h1>
    
    <h2>Caching</h2>', 
    $cache->set('protagonist', $hero) ? 'Successfuly cached' : 'error',
    
    '<h2>Retrieving</h2>';

    $protagonist = $cache->get('protagonist');
    
    if ($hero == $protagonist) {
        echo 
        'Caching is sucessfull!!<br> 
        The hero\'s name is '.$protagonist->getName();
    } else {
        echo 'Caching failure';
    }
        
echo         
'</div>';

require 'resources/footer.html';