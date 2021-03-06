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
	protected $notInCombinationWith_id;

	private $notInCombinationWith;

	public static function getListWithCriteria(array $properties, $printErrorMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $printErrorMessage);
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
	 * Returns whether the combination of types is allowed
	 *
	 * @param int[] $types The participant type ids in question
	 *
	 * @return bool Whether the combination of types is allowed
	 */
	public static function isCombinationOfTypesAllowed(array $types) {
		foreach (CachedConferenceApi::getParticipantTypes() as $type) {
			if ((array_search($type->getId(), $types) !== false) &&
				(count(array_intersect($type->getNotInCombinationWithId(), $types)) > 0)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns a string describing the combinations of participant types that are not allowed in a single session
	 *
	 * @return string Describes the combinations of participant types that are not allowed in a single session
	 */
	public static function getCombinationsNotAllowedText() {
		$text = array();
		foreach (CachedConferenceApi::getParticipantTypes() as $type) {
			if (count($type->getNotInCombinationWith()) > 0) {
				$text[] = iish_t('The role @type is not allowed in combination with the role(s) @types',
					array('@type'  => $type->getType(),
					      '@types' => implode(', ', $type->getNotInCombinationWith())));
			}
		}

		return implode("\n", $text);
	}

	/**
	 * Returns whether one of the types requires a paper
	 *
	 * @param int[] $types The participant type ids in question
	 *
	 * @return bool Whether one of the types requires a paper
	 */
	public static function containsTypeWithPaper(array $types) {
		foreach (CachedConferenceApi::getParticipantTypes() as $type) {
			if ($type->getWithPaper() && (array_search($type->getId(), $types) !== false)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns a list of participant type ids with which this type cannot be combined together in a single session
	 *
	 * @return int[] A list of participant type ids
	 */
	public function getNotInCombinationWithId() {
		return $this->notInCombinationWith_id;
	}

	/**
	 * Returns a list of participant types with which this type cannot be combined together in a single session
	 *
	 * @return ParticipantTypeApi[] A list of participant types
	 */
	public function getNotInCombinationWith() {
		if ($this->notInCombinationWith === null) {
			$this->notInCombinationWith = array();
			foreach (CachedConferenceApi::getParticipantTypes() as $type) {
				foreach ($this->notInCombinationWith_id as $typeId) {
					if ($type->getId() == $typeId) {
						$this->notInCombinationWith[] = $type;
					}
				}
			}
		}

		return $this->notInCombinationWith;
	}

	/**
	 * Should a participant with this type should be added to a session with a paper?
	 *
	 * @return bool Whether a participant with this type should be added to a session with a paper
	 */
	public function getWithPaper() {
		return $this->withPaper;
	}

	/**
	 * The name of this type
	 *
	 * @return string The type
	 */
	public function getType() {
		return $this->type;
	}

	public function __toString() {
		return $this->getType();
	}
} 