<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_sessionstate {
	private $state_id = 0;
	private $date_id = 0;
	private $description = '';

	/**
	 * TODOEXPLAIN
	 */
	public function __construct( $state_id ) {
		$this->init( $state_id );
	}

	/**
	 * TODOEXPLAIN
	 */
	private function init( $state_id ) {
		db_set_active( getSetting('db_connection') );

		$query = 'SELECT * FROM session_states WHERE session_state_id=' . $state_id;
//echo $query . '+ <br><br>';

		$result = db_query($query);
		foreach ( $result as $record) {
			$this->state_id = $state_id;
			$this->description = $record->description;
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
	public function getDescription() {
		return $this->description;
	}
}