<?php

require_once('Client.php');
require_once('SimpleApiCache.class.php');
require_once('GrantType/IGrantType.php');
require_once('GrantType/ClientCredentials.php');

/**
 * Client that allows communication with the Conference Management System API
 */
class ConferenceApiClient {
	private $oAuthClient;
	private $requestCache;

	private static $yearCode = null;

	public function __construct() {
		$clientId = variable_get('conference_client_id');
		$clientSecret = variable_get('conference_client_secret');

		$this->oAuthClient = new Client($clientId, $clientSecret);
		$this->requestCache = SimpleApiCache::getInstance();

		$this->oAuthClient->setAccessTokenType(Client::ACCESS_TOKEN_BEARER);
		if ($cachedToken = cache_get('conference_access_token_' . $clientId)) {
			$this->oAuthClient->setAccessToken($cachedToken->data);
		}
        else {
            $this->requestNewToken();
        }
	}

	/**
	 * Returns the year code to use when calling the Conference Management System API
	 *
	 * @return string The year code
	 */
	public static function getYearCode() {
		if (self::$yearCode === null) {
			return variable_get('conference_date_code');
		}

		return self::$yearCode;
	}

	/**
	 * Allows to override the configured year code
	 *
	 * @param string $yearCode Override the configured year code with a new year code
	 */
	public static function setYearCode($yearCode) {
		self::$yearCode = $yearCode;
	}

	/**
	 * Make a GET call to the Conference Management System API
	 *
	 * @param string $apiName           The name of the API to call
	 * @param array  $parameters        The parameters to send with the call
	 * @param bool   $printErrorMessage Whether to print an error message in case of failure
	 *
	 * @return mixed The response message if found, else null is returned
	 */
	public function get($apiName, array $parameters, $printErrorMessage = true) {
		return $this->call($apiName, $parameters, $printErrorMessage, Client::HTTP_METHOD_GET);
	}

	/**
	 * Make a POST call to the Conference Management System API
	 *
	 * @param string $apiName           The name of the API to call
	 * @param array  $parameters        The parameters to send with the call
	 * @param bool   $printErrorMessage Whether to print an error message in case of failure
	 *
	 * @return mixed The response message if found, else null is returned
	 */
	public function post($apiName, array $parameters, $printErrorMessage = true) {
		return $this->call($apiName, $parameters, $printErrorMessage, Client::HTTP_METHOD_POST);
	}

	/**
	 * Make a PUT call to the Conference Management System API
	 *
	 * @param string $apiName           The name of the API to call
	 * @param array  $parameters        The parameters to send with the call
	 * @param bool   $printErrorMessage Whether to print an error message in case of failure
	 *
	 * @return mixed The response message if found, else null is returned
	 */
	public function put($apiName, array $parameters, $printErrorMessage = true) {
		// Make sure we send it with content-type 'application/x-www-form-urlencoded'
		$parameters = http_build_query($parameters, null, '&');

		return $this->call($apiName, $parameters, $printErrorMessage, Client::HTTP_METHOD_PUT);
	}

	/**
	 * Make a DELETE call to the Conference Management System API
	 *
	 * @param string $apiName           The name of the API to call
	 * @param array  $parameters        The parameters to send with the call
	 * @param bool   $printErrorMessage Whether to print an error message in case of failure
	 *
	 * @return mixed The response message if found, else null is returned
	 */
	public function delete($apiName, array $parameters, $printErrorMessage = true) {
		return $this->call($apiName, $parameters, $printErrorMessage, Client::HTTP_METHOD_DELETE);
	}

	/**
	 * Make a call to the Conference Management System API
	 *
	 * @param string       $apiName           The name of the API to call
	 * @param array|string $parameters        The parameters to send with the call
	 * @param bool         $printErrorMessage Whether to print an error message in case of failure
	 * @param string       $http_method       The HTTP method to use
	 *
	 * @return mixed The response message if found, else null is returned
	 *
	 * @throws Exception May throw an exception in case of failure if $printErrorMessage == false
	 */
	private function call($apiName, $parameters, $printErrorMessage = true, $http_method = Client::HTTP_METHOD_GET) {
		// See if this request was made before
		$result = $this->requestCache->get($apiName, self::getYearCode(), $parameters, $http_method);

		if ($result === null) {
			$url = self::getUrl() . $apiName;

			try {
				$response = $this->oAuthClient->fetch($url, $parameters, $http_method);

				// Authorization error, request a new token and try again
				if (in_array($response['code'], array(302, 401))) {
					$this->requestNewToken();
					$response = $this->oAuthClient->fetch($url, $parameters, $http_method);
				}

				if ($response['code'] === 200) {
					$result = $response['result'];
					$this->requestCache->set($apiName, self::getYearCode(), $parameters, $http_method, $result);
				}
				else {
					throw new Exception('Failed to communicate with the conference API: returned ' . $response['code']);
				}
			}
			catch (Exception $exception) {
				// Print error message by interrupting the current page building and delivering an empty page with an error message
				if ($printErrorMessage) {
					drupal_set_message(iish_t('There are currently problems obtaining the necessary data. ' .
						'Please try again later. We are sorry for the inconvenience.'), 'error');
					drupal_deliver_html_page('');
					drupal_exit();
				}
				else {
					throw $exception;
				}
			}
		}

		return $result;
	}

	/**
	 * Request a new token to access the API
	 */
	private function requestNewToken() {
		$response = $this->oAuthClient->getAccessToken(self::getTokenUrl(), ClientCredentials::GRANT_TYPE, array());

		if ($response['code'] === 200) {
			$token = $response['result']['access_token'];
			$this->oAuthClient->setAccessToken($token);
			cache_set('conference_access_token_' . $this->oAuthClient->getClientId(), $token, 'cache',
				time() + 60 * 60 * 12);
		}
	}

	/**
	 * Returns the url (without the api name) for a API call to the Conference Management System API
	 *
	 * @return string The url for a API call to the Conference Management System API
	 */
	private static function getUrl() {
		return variable_get('conference_base_url') . variable_get('conference_event_code') .
		'/' . self::getYearCode() . '/api/';
	}

	/**
	 *  Returns the url (without the api name) for a token request to the Conference Management System API
	 *
	 * @return string Returns the url for a token request to the Conference Management System API
	 */
	private static function getTokenUrl() {
		return variable_get('conference_base_url') . 'oauth/token';
	}
}