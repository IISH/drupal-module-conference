<?php

/**
 * API that allows a user to change his password
 */
class ChangePasswordApi {
	private $client;
	private static $apiName = 'changePassword';

	public function __construct() {
		$this->client = new ConferenceApiClient();
	}

	/**
	 * Change the password of a user
	 *
	 * @param int|UserApi $userId            The user (id) whos password has to be changed
	 * @param string      $newPassword       The new password
	 * @param string      $newPasswordRepeat The new password repeated
	 *
	 * @return bool Whether the password was successfully changed or not
	 */
	public function changePassword($userId, $newPassword, $newPasswordRepeat) {
		if ($userId instanceof UserApi) {
			$userId = $userId->getId();
		}

		$response = $this->client->get(self::$apiName, array(
			'userId'            => $userId,
			'newPassword'       => $newPassword,
			'newPasswordRepeat' => $newPasswordRepeat,
		));

		return ($response != null) ? $response['success'] : false;
	}

	/**
	 * Whether the given password would pass the validation phase
	 *
	 * @param string $password The password to check
	 *
	 * @return bool True if valid, false if not
	 */
	public static function isPasswordValid($password) {
		$ret = true;

		if ($ret) {
			$ret = ConferenceMisc::regexpValue($password, '/[a-z]/');
		}

		if ($ret) {
			$ret = ConferenceMisc::regexpValue($password, '/[A-Z]/');
		}

		if ($ret) {
			$ret = ConferenceMisc::regexpValue($password, '/[0-9]/');
		}

		return $ret;
	}
} 