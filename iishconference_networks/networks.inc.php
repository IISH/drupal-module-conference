<?php

/**
 * Creates a page that lists all networks
 *
 * @return string The HTML that displays all networks
 */
function iishconference_networks() {
	$networks = CachedConferenceApi::getNetworks();

	return theme('conference_networks', array('networks' => $networks));
}

/**
 * Creates a page that displays the details of a network
 *
 * @param NetworkApi|null $network The network to display
 *
 * @return string The HTML that displays the network details
 */
function iishconference_network_detail($network) {
	if (!empty($network)) {
		return theme('conference_network_detail', array('network' => $network));
	}

	drupal_set_message(t('The @network could unfortunately not be found!',
		array('@network' => NetworkApi::getNetworkName(true, true))), 'error');

	drupal_goto(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . NetworkApi::getNetworkName(false, true));
}