# Caching
This is a [PSR-16](https://www.php-fig.org/psr/psr-16/) implementation build around the php Filesystem.

## How to use it
Once instantiated, use like specified in the [PSR-16](https://www.php-fig.org/psr/psr-16/)

```php
use AdinanCenci\FileCache\Cache;
$cache = new Cache('my-cache-directory/');
```

### Caching
Inform an unique identifier for the data you desire to cache. Optionally you may inform its time to live, otherwise the cached data will never expire.
```php
$cache->set('somethingCostlyToAcquire', $value, 60 * 60 * 24);
```

### Caching multiple values at once
```php
$cache->setMultiple([
    'foo'           => $bar,
    'hello'         => $world,
    'myObject'      => $myObject
], 60 * 60 * 24);
```

### Retrieving
Use ::get to retrieve your data, if the data doesn't exist in cache or has expired then a fallback value will be returned, which defaults to null if not informed.
```php
$fallback = 'nothing here';
$cache->get('somethingCostlyToAcquire', $fallback);
```

### Retrieving multiple values at once
```php
$cache->getMultiple([
    'object1', 
    'value1',
    'anotherObject'
], $fallback);
```

## How to install
Use composer

```cmd
composer require adinan-cenci/file-cache
```

## License
MIT