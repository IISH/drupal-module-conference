<?php

/**
 * Holds an election advisory board candidate obtained from the API
 */
class ElectionsAdvisoryBoardApi extends CRUDApiClient {
	protected $nameCandidate;
	protected $noOfVotes;

	public static function getListWithCriteria(array $properties, $printErrorMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $printErrorMessage);
	}

	/**
	 * Add another vote to the 'noOfVotes' for this candidate
	 */
	public function vote() {
		$this->noOfVotes = intval($this->noOfVotes) + 1;
		$this->toSave['noOfVotes'] = $this->noOfVotes;
	}

	/**
	 * Returns the name of the candidate
	 *
	 * @return string The name of the candidate
	 */
	public function getNameCandidate() {
		return $this->nameCandidate;
	}

	/**
	 * Returns the number of votes for this candidate
	 *
	 * @return int The number of votes for this candidate
	 */
	public function getNoOfVotes() {
		return $this->noOfVotes;
	}

	public function __toString() {
		return $this->getNameCandidate();
	}
} 