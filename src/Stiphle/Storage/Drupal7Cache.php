<?php

namespace Stiphle\Storage;

class Drupal7Cache implements StorageInterface
{
    
    protected $lockWaitTimeout = 10000;
    protected $lockWaitInterval = 100;
    protected $ttl = 1000;
    protected $sleep = 100;

    public function __construct($cachename = 'throttle_cache', $lockWaitTimeout = 1000, $lockWaitInterval = 100)
    {
        $this->cachename = $cachename;
        $this->lockWaitTimeout = $lockWaitTimeout;
        $this->lockWaitInterval = $lockWaitInterval;
    }

    public function setLockWaitTimeout($milliseconds)
    {
        $this->lockWaitTimeout = $milliseconds;
        return;
    }

    public function setSleep($microseconds)
    {
        $this->sleep = $microseconds;
        return;
    }

    public function lock($key)
    {
        $start = microtime(true);

        cache_set($this->uniquekey($key), 0, 'cache', $this->ttl);

        $passed = (microtime(true) - $start) * 1000;
        if($passed > $this->lockWaitTimeout) {
            throw new LockWaitTimeoutException();
        }
        usleep($this->sleep);

        return;
    }

    public function unlock($key)
    {
        cache_clear_all($this->uniquekey($key), 'cache');
    }

    public function get($key)
    {
        $cache = cache_get($this->uniquekey($key), 'cache');
        return $cache->data;
    }

    public function set($key, $value)
    {
        return cache_set($this->uniquekey($key), $value, 'cache', $this->ttl);
    }

    private function uniquekey($key){
        return $this->cachename . ':' . $key;
    }

}
