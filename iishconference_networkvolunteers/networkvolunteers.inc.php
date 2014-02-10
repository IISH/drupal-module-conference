<?php

/**
 * Returns a list of networks to choose from
 *
 * @return string The HTML for a list of networks
 */
function iishconference_networkvolunteers_main() {
	if (!LoggedInUserDetails::isLoggedIn()) {
		// redirect to login page
		Header("Location: /" . getSetting('pathForMenu') . "login/?backurl=" . urlencode($_SERVER["REQUEST_URI"]));
		die('Go to <a href="/' . getSetting('pathForMenu') . 'login/?backurl=' . urlencode($_SERVER["REQUEST_URI"]) .
			'">login</a> page.');
	}

	if (!LoggedInUserDetails::isCrew() && !LoggedInUserDetails::isNetworkChair()) {
		drupal_set_message(t('Access denied. You are not a network chair.'), 'error');

		return '';
	}

	$networks = iishconference_networkvolunteers_get_networks();
	$links = array();
	foreach ($networks as $network) {
		$links[] = l($network->getName(), getSetting('pathForMenu') . 'networkvolunteers/' . $network->getId());
	}

	if (count($links) > 0) {
		return theme('item_list', array(
			'title' => t('Your network(s)'),
			'items' => $links,
		));
	}
	else {
		drupal_set_message(t('No networks found!'), 'warning');

		return '';
	}
}

/**
 * Returns tables with all participant volunteers of the given network
 *
 * @param int $networkId The network in question
 *
 * @return string The HTML for the page
 */
function iishconference_networkvolunteers_detail($networkId) {
	if (!LoggedInUserDetails::isLoggedIn()) {
		// redirect to login page
		Header("Location: /" . getSetting('pathForMenu') . "login/?backurl=" . urlencode($_SERVER["REQUEST_URI"]));
		die('Go to <a href="/' . getSetting('pathForMenu') . 'login/?backurl=' . urlencode($_SERVER["REQUEST_URI"]) .
			'">login</a> page.');
	}

	if (!LoggedInUserDetails::isCrew() && !LoggedInUserDetails::isNetworkChair()) {
		drupal_set_message(t('Access denied. You are not a network chair.'), 'error');

		return '';
	}

	$networkId = EasyProtection::easyIntegerProtection($networkId);
	$network = CRUDApiMisc::getById(new NetworkApi(), $networkId);

	$header = theme('iishconference_navigation', array(
		'list'     => iishconference_networkvolunteers_get_networks(),
		'current'  => $network,
		'prevLink' => l('« ' . t('Go back to networks list'), getSetting('pathForMenu') . 'networkvolunteers'),
		'curUrl'   => getSetting('pathForMenu') . 'networkvolunteers/',
	));

	$chairLinks = array();
	foreach ($network->getChairs() as $chair) {
		$chairLinks[] = l($chair->getFullName(), 'mailto:' . $chair->getEmail(), array('absolute' => true));
	}

	$title = theme('iishconference_container_field', array(
		'label' => t('Network'),
		'value' => $network->getName(),
	));
	$title .= theme('iishconference_container_field', array(
		'label'       => t('Network chairs'),
		'value'       => ConferenceMisc::getEnumSingleLine($chairLinks),
		'valueIsHTML' => true,
	));

	$volunteers = array();
	foreach (CachedConferenceApi::getVolunteering() as $volunteering) {
		$volunteers[] = iishconference_networkvolunteers_listofparticipants_details($volunteering, $network);
	}

	$seperator = '<br/><hr /><br/>';

	return $header . $title . $seperator . implode($seperator, $volunteers);
}

/**
 * Returns the participant details for a given volunteering type and network
 *
 * @param VolunteeringApi $volunteering Volunteering type in question
 * @param NetworkApi      $network      The network in question
 *
 * @return string The HTML
 */
function iishconference_networkvolunteers_listofparticipants_details($volunteering, $network) {
	$header = theme('iishconference_container_header', array(
		'text' => t('@name volunteers', array('@name' => $volunteering->getDescription())),
	));

	$props = new ApiCriteriaBuilder();
	$participantVolunteering = ParticipantVolunteeringApi::getListWithCriteria(
		$props
			->eq('volunteering_id', $volunteering->getId())
			->eq('network_id', $network->getId())
			->get()
	)->getResults();

	CRUDApiClient::sort($participantVolunteering);

	$rows = array();
	foreach ($participantVolunteering as $participantVolunteer) {
		$rows[] = array(
			array('data' => $participantVolunteer->getUser()->getLastName()),
			array('data' => $participantVolunteer->getUser()->getFirstName()),
			array('data' => l($participantVolunteer->getUser()->getEmail(),
				'mailto:' . $participantVolunteer->getUser()->getEmail(),
				array('absolute' => true))),
			array('data' => $participantVolunteer->getUser()->getOrganisation()),
		);
	}

	return $header . theme_table(
		array(
			"header"     => array(
				array('data' => t('Last name')),
				array('data' => t('First name')),
				array('data' => t('E-mail')),
				array('data' => t('Organisation')),
			),
			"rows"       => $rows,
			"attributes" => array(),
			"sticky"     => true,
			"caption"    => null,
			"colgroups"  => array(),
			"empty"      => t('No volunteers found!'),
		)
	);
}

/**
 * Returns all networks, or only those the network chair is chair of
 *
 * @return NetworkApi[] Returns the networks
 */
function iishconference_networkvolunteers_get_networks() {
	$networks = CachedConferenceApi::getNetworks();
	if (LoggedInUserDetails::isChair()) {
		$networks = NetworkApi::getOnlyNetworksOfChair($networks, LoggedInUserDetails::getUser());
	}

	return $networks;
}