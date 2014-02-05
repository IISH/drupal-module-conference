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

	public static function getListWithCriteria(array $properties, $showDrupalMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $showDrupalMessage);
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
	 * Returns the co-authors of this paper as a single string
	 *
	 * @return string|null The co-authors of this paper as a single string
	 */
	public function getCoAuthors() {
		return $this->coAuthors;
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
	 * Returns the id of the session this paper may be planned in
	 *
	 * @return int|null The session id
	 */
	public function getSessionId() {
		return $this->session_id;
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
			$props = new ApiCriteriaBuilder();
			$this->user = UserApi::getListWithCriteria(
			                     $props
				                     ->eq('id', $this->user_id)
				                     ->get()
			)->getFirstResult();
		}

		return $this->user;
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

	public function __toString() {
		return $this->getTitle();
	}
}