<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_networks {
	private $date_id = 0;

	/**
	 * TODOEXPLAIN
	 */
	public function __construct($date_id) {
		$this->date_id = $date_id;
	}

	/**
	 * TODOEXPLAIN
	 */
	private function getDateId() {
		return $this->date_id;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getNetworkIds() {
		$arr = array();

		db_set_active( getSetting('db_connection') );

		// order by session_name
		$query = 'SELECT network_id FROM networks WHERE enabled=1 AND deleted=0 AND show_online=1 AND date_id=' . $this->getDateId() . ' ORDER BY name ';
//echo $query . " ++<br>";
		$result = db_query($query);
		foreach ( $result as $record) {
			$arr[] = $record->network_id;
		}

		db_set_active();

		return $arr;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getNetworkObjects( $enabled = 1, $deleted = 0, $show_online = 1 ) {
		$arr = array();

		db_set_active( getSetting('db_connection') );

		// order by session_name
		$query = 'SELECT network_id FROM networks WHERE enabled=' . $enabled . ' AND deleted=' . $deleted . ' AND show_online=' . $show_online . ' AND date_id=' . $this->getDateId() . ' ORDER BY name ';
//echo $query . " ++<br>";
		$result = db_query($query);
		foreach ( $result as $record) {
			$oNetwork = new class_conference_network($record->network_id);
			$arr[] = $oNetwork;
		}

		db_set_active();

		return $arr;
	}
}