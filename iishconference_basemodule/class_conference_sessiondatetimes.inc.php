<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_sessiondatetimes {
	private $day_id = 0;

	/**
	 * TODOEXPLAIN
	 */
	public function __construct( $day_id, $all = false ) {
		if ( $day_id == '' ) {
			$day_id = 0;
		}

		$this->day_id = $day_id;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getTimes() {
		$arr = array();
		db_set_active( getSetting('db_connection') );

		$query = 'SELECT * FROM session_datetime WHERE day_id=' . $this->day_id . ' AND deleted=0 AND enabled=1 ORDER BY index_number ';

		$result = db_query($query);
		foreach ( $result as $record) {
			$arr[] = new class_conference_sessiondatetime( $record->session_datetime_id );
		}

		db_set_active();

		return $arr;
	}
}
