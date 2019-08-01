<?php

/**
 * Implements hook_form()
 */
function preregister_typeofregistration_form($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$data = array();

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// AUTHOR

	if (SettingsApi::getSetting(SettingsApi::SHOW_AUTHOR_REGISTRATION) == 1) {
		$form['author'] = array(
			'#type'  => 'fieldset',
			'#title' => iish_t('I would like to propose a paper'),
		);

		if (PreRegistrationUtils::isAuthorRegistrationOpen()) {
			$papers = PreRegistrationUtils::getPapersOfUser($state);
			$maxPapers = SettingsApi::getSetting(SettingsApi::MAX_PAPERS_PER_PERSON_PER_SESSION);
			$canSubmitNewPaper = (($maxPapers === null) || (count($papers) < $maxPapers));
			$data['canSubmitNewPaper'] = $canSubmitNewPaper;

			if ($canSubmitNewPaper) {
				$form['author']['submit_paper'] = array(
					'#type'   => 'submit',
					'#name'   => 'submit_paper',
					'#value'  => iish_t('Add a new paper'),
					'#suffix' => '<br /><br />',
				);
			}

			$printOr = true;
			foreach ($papers as $paper) {
				$prefix = '';
				if ($printOr && $canSubmitNewPaper) {
					$prefix = ' &nbsp;' . iish_t('or') . '<br /><br />';
					$printOr = false;
				}

				$form['author']['submit_paper_' . $paper->getId()] = array(
					'#name'   => 'submit_paper_' . $paper->getId(),
					'#type'   => 'submit',
					'#value'  => iish_t('Edit paper'),
					'#prefix' => $prefix,
					'#suffix' => ' ' . $paper->getTitle() . '<br /><br />',
				);
			}
		}
		else {
			$form['author']['closed_message'] = array(
				'#type'   => 'markup',
				'#markup' =>
					'<font color="red">' . iish_t('It is no longer possible to pre-register a paper.') . '<br/ >' .
					iish_t('You can still pre-register for the conference as a spectator.') . '</font>',
			);
		}
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// ORGANIZER

	if (SettingsApi::getSetting(SettingsApi::SHOW_ORGANIZER_REGISTRATION) == 1) {
		$form['organizer'] = array(
			'#type'  => 'fieldset',
			'#title' => iish_t('I\'m an organizer and I would like to propose a session (including multiple participants and papers)'),
		);

		if (PreRegistrationUtils::isOrganizerRegistrationOpen()) {
			if (PreRegistrationUtils::useSessions()) {
				// Use 'session-inline' to trigger css styling on the parent/wrapper div of this select
				$form['organizer']['session-inline'] = array(
					'#type'         => 'select',
					'#title'        => iish_t('Session'),
					'#options'      => CachedConferenceApi::getSessionsKeyValue(),
					'#empty_option' => '- ' . iish_t('Select a session') . ' -',
				);

				$form['organizer']['submit_existing_session'] = array(
					'#type'  => 'submit',
					'#name'  => 'submit_existing_session',
					'#value' => iish_t('Organize session'),
					'#suffix'       => '<br /><br />',
				);
			}
			else {
				$form['organizer']['submit_session'] = array(
					'#type'   => 'submit',
					'#name'   => 'submit_session',
					'#value'  => iish_t('Add a new session'),
					'#suffix' => '<br /><br />',
				);
			}

			$sessionParticipants = PreRegistrationUtils::getSessionParticipantsAddedByUser($state);
			$sessions = SessionParticipantApi::getAllSessions($sessionParticipants);

			$printOr = true;
			foreach (array_unique($sessions) as $session) {
				$prefix = '';
				if ($printOr) {
					$prefix = ' &nbsp;' . iish_t('or') . '<br /><br />';
					$printOr = false;
				}

				$form['organizer']['submit_session_' . $session->getId()] = array(
					'#name'   => 'submit_session_' . $session->getId(),
					'#type'   => 'submit',
					'#value'  => iish_t('Edit session'),
					'#prefix' => $prefix,
					'#suffix' => ' ' . $session->getName() . '<br /><br />',
				);
			}
		}
		else {
			$form['organizer']['closed_message'] = array(
				'#type'   => 'markup',
				'#markup' =>
					'<font color="red">' . iish_t('It is no longer possible to propose a session.') . '<br/ >' .
					iish_t('You can still pre-register for the conference as a spectator.') . '</font>',
			);
		}
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// SESSION PARTICIPANT TYPES

	$participantTypes = PreRegistrationUtils::getParticipantTypesForUser();
	if (count($participantTypes) > 0) {
		$typesOr = strtolower(implode(' or ', $participantTypes));

		if (PreRegistrationUtils::isAuthorRegistrationOpen()) {
			$form['sessionparticipanttypes'] = array(
				'#type'  => 'fieldset',
				'#title' => iish_t('I would like to register as a @types in one or multiple sessions',
					array('@types' => $typesOr)),
			);

			$form['sessionparticipanttypes']['submit_sessionparticipanttypes'] = array(
				'#type'   => 'submit',
				'#name'   => 'submit_sessionparticipanttypes',
				'#value'  => iish_t('Register as a @types', array('@types' => $typesOr)),
				'#suffix' => '<br /><br />',
			);

			foreach ($participantTypes as $participantType) {
				$sessionParticipants =
					PreRegistrationUtils::getSessionParticipantsOfUserWithType($state, $participantType);

				if (count($sessionParticipants) > 0) {
					$sessions = CRUDApiClient::getForMethod($sessionParticipants, 'getSession');

					$form['sessionparticipanttypes']['type_' . $participantType->getId()] = array(
						'#type'   => 'markup',
						'#markup' => '<strong>' . iish_t('I would like to be a @type in the sessions',
								array('@type' => strtolower($participantType))) . ':</strong>' .
							theme('item_list', array(
									'type'  => 'ul',
									'items' => $sessions,
								)
							),
					);
				}
			}
		}
		else {
			$form['sessionparticipanttypes']['closed_message'] = array(
				'#type'   => 'markup',
				'#markup' =>
					'<font color="red">' . iish_t('It is no longer possible to pre-register as @types ' .
						'in one or multiple sessions.', array('@types' => $typesOr)) . '<br/ >' .
					iish_t('You can still pre-register for the conference as a spectator.') . '</font>',
			);
		}
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// SPECTATOR

	$form['spectator'] = array(
		'#type'  => 'fieldset',
		'#title' => iish_t('I would like to register as a spectator'),
	);

	$form['spectator']['help_text'] = array(
		'#type'   => 'markup',
		'#markup' => iish_t('Then you may skip this page and go right away to the comments and confirmation page.'),
	);

	// + + + + + + + + + + + + + + + + + + + + + + + +

	$commentsPage = new PreRegistrationPage(PreRegistrationPage::COMMENTS);

	$valueNextPage = iish_t('Next to confirmation page');
	if ($commentsPage->isOpen()) {
		$valueNextPage = iish_t('Next to general comments page');
	}

	$form['submit_back'] = array(
		'#type'                    => 'submit',
		'#name'                    => 'submit_back',
		'#value'                   => iish_t('Back to personal info'),
		'#submit'                  => array('preregister_form_submit'),
		'#limit_validation_errors' => array(),
	);

	$form['submit'] = array(
		'#type'  => 'submit',
		'#name'  => 'submit',
		'#value' => $valueNextPage,
	);

	$state->setFormData($data);

	return $form;
}

/**
 * Implements hook_form_submit()
 */
function preregister_typeofregistration_form_submit($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$data = $state->getFormData();
	$submitName = $form_state['triggering_element']['#name'];

	if ($submitName === 'submit') {
		$commentsPage = new PreRegistrationPage(PreRegistrationPage::COMMENTS);

		if ($commentsPage->isOpen()) {
			return PreRegistrationPage::COMMENTS;
		}
		else {
			return PreRegistrationPage::CONFIRM;
		}
	}

	if (PreRegistrationUtils::isAuthorRegistrationOpen()) {
		if (($submitName === 'submit_paper') && $data['canSubmitNewPaper']) {
			return preregister_typeofregistration_set_paper($state, null);
		}

		if (strpos($submitName, 'submit_paper_') === 0) {
			$id = EasyProtection::easyIntegerProtection(str_replace('submit_paper_', '', $submitName));

			return preregister_typeofregistration_set_paper($state, $id);
		}
	}

	if (PreRegistrationUtils::isOrganizerRegistrationOpen()) {
		if ($submitName === 'submit_session') {
			return preregister_typeofregistration_set_session($state, null);
		}

		if (strpos($submitName, 'submit_session_') === 0) {
			$id = EasyProtection::easyIntegerProtection(str_replace('submit_session_', '', $submitName));

			return preregister_typeofregistration_set_session($state, $id);
		}

		if ($submitName === 'submit_existing_session') {
			$id = EasyProtection::easyIntegerProtection($form_state['values']['session-inline']);

			return preregister_typeofregistration_set_session($state, $id, true);
		}
	}

	if (PreRegistrationUtils::isAuthorRegistrationOpen() && ($submitName === 'submit_sessionparticipanttypes')) {
		return PreRegistrationPage::SESSION_PARTICIPANT_TYPES;
	}

	return PreRegistrationPage::TYPE_OF_REGISTRATION;
}

/**
 * What is the previous page?
 */
function preregister_typeofregistration_form_back($form, &$form_state) {
	return PreRegistrationPage::PERSONAL_INFO;
}

/**
 * Check access to the edit page for the specified paper id and prepare a paper instance for the paper edit step
 *
 * @param PreRegistrationState $state The pre-registration flow
 * @param int|null             $id    The paper id
 *
 * @return string The function name of the next step, which is the paper edit form,
 * unless the paper cannot be edited by the user
 */
function preregister_typeofregistration_set_paper($state, $id) {
	$user = $state->getUser();

	// Make sure the paper can be edited
	if ($id !== null) {
		$paper = CRUDApiMisc::getById(new PaperApi(), $id);

		if ($paper === null) {
			drupal_set_message('The paper you try to edit could not be found!', 'error');

			return PreRegistrationPage::PERSONAL_INFO;
		}
		else if (($paper->getAddedById() != $user->getId()) || ($paper->getUserId() != $user->getId())) {
			drupal_set_message('You can only edit the papers you created!', 'error');

			return PreRegistrationPage::PERSONAL_INFO;
		}
	}
	else {
		$paper = new PaperApi();
	}

	$state->setMultiPageData(array('paper' => $paper));

	return PreRegistrationPage::PAPER;
}

/**
 * Check access to the edit page for the specified session id and prepare a session instance for the session edit step
 *
 * @param PreRegistrationState $state          The pre-registration flow
 * @param int|null             $id             The session id
 * @param bool                 $addAsOrganizer Whether to add the user as organizer to the session right away
 *
 * @return string The function name of the next step, which is the session edit form,
 * unless the session cannot be edited by the user
 */
function preregister_typeofregistration_set_session($state, $id, $addAsOrganizer = false) {
	$user = $state->getUser();

	// Make sure the session can be edited
	if ($id !== null) {
		$session = CRUDApiMisc::getById(new SessionApi(), $id);

		if ($session === null) {
			drupal_set_message('The session you try to edit could not be found!', 'error');

			return PreRegistrationPage::TYPE_OF_REGISTRATION;
		}
		else if (!PreRegistrationUtils::useSessions() && ($session->getAddedById() != $user->getId())) {
			drupal_set_message('You can only edit the sessions you created!', 'error');

			return PreRegistrationPage::TYPE_OF_REGISTRATION;
		}
	}
	else if (PreRegistrationUtils::useSessions()) {
		drupal_set_message('Please select the session you would like to organize!', 'error');

		return PreRegistrationPage::TYPE_OF_REGISTRATION;
	}
	else {
		$session = new SessionApi();
	}

	if (PreRegistrationUtils::useSessions() && $addAsOrganizer) {
		$organiser = new SessionParticipantApi();
		$organiser->setUser($user);
		$organiser->setSession($session);
		$organiser->setType(ParticipantTypeApi::ORGANIZER_ID);

		$organiser->save();
		drupal_set_message(iish_t('You are added as organizer to this session.') . '<br />' .
			iish_t('Please add participants to the session.'), 'status');
	}

	$state->setMultiPageData(array('session' => $session));

	return PreRegistrationPage::SESSION;
}


