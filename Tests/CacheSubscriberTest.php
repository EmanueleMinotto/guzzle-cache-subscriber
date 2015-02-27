<?php

namespace EmanueleMinotto\Guzzle\Tests;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;
use EmanueleMinotto\Guzzle\CacheSubscriber;
use GuzzleHttp\Client;
use PHPUnit_Framework_TestCase;

/**
 * Subscriber test class.
 *
 * @author Emanuele Minotto <minottoemanuele@gmail.com>
 *
 * @coversDefaultClass \EmanueleMinotto\Guzzle\CacheSubscriber
 */
class CacheSubscriberTest extends PHPUnit_Framework_TestCase
{
    /**
     * Attributes initialization before every test.
     *
     * @return void
     */
    protected function setUp()
    {
        $client =& $this->client;

        $client = new Client();
        $subscriber = new CacheSubscriber();

        $emitter = $client->getEmitter();
        $emitter->attach($subscriber);
    }

    /**
     * Test cache accessors (get, set).
     *
     * @covers ::setCache
     * @covers ::getCache
     *
     * @return void
     */
    public function testCacheAccessors()
    {
        $cache = new ChainCache([
            new ArrayCache(),
            new ArrayCache(),
        ]);

        $subscriber = new CacheSubscriber();
        $subscriber->setCache($cache);

        $getterValue = $subscriber->getCache();

        $this->assertInstanceOf('Doctrine\Common\Cache\Cache', $getterValue);
        $this->assertSame($cache, $getterValue);
    }

    /**
     * Test Guzzle subscriber events.
     *
     * @covers ::getEvents
     *
     * @return void
     */
    public function testGetEvents()
    {
        $subscriber = new CacheSubscriber();
        $events = $subscriber->getEvents();

        $this->assertNotEmpty($events);
        $this->assertInternalType('array', $events);
    }

    /**
     * Subscriber functional test.
     *
     * @covers ::onBefore
     * @covers ::onComplete
     *
     * @return void
     */
    public function testEvents()
    {
        $client = $this->client;

        $originalResponse = $client->get('http://httpbin.org/get');

        $emitter = $client->getEmitter();
        $emitter->on('before', function () {
            $this->assertTrue(false);
        });

        $cachedResponse = $client->get('http://httpbin.org/get');

        $this->assertInstanceOf('GuzzleHttp\Message\ResponseInterface', $originalResponse);
        $this->assertInstanceOf('GuzzleHttp\Message\ResponseInterface', $cachedResponse);
        $this->assertSame($originalResponse, $cachedResponse);
    }
}
