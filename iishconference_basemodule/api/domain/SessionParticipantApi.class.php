<?php

/**
 * Holds a session participant obtained from the API
 */
class SessionParticipantApi extends CRUDApiClient {
	protected $user_id;
	protected $session_id;
	protected $type_id;
	protected $addedBy_id;
	protected $user;
	protected $session;
	protected $type;
	protected $addedBy;

	private $userInstance;
	private $sessionInstance;
	private $typeInstance;
	private $addedByInstance;

	public static function getListWithCriteria(array $properties, $printErrorMessage = true) {
		// Even though none of the ids can be null, querying it like this triggers a join
		// This join makes sure that instances with removed sessions, types or users are filtered out
		$prop = new ApiCriteriaBuilder();
		$properties = array_merge($prop
				->ne('session_id', null)
				->ne('user_id', null)
				->ne('type_id', null)
				->get(),
			$properties);

		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $printErrorMessage);
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
	 * For the given list with session participants, filter out all users that were found in that list
	 *
	 * @param SessionParticipantApi[] $sessionParticipants The list with session participants
	 *
	 * @return UserApi[] The user that were found
	 */
	public static function getAllUsers($sessionParticipants) {
		$users = array();
		foreach ($sessionParticipants as $sessionParticipant) {
			$users[] = $sessionParticipant->getUser();
		}

		return array_values(array_unique($users));
	}

	/**
	 * For the given list with session participants, filter out the types
	 * with which the given user was added to the given session
	 *
	 * @param SessionParticipantApi[] $sessionParticipants The list with session participants
	 * @param int                     $userId              The user id
	 * @param int                     $sessionId           The session id
	 *
	 * @return ParticipantTypeApi[] The participant types found
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
	 * Set the session to which the participant is added
	 *
	 * @param int|SessionApi $session The session (id)
	 */
	public function setSession($session) {
		if ($session instanceof SessionApi) {
			$session = $session->getId();
		}

		$this->session = null;
		$this->sessionInstance = null;
		$this->session_id = $session;
		$this->toSave['session.id'] = $session;
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
	 * @return ParticipantTypeApi The participant type
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
	 * Set the type with which the participant is added to the session
	 *
	 * @param int|ParticipantTypeApi $type The participant type (id)
	 */
	public function setType($type) {
		if ($type instanceof ParticipantTypeApi) {
			$type = $type->getId();
		}

		$this->type = null;
		$this->typeInstance = null;
		$this->type_id = $type;
		$this->toSave['type.id'] = $type;
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
	 * Set the user added to a session
	 *
	 * @param int|UserApi $user The user (id)
	 */
	public function setUser($user) {
		if ($user instanceof UserApi) {
			$user = $user->getId();
		}

		$this->user = null;
		$this->userInstance = null;
		$this->user_id = $user;
		$this->toSave['user.id'] = $user;
	}

	/**
	 * Returns the user that created this session participant
	 *
	 * @return UserApi The user that created this session participant
	 */
	public function getAddedBy() {
		if (!$this->addedByInstance) {
			$this->addedByInstance = $this->createNewInstance('UserApi', $this->addedBy);
		}

		return $this->addedByInstance;
	}

	/**
	 * Set the user who added this session participant
	 *
	 * @param int|UserApi $addedBy The user (id)
	 */
	public function setAddedBy($addedBy) {
		if ($addedBy instanceof UserApi) {
			$addedBy = $addedBy->getId();
		}

		$this->addedBy = null;
		$this->addedBy_id = $addedBy;
		$this->toSave['addedBy.id'] = $addedBy;
	}

	/**
	 * The user id of the user who created this session participant
	 *
	 * @return int The user id of the user who created this session participant
	 */
	public function getAddedById() {
		return $this->addedBy_id;
	}
} 