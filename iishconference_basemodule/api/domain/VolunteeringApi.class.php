<?php

/**
 * Holds a volunteering type obtained from the API
 */
class VolunteeringApi extends CRUDApiClient {
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