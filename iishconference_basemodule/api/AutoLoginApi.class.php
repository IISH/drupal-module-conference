<?php

/**
 * API that allows a user to login automatically using a code
 */
class AutoLoginApi {
	private $client;
	private static $apiName = 'autoLogin';

	public function __construct() {
		$this->client = new ConferenceApiClient();
	}

	/**
	 * Allows a user to login with his email and auto login code
	 *
   * @param string $email         The email address of the user
	 * @param string $autoLoginCode The auto login code of the user
	 *
	 * @return int The status
	 */
	public function login($email, $autoLoginCode) {
		$response = $this->client->get(self::$apiName, array(
      'email' => trim($email),
			'code'  => trim($autoLoginCode),
		));

		return LoggedInUserDetails::setCurrentlyLoggedInWithResponse($response);
	}
} 