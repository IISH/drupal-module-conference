<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_equipment {
	private $equipment_id = 0;
	private $name = '';

	/**
	 * TODOEXPLAIN
	 */
	public function __construct( $equipment_id, $all = false ) {
		$this->init( $all );
	}

	/**
	 * TODOEXPLAIN
	 */
	private function init( $all = false ) {
		db_set_active( getSetting('db_connection') );

		$query = 'SELECT * FROM equipment WHERE equipment_id=' . $this->equipment_id . ' AND deleted=0 AND enabled=1 ';

		$result = db_query($query);
		foreach ( $result as $record) {
			$this->equipment_id = $record->equipment_id;
			$this->name = $record->equipment;
		}

		db_set_active();

	}

	/**
	 * TODOEXPLAIN
	 */
	public function getId() {
		return $this->equipment_id;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getName() {
		return $this->name;
	}
}