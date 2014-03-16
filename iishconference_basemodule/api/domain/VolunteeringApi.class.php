<?php

/**
 * Holds a volunteering type obtained from the API
 */
class VolunteeringApi extends CRUDApiClient {
	const CHAIR = 9;
	const DISCUSSANT = 10;
	const COACH = 11;
	const PUPIL = 12;

	protected $description;

	public static function getListWithCriteria(array $properties, $printErrorMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $printErrorMessage);
	}

	/**
	 * The description of the volunteering type
	 *
	 * @return string The description
	 */
	public function getDescription() {
		return $this->description;
	}

	public function __toString() {
		return $this->getDescription();
	}
} 