<?php

/**
 * Returns a list of networks to choose from
 *
 * @return string The HTML for a list of networks
 */
function iishconference_networkparticipants_main() {
	if (!LoggedInUserDetails::isLoggedIn()) {
		// redirect to login page
		header('Location: ' . url(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())));
		die(iish_t('Go to !login page.',
			array('!login' => l(iish_t('login'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU)
				. 'login', array('query' => drupal_get_destination())))));
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
			SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) .
			NetworkApi::getNetworkName(false, true) . 'participants/' . $network->getId()) . ' (xls)';
	}

	$output = l('« ' . iish_t('Go back to your personal page'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'personal-page', array('fragment' => 'nclinks')) . '<br /><br />';

	if (count($links) > 0) {
		return $output . theme('item_list',
			array(
				'title' => iish_t('Networks'),
				'type'  => 'ol',
				'attributes' => array( 'class' => 'networkparticipants' ),
				'items' => $links,
			));
	}
	else {
		drupal_set_message(iish_t('No networks found!'), 'warning');

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
		header('Location: ' . url(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())));
		die(iish_t('Go to !login page.', array('!login' => l(iish_t('login'),
			SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
			array('query' => drupal_get_destination())))));
	}

	if (!LoggedInUserDetails::isCrew() && !LoggedInUserDetails::isNetworkChair()) {
		drupal_set_message(iish_t('Access denied. You are not a chair of a network.'), 'error');

		return '';
	}

	if (!empty($network)) {
		$networkName = EasyProtection::easyAlphaNumericStringProtection($network->getName());
		$participantsInNetworkApi = new ParticipantsInNetworkApi();
		if ($participants = $participantsInNetworkApi->getParticipantsForNetwork($network, true)) {
			drupal_add_http_header('Pragma', 'public');
			drupal_add_http_header('Expires', '0');
			drupal_add_http_header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
			drupal_add_http_header('Content-Type', 'application/vnd.ms-excel');
			drupal_add_http_header('Content-Disposition',
				'attachment; filename=' .
				iish_t('Participants in network @name on @date',
					array('@name' => $networkName, '@date' => date('m-d-Y'))) . '.xls;');
			drupal_add_http_header('Content-Transfer-Encoding', 'binary');
			drupal_add_http_header('Content-Length', strlen($participants));

			echo $participants;
			drupal_exit();
		}
	}

	drupal_set_message(iish_t('Failed to create an excel file for download.'), 'error');
	drupal_goto(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) .
		NetworkApi::getNetworkName(false, true) . 'participants');
}
