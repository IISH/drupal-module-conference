<?php

/**
 * Returns a list of networks to choose from
 *
 * @return string The HTML for a list of networks
 */
function iishconference_networksforchairs_main() {
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

	$params = drupal_get_query_parameters();
	$search = (isset($params['search']) && (strlen(trim($params['search'])) > 0)) ?
		EasyProtection::easyStringProtection($params['search']) : null;
	if ($search !== null) {
		drupal_goto(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) .
			NetworkApi::getNetworkName(false, true) . 'forchairs/-1',
			array('query' => array('search' => $search)));
	}

	$form = drupal_get_form('iishconference_networksforchairs_form');
	$output = '<div class="iishconference_container_inline">';
	$output .= l('« ' . iish_t('Go back to your personal page'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'personal-page', array('fragment' => 'nclinks')) . '<br /><br />';
	$output .= render($form) . '</div>';

	$networks = CachedConferenceApi::getNetworks();
	//if (!LoggedInUserDetails::isCrew() && LoggedInUserDetails::isNetworkChair()) {
	if ( SettingsApi::getSetting(SettingsApi::ALLOW_NETWORK_CHAIRS_TO_SEE_ALL_NETWORKS) <> 1 && !LoggedInUserDetails::isCrew() ) {
		$networks = NetworkApi::getOnlyNetworksOfChair($networks, LoggedInUserDetails::getUser());
	}

	$links = array();
	foreach ($networks as $network) {
		$links[] = l($network->getName(),
			SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) .
			NetworkApi::getNetworkName(false, true) . 'forchairs/' . $network->getId());
	}

	$output .= theme('item_list', array(
		'title' => iish_t('Networks'),
		'type'  => 'ol',
		'attributes' => array( 'class' => 'networksforchairs' ),
		'items' => $links,
	));

	if (count($links) > 0) {
		return $output;
	}
	else {
		drupal_set_message(iish_t('No networks found!'), 'warning');

		return '';
	}
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

	$params = drupal_get_query_parameters();
	$network = null;
	$search = null;
	$title = '';
	$highlighter = null;
	if (isset($params['search']) && (strlen(trim($params['search'])) > 0)) {
		$search = EasyProtection::easyStringProtection($params['search']);

		$highlighter = new Highlighter(explode(' ', $search));
		$highlighter->setOpeningTag('<span class="highlight">');
		$highlighter->setClosingTag('</span>');
	}
	else {
		$networkId = EasyProtection::easyIntegerProtection($networkId, true);
		$network = CRUDApiMisc::getById(new NetworkApi(), $networkId);

		if (!$network) {
			drupal_set_message(iish_t('The network does not exist.'), 'error');

			drupal_goto(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . NetworkApi::getNetworkName(false, true)
				. 'forchairs');
		}

		$title = theme('iishconference_container_field', array(
			'label' => 'Network',
			'value' => $network->getName(),
		));

		if (SettingsApi::getSetting(SettingsApi::NETWORKSFORCHAIRS_SHOWNETWORKCHAIRS) == 1) {
			$chairLinks = array();
			foreach ($network->getChairs() as $chair) {
				$chairLinks[] = l($chair->getFullName(), 'mailto:' . $chair->getEmail(), array('absolute' => true));
			}

			$title .= theme('iishconference_container_field', array(
				'label'       => 'Chairs in this network',
				'value'       => ConferenceMisc::getEnumSingleLine($chairLinks),
				'valueIsHTML' => true,
			));
		}
	}

	if ($network !== null) {
		$props = new ApiCriteriaBuilder();
		$sessions = SessionApi::getListWithCriteria(
			$props
				->eq('networks_id', $network->getId())
				->sort('name', 'asc')
				->get()
		)->getResults();
	}
	else {
		$sessionSearchApi = new SessionsSearchApi();
		$sessions = $sessionSearchApi->getSessions($search);
	}

	$links = array();
	foreach ($sessions as $session) {
		$name = $session->getName();
		if ($search && $highlighter) {
			$name = $highlighter->highlight($name);
		}

		$links[] = l($name,
				SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . NetworkApi::getNetworkName(false, true) .	'forchairs/' . $networkId . '/' . $session->getId(),
				array('query' => array('search' => $search), 'html' => true)
			) .
			' <em>(' . $session->getState()->getSimpleDescription() . ')</em>';
	}

	if ($network !== null) {
		$links[] = l(iish_t('... Individual paper proposals ...'),
			SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) .
			NetworkApi::getNetworkName(false, true) . 'forchairs/' . $networkId . '/-1');
	}

	$header = theme('iishconference_navigation', array(
		'list'     => CachedConferenceApi::getNetworks(),
		'current'  => $network,
		'prevLink' => l('« ' . iish_t('Go back to networks list'),
			SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) .
			NetworkApi::getNetworkName(false, true) .
			'forchairs'),
		'curUrl'   =>
			SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) .
			NetworkApi::getNetworkName(false, true) .
			'forchairs/',
	));

	$hrLine = '';
	if (strlen($title) > 0) {
		$hrLine = '<br /><hr /><br />';
	}

	$sessionLinks = theme('item_list', array(
		'title' => iish_t('Sessions'),
		'type'  => 'ol',
		'attributes' => array( 'class' => 'networksforchairs' ),
		'items' => $links,
	));

	return $header . $title . $hrLine . $sessionLinks;
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

	$networkId = EasyProtection::easyIntegerProtection($networkId, true);
	$network = null;
	if ($networkId > 0) {
		$network = CRUDApiMisc::getById(new NetworkApi(), $networkId);
	}

	$sessionId = EasyProtection::easyIntegerProtection($sessionId, true);
	$session = null;
	if ($sessionId > 0) {
		$session = CRUDApiMisc::getById(new SessionApi(), $sessionId);
	}

	$params = drupal_get_query_parameters();
	$search = (isset($params['search']) && (strlen(trim($params['search'])) > 0)) ?
		EasyProtection::easyStringProtection($params['search']) : null;

	// Show error only if there is a network id given and the session does not belong in the network
	// or the network and/or session do not exist
	// Also show error when no network is chosen, but neither is a session search term
	if (($networkId > 0) && (!$network || ($session && !in_array($network->getId(), $session->getNetworksId())))) {
		drupal_set_message(iish_t('The network and/or session do not exist!'), 'error');

		drupal_goto(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . NetworkApi::getNetworkName(false, true)
			. 'forchairs');
	}
	else if (($networkId <= 0) && ($search === null)) {
		drupal_set_message(iish_t('No network or search parameter given!'), 'error');

		drupal_goto(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . NetworkApi::getNetworkName(false, true)
			. 'forchairs');
	}

	if ($network) {
		$props = new ApiCriteriaBuilder();
		$sessions = SessionApi::getListWithCriteria(
			$props
				->eq('networks_id', $network->getId())
				->sort('name', 'asc')
				->get()
		)->getResults();
		$sessions[] = new EmptyApi();
	}
	else {
		$sessionSearchApi = new SessionsSearchApi();
		$sessions = $sessionSearchApi->getSessions($search);
	}

	$header = theme('iishconference_navigation', array(
		'list'     => $sessions,
		'current'  => ($session === null) ? new EmptyApi() : $session,
		'prevLink' => l('« ' . iish_t('Go back to sessions list'),
			SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) .
			NetworkApi::getNetworkName(false, true) . 'forchairs/' . $networkId,
			array('query' => array('search' => $search))),
		'curUrl'   => SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) .
			NetworkApi::getNetworkName(false, true) . 'forchairs/' . $networkId . '/',
		'curQuery' => array('search' => $search),
	));

	$title = '';
	if ($network !== null) {

		$title = theme('iishconference_container_field', array(
			'label' => 'Network',
			'value' => $network->getName(),
		));

		if (SettingsApi::getSetting(SettingsApi::NETWORKSFORCHAIRS_SHOWNETWORKCHAIRS) == 1) {
			$chairLinks = array();
			foreach ($network->getChairs() as $chair) {
				$chairLinks[] = l($chair->getFullName(), 'mailto:' . $chair->getEmail(), array('absolute' => true));
			}

			$title .= theme('iishconference_container_field', array(
				'label'       => 'Chairs in this network',
				'value'       => ConferenceMisc::getEnumSingleLine($chairLinks),
				'valueIsHTML' => true,
			));
		}

		$title .= '<br />';
	}

	$title .= theme('iishconference_container_field', array(
		'label' => 'Session',
		'value' => ($session === null) ? iish_t('... Individual paper proposals ...') : $session->getName(),
	));

	if ($session !== null) {
		$title .= theme('iishconference_container_field', array(
			'label' => 'Session state',
			'value' => iish_t( $session->getState()->getDescription() ),
		));

		if ($session->getAddedBy() !== null) {
			$title .= theme('iishconference_container_field', array(
				'label'       => 'Session added by',
				'value'       => l($session->getAddedBy()->getFullName(),
					'mailto:' . $session->getAddedBy()->getEmail(),
					array('absolute' => true)),
				'valueIsHTML' => true,
			));
		}

		$title .= theme('iishconference_container_field', array(
			'label'          => 'Session abstract',
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
		$participant_date = $participant['participantDate'];

		$result = theme('iishconference_container_field', array(
			'label'       => 'Participant name',
			'value'       => l($user->getFullName(), 'mailto:' . $user->getEmail(), array('absolute' => true)),
			'valueIsHTML' => true,
		));

		#
		if (SettingsApi::getSetting(SettingsApi::NETWORKSFORCHAIRS_SHOWPARTICIPANTSTATE) == 1) {
			$state_pre_style = ( ( $participant_date->getStateId() == 0 || $participant_date->getStateId() == 999 ) ? '<span class="eca_warning">' : '');
			$state_post_style = ( ( $participant_date->getStateId() == 0 || $participant_date->getStateId() == 999 ) ? '</span>' : '');

			$result .= theme('iishconference_container_field', array(
				'label'       => 'Participant state',
				'value'       =>  $state_pre_style . SettingsApi::getSetting('participantstate' . $participant_date->getStateId()) . $state_post_style,
				'valueIsHTML' => true,
			));
		}

		if (($user->getOrganisation() !== null) && (strlen($user->getOrganisation()) > 0)) {
			$result .= theme('iishconference_container_field', array(
				'label' => 'Organisation',
				'value' => $user->getOrganisation(),
			));
		}

		if ((SettingsApi::getSetting(SettingsApi::SHOW_CV) == 1) && ($user->getCv() !== null) &&
			(strlen($user->getCv()) > 0)
		) {
			$result .= theme('iishconference_container_field', array(
				'label'          => 'CV',
				'value'          => $user->getCv(),
				'valueOnNewLine' => true,
			));
		}

		if ($type) {
			$result .= theme('iishconference_container_field', array(
				'label' => 'Type',
				'value' => $type->getType(),
			));
		}

		// show if type is 'with paper' of if session id lower then 0 (individual paper)
		if ($paper && ( $type && $type->getWithPaper() || $sessionId < 0 ) ) {
			$result .= '<br />';
			$result .= theme('iishconference_container_field', array(
				'label' => 'Paper',
				'value' => $paper->getTitle(),
			));
			$result .= theme('iishconference_container_field', array(
				'label' => 'Paper state',
				'value' => $paper->getState(),
			));
			$result .= theme('iishconference_container_field', array(
				'label'          => 'Paper abstract',
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

/**
 * Implements hook_form()
 */
function iishconference_networksforchairs_form($form, &$form_state) {
	$form['#method'] = 'get';
	$form['#token'] = false;
	$form['#after_build'] = array('iishconference_networksforchairs_unset_default_form_elements');
	$form['#attributes']['class'][] = 'iishconference_form';

	$form['search'] = array(
		'#type'      => 'textfield',
		'#title'     => iish_t('Search in session name'),
		'#size'      => 20,
		'#maxlength' => 50,
		'#prefix'    => '<div class="iishconference_inline">',
		'#suffix'    => '</div>',
	);

	$form['btnSubmit'] = array(
		'#type'  => 'submit',
		'#value' => iish_t('Go'),
	);

	return $form;
}

/**
 * Makes sure all unnecessary elements are removed
 */
function iishconference_networksforchairs_unset_default_form_elements($form) {
	unset($form['#build_id'], $form['form_build_id'], $form['form_id'], $form['btnSubmit']['#name']);

	return $form;
}
