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
	private $types;
	private $networks;
	private $addedBy;

	public static function getListWithCriteria(array $properties, $showDrupalMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $showDrupalMessage);
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
	 * The user id of the user who created this session
	 *
	 * @return int The user id of the user who created this session
	 */
	public function getAddedById() {
		return $this->addedBy_id;
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
	 * Returns a list with ids of all networks to which this session belongs
	 *
	 * @return int[] The network ids
	 */
	public function getNetworksId() {
		return $this->networks_id;
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
	 * Returns the user that created this session
	 *
	 * @return UserApi The user that created this session
	 */
	public function getAddedBy() {
		if (!$this->addedBy && is_int($this->getAddedById())) {
			$props = new ApiCriteriaBuilder();
			$this->addedBy = UserApi::getListWithCriteria(
				$props
					->eq('id', $this->getAddedById())
					->get()
			)->getFirstResult();
		}

		return $this->addedBy;
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
} 