<?php

/**
 * Implements hook_form()
 */
function preregister_session_form($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$multiPageData = $state->getMultiPageData();
	$session = $multiPageData['session'];
	$data = array();

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// SESSION

	$form['session'] = array(
		'#type'  => 'fieldset',
		'#title' => t('Session info')
	);

	$form['session']['sessionname'] = array(
		'#type'          => 'textfield',
		'#title'         => t('Session name'),
		'#size'          => 40,
		'#required'      => true,
		'#maxlength'     => 255,
		//	'#prefix'        => '<div class="iishconference_container_inline">',
		//	'#suffix'        => '</div>',
		'#default_value' => $session->getName(),
	);

	$form['session']['sessionabstract'] = array(
		'#type'          => 'textarea',
		'#title'         => t('Abstract'),
		'#description'   => '<em>' . t('(max. 1.000 words)') . '</em>',
		'#rows'          => 3,
		'#required'      => true,
		'#default_value' => $session->getAbstr(),
	);

	$networks = CachedConferenceApi::getNetworks();
	$networkOptions = CRUDApiClient::getAsKeyValueArray($networks);

	$networkIds = $session->getNetworksId();
	$form['session']['sessioninnetwork'] = array(
		'#title'         => NetworkApi::getNetworkName(),
		'#type'          => 'select',
		'#options'       => $networkOptions,
		'#required'      => true,
		//	'#prefix'        => '<div class="iishconference_container_inline">',
		//	'#suffix'        => '</div>',
		'#size'          => 4,
		'#default_value' => isset($networkIds[0]) ? $networkIds[0] : null,
	);

	if (SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) != 1) {
		$form['session']['sessioninnetwork']['#access'] = false;
		$form['session']['sessioninnetwork']['#default_value'] =
			SettingsApi::getSetting(SettingsApi::DEFAULT_NETWORK_ID);
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// SESSION PARTICIPANTS

	$sessionParticipants =
		CRUDApiMisc::getAllWherePropertyEquals(new SessionParticipantApi(), 'session_id', $session->getId())
			->getResults();
	$users = SessionParticipantApi::getAllUsers($sessionParticipants);
	$data['session_participants'] = $sessionParticipants;

	$form['session_participants'] = array(
		'#type'  => 'fieldset',
		'#title' => t('Participants'),
	);

	$form['session_participants']['submit_participant'] = array(
		'#type'   => 'submit',
		'#name'   => 'submit_participant',
		'#value'  => t('New participant'),
		'#suffix' => '<br /><br />',
	);

	foreach ($users as $i => $user) {
		$form['session_participants']['submit_participant_' . $user->getId()] = array(
			'#name'   => 'submit_participant_' . $user->getId(),
			'#type'   => 'submit',
			'#value'  => 'Edit',
			'#prefix' => ' &nbsp;' . t('or') . '<br /><br />',
			'#suffix' => ' ' . $user->getFullName() . '<br /><br />',
		);
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +

	$form['submit_back'] = array(
		'#type'                    => 'submit',
		'#name'                    => 'submit_back',
		'#value'                   => t('Back'),
		'#submit'                  => array('preregister_form_submit'),
		'#limit_validation_errors' => array(),
	);

	$form['submit'] = array(
		'#type'  => 'submit',
		'#name'  => 'submit',
		'#value' => t('Save session'),
	);

	// We can only remove a session if it has been persisted
	if ($session->isUpdate()) {
		$form['submit_remove'] = array(
			'#type'                    => 'submit',
			'#name'                    => 'submit_remove',
			'#value'                   => t('Remove session'),
			'#submit'                  => array('preregister_form_submit'),
			'#limit_validation_errors' => array(),
			'#attributes'              => array('onclick' =>
				                                    'if (!confirm("' .
				                                    t('Are you sure you want to remove this session?') .
				                                    '")) { return false; }'),
		);
	}

	$state->setFormData($data);

	return $form;
}

/**
 * Implements hook_form_submit()
 */
function preregister_session_form_submit($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$user = $state->getUser();

	$multiPageData = $state->getMultiPageData();
	$session = $multiPageData['session'];

	// Save session information
	$session->setName($form_state['values']['sessionname']);
	$session->setAbstr($form_state['values']['sessionabstract']);

	$networkId = EasyProtection::easyIntegerProtection($form_state['values']['sessioninnetwork']);
	$session->setNetworks(array($networkId));

	// Before we persist this data, is this a new session?
	$newSession = !$session->isUpdate();
	$session->save();

	// Also add the current user to the session as an organiser if this is a new session
	if ($newSession) {
		$organiser = new SessionParticipantApi();
		$organiser->setUser($user);
		$organiser->setSession($session);
		$organiser->setType(ParticipantTypeApi::ORGANIZER_ID);

		$organiser->save();
		drupal_set_message(t('You are added as organizer to this session.') . '<br />' .
			t('Please add participants to the session.'), 'status');
	}

	// Now find out if we have to add a participant or simply save the session
	$submitName = $form_state['triggering_element']['#name'];

	// Move back to the 'type of registration' page, clean cached data
	if ($submitName === 'submit') {
		$state->setMultiPageData(array());

		return 'preregister_typeofregistration_form';
	}

	if ($submitName === 'submit_participant') {
		return preregister_session_set_sessionparticipant($state, $session, null);
	}

	if (strpos($submitName, 'submit_participant_') === 0) {
		$id = EasyProtection::easyIntegerProtection(str_replace('submit_participant_', '', $submitName));

		return preregister_session_set_sessionparticipant($state, $session, $id);
	}
}

/**
 * What is the previous page?
 */
function preregister_session_form_back($form, &$form_state) {
    $state = new PreRegistrationState($form_state);
    $state->setMultiPageData(array());

	return 'preregister_typeofregistration_form';
}

/**
 * Remove the session
 */
function preregister_session_form_remove($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$multiPageData = $state->getMultiPageData();

	$session = $multiPageData['session'];
	$session->delete();

	$state->setMultiPageData(array());

	return 'preregister_typeofregistration_form';
}

/**
 * Check access to the edit page for the specified user id
 * and prepare a user instance for the session participant edit step
 *
 * @param PreRegistrationState $state   The pre-registration flow
 * @param SessionApi           $session The session in question
 * @param int|null             $id      The user id
 *
 * @return string The function name of the next step, which is the session participant edit form,
 * unless the session participant cannot be edited by the user
 */
function preregister_session_set_sessionparticipant($state, $session, $id) {
	$preRegisterUser = $state->getUser();

	// Make sure the session participant can be edited
	if ($id !== null) {
		$user = CRUDApiMisc::getById(new UserApi(), $id);

		if ($user === null) {
			drupal_set_message('The user you try to edit could not be found!', 'error');

			return 'preregister_session_form';
		}
	}
	else {
		$user = new UserApi();
	}

	// Now collect the with which roles we added the participant to a session
	$props = new ApiCriteriaBuilder();
	$sessionParticipants = SessionParticipantApi::getListWithCriteria(
		$props
			->eq('session_id', $session->getId())
			->eq('user_id', $id)
			->eq('addedBy_id', $preRegisterUser->getId())
			->get()
	)->getResults();

	// Did we add the participant to the session with roles or is it a new user?
	if ($user->isUpdate() && (count($sessionParticipants) === 0)) {
		drupal_set_message('You can only edit the users you created or added to a session!', 'error');

		return 'preregister_session_form';
	}

	$state->setMultiPageData(array('session'              => $session, 'user' => $user,
	                               'session_participants' => $sessionParticipants));

	return 'preregister_sessionparticipant_form';
}