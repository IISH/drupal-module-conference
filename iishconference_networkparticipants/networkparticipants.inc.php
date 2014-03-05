<?php

/**
 * Returns a list of networks to choose from
 *
 * @return string The HTML for a list of networks
 */
function iishconference_networkparticipants_main() {
	if (!LoggedInUserDetails::isLoggedIn()) {
		// redirect to login page
		header('Location: ' . url(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login', array('query' => drupal_get_destination())));
		die(t('Go to !login page.', array('!login' => l(t('login'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
			array('query' => drupal_get_destination())))));
	}

	if (!LoggedInUserDetails::isCrew() && !LoggedInUserDetails::isNetworkChair()) {
		drupal_set_message(t('Access denied. You are not a network chair.'), 'error');

		return '';
	}

	$networks = CachedConferenceApi::getNetworks();
	if (LoggedInUserDetails::isChair()) {
		$networks = NetworkApi::getOnlyNetworksOfChair($networks, LoggedInUserDetails::getUser());
	}

	$links = array();
	foreach ($networks as $network) {
		$links[] = l($network->getName(), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'networkparticipants/' . $network->getId());
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
 * Returns an Excel file with all participants of the given network
 *
 * @param NetworkApi|null $network The network in question
 *
 * @return mixed The download, or else an error message
 */
function iishconference_networkparticipants_detail($network) {
	if (!LoggedInUserDetails::isLoggedIn()) {
		// redirect to login page
		header('Location: ' . url(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login', array('query' => drupal_get_destination())));
		die(t('Go to !login page.', array('!login' => l(t('login'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
			array('query' => drupal_get_destination())))));
	}

	if (!LoggedInUserDetails::isCrew() && !LoggedInUserDetails::isNetworkChair()) {
		drupal_set_message(t("Access denied. You are not a network chair."), 'error');

		return '';
	}

	if ($network !== null) {
		$networkName = EasyProtection::easyAlphaNumericStringProtection($network->getName());
		$participantsInNetworkApi = new ParticipantsInNetworkApi();
		if ($participants = $participantsInNetworkApi->getParticipantsForNetwork($network, true)) {
			drupal_add_http_header('Pragma', 'public');
			drupal_add_http_header('Expires', '0');
			drupal_add_http_header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
			drupal_add_http_header('Content-Type', 'application/vnd.ms-excel');
			drupal_add_http_header('Content-Disposition', 'attachment; filename=' .
				t('Participants in network @network on @date',
					array('@network' => $networkName, '@date' => date('m-d-Y'))) . '.xls;');
			drupal_add_http_header('Content-Transfer-Encoding', 'binary');
			drupal_add_http_header('Content-Length', strlen($participants));

			echo $participants;
			drupal_exit();
		}
	}

	drupal_set_message(t('Failed to create an excel file for download.'), 'error');

	return '';
}
