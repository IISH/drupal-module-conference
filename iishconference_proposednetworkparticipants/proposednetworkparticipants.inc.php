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
		die(t('Go to !login page.',
			array('!login' => l(t('login'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())))));
	}

	if (!LoggedInUserDetails::isCrew() && !LoggedInUserDetails::isNetworkChair()) {
		drupal_set_message(t('Access denied. You are not a chair of a @network.',
			array('@network' => NetworkApi::getNetworkName(true, true))), 'error');

		return '';
	}

	$allNetworks = CachedConferenceApi::getNetworks();
	$output = '';

	if (!LoggedInUserDetails::isCrew() && LoggedInUserDetails::isNetworkChair()) {
		$networks = NetworkApi::getOnlyNetworksOfChair($allNetworks, LoggedInUserDetails::getUser());
		$links = array();
		foreach ($networks as $network) {
			$links[] =
				l($network->getName(),
					SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'proposed' .
					NetworkApi::getNetworkName(true, true) . 'participants/' .
					$network->getId());
		}

		$output .= theme('item_list', array(
			'title' => t('Your @networks', array('@networks' => NetworkApi::getNetworkName(false, true))),
			'items' => $links,
		));
	}

	$links = array();
	foreach ($allNetworks as $network) {
		$links[] =
			l($network->getName(),
				SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'proposed' .
				NetworkApi::getNetworkName(true, true) . 'participants/' .
				$network->getId());
	}

	$output .= theme('item_list', array(
		'title' => t('All @networks', array('@networks' => NetworkApi::getNetworkName(false, true))),
		'items' => $links,
	));

	return $output;
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
		die(t('Go to !login page.',
			array('!login' => l(t('login'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())))));
	}

	if (!LoggedInUserDetails::isCrew() && !LoggedInUserDetails::isNetworkChair()) {
		drupal_set_message(t('Access denied. You are not a chair of a @network.',
			array('@network' => NetworkApi::getNetworkName(true, true))), 'error');

		return '';
	}

	if (empty($network)) {
		drupal_set_message(t('The @network does not exist.',
			array('@network' => NetworkApi::getNetworkName(true, true))), 'error');

		drupal_goto(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'proposed' .
			NetworkApi::getNetworkName(true, true) . 'participants');
	}

	$header = theme('iishconference_navigation', array(
		'list'     => CachedConferenceApi::getNetworks(),
		'current'  => $network,
		'prevLink' => l('« ' .
			t('Go back to @networks list', array('@networks' => NetworkApi::getNetworkName(false, true))),
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
		'label' => NetworkApi::getNetworkName(),
		'value' => $network->getName(),
	));
	$title .= theme('iishconference_container_field', array(
		'label'       => t('Chairs in this @network', array('@network' => NetworkApi::getNetworkName(true, true))),
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
					'<em>(' . t('Unknown affiliation') . ')</em>',
			'valueIsHTML' => ($user->getOrganisation() === null),
		));
		$result .= theme('iishconference_container_field', array(
			'label' => t('Paper name'),
			'value' => $paper->getTitle(),
		));

		if (($paper->getCoAuthors() !== null) && (strlen(trim($paper->getCoAuthors())) > 0)) {
			$result .= theme('iishconference_container_field', array(
				'label' => t('Co-authors'),
				'value' => $paper->getCoAuthors(),
			));
		}

		$result .= theme('iishconference_container_field', array(
			'label' => t('Paper state'),
			'value' => $paper->getState(),
		));
		$result .= theme('iishconference_container_field', array(
			'label'       => t('Session name'),
			'value'       => ($session !== null) ? $session->getName() : '<em>(' . t('No session yet') . ')</em>',
			'valueIsHTML' => ($session === null),
		));
		$result .= theme('iishconference_container_field', array(
			'label'          => t('Paper abstract'),
			'value'          => ConferenceMisc::getFirstPartOfText($paper->getAbstr()),
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
