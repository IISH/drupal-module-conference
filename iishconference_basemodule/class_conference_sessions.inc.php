<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_sessions {
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
	public function getSessionIds( $crit = '', $all = false ) {
		$arr = array();

		db_set_active( getSetting('db_connection') );

		$oMisc = new class_conference_misc();
		$crit = $oMisc->protectSearchQuery($crit);

		if ( $crit != '' ) {
			$crit = ' AND ' . $oMisc->explodeSqlQuery('session_name', $crit, "'", ' AND ');
		}

		if ( !$all ) {
			$all = ' AND enabled=1 AND deleted=0 ';
		} else {
			$all = '';
		}

		// order by session_name
		$query = 'SELECT session_id FROM sessions WHERE date_id=' . $this->getDateId() . $all . $crit . ' ORDER BY session_name ASC, deleted ASC ';
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
	public function getSessionObjects( $crit = '', $all = false) {
		$arr = array();

		db_set_active( getSetting('db_connection') );

		$oMisc = new class_conference_misc();
		$crit = $oMisc->protectSearchQuery($crit);

		if ( $crit != '' ) {
			$crit = ' AND ' . $oMisc->explodeSqlQuery('session_name', $crit, "'", ' AND ');
		}

		if ( !$all ) {
			$all = ' AND enabled=1 AND deleted=0 ';
		} else {
			$all = '';
		}

		// order by session_name
		$query = 'SELECT session_id FROM sessions WHERE date_id=' . $this->getDateId() . $all . $crit . ' ORDER BY session_name ASC, deleted ASC ';
//echo $query . " ++<br>";
		$result = db_query($query);
		foreach ( $result as $record) {
			$oSession = new class_conference_session($record->session_id, true);
			$arr[] = $oSession;
		}

		db_set_active();

		return $arr;
	}
}