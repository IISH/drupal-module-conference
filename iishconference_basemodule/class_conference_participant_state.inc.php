<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_participant_state {
	private $id = 0;
	private $date_id = 0;
	private $description = '';

	/**
	 * TODOEXPLAIN
	 */
	public function __construct( $id, $all = false ) {
		$this->init( $id, $all );
	}

	/**
	 * TODOEXPLAIN
	 */
	private function init( $id, $all = false ) {
		db_set_active( getSetting('db_connection') );

		$query = 'SELECT * FROM participant_states WHERE participant_state_id=' . $id;
//echo $query . '+ <br><br>';

		$result = db_query($query);
		foreach ( $result as $record) {
			$this->id = $id;
			$this->description = $record->participant_state;
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