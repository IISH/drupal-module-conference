<?php

/**
 * Holds a combined session participant obtained from the API
 */
class CombinedSessionParticipantApi extends SessionParticipantApi {
  protected $sessionParticipant_id;

	public static function getListWithCriteria(array $properties, $printErrorMessage = true) {
		// Even though none of the ids can be null, querying it like this triggers a join
		// This join makes sure that instances with removed sessions, types or users are filtered out
		$prop = new ApiCriteriaBuilder();
		$properties = array_merge($prop
				->ne('session_id', null)
				->ne('user_id', null)
				->ne('type_id', null)
				->get(),
			$properties);

		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $printErrorMessage);
	}

  /**
   * The session participant
   *
   * @return SessionParticipantApi The session participant
   */
  public function getSessionParticipant() {
    if ($this->sessionParticipant_id !== null) {
      return $this->createNewInstance('SessionParticipantApi', array_merge(
        get_object_vars($this),
        ['id' => $this->sessionParticipant_id]
      ));
    }
    return null;
  }

  /**
   * For the given list with combined session participants, filter out all actual session participants
   *
   * @param CombinedSessionParticipantApi[] $sessionParticipants The list with combined session participants
   *
   * @return SessionParticipantApi[] The session participants
   */
  public static function getAllSessionParticipants($sessionParticipants) {
    $sp = array();
    foreach ($sessionParticipants as $sessionParticipant) {
      if ($sessionParticipant->getSessionParticipant() !== NULL) {
        $sp[] = $sessionParticipant->getSessionParticipant();
      }
    }

    return array_values(array_unique($sp));
  }
} 