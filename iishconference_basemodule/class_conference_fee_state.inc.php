<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_fee_state {
    private $id = 0;
    private $name;
    private $is_default_fee = false;

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

		$query = 'SELECT * FROM fee_states WHERE fee_state_id=' . $id . ' AND deleted=0 AND enabled=1 ';

		$result = db_query($query);
		foreach ($result as $record) {
			$this->id = $id;
            $this->name = $record->name;
            $this->is_default_fee = $record->is_default_fee;
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
    public function getName() {
        return $this->name;
    }

    /**
     * TODOEXPLAIN
     */
    public function isDefaultFee() {
        return $this->is_default_fee;
    }

	/**
	 * TODOEXPLAIN
	 */
	public static function getDefaultFee() {
		db_set_active(getSetting('db_connection'));

		$queryOr = $db_or = db_or()
			->condition('fs.event_id', getSetting('event_id'))
			->isNull('fs.event_id');

		$record = db_select('fee_states', 'fs')
			->fields('fs', array('fee_state_id'))
			->condition('fs.is_default_fee', 1)
			->condition($db_or)
			->condition('fs.enabled', 1)
			->condition('fs.deleted', 0)
			->orderBy('fs.name')
			->execute()
			->fetchAssoc();

		db_set_active();

		return new class_conference_fee_state($record['fee_state_id']);
	}
}