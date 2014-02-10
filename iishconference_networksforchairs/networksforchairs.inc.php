<?php

/**
 * Returns a list of networks to choose from
 *
 * @return string The HTML for a list of networks
 */
function iishconference_networksforchairs_main() {
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

	$allNetworks = CachedConferenceApi::getNetworks();
	$output = '';

	if (LoggedInUserDetails::isNetworkChair()) {
		$networks = NetworkApi::getOnlyNetworksOfChair($allNetworks, LoggedInUserDetails::getUser());
		$links = array();
		foreach ($networks as $network) {
			$links[] = l($network->getName(), getSetting('pathForMenu') . 'networksforchairs/' . $network->getId());
		}

		$output .= theme('item_list', array(
			'title' => t('Your network(s)'),
			'items' => $links,
		));
	}

	$links = array();
	foreach ($allNetworks as $network) {
		$links[] = l($network->getName(), getSetting('pathForMenu') . 'networksforchairs/' . $network->getId());
	}

	$output .= theme('item_list', array(
		'title' => t('All network(s)'),
		'items' => $links,
	));

	return $output;
}

/**
 * Returns a list of session in the chosen network to choose from
 *
 * @param int $networkId The chosen network id
 *
 * @return string The HTML for a list of sessions
 */
function iishconference_networksforchairs_sessions($networkId) {
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

	if (!$network) {
		drupal_set_message(t('The network does not exist.'), 'error');

		return '';
	}

	$header = theme('iishconference_navigation', array(
		'list'     => CachedConferenceApi::getNetworks(),
		'current'  => $network,
		'prevLink' => l('« ' . t('Go back to networks list'), getSetting('pathForMenu') . 'networksforchairs'),
		'curUrl'   => getSetting('pathForMenu') . 'networksforchairs/',
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

	$props = new ApiCriteriaBuilder();
	$sessions = SessionApi::getListWithCriteria(
		$props
			->eq('networks_id', $network->getId())
			->sort('name', 'asc')
			->get()
	)->getResults();

	$links = array();
	foreach ($sessions as $session) {
		$links[] = l($session->getName(),
				getSetting('pathForMenu') . 'networksforchairs/' . $network->getId() . '/' . $session->getId()) .
			' <em>(' . $session->getState()->getSimpleDescription() .
			')</em>';
	}
	$links[] = l(t('... Individual paper proposals ...'),
		getSetting('pathForMenu') . 'networksforchairs/' . $network->getId() . '/-1');

	$sessionLinks = theme('item_list', array(
		'title' => t('Sessions'),
		'type'  => 'ol',
		'items' => $links,
	));

	return $header . $title . '<br /><hr /><br />' . $sessionLinks;
}

/**
 * Returns all participants and papers in the given session
 *
 * @param int $networkId The chosen network id
 * @param int $sessionId The chosen session id
 *
 * @return string The HTML listing the participants and their papers
 */
function iishconference_networksforchairs_papers($networkId, $sessionId) {
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

	$sessionId = EasyProtection::easyIntegerProtection($sessionId);
	$session = null;
	if ($sessionId > 0) {
		$session = CRUDApiMisc::getById(new SessionApi(), $sessionId);
	}

	if (!$network || ($session && !in_array($network->getId(), $session->getNetworksId()))) {
		drupal_set_message(t('The network and/or session do not exist'), 'error');

		return '';
	}

	$props = new ApiCriteriaBuilder();
	$sessions = SessionApi::getListWithCriteria(
		$props
			->eq('networks_id', $network->getId())
			->sort('name', 'asc')
			->get()
	)->getResults();
	$sessions[] = new EmptyApi();

	$header = theme('iishconference_navigation', array(
		'list'     => $sessions,
		'current'  => ($session === null) ? new EmptyApi() : $session,
		'prevLink' => l('« ' . t('Go back to sessions list'),
			getSetting('pathForMenu') . 'networksforchairs/' . $network->getId()),
		'curUrl'   => getSetting('pathForMenu') . 'networksforchairs/' . $network->getId() . '/',
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
	$title .= '<br />';
	$title .= theme('iishconference_container_field', array(
		'label' => t('Session'),
		'value' => ($session === null) ? t('... Individual paper proposals ...') : $session->getName(),
	));

	if ($session !== null) {
		$title .= theme('iishconference_container_field', array(
			'label' => t('Session state'),
			'value' => $session->getState()->getDescription(),
		));

		if ($session->getAddedBy() !== null) {
			$title .= theme('iishconference_container_field', array(
				'label'       => t('Session added by'),
				'value'       => l($session->getAddedBy()->getFullName(),
					'mailto:' . $session->getAddedBy()->getEmail(),
					array('absolute' => true)),
				'valueIsHTML' => true,
			));
		}

		$title .= theme('iishconference_container_field', array(
			'label'          => t('Session abstract'),
			'value'          => $session->getAbstr(),
			'valueOnNewLine' => true,
		));
	}

	$title .= '<br />';

	$participantsInSessionApi = new ParticipantsInSessionApi();
	$participantsInSession = $participantsInSessionApi->getParticipantsForSession($network, $session);
	$participantData = array();
	foreach ($participantsInSession as $participant) {
		$user = $participant['user'];
		$paper = $participant['paper'];
		$type = $participant['type'];

		$result = theme('iishconference_container_field', array(
			'label'       => t('Participant'),
			'value'       => l($user->getFullName(), 'mailto:' . $user->getEmail(), array('absolute' => true)),
			'valueIsHTML' => true,
		));
		$result .= theme('iishconference_container_field', array(
			'label' => t('Organisation'),
			'value' => $user->getOrganisation(),
		));

		if ($type) {
			$result .= theme('iishconference_container_field', array(
				'label' => t('Type'),
				'value' => $type->getType(),
			));
		}

		if ($paper) {
			$result .= '<br />';
			$result .= theme('iishconference_container_field', array(
				'label' => t('Paper'),
				'value' => $paper->getTitle(),
			));
			$result .= theme('iishconference_container_field', array(
				'label' => t('Paper state'),
				'value' => $paper->getState(),
			));
			$result .= theme('iishconference_container_field', array(
				'label'          => t('Paper abstract'),
				'value'          => $paper->getAbstr(),
				'valueOnNewLine' => true,
			));
		}

		$result .= '<br />';
		$participantData[] = $result;
	}

	$seperator = '<br/><hr /><br/>';

	if (count($participantData) > 0) {
		return $header . $title . $seperator . implode($seperator, $participantData);
	}
	else {
		return $header . $title;
	}
}