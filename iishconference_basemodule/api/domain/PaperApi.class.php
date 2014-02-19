<?php

/**
 * Holds a paper obtained from the API
 */
class PaperApi extends CRUDApiClient {
	protected $user_id;
	protected $state_id;
	protected $session_id;
	protected $title;
	protected $coAuthors;
	protected $abstr;
	protected $networkProposal_id;
	protected $sessionProposal;
	protected $proposalDescription;
	protected $fileName;
	protected $contentType;
	protected $fileSize;
	protected $equipmentComment;
	protected $equipment_id;

	private $paperState;
	private $equipment;
	private $user;

	public static function getListWithCriteria(array $properties, $printErrorMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $printErrorMessage);
	}

	/**
	 * Allows the creation of a papaer via an array with details
	 *
	 * @param array $paper An array with papaer details
	 *
	 * @return PaperApi A papaer object
	 */
	public static function getPaperFromArray(array $paper) {
		return self::createNewInstance(__CLASS__, $paper);
	}

	/**
	 * For all given papers, find those planned in the session with the given session id
	 *
	 * @param PaperApi|PaperApi[] $papers    The papers to search through
	 * @param int                 $sessionId The id of the session in question
	 *
	 * @return PaperApi[] All papers planned in the session with the given session id
	 */
	public static function getPapersWithSession($papers, $sessionId) {
		$papersWithSession = array();

		if ($papers instanceof PaperApi) {
			if ($papers->getSessionId() == $sessionId) {
				$papersWithSession[] = $papers;
			}
		}
		else if (is_array($papers)) {
			foreach ($papers as $paper) {
				if ($paper->getSessionId() == $sessionId) {
					$papersWithSession[] = $paper;
				}
			}
		}

		return $papersWithSession;
	}

	/**
	 * For all given papers, find those that are not yet planned in a session
	 *
	 * @param PaperApi|PaperApi[] $papers The papers to search through
	 *
	 * @return PaperApi[] All papers planned in the session not planned in a session yet
	 */
	public static function getPapersWithoutSession($papers) {
		$papersWithoutSession = array();

		if ($papers instanceof PaperApi) {
			if ($papers->getSessionId() == null) {
				$papersWithoutSession[] = $papers;
			}
		}
		else if (is_array($papers)) {
			foreach ($papers as $paper) {
				if ($paper->getSessionId() == null) {
					$papersWithoutSession[] = $paper;
				}
			}
		}

		return $papersWithoutSession;
	}

	/**
	 * Returns the id of the session this paper may be planned in
	 *
	 * @return int|null The session id
	 */
	public function getSessionId() {
		return $this->session_id;
	}

	/**
	 * Set the state of this paper
	 *
	 * @param int|PaperStateApi $state The paper state (id)
	 */
	public function setStateId($state) {
		if ($state instanceof PaperStateApi) {
			$state = $state->getId();
		}

		$this->paperState = null;
		$this->state_id = $state;
		$this->toSave['state.id'] = $state;
	}

	/**
	 * Set the network proposal for this paper
	 *
	 * @param int|NetworkApi $networkProposal The network (id)
	 */
	public function setNetworkProposal($networkProposal) {
		if ($networkProposal instanceof NetworkApi) {
			$networkProposal = $networkProposal->getId();
		}

		$this->networkProposal_id = $networkProposal;
		$this->toSave['networkProposal.id'] = $networkProposal;
	}

	/**
	 * Returns the abstract of this paper
	 *
	 * @return string The abstract of this paper
	 */
	public function getAbstr() {
		return $this->abstr;
	}

	/**
	 * Set the abstract of this paper
	 *
	 * @param string|null $abstr The abstract of this paper
	 */
	public function setAbstr($abstr) {
		$abstr = (($abstr !== null) && strlen(trim($abstr)) > 0) ? trim($abstr) : null;

		$this->abstr = $abstr;
		$this->toSave['abstr'] = $abstr;
	}

	/**
	 * Returns the co-authors of this paper as a single string
	 *
	 * @return string|null The co-authors of this paper as a single string
	 */
	public function getCoAuthors() {
		return $this->coAuthors;
	}

	/**
	 * Set the co authors of this paper
	 *
	 * @param string|null $coAuthors The co authors
	 */
	public function setCoAuthors($coAuthors) {
		$coAuthors = (($coAuthors !== null) && strlen(trim($coAuthors)) > 0) ? trim($coAuthors) : null;

		$this->coAuthors = $coAuthors;
		$this->toSave['coAuthors'] = $coAuthors;
	}

	/**
	 * Returns any comments made by the author of the paper, regarding the necessary equipment
	 *
	 * @return string|null Any equipment comments
	 */
	public function getEquipmentComment() {
		return $this->equipmentComment;
	}

	/**
	 * Set the equipment comment for this paper
	 *
	 * @param string|null $equipmentComment The equipment comment
	 */
	public function setEquipmentComment($equipmentComment) {
		$equipmentComment =
			(($equipmentComment !== null) && strlen(trim($equipmentComment)) > 0) ? trim($equipmentComment) : null;

		$this->equipmentComment = $equipmentComment;
		$this->toSave['equipmentComment'] = $equipmentComment;
	}

	/**
	 * Returns the session proposal description made by the author of this paper for this paper
	 *
	 * @return string|null The session proposal description
	 */
	public function getProposalDescription() {
		return $this->proposalDescription;
	}

	/**
	 * Returns the session proposal name made by the author of this paper for this paper
	 *
	 * @return string|null The session proposal name
	 */
	public function getSessionProposal() {
		return $this->sessionProposal;
	}

	/**
	 * Set the session proposal for this paper
	 *
	 * @param string|null $sessionProposal The session proposal
	 */
	public function setSessionProposal($sessionProposal) {
		$sessionProposal =
			(($sessionProposal !== null) && strlen(trim($sessionProposal)) > 0) ? trim($sessionProposal) : null;

		$this->sessionProposal = $sessionProposal;
		$this->toSave['sessionProposal'] = $sessionProposal;
	}

	/**
	 * Returns the id of the author of this paper
	 *
	 * @return int The id of the author
	 */
	public function getUserId() {
		return $this->user_id;
	}

	/**
	 * Returns the content type of the uploaded file for this paper
	 *
	 * @return string|null The content type
	 */
	public function getContentType() {
		return $this->contentType;
	}

	/**
	 * Returns the name of the uploaded file for this paper
	 *
	 * @return string|null The file name
	 */
	public function getFileName() {
		return $this->fileName;
	}

	/**
	 * Returns the size of the uploaded file for this paper
	 *
	 * @return int|null The file size
	 */
	public function getFileSize() {
		return $this->fileSize;
	}

	/**
	 * Returns the author of this paper
	 *
	 * @return UserApi|null The author of this paper
	 */
	public function getUser() {
		if (!$this->user) {
			$this->user = CRUDApiMisc::getById(new UserApi(), $this->user_id);
		}

		return $this->user;
	}

	/**
	 * Set the user of this paper
	 *
	 * @param int|UserApi $user The user (id)
	 */
	public function setUser($user) {
		if ($user instanceof UserApi) {
			$user = $user->getId();
		}

		$this->user = null;
		$this->user_id = $user;
		$this->toSave['user.id'] = $user;
	}

	/**
	 * Returns the state of this paper
	 *
	 * @return PaperStateApi The paper state of this paper
	 */
	public function getState() {
		if (!$this->paperState) {
			$paperStates = CachedConferenceApi::getPaperStates();

			foreach ($paperStates as $paperState) {
				if ($paperState->getId() == $this->state_id) {
					$this->paperState = $paperState;
					break;
				}
			}
		}

		return $this->paperState;
	}

	/**
	 * Returns all equipment necessary for this paper according to the author
	 *
	 * @return EquipmentApi[] The equipment necessary
	 */
	public function getEquipment() {
		if (!$this->equipment) {
			$this->equipment = array();

			$allEquipment = CachedConferenceApi::getEquipment();
			foreach ($allEquipment as $equipment) {
				if (is_int(array_search($equipment->getId(), $this->equipment_id))) {
					$this->equipment[] = $equipment;
				}
			}
		}

		return $this->equipment;
	}

	/**
	 * Set the equipment required for this paper
	 *
	 * @param int[]|EquipmentApi[] $equipment The equipment (ids)
	 */
	public function setEquipment($equipment) {
		$this->equipment = null;
		$this->equipment_id = array();

		foreach ($equipment as $equip) {
			if ($equip instanceof EquipmentApi) {
				$this->equipment_id[] = $equip->getId();
			}
			else if (is_int($equip)) {
				$this->equipment_id[] = $equip;
			}
		}

		$this->toSave['equipment.id'] = implode(';', $this->equipment_id);
	}

	/**
	 * Returns the title of this paper
	 *
	 * @return string The title of this paper
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Set the title of this paper
	 *
	 * @param string|null $title The title
	 */
	public function setTitle($title) {
		$title = (($title !== null) && strlen(trim($title)) > 0) ? trim($title) : null;

		$this->title = $title;
		$this->toSave['title'] = $title;
	}

	public function __toString() {
		return $this->getTitle();
	}
}