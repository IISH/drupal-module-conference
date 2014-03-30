<?php

/**
 * Implements hook_form()
 */
function preregister_typeofregistration_form($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$user = $state->getUser();
	$data = array();

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// AUTHOR

	$showAuthor = SettingsApi::getSetting(SettingsApi::SHOW_AUTHOR_REGISTRATION);
	$authorClosesOn = SettingsApi::getSetting(SettingsApi::AUTHOR_REGISTRATION_CLOSES_ON);

	$authorRegistrationClosed =
		(($authorClosesOn !== null) && (strlen(trim($authorClosesOn)) > 0) && (time() >= strtotime($authorClosesOn)));
	$data['authorRegistrationOpen'] = (($showAuthor == 1) && !$authorRegistrationClosed);

	if ($showAuthor == 1) {
		$form['author'] = array(
			'#type'  => 'fieldset',
			'#title' => t('I would like to propose a paper'),
		);

		if (!$authorRegistrationClosed) {
			$props = new ApiCriteriaBuilder();
			$paperResults = PaperApi::getListWithCriteria(
				$props
					->eq('addedBy_id', $user->getId())
					->eq('user_id', $user->getId())
					->get()
			);
			$papers = $paperResults->getResults();

			$maxPapers = SettingsApi::getSetting(SettingsApi::MAX_PAPERS_PER_PERSON_PER_SESSION);
			$canSubmitNewPaper = (($maxPapers === null) || ($paperResults->getTotalSize() < $maxPapers));
			$data['canSubmitNewPaper'] = $canSubmitNewPaper;

			if ($canSubmitNewPaper) {
				$form['author']['submit_paper'] = array(
					'#type'   => 'submit',
					'#name'   => 'submit_paper',
					'#value'  => t('Add a new paper'),
					'#suffix' => '<br /><br />',
				);
			}

			$printOr = true;
			foreach ($papers as $paper) {
				$prefix = '';
				if ($printOr  && $canSubmitNewPaper) {
					$prefix = ' &nbsp;' . t('or') . '<br /><br />';
					$printOr = false;
				}

				$form['author']['submit_paper_' . $paper->getId()] = array(
					'#name'   => 'submit_paper_' . $paper->getId(),
					'#type'   => 'submit',
					'#value'  => t('Edit paper'),
					'#prefix' => $prefix,
					'#suffix' => ' ' . $paper->getTitle() . '<br /><br />',
				);
			}
		}
		else {
			$form['author']['closed_message'] = array(
				'#type'   => 'markup',
				'#markup' => '<font color="red">' . t('It is no longer possible to pre-register a paper.') . '<br/ >' .
					t('You can still pre-register for the conference as a spectator.') . '</font>',
			);
		}
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// ORGANIZER

	$showOrganizer = SettingsApi::getSetting(SettingsApi::SHOW_ORGANIZER_REGISTRATION);
	$organizerClosesOn = SettingsApi::getSetting(SettingsApi::ORGANIZER_REGISTRATION_CLOSES_ON);

	$organizerRegistrationClosed = (($organizerClosesOn !== null) && (strlen(trim($organizerClosesOn)) > 0) &&
		(time() >= strtotime($organizerClosesOn)));
	$data['organizerRegistrationOpen'] = (($showOrganizer == 1) && !$organizerRegistrationClosed);

	if ($showOrganizer == 1) {
		$form['organizer'] = array(
			'#type'  => 'fieldset',
			'#title' => t('I\'m an organizer and I would like to propose a session (including multiple participants and papers)'),
		);

		if (!$organizerRegistrationClosed) {
			$sessions = CRUDApiMisc::getAllWherePropertyEquals(new SessionApi(), 'addedBy_id', $user->getId())
				->getResults();

			$form['organizer']['submit_session'] = array(
				'#type'   => 'submit',
				'#name'   => 'submit_session',
				'#value'  => t('Add a new session'),
				'#suffix' => '<br /><br />',
			);

			$printOr = true;
			foreach ($sessions as $session) {
				$prefix = '';
				if ($printOr) {
					$prefix = ' &nbsp;' . t('or') . '<br /><br />';
					$printOr = false;
				}

				$form['organizer']['submit_session_' . $session->getId()] = array(
					'#name'   => 'submit_session_' . $session->getId(),
					'#type'   => 'submit',
					'#value'  => t('Edit session'),
					'#prefix' => $prefix,
					'#suffix' => ' ' . $session->getName() . '<br /><br />',
				);
			}
		}
		else {
			$form['organizer']['closed_message'] = array(
				'#type'   => 'markup',
				'#markup' => '<font color="red">' . t('It is no longer possible to propose a session.') . '<br/ >' .
					t('You can still pre-register for the conference as a spectator.') . '</font>',
			);
		}
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// SPECTATOR

	$form['spectator'] = array(
		'#type'  => 'fieldset',
		'#title' => t('I would like to register as a @spectator',
			array('@spectator' => strtolower(SettingsApi::getSetting(SettingsApi::SPECTATOR_NAME)))),
	);

	$form['spectator']['help_text'] = array(
		'#type'   => 'markup',
		'#markup' => t('Then you may skip this page and go right away to the confirmation page.'),
	);

	// + + + + + + + + + + + + + + + + + + + + + + + +

	$form['submit_back'] = array(
		'#type'                    => 'submit',
		'#name'                    => 'submit_back',
		'#value'                   => t('Back to personal info'),
		'#submit'                  => array('preregister_form_submit'),
		'#limit_validation_errors' => array(),
	);

	$form['submit'] = array(
		'#type'  => 'submit',
		'#name'  => 'submit',
		'#value' => t('Next to confirmation page'),
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
		return 'preregister_confirm_form';
	}

	if ($data['authorRegistrationOpen']) {
		if (($submitName === 'submit_paper') && $data['canSubmitNewPaper']) {
			return preregister_typeofregistration_set_paper($state, null);
		}

		if (strpos($submitName, 'submit_paper_') === 0) {
			$id = EasyProtection::easyIntegerProtection(str_replace('submit_paper_', '', $submitName));

			return preregister_typeofregistration_set_paper($state, $id);
		}
	}

	if ($data['organizerRegistrationOpen']) {
		if ($submitName === 'submit_session') {
			return preregister_typeofregistration_set_session($state, null);
		}

		if (strpos($submitName, 'submit_session_') === 0) {
			$id = EasyProtection::easyIntegerProtection(str_replace('submit_session_', '', $submitName));

			return preregister_typeofregistration_set_session($state, $id);
		}
	}

	return 'preregister_typeofregistration_form';
}

/**
 * What is the previous page?
 */
function preregister_typeofregistration_form_back($form, &$form_state) {
	return 'preregister_personalinfo_form';
}

/**
 * Check access to the edit page for the specified paper id and prepare a paper instance for the paper edit step
 *
 * @param PreRegistrationState $state The pre-registration flow
 * @param int|null            $id   The paper id
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

			return 'preregister_typeofregistration_form';
		}
		else if (($paper->getAddedById() != $user->getId()) || ($paper->getUserId() != $user->getId())) {
			drupal_set_message('You can only edit the papers you created!', 'error');

			return 'preregister_typeofregistration_form';
		}
	}
	else {
		$paper = new PaperApi();
	}

	$state->setMultiPageData(array('paper' => $paper));

	return 'preregister_paper_form';
}

/**
 * Check access to the edit page for the specified session id and prepare a session instance for the session edit step
 *
 * @param PreRegistrationState $state The pre-registration flow
 * @param int|null            $id   The session id
 *
 * @return string The function name of the next step, which is the session edit form,
 * unless the session cannot be edited by the user
 */
function preregister_typeofregistration_set_session($state, $id) {
	$user = $state->getUser();

	// Make sure the session can be edited
	if ($id !== null) {
		$session = CRUDApiMisc::getById(new SessionApi(), $id);

		if ($session === null) {
			drupal_set_message('The session you try to edit could not be found!', 'error');

			return 'preregister_typeofregistration_form';
		}
		else if ($session->getAddedById() != $user->getId()) {
			drupal_set_message('You can only edit the sessions you created!', 'error');

			return 'preregister_typeofregistration_form';
		}
	}
	else {
		$session = new SessionApi();
	}

	$state->setMultiPageData(array('session' => $session));

	return 'preregister_session_form';
}


