<?php

/**
 * Returns a list of networks to choose from
 *
 * @return string The HTML for a list of networks
 */
function iishconference_proposednetworkparticipants_main() {
	if (!LoggedInUserDetails::isLoggedIn()) {
		// redirect to login page
		header('Location: ' .
			url(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())));
		die(iish_t('Go to !login page.',
			array('!login' => l(iish_t('login'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())))));
	}

	if (!LoggedInUserDetails::isCrew() && !LoggedInUserDetails::isNetworkChair()) {
		drupal_set_message(iish_t('Access denied. You are not a chair of a network.'), 'error');

		return '';
	}

	$networks = CachedConferenceApi::getNetworks();
//	if (!LoggedInUserDetails::isCrew() && LoggedInUserDetails::isNetworkChair()) {
	if ( SettingsApi::getSetting(SettingsApi::ALLOW_NETWORK_CHAIRS_TO_SEE_ALL_NETWORKS) <> 1 && !LoggedInUserDetails::isCrew() ) {
		$networks = NetworkApi::getOnlyNetworksOfChair($networks, LoggedInUserDetails::getUser());
	}

	$links = array();
	foreach ($networks as $network) {
		$links[] = l($network->getName(),
			SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'proposed' .
			NetworkApi::getNetworkName(true, true) . 'participants/' . $network->getId());
	}

	$output = l('« ' . iish_t('Go back to your personal page'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'personal-page', array('fragment' => 'nclinks')) . '<br /><br />';

	if (count($links) > 0) {
		return $output . theme('item_list',
			array(
				'title' => iish_t('Networks'),
				'type'  => 'ol',
				'attributes' => array( 'class' => 'proposednetworkparticipants' ),
				'items' => $links,
			));
	}
	else {
		drupal_set_message(iish_t('No networks found!'), 'warning');

		return '';
	}
}

/**
 * Returns a list of all proposed papers of participants for the given network
 *
 * @param NetworkApi|null $network The network in question
 *
 * @return string The HTML listing all participants and their papers
 */
function iishconference_proposednetworkparticipants_detail($network) {
	if (!LoggedInUserDetails::isLoggedIn()) {
		// redirect to login page
		header('Location: ' . url(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())));
		die(iish_t('Go to !login page.',
			array('!login' => l(iish_t('login'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())))));
	}

	if (!LoggedInUserDetails::isCrew() && !LoggedInUserDetails::isNetworkChair()) {
		drupal_set_message(iish_t('Access denied. You are not a chair of a network.'), 'error');

		return '';
	}

	if (empty($network)) {
		drupal_set_message(iish_t('The network does not exist.'), 'error');

		drupal_goto(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'proposed' .
			NetworkApi::getNetworkName(true, true) . 'participants');
	}

	$header = theme('iishconference_navigation', array(
		'list'     => CachedConferenceApi::getNetworks(),
		'current'  => $network,
		'prevLink' => l('« ' .
			iish_t('Go back to networks list'),
			SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'proposed' .
			NetworkApi::getNetworkName(true, true) . 'participants/'),
		'curUrl'   => SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'proposed' .
			NetworkApi::getNetworkName(true, true) . 'participants/',
	));

	$chairLinks = array();
	foreach ($network->getChairs() as $chair) {
		$chairLinks[] = l($chair->getFullName(), 'mailto:' . $chair->getEmail(), array('absolute' => true));
	}

	$title = theme('iishconference_container_field', array(
		'label' => 'Network',
		'value' => $network->getName(),
	));
	$title .= theme('iishconference_container_field', array(
		'label'       => 'Chairs in this network',
		'value'       => ConferenceMisc::getEnumSingleLine($chairLinks),
		'valueIsHTML' => true,
	));

	$title .= '<br />';

	$participantsInProposedNetworkApi = new ParticipantsInProposedNetworkApi();
	$participantsInProposedNetwork = $participantsInProposedNetworkApi->getParticipantsInProposedNetwork($network);
	$participantData = array();
	foreach ($participantsInProposedNetwork as $participant) {
		$user = $participant['user'];
		$paper = $participant['paper'];
		$session = $participant['session'];

		$result = theme('iishconference_container_field', array(
			'label'       => '',
			'value'       => l($user->getFullName(), 'mailto:' . $user->getEmail(), array('absolute' => true)),
			'valueIsHTML' => true,
		));
		$result .= theme('iishconference_container_field', array(
			'label'       => '',
			'value'       => (($user->getOrganisation() !== null) && (strlen(trim($user->getOrganisation())) > 0)) ?
				$user->getOrganisation() :
				'<em>(' . iish_t('Unknown affiliation') . ')</em>',
			'valueIsHTML' => ($user->getOrganisation() === null),
		));
		$result .= theme('iishconference_container_field', array(
			'label' => 'Paper name',
			'value' => $paper->getTitle(),
		));

		if (($paper->getCoAuthors() !== null) && (strlen(trim($paper->getCoAuthors())) > 0)) {
			$result .= theme('iishconference_container_field', array(
				'label' => 'Co-authors',
				'value' => $paper->getCoAuthors(),
			));
		}

		$result .= theme('iishconference_container_field', array(
			'label' => 'Paper state',
			'value' => $paper->getState(),
		));
		$result .= theme('iishconference_container_field', array(
			'label'       => 'Session name',
			'value'       => ($session !== null) ? $session->getName() : '<em>(' . iish_t('No session yet') . ')</em>',
			'valueIsHTML' => ($session === null),
		));
		$result .= theme('iishconference_container_field', array(
			'label'          => 'Paper abstract',
			'value'          => ConferenceMisc::getHTMLForLongText($paper->getAbstr()),
			'valueIsHTML'    => true,
			'valueOnNewLine' => true,
		));

		$result .= '<br />';
		$participantData[] = $result;
	}

	$seperator = '<br /><hr /><br />';

	if (count($participantData) > 0) {
		return $header . $title . $seperator . implode($seperator, $participantData);
	}
	else {
		return $header . $title;
	}
}
