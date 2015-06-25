<?php

/**
 * Holds a participant state obtained from the API
 */
class ParticipantStateApi extends CRUDApiClient {
	const NEW_PARTICIPANT = 0;
	const PARTICIPANT_DATA_CHECKED = 1;
	const PARTICIPANT = 2;
	const WILL_BE_REMOVED = 3;
	const REMOVED_CANCELLED = 4;
	const REMOVED_DOUBLE_ENTRY = 5;
	const NO_SHOW = 6;
	const UNCLEAR = 7;
	const DID_NOT_FINISH_REGISTRATION = 999;

	protected $state;

	public static function getListWithCriteria(array $properties, $printErrorMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $printErrorMessage);
	}

	/**
	 * Returns the name of this state
	 *
	 * @return string The state
	 */
	public function getState() {
		return $this->state;
	}

	public function __toString() {
		return $this->getState();
	}

	/**
	 * Allows the creation of a participant type via an array with details
	 *
	 * @param array $type An array with participant type details
	 *
	 * @return ParticipantTypeApi A participant type object
	 */
	public static function getParticipantStateFromArray(array $state) {
		return self::createNewInstance(__CLASS__, $state);
	}
}