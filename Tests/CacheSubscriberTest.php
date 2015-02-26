<?php

namespace EmanueleMinotto\Guzzle\Tests;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use EmanueleMinotto\Guzzle\CacheSubscriber;
use GuzzleHttp\Client;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass \EmanueleMinotto\Guzzle\CacheSubscriber
 */
class CacheSubscriberTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var CacheSubscriber
     */
    protected $subscriber;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->client = new Client();
        $this->subscriber = new CacheSubscriber();

        $this->client->getEmitter()->attach($this->subscriber);
    }

    /**
     * @covers EmanueleMinotto\Guzzle\CacheSubscriber::setCache
     * @covers EmanueleMinotto\Guzzle\CacheSubscriber::getCache
     */
    public function testCacheAccessors()
    {
        $cache = new ChainCache([
            new ArrayCache(),
            new ArrayCache(),
        ]);

        $this->subscriber->setCache($cache);

        $getterValue = $this->subscriber->getCache();

        $this->assertInstanceOf('Doctrine\Common\Cache\Cache', $getterValue);
        $this->assertSame($cache, $getterValue);
    }

    /**
     * @covers EmanueleMinotto\Guzzle\CacheSubscriber::getEvents
     * @todo   Implement testGetEvents().
     */
    public function testGetEvents()
    {
        $events = $this->subscriber->getEvents();

        $this->assertNotEmpty($events);
        $this->assertInternalType('array', $events);
    }

    /**
     * @covers EmanueleMinotto\Guzzle\CacheSubscriber::onBefore
     * @covers EmanueleMinotto\Guzzle\CacheSubscriber::onComplete
     */
    public function testEvents()
    {
        $client = $this->client;

        $originalResponse = $client->get('http://httpbin.org/get');

        $client->getEmitter()->on('before', function () {
            $this->assertTrue(false);
        });

        $cachedResponse = $client->get('http://httpbin.org/get');

        $this->assertInstanceOf('GuzzleHttp\Message\ResponseInterface', $originalResponse);
        $this->assertInstanceOf('GuzzleHttp\Message\ResponseInterface', $cachedResponse);
        $this->assertSame($originalResponse, $cachedResponse);
    }
}
