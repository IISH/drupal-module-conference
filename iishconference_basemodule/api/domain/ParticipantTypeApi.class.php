<?php

/**
 * Holds a participant type obtained from the API
 */
class ParticipantTypeApi extends CRUDApiClient {
	const CHAIR_ID = 6;
	const ORGANIZER_ID = 7;
	const AUTHOR_ID = 8;
	const CO_AUTHOR_ID = 9;
	const DISCUSSANT_ID = 10;

	protected $type;
	protected $withPaper;

	public static function getListWithCriteria(array $properties, $showDrupalMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $showDrupalMessage);
	}

	/**
	 * Allows the creation of a participant type via an array with details
	 *
	 * @param array $type An array with participant type details
	 *
	 * @return ParticipantTypeApi A participant type object
	 */
	public static function getParticipantTypeFromArray(array $type) {
		return self::createNewInstance(__CLASS__, $type);
	}

	/**
	 * The name of this type
	 *
	 * @return string The type
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Should a participant with this type should be added to a session with a paper?
	 *
	 * @return bool Whether a participant with this type should be added to a session with a paper
	 */
	public function getWithPaper() {
		return $this->withPaper;
	}

	public function __toString() {
		return $this->getType();
	}
} 