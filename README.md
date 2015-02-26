Guzzle Cache Subscriber
=======================

[![Build Status](https://img.shields.io/travis/EmanueleMinotto/guzzle-cache-subscriber.svg?style=flat)](https://travis-ci.org/EmanueleMinotto/guzzle-cache-subscriber)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/xxx.svg?style=flat)](https://insight.sensiolabs.com/projects/xxx)
[![Coverage Status](https://img.shields.io/coveralls/EmanueleMinotto/guzzle-cache-subscriber.svg?style=flat)](https://coveralls.io/r/EmanueleMinotto/guzzle-cache-subscriber)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/EmanueleMinotto/guzzle-cache-subscriber.svg?style=flat)](https://scrutinizer-ci.com/g/EmanueleMinotto/guzzle-cache-subscriber/)
[![Total Downloads](https://img.shields.io/packagist/dt/emanueleminotto/guzzle-cache-subscriber.svg?style=flat)](https://packagist.org/packages/emanueleminotto/guzzle-cache-subscriber)

Server side caching (based on [doctrine/cache](http://doctrine-orm.readthedocs.org/en/latest/reference/caching.html)) for [Guzzle 4/5](http://docs.guzzlephp.org/en/latest/).

Server side caching helps to improve performances intercepting cached requests before these are sent to the URL (save network usage), and caching (when possible and permitted) responses.

API: [emanueleminotto.github.io/guzzle-cache-subscriber](http://emanueleminotto.github.io/guzzle-cache-subscriber/)

Install
-------

Install the CacheSubscriber adding `emanueleminotto/guzzle-cache-subscriber` to your composer.json or from CLI:

```
$ composer require emanueleminotto/guzzle-cache-subscriber
```

Usage
-----

```php
use GuzzleHttp\Client;
use EmanueleMinotto\Guzzle\CacheSubscriber;

$client = new Client();

$subscriber = new CacheSubscriber(/* Doctrine cache instance, optional */);
// there are the getCache and setCache methods to
// change the storage system

$client->getEmitter()->attach($subscriber);

// request sent
$client->get('http://httpbin.org');

// request intercepted
$client->get('http://httpbin.org');
```
