<?php
// enable error reporting in page
error_reporting(-1);
$conf['error_level'] = 2;
ini_set('display_errors', true);
ini_set('display_startup_errors', true);

// + + + + + + + + + + + + + + + + + + + + + + + +

/**
 * TODOEXPLAIN
 */
function getRemoteAddress() {
	$ret = trim(isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : '');
	if ($ret == '') {
		$ret = trim(isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : '');
	}

	return $ret;
}

/**
 * TODOEXPLAIN
 */
function isRefererCorrect() {
	$ret = true;

	$current = $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	$referer = $_SERVER["HTTP_REFERER"];

	$current = strtolower($current);
	$referer = strtolower($referer);

	$remove = array('http://', 'https://', '-send', 'backend1.', 'backend2.');

	$current = str_replace($remove, '', $current);
	$referer = str_replace($remove, '', $referer);

	if ($current != $referer) {
		$ret = false;
	}

	return $ret;
}

/**
 * TODOEXPLAIN
 */
function protectBackUrl($url) {
	$url = str_ireplace(array('http://', 'https://', 'ftp://', 'ftps://', '<script'), ' ', $url);
	$url = trim($url);
	$url = get_left_part($url, ' ');

	return $url;
}

/**
 * TODOEXPLAIN
 */
function showLiveTestEnvironment(&$form, $ct) {
	$form['ct' . $ct] = array(
		'#type' => 'markup',
		'#markup' => '<strong>&gt; ' . ((getSetting('live') == 1) ? "LIVE" : "TEST") . ' environment</strong><br><br>',
	);
}

/**
 * TODOEXPLAIN
 */
// TODOTODO moet class van gemaakt worden
function getSetting($field) {
	require 'settings.default.php';
	require 'settings.php';

	$value = '';
	if (isset($_SESSION["conf_setting_" . $field]) && $_SESSION["conf_setting_" . $field] != '') {
		$value = $_SESSION["conf_setting_" . $field];
	}
	elseif (isset ($conference_settings[$field])) {
		$value = $conference_settings[$field];
	}

	return $value;
}

/**
 * TODOEXPLAIN
 */
function saveEmailInDatabase($subject, $body, $user_id, $date_id, $set_as_sent = false) {
	db_set_active(getSetting('db_connection'));

//	if ( strlen($body) > 60000 ) {
//		$body = substr($body, 0, 60000);
//	}
//echo strlen(addslashes($body)) . "+<br>";

	$query =
		"INSERT INTO sent_emails (user_id, date_id, from_name, from_email, subject, body, date_time_sent, num_tries) VALUES ($user_id, $date_id, '" .
		addslashes(getSetting('email_fromname')) . "', '" . addslashes(SettingsApi::getSetting(SettingsApi::DEFAULT_ORGANISATION_EMAIL)) . "', '" .
		addslashes($subject) . "', '" . addslashes($body) . "', '::DATE::', ::NUMOFTRIES::) ";

	if ($set_as_sent) {
		$query = str_replace('::DATE::', date("Y-m-d H:i:s"), $query);
		$query = str_replace('::NUMOFTRIES::', 1, $query);
	}
	else {
		$query = str_replace('::DATE::', '', $query);
		$query = str_replace('::NUMOFTRIES::', 0, $query);
	}

	$result = db_query($query);

	db_set_active();
}

/**
 * TODOEXPLAIN
 */
function sendEmail($to, $subject, $body, $bcc = '') {
	$headers = "From: " . SettingsApi::getSetting(SettingsApi::DEFAULT_ORGANISATION_EMAIL) . "\r\nReply-To: " .
		SettingsApi::getSetting(SettingsApi::DEFAULT_ORGANISATION_EMAIL) . "\r\nReturn-Path: gcu@iisg.nl";

	mail($to, $subject, $body, $headers);

	if ($bcc != '') {
		$bcc = str_replace(array(' ', ':', ';'), ',', $bcc);
		$arrBcc = explode(',', $bcc);
		foreach ($arrBcc as $bccMail) {
			// 
			mail($bccMail, 'BCC: ' . $subject . ' (' . $to . ')', $body, $headers);
		}
	}
}

/**
 * TODOEXPLAIN
 */
function sendDebugInfo() {
	$oUser = new class_conference_user(getIdLoggedInUser());

	$to = getSetting('bcc_debug');

	if ($to != '') {
		// create subject/body
		$timestamp = date("Y-m-d H:i:s");
		$subject = "CONFERENCE DEBUG INFO: " . getRemoteAddress() . ' ' . $timestamp . ' ' .
			$_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		$body = $subject . " \n";
		$body .= 'DATE: ' . $timestamp . " \n";
		$body .= 'Remote address: ' . getRemoteAddress() . " \n \n";

		$body .= 'user id: ' . getIdLoggedInUser() . " \n";

		if (isset($_SESSION["conference"]["user_email"])) {
			$body .= 'user email: ' . trim($_SESSION["conference"]["user_email"]) . " \n";
		}
		else {
			$body .= 'user email: -' . " \n";
		}

		//
		if (isset($_SESSION)) {
			$body .= " \n \n";
			$body .= '$_SESSION' . " \r\n";
			foreach ($_SESSION as $a => $b) {
				$body .= convert2String($a, $b);
			}
		}

//dbug( $to . ' - ' . $subject);
		// send mail
		mail($to, $subject, $body);
	}
}

/**
 * TODOEXPLAIN
 */
function convert2String($name, $arr) {
	$ret = '';

	if (is_array($arr)) {
		foreach ($arr as $a => $b) {
			if (is_array($b)) {
				$ret .= $name . '|' . $a . ": ";
				if (count($b) > 0) {
					$ret .= "Array \n";
					$ret .= convert2String($name . '|' . $a, $b);
				}
				else {
					$ret .= "Array( empty ) \n";
				}
			}
			else {
				$ret .= $name . '|' . $a . ": " . $b . " \n";
			}
		}

	}
	else {
		$ret = $name . ':' . $arr . " \n";
	}

	return $ret;
}

/**
 * TODOEXPLAIN
 */
//function eval_expression($expression) {
//	eval("\$temp = $expression;");
//	return $temp;
//}

/**
 * TODOEXPLAIN
 */
function executeQueryGetListOfValues($query, $field) {
	$arr = '';
	$separator = '';

	db_set_active(getSetting('db_connection'));

	$result = db_query($query);

	foreach ($result as $record) {
		$arr .= $separator . $record->$field;
		$separator = ', ';
	}

	db_set_active();

	return $arr;
}

/**
 * TODOEXPLAIN
 */
function getArrayOfNetworks($extracriterium = '') {
	$arr = array();
	$date_id = getSetting('date_id');

	db_set_active(getSetting('db_connection'));

	$result = db_query('SELECT * FROM networks WHERE date_id=' . $date_id .
	' AND enabled=1 AND deleted=0 AND show_online=1 ' . $extracriterium . ' ORDER BY name ');

	foreach ($result as $record) {
		$arr["" . $record->network_id] = $record->name;
	}

	db_set_active();

	return $arr;
}

/**
 * TODOEXPLAIN
 */
function getSessionDetailsAsArray($sessionid, $field) {
	$ret = '';

	db_set_active(getSetting('db_connection'));

	$result = db_select('sessions', 'n')
		->fields('n')
		->condition('session_id', $sessionid, '=')
		->execute()
		->fetchAssoc();

	if ($result) {

		if (is_array($field)) {
			foreach ($field as $a) {
				$ret[$a] = trim($result[$a]);
			}
		}
		else {
			$ret[$field] = trim($result[$field]);
		}

	}

	db_set_active();

	return $ret;
}

/**
 * TODOEXPLAIN
 */
function getDetailsAsArray($query, $returnfield) {
	$ret = array();

	db_set_active(getSetting('db_connection'));

	$result = db_query($query);

	if ($result) {

		foreach ($result as $row) {
			if (is_array($returnfield)) {
				foreach ($returnfield as $a) {
					$ret[$a] = trim($row->$a);
				}
			}
			else {
				$ret[$returnfield] = trim($row->$returnfield);
			}

		}
	}

	db_set_active();

	return $ret;
}

/**
 * TODOEXPLAIN
 */
// TODOTODO functie vervangen door...
function executeQueryReturnFields($query, $field) {
	$ret = '';

	db_set_active(getSetting('db_connection'));

	$result = db_query($query);

	foreach ($result as $record) {
		if (is_array($field)) {
			$ret = array();
			foreach ($field as $a) {
				$ret[$a] = trim($record->$a);
			}
		}
		else {
			$ret = trim($record->$field);
		}
	}

	db_set_active();

	return $ret;
}

/**
 * TODOEXPLAIN
 */
function dbug($text) {
	$oUser = new class_conference_user(getIdLoggedInUser());

	if ($oUser->isSuperAdmin()) {
		echo '<br>Debug info: +' . $text . '+<br>';
	}
}

/**
 * TODOEXPLAIN
 */
function getParticipantFunction($userid, $sessionid) {
	$ret = '';

	db_set_active(getSetting('db_connection'));

	$query = "SELECT participant_types.type FROM users INNER JOIN participant_date ON users.user_id=participant_date.user_id 
	INNER JOIN session_participant ON users.user_id=session_participant.user_id 
	INNER JOIN participant_types ON session_participant.participant_type_id=participant_types.participant_type_id
	WHERE users.user_id=" . $userid . " AND participant_date.date_id=" . getSetting('date_id') . " 
	AND session_participant.session_id=" . $sessionid . " 
	";

	$result = db_query($query);

	//
	$separator = '';
	foreach ($result as $row) {
		$ret .= $separator . $row->type;
		$separator = ', ';
	}

	db_set_active();

	return $ret;
}

/**
 * TODOEXPLAIN
 */
function loadNetworksInArray($query, $field, $autoIndex = 0) {
	$settings = array();

	db_set_active(getSetting('db_connection'));

	$result = db_query($query);

	foreach ($result as $record) {
		if ($autoIndex == 1) {
			$settings[] = $record->$field;
		}
		else {
			$settings["" . $record->$field] = $record->$field;
		}
	}

	db_set_active();

	return $settings;
}

/**
 * TODOEXPLAIN
 */
function getNetworkChairsTotals() {
	$ret = array();

	db_set_active(getSetting('db_connection'));

	$result =
		db_query('SELECT network_id, count(*) as chairstotal FROM networks_chairs WHERE enabled=1 AND deleted=0 GROUP BY network_id ');

	foreach ($result as $record) {
		$ret["" . $record->network_id] = $record->chairstotal;
	}

	db_set_active();

	return $ret;
}

/**
 * TODOEXPLAIN
 */
function getArrayOfSessionsAddedByParticipant($user_id) {
	$arr = array();

	$arr[0] = 'Add a new session';

	db_set_active(getSetting('db_connection'));

	$result = db_query('SELECT session_id, session_name FROM sessions WHERE enabled=1 AND deleted=0 AND added_by=' .
	$user_id . ' AND date_id=' . getSetting('date_id') . ' ORDER BY session_name');

	foreach ($result as $record) {
		$arr[$record->session_id] = $record->session_name;
	}

	db_set_active();

	return $arr;
}

/**
 * TODOEXPLAIN
 */
function loadSessionData($sessionId) {

	$_SESSION['storage']['preregistersession_sessionname'] = '';
	$_SESSION['storage']['preregistersession_sessionabstract'] = '';
	$_SESSION['storage']['preregistersession_sessioninnetwork'] = 0;

	db_set_active(getSetting('db_connection'));

	$result = db_query('SELECT session_name, session_abstract FROM sessions WHERE session_id=' . $sessionId);

	foreach ($result as $record) {
		$_SESSION['storage']['preregistersession_sessionname'] = $record->session_name;
		$_SESSION['storage']['preregistersession_sessionabstract'] = $record->session_abstract;
	}

	$network_id = 0;
	$result = db_query('SELECT network_id FROM session_in_network WHERE session_id=' . $sessionId);
	foreach ($result as $record) {
		$network_id = $record->network_id;
	}
	$_SESSION['storage']['preregistersession_sessioninnetwork'] = $network_id;

	db_set_active();
}

/**
 * TODOEXPLAIN
 */
function saveSessionData($sessionId) {
	db_set_active(getSetting('db_connection'));
	$is_new_session = 0;

	// save session
	if ($sessionId == 0) {
		// INSERT
		$query =
			"INSERT INTO sessions(session_name, session_abstract, date_id, added_by) VALUES('::NAME::', '::ABSTRACT::', ::DATEID::, ::ADDEDBY::) ";
		$is_new_session = 1;
	}
	else {
		// UPDATE
		$query =
			"UPDATE sessions SET session_name='::NAME::', session_abstract='::ABSTRACT::' WHERE session_id=::ID:: ";
	}
	$query = str_replace('::ID::', $sessionId, $query);
	$query = str_replace('::DATEID::', getSetting('date_id'), $query);
	$query = str_replace('::NAME::', addslashes(trim($_SESSION['storage']['preregistersession_sessionname'])), $query);
	$query =
		str_replace('::ABSTRACT::', addslashes($_SESSION['storage']['preregistersession_sessionabstract']), $query);
	$query = str_replace('::ADDEDBY::', getIdLoggedInUser(), $query);

	$result = db_query($query);

	// if 0 get new session_id
	if ($sessionId == 0) {
		$sessionId = getSessionIdBySessionName($_SESSION['storage']['preregistersession_sessionname']);
	}
	$_SESSION['storage']['preregistersession_sessionid'] = $sessionId;

	// 
	db_set_active();

	// save network
	addSessionInNetwork($sessionId, $_SESSION['storage']['preregistersession_sessioninnetwork']);

	if ($is_new_session == 1) {
		addOrganizerToSession($sessionId, getIdLoggedInUser());
	}
}

/**
 * TODOEXPLAIN
 */
function getSessionIdBySessionName($sessionname) {
	$ret = 0;

	db_set_active(getSetting('db_connection'));

	$result = db_query("SELECT session_id FROM sessions WHERE session_name='" . addslashes($sessionname) . "' ");

	foreach ($result as $record) {
		$ret = $record->session_id;
	}

	db_set_active();

	return $ret;
}

/**
 * TODOEXPLAIN
 */
function addSessionInNetwork($session, $network) {
	db_set_active(getSetting('db_connection'));

	$count = 0;
	$result = db_query('SELECT * FROM session_in_network WHERE session_id=' . $session);

	foreach ($result as $record) {
		$count++;
	}

	if ($count == 0) {
		// INSERT
		$result =
			db_query('INSERT INTO session_in_network (network_id, session_id, added_by) VALUES (' . $network . ', ' .
			$session . ', ' . getIdLoggedInUser() . ') ');
	}
	elseif ($count == 1) {
		// UPDATE
		$result =
			db_query('UPDATE session_in_network SET network_id=' . $network . ', added_by=' . getIdLoggedInUser() .
			' WHERE session_id=' . $session);
	}
	else {
		// TODOLATER: SESSION IN MEERDERE NETWERKEN, WAT NU???
		// is het mogelijk dat een sessie in meerdere netwerken zit? ook in registratie pagina's?
		// maken als in db meerdere waardes, maak dan veld multiple
		// maar dan moet deze hele functie aangepast worden met multiple functionaliteit
	}

	db_set_active();
}

/**
 * TODOEXPLAIN
 */
function countNrOfSessionsForOrganizer($user_id) {
	$ret = 0;

	db_set_active(getSetting('db_connection'));

	$result = db_query('SELECT count(*) AS aantal FROM sessions WHERE added_by=' . $user_id . ' AND date_id=' .
	getSetting('date_id') . ' GROUP BY added_by');

	foreach ($result as $record) {
		$ret = $record->aantal;
	}

	db_set_active();

	return $ret;
}

/**
 * TODOEXPLAIN
 */
function getArrayOfSessionParticipants($session_id) {
	$arr = array();

	$arr[0] = 'Add a new participant';

	db_set_active(getSetting('db_connection'));

	$result =
		db_query('SELECT user_id FROM session_participant WHERE enabled=1 AND deleted=0 AND session_id=' . $session_id);

	foreach ($result as $record) {
		$participantDetails =
			getDetailsAsArray('SELECT * FROM users WHERE user_id=' . $record->user_id, array("firstname", "lastname"));
		$name = $participantDetails['firstname'] . ' ' . $participantDetails['lastname'];
		$type =
			' (' . getParticipantFunction($record->user_id, $_SESSION['storage']['preregistersession_sessionid']) . ')';

		$arr[$record->user_id] = $name . $type;
	}

	db_set_active();

	return $arr;
}

/**
 * TODOEXPLAIN
 */
function getArrayOfParticipantTypes($event_id) {
	$arr = array();

	db_set_active(getSetting('db_connection'));

	$result =
		db_query('SELECT * FROM participant_types WHERE ( event_id IS NULL ) AND enabled=1 AND deleted=0 ORDER BY importance DESC ');

	foreach ($result as $record) {
		$arr[$record->participant_type_id] = $record->type;
	}

	db_set_active();

	return $arr;
}

/**
 * TODOEXPLAIN
 */
// controleer of email adres niet al bestaat (uitgezonderd huidige record)
function checkIfEmailAlreadyExists($email, $recordnr) {
	$ret = 0;

	db_set_active(getSetting('db_connection'));

	$result = db_query('SELECT * FROM users WHERE email=\'' . addslashes($email) . '\' AND user_id<>' . $recordnr);

	foreach ($result as $record) {
		$ret = $record->user_id;
	}

	db_set_active();

	return $ret;
}

/**
 * TODOEXPLAIN
 */
function findUserIdByEmailAddress($email) {
	$ret = 0;

	db_set_active(getSetting('db_connection'));

	$result = db_query('SELECT user_id FROM users WHERE email=\'' . addslashes($email) . '\' ORDER BY user_id DESC ');

	foreach ($result as $record) {
		$ret = $record->user_id;
	}

	db_set_active();

	return $ret;
}

/**
 * TODOEXPLAIN
 */
function byWhomIsTheUserRecordAdded($id) {
	$ret = 0;

	db_set_active(getSetting('db_connection'));

	$result = db_query('SELECT added_by FROM users WHERE user_id=' . $id);

	foreach ($result as $record) {
		$ret = $record->added_by;
	}

	db_set_active();

	return $ret;
}

/**
 * TODOEXPLAIN
 */
function doesRecordExist($query) {
	$ret = 0;

	db_set_active(getSetting('db_connection'));

	$result = db_query($query);

	foreach ($result as $record) {
		return 1;
	}

	db_set_active();

	return $ret;
}

/**
 * TODOEXPLAIN
 */
function addOrganizerToSession($sessionId, $userid) {
	// controleer of persoon al als organizer in de sessie zit
	$query = 'SELECT * FROM session_participant WHERE user_id=' . $userid . ' AND session_id=' . $sessionId .
		' AND participant_type_id=' . getSetting('organizer_id');
	if (!doesRecordExist($query)) {
		// participant is nog geen organizer in sessie

		$query =
			"INSERT INTO session_participant (user_id, session_id, added_by, participant_type_id) VALUES (::USERID::, ::SESSIONID::, ::ADDEDBY::, ::TYPE::) ";
		$query = str_replace('::USERID::', $userid, $query);
		$query = str_replace('::SESSIONID::', $sessionId, $query);
		$query = str_replace('::ADDEDBY::', $userid, $query);
		$query = str_replace('::TYPE::', getSetting('organizer_id'), $query);

		db_set_active(getSetting('db_connection'));
		$result = db_query($query);
		db_set_active();

		drupal_set_message('You are added as organizer to this session.<br>Please add participants to the session.',
			'status');
	}

}

/**
 * TODOEXPLAIN
 */
function isPaperRequired($types) {
	$ret = 0;

	if (is_array($types)) {
		if (count($types) > 0) {
			foreach ($types as $type) {
				$query = 'SELECT * FROM participant_types WHERE participant_type_id=' .
					$type . ' AND with_paper=1 AND enabled=1 AND deleted=0 ';
				if (checkRecordExists($query)) {
					$ret = 1;
				}
			}
		}
	}

	return $ret;
}

/**
 * TODOEXPLAIN
 */
function isAllowedCombination($types) {
	$ret = 1;

	db_set_active(getSetting('db_connection'));

	if (is_array($types)) {
		if (count($types) > 0) {
			foreach ($types as $type) {

				$result = db_query('SELECT * FROM participant_type_rules WHERE participant_type_1_id=' . $type .
				' AND enabled=1 AND deleted=0 ');
				foreach ($result as $record) {
					if (in_array($record->participant_type_2_id, $types)) {
						$ret = 0;
					}
				}

				$result = db_query('SELECT * FROM participant_type_rules WHERE participant_type_2_id=' . $type .
				' AND enabled=1 AND deleted=0 ');
				foreach ($result as $record) {
					if (in_array($record->participant_type_1_id, $types)) {
						$ret = 0;
					}
				}

			}
		}
	}

	db_set_active();

	return $ret;
}

/**
 * TODOEXPLAIN
 */
function countRecords($query) {
	$ret = 0;

	db_set_active(getSetting('db_connection'));

	$result = db_query($query);

	foreach ($result as $record) {
		$ret += 1;
	}

	db_set_active();

	return $ret;
}

/**
 * TODOEXPLAIN
 */
function getOrganizerDataOverview($user_id) {
	$ret = '';
	$nrOfSessions = 0;

	$query = "SELECT * FROM sessions WHERE date_id=" . getSetting('date_id') . ' AND added_by=' .
		$user_id . ' AND enabled=1 AND deleted=0 ';

	db_set_active(getSetting('db_connection'));

	$result = db_query($query);
	foreach ($result as $record) {
		$ret .= "SESSION: " . $record->session_name . " \n";
		$ret .= "Abstract: " . $record->session_abstract . " \n";
		if (SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) {
			$ret .= "Network(s): " . implode(', ',
					loadNetworksInArray("SELECT networks.name FROM session_in_network INNER JOIN networks ON session_in_network.network_id=networks.network_id WHERE session_in_network.session_id=" .
					$record->session_id . " GROUP BY networks.name ORDER BY networks.name ", "name")) . " \n \n";
		}
		$ret .= "Participants: \n";

		$ret .= getSessionParticipants($record->session_id);

		$ret .= " \n \n";
		$nrOfSessions++;
	}

	db_set_active();

	if ($nrOfSessions > 0) {
		$ret = "You are the organizer of the following session" . ($nrOfSessions > 1 ? 's' : '') . ": \n \n" .
			$ret . " \n";
	}

	return $ret;
}

/**
 * TODOEXPLAIN
 */
function getSessionParticipants($sessionid) {
	$ret = '';

	$query =
		"SELECT DISTINCT users.user_id, users.firstname, users.email, users.lastname FROM session_participant INNER JOIN users ON session_participant.user_id=users.user_id WHERE session_participant.session_id=" .
		$sessionid . ' ORDER BY users.lastname, users.firstname ';
//dbug($query);
	db_set_active(getSetting('db_connection'));

	$result = db_query($query);
	foreach ($result as $record) {
		$ret .= " \nname: ";
		$ret .= $record->firstname . ' ' . $record->lastname;

		// type
		$ret .= " (";
		$separator = '';
		foreach (getParticipantTypesAsArray2($record->user_id, $sessionid) as $a) {
			$ret .= $separator . $a;
			$separator = ', ';
		}
		$ret .= ") \n";
		$ret .= "e-mail: " . $record->email . " \n";

		// student?
		$query3 = "SELECT * FROM participant_date WHERE date_id=" . getSetting('date_id') . ' AND user_id=' .
			$record->user_id . ' AND student=1 ';
//dbug($query3);
		if (doesRecordExist($query3)) {
			$ret .= "student: yes \n";
		}
		else {
			$ret .= "student: no \n";
		}

		$query2 =
			"SELECT * FROM session_participant WHERE session_id=" . $sessionid . ' AND user_id=' . $record->user_id .
			' AND participant_type_id IN (' . getSetting('author_id') . ', ' . getSetting('coauthor_id') . ') ';
//dbug($query2);
		if (doesRecordExist($query2)) {
			$paper = getDetailsAsArray('SELECT * FROM papers WHERE user_id=' . $record->user_id . ' AND session_id=' .
			$sessionid, array('title', 'abstract'));
			// paper
			$ret .= "title: " . $paper["title"] . " \n";
			$ret .= "abstract: " . $paper["abstract"] . " \n";
		}
	}

	db_set_active();

	return $ret;
}

/**
 * TODOEXPLAIN
 */
function sendConfirmationEmail($user_id, $subject, $body) {
	$dateid = getSetting('date_id');

	$query = "SELECT participant_date_id FROM participant_date WHERE user_id=" . $user_id . " AND date_id=" . $dateid;
	$participant_date_id = executeQueryReturnFields($query, "participant_date_id");
	$participant_date_id = ifEmpty($participant_date_id, 0);

	$queryParticipant =
		"SELECT lastname, firstname, gender, city, country_id, organisation, department, student, mobile, phone FROM users INNER JOIN participant_date ON users.user_id=participant_date.user_id AND users.user_id=" .
		$user_id . ' AND participant_date.date_id=' . getSetting('date_id');
//dbug($queryParticipant);
	$participant = executeQueryReturnFields($queryParticipant,
		array('firstname', 'lastname', 'gender', 'city', 'country_id', 'organisation', 'department', 'student',
			'mobile', 'phone'));

	// create mail
	$to = getUserDetails($user_id, 'email');

	$body = str_replace('[EmailParticipant]', $to, $body);

	// TODOLATER alles SESSION vervangen door db waardes !!!!!!
	$naam = trim($participant["firstname"] . ' ' . $participant["lastname"]);
	$body = str_replace('[NameParticipant]', $naam, $body);

	$naw = "First name: " . $participant["firstname"] . " \n";
	$naw .= "Last name: " . $participant["lastname"] . " \n";
	$gender_options = getArrayOfGender();
	$naw .= "Gender: " . $gender_options[$participant["gender"]] . " \n";
	$naw .= "Organisation: " . $participant["organisation"] . " \n";
	$naw .= "Department: " . $participant["department"] . " \n";

	$naw .= "(PhD) Student?: ";
	$naw .= ($participant["student"] == 1) ? 'yes' : 'no';
	$naw .= " \n";

	$naw .= "E-mail: " . $to . " \n";
	$naw .= "City: " . $participant["city"] . " \n";
	$list_of_countries = getArrayOfCountries();
	$naw .= "Country: " . $list_of_countries[$participant["country_id"]] . " \n";
	$naw .= "Phone number: " . $participant["phone"] . " \n";
	$naw .= "Mobile number: " . $participant["mobile"];
	$body = str_replace('[ParticipantData]', $naw, $body);

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

	// SESSIONINFO
	if (strpos($body, '[SessionInfo]') !== false) {
		$sessioninfo = '';

		$organizername = $_SESSION['storage']['preregister_personalinfo_firstname'] .
			' ' . $_SESSION['storage']['preregister_personalinfo_lastname'];
		$organizeremail = trim($_SESSION["conference"]["user_email"]);
		if (trim($organizername) == trim($participant["firstname"] . ' ' . $participant["lastname"])) {
			// dirty
			$body = str_replace(' by [OrganizerName]', '', $body);
		}
		else {
			$body = str_replace('[OrganizerName]', $organizername, $body);
		}

		$sessioninfo .= "ORGANIZER \n";
		$sessioninfo .= 'name: ' . $organizername . " \n";
		$sessioninfo .= 'e-mail: ' . $organizeremail . " \n";
		$sessioninfo .= " \n";

		$querySession =
			"SELECT DISTINCT sessions.session_id, session_name, session_abstract FROM sessions INNER JOIN session_participant ON sessions.session_id=session_participant.session_id WHERE sessions.added_by=" .
			getIdLoggedInUser() . ' AND session_participant.user_id=' . $user_id;
//dbug($querySession);
		db_set_active(getSetting('db_connection'));

		$sessionnames = '';
		$sessionnames_separator = '';

		$result = db_query($querySession);
		foreach ($result as $record) {

			$sessioninfo .= 'SESSION' . " \n";
			$sessioninfo .= 'name: ' . $record->session_name . " \n";
			$sessioninfo .= 'abstract: ' . $record->session_abstract . " \n";
			$sessioninfo .= "participant type: " . getParticipantFunction($user_id, $record->session_id) . " \n";

			$query2 = "SELECT * FROM session_participant WHERE session_id=" . $record->session_id . ' AND user_id=' .
				$user_id . ' AND participant_type_id IN (' . getSetting('author_id') . ', ' .
				getSetting('coauthor_id') . ') ';
//dbug($query2);
			if (doesRecordExist($query2)) {
				$paper = getDetailsAsArray('SELECT * FROM papers WHERE user_id=' . $user_id . ' AND session_id=' .
				$record->session_id, array('title', 'abstract'));
				// paper
				$sessioninfo .= "Your paper \n";
				$sessioninfo .= "title: " . $paper["title"] . " \n";
				$sessioninfo .= "abstract: " . $paper["abstract"];
			}

			$sessioninfo .= " \n";
			$sessioninfo .= " \n";

			$sessionnames .= $sessionnames_separator . $record->session_name;
			$sessionnames_separator = ', ';
		}
		db_set_active();

		$body = str_replace('[SessionName]', $sessionnames, $body);

		$body = str_replace('[SessionInfo]', $sessioninfo, $body);
	}

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

	// PAPER
	if (strpos($body, '[ParticipantPaper]') !== false) {
		$paper = '';
		if ($_SESSION['storage']['what'] == 'paper') {
			$paper .= "Paper title: " . $_SESSION['storage']['preregister_registerpaper_papertitle'] . " \n";
			$paper .= "Abstract:\n" . $_SESSION['storage']['preregister_registerpaper_paperabstract'] . " \n";
			$paper .= "Co-author(s): " . $_SESSION['storage']['preregister_registerpaper_coauthors'] . " \n";

			$list_of_networks = getArrayOfNetworks();
			if (SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) {
				$paper .= "Proposed network: " .
					$list_of_networks[$_SESSION['storage']['preregister_registerpaper_proposednetwork']] . " \n";
			}

			$paper .= "Is this part of an existing session? ";
			$paper .= ((isset($_SESSION['storage']['preregister_registerpaper_partofexistingsession']['y']) &&
				$_SESSION['storage']['preregister_registerpaper_partofexistingsession']['y'] === 'y') ? 'yes' : 'no');
			$paper .= " \n";
			if (isset($_SESSION['storage']['preregister_registerpaper_partofexistingsession']['y']) &&
				$_SESSION['storage']['preregister_registerpaper_partofexistingsession']['y'] === 'y'
			) {
				$paper .=
					"Proposed session: " . $_SESSION['storage']['preregister_registerpaper_proposedsession'] . " \n";
			}

			if (SettingsApi::getSetting(SettingsApi::SHOW_AWARD) == 1) {
				if (isset($_SESSION['storage']['preregister_personalinfo_student']['y']) &&
					$_SESSION['storage']['preregister_personalinfo_student']['y'] === 'y'
				) {
					$paper .= "Prof. Jan Lucassen award?: ";
					$paper .= (isset($_SESSION['storage']['preregister_registerpaper_award']['y']) &&
						$_SESSION['storage']['preregister_registerpaper_award']['y'] === 'y') ? 'yes' : 'no';
					$paper .= " \n";
				}
			}
			$paper .= "Audio/visual equipment\n";
			$paper .= "Beamer? ";
			$paper .= ((isset($_SESSION['storage']['preregister_registerpaper_audiovisual']["beamer"]) &&
				$_SESSION['storage']['preregister_registerpaper_audiovisual']["beamer"] === 'beamer') ? 'yes' : 'no');
			$paper .= " \n";
			$paper .= "Extra audio/visual request: ";
			$paper .= (isset($_SESSION['storage']['preregister_registerpaper_extraaudiovisual']) ?
				$_SESSION['storage']['preregister_registerpaper_extraaudiovisual'] : '');
			$paper .= " \n";
		}
		else {
			$paper = 'No paper';
		}
		$body = str_replace('[ParticipantPaper]', $paper, $body);
	}

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

	// POOL
	if (strpos($body, '[ParticipantPool]') !== false) {
		$pool = "I would like to volunteer as Chair: ";
		$pool .= (isset($_SESSION['storage']['preregister_chairdiscussantpool_volunteerchair']['y']) &&
			$_SESSION['storage']['preregister_chairdiscussantpool_volunteerchair']['y'] === 'y') ? 'yes' : 'no';
		$pool .= " \n";
		if (SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) {
			if (isset($_SESSION['storage']['preregister_chairdiscussantpool_volunteerchair']['y']) &&
				$_SESSION['storage']['preregister_chairdiscussantpool_volunteerchair']['y'] === 'y'
			) {
				$pool .= 'Network(s): ' .
					getStringOfNetworks($_SESSION['storage']['preregister_chairdiscussantpool_volunteerchair_networks']) . " \n";
			}
		}
		$pool .= "I would like to volunteer as Discussant: ";
		$pool .= (isset($_SESSION['storage']['preregister_chairdiscussantpool_volunteerdiscussant']['y']) &&
			$_SESSION['storage']['preregister_chairdiscussantpool_volunteerdiscussant']['y'] === 'y') ? 'yes' : 'no';
		$pool .= " \n";
		if (SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) {
			if (isset($_SESSION['storage']['preregister_chairdiscussantpool_volunteerdiscussant']['y']) &&
				$_SESSION['storage']['preregister_chairdiscussantpool_volunteerdiscussant']['y'] === 'y'
			) {
				$pool .= 'Network(s): ' . getStringOfNetworks($_SESSION['storage']['preregister_chairdiscussantpool_volunteerdiscussant_networks']);
			}
		}
		$body = str_replace('[ParticipantPool]', $pool, $body);
	}

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

	if (SettingsApi::getSetting(SettingsApi::SHOW_LANGUAGE_COACH_PUPIL) == 1) {
		// LANGUAGE
		if (strpos($body, '[ParticipantLanguage]') !== false) {
			$arrLanguageCoaches = getArrayOfLanguageCoach();
			$chosenLangeuageCoach = $_SESSION['storage']['preregister_chairdiscussantpool_coachpupil'];
			$language = str_replace('<br>', '', $arrLanguageCoaches[$chosenLangeuageCoach]);
			if ($chosenLangeuageCoach == 0 || $chosenLangeuageCoach == 1) {
				$language .= 'Network(s): ' . getStringOfNetworks($_SESSION['storage']['preregister_chairdiscussantpool_coachpupil_networks']);
			}
			$body = str_replace('[ParticipantLanguage]', $language, $body);
			$body = str_replace('[ParticipantLanguageLabel]', 'English Language Coach:', $body);
		}
	}
	$body = str_replace("[ParticipantLanguage]", '', $body);
	$body = str_replace("[ParticipantLanguageLabel]", '', $body);

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

	// ORGANIZER
	if (strpos($body, '[ParticipantOrganizer]') !== false) {
		$organizerdata = getOrganizerDataOverview($user_id);
		$body = str_replace('[ParticipantOrganizer]', $organizerdata, $body);
	}

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

	$body = str_replace('[SenderName]', getSetting('with_kind_regards_name'), $body);

	// mail
	saveEmailInDatabase($subject, $body, $user_id, $dateid, true);
	sendEmail($to, $subject, $body, getSetting('bcc_debug') . ',' . getSetting('bcc_registration'));
}

/**
 * TODOEXPLAIN
 */
function sendConfirmationEmailToSessionParticipants() {
	$query =
		"SELECT sessions.session_id, session_participant.user_id, users.password FROM sessions INNER JOIN session_participant ON sessions.session_id=session_participant.session_id INNER JOIN users ON session_participant.user_id=users.user_id WHERE sessions.date_id=" .
		getSetting('date_id') . ' AND sessions.added_by=' . getIdLoggedInUser() .
		' AND session_participant.added_by=' . getIdLoggedInUser();

	db_set_active(getSetting('db_connection'));

	$result = db_query($query);
	foreach ($result as $record) {

		if ($record->password == '') {
			// NEW REGISTRATION
			$oEmail = new class_conference_email(getSetting('email_template_session_registration_new'));

			sendConfirmationEmail($record->user_id, $oEmail->getSubject(), $oEmail->getBody());

			// SEND PASSWORD
			createAndMailPassword($record->user_id, 1);
		}
		else {
			// EXISTING REGISTRATION
			$oEmail = new class_conference_email(getSetting('email_template_session_registration_existing'));

			sendConfirmationEmail($record->user_id, $oEmail->getSubject(), $oEmail->getBody());
		}

	}

	db_set_active();
}

/**
 * TODOEXPLAIN
 */
function loadData($user_id, &$form_state) {

	$date_id = getSetting('date_id');

	$query = "SELECT participant_date_id, student, award FROM participant_date WHERE user_id=" . getIdLoggedInUser() .
		" AND date_id=" . $date_id;
	$arrQuery = executeQueryReturnFields($query, array("participant_date_id", "student", "award"));
	if (is_array($arrQuery)) {
		$participant_date_id = ifEmpty($arrQuery["participant_date_id"], 0);
		$student = ifEmpty($arrQuery["student"], 0);
		$award = ifEmpty($arrQuery["award"], 0);
	}
	else {
		$participant_date_id = 0;
		$student = 0;
		$award = 0;
	}

	if ($student == 1) {
		$_SESSION['storage']['preregister_personalinfo_student']['y'] = 'y';
	}
	else {
		$_SESSION['storage']['preregister_personalinfo_student']['y'] = '';
	}

	if ($award == 1) {
		$_SESSION['storage']['preregister_registerpaper_award']['y'] = 'y';
	}
	else {
		$_SESSION['storage']['preregister_registerpaper_award']['y'] = '';
	}

	db_set_active(getSetting('db_connection'));

	// + + + + + + + + + + + + + + + + + + + + + +

	// NAW

	$result = db_select('users', 'n')
		->fields('n')
		->condition('user_id', $user_id, '=')
		->execute()
		->fetchAssoc();

	if ($result) {

		$_SESSION['storage']['preregister_personalinfo_firstname'] = $result["firstname"];
		$_SESSION['storage']['preregister_personalinfo_lastname'] = $result["lastname"];
		$_SESSION['storage']['preregister_personalinfo_gender'] = $result["gender"];
		$_SESSION['storage']['preregister_personalinfo_organisation'] = $result["organisation"];
		$_SESSION['storage']['preregister_personalinfo_department'] = $result["department"];
		$_SESSION['storage']['preregister_personalinfo_city'] = $result["city"];
		$_SESSION['storage']['preregister_personalinfo_country'] = $result["country_id"];
		$_SESSION['storage']['preregister_personalinfo_phone'] = $result["phone"];
		$_SESSION['storage']['preregister_personalinfo_mobile'] = $result["mobile"];
		$_SESSION['storage']['preregister_personalinfo_cv'] = $result["cv"];
	}

	// + + + + + + + + + + + + + + + + + + + + + +

	// PAPER

	// TODOTODO added session_id check
	$result2 = db_select('papers', 'n')
		->fields('n')
		->condition('user_id', $user_id, '=')
		->condition('date_id', $date_id, '=')
		->condition('session_id', null, 'IS')
		->execute()
		->fetchAssoc();

	if ($result2) {

		$_SESSION['storage']['what'] = 'paper';
		$_SESSION['storage']['preregister_registerpaper_papertitle'] = $result2["title"];
		$_SESSION['storage']['preregister_registerpaper_paperabstract'] = $result2["abstract"];
		$_SESSION['storage']['preregister_registerpaper_coauthors'] = $result2["co_authors"];
		$_SESSION['storage']['preregister_registerpaper_proposedsession'] = trim($result2["session_proposal"]);
		if ($_SESSION['storage']['preregister_registerpaper_proposedsession'] != '') {
			$_SESSION['storage']['preregister_registerpaper_partofexistingsession']['y'] = 'y';
		}
		$_SESSION['storage']['preregister_registerpaper_proposednetwork'] = $result2["network_proposal_id"];

		$_SESSION['storage']['preregister_registerpaper_extraaudiovisual'] = trim($result2["equipment_comment"]);

		// beamer
		$beamerid = getSetting('equipment_beamer_id');
		$paperid = $result2["paper_id"];
		if (checkRecordExists("SELECT * FROM paper_equipment WHERE paper_id=" . $paperid . " AND equipment_id=" .
		$beamerid)
		) {
			$_SESSION['storage']['preregister_registerpaper_audiovisual']["beamer"] = 'beamer';
		}

	}
	else {
		$_SESSION['storage']['what'] = 'spectator';
	}

	// + + + + + + + + + + + + + + + + + + + + + +

	// CHAIR
	$volunteering_id = getSetting('volunteering_chair');
	$arr = loadNetworksInArray("SELECT network_id FROM participant_volunteering WHERE participant_date_id=" .
	$participant_date_id . " AND volunteering_id=" . $volunteering_id, "network_id");
	$_SESSION['storage']['preregister_chairdiscussantpool_volunteerchair_networks'] = $arr;
	if (count($arr) > 0) {
		$_SESSION['storage']['preregister_chairdiscussantpool_volunteerchair']['y'] = 'y';
	}

	// + + + + + + + + + + + + + + + + + + + + + +

	// DISCUSSANT
	$volunteering_id = getSetting('volunteering_discussant');
	$arr = loadNetworksInArray("SELECT network_id FROM participant_volunteering WHERE participant_date_id=" .
	$participant_date_id . " AND volunteering_id=" . $volunteering_id, "network_id");
	$_SESSION['storage']['preregister_chairdiscussantpool_volunteerdiscussant_networks'] = $arr;
	if (count($arr) > 0) {
		$_SESSION['storage']['preregister_chairdiscussantpool_volunteerdiscussant']['y'] = 'y';
	}

	// + + + + + + + + + + + + + + + + + + + + + +

	// LANGUAGE
	$arr = loadNetworksInArray("SELECT network_id FROM participant_volunteering WHERE participant_date_id=" .
	$participant_date_id . " AND volunteering_id IN (" . getSetting('volunteering_languagecoach') . ", " .
	getSetting('volunteering_languagepupil') . ") ", "network_id");
	$_SESSION['storage']['preregister_chairdiscussantpool_coachpupil_networks'] = $arr;
	if (count($arr) > 0) {

		$arr2 = loadNetworksInArray("SELECT volunteering_id FROM participant_volunteering WHERE participant_date_id=" .
		$participant_date_id . " AND volunteering_id IN (" . getSetting('volunteering_languagecoach') . ", " .
		getSetting('volunteering_languagepupil') . ") GROUP BY volunteering_id ORDER BY COUNT(*) DESC ",
			"volunteering_id", 1);
		if (count($arr2) > 0) {
			if ($arr2[0] == getSetting('volunteering_languagecoach')) {
				$_SESSION['storage']['preregister_chairdiscussantpool_coachpupil'] = 0;
			}
			elseif ($arr2[0] == getSetting('volunteering_languagepupil')) {
				$_SESSION['storage']['preregister_chairdiscussantpool_coachpupil'] = 1;
			}
			else {
				$_SESSION['storage']['preregister_chairdiscussantpool_coachpupil'] = 2;
			}
		}
		else {
			$_SESSION['storage']['preregister_chairdiscussantpool_coachpupil'] = 2;
		}
	}

	// + + + + + + + + + + + + + + + + + + + + + +

	db_set_active();
}

/**
 * TODOEXPLAIN
 */
function saveUpdateParticipant() {
	$new = false;

	$checkQuery = "SELECT * FROM users WHERE email='" . addslashes(trim($_SESSION["conference"]["user_email"])) . "' ";
	if (checkRecordExists($checkQuery)) {
		$new = false;

		// EXISTS query
		$query =
			"UPDATE users SET lastname='::LASTNAME::', firstname='::FIRSTNAME::', gender=::GENDER::, city='::CITY::', country_id=::COUNTRY::, phone='::PHONE::', mobile='::MOBILE::', organisation='::ORGANISATION::', department='::DEPARTMENT::' ::CVSEPARATOR:: ::CVFIELD::=::CVVALUE:: WHERE email='::EMAIL::' ";

	}
	else {
		$new = true;

		// NEW query
		$query =
			"INSERT INTO users(lastname, firstname, gender, city, country_id, phone, mobile, organisation, department, email, date_added ::CVSEPARATOR:: ::CVFIELD::) VALUES('::LASTNAME::', '::FIRSTNAME::', ::GENDER::, '::CITY::', ::COUNTRY::, '::PHONE::', '::MOBILE::', '::ORGANISATION::', '::DEPARTMENT::', '::EMAIL::', '::DATE_ADDED::' ::CVSEPARATOR:: ::CVVALUE::) ";
	}

	// fill values
	$query = str_replace('::LASTNAME::', addslashes($_SESSION['storage']['preregister_personalinfo_lastname']), $query);
	$query =
		str_replace('::FIRSTNAME::', addslashes($_SESSION['storage']['preregister_personalinfo_firstname']), $query);

	// gender is not requered
	$tmpGender = addslashes($_SESSION['storage']['preregister_personalinfo_gender']);
	if ($tmpGender == '') {
		$tmpGender = 'NULL';
	}
	else {
		$tmpGender = "'" . $tmpGender . "'";
	}
	$query = str_replace('::GENDER::', $tmpGender, $query);

	$query = str_replace('::CITY::', addslashes($_SESSION['storage']['preregister_personalinfo_city']), $query);
	$query = str_replace('::ORGANISATION::', addslashes($_SESSION['storage']['preregister_personalinfo_organisation']),
		$query);
	$query =
		str_replace('::DEPARTMENT::', addslashes($_SESSION['storage']['preregister_personalinfo_department']), $query);
	$query = str_replace('::EMAIL::', strtolower(addslashes(trim($_SESSION["conference"]["user_email"]))), $query);
	$query = str_replace('::COUNTRY::', addslashes($_SESSION['storage']['preregister_personalinfo_country']), $query);
	$query = str_replace('::PHONE::', addslashes($_SESSION['storage']['preregister_personalinfo_phone']), $query);
	$query = str_replace('::MOBILE::', addslashes($_SESSION['storage']['preregister_personalinfo_mobile']), $query);
	$query = str_replace('::DATE_ADDED::', addslashes(date("Y-m-d")), $query);
	if (SettingsApi::getSetting(SettingsApi::SHOW_CV) == 1) {
		$query = str_replace('::CVSEPARATOR::', ', ', $query);
		$query = str_replace('::CVFIELD::', 'cv', $query);
		$query = str_replace('::CVVALUE::', "'" . addslashes($_SESSION['storage']['preregister_personalinfo_cv']) . "'",
			$query);
	}
	else {
		$query = str_replace('::CVSEPARATOR::', '', $query);
		$query = str_replace('::CVFIELD::=', '', $query);
		$query = str_replace('::CVFIELD::', '', $query);
		$query = str_replace('::CVVALUE::', '', $query);
	}

//echo $query;

	// execute query
	executeQuery($query);

	// if new
	if ($new) {
		// get new user id
		$_SESSION["conference"]["user_id"] = executeQueryReturnFields($checkQuery, "user_id");
	}

	// check if user has password
	$checkQuery = "SELECT * FROM users WHERE email='" .
		addslashes(trim($_SESSION["conference"]["user_email"])) . "' AND (salt IS NULL OR salt = '' OR password IS NULL OR password = '' )";
	if (checkRecordExists($checkQuery)) {
		// create and mail password
		createAndMailPassword(getIdLoggedInUser(), 1);
	}
}

/**
 * TODOEXPLAIN
 */
function saveUpdateParticipantDate() {
	$dateid = getSetting('date_id');

	$checkQuery =
		"SELECT * FROM participant_date WHERE user_id=" . addslashes(getIdLoggedInUser()) . " AND date_id=" . $dateid;

	if (checkRecordExists($checkQuery)) {
		// update
		$query =
			"UPDATE participant_date SET participant_state_id=999, student=::STUDENT::, lower_fee_requested=::STUDENT::, student_confirmed=0, award=::AWARD:: WHERE user_id=::USERID:: AND date_id=::DATEID:: ";
	}
	else {
		// add user to participant_date table
		$query =
			"INSERT INTO participant_date (user_id, date_id, date_added, participant_state_id, student, lower_fee_requested, student_confirmed, award) VALUES ('::USERID::', '::DATEID::', '::DATEADDED::', 999, ::STUDENT::, ::STUDENT::, 0, ::AWARD:: ) ";
	}

	$query = str_replace('::USERID::', getIdLoggedInUser(), $query);
	$query = str_replace('::DATEID::', $dateid, $query);
	$query = str_replace('::DATEADDED::', addslashes(date("Y-m-d")), $query);

	if (isset($_SESSION['storage']['preregister_personalinfo_student']['y']) &&
		$_SESSION['storage']['preregister_personalinfo_student']['y'] === 'y'
	) {
		$query = str_replace('::STUDENT::', 1, $query);
		if (isset($_SESSION['storage']['preregister_registerpaper_award']['y']) &&
			$_SESSION['storage']['preregister_registerpaper_award']['y'] === 'y'
		) {
			$query = str_replace('::AWARD::', 1, $query);
		}
		else {
			$query = str_replace('::AWARD::', 0, $query);
		}
	}
	else {
		$query = str_replace('::STUDENT::', 0, $query);
		$query = str_replace('::AWARD::', 0, $query);
	}
//echo $query . ' +<br>';

	executeQuery($query);
}

/**
 * TODOEXPLAIN
 */
function saveUpdatePaper() {
	$dateid = getSetting('date_id');

	if ($_SESSION['storage']['what'] == 'paper') {
		// GET PAPER ID
		$getPaperIdQuery = "SELECT paper_id FROM papers WHERE user_id=" . getIdLoggedInUser() . " AND date_id=" .
			$dateid . " AND session_id IS NULL ";
		$paperId = executeQueryReturnFields($getPaperIdQuery, "paper_id");

		if ($paperId != '') {
			// UPDATE
			$query =
				"UPDATE papers SET paper_state_id=1, date_id=::DATEID::, title='::TITLE::', co_authors='::COAUTHORS::', abstract='::ABSTRACT::', session_proposal='::PROPOSAL::', equipment_comment='::COMMENT::', network_proposal_id=::NETWORK:: WHERE paper_id=::PAPERID:: ";
		}
		else {
			// NEW
			$query =
				"INSERT INTO papers (user_id, date_id, title, co_authors, abstract, session_proposal, equipment_comment, paper_state_id, network_proposal_id) VALUES (::USERID::, ::DATEID::, '::TITLE::', '::COAUTHORS::', '::ABSTRACT::', '::PROPOSAL::', '::COMMENT::', 1, ::NETWORK::) ";
		}

		$query = str_replace('::USERID::', getIdLoggedInUser(), $query);
		$query = str_replace('::DATEID::', addslashes($dateid), $query);
		$query =
			str_replace('::TITLE::', addslashes($_SESSION['storage']['preregister_registerpaper_papertitle']), $query);
		$query = str_replace('::COAUTHORS::', addslashes($_SESSION['storage']['preregister_registerpaper_coauthors']),
			$query);
		$query =
			str_replace('::ABSTRACT::', addslashes($_SESSION['storage']['preregister_registerpaper_paperabstract']),
				$query);
		$query =
			str_replace('::NETWORK::', addslashes($_SESSION['storage']['preregister_registerpaper_proposednetwork']),
				$query);

		if ((isset($_SESSION['storage']['preregister_registerpaper_partofexistingsession']['y']) &&
			$_SESSION['storage']['preregister_registerpaper_partofexistingsession']['y'] === 'y')
		) {
			$proposal = $_SESSION['storage']['preregister_registerpaper_proposedsession'];
		}
		else {
			$proposal = '';
		}
		$query = str_replace('::PROPOSAL::', addslashes($proposal), $query);
		$query =
			str_replace('::COMMENT::', addslashes($_SESSION['storage']['preregister_registerpaper_extraaudiovisual']),
				$query);
		$query = str_replace('::PAPERID::', addslashes($paperId), $query);

//echo 'nnnn ';
		// execute query
		executeQuery($query);
//echo $query;

		if ($paperId == '') {
			// get paperId
			$paperId = executeQueryReturnFields($getPaperIdQuery, "paper_id");
		}
//echo 'mmm ';

		// ADD/REMOVE EQUIPMENT
		$beamerid = getSetting('equipment_beamer_id');
		if ((isset($_SESSION['storage']['preregister_registerpaper_audiovisual']["beamer"]) &&
			$_SESSION['storage']['preregister_registerpaper_audiovisual']["beamer"] === 'beamer')
		) {
			if (checkRecordExists("SELECT * FROM paper_equipment WHERE paper_id=" . $paperId . " AND equipment_id=" .
			$beamerid)
			) {
				$query = '';
			}
			else {
				// add
				$query = 'INSERT INTO paper_equipment (paper_id, equipment_id) VALUES (::PAPERID::, ::EQUIPMENTID::) ';
			}
		}
		else {
			// remove
			$query = 'DELETE FROM paper_equipment WHERE paper_id=::PAPERID:: AND equipment_id=::EQUIPMENTID:: ';

		}
		$query = str_replace('::PAPERID::', addslashes($paperId), $query);
		$query = str_replace('::EQUIPMENTID::', addslashes($beamerid), $query);

//echo 'kkkk ';
		// execute query
		executeQuery($query);
//echo $query;

	}
	elseif ($_SESSION['storage']['what'] == 'spectator') {
//echo "spectator ";
		// GET PAPER ID
		$getPaperIdQuery = "SELECT * FROM papers WHERE user_id=" . getIdLoggedInUser() . " AND date_id=" . $dateid;
		$paperId = executeQueryReturnFields($getPaperIdQuery, "paper_id");

		if ($paperId != '') {
			// DELETE EQUIPMENT
			$deleteQuery = "DELETE FROM paper_equipment WHERE paper_id=" . $paperId;
			executeQuery($deleteQuery);

			// DELETE PAPERS RECORD
			$deleteQuery = "DELETE FROM papers WHERE user_id=" . getIdLoggedInUser() . " AND date_id=" . $dateid;
			executeQuery($deleteQuery);

		}
	}
	elseif ($_SESSION['storage']['what'] == 'skip') {
		// TODO: skip
	}
	elseif ($_SESSION['storage']['what'] == 'session') {
		// TODO: SESSION
	}
//echo 'lll ';

}

/**
 * TODOEXPLAIN
 */
function show_more($string, $length = 300, $randomCode = '') {
	if ($randomCode == '') {
		$randomCode = rand(10000, 99999);
	}

	if (strlen($string) <= $length) {
		$text = $string;
	}
	else {
		$teaser = text_summary($string, null, $length);
		$article = substr($string, -(strlen($string) - strlen($teaser)));
		$more = "
<script language=\"javascript\">
function toggle::RANDOMCODE::() {
	var ele = document.getElementById(\"toggleText::RANDOMCODE::\");
	var text = document.getElementById(\"displayText::RANDOMCODE::\");
	if(ele.style.display == \"block\") {
		ele.style.display = \"none\";
		text.innerHTML = \"Read more...\";
	} else {
		ele.style.display = \"block\";
		text.innerHTML = \"Hide\";
	}
}
</script>
<a href=\"javascript:toggle::RANDOMCODE::();\" id=\"displayText::RANDOMCODE::\">Read more...</a>
<div id=\"toggleText::RANDOMCODE::\" style=\"display: none;\">" . $article . "</div>
";

		$more = str_replace('::RANDOMCODE::', $randomCode, $more);

		$text = $teaser . $more;
	}

	return $text;
}

/**
 * TODOEXPLAIN
 */
function saveUpdatePool() {

	$dateid = getSetting('date_id');

	$chairid = getSetting('volunteering_chair');
	$discussantid = getSetting('volunteering_discussant');

	$query = "SELECT participant_date_id FROM participant_date WHERE user_id=" . addslashes(getIdLoggedInUser()) .
		" AND date_id=" . $dateid;
	$participant_date_id = executeQueryReturnFields($query, "participant_date_id");
	$participant_date_id = ifEmpty($participant_date_id, 0);

	// CHAIR

	if ($_SESSION['storage']["preregister_chairdiscussantpool_volunteerchair"]['y'] === 'y') {
		// ADD

//		;

		$list = '';
		$separator = '';
		// TODOIMPLODE
		$tmp = $_SESSION['storage']["preregister_chairdiscussantpool_volunteerchair_networks"];
		if (!is_array($tmp)) {
			$list = $tmp;
		}
		else {
			$list = implode(", ", $tmp);
//			foreach ( $tmp as $network ) {
//				$list .= $separator . $network;
//				$separator = ', ';
//			}
		}

		/*
				foreach ( $_SESSION['storage']["preregister_chairdiscussantpool_volunteerchair_networks"] as $network ) {
					$list .= $separator . $network;
					$separator = ', ';
				}
		*/
		$deleteQuery = "DELETE FROM participant_volunteering WHERE participant_date_id=" . $participant_date_id .
			" AND volunteering_id=" . $chairid . " AND network_id NOT IN ( " . $list . ") ";
		executeQuery($deleteQuery);
		$querySelect =
			"SELECT * FROM participant_volunteering WHERE participant_date_id=::PARTICIPANTDATEID:: AND volunteering_id=::VOLUNTEERINGID:: AND network_id=::NETWORKINGID:: ";
		$queryInsert =
			"INSERT INTO participant_volunteering (participant_date_id, volunteering_id, network_id) VALUES (::PARTICIPANTDATEID::, ::VOLUNTEERINGID::, ::NETWORKINGID::) ";
//		foreach ( $_SESSION['storage']["preregister_chairdiscussantpool_volunteerchair_networks"] as $network ) {
		if (!is_array($tmp)) {
			insertIfNotExists($querySelect, $queryInsert,
				array("PARTICIPANTDATEID" => $participant_date_id, "VOLUNTEERINGID" => $chairid,
				      "NETWORKINGID"      => $tmp));
		}
		else {
			foreach ($tmp as $network) {
				insertIfNotExists($querySelect, $queryInsert,
					array("PARTICIPANTDATEID" => $participant_date_id, "VOLUNTEERINGID" => $chairid,
					      "NETWORKINGID"      => $network));
			}
		}

	}
	else {
		// DELETE
		$deleteQuery = "DELETE FROM participant_volunteering WHERE participant_date_id=" . $participant_date_id .
			" AND volunteering_id=" . $chairid . " ";
		executeQuery($deleteQuery);
	}

	// DISCUSSANT
	if ($_SESSION['storage']["preregister_chairdiscussantpool_volunteerdiscussant"]['y'] === 'y') {
		// ADD

		$list = '';
		// TODOIMPLODE
		$tmp = $_SESSION['storage']["preregister_chairdiscussantpool_volunteerdiscussant_networks"];
		if (!is_array($tmp)) {
			$list = $tmp;
		}
		else {
			$list = implode(", ", $tmp);
		}

		$deleteQuery = "DELETE FROM participant_volunteering WHERE participant_date_id=" . $participant_date_id .
			" AND volunteering_id=" . $discussantid . " AND network_id NOT IN ( " . $list . ") ";
		executeQuery($deleteQuery);

		$querySelect =
			"SELECT * FROM participant_volunteering WHERE participant_date_id=::PARTICIPANTDATEID:: AND volunteering_id=::VOLUNTEERINGID:: AND network_id=::NETWORKINGID:: ";
		$queryInsert =
			"INSERT INTO participant_volunteering (participant_date_id, volunteering_id, network_id) VALUES (::PARTICIPANTDATEID::, ::VOLUNTEERINGID::, ::NETWORKINGID::) ";
		if (!is_array($tmp)) {
			insertIfNotExists($querySelect, $queryInsert,
				array("PARTICIPANTDATEID" => $participant_date_id, "VOLUNTEERINGID" => $discussantid,
				      "NETWORKINGID"      => $tmp));
		}
		else {
			foreach ($tmp as $network) {
				insertIfNotExists($querySelect, $queryInsert,
					array("PARTICIPANTDATEID" => $participant_date_id, "VOLUNTEERINGID" => $discussantid,
					      "NETWORKINGID"      => $network));
			}
		}

	}
	else {
		// DELETE
		$deleteQuery = "DELETE FROM participant_volunteering WHERE participant_date_id=" . $participant_date_id .
			" AND volunteering_id=" . $discussantid . " ";
		executeQuery($deleteQuery);
	}

}

/**
 * TODOEXPLAIN
 */
function saveUpdateLanguage() {
	$dateid = getSetting('date_id');

	$query = "SELECT participant_date_id FROM participant_date WHERE user_id=" . addslashes(getIdLoggedInUser()) .
		" AND date_id=" . $dateid;
	$participant_date_id = executeQueryReturnFields($query, "participant_date_id");
	$participant_date_id = ifEmpty($participant_date_id, 0);

	$coachid = getSetting('volunteering_languagecoach');
	$pupilid = getSetting('volunteering_languagepupil');

	if ($_SESSION['storage']["preregister_chairdiscussantpool_coachpupil"] == 0) {
		// COACH

		// delete pupil
		$deleteQuery = "DELETE FROM participant_volunteering WHERE participant_date_id=" . $participant_date_id .
			" AND volunteering_id IN (" . $pupilid . " ) ";
		executeQuery($deleteQuery);

		// add coach
		$list = '';
		$separator = '';
		// TODOIMPLODE
		foreach ($_SESSION['storage']["preregister_chairdiscussantpool_coachpupil_networks"] as $network) {
			$list .= $separator . $network;
			$separator = ', ';
		}

		$deleteQuery = "DELETE FROM participant_volunteering WHERE participant_date_id=" . $participant_date_id .
			" AND volunteering_id=" . $coachid . " AND network_id NOT IN ( " . $list . ") ";
		executeQuery($deleteQuery);

		$checkQuery =
			"SELECT * FROM participant_volunteering WHERE participant_date_id=::PARTICIPANTDATEID:: AND volunteering_id=::VOLUNTEERINGID:: AND network_id=::NETWORKINGID:: ";
		$insertQuery =
			"INSERT INTO participant_volunteering (participant_date_id, volunteering_id, network_id) VALUES (::PARTICIPANTDATEID::, ::VOLUNTEERINGID::, ::NETWORKINGID::) ";

		foreach ($_SESSION['storage']["preregister_chairdiscussantpool_coachpupil_networks"] as $network) {
			$parameters = array("PARTICIPANTDATEID" => $participant_date_id, "VOLUNTEERINGID" => $coachid,
			                    "NETWORKINGID"      => $network);
			insertIfNotExists($checkQuery, $insertQuery, $parameters);
		}

	}
	elseif ($_SESSION['storage']["preregister_chairdiscussantpool_coachpupil"] == 1) {
		// PUPIL

		// delete coach
		$deleteQuery = "DELETE FROM participant_volunteering WHERE participant_date_id=" . $participant_date_id .
			" AND volunteering_id IN (" . $coachid . " ) ";
		executeQuery($deleteQuery);

		// add pupil
		$list = '';
		$separator = '';
		// TODOIMPLODE
		foreach ($_SESSION['storage']["preregister_chairdiscussantpool_coachpupil_networks"] as $network) {
			$list .= $separator . $network;
			$separator = ', ';
		}

		$deleteQuery = "DELETE FROM participant_volunteering WHERE participant_date_id=" . $participant_date_id .
			" AND volunteering_id=" . $pupilid . " AND network_id NOT IN ( " . $list . ") ";
		executeQuery($deleteQuery);

		$checkQuery =
			"SELECT * FROM participant_volunteering WHERE participant_date_id=::PARTICIPANTDATEID:: AND volunteering_id=::VOLUNTEERINGID:: AND network_id=::NETWORKINGID:: ";
		$insertQuery =
			"INSERT INTO participant_volunteering (participant_date_id, volunteering_id, network_id) VALUES (::PARTICIPANTDATEID::, ::VOLUNTEERINGID::, ::NETWORKINGID::) ";

		foreach ($_SESSION['storage']["preregister_chairdiscussantpool_coachpupil_networks"] as $network) {
			$parameters = array("PARTICIPANTDATEID" => $participant_date_id, "VOLUNTEERINGID" => $pupilid,
			                    "NETWORKINGID"      => $network);
			insertIfNotExists($checkQuery, $insertQuery, $parameters);
		}

	}
	else {
		// DELETE ALL
		$deleteQuery = "DELETE FROM participant_volunteering WHERE participant_date_id=" . $participant_date_id .
			" AND volunteering_id IN (" . $coachid . ", " . $pupilid . " ) ";
		executeQuery($deleteQuery);
	}
}

/**
 * TODOEXPLAIN
 */
function insertIfNotExists($checkQuery, $insertQuery, $parameters) {
	foreach ($parameters as $a => $b) {
		$checkQuery = str_replace('::' . $a . '::', addslashes($b), $checkQuery);
	}

	if (!checkRecordExists($checkQuery)) {

		foreach ($parameters as $a => $b) {
			$insertQuery = str_replace('::' . $a . '::', addslashes($b), $insertQuery);
		}

		executeQuery($insertQuery);
	}
}

/**
 * TODOEXPLAIN
 */
function loadParticipantData($participantid) {

	$_SESSION['storage']['preregistersession_participantemail'] = '';

	unset($_SESSION['storage']['preregistersession_participanttype']);

	$_SESSION['storage']['preregistersession_participantfirstname'] = '';
	$_SESSION['storage']['preregistersession_participantlastname'] = '';
	$_SESSION['storage']['preregistersession_participantpapertitle'] = '';
	$_SESSION['storage']['preregistersession_participantpaperabstract'] = '';
	$_SESSION['storage']['preregistersession_participantcv'] = '';
	unset($_SESSION['storage']['preregistersession_participantstudent']);

	if ($participantid > 0) {

		// NAW
		$arrParticipant = getDetailsAsArray('SELECT * FROM users WHERE user_id=' . $participantid,
			array('firstname', 'lastname', 'email', 'cv'));
		$_SESSION['storage']['preregistersession_participantemail'] = trim($arrParticipant['email']);
		$_SESSION['storage']['preregistersession_participantfirstname'] = trim($arrParticipant['firstname']);
		$_SESSION['storage']['preregistersession_participantlastname'] = trim($arrParticipant['lastname']);
		$_SESSION['storage']['preregistersession_participantcv'] = trim($arrParticipant['cv']);

// TODOLATER, disable enable paper/abstract field afhankelijk van type participant

		// PAPER
		$arrPaper = getDetailsAsArray('SELECT * FROM papers WHERE user_id=' . $participantid . ' AND date_id=' .
		getSetting('date_id') . ' AND ( session_id IS NULL OR session_id=' .
		$_SESSION['storage']['preregistersession_sessionid'] . ') ', array('title', 'abstract'));
		if (isset($arrPaper['title'])) {
			$_SESSION['storage']['preregistersession_participantpapertitle'] = $arrPaper['title'];
		}
		if (isset($arrPaper['abstract'])) {
			$_SESSION['storage']['preregistersession_participantpaperabstract'] = $arrPaper['abstract'];
		}

		// PARTICIPANT TYPE IN SESSION (MULTIPLE)
		$_SESSION['storage']['preregistersession_participanttype'] =
			getParticipantTypesAsArray($participantid, $_SESSION['storage']['preregistersession_sessionid']);

		// STUDENT
		$arrStudent =
			getDetailsAsArray('SELECT * FROM participant_date WHERE user_id=' . $participantid . ' AND date_id=' .
			getSetting('date_id'), array('student'));
		if (isset($arrStudent['student']) && $arrStudent['student'] == 1) {
			$_SESSION['storage']['preregistersession_participantstudent']['y'] = 'y';
		}
		else {
			$_SESSION['storage']['preregistersession_participantstudent']['y'] = '';
		}

	}
}

/**
 * TODOEXPLAIN
 */
function getParticipantTypesAsArray($participantid, $sessionid) {
	$ret = array();

	$query = 'SELECT * FROM session_participant WHERE user_id=' . $participantid . ' AND session_id=' . $sessionid;

	db_set_active(getSetting('db_connection'));

	$result = db_query($query);
	if ($result) {
		foreach ($result as $row) {
			$ret[$row->participant_type_id] = trim($row->participant_type_id);
		}
	}

	db_set_active();

	return $ret;
}

/**
 * TODOEXPLAIN
 */
function getParticipantTypesAsArray2($participantid, $sessionid) {
	$ret = array();

	$query =
		'SELECT participant_types.type FROM session_participant INNER JOIN participant_types ON session_participant.participant_type_id =participant_types.participant_type_id WHERE user_id=' .
		$participantid . ' AND session_id=' . $sessionid;

	db_set_active(getSetting('db_connection'));

	$result = db_query($query);
	if ($result) {
		foreach ($result as $row) {
			$ret[] = $row->type;
		}
	}

	db_set_active();

	return $ret;
}

/**
 * TODOEXPLAIN
 */
function saveParticipant() {
	// try to find a user with the current email address
	if ($_SESSION['storage']['preregistersession_participantid'] == '0') {
		$_SESSION['storage']['preregistersession_participantid'] =
			checkIfEmailAlreadyExists($_SESSION['storage']['preregistersession_participantemail'], 0);
	}

	// users table
	if ($_SESSION['storage']['preregistersession_participantid'] == '0') {
		// NEW
		$query =
			"INSERT INTO users (email, lastname, firstname, date_added, added_by ::CVSEPARATOR:: ::CVFIELD::) VALUES ('::EMAIL::', '::LASTNAME::', '::FIRSTNAME::', '::DATE_ADDED::', ::ADDEDBY:: ::CVSEPARATOR:: ::CVVALUE:: ) ";
	}
	else {
		// check by who it is added
		// if added by loggedInUser then allow modifying
		if (byWhomIsTheUserRecordAdded($_SESSION['storage']['preregistersession_participantid']) == getIdLoggedInUser()
		) {
			// UPDATE
			$query =
				"UPDATE users SET email='::EMAIL::', lastname='::LASTNAME::', firstname='::FIRSTNAME::' ::CVSEPARATOR:: ::CVFIELD::=::CVVALUE:: WHERE user_id=::USERID::";
		}
		else {
			// added by someone else
			// no modifying user table
			$query = '';
		}
	}

	$query =
		str_replace('::EMAIL::', strtolower(addslashes($_SESSION['storage']['preregistersession_participantemail'])),
			$query);
	$query =
		str_replace('::LASTNAME::', addslashes($_SESSION['storage']['preregistersession_participantlastname']), $query);
	$query = str_replace('::FIRSTNAME::', addslashes($_SESSION['storage']['preregistersession_participantfirstname']),
		$query);
	$query = str_replace('::DATE_ADDED::', date("Y-m-d"), $query);
	$query = str_replace('::ADDEDBY::', getIdLoggedInUser(), $query);
	$query = str_replace('::USERID::', $_SESSION['storage']['preregistersession_participantid'], $query);

	if (SettingsApi::getSetting(SettingsApi::SHOW_CV) == 1) {
		$query = str_replace('::CVSEPARATOR::', ', ', $query);
		$query = str_replace('::CVFIELD::', 'cv', $query);
		$query = str_replace('::CVVALUE::', "'" . addslashes($_SESSION['storage']['preregister_personalinfo_cv']) . "'",
			$query);
	}
	else {
		$query = str_replace('::CVSEPARATOR::', '', $query);
		$query = str_replace('::CVFIELD::=', '', $query);
		$query = str_replace('::CVFIELD::', '', $query);
		$query = str_replace('::CVVALUE::', '', $query);
	}

//echo $query;

	if ($query != '') {
		db_set_active(getSetting('db_connection'));
		$result = db_query($query);
		db_set_active();
	}

	// find new participant id
	if ($_SESSION['storage']['preregistersession_participantid'] == '0') {
		$_SESSION['storage']['preregistersession_participantid'] =
			findUserIdByEmailAddress($_SESSION['storage']['preregistersession_participantemail']);
	}

	$student = 0;
	if (isset($_SESSION['storage']['preregistersession_participantstudent'])) {
		if ($_SESSION['storage']['preregistersession_participantstudent']['y'] === 'y') {
			$student = 1;
		}
	}

	// participantfordate table
	$query =
		'SELECT * FROM participant_date WHERE user_id=' . $_SESSION['storage']['preregistersession_participantid'] .
		' AND date_id=' . getSetting('date_id');
	if (doesRecordExist($query)) {
		// participant date exists
		// update student
		$query =
			"UPDATE participant_date SET student=::STUDENT::, lower_fee_requested=::STUDENT:: WHERE user_id=::USERID:: AND date_id=::DATEID:: ";
	}
	else {
		// participant date does NOT exist
		$query =
			"INSERT INTO participant_date (user_id, date_id, date_added, added_by, student, lower_fee_requested) VALUES (::USERID::, ::DATEID::, '::DATE_ADDED::', ::ADDEDBY::, ::STUDENT::, ::STUDENT::) ";
	}

	$query = str_replace('::USERID::', $_SESSION['storage']['preregistersession_participantid'], $query);
	$query = str_replace('::DATEID::', getSetting('date_id'), $query);
	$query = str_replace('::DATE_ADDED::', date("Y-m-d"), $query);
	$query = str_replace('::ADDEDBY::', getIdLoggedInUser(), $query);
	$query = str_replace('::STUDENT::', $student, $query);

	db_set_active(getSetting('db_connection'));
	$result = db_query($query);
	db_set_active();

	// session participant table
	foreach ($_SESSION['storage']['preregistersession_participanttype'] as $ptype) {

		$query = 'SELECT * FROM session_participant WHERE user_id=' .
			$_SESSION['storage']['preregistersession_participantid'] . ' AND session_id=' .
			$_SESSION['storage']['preregistersession_sessionid'] . ' AND participant_type_id=' . $ptype;
		if (!doesRecordExist($query)) {
			// session participant does NOT exist

			$query =
				"INSERT INTO session_participant (user_id, session_id, added_by, participant_type_id) VALUES (::USERID::, ::SESSIONID::, ::ADDEDBY::, ::TYPE::) ";
			$query = str_replace('::USERID::', $_SESSION['storage']['preregistersession_participantid'], $query);
			$query = str_replace('::SESSIONID::', $_SESSION['storage']['preregistersession_sessionid'], $query);
			$query = str_replace('::ADDEDBY::', getIdLoggedInUser(), $query);
			$query = str_replace('::TYPE::', $ptype, $query);

			db_set_active(getSetting('db_connection'));
			$result = db_query($query);
			db_set_active();

		}
	}

	// verwijder verwijzingen naar alle niet gekozen types
	$query =
		'DELETE FROM session_participant WHERE user_id=' . $_SESSION['storage']['preregistersession_participantid'] .
		' AND session_id=' . $_SESSION['storage']['preregistersession_sessionid'] .
		' AND participant_type_id NOT IN (' .
		implode(',', $_SESSION['storage']['preregistersession_participanttype']) . ') ';
	db_set_active(getSetting('db_connection'));
	$result = db_query($query);
	db_set_active();

	// save/update papers
	insertUpdateParticipantPaper($_SESSION['storage']['preregistersession_participantid'],
		$_SESSION['storage']['preregistersession_participanttype'],
		$_SESSION['storage']['preregistersession_sessionid']);

	//
	db_set_active();
}

/**
 * TODOEXPLAIN
 */
function checkIfParticipantIsMultipleAuthor($userid, $types, $sessionid) {
	$ret = false;

	if ($userid != 0) {

		if (in_array(getSetting('author_id'), $types)) {

			$query = "SELECT * FROM session_participant WHERE user_id=" . $userid . ' AND session_id<>' . $sessionid .
				' AND participant_type_id=' . getSetting('author_id');
			$ret = checkRecordExists($query);

		}
	}

	return $ret;
}

/**
 * TODOEXPLAIN
 */
function insertUpdateParticipantPaper($userid, $types, $sessionid) {
	// check if user is an author or coauthor
	if (in_array(getSetting('author_id'), $types) === false && in_array(getSetting('coauthor_id'), $types) === false) {
		// NO, then delete all papers in current session made by loggedInUser
		$query = "DELETE FROM papers WHERE user_id=" . $userid . ' AND session_id=' . $sessionid .
			' AND added_by=' . getIdLoggedInUser();
//dbug($query);
		db_set_active(getSetting('db_connection'));
		$result = db_query($query);
		db_set_active();

		// if user has a paper in this session but not created by loggedInUser then don't remove it, but only remove sessionid
		$query = "UPDATE papers SET session_id=NULL WHERE user_id=" . $userid . ' AND session_id=' . $sessionid;
//dbug($query);
		db_set_active(getSetting('db_connection'));
		$result = db_query($query);
		db_set_active();
	}
	else {
		// insert/update paper

		$paperId = 0;
		$queryPaper = '';

		// try to find a paper for current session
		$query3 = 'SELECT paper_id FROM papers WHERE user_id=' . $userid . ' AND session_id=' . $sessionid .
			' AND added_by=' . getIdLoggedInUser();
		$recordsFound3 = countRecords($query3);
		if ($recordsFound3 == 1) {
			// GET PAPERID
			$tmp = getDetailsAsArray($query3, "paper_id");
			$paperId = $tmp["paper_id"];

			// UPDATE, als eigenaar mag je title en abstract wijzigen
			$queryPaper =
				'UPDATE papers SET title=\'::TITLE::\', abstract=\'::ABSTRACT::\', network_proposal_id=::NETWORK:: WHERE paper_id=::PAPERID:: ';
		}
		elseif ($recordsFound3 > 1) {
			// TODOLATER: SHOW ERROR: MULTIPLE PAPERS IN CURRENT SESSION FOR USER
			$queryPaper = '';
		}
		else {

			// try to find a paper for current session
			$query = 'SELECT paper_id FROM papers WHERE user_id=' . $userid . ' AND session_id=' . $sessionid;
			$recordsFound = countRecords($query);
			if ($recordsFound == 1) {
				// GET PAPERID
				$tmp = getDetailsAsArray($query, "paper_id");
				$paperId = $tmp["paper_id"];

				// GEEN UPDATE, als NIET eigenaar mag je title en abstract NIET wijzigen
				$queryPaper = '';
			}
			elseif ($recordsFound > 1) {
				// TODOLATER: SHOW ERROR: MULTIPLE PAPERS IN CURRENT SESSION FOR USER
				$queryPaper = '';
			}
			else {
				// if not found, count number of papers for current dateid but without sessionid
				$query2 = 'SELECT paper_id FROM papers WHERE user_id=' . $userid .
					' AND (session_id IS NULL OR session_id=0) AND date_id=' . getSetting('date_id');
				$recordsFound2 = countRecords($query2);
				if ($recordsFound2 == 1) {
					// GET PAPERID
					$tmp = getDetailsAsArray($query2, "paper_id");
					$paperId = $tmp["paper_id"];

					// UPDATE, maar alleen sessie id en network proposal id
					$queryPaper =
						'UPDATE papers SET session_id=::SESSIONID::, network_proposal_id=::NETWORK:: WHERE paper_id=::PAPERID:: ';
				}
				elseif ($recordsFound2 > 1) {
					// TODOLATER: SHOW ERROR: MULTIPLE PAPERS IN CURRENT SESSION FOR USER
					$queryPaper = '';
				}
				else {
					// if still not found, create new one
					$queryPaper =
						'INSERT INTO papers (user_id, session_id, date_id, title, abstract, added_by, network_proposal_id) VALUES(::USERID::, ::SESSIONID::, ::DATEID::, \'::TITLE::\', \'::ABSTRACT::\', ::ADDEDBY::, ::NETWORK::) ';
//dbug($queryPaper);
				}
			}
		}

		$queryPaper = str_replace('::PAPERID::', $paperId, $queryPaper);
		$queryPaper = str_replace('::USERID::', $userid, $queryPaper);
		$queryPaper = str_replace('::SESSIONID::', $sessionid, $queryPaper);
		$queryPaper = str_replace('::ADDEDBY::', getIdLoggedInUser(), $queryPaper);
		$queryPaper = str_replace('::DATEID::', getSetting('date_id'), $queryPaper);
		$queryPaper =
			str_replace('::TITLE::', addslashes($_SESSION['storage']['preregistersession_participantpapertitle']),
				$queryPaper);
		$queryPaper =
			str_replace('::ABSTRACT::', addslashes($_SESSION['storage']['preregistersession_participantpaperabstract']),
				$queryPaper);
		$queryPaper =
			str_replace('::NETWORK::', addslashes($_SESSION['storage']['preregistersession_sessioninnetwork']),
				$queryPaper);

//dbug($queryPaper);
		if ($queryPaper != '') {
			db_set_active(getSetting('db_connection'));
			$result = db_query($queryPaper);
			db_set_active();
		}

	}
}

// TODOLATER autoinvullen velden na invullen email veld

/**
 * TODOEXPLAIN
 */
function removeParticipantAddedBy($participantid, $organizerid, $sessionid) {
	db_set_active(getSetting('db_connection'));

	// SESSION_PARTICIPANT
	$query = 'DELETE FROM session_participant WHERE user_id=' . $participantid . ' AND added_by=' . $organizerid .
		' AND session_id=' . $sessionid;
	$result = db_query($query);

	if ($participantid != getIdLoggedInUser()) {
		// PARTICIPANT_DATE
		$query = 'DELETE FROM participant_date WHERE user_id=' . $participantid . ' AND added_by=' . $organizerid .
			' AND date_id=' . getSetting('date_id');
		$result = db_query($query);

		// USERS
		$query = 'DELETE FROM users WHERE user_id=' . $participantid . ' AND added_by=' . $organizerid;
		$result = db_query($query);
	}

	db_set_active();
}

/**
 * TODOEXPLAIN
 */
function removeSessionAddedBy($sessionid, $organizerid) {
	// find all participants in current session and remove them
	foreach (getListOfParticipantsInSession($sessionid, $organizerid) as $participantid) {
		removeParticipantAddedBy($participantid, $organizerid, $sessionid);
	}

	db_set_active(getSetting('db_connection'));

	// TODOLATER: controleer eerst wie eigenaar is van sessions record, indien huidige persoon, dan mag sessioninnetwork verwijderd worden zonder addedby controle

	// SESSION_IN_NETWORK
	$query = 'DELETE FROM session_in_network WHERE session_id=' . $sessionid . ' AND added_by=' . $organizerid;
	$result = db_query($query);

	// SESSIONS
	$query = 'DELETE FROM sessions WHERE session_id=' . $sessionid . ' AND added_by=' . $organizerid;
	$result = db_query($query);

	db_set_active();
}

/**
 * TODOEXPLAIN
 */
function getListOfParticipantsInSession($sessionid, $organizerid) {
	$arr = array();

	db_set_active(getSetting('db_connection'));

	$result = db_query('SELECT * FROM session_participant WHERE session_id=' . $sessionid);

	foreach ($result as $record) {
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
function getShiftValue($value, $shift) {
	$ret = '';

	$value = str_replace('/', ' ', $value);

	while (strpos($value, '  ') !== false) {
		$value = str_replace('  ', ' ', $value);
	}

	$value = trim($value);

	$arr = explode(' ', $value);

	if (count($arr) > $shift) {
		for ($i = 1; $i <= $shift; $i++) {
			array_shift($arr);
		}

		$ret = $arr[0];
	}

	return $ret;
}

/**
 * TODOEXPLAIN
 */
function getNumberOfDirectories($value) {
	$ret = 0;

	$value = str_replace('/', ' ', $value);

	while (strpos($value, '  ') !== false) {
		$value = str_replace('  ', ' ', $value);
	}

	$value = trim($value);

	$arr = explode(' ', $value);

	$ret = count($arr);

	return $ret;
}