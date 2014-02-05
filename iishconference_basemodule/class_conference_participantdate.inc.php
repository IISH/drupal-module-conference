<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_participantdate extends class_conference_user {
	private $participant_date_id = 0;
	private $deleted = 0;
	private $oState;
    private $oFeeState;
    private $payment_id;
    private $invitation_letter;
    private $invitation_letter_sent;

	/**
	 * TODOEXPLAIN
	 */
	public function __construct( $user_id, $all = false ) {
		parent::__construct($user_id, $all);
		$this->init($user_id, $all);
	}

	/**
	 * TODOEXPLAIN
	 */
	private function init( $user_id, $all = false ) {

		db_set_active( getSetting('db_connection') );

		$extracrit = '';
		if ( $all == false ) {
			$extracrit = ' AND participant_date.enabled=1 AND participant_date.deleted=0 AND users.enabled=1 AND users.deleted=0 ';
		}
		$query = "SELECT participant_date_id, participant_date.deleted, participant_date.participant_state_id,
participant_date.fee_state_id, participant_date.payment_id, participant_date.invitation_letter,
participant_date.invitation_letter_sent FROM users
INNER JOIN participant_date ON users.user_id=participant_date.user_id 
WHERE users.user_id=" . $user_id . " AND participant_date.date_id=" . getSetting('date_id') . $extracrit;
//echo $query . " +<br><br>";

		$result = db_query($query);
		foreach ( $result as $record) {
			$this->participant_date_id = $record->participant_date_id;
			$this->deleted = $record->deleted;
			$this->oState = new class_conference_participant_state($record->participant_state_id);
			$this->payment_id = $record->payment_id;
			$this->invitation_letter = $record->invitation_letter;
            $this->invitation_letter_sent = $record->invitation_letter_sent;

			$this->oFeeState = new class_conference_fee_state($record->fee_state_id);
			if (empty($this->oFeeState) || $this->oFeeState->getId() == 0 || $this->oFeeState->getId() == NULL) {
				$this->oFeeState = class_conference_fee_state::getDefaultFee();
				if (!empty($this->oFeeState)) {
					$this->setFeeState($this->oFeeState->getId());
				}
			}
		}

		db_set_active();
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getDeleted() {
		return $this->deleted;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getState() {
		return $this->oState;
	}

    /**
     * TODOEXPLAIN
     */
    public function getFeeState() {
		 return $this->oFeeState;
    }

  /**
	 * TODOEXPLAIN
	 */
	public function getParticipantDateId() {
		return $this->participant_date_id;
	}

    /**
     * TODOEXPLAIN
     */
    public function getPaymentId() {
        return $this->payment_id;
    }

    /**
     * TODOEXPLAIN
     */
    public function getInvitationLetter() {
        return $this->invitation_letter;
    }

    /**
     * TODOEXPLAIN
     */
    public function getInvitationLetterSent() {
        return $this->invitation_letter_sent;
    }

    /**
	 * TODOEXPLAIN
	 */
	public function getSessionType( $session_id ) {
		$ret = '';
		$separator = '';

		if ( $session_id == '' ) {
			$session_id = 0;
		}

		db_set_active( getSetting('db_connection') );

		$query = 'SELECT session_participant.participant_type_id, `type` FROM session_participant INNER JOIN participant_types ON session_participant.participant_type_id = participant_types.participant_type_id WHERE session_participant.user_id=' . $this->getId() . ' AND session_participant.session_id=' . $session_id . ' AND session_participant.enabled=1 AND session_participant.deleted=0 ORDER BY participant_types.importance DESC ';
		$result = db_query($query);
		foreach ( $result as $record) {
			$ret .= $separator . $record->type;
			$separator = ', ';
		}

		db_set_active();

		return $ret;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function setState( $state_id ) {
		db_set_active( getSetting('db_connection') );

		$query = "UPDATE participant_date SET participant_state_id=" . $state_id . " WHERE user_id=" . $this->getId() . " AND date_id=" . getSetting('date_id');
//echo $query . ' +<br><br>';
		$result = db_query($query);

		db_set_active();
	}

	/**
	 * TODOEXPLAIN
	 */
	public function setFeeState( $fee_state_id ) {
		db_set_active( getSetting('db_connection') );

		$query = "UPDATE participant_date SET fee_state_id=" . $fee_state_id . " WHERE user_id=" . $this->getId() . " AND date_id=" . getSetting('date_id');
//echo $query . ' +<br><br>';
		$result = db_query($query);

		db_set_active();
	}

	/**
	 * TODOEXPLAIN
	 */
	public function setMailSent( $type_of_email ) {
		db_set_active( getSetting('db_connection') );

		$query = "INSERT INTO participant_date_emails (participant_date_id, `type`, datesent) VALUES (" . $this->getParticipantDateId() . ", '$type_of_email', '" . date("Y-m-d H:i:s") . "') ";
//echo $query . ' +<br><br>';
		$result = db_query($query);

		db_set_active();
	}

	/**
	 * TODOEXPLAIN
	 */
	public function setLowerFeeAnswered() {
		db_set_active( getSetting('db_connection') );

//echo "+<br>+<br>+<br>";
		$query = "UPDATE participant_date SET lower_fee_answered=1 WHERE user_id=" . $this->getId() . " AND date_id=" . getSetting('date_id');
//echo $query . ' +<br><br>';
		$result = db_query($query);

		db_set_active();
	}

    /**
     * TODOEXPLAIN
     */
    public function setPaymentId( $payment_id ) {
        db_set_active( getSetting('db_connection') );

        $query = "UPDATE participant_date SET payment_id=" . $payment_id . " WHERE user_id=" . $this->getId() . " AND date_id=" . getSetting('date_id');
        $result = db_query($query);

        db_set_active();
    }

    /**
     * TODOEXPLAIN
     */
    public function setInvitationLetter( $invitationLetter ) {
        db_set_active( getSetting('db_connection') );

        $query = "UPDATE participant_date SET invitation_letter =" . $invitationLetter . " WHERE user_id=" . $this->getId() . " AND date_id=" . getSetting('date_id');
        $result = db_query($query);

        db_set_active();
    }

    /**
     * TODOEXPLAIN
     */
    public function setInvitationLetterSent( $invitationLetterSent ) {
        db_set_active( getSetting('db_connection') );

        $query = "UPDATE participant_date SET invitation_letter_sent =" . $invitationLetterSent . " WHERE user_id=" . $this->getId() . " AND date_id=" . getSetting('date_id');
        $result = db_query($query);

        db_set_active();
    }

    /**
	 * TODOEXPLAIN
	 */
	public function getSessions() {
		$ret = array();

		db_set_active( getSetting('db_connection') );

		$query = "
SELECT sessions.session_id, sessions.session_name 
FROM `session_participant` INNER JOIN sessions 
	ON session_participant.session_id = sessions.session_id 
WHERE user_id=" . $this->getId() . " 
    AND sessions.date_id=" . getSetting('date_id') . "
	AND sessions.enabled=1 AND sessions.deleted=0 AND session_participant.enabled=1 AND session_participant.deleted=0
GROUP BY sessions.session_id, sessions.session_name 
ORDER BY sessions.session_name 
";
//echo $query . '  ++++<br>';
		$result = db_query($query);
		foreach ( $result as $record) {
			$ret[] = $record->session_id;
		}

		db_set_active();

		return $ret;
	}

  /**
   * TODOEXPLAIN
   */
  public function getSessionDays() {
    $ret = array();

    db_set_active( getSetting('db_connection') );

    $query = "
SELECT day_id
FROM session_datetime sdt

INNER JOIN session_room_datetime AS srdt
ON sdt.session_datetime_id = srdt.session_datetime_id

INNER JOIN session_participant AS sp
ON srdt.session_id = sp.session_id

WHERE user_id=" . $this->getId() . "

AND sdt.enabled=1 AND sdt.deleted=0
AND srdt.enabled=1 AND srdt.deleted=0
AND sp.enabled=1 AND sp.deleted=0

GROUP BY sdt.day_id
";
    $result = db_query($query);
    foreach ( $result as $record) {
      $ret[] = $record->day_id;
    }

    db_set_active();

    return $ret;
  }

    /**
     * TODOEXPLAIN
     */
    public function getDaysPresent() {
        $ret = array();

        db_set_active(getSetting('db_connection'));

        $query = "
SELECT day_id
FROM `participant_day`
WHERE user_id=" . $this->getId() . "
AND date_id = " . getSetting('date_id') . "
AND enabled = 1 AND deleted=0
";
        $result = db_query($query);
        foreach ( $result as $record) {
            $ret[] = $record->day_id;
        }

        db_set_active();

        return $ret;
    }

    /**
     * TODOEXPLAIN
     */
    public function getExtras() {
        $ret = array();

        db_set_active(getSetting('db_connection'));

        $query = "
SELECT extra_id
FROM `participant_date_extra`
WHERE participant_date_id=" . $this->getParticipantDateId();

        $result = db_query($query);
        foreach ( $result as $record) {
          $ret[] = $record->extra_id;
        }

        db_set_active();

        return $ret;
    }

	/**
	 * TODOEXPLAIN
	 */
	public function getNetworkIdsWhereChair() {
		$arr = array();

		db_set_active( getSetting('db_connection') );

		$query = 'SELECT networks.network_id FROM networks_chairs INNER JOIN networks ON networks_chairs.network_id=networks.network_id WHERE networks_chairs.enabled=1 AND networks_chairs.deleted=0 AND networks.enabled=1 AND networks.deleted=0 AND date_id=' . getSetting('date_id') . ' AND user_id=' . $this->getId() . ' ORDER BY networks.name ASC ';
//echo $query . '<br><br><br>';
		$result = db_query($query);
		foreach ( $result as $record) {
			$arr[] = $record->network_id;
		}

		db_set_active();

		return $arr;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getNetworkObjectsWhereChair() {
		$arr = array();

		db_set_active( getSetting('db_connection') );

		$query = 'SELECT networks.network_id FROM networks_chairs INNER JOIN networks ON networks_chairs.network_id=networks.network_id WHERE networks_chairs.enabled=1 AND networks_chairs.deleted=0 AND networks.enabled=1 AND networks.deleted=0 AND date_id=' . getSetting('date_id') . ' AND user_id=' . $this->getId() . ' ORDER BY networks.name ASC ';
//		$query = 'SELECT networks.network_id FROM networks_chairs INNER JOIN networks ON networks_chairs.network_id=networks.network_id WHERE networks_chairs.enabled=1 AND networks_chairs.deleted=0 AND networks.enabled=1 AND networks.deleted=0 AND date_id=' . getSetting('date_id') . ' AND user_id=1 ORDER BY networks.name ASC ';
//echo $query . '<br><br><br>';
		$result = db_query($query);
		foreach ( $result as $record) {
			$oNetwork = new class_conference_network($record->network_id);
			$arr[] = $oNetwork;
		}

		db_set_active();

		return $arr;
	}

    /**
     * TODOEXPLAIN
     */
    public function setDay($dayId) {
      db_set_active(getSetting('db_connection'));

      db_merge('participant_day')
        ->key(array(
          'user_id' => $this->getId(),
          'date_id' => getSetting('date_id'),
          'day_id' => $dayId
        ))
        ->execute();

      db_set_active();
    }

    /**
     * TODOEXPLAIN
     */
    public function removeDay($dayId) {
      db_set_active(getSetting('db_connection'));

      db_delete('participant_day')
        ->condition('user_id', $this->getId())
        ->condition('date_id', getSetting('date_id'))
        ->condition('day_id', $dayId)
        ->execute();

      db_set_active();
    }

    /**
     * TODOEXPLAIN
     */
    public function setExtra($extraId) {
      db_set_active(getSetting('db_connection'));

      db_merge('participant_date_extra')
        ->key(array(
          'participant_date_id' => $this->getParticipantDateId(),
          'extra_id' => $extraId
        ))
        ->execute();

      db_set_active();
    }

    /**
     * TODOEXPLAIN
     */
    public function removeExtra($extraId) {
      db_set_active(getSetting('db_connection'));

      db_delete('participant_date_extra')
        ->condition('participant_date_id', $this->getParticipantDateId())
        ->condition('extra_id', $extraId)
        ->execute();

      db_set_active();
    }
}
