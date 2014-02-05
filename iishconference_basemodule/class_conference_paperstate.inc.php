<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_paperstate {
	private $state_id = 0;
	private $date_id = 0;
	private $description = '';

	/**
	 * TODOEXPLAIN
	 */
	public function __construct( $state_id ) {
		$this->state_id = $state_id;

		$this->init();
	}

	/**
	 * TODOEXPLAIN
	 */
	private function init() {
		db_set_active( getSetting('db_connection') );

		$query = 'SELECT * FROM paper_states WHERE paper_state_id=' . $this->state_id;
//echo $query . '+ <br><br>';

		$result = db_query($query);
		foreach ( $result as $record) {
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
