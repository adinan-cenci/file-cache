
# Caching
This is a PSR-16 implementation build around the php Filesystem.

## How to use it

Once instantiated, use like specified in the PSR-16

```php
use AdinanCenci\FileCache\Cache;
$cache = new Cache('my-directory/');
```

### Caching

```php
$cache->set('something', $someObject, 60 * 60 * 24);

// or

$cache->setMultiple([
    'object1'       => $object1, 
    'value1'        => $value1, 
    'anotherObject' => $someObject
], 60 * 60 * 24);
```

### Retrieving

```php
$cache->get('something', $fallBackValue);

// or

$cache->getMultiple([
    'object1'       => $object1, 
    'value1'        => $value1, 
    'anotherObject' => $someObject
], $fallBackValue);
```

## How to install

Use composer

```json
"repositories" : [
    {
        "type": "vcs",
        "url": "https://github.com/adinan-cenci/file-cache"
    }
], 
"require": {
    "adinan-cenci/file-cache" : "1.0.0"
}
```

