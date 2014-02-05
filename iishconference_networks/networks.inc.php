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
 * @param int $networkId The id of the network to display
 *
 * @return string The HTML that displays the network details
 */
function iishconference_network_detail($networkId) {
	$networkId = EasyProtection::easyIntegerProtection($networkId);
	$networks = CachedConferenceApi::getNetworks();

	foreach ($networks as $network) {
		if ($network->getId() === $networkId) {
			drupal_set_title($network->getName());
			return theme('conference_network_detail', array('network' => $network));
		}
	}

	drupal_set_message(t('The network could unfortunately not be found!'), 'error');

	return '';
}