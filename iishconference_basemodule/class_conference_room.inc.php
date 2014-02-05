<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_room {
	private $id = 0;
	private $room_name = '';
	private $room_number = '';
	private $comment = '';

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

		$query = 'SELECT * FROM rooms WHERE room_id=' . $id . ' AND enabled=1 AND deleted=0 ';

		$result = db_query($query);
		foreach ( $result as $record) {
			$this->id = $id;

			$this->room_name = $record->room_name;
			$this->room_number = $record->room_number;
			$this->comment = $record->comment;
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
	public function getRoomName() {
		return $this->room_name;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getRoomNumber() {
		return $this->room_number;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getComment() {
		return $this->comment;
	}
}
