<?php

declare(strict_types=1);

/**
 * Class Minify_Cache_Memcache
 * @package Minify
 */

/**
 * Memcache-based cache class for Minify
 *
 * <code>
 * // fall back to disk caching if memcache can't connect
 * $memcache = new Memcache;
 * if ($memcache->connect('localhost', 11211)) {
 *     Minify::setCache(new Minify_Cache_Memcache($memcache));
 * } else {
 *     Minify::setCache();
 * }
 * </code>
 **/
class Minify_Cache_Memcache implements Minify_CacheInterface
{
    private Memcache $_mc;

    private int $_exp;

    // cache of most recently fetched id
    private $_lm = null;

    private $_data = null;

    private $_id = null;

    /**
     * Create a Minify_Cache_Memcache object, to be passed to
     * Minify::setCache().
     *
     * @param Memcache $memcache already-connected instance
     *
     * @param int      $expire seconds until expiration (default = 0
     * meaning the item will not get an expiration date)
     */
    public function __construct($memcache, $expire = 0)
    {
        $this->_mc  = $memcache;
        $this->_exp = $expire;
    }

    /**
     * Write data to cache.
     *
     * @param string $id cache id
     * @param string $data
     * @return bool success
     */
    public function store($id, $data)
    {
        return $this->_mc->set($id, "{$_SERVER['REQUEST_TIME']}|{$data}", 0, $this->_exp);
    }

    /**
     * Get the size of a cache entry
     *
     * @param string $id cache id
     * @return int size in bytes
     */
    public function getSize($id)
    {
        if (!$this->_fetch($id)) {
            return false;
        }

        return mb_strlen($this->_data, '8bit');
    }

    /**
     * Does a valid cache entry exist?
     *
     * @param string $id cache id
     * @param int    $srcMtime mtime of the original source file(s)
     * @return bool exists
     */
    public function isValid($id, $srcMtime)
    {
        return ($this->_fetch($id) && ($this->_lm >= $srcMtime));
    }

    /**
     * Send the cached content to output
     *
     * @param string $id cache id
     */
    public function display($id)
    {
        echo $this->_fetch($id) ? $this->_data : '';
    }

    /**
     * Fetch the cached content
     *
     * @param string $id cache id
     * @return string
     */
    public function fetch($id)
    {
        return $this->_fetch($id) ? $this->_data : '';
    }

    /**
     * Fetch data and timestamp from memcache, store in instance
     *
     * @param string $id
     * @return bool success
     */
    private function _fetch($id)
    {
        if ($this->_id === $id) {
            return true;
        }

        $ret = $this->_mc->get($id);
        if (false === $ret) {
            $this->_id = null;

            return false;
        }

        [$this->_lm, $this->_data] = explode('|', $ret, 2);
        $this->_id = $id;

        return true;
    }
}
