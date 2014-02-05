<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_session {
	private $session_id = 0;
	private $init_session_id = 0;
	private $session_code = '';
	private $session_name = '';
	private $session_abstract = '';
	private $added_by;
	private $deleted = 0;
	private $oState = null;
	private $oRoom = null;
	private $oDay = null;
	private $oTime = null;

	/**
	 * TODOEXPLAIN
	 */
	public function __construct( $session_id, $all = false ) {
		$this->init($session_id, $all);
	}

	/**
	 * TODOEXPLAIN
	 */
	private function init( $session_id, $all = false ) {
		db_set_active( getSetting('db_connection') );

		if ( $session_id == '' ) {
			$session_id = 0;
		}
		$this->init_session_id = $session_id;

		if ( $all ) {
			$query = 'SELECT session_id, session_code, session_name, session_abstract, added_by, deleted, session_state_id FROM sessions WHERE session_id=' . $session_id . ' ';
		} else {
			$query = 'SELECT session_id, session_code, session_name, session_abstract, added_by, deleted, session_state_id FROM sessions WHERE session_id=' . $session_id . ' AND enabled=1 AND deleted=0 ';
		}
//echo $query . ' xx<br>';
		$result = db_query($query);
		foreach ( $result as $record) {
			$this->session_id = $record->session_id;
			$this->session_code = $record->session_code;
			$this->session_name = $record->session_name;
			$this->session_abstract = $record->session_abstract;
			$this->added_by = new class_conference_user($record->added_by);
			$this->deleted = $record->deleted;
			$this->oState= new class_conference_sessionstate( $record->session_state_id );

			$query2 = "SELECT room_id, day_id, session_datetime.session_datetime_id 
FROM session_room_datetime 
INNER JOIN session_datetime 
   ON session_room_datetime.session_datetime_id = session_datetime.session_datetime_id 
WHERE session_id = " . $session_id . " 
AND session_room_datetime.enabled=1 AND session_room_datetime.deleted=0 
AND session_datetime.enabled=1 AND session_datetime.deleted=0 ";
//echo $query2 . ' +<br>';

			db_set_active( getSetting('db_connection') );
			$result2 = db_query($query2);
			foreach ( $result2 as $record2) {
				$this->oRoom = new class_conference_room( $record2->room_id );
				$this->oDay = new class_conference_day( $record2->day_id );
				$this->oTime = new class_conference_sessiondatetime( $record2->session_datetime_id );
			}

		}

		db_set_active();
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getId() {
		return $this->session_id;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getTime() {
		return $this->oTime;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getDay() {
		return $this->oDay;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getRoom() {
		return $this->oRoom;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getInitId() {
		return $this->init_session_id;
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
	public function getAddedBy() {
		return $this->added_by;
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
	public function getCode() {
		return $this->session_code;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getName() {
		return $this->session_name;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getAbstract($length = 0) {
		$ret = $this->session_abstract;
		if ( $length > 0 && strlen( $ret ) > $length ) {
			$ret = substr($ret, 0, $length) . "...";
		}

		return $ret;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getListOfNewRegistrations() {
		$arr = array();

		db_set_active( getSetting('db_connection') );

		// order by importance
		$query = "
SELECT session_participant.* 
FROM session_participant 
	INNER JOIN participant_date ON session_participant.user_id = participant_date.user_id 
	INNER JOIN participant_types ON session_participant.participant_type_id=participant_types.participant_type_id 
WHERE session_id=" . $this->getId() . " 
	AND participant_date.date_id=" . getSetting('date_id') . " 
	AND participant_date.deleted=0 
	AND participant_date.enabled=1 
	AND participant_state_id=1 
	AND participant_date_id NOT IN ( 
		SELECT participant_date_id 
		FROM participant_date_emails 
		WHERE `type`='newregistrationtochair' 
		AND enabled=1 AND deleted=0 
	) 
ORDER BY participant_types.importance DESC 
";

		$result = db_query($query);
		foreach ( $result as $record) {
			$arr[] = $record->user_id;
		}

		// remove duplicate values
		$arr = array_unique($arr);

		db_set_active();

		return $arr;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getListOfAllRegistrations() {
		$arr = array();

		db_set_active( getSetting('db_connection') );

		// order by importance
		$query = "
SELECT session_participant.* 
FROM session_participant 
	INNER JOIN participant_date ON session_participant.user_id = participant_date.user_id 
	INNER JOIN participant_types ON session_participant.participant_type_id=participant_types.participant_type_id 
WHERE session_id=" . $this->getId() . " 
	AND participant_date.date_id=" . getSetting('date_id') . " 
	AND participant_date.deleted=0 
	AND participant_date.enabled=1 
	AND participant_state_id IN (0,1,2) 
ORDER BY participant_types.importance DESC 
";

		$result = db_query($query);
		foreach ( $result as $record) {
			$arr[] = $record->user_id;
		}

		// remove duplicate values
		$arr = array_unique($arr);

		db_set_active();

		return $arr;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getNetworks() {
		$arr = array();

		db_set_active( getSetting('db_connection') );

		// order by importance
		$query = "
SELECT networks.network_id 
FROM session_in_network INNER JOIN networks ON session_in_network.network_id = networks.network_id
WHERE session_id=" . $this->getId() . " 
AND networks.enabled = 1 AND networks.deleted = 0 
ORDER BY networks.name
";

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
	public function getSubmittedByName() {
		$ret = '';

		db_set_active( getSetting('db_connection') );

		// order by importance
		$query = "
SELECT sessions.added_by 
FROM sessions
	INNER JOIN users ON sessions.added_by = users.user_id 
WHERE session_id=" . $this->getId() . " 
	AND users.enabled = 1 AND users.deleted = 0 
";

		$result = db_query($query);
		foreach ( $result as $record) {
			$oParticipant = new class_conference_participantdate($record->added_by);
			$ret = $oParticipant->getFirstLastname();
		}

		db_set_active();

		return $ret;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getPrevNext($networkId = null, $all = false, $post = null) {
		$prev = 0;
		$next = 0;
		$found = 0;
		$tmp = 0;

		db_set_active( getSetting('db_connection') );

		// order by session_name
		$query = "
SELECT session_in_network.session_id, session_name 
FROM session_in_network 
	INNER JOIN sessions ON session_in_network.session_id=sessions.session_id 
WHERE network_id=" . $networkId . " 
	AND sessions.date_id=" . getSetting('date_id') . " 
ORDER BY session_name 
";
//echo $query . " ++<br>";
		$result = db_query($query);
		foreach ( $result as $record) {
			$s = new class_conference_session( $record->session_id, $all );
			if ( $s->getId() != 0 ) {
//				$arr[] = $s;

				if ( $found == 1 ) {
					$next = $record->session_id;
					break;
				}
				if ( $this->getId() == $record->session_id ) {
					$prev = $tmp;
					$found = 1;
				}

				$tmp = $record->session_id;

			}
		}

		if ( $post != null ) {
			// if -1 and next is 0 then next => -1
			if ( $post != $this->getInitId() && $next == 0 ) {
				$next = $post;
			}

			// if -1 then prev is the last sessions from for loop
			if ( $post == $this->getInitId() ) {
				$prev = $tmp;
			}
		}

		db_set_active();

		return array($prev, $next);
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getParticipants( $all = false ) {
		$arr = array();

		db_set_active( getSetting('db_connection') );

		$crit_enabled = '';
		if ( $all !== true ) {
			$crit_enabled = "
	AND participant_date.deleted=0 AND participant_date.enabled=1 
	AND users.deleted=0 AND users.enabled=1 
	AND participant_state_id IN (1,2) 
";
		}

		$query = "
SELECT session_participant.* 
FROM session_participant 
	INNER JOIN participant_date ON session_participant.user_id = participant_date.user_id 
	INNER JOIN participant_types ON session_participant.participant_type_id=participant_types.participant_type_id 
	INNER JOIN users ON participant_date.user_id = users.user_id 
WHERE session_participant.session_id=" . $this->getId() . " AND participant_date.date_id=" . getSetting('date_id') . " " . $crit_enabled . " 
ORDER BY participant_types.importance DESC, users.lastname ASC, users.firstname ASC 
";

		$result = db_query($query);
		foreach ( $result as $record) {
			$p = new class_conference_user($record->user_id, true);
			$arr[] = $p;
		}

		db_set_active();

		return $arr;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getParticipantsWithoutSession( $networkId = 0, $all = false ) {
		$arr = array();

		db_set_active( getSetting('db_connection') );

		$crit_enabled = '';
		if ( $all !== true ) {
			$crit_enabled = "
	AND participant_date.deleted=0 
	AND participant_date.enabled=1 
	AND users.deleted=0 
	AND users.enabled=1 
	AND participant_state_id IN (0,1,2) 
";
		}

		$query = "
SELECT papers.user_id 
FROM papers 
	INNER JOIN participant_date ON papers.user_id = participant_date.user_id 
	INNER JOIN users ON participant_date.user_id = users.user_id 
WHERE papers.session_id IS NULL AND papers.network_proposal_id=" . $networkId . " AND papers.date_id=" . getSetting('date_id') . " AND participant_date.date_id=" . getSetting('date_id') . " " . $crit_enabled . " 
ORDER BY users.lastname ASC, users.firstname ASC 
";

//echo $query . ' +++<br>';

		// order by importance

		$result = db_query($query);
		foreach ( $result as $record) {
			$p = new class_conference_user($record->user_id, true);
			$arr[] = $p;
		}

		db_set_active();

		return $arr;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function setMailSessionState( $value ) {
		db_set_active( getSetting('db_connection') );

		$query = "UPDATE sessions SET mail_session_state=" . $value . " WHERE session_id=" . $this->getId();
//echo $query . ' +<br><br>';
		$result = db_query($query);

		db_set_active();
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getSessionInfo_for_mail() {
		$ret = '';

		$ret .= " \n";
		$ret .= "Session Info \n";
		$ret .= "- - - - - - - - - \n \n";

		$ret .= "Session proposal: " . $this->getName() . " \n \n";
		$ret .= "Session state: " . $this->getState()->getDescription() . " \n";
		$ret .= " \n \n";

		// NETWORK
		$networks = $this->getNetworks();
		foreach ( $networks as $network ) {
			$oNetwork = new class_conference_network( $network );
			$ret .= "Network: " . $oNetwork->getNetworkName() . " \n";
			$chairs = $oNetwork->getChairs();
			$ret .= "Network chair(s): \n";
			foreach ( $chairs as $chair ) {
				$ret .= " - " . $chair->getFirstLastname() . " ( " . $chair->getEmail() . " ) " . " \n";
			}
			$ret .= " \n";
		}
		$ret .= " \n";

		// DEELNEMERS MET EN ZONDER PAPERS
		$participants = $this->getParticipants( false );
		$ret .= "Participants \n";
		foreach ( $participants as $participant ) {
			$ret .= $participant->getParticipantNAPInfo_short();

			// function
			$oParticipantType = new class_conference_participanttype($participant->getId(), $this->getId(), true);
			$arrFunctions = array();
			foreach ( $oParticipantType->getFunctions() as $function ) {
				$arrFunctions[] = $function->getDescription();
			}
			$sFunctions = implode(', ', $arrFunctions);
			$ret .= "Function: " . $sFunctions . " \n";

			// paper
			$oParticipantSession = new class_conference_participantsession($participant->getId(), $this->getId(), 0, true);
			if ( is_object( $oParticipantSession->getState() ) ) {
				$ret .= "Paper title: " . $oParticipantSession->getTitle() . " \n";

				$state = '';
				if ( $oParticipantSession->getDeleted() ) {
					$state = 'paper deleted';
				}
				if ( $state == '' ) {
					$state = $oParticipantSession->getState()->getDescription();
				}
				$ret .= "Paper state: " . $state . " \n";

				$coauthor = trim($oParticipantSession->getCoauthors());
				if ( $coauthor != '' ) {
					$ret .= "Co-author(s): " . $coauthor . " \n";
				}
			}
			$ret .= " \n";
		}

		return $ret;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getOrganizersAndAddedBy() {
		$arr = array();

		$ids = array();

		if ( $this->getAddedBy()->getId() > 0 ) {
			$ids[] = $this->getAddedBy()->getId();
		}

		db_set_active( getSetting('db_connection') );

		$query = "
SELECT user_id 
FROM session_participant 
WHERE session_id=" . $this->getId() . " AND participant_type_id=7 AND enabled=1 AND deleted=0 ";
//echo $query . ' +++<br>';

		$result = db_query($query);
		foreach ( $result as $record) {
//echo $record->user_id . ' x<br>';
			$ids[] = $record->user_id;
		}

		$ids = array_unique($ids);

		foreach ( $ids as $id ) {
			$arr[] = new class_conference_user( $id );
		}
		db_set_active();

		return $arr;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getParticipantsByType( $type ) {
		$arr = array();

		db_set_active( getSetting('db_connection') );

		$query = "
SELECT users.user_id 
FROM session_participant 
	INNER JOIN users on session_participant.user_id = users.user_id
WHERE session_id=" . $this->getId() . " 
	AND participant_type_id=" . $type . " 
	AND session_participant.enabled=1 AND session_participant.deleted=0 
	AND users.enabled=1 AND users.deleted=0 
ORDER BY users.lastname, users.firstname
";
//echo $query . ' +++<br>';

		$result = db_query($query);
		foreach ( $result as $record) {
			$arr[] = new class_conference_user( $record->user_id );
		}

		db_set_active();

		return $arr;
	}
}
