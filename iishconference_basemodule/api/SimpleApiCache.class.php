<?php

/**
 * A very simple API caching system, simply caching responses in memory for the current request only
 */
class SimpleApiCache {
	private $requestCache = array();
	private static $instance;

	// Singleton, so no constructor or cloning allowed
	private function __construct() { }
	private function __clone() { }

	/**
	 * Returns an instance (singleton) of this class
	 *
	 * @return SimpleApiCache The singleton instance
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new SimpleApiCache();
		}

		return self::$instance;
	}

	/**
	 * Sets a item in the cache
	 *
	 * @param string     $apiName     The name of the API called
	 * @param array      $parameters  The parameters send with the API call
	 * @param string     $http_method The HTTP method used
	 * @param array|null $response    The response
	 */
	public function set($apiName, $parameters, $http_method, $response) {
		$this->requestCache[$apiName . ':' . $http_method . ':' . serialize($parameters)] = $response;
	}

	/**
	 * Returns an item from the cache, if it exists
	 *
	 * @param string $apiName     The name of the API called
	 * @param array  $parameters  The parameters send with the API call
	 * @param string $http_method The HTTP method used
	 *
	 * @return array|null The response found in the cache
	 */
	public function get($apiName, $parameters, $http_method) {
		if (array_key_exists($apiName . ':' . $http_method . ':' . serialize($parameters), $this->requestCache)) {
			return $this->requestCache[$apiName . ':' . $http_method . ':' . serialize($parameters)];
		}
		else {
			return null;
		}
	}
} 