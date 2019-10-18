<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*--------------------------------*/

require '../vendor/autoload.php';
use AdinanCenci\FileCache\Cache;

/*--------------------------------*/

try {
    $cache = new Cache(__DIR__.'/cache/');
} catch (\Exception $e) {
    echo 
    $e->getMessage();
    die();
}

/*--------------------------------*/

$name       = 'Odysseus';
$father     = 'Laërtes';
$wife       = 'Penelope';
$son        = 'Telemachus';
$epic       = 'The Odyssey';

/*--------------------------------*/

require 'resources/header.html';
echo 
'<div class="foreground">
    <h1>Saving</h1>
    
    <h2>Caching a single value</h2>
    <p>*', 
        $cache->set('name', $name, 60 * 60 * 5) ? 'name cached' : 'failed caching the name', 
    '</p>
    
    <h2>Caching multiple files</h2>
    <p>*', 
        $cache->setMultiple(array(
            'father'     => $father, 
            'wife'       => $wife, 
            'son'        => $son, 
            'epic'       => $epic
        ), 60 * 60 * 5) ? 'details cached' : 'failed caching the details', 
    '</p>
</div>';

/*--------------------------------*/

echo 
'<div class="foreground">
    <h1>Retrieving</h1>
    
    <p>', 
        $cache->get('name').' is ',
        vsprintf(
            'son of %s, husband of %s and father of %s. He is the protagonist of the epic %s', 
            $cache->getMultiple(array('father', 'wife', 'son', 'epic'))
        ), 
    '</p>
</div>';

/*--------------------------------*/

require 'resources/footer.html';