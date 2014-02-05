<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_network {
	private $network_id = 0;
	private $network_name = '';
	private $network_forward_email = '';
	private $network_chair_email = array();
	private $network_chair_name = array();
	private $network_chair_id = array();

	/**
	 * TODOEXPLAIN
	 */
	public function __construct( $network_id ) {
		if ( $network_id == '' ) {
			$network_id = 0;
		}

		$this->network_id = $network_id;
		$this->calcNetworkName();
		$this->calcChairInfo();
	}

	/**
	 * TODOEXPLAIN
	 */
	private function addNetworkChairEmail( $value ) {
		$this->network_chair_email[] = $value;
	}

	/**
	 * TODOEXPLAIN
	 */
	private function addNetworkChairName( $value ) {
		$this->network_chair_name[] = $value;
	}

	/**
	 * TODOEXPLAIN
	 */
	private function addNetworkChairId( $value ) {
		$this->network_chair_id[] = $value;
	}

	/**
	 * TODOEXPLAIN
	 */
	private function setNetworkName( $value ) {
		$this->network_name = $value;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getNetworkId() {
		return $this->network_id;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getNetworkChairEmail() {
		return $this->network_chair_email;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getNetworkChairName() {
		return $this->network_chair_name;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getNetworkChairId() {
		return $this->network_chair_id;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getNetworkName() {
		return $this->network_name;
	}

	/**
	 * TODOEXPLAIN
	 */
	private function calcChairInfo() {
		$email = '';
		$name = '';
		$separator = '';
		$separator2 = '';

		db_set_active( getSetting('db_connection') );

		$query = 'SELECT users.user_id, users.firstname, users.lastname, users.email FROM networks_chairs INNER JOIN users ON networks_chairs.user_id=users.user_id WHERE networks_chairs.network_id=' . $this->getNetworkId() . ' AND networks_chairs.enabled=1 AND networks_chairs.deleted=0 AND users.enabled=1 AND users.deleted=0 ORDER BY networks_chairs.is_main_chair DESC, users.lastname, users.firstname ';
//echo $query . ' +<br><br>';
		$result = db_query($query);
		foreach ($result as $record) {
			$this->addNetworkChairEmail( $record->email );
			$this->addNetworkChairName( $record->firstname . ' ' . $record->lastname );
			$this->addNetworkChairId( $record->user_id );
		}

		db_set_active();
	}

	/**
	 * TODOEXPLAIN
	 */
	private function calcNetworkName() {
		db_set_active( getSetting('db_connection') );

		$query = 'SELECT `name` FROM networks WHERE network_id=' . $this->getNetworkId();
		$result = db_query($query);
		foreach ( $result as $record) {
			$this->setNetworkName( $record->name );
		}

		db_set_active();
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getListOfSessions() {
		$arr = array();

		db_set_active( getSetting('db_connection') );

		// order by session_name
		$query = 'SELECT session_in_network.session_id, session_name FROM session_in_network INNER JOIN sessions ON session_in_network.session_id=sessions.session_id WHERE network_id=' . $this->getNetworkId() . ' ORDER BY session_name ';
//echo $query . " ++<br>";
		$result = db_query($query);
		foreach ( $result as $record) {
			$arr[] = $record->session_id;
		}

		db_set_active();

		return $arr;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getListOfNewRegistrationsWithoutSession() {
		$arr = array();

		db_set_active( getSetting('db_connection') );

		// TODOLATER order by naam of type of ...
		$query = "
SELECT * 
FROM papers 
	INNER JOIN participant_date ON papers.user_id = participant_date.user_id 
WHERE ( papers.session_id=0 OR papers.session_id IS NULL ) 
	AND papers.network_proposal_id=" . $this->getNetworkId() . "
	AND participant_date.date_id=" . getSetting('date_id') . " 
	AND participant_date.deleted=0 
	AND participant_date.enabled=1 
	AND participant_state_id=1 
	AND participant_date_id NOT IN ( 
		SELECT participant_date_id 
		FROM participant_date_emails 
		WHERE `type`='newregistrationtochair' 
		AND enabled=1 AND deleted=0 
	) 
";

//echo $query . ' +<br>';

		$result = db_query($query);
		foreach ( $result as $record) {
			$arr[] = $record->user_id;
		}

		// remove duplicate values
		$arr = array_unique($arr);

		db_set_active();

		return $arr;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getListOfAllRegistrationsWithoutSession() {
		$arr = array();

		db_set_active( getSetting('db_connection') );

		// TODOLATER order by naam of type of ...
		$query = "
SELECT * 
FROM papers 
	INNER JOIN participant_date ON papers.user_id = participant_date.user_id 
WHERE ( papers.session_id=0 OR papers.session_id IS NULL ) 
	AND papers.network_proposal_id=" . $this->getNetworkId() . "
	AND participant_date.date_id=" . getSetting('date_id') . " 
	AND participant_date.deleted=0 
	AND participant_date.enabled=1 
	AND participant_state_id IN (0,1,2) 
";

//echo $query . ' +<br>';

		$result = db_query($query);
		foreach ( $result as $record) {
			$arr[] = $record->user_id;
		}

		// remove duplicate values
		$arr = array_unique($arr);

		db_set_active();

		return $arr;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getChairs() {
		$arr = array();

		db_set_active( getSetting('db_connection') );

		$query = 'SELECT networks_chairs.user_id FROM networks_chairs INNER JOIN users ON networks_chairs.user_id=users.user_id WHERE networks_chairs.network_id=' . $this->getNetworkId() . ' AND networks_chairs.enabled=1 AND networks_chairs.deleted=0 AND users.enabled=1 AND users.deleted=0 ORDER BY networks_chairs.is_main_chair DESC, users.lastname, users.firstname ';
//echo $query . ' +<br><br>';
		$result = db_query($query);
		foreach ($result as $record) {
			$p = new class_conference_participantdate( $record->user_id );
			$arr[] = $p;
		}

		db_set_active();

		return $arr;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getSessions($all = false) {
		$arr = array();

		db_set_active( getSetting('db_connection') );

		// order by session_name
		$query = "
SELECT session_in_network.session_id, session_name 
FROM session_in_network 
	INNER JOIN sessions ON session_in_network.session_id=sessions.session_id 
WHERE network_id=" . $this->getNetworkId() . " 
	AND sessions.date_id=" . getSetting('date_id') . " 
ORDER BY session_name 
";
//echo $query . " ++<br>";
		$result = db_query($query);
		foreach ( $result as $record) {
			$s = new class_conference_session( $record->session_id, $all );
			if ( $s->getId() != 0 ) {
				$arr[] = $s;
			}
		}

		db_set_active();

		return $arr;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getPrevNext() {
		$prev = 0;
		$next = 0;
		$found = 0;
		$tmp = 0;

		db_set_active( getSetting('db_connection') );

		// order by session_name
		$query = 'SELECT network_id FROM networks WHERE enabled=1 AND deleted=0 AND show_online=1 AND date_id=' . getSetting('date_id') . ' ORDER BY name ';
//echo $query . " ++<br>";
		$result = db_query($query);
		foreach ( $result as $record) {

//echo $record->network_id . ' +<br>';

			if ( $found == 1 ) {
				$next = $record->network_id;
				break;
			}
			if ( $this->getNetworkId() == $record->network_id ) {
				$prev = $tmp;
				$found = 1;
			}

			$tmp = $record->network_id;

		}

		db_set_active();

		return array($prev, $next);
	}

    /**
     * TODOEXPLAIN
     */
    public function getPrevNextWhereChair( $userId ) {
        $prev = 0;
        $next = 0;
        $found = 0;
        $tmp = 0;

        db_set_active( getSetting('db_connection') );

        // order by session_name
        $query = 'SELECT networks.network_id FROM networks INNER JOIN networks_chairs ON networks.network_id = networks_chairs.network_id WHERE networks.enabled=1 AND networks.deleted=0 AND networks.show_online=1 AND networks.date_id=' . getSetting('date_id') . ' AND networks_chairs.enabled=1 AND networks_chairs.deleted=0 AND user_id =' . $userId . ' ORDER BY name ';

//        echo $query . " ++<br>";

        $result = db_query($query);
        foreach ( $result as $record) {

//echo $record->network_id . ' +<br>';

            if ( $found == 1 ) {
                $next = $record->network_id;
                break;
            }
            if ( $this->getNetworkId() == $record->network_id ) {
                $prev = $tmp;
                $found = 1;
            }

            $tmp = $record->network_id;

        }

        db_set_active();

        return array($prev, $next);
    }

	/**
	 * TODOEXPLAIN
	 */
	private function getVolunteers( $typeOfVolunteer = 0 ) {
		$arr = array();

		db_set_active( getSetting('db_connection') );

		$query = "SELECT users.user_id 
FROM participant_volunteering 
	INNER JOIN participant_date ON participant_volunteering.participant_date_id = participant_date.participant_date_id 
	INNER JOIN users ON participant_date.user_id=users.user_id 
WHERE participant_date.date_id=" . getSetting('date_id') . " 
AND participant_date.enabled=1 AND participant_date.deleted=0 AND users.enabled=1 AND users.deleted=0 
AND participant_volunteering.volunteering_id=" . $typeOfVolunteer . " 
AND participant_volunteering.network_id=" . $this->getNetworkId() . " 
AND participant_date.participant_state_id IN (1,2) 
ORDER BY users.lastname, users.firstname ";

//echo $query . ' +<br><br>';
		$result = db_query($query);
		foreach ($result as $record) {
			$p = new class_conference_participantdate( $record->user_id );
			$arr[] = $p;
		}

		db_set_active();

		return $arr;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getChairVolunteers() {
		// TODOLATER
		return $this->getVolunteers( getSetting('volunteering_chair') );
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getDiscussantVolunteers() {
		// TODOLATER
		return $this->getVolunteers( getSetting('volunteering_discussant') );
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getParticipantsProposedNetwork() {
		$arr = array();

		db_set_active( getSetting('db_connection') );

		$query = "SELECT users.user_id 
FROM papers 
	INNER JOIN participant_date ON papers.user_id = participant_date.user_id
	INNER JOIN users ON participant_date.user_id=users.user_id
WHERE participant_date.date_id=" . getSetting('date_id') . " 
AND papers.date_id=" . getSetting('date_id') . " 
	AND papers.enabled=1 
	AND papers.deleted=0 
	AND participant_date.enabled=1 
	AND participant_date.deleted=0 
	AND users.enabled=1 
	AND users.deleted=0 
	AND papers.network_proposal_id=" . $this->getNetworkId() . " 
	AND participant_date.participant_state_id IN (1,2) 
ORDER BY users.lastname, users.firstname ";

//echo $query . ' +<br><br>';
		$result = db_query($query);
		foreach ($result as $record) {
			$p = new class_conference_participantdate( $record->user_id );
			$arr[] = $p;
		}

		db_set_active();

		return $arr;
	}

    /**
     * TODOEXPLAIN
     */
    public function getListOfAcceptedParticipantsInAcceptedSessions() {
        $arr = array();

        db_set_active( getSetting('db_connection') );

        //
        $query = "
SELECT DISTINCT users.user_id FROM networks
INNER JOIN session_in_network ON networks.network_id = session_in_network.network_id
INNER JOIN sessions ON session_in_network.session_id = sessions.session_id
INNER JOIN session_participant ON sessions.session_id = session_participant.session_id
INNER JOIN participant_date ON session_participant.user_id = participant_date.user_id
INNER JOIN users ON participant_date.user_id = users.user_id
WHERE networks.date_id = " . getSetting('date_id') . " AND networks.network_id = " . $this->getNetworkId() . " AND networks.deleted = 0
AND sessions.date_id = " . getSetting('date_id') . " AND sessions.deleted = 0 AND sessions.session_state_id = 2
AND session_participant.deleted = 0
AND participant_date.deleted = 0 AND participant_date.date_id = " . getSetting('date_id') . " AND participant_date.participant_state_id IN (1,2)
AND users.deleted = 0
ORDER BY users.lastname, users.firstname, users.email
";

//echo $query . ' +<br>';

        $result = db_query($query);
        foreach ( $result as $record) {
            $p = new class_conference_participantdate( $record->user_id );
            $arr[] = $p;
        }

        db_set_active();

        return $arr;
    }
}
