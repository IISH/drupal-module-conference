<?php

/**
 * Main entry point for the election advisory board page
 *
 * @return array|mixed|string The election form
 */
function iishconference_electionadvisory_main() {
	if (!LoggedInUserDetails::isLoggedIn()) {
		// redirect to login page
		header('Location: ' .
		url(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login', array('query' => drupal_get_destination())));
		die(iish_t('Go to !login page.',
			array('!login' => l(iish_t('login'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())))));
	}

	if (!LoggedInUserDetails::isCrew() && !LoggedInUserDetails::isNetworkChair()) {
		$networkName = NetworkApi::getNetworkName(true, true);
		drupal_set_message(iish_t('Access denied. You are not a chair of a @network.', array('@network' => $networkName)),
			'error');

		return '';
	}

	$props = new ApiCriteriaBuilder();
	$hasVotedCount = NetworkChairApi::getListWithCriteria(
		$props
			->eq('chair_id', LoggedInUserDetails::getId())
			->eq('votedAdvisoryBoard', true)
			->get()
	)->getTotalSize();

	if ($hasVotedCount > 0) {
		drupal_set_message(iish_t('You already voted for the advisory board!'), 'warning');

		return '';
	}

	return drupal_get_form('iishconference_electionadvisory_form');
}

/**
 * Implements hook_form()
 */
function iishconference_electionadvisory_form($form, &$form_state) {
	$ecaSettings = CachedConferenceApi::getSettings();
	$nrChoices = intval($ecaSettings[SettingsApi::NUM_CANDIDATE_VOTES_ADVISORY_BOARD]);

	$candidates = ElectionsAdvisoryBoardApi::getListWithCriteria(array())->getResults();
	$candidatesKeyValue = CRUDApiClient::getAsKeyValueArray($candidates);

	$form['candidates'] = array(
		'#title'       => iish_t('Candidates'),
		'#type'        => 'checkboxes',
		'#description' => iish_t('Please vote for @nrChoices persons for the election board.',
			array('@nrChoices' => $nrChoices)),
		'#options'     => $candidatesKeyValue,
		'#required'    => true,
	);

	$form['submit-votes'] = array(
		'#type'  => 'submit',
		'#name'  => 'submit-votes',
		'#value' => iish_t('Submit votes'),
	);

	return $form;
}

/**
 * Implements hook_form_validate()
 */
function iishconference_electionadvisory_form_validate($form, &$form_state) {
	$ecaSettings = CachedConferenceApi::getSettings();
	$nrChoices = intval($ecaSettings[SettingsApi::NUM_CANDIDATE_VOTES_ADVISORY_BOARD]);

	$count = 0;
	foreach ($form_state['values']['candidates'] as $candidateId => $candidateValue) {
		$count = ($candidateId == $candidateValue) ? $count + 1 : $count;
	}

	if ($count !== $nrChoices) {
		form_set_error('candidates', iish_t('Make sure to vote for exactly @nrChoices persons for the election board.',
			array('@nrChoices' => $nrChoices)));
	}
}

/**
 * Implements hook_form_submit()
 */
function iishconference_electionadvisory_form_submit($form, &$form_state) {
	$candidates = ElectionsAdvisoryBoardApi::getListWithCriteria(array())->getResults();

	// Increase the number of votes for the candidates voted for by the network chair
	foreach ($form_state['values']['candidates'] as $candidateId => $candidateValue) {
		if ($candidateId == $candidateValue) {
			foreach ($candidates as $candidate) {
				if ($candidate->getId() == $candidateId) {
					$candidate->vote();
					$candidate->save();
				}
			}
		}
	}

	// Indicate that the chair has made its vote for the advisory board
	$chairs = CRUDApiMisc::getAllWherePropertyEquals(new NetworkChairApi(), 'chair_id', LoggedInUserDetails::getId())
		->getResults();
	foreach ($chairs as $chair) {
		$chair->setVotedAdvisoryBoard(true);
		$chair->save();
	}

	// Redirect back to the personal page
	drupal_set_message('Thank you for your vote!', 'status');
	drupal_goto(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'personal-page');
}