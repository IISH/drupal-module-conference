<?php

/**
 * Holds a session state obtained from the API
 */
class SessionStateApi extends CRUDApiClient {
	const NEW_SESSION = 1;
	const SESSION_ACCEPTED = 2;
	const SESSION_NOT_ACCEPTED = 3;
	const SESSION_IN_CONSIDERATION = 4;
	
	protected $description;
	protected $shortDescription;

	public static function getListWithCriteria(array $properties, $showDrupalMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $showDrupalMessage);
	}

	/**
	 * Returns the description of this session state
	 *
	 * @return string The description
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Returns the short description of this session state
	 *
	 * @return string The short description
	 */
	public function getShortDescription() {
		return $this->shortDescription;
	}

	public function __toString() {
		return $this->getDescription();
	}
} 