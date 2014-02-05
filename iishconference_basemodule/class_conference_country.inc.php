<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_country {
	private $country_id = 0;
	private $name = '';
	private $iso_code;

	/**
	 * TODOEXPLAIN
	 */
	public function __construct( $country_id, $all = false ) {
		if ( $country_id == '' ) {
			$country_id = 0;
		}

		$this->init( $country_id, $all );
	}

	/**
	 * TODOEXPLAIN
	 */
	private function init( $country_id, $all = false ) {
		db_set_active( getSetting('db_connection') );

		$query = 'SELECT * FROM countries WHERE country_id=' . $country_id . ' AND deleted=0 AND enabled=1 ';

		$result = db_query($query);
		foreach ( $result as $record) {
			$this->country_id = $country_id;
			$this->name = $record->name_english;
			$this->iso_code = $record->iso_code;
		}

		db_set_active();

	}

	/**
	 * TODOEXPLAIN
	 */
	public function getId() {
		return $this->country_id;
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
	public function getISOCode() {
		return $this->iso_code;
	}

	/**
	* TODOEXPLAIN
	*/
	public static function getExcemptCountries() {
		$ret = array();
		db_set_active(getSetting('db_connection'));

		$query = "
			SELECT exempt_country_id
			FROM `country_exemptions`
			WHERE country_id=" . variable_get('country_id');

		$result = db_query($query);
		foreach ( $result as $record) {
			$ret[] = $record->exempt_country_id;
		}

		db_set_active();
		return $ret;
	}
}