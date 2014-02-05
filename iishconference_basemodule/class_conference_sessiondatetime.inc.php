<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_sessiondatetime {
	private $id = 0;
	private $period = '';
	private $index_number = 0;

	/**
	 * TODOEXPLAIN
	 */
	public function __construct( $id, $all = false ) {
		if ( $id == '' ) {
			$id = 0;
		}

		$this->init( $id, $all );
	}

	/**
	 * TODOEXPLAIN
	 */
	private function init( $id, $all = false ) {
		db_set_active( getSetting('db_connection') );

		$query = 'SELECT * FROM session_datetime WHERE session_datetime_id=' . $id . ' AND deleted=0 AND enabled=1 ';

		$result = db_query($query);
		foreach ( $result as $record) {
			$this->id = $id;
			$this->period = $record->period;
			$this->index_number = $record->index_number;
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
	public function getPeriod( $extra_spacing = false ) {
		$period = $this->period;
		if ( $extra_spacing ) {
			$period = str_replace('-', ' - ', $period);
			$period = str_replace('  ', ' ', $period);
		}

		return $period;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getIndexNumber() {
		return $this->index_number;
	}
}