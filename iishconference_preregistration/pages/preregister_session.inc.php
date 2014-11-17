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
		'#title' => iish_t('Session info')
	);

	if (!PreRegistrationUtils::useSessions()) {
		$form['session']['sessionname'] = array(
			'#type'          => 'textfield',
			'#title'         => iish_t('Session name'),
			'#size'          => 40,
			'#required'      => true,
			'#maxlength'     => 255,
			'#default_value' => $session->getName(),
		);

		$form['session']['sessionabstract'] = array(
			'#type'          => 'textarea',
			'#title'         => iish_t('Abstract'),
			'#description'   => '<em>(' . iish_t('max. 1.000 words') . ')</em>',
			'#rows'          => 3,
			'#required'      => true,
			'#default_value' => $session->getAbstr(),
		);

		$networkIds = $session->getNetworksId();
		$form['session']['sessioninnetwork'] = array(
			'#title'         => NetworkApi::getNetworkName(),
			'#type'          => 'select',
			'#options'       => CRUDApiClient::getAsKeyValueArray(CachedConferenceApi::getNetworks()),
			'#required'      => true,
			'#size'          => 4,
			'#default_value' => isset($networkIds[0]) ? $networkIds[0] : null,
		);

		PreRegistrationUtils::hideAndSetDefaultNetwork($form['session']['sessioninnetwork']);
	}
	else {
		$markup = array();

		$markup[] = theme('iishconference_container_field', array(
			'label' => 'Session name',
			'value' => $session->getName(),
		));

		$markup[] = theme('iishconference_container_field', array(
			'label'          => 'Abstract',
			'value'          => $session->getAbstr(),
			'valueOnNewLine' => true,
		));

		if (PreRegistrationUtils::showNetworks()) {
			$markup[] = theme('iishconference_container_field', array(
				'label' => 'Networks',
				'value' => implode(', ', $session->getNetworks())
			));
		}

		$form['session']['info'] = array(
			'#type'   => 'markup',
			'#markup' => implode('', $markup),
		);
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// SESSION PARTICIPANTS

	$sessionParticipants = PreRegistrationUtils::getSessionParticipantsAddedByUserForSession($state, $session);
	$users = SessionParticipantApi::getAllUsers($sessionParticipants);
	$data['session_participants'] = $sessionParticipants;

	$form['session_participants'] = array(
		'#type'  => 'fieldset',
		'#title' => iish_t('Participants'),
	);

	$form['session_participants']['submit_participant'] = array(
		'#type'   => 'submit',
		'#name'   => 'submit_participant',
		'#value'  => iish_t('New participant'),
		'#suffix' => '<br /><br />',
	);

	$printOr = true;
	foreach ($users as $user) {
		$prefix = '';
		if ($printOr) {
			$prefix = ' &nbsp;' . iish_t('or') . '<br /><br />';
			$printOr = false;
		}

		$roles = SessionParticipantApi::getAllTypesOfUserForSession(
			$sessionParticipants,
			$user->getId(),
			$session->getId()
		);

		$form['session_participants']['submit_participant_' . $user->getId()] = array(
			'#name'   => 'submit_participant_' . $user->getId(),
			'#type'   => 'submit',
			'#value'  => 'Edit',
			'#prefix' => $prefix,
			'#suffix' => ' ' . $user->getFullName() . ' &nbsp;&nbsp; <em>(' .
				ConferenceMisc::getEnumSingleLine($roles) . ')</em><br /><br />',
		);
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +

	$form['submit_back'] = array(
		'#type'                    => 'submit',
		'#name'                    => 'submit_back',
		'#value'                   => iish_t('Back'),
		'#submit'                  => array('preregister_form_submit'),
		'#limit_validation_errors' => array(),
	);

	if (!PreRegistrationUtils::useSessions()) {
		$form['submit'] = array(
			'#type'  => 'submit',
			'#name'  => 'submit',
			'#value' => iish_t('Save session'),
		);
	}

	// We can only remove a session if it has been persisted
	if (!PreRegistrationUtils::useSessions() && $session->isUpdate()) {
		$form['submit_remove'] = array(
			'#type'                    => 'submit',
			'#name'                    => 'submit_remove',
			'#value'                   => iish_t('Remove session'),
			'#submit'                  => array('preregister_form_submit'),
			'#limit_validation_errors' => array(),
			'#attributes'              => array('onclick' =>
				                                    'if (!confirm("' .
				                                    iish_t('Are you sure you want to remove this session?') .
				                                    '")) { return false; }'),
		);
	}

	$state->setFormData($data);

	return $form;
}

/**
 * Implements hook_form_validate()
 */
function preregister_session_form_validate($form, &$form_state) {
	if (!PreRegistrationUtils::useSessions()) {
		$state = new PreRegistrationState($form_state);
		$multiPageData = $state->getMultiPageData();
		$session = $multiPageData['session'];

		$props = new ApiCriteriaBuilder();
		$props
			->eq('name', trim($form_state['values']['sessionname']))
			->eq('addedBy.id', $state->getUser()->getId());

		if ($session->isUpdate()) {
			$props->ne('id', $session->getId());
		}

		// Don't allow multiple sessions with the same name
		$sessions = SessionApi::getListWithCriteria($props->get());
		if ($sessions->getTotalSize() > 0) {
			form_set_error('sessionname', iish_t('You already created a session with the same name.'));
		}
	}
}

/**
 * Implements hook_form_submit()
 */
function preregister_session_form_submit($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$user = $state->getUser();

	$multiPageData = $state->getMultiPageData();
	$session = $multiPageData['session'];

	if (!PreRegistrationUtils::useSessions()) {
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
			drupal_set_message(iish_t('You are added as organizer to this session.') . '<br />' .
				iish_t('Please add participants to the session.'), 'status');
		}
	}

	// Now find out if we have to add a participant or simply save the session
	$submitName = $form_state['triggering_element']['#name'];

	// Move back to the 'type of registration' page, clean cached data
	if ($submitName === 'submit') {
		$state->setMultiPageData(array());

		return PreRegistrationPage::TYPE_OF_REGISTRATION;
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

	return PreRegistrationPage::TYPE_OF_REGISTRATION;
}

/**
 * Remove the session, if created by them
 */
function preregister_session_form_remove($form, &$form_state) {
	if (!PreRegistrationUtils::useSessions()) {
		$state = new PreRegistrationState($form_state);
		$multiPageData = $state->getMultiPageData();

		$session = $multiPageData['session'];
		$session->delete();

		$state->setMultiPageData(array());
	}

	return PreRegistrationPage::TYPE_OF_REGISTRATION;
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
	// Make sure the session participant can be edited
	if ($id !== null) {
		$user = CRUDApiMisc::getById(new UserApi(), $id);

		if ($user === null) {
			drupal_set_message('The user you try to edit could not be found!', 'error');

			return PreRegistrationPage::SESSION;
		}
	}
	else {
		$user = new UserApi();
	}

	// Now collect the roles with which we added the participant to a session
	$sessionParticipants = PreRegistrationUtils::getSessionParticipantsAddedByUserForSessionAndUser($state, $session, $user);

	// Did we add the participant to the session with roles or is it a new user?
	if ($user->isUpdate() && (count($sessionParticipants) === 0)) {
		drupal_set_message('You can only edit the users you created or added to a session!', 'error');

		return PreRegistrationPage::SESSION;
	}

	$state->setMultiPageData(array('session'              => $session,
	                               'user'                 => $user,
	                               'session_participants' => $sessionParticipants));

	return PreRegistrationPage::SESSION_PARTICIPANT;
}