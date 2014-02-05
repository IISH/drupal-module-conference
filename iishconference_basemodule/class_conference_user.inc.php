<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_user {
	private $id = 0;
	private $firstname = '';
	private $lastname = '';
	private $email = '';
   private $address = '';
	private $organisation = '';
	private $department = '';
	private $firstlastname = '';
	private $lastfirstname = '';
	private $gender = '';
	private $city = '';
	private $phone = '';
	private $mobile = '';
	private $oCountry = null;
	private $deleted = 0;
	private $enabled = 1;

	/**
	 * TODOEXPLAIN
	 */
	public function __construct( $id_or_email, $all = false ) {
		$this->init($id_or_email, $all);
	}

	/**
	 * TODOEXPLAIN
	 */
	private function init( $id_or_email, $all = false ) {
		db_set_active( getSetting('db_connection') );

		if ( is_numeric( $id_or_email ) && (int)$id_or_email == ($id_or_email+0) ) {
			$id_or_email = (int)$id_or_email;
			$query = "SELECT * FROM users WHERE user_id=" . $id_or_email . " ";
		} else {
			$query = "SELECT * FROM users WHERE email='" . addslashes($id_or_email) . "' ";
		}

		if ( !$all ) {
			$query .= " AND enabled=1 AND deleted=0 ";
		}

//echo $query . ' +<br>';

		$result = db_query($query);
		foreach ( $result as $record) {
			$this->id = $record->user_id;
			$this->firstname = trim($record->firstname);
			$this->lastname = trim($record->lastname);
			$this->email = trim($record->email);
            $this->address = trim($record->address);
			$this->organisation = trim($record->organisation);
			$this->department = trim($record->department);
			$this->gender = $record->gender;
			$this->city = $record->city;
			$this->phone = trim($record->phone);
			$this->mobile = trim($record->mobile);
			$this->oCountry = new class_conference_country($record->country_id);
			$this->enabled = $record->enabled;
			$this->deleted = $record->deleted;

			//
			$this->concatNames();
		}

		db_set_active();
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getEmail() {
		return $this->email;
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
	private function concatNames() {
		$this->firstlastname = trim( $this->firstname . ' ' . $this->lastname );

		if ( $this->lastname != '' && $this->firstname != '' ) {
			$this->lastfirstname = trim( $this->lastname . ', ' . $this->firstname);
		} else {
			$this->lastfirstname = trim( $this->lastname . ' ' . $this->firstname);
		}
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getFirstname() {
		return $this->firstname;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getLastname() {
		return $this->lastname;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getLastFirstname() {
		return $this->lastfirstname;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getFirstLastname() {
		return $this->firstlastname;
	}

	/**
	* TODOEXPLAIN
	*/
	function getAddress() {
	  	return $this->address;
	}

	/**
	 * TODOEXPLAIN
	 */
	function setAddress($address) {
		db_set_active(getSetting('db_connection'));

		db_update('users')
			->fields(array(
				'address' => $address,
			))
			->condition('user_id', $this->getId())
			->execute();

		db_set_active();
	}

  /**
	 * TODOEXPLAIN
	 */
	public function getOrganisation() {
		return $this->organisation;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getDepartment() {
		return $this->department;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getDeleted() {
		return $this->deleted;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getGender() {
		return $this->gender;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getPhone() {
		return $this->phone;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getMobile() {
		return $this->mobile;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getCountry() {
		return $this->oCountry;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getGenderFull() {
		$gender = $this->getGender();

		switch ( strtolower($gender) ) {
			case "m":
				$gender = 'Male';
				break;
			case "f":
				$gender = 'Female';
				break;
			default:
				$gender = '';
		}

		return $gender;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getParticipantNAPInfo() {
		$ret = '';

		$ret .= "First name: " . $this->getFirstname() . " \n";
		$ret .= "Last name: " . $this->getLastname() . " \n";
		$ret .= "Gender: " . $this->getGenderFull() . " \n";
		$ret .= "Organisation: " . ifEmpty($this->getOrganisation(), '-') . " \n";
		$ret .= "Department: " . ifEmpty($this->getDepartment(), '-') . " \n";
		$ret .= "E-mail: " . $this->getEmail() . " \n";
		$ret .= "City: " . ifEmpty($this->getCity(), '-') . " \n";
		$country = '-';
		if ( is_object( $this->getCountry() ) ) {
			if ( $this->getCountry()->getId() > 0 ) {
				$country = $this->getCountry()->getName();
			}
		}
		$ret .= "Country: " . $country . " \n";
		$ret .= "Phone: " . ifEmpty($this->getPhone(), '-') . " \n";
		$ret .= "Mobile: " . ifEmpty($this->getMobile(), '-') . " \n";

		return $ret;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getParticipantNAPInfo_short() {
		$ret = '';

		$ret .= "Participant name: " . $this->getFirstLastname() . " ( " . $this->getEmail() . " ) \n";
/*		$state = '';
		if ( $this->getDeleted() ) {
			$state = 'deleted';
		}
		if ( $state == '' ) {
			$oParticipantDate = new class_conference_participantdate($this->getId());
			$state = $oParticipantDate->getState()->getDescription();
		}
		$ret .= "Participant state: " . $state . " \n";
*/
		$country = '';
		if ( $this->getCountry()->getId() > 0 ) {
			$country = " ( " . $this->getCountry()->getName() . " ) ";
		}
		$ret .= "Organisation: " . ifEmpty($this->getOrganisation(), '-') . $country . " \n";

		return $ret;
	}

	/**
	 * TODOEXPLAIN
	 */
	function isCrew() {
		$ret = false;

		db_set_active( getSetting('db_connection') );

		// TODOLATER: ook via groep controleren
		$result = db_query('SELECT * FROM users_roles WHERE user_id=' . $this->getId() . ' AND ( event_id=' . getSetting('event_id') . ' OR event_id IS NULL ) ');

		foreach ($result as $record) {
			$ret = true;
		}

		db_set_active();

		return $ret;
	}

	/**
	 * TODOEXPLAIN
	 */
	function isSuperAdmin() {
		$ret = false;

		db_set_active( getSetting('db_connection') );

		// 
		$query = 'SELECT * FROM users_roles WHERE user_id=' . $this->getId() . ' AND ( event_id IS NULL OR event_id = ' . getSetting('event_id') . ' ) ';
//echo $query . ' +<br>';
		$result = db_query( $query );

//		if ( $result ) {
		foreach ($result as $record) {
			$ret = true;
		}

		db_set_active();

		return $ret;
	}

	/**
	 * TODOEXPLAIN
	 */
	function isNetworkChair() {
		$ret = false;

		db_set_active( getSetting('db_connection') );

		$query = 'SELECT * FROM networks_chairs INNER JOIN networks ON networks_chairs.network_id=networks.network_id WHERE networks_chairs.enabled=1 AND networks_chairs.deleted=0 AND networks.enabled=1 AND networks.deleted=0 AND date_id=' . getSetting('date_id') . ' AND user_id=' . $this->getId();
//echo $query . ' +<br>';
		$result = db_query( $query );

		foreach ($result as $record) {
			$ret = true;
		}

		db_set_active();

		return $ret;
	}

	/**
	 * TODOEXPLAIN
	 */
	function isOfParticipantType( $type = 0 ) { // 7 organizer, 6 chair
		$ret = false;

		db_set_active( getSetting('db_connection') );

		// 
		$query = "
SELECT session_participant.user_id FROM session_participant INNER JOIN sessions ON session_participant.session_id = sessions.session_id
WHERE session_participant.enabled=1 AND session_participant.deleted=0
AND sessions.enabled=1 AND sessions.deleted=0
AND session_participant.user_id=" . $this->getId() . " 
AND sessions.date_id=" . getSetting('date_id') . " ";
//echo $query . ' +<br>';
		$result = db_query( $query );

		foreach ($result as $record) {
			$ret = true;
		}

		db_set_active();

		return $ret;
	}

	function isOrganiser() {
		return $this->isOfParticipantType(7);
	}

	function isChair() {
		return $this->isOfParticipantType(6);
	}

	function hasPaperWithoutSession() {
		$ret = false;

		db_set_active( getSetting('db_connection') );

		// 
		$query = "
SELECT paper_id FROM papers
WHERE enabled=1 AND deleted=0
AND user_id=" . $this->getId() . " 
AND date_id=" . getSetting('date_id') . "
AND session_id IS NULL
";

//echo $query . ' +<br>';

		$result = db_query( $query );

		foreach ($result as $record) {
			$ret = true;
		}

		db_set_active();

		return $ret;
	}
}
