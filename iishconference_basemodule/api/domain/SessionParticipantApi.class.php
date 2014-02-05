<?php

/**
 * Holds a session participant obtained from the API
 */
class SessionParticipantApi extends CRUDApiClient {
	protected $user_id;
	protected $session_id;
	protected $type_id;
	protected $user;
	protected $session;
	protected $type;

	private $userInstance;
	private $sessionInstance;
	private $typeInstance;

	public static function getListWithCriteria(array $properties, $showDrupalMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $showDrupalMessage);
	}

	/**
	 * The session to which the participant is added
	 *
	 * @return SessionApi The session
	 */
	public function getSession() {
		if (!$this->sessionInstance) {
			$this->sessionInstance = $this->createNewInstance('SessionApi', $this->session);
		}

		return $this->sessionInstance;
	}

	/**
	 * The id of the session to which the participant is added
	 *
	 * @return int The session id
	 */
	public function getSessionId() {
		return $this->session_id;
	}

	/**
	 * The type of the participant with which he/she is added to the session
	 *
	 * @return ParticipantType The participant type
	 */
	public function getType() {
		if (!$this->typeInstance) {
			$this->typeInstance = $this->createNewInstance('ParticipantTypeApi', $this->type);
		}

		return $this->typeInstance;
	}

	/**
	 * The id of the type of the participant with which he/she is added to the session
	 *
	 * @return int The participant type
	 */
	public function getTypeId() {
		return $this->type_id;
	}

	/**
	 * The user that is added to the session
	 *
	 * @return UserApi The user added to the session
	 */
	public function getUser() {
		if (!$this->userInstance) {
			$this->userInstance = $this->createNewInstance('UserApi', $this->user);
		}

		return $this->userInstance;
	}

	/**
	 * The id of the user that is added to the session
	 *
	 * @return int The user id
	 */
	public function getUserId() {
		return $this->user_id;
	}

	/**
	 * For the given list with session participants, filter out all sessions that were found in that list
	 *
	 * @param SessionParticipantApi[] $sessionParticipants The list with session participants
	 *
	 * @return SessionApi[] The sessions that were found
	 */
	public static function getAllSessions($sessionParticipants) {
		$sessions = array();
		foreach ($sessionParticipants as $sessionParticipant) {
			$sessions[] = $sessionParticipant->getSession();
		}

		return array_values(array_unique($sessions));
	}

	/**
	 * For the given list with session participants, filter out the types
	 * with which the given user was added to the given session
	 *
	 * @param SessionParticipantApi[] $sessionParticipants The list with session participants
	 * @param int                     $userId              The user id
	 * @param int                     $sessionId           The session id
	 *
	 * @return ParticipantType[] The participant types found
	 */
	public static function getAllTypesOfUserForSession($sessionParticipants, $userId, $sessionId) {
		$types = array();
		foreach ($sessionParticipants as $sessionParticipant) {
			if (($sessionParticipant->getUserId() == $userId) && ($sessionParticipant->getSessionId() == $sessionId)) {
				$types[] = $sessionParticipant->getType();
			}
		}

		return array_values(array_unique($types));
	}
} 