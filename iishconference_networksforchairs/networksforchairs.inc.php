<?php 
// TODOLATER testen registratie single paper
// TODOLATER testen registratie sessie
/**
 * TODOEXPLAIN
 */
function iishconference_networksforchairs_form( $form, &$form_state ) {
	$date_id = getSetting('date_id');
	$ct=0;

	$oUser = new class_conference_user( getIdLoggedInUser() );

	// check user logged in
	if ( !isUserLoggedIn() ) {

		// redirect to login page
		Header("Location: /" . getSetting('pathForMenu') . "login/?backurl=" . urlencode($_SERVER["REQUEST_URI"]) );
		die('Go to <a href="/' . getSetting('pathForMenu') . 'login/?backurl=' . urlencode($_SERVER["REQUEST_URI"]) . '">login</a> page.');

	} elseif ( !$oUser->isCrew() && !$oUser->isNetworkChair() ) {

		drupal_set_message("Access denied. You are not a network chair.", 'error');

	} else {

		$network = 0;
		$session = 0;

		$url = $_SERVER["REQUEST_URI"];

		//TODOLATER
		$url = str_replace(getSetting('pathForMenu'), '', $url);

		$url = str_replace(array('/', '\\'), ' ', $url);
		$url = trim($url);
		$arrUrl = explode(' ', $url);

		if ( count($arrUrl) > 2 ) {
			$session = $arrUrl[2];
		}

		if ( count($arrUrl) > 1 ) {
			$network = $arrUrl[1];
		}

		$network = intval($network);
		$session = intval($session);

		if ( $network !== 0 && $session !== 0 ) {
			// show single session
			iishconference_networksforchairs_listofparticipants($form, $ct, $network, $session);
		} elseif ( $network !== 0 && $session === 0) {
			// show single network with sessions
			iishconference_networksforchairs_listofsessions($form, $ct, $network);
		} else {
			// show list of all networks
			iishconference_networksforchairs_listofnetworks($form, $ct, getIdLoggedInUser());
		}

	}

	return $form;
}

/**
 * TODOEXPLAIN
 */
function iishconference_networksforchairs_listofnetworks( &$form, &$ct, $userId ) {

	$oPart = new class_conference_participantdate($userId);
	$arrNetworks = $oPart->getNetworkObjectsWhereChair();

	if ( count($arrNetworks) > 0 ) {
		$form['ct'.$ct++] = array(
				'#type' => 'markup',
				'#markup' => '<strong>Your network(s)</strong><br>',
				);

		// show networks
		for ( $i = 0; $i < count($arrNetworks); $i++ ) {

			$netw = $arrNetworks[$i];
			$form['ct'.$ct++] = array(
					'#type' => 'markup',
					'#markup' => '<a href="/' . getSetting('pathForMenu') . 'networksforchairs/' . $netw->getNetworkId() . '">' . $netw->getNetworkName() . '</a><br>',
					);

		}

		$form['ct'.$ct++] = array(
				'#type' => 'markup',
				'#markup' => '<br>',
				);
	}

	//
	$oNetworks = new class_conference_networks( getSetting('date_id') );
	$arrNetworks = $oNetworks->getNetworkObjects();

	$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => '<strong>All networks</strong><br>',
			);

	if ( count($arrNetworks) > 0 ) {

		// show networks
		for ( $i = 0; $i < count($arrNetworks); $i++ ) {

			$netw = $arrNetworks[$i];
			$form['ct'.$ct++] = array(
					'#type' => 'markup',
					'#markup' => '<a href="/' . getSetting('pathForMenu') . 'networksforchairs/' . $netw->getNetworkId() . '">' . $netw->getNetworkName() . '</a><br>',
					);

		}

	} else {
		$form['ct'.$ct++] = array(
				'#type' => 'markup',
				'#markup' => 'No networks found...<br>',
				);
	}

}

/**
 * TODOEXPLAIN
 */
function iishconference_networksforchairs_listofsessions( &$form, &$ct, $networkId ) {
	$oNetwork = new class_conference_network($networkId);

	$prevNext = $oNetwork->getPrevNext();
	$prev = '&laquo; prev';
	$next = 'next &raquo;';
	if ( $prevNext[0] != 0 ) {
		$prev = '<a href="/' . getSetting('pathForMenu') . 'networksforchairs/' . $prevNext[0] . '" alt="previous network" title="previous network">' . $prev . '</a>';
	}
	if ( $prevNext[1] != 0 ) {
		$next = '<a href="/' . getSetting('pathForMenu') . 'networksforchairs/' . $prevNext[1] . '" alt="next network " title="next network">' . $next . '</a>';
	}

	$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => '<table class="noborder"><tr><td class="noborder"><strong><a href="/' . getSetting('pathForMenu') . 'networksforchairs">&laquo; Go back to networks list</a></strong></td><td align=right class="noborder">' . $prev . ' &nbsp; ' . $next . '</td></tr></table><br>',
			);

	$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => '<strong>Network:</strong> ' . $oNetwork->getNetworkName() . '<br>',
			);

	$arrChairs = $oNetwork->getChairs();
	$chairs = '';
	$separator = '';
	for ( $i = 0; $i < count($arrChairs); $i++ ) {
		$p = $arrChairs[$i];
		$chairs .= $separator . '<a href="mailto:' . $p->getEmail() . '">' . $p->getFirstName() . ' ' . $p->getLastName() . '</a>';
		if ( $i < count($arrChairs)-2 ) {
			$separator = ', ';
		} else {
			$separator = ' and ';
		}
	}

	$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => '<strong>Network chairs:</strong> ' . $chairs . '<br><br>',
			);

	$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => '<strong>Sessions</strong><br><ol>',
			);

	// list of sessions (all)
	$arrSessions = $oNetwork->getSessions(true);
	for ( $i = 0; $i < count($arrSessions); $i++ ) {

		$session = $arrSessions[$i];

		$startstrike = '';
		$endstrike = '';
		if ( $session->getDeleted() == 1 ) {
			$startstrike = '<strike>';
			$endstrike = '</strike> <sup><i>(<a alt="Session is deleted" title="Session is deleted">?</a>)</i></sup>';
		}

		if ( $session->getDeleted() ) {
			$sessionState = 'Session Deleted';
		} else {
			$sessionState = $session->getState()->getDescription();
		}
		$sessionState = trim(str_replace('Session', '', $sessionState));
		$form['ct'.$ct++] = array(
				'#type' => 'markup',
				'#markup' => '<li>' . $startstrike . '<a href="/' . getSetting('pathForMenu') . 'networksforchairs/' . $networkId . '/' . $session->getId() . '">' . $session->getName() . '</a>' . $endstrike . ' <em>(' . $sessionState . ')</em></li>',
				);
	}

	$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => '<li><a href="/' . getSetting('pathForMenu') . 'networksforchairs/' . $networkId . '/-1">...Individual paper proposals...</a></li>',
			);

	$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => '<ol>',
			);

}

/**
 * TODOEXPLAIN
 */
function iishconference_networksforchairs_listofparticipants( &$form, &$ct, $networkId, $sessionId ) {
	$oNetwork = new class_conference_network($networkId);

	$oSession = new class_conference_session($sessionId, true);

	$prevNext = $oSession->getPrevNext($networkId, true, -1);
	$prev = '&laquo; prev';
	$next = 'next &raquo;';
	if ( $prevNext[0] != 0 ) {
		$prev = '<a href="/' . getSetting('pathForMenu') . 'networksforchairs/' . $networkId . '/' . $prevNext[0] . '" alt="previous session" title="previous session">' . $prev . '</a>';
	}
	if ( $prevNext[1] != 0 ) {
		$next = '<a href="/' . getSetting('pathForMenu') . 'networksforchairs/' . $networkId . '/' . $prevNext[1] . '" alt="next session" title="next session">' . $next . '</a>';
	}

	$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => '<table class="noborder"><tr><td class="noborder"><strong><a href="/' . getSetting('pathForMenu') . 'networksforchairs/' . $networkId . '">&laquo; Go back to sessions list</a></strong></td><td align=right class="noborder">' . $prev . ' &nbsp; ' . $next . '</td></tr></table><br>',
			);

	$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => '<strong>Network:</strong> ' . $oNetwork->getNetworkName() . '<br>',
			);

	$arrChairs = $oNetwork->getChairs();
	$chairs = '';
	$separator = '';
	for ( $i = 0; $i < count($arrChairs); $i++ ) {
		$p = $arrChairs[$i];
		$chairs .= $separator . '<a href="mailto:' . $p->getEmail() . '">' . $p->getFirstName() . ' ' . $p->getLastName() . '</a>';
		if ( $i < count($arrChairs)-2 ) {
			$separator = ', ';
		} else {
			$separator = ' and ';
		}
	}

	$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => '<strong>Network chairs:</strong> ' . $chairs . '<br><br>',
			);

	$startstrike = '';
	$endstrike = '';

	if ( $sessionId < 0 ) {
		// NO SESSION
		$sessionname = '...Individual paper proposals...';
	} else {
		// SESSION
		$sessionname = $oSession->getName();

		if ( $oSession->getDeleted() == 1 ) {
			$startstrike = '<strike>';
			$endstrike = '</strike> <sup><i>(<a alt="Session is deleted" title="Session is deleted">?</a>)</i></sup>';
		}
	}

	$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => '<strong>Session:</strong> ' . $startstrike . $sessionname . $endstrike . '<br>',
			);

	if ( $sessionId > 0 ) {
		if ( $oSession->getDeleted() == 1 ) {
			$form['ct'.$ct++] = array(
					'#type' => 'markup',
					'#markup' => '<strong>Session state:</strong> Session deleted<br>',
					);
		} else {
			$form['ct'.$ct++] = array(
					'#type' => 'markup',
					'#markup' => '<strong>Session state:</strong> ' . $oSession->getState()->getDescription() . '<br>',
					);
		}

		$oAddedBy = $oSession->getAddedBy();
		if ( $oAddedBy->getId() > 0 ) {
			$form['ct'.$ct++] = array(
					'#type' => 'markup',
					'#markup' => '<strong>Session added by:</strong> <a href="mailto:' . $oAddedBy->getEmail() . '">' . $oAddedBy->getFirstName() . ' ' . $oAddedBy->getLastName() . '</a><br>',
					);
		}

		$form['ct'.$ct++] = array(
				'#type' => 'markup',
				'#markup' => '<strong>Session abstract:</strong><br>' . $oSession->getAbstract() . '<br>',
				);
	}

	if ( $sessionId < 0 ) {
		// NO SESSION
		$arrParticipants = $oSession->getParticipantsWithoutSession($networkId, true);
	} else {
		// SESSION
		$arrParticipants = $oSession->getParticipants( true );
	}
//echo count($arrParticipants);

	for ( $i = 0; $i < count($arrParticipants); $i++ ) {

		$form['ct'.$ct++] = array(
				'#type' => 'markup',
				'#markup' => '<br><hr><br>',
				);

		$participant = $arrParticipants[$i];
		$participantdate = new class_conference_participantdate($participant->getId(), true);

		$startstrike = '';
		$endstrike = '';
		if ( $participant->getDeleted() == 1 || $participantdate->getDeleted() == 1 ) {
			$startstrike = '<strike>';
			$endstrike = '</strike> <sup><i>(<a alt="Participant is deleted" title="Participant is deleted">?</a>)</i></sup>';
		}

		$form['ct'.$ct++] = array(
				'#type' => 'markup',
				'#markup' => '<strong>Participant:</strong> <a href="mailto:' . $participant->getEmail() . '">' . $startstrike . $participant->getFirstName() . ' ' . $participant->getLastName() . $endstrike . '</a><br>',
				);
		$form['ct'.$ct++] = array(
				'#type' => 'markup',
				'#markup' => '<strong>Organisation:</strong> ' . $participant->getOrganisation() . '<br>',
				);

		if ( $sessionId >= 0 ) {
			// SESSION
			$form['ct'.$ct++] = array(
					'#type' => 'markup',
					'#markup' => '<strong>Type:</strong> ' . $participantdate->getSessionType( $sessionId ) . '<br><br>',
					);
		}

		if ( $sessionId < 0 ) {
			// NO SESSION
			$oPaper = new class_conference_participantsession( $participant->getId(), 0, $networkId, true );
		} else {
			// SESSION
			$oPaper = new class_conference_participantpaper( $participant->getId(), $sessionId, 0, true );
		}

		if ( $oPaper->getId() != 0 ) {

			$startstrike = '';
			$endstrike = '';
			if ( $oPaper->getDeleted() == 1 ) {
				$startstrike = '<strike>';
				$endstrike = '</strike> <sup><i>(<a alt="Paper is deleted" title="Paper is deleted">?</a>)</i></sup>';
			}

			$form['ct'.$ct++] = array(
					'#type' => 'markup',
					'#markup' => '<strong>Paper:</strong> ' . $startstrike . $oPaper->getTitle() . $endstrike . '<br>',
					);

			if ( $oPaper->getCoAuthors() != '' ) {
				$form['ct'.$ct++] = array(
						'#type' => 'markup',
						'#markup' => '<strong>Co-author(s):</strong> ' . $oPaper->getCoAuthors() . '<br>',
						);
			}

			$state = $oPaper->getState()->getDescription();
			$form['ct'.$ct++] = array(
					'#type' => 'markup',
					'#markup' => '<strong>Paper state:</strong> ' . $state . '<br>',
					);

			$form['ct'.$ct++] = array(
					'#type' => 'markup',
					'#markup' => '<strong>Paper abstract:</strong><br>' . $oPaper->getAbstract() . '<br>',
					);

			$form['ct'.$ct++] = array(
					'#type' => 'markup',
					'#markup' => '<br>',
					);
		}

	}
}