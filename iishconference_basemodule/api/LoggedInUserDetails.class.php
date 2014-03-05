<?php

/**
 * Holds information about the currently logged in user
 */
class LoggedInUserDetails {
	const USER_STATUS_DOES_NOT_EXISTS = 0;
	const USER_STATUS_EXISTS = 1;
	const USER_STATUS_DISABLED = 2;
	const USER_STATUS_DELETED = 3;

	/**
	 * Is the user currently logged in?
	 *
	 * @return bool Whether the user is currently logged in
	 */
	public static function isLoggedIn() {
		return is_int(self::getId());
	}

	/**
	 * Returns the user id of the currently logged in user, if logged in
	 *
	 * @return int|null The user id
	 */
	public static function getId() {
		$id = null;
		if (isset($_SESSION["conference"]["user_id"]) && is_int($_SESSION["conference"]["user_id"]) &&
		    ($_SESSION["conference"]["user_id"] > 0)
		) {

			$id = $_SESSION["conference"]["user_id"];
		}

		return $id;
	}

	/**
	 * Returns the user details of the currently logged in user, if logged in
	 *
	 * @return UserApi|null The user details
	 */
	public static function getUser() {
		$user = null;
		if (isset($_SESSION['conference']['user'])) {
			$user = unserialize($_SESSION['conference']['user']);
		}
		else if (is_int(LoggedInUserDetails::getId())) {
			$user = CRUDApiMisc::getById(new UserApi(), LoggedInUserDetails::getId());
			$_SESSION['conference']['user'] = serialize($user);
		}

		return $user;
	}

	/**
	 * Is the user currently logged in a participant?
	 *
	 * @return bool Whether the logged in user is a participant
	 */
	public static function isAParticipant() {
		return ((self::getParticipant() !== null) &&
		        (self::getParticipant()->getStateId() !== ParticipantStateApi::DID_NOT_FINISH_REGISTRATION));
	}

	/**
	 * Returns the participant details of the currently logged in user, if logged in and a participant
	 *
	 * @return ParticipantDateApi|null The participant details
	 */
	public static function getParticipant() {
		$participant = null;
		if (isset($_SESSION['conference']['participant'])) {
			$participant = unserialize($_SESSION['conference']['participant']);
		}
		else if (is_int(LoggedInUserDetails::getId())) {
			$participant = CRUDApiMisc::getFirstWherePropertyEquals(new ParticipantDateApi(), 'user_id', LoggedInUserDetails::getId());
			$_SESSION['conference']['participant'] = serialize($participant);
		}

		return $participant;
	}

	/**
	 * Does the logged in user have full rights?
	 *
	 * @return bool Whether the logged in user has full rights
	 */
	public static function hasFullRights() {
		$hasFullRights = false;
		if (isset($_SESSION['conference']['hasFullRights'])) {
			$hasFullRights = $_SESSION['conference']['hasFullRights'];
		}

		return $hasFullRights;
	}

	/**
	 * Is the logged in user a network chair?
	 *
	 * @return bool Whether the logged in user is a network chair
	 */
	public static function isNetworkChair() {
		$isNetworkChair = false;
		if (isset($_SESSION['conference']['isNetworkChair'])) {
			$isNetworkChair = $_SESSION['conference']['isNetworkChair'];
		}

		return $isNetworkChair;
	}

	/**
	 * Is the logged in user a session chair?
	 *
	 * @return bool Whether the logged in user is a session chair
	 */
	public static function isChair() {
		$isChair = false;
		if (isset($_SESSION['conference']['isChair'])) {
			$isChair = $_SESSION['conference']['isChair'];
		}

		return $isChair;
	}

	/**
	 * Is the logged in user a session organiser?
	 *
	 * @return bool Whether the logged in user is a session organiser
	 */
	public static function isOrganiser() {
		$isOrganiser = false;
		if (isset($_SESSION['conference']['isOrganiser'])) {
			$isOrganiser = $_SESSION['conference']['isOrganiser'];
		}

		return $isOrganiser;
	}

	/**
	 * Is the logged in user a crew member?
	 *
	 * @return bool Whether the logged in user is a crew member
	 */
	public static function isCrew() {
		$isCrew = false;
		if (isset($_SESSION['conference']['isCrew'])) {
			$isCrew = $_SESSION['conference']['isCrew'];
		}

		return $isCrew;
	}

	/**
	 * !!! FOR TESTING PURPOSES ONLY !!!
	 * Set the currently logged in user without logging in
	 *
	 * @param int  $userId         The id of the user in question
	 * @param bool $hasFullRights  Whether the user will have full rights
	 * @param bool $isNetworkChair Whether the user will be a network chair
	 * @param bool $isChair        Whether the user will be a chair
	 * @param bool $isOrganiser    Whether the user will be an organiser
	 * @param bool $isCrew         Whether the user will be a crew member
	 *
	 * @return int The user status of the currently logged in user
	 */
	public static function setCurrentlyLoggedIn($userId, $hasFullRights = false, $isNetworkChair = false,
	                                            $isChair = false, $isOrganiser = false, $isCrew = false) {
		$user = CRUDApiMisc::getById(new UserApi(), $userId);
		$participant = CRUDApiMisc::getFirstWherePropertyEquals(new ParticipantDateApi(), 'user_id', $userId);

		return self::setCurrentlyLoggedInWithResponse(array(
			'status'         => self::USER_STATUS_EXISTS,
			'hasFullRights'  => $hasFullRights,
			'isNetworkChair' => $isNetworkChair,
			'isChair'        => $isChair,
			'isOrganiser'    => $isOrganiser,
			'isCrew'         => $isCrew,
			'user'           => $user,
			'participant'    => $participant,
		));
	}

	/**
	 * Set the currently logged in user for use with the LoginApi or the UserInfoApi
	 *
	 * @param array $response The response from either api calls
	 *
	 * @return int The user status of the currently logged in user
	 */
	public static function setCurrentlyLoggedInWithResponse(array $response) {
		$userStatus = self::USER_STATUS_DOES_NOT_EXISTS;
		$_SESSION['conference']['user_email'] = null;
		$_SESSION['conference']['user_id'] = null;

		if ($response !== null) {
			$userStatus = $response['status'];
			$_SESSION['conference']['hasFullRights'] = $response['hasFullRights'];
			$_SESSION['conference']['isNetworkChair'] = $response['isNetworkChair'];
			$_SESSION['conference']['isChair'] = $response['isChair'];
			$_SESSION['conference']['isOrganiser'] = $response['isOrganiser'];
			$_SESSION['conference']['isCrew'] = $response['isCrew'];

			$user = null;
			if ($response['user'] instanceof UserApi) {
				$user = $response['user'];
			}
			else if (is_array($response['user'])) {
				$user = UserApi::getUserFromArray($response['user']);
			}

			$participant = null;
			if ($response['participant'] instanceof ParticipantDateApi) {
				$participant = $response['participant'];
			}
			else if (is_array($response['participant'])) {
				$participant = ParticipantDateApi::getParticipantDateFromArray($response['participant']);
			}

			$_SESSION['conference']['user_email'] = $user->getEmail();
			$_SESSION['conference']['user_id'] = $user->getId();

			$_SESSION['conference']['user'] = serialize($user);
			$_SESSION['conference']['participant'] = serialize($participant);
		}

		return $userStatus;
	}
}