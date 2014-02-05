<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_day {
	private $id = 0;
	private $day = 0;
	private $month = 0;
	private $year = 0;
	private $mdate;
    private $day_number;

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

		$query = 'SELECT * FROM days WHERE day_id=' . $id . ' AND deleted=0 AND enabled=1 ';

		$result = db_query($query);
		foreach ( $result as $record) {
			$this->id = $id;

			$arr = explode('-', $record->day);
			$this->day = $arr[2];
			$this->month = $arr[1];
			$this->year = $arr[0];
			$this->mdate = mktime(0,0,0, $this->month, $this->day, $this->year);

            $this->day_number = $record->day_number;
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
	public function getDay( $format = "Y-m-d" ) {
		return date($format, $this->mdate);
	}

    /**
     * TODOEXPLAIN
     */
    public function getDayNumber() {
        return $this->day_number;
    }

	/**
	 * TODOEXPLAIN
	 */
	public function __toString() {
		return 'Day ' . $this->getDayNumber() . ': ' . $this->getDay('l j F Y');
	}
}