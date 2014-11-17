<?php

/**
 * Returns a list of networks to choose from
 *
 * @return string The HTML for a list of networks
 */
function iishconference_networkvolunteers_main() {
	if (!LoggedInUserDetails::isLoggedIn()) {
		// redirect to login page
		header('Location: ' . url(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())));
		die(iish_t('Go to !login page.',
			array('!login' => l(iish_t('login'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())))));
	}

	if (!LoggedInUserDetails::isCrew() && !LoggedInUserDetails::isNetworkChair()) {
		drupal_set_message(iish_t('Access denied. You are not a chair of a @network.',
			array('@network' => NetworkApi::getNetworkName(true, true))), 'error');

		return '';
	}

	$networks = iishconference_networkvolunteers_get_networks();
	$links = array();
	foreach ($networks as $network) {
		$links[] = l($network->getName(),
			SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) .
			NetworkApi::getNetworkName(true, true) .
			'volunteers/' .
			$network->getId());
	}

	if (count($links) > 0) {
		return theme('item_list', array(
			'title' => iish_t('Your @networks', array('@networks' => NetworkApi::getNetworkName(false, true))),
			'items' => $links,
		));
	}
	else {
		drupal_set_message(iish_t('No @networks found!', array('@networks' => NetworkApi::getNetworkName(false, true))),
			'warning');

		return '';
	}
}

/**
 * Returns tables with all participant volunteers of the given network
 *
 * @param NetworkApi|null $network The network in question
 *
 * @return string The HTML for the page
 */
function iishconference_networkvolunteers_detail($network) {
	if (!LoggedInUserDetails::isLoggedIn()) {
		// redirect to login page
		header('Location: ' . url(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())));
		die(iish_t('Go to !login page.',
			array('!login' => l(iish_t('login'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())))));
	}

	if (!LoggedInUserDetails::isCrew() && !LoggedInUserDetails::isNetworkChair()) {
		drupal_set_message(iish_t('Access denied. You are not a chair of a @network.',
			array('@network' => NetworkApi::getNetworkName(true, true))), 'error');

		return '';
	}

	if (empty($network)) {
		drupal_set_message(iish_t('The @network could not be found.',
			array('@network' => NetworkApi::getNetworkName(true, true))), 'error');

		drupal_goto(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . NetworkApi::getNetworkName(true, true) .
			'volunteers');
	}

	$header = theme('iishconference_navigation', array(
		'list'     => iishconference_networkvolunteers_get_networks(),
		'current'  => $network,
		'prevLink' => l('« ' .
			iish_t('Go back to @networks list', array('@networks' => NetworkApi::getNetworkName(false, true))),
			SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . NetworkApi::getNetworkName(true, true) .
			'volunteers'),
		'curUrl'   => SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . NetworkApi::getNetworkName(true, true) .
			'volunteers/',
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
		'label'       => iish_t('Chairs in this @network', array('@network' => NetworkApi::getNetworkName(true, true))),
		'value'       => ConferenceMisc::getEnumSingleLine($chairLinks),
		'valueIsHTML' => true,
	));

	$volunteers = array();
	foreach (CachedConferenceApi::getVolunteering() as $volunteering) {
		// Make sure we only show chair/discussant, language coach/volunteering if allowed, or additional volunteering
		$isChairDiscussant = (in_array($volunteering->getId(), array(VolunteeringApi::CHAIR, VolunteeringApi::DISCUSSANT)) !== false);
		$isLanguage = (in_array($volunteering->getId(), array(VolunteeringApi::COACH, VolunteeringApi::PUPIL)) !== false);

		$showChairDiscussant = (SettingsApi::getSetting(SettingsApi::SHOW_CHAIR_DISCUSSANT_POOL) == 1);
		$showLanguage = (SettingsApi::getSetting(SettingsApi::SHOW_LANGUAGE_COACH_PUPIL) == 1);

		if (($isChairDiscussant && $showChairDiscussant) || ($isLanguage && $showLanguage) || (!$isChairDiscussant && !$isLanguage)) {
			$volunteers[] = iishconference_networkvolunteers_listofparticipants_details($volunteering, $network);
		}
	}

	$seperator = '<br /><hr /><br />';

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
		'text' => iish_t('@name volunteers', array('@name' => $volunteering->getDescription())),
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
				array('data' => iish_t('Last name')),
				array('data' => iish_t('First name')),
				array('data' => iish_t('E-mail')),
				array('data' => iish_t('Organisation')),
			),
			"rows"       => $rows,
			"attributes" => array(),
			"sticky"     => true,
			"caption"    => null,
			"colgroups"  => array(),
			"empty"      => iish_t('No volunteers found!'),
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
	if (!LoggedInUserDetails::isCrew() && LoggedInUserDetails::isNetworkChair()) {
		$networks = NetworkApi::getOnlyNetworksOfChair($networks, LoggedInUserDetails::getUser());
	}

	return $networks;
}