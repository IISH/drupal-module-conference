<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_extras {
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
	public function getExtras() {
		$arr = array();
		db_set_active( getSetting('db_connection') );

		$query = 'SELECT * FROM extras WHERE date_id=' . $this->date_id . ' AND deleted=0 AND enabled=1 ORDER BY extra';

		$result = db_query($query);
		foreach ( $result as $record) {
			$arr[] = new class_conference_extra( $record->extra_id );
		}

		db_set_active();

		return $arr;
	}
}
