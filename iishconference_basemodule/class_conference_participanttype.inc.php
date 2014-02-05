<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_participanttype {
	private $user_id = 0;
	private $session_id = 0;
	private $date_id = 0;
//	private $description = '';
	private $functions = array();

	/**
	 * TODOEXPLAIN
	 */
	public function __construct( $user_id, $session_id, $all = false ) {
		$this->init( $user_id, $session_id, $all = false );
	}

	/**
	 * TODOEXPLAIN
	 */
	private function init( $user_id, $session_id, $all = false ) {
		db_set_active( getSetting('db_connection') );

		$query = "SELECT participant_types.participant_type_id 
FROM session_participant 
 INNER JOIN participant_types ON session_participant.participant_type_id = participant_types.participant_type_id
WHERE 1=1
AND user_id=" . $user_id . " 
AND session_id=" . $session_id . " 
ORDER BY importance DESC
";
//echo $query . ' ++++ <br><br>';

		$result = db_query($query);
		foreach ( $result as $record) {
//			$this->state_id = $state_id;
//			$this->description = $record->description;
			$this->functions[] = new class_conference_participant_type($record->participant_type_id, true);
		}

		db_set_active();
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getId() {
		return $this->state_id;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getFunctions() {
		return $this->functions;
	}
}

/**
 * TODOEXPLAIN
 */
class class_conference_participant_type {
	private $id = 0;
	private $date_id = 0;
	private $description = '';

	/**
	 * TODOEXPLAIN
	 */
	public function __construct( $id, $all = false ) {
		$this->init( $id, $all = false );
	}

	/**
	 * TODOEXPLAIN
	 */
	private function init( $id, $all = false ) {
		db_set_active( getSetting('db_connection') );

		$query = "SELECT * FROM participant_types WHERE participant_type_id=" . $id;
//echo $query . '+ <br><br>';

		$result = db_query($query);
		foreach ( $result as $record) {
			$this->id = $id;
			$this->description = $record->type;
//echo $record->type . ' oooo<br>';
		}

		db_set_active();
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getDescription() {
		return $this->description;
	}
}