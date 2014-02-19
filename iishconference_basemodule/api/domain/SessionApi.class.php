<?php

/**
 * Holds a session obtained from the API
 */
class SessionApi extends CRUDApiClient {
	protected $name;
	protected $abstr;
	protected $state_id;
	protected $papers_id;
	protected $networks_id;
	protected $addedBy_id;

	private $sessionState;
	private $networks;
	private $addedBy;
	private $sessionParticipants;

	public static function getListWithCriteria(array $properties, $printErrorMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $printErrorMessage);
	}

	/**
	 * Allows the creation of a session via an array with details
	 *
	 * @param array $session An array with session details
	 *
	 * @return SessionApi A session object
	 */
	public static function getSessionFromArray(array $session) {
		return self::createNewInstance(__CLASS__, $session);
	}

	/**
	 * Returns all the planned days of the listed sessions
	 *
	 * @param SessionApi[] $sessions The sessions in question
	 *
	 * @return DayApi[] The planned days
	 */
	public static function getAllPlannedDaysForSessions($sessions) {
		$daysPlanned = array();
		foreach ($sessions as $session) {
			$daysPlanned[] =
				CRUDApiMisc::getFirstWherePropertyEquals(new SessionRoomDateTimeApi(), 'session_id', $session->getId())
					->getDay();
		}
		sort($daysPlanned);

		return array_unique($daysPlanned);
	}

	/**
	 * Returns the abstract of this session
	 *
	 * @return string The abstract of this session
	 */
	public function getAbstr() {
		return $this->abstr;
	}

	/**
	 * Set the abstract for this paper
	 *
	 * @param string $abstr The abstract
	 */
	public function setAbstr($abstr) {
		$abstr = (($abstr !== null) && strlen(trim($abstr)) > 0) ? trim($abstr) : null;

		$this->abstr = $abstr;
		$this->toSave['abstr'] = $abstr;
	}

	/**
	 * Returns the name of this session
	 *
	 * @return string The name of this session
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Set the name for this paper
	 *
	 * @param string $name The name
	 */
	public function setName($name) {
		$name = (($name !== null) && strlen(trim($name)) > 0) ? trim($name) : null;

		$this->name = $name;
		$this->toSave['name'] = $name;
	}

	/**
	 * Returns a list with ids of all papers added to this session
	 *
	 * @return int[] The list with ids of all papers added to this session
	 */
	public function getPapersId() {
		return $this->papers_id;
	}

	/**
	 * Returns the id of this sessions state
	 *
	 * @return int The session state id
	 */
	public function getStateId() {
		return $this->state_id;
	}

	/**
	 * Returns this sessions state
	 *
	 * @return SessionStateApi The session state
	 */
	public function getState() {
		if (!$this->sessionState) {
			$sessionStates = CachedConferenceApi::getSessionStates();

			foreach ($sessionStates as $sessionState) {
				if ($sessionState->getId() == $this->state_id) {
					$this->sessionState = $sessionState;
					break;
				}
			}
		}

		return $this->sessionState;
	}

	/**
	 * Returns all the networks to which this session belongs
	 *
	 * @return NetworkApi[] All networks to which this session belongs
	 */
	public function getNetworks() {
		if (!$this->networks) {
			$this->networks = array();

			$networks = CachedConferenceApi::getNetworks();
			foreach ($networks as $network) {
				if (is_int(array_search($network->getId(), $this->getNetworksId()))) {
					$this->networks[] = $network;
				}
			}
		}

		return $this->networks;
	}

	/**
	 * Returns a list with ids of all networks to which this session belongs
	 *
	 * @return int[] The network ids
	 */
	public function getNetworksId() {
		return $this->networks_id;
	}

	/**
	 * Returns the user that created this session
	 *
	 * @return UserApi The user that created this session
	 */
	public function getAddedBy() {
		if (!$this->addedBy && is_int($this->getAddedById())) {
			$this->addedBy = CRUDApiMisc::getById(new UserApi(), 'id', $this->getAddedById());
		}

		return $this->addedBy;
	}

	/**
	 * Set the user who added this session
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
	 * The user id of the user who created this session
	 *
	 * @return int The user id of the user who created this session
	 */
	public function getAddedById() {
		return $this->addedBy_id;
	}

	/**
	 * Returns session participants information of this session
	 *
	 * @return SessionParticipantApi[] The session participant information
	 */
	public function getSessionParticipantInfo() {
		if (!$this->sessionParticipants) {
			$this->sessionParticipants =
				CRUDApiMisc::getAllWherePropertyEquals(new SessionParticipantApi(), 'session_id', $this->getId())
					->getResults();
		}

		return $this->sessionParticipants;
	}

	public function __toString() {
		return $this->getName();
	}
} 