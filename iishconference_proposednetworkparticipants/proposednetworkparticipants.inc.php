<?php 
/**
 * TODOEXPLAIN
 */
function iishconference_proposednetworkparticipants_form( $form, &$form_state ) {
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

		$url = $_SERVER["REQUEST_URI"];
		$url = str_replace(array(getSetting('pathForMenu')), '', $url);
		$url = str_replace(array('/', '\\'), ' ', $url);
		$url = trim($url);
		$arrUrl = explode(' ', $url);

		if ( count($arrUrl) > 1 ) {
			$network = $arrUrl[1];
		}

		$network = intval($network);

		if ( $network !== 0 ) {
			// show single session
			iishconference_proposednetworkparticipants_listofparticipants($form, $ct, $network);
		} else {
			// show list of all networks
			iishconference_proposednetworkparticipants_listofnetworks($form, $ct, getIdLoggedInUser());
		}

	}

	return $form;
}

/**
 * TODOEXPLAIN
 */
function iishconference_proposednetworkparticipants_listofnetworks( &$form, &$ct, $userId ) {

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
					'#markup' => '<a href="/' . getSetting('pathForMenu') . 'proposednetworkparticipants/' . $netw->getNetworkId() . '">' . $netw->getNetworkName() . '</a><br>',
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
					'#markup' => '<a href="/' . getSetting('pathForMenu') . 'proposednetworkparticipants/' . $netw->getNetworkId() . '">' . $netw->getNetworkName() . '</a><br>',
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
function iishconference_proposednetworkparticipants_listofparticipants( &$form, &$ct, $networkId ) {
	$oNetwork = new class_conference_network($networkId);

	$prevNext = $oNetwork->getPrevNext();
	$prev = '&laquo; prev';
	$next = 'next &raquo;';
	if ( $prevNext[0] != 0 ) {
		$prev = '<a href="/' . getSetting('pathForMenu') . 'proposednetworkparticipants/' . $prevNext[0] . '" alt="previous network" title="previous network">' . $prev . '</a>';
	}
	if ( $prevNext[1] != 0 ) {
		$next = '<a href="/' . getSetting('pathForMenu') . 'proposednetworkparticipants/' . $prevNext[1] . '" alt="next network " title="next network">' . $next . '</a>';
	}

	$form['ct'.$ct++] = array(
			'#type' => 'markup',
			'#markup' => '<table class="noborder"><tr><td class="noborder"><strong><a href="/' . getSetting('pathForMenu') . 'proposednetworkparticipants/">&laquo; Go back to networks list</a></strong></td><td align=right class="noborder">' . $prev . ' &nbsp; ' . $next . '</td></tr></table><br>',
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
			'#markup' => '<hr><br>',
			);

	//
	$arrParticipants = $oNetwork->getParticipantsProposedNetwork();
	if ( count($arrParticipants) == 0 ) {
		$form['ct'.$ct++] = array(
				'#type' => 'markup',
				'#markup' => 'No participants found in network<br><br>',
				);
	} else {
		iishconference_proposednetworkparticipants_listofparticipants_details($form, $ct, $arrParticipants, $oNetwork->getNetworkId());
	}
}

/**
 * TODOEXPLAIN
 */
function iishconference_proposednetworkparticipants_listofparticipants_details( &$form, &$ct, $arrParticipants, $proposedNetworkId ) {

	$separator = '';

	foreach ( $arrParticipants as $oParticipant ) {

		$form['ct'.$ct++] = array(
				'#type' => 'markup',
				'#markup' => $separator . "<a href=\"mailto:" . $oParticipant->getEmail() . "\">" . $oParticipant->getFirstname() . " " . $oParticipant->getLastname() . "</a><br>",
				);

		$form['ct'.$ct++] = array(
				'#type' => 'markup',
				'#markup' => ifEmpty($oParticipant->getOrganisation(), '<em>(Unknown affiliation)</em>') . "<br>",
				);

		$oPaper = new class_conference_participantsession($oParticipant->getId(), -1, $proposedNetworkId, true);

		$form['ct'.$ct++] = array(
				'#type' => 'markup',
				'#markup' => '<strong>Paper name:</strong> ' . $oPaper->getTitle() . "<br>",
				);

		if ( trim( $oPaper->getCoAuthors() ) != '' ) {
			$form['ct'.$ct++] = array(
					'#type' => 'markup',
					'#markup' => '<strong>Co-authors:</strong> ' . $oPaper->getCoAuthors() . "<br>",
					);
		}

		$form['ct'.$ct++] = array(
				'#type' => 'markup',
				'#markup' => "<strong>Paper state:</strong> " . $oPaper->getState()->getDescription() . "<br>",
				);

		$oSession = $oPaper->getSession();
		if ( is_object($oSession) ) {
			$sessionValue = trim($oSession->getName());
		} else {
			$sessionValue = '<em>(NO SESSION YET)</em>';
		}
		$form['ct'.$ct++] = array(
				'#type' => 'markup',
				'#markup' => '<strong>Session name:</strong> ' . $sessionValue . "<br>",
				);

		$form['ct'.$ct++] = array(
				'#type' => 'markup',
				'#markup' => '<strong>Paper abstract:</strong><br>' . $oPaper->getAbstract(300) . "<br>",
				);

		$separator = '<br><br>';
	}
}