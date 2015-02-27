<?php

namespace EmanueleMinotto\Guzzle;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Subscriber\Cache\Utils;

/**
 * Server side caching subscriber (for Guzzle 4 and 5 only).
 *
 * @author Emanuele Minotto <minottoemanuele@gmail.com>
 */
class CacheSubscriber implements SubscriberInterface
{
    /**
     * Constructor with optional cache strategy.
     *
     * @param Cache|null $cache Doctrine cache implementation.
     */
    public function __construct(Cache $cache = null)
    {
        $this->setCache($cache ?: new ArrayCache());
    }

    /**
     * Cache setter.
     *
     * @param Cache $cache New doctrine cache implementation.
     *
     * @return self
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Cache getter.
     *
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Subscriber events.
     *
     * @link http://docs.guzzlephp.org/en/latest/events.html#working-with-request-events
     *
     * @return array
     */
    public function getEvents()
    {
        return [
            'before' => ['onBefore', 'first'],
            'complete' => ['onComplete', 'last'],
        ];
    }

    /**
     * Check if the request is cached and intercept it.
     *
     * @param BeforeEvent $event Guzzle 4/5 event.
     *
     * @return void
     */
    public function onBefore(BeforeEvent $event)
    {
        $request = $event->getRequest();
        $this->request = $request;

        if (!Utils::canCacheRequest($request)) {
            return;
        }

        $key = $request->getMethod().' '.$request->getUrl();

        if (!$this->cache->contains($key)) {
            return;
        }

        $response = $this->cache->fetch($key);

        $event->intercept($response);
    }

    /**
     * When the request is completed, it's cached.
     *
     * @param CompleteEvent $event Guzzle 4/5 event.
     *
     * @return void
     */
    public function onComplete(CompleteEvent $event)
    {
        $response = $event->getResponse();

        if (!Utils::canCacheResponse($response)) {
            return;
        }

        $request = $this->request;

        $keys = [
            $request->getMethod().' '.$request->getUrl(),
            $request->getMethod().' '.$response->getEffectiveUrl(),
        ];

        $ttl = $this->getTtl($response);

        foreach ($keys as $key) {
            $this->cache->save($key, $response, $ttl);
        }
    }

    /**
     * Return the TTL to use when caching a Response (extracted from guzzle/cache-subscriber).
     *
     * @param ResponseInterface $response Guzzle 4/5 response message.
     *
     * @link https://github.com/guzzle/cache-subscriber/blob/master/src/CacheStorage.php
     *
     * @return integer
     */
    private function getTtl(ResponseInterface $response)
    {
        $ttl = 0;

        if ($response->getHeader('Cache-Control')) {
            return $ttl;
        }

        $maxAge = Utils::getDirective($response, 'max-age');
        if (is_numeric($maxAge)) {
            $ttl += $maxAge;
        }

        // According to RFC5861 stale headers are *in addition* to any
        // max-age values.
        $stale = Utils::getDirective($response, 'stale-if-error');
        if (is_numeric($stale)) {
            $ttl += $stale;
        }

        return $ttl;
    }
}
