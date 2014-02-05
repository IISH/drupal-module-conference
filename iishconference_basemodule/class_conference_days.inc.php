<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_days {
	private $date_id = 0;

	/**
	 * TODOEXPLAIN
	 */
	public function __construct( $date_id, $all = false ) {
		if ( $date_id == '' ) {
			$date_id = 0;
		}

		$this->date_id = $date_id;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getDays() {
		$arr = array();
		db_set_active( getSetting('db_connection') );

		$query = 'SELECT * FROM days WHERE date_id=' . $this->date_id . ' AND deleted=0 AND enabled=1 ORDER BY day_number ';

		$result = db_query($query);
		foreach ( $result as $record) {
			// modified by gcu, 2013-11-04
			//$arr[$record->day_id] = new class_conference_day( $record->day_id );
			$arr[] = new class_conference_day( $record->day_id );
		}

		db_set_active();

		return $arr;
	}
}
