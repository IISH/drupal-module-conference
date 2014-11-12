<?php

/**
 * Implements hook_form()
 */
function preregister_sessionparticipant_form($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$preRegisterUser = $state->getUser();

	$multiPageData = $state->getMultiPageData();
	$session = $multiPageData['session'];
	$user = $multiPageData['user'];
	$sessionParticipants = $multiPageData['session_participants'];

	$participant = CRUDApiMisc::getFirstWherePropertyEquals(new ParticipantDateApi(), 'user_id', $user->getId());
	$participant = ($participant === null) ? new ParticipantDateApi() : $participant;

	// Now collect the paper added to this session
	$props = new ApiCriteriaBuilder();
	$paper = PaperApi::getListWithCriteria(
		$props
			->eq('session_id', $session->getId())
			->eq('user_id', $user->getId())
			->get()
	)->getFirstResult();

	$paper = ($paper !== null) ? $paper : new PaperApi();

	$state->setFormData(array('session'              => $session,
	                          'user'                 => $user,
	                          'participant'          => $participant,
	                          'paper'                => $paper,
	                          'session_participants' => $sessionParticipants));

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// PARTICIPANT

	// If the user was added by the currently logged in user, he/she may change him/her
	$readOnlyUser = array();
	$readOnlyParticipant = array();
	if ($user->isUpdate() && ($user->getAddedById() != $preRegisterUser->getId()) &&
		($user->getId() != $preRegisterUser->getId())
	) {
		$readOnlyUser['readonly'] = 'readonly';
		$readOnlyUser['class'] = array('readonly-text');
	}
	if ($participant->isUpdate() && ($participant->getAddedById() != $preRegisterUser->getId()) &&
		($participant->getUserId() != $preRegisterUser->getId())
	) {
		$readOnlyParticipant['readonly'] = 'readonly';
		$readOnlyParticipant['class'] = array('readonly-text');
	}

	$form['participant'] = array(
		'#type'  => 'fieldset',
		'#title' => iish_t('Add a participant'),
	);

	$form['participant']['addparticipantemail'] = array(
		'#type'          => 'textfield',
		'#title'         => iish_t('E-mail'),
		'#required'      => true,
		'#size'          => 40,
		'#maxlength'     => 100,
		'#default_value' => $user->getEmail(),
		'#attributes'    => $readOnlyUser,
	);

	$form['participant']['addparticipantfirstname'] = array(
		'#type'          => 'textfield',
		'#title'         => iish_t('First name'),
		'#required'      => true,
		'#size'          => 40,
		'#maxlength'     => 255,
		'#default_value' => $user->getFirstName(),
		'#attributes'    => $readOnlyUser,
	);

	$form['participant']['addparticipantlastname'] = array(
		'#type'          => 'textfield',
		'#title'         => iish_t('Last name'),
		'#required'      => true,
		'#size'          => 40,
		'#maxlength'     => 255,
		'#default_value' => $user->getLastName(),
		'#attributes'    => $readOnlyUser,
	);

	if (SettingsApi::getSetting(SettingsApi::SHOW_STUDENT) == 1) {
		$form['participant']['addparticipantstudent'] = array(
			'#type'          => 'checkbox',
			'#title'         => iish_t('Please check if this participant is a (PhD) student'),
			'#default_value' => $participant->getStudent(),
			'#attributes'    => $readOnlyParticipant,
		);
	}

	// If a field is required, but turns out to be missing in the existing record, allow the user to add a value
	$userIsReadOnly = isset($readOnlyUser['readonly']);
	$cvRequired = (SettingsApi::getSetting(SettingsApi::REQUIRED_CV) == 1);
	$userCv = $user->getCv();
	if (SettingsApi::getSetting(SettingsApi::SHOW_CV) == 1) {
		$form['participant']['addparticipantcv'] = array(
			'#type'          => 'textarea',
			'#title'         => iish_t('Curriculum Vitae'),
			'#description'   => '<em>' . iish_t('(max. 200 words)') . '</em>',
			'#rows'          => 2,
			'#required'      => $cvRequired,
			'#default_value' => $userCv,
			'#attributes'    => ($cvRequired && $userIsReadOnly && empty($userCv)) ? array() : $readOnlyUser,
		);
	}

	$userCountryId = $user->getCountryId();
	$form['participant']['addparticipantcountry'] = array(
		'#type'          => 'select',
		'#title'         => iish_t('Country'),
		'#options'       => CRUDApiClient::getAsKeyValueArray(CachedConferenceApi::getCountries()),
		'#required'      => true,
		'#default_value' => $userCountryId,
		'#attributes'    => ($userIsReadOnly && empty($userCountryId)) ? array() : $readOnlyUser,
	);

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// PARTICIPANT ROLES

	$participantTypes = CachedConferenceApi::getParticipantTypes();
	$participantTypeOptions = CRUDApiClient::getAsKeyValueArray($participantTypes);

	$chosenTypes =
		SessionParticipantApi::getAllTypesOfUserForSession($sessionParticipants, $user->getId(), $session->getId());
	$chosenTypeValues = CRUDApiClient::getIds($chosenTypes);

	$form['participant_roles'] = array(
		'#type'  => 'fieldset',
		'#title' => iish_t('The roles of the participant in this session'),
	);

	$description = ParticipantTypeApi::getCombinationsNotAllowedText();
	if (strlen(trim($description)) > 0) {
		$description = '<br />' . ConferenceMisc::getCleanHTML($description);
	}
	else {
		$description = '';
	}

	$form['participant_roles']['addparticipanttype'] = array(
		'#type'          => 'checkboxes',
		'#description'   => $description,
		'#required'      => true,
		'#options'       => $participantTypeOptions,
		'#default_value' => $chosenTypeValues,
	);

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// PARTICIPANT PAPER

	// For which selected participant types should a paper be added as well?
	$visibleStates = array();
	foreach ($participantTypes as $type) {
		if ($type->getWithPaper()) {
			$visibleStates[] =
				array(':input[name="addparticipanttype[' . $type->getId() . ']"]' => array('checked' => true));
			$visibleStates[] = 'or';
		}
	}
	array_pop($visibleStates); // Removes the last 'or'

	$form['participant_paper'] = array(
		'#type'   => 'fieldset',
		'#title'  => iish_t('Add paper for participant'),
		'#states' => array('visible' => $visibleStates),
	);

	$form['participant_paper']['addparticipantpapertitle'] = array(
		'#type'          => 'textfield',
		'#title'         => iish_t('Paper title'),
		'#size'          => 40,
		'#maxlength'     => 255,
		'#default_value' => $paper->getTitle(),
	);

	$form['participant_paper']['addparticipantpaperabstract'] = array(
		'#type'          => 'textarea',
		'#title'         => iish_t('Paper abstract'),
		'#description'   => '<em>' . iish_t('(max. 500 words)') . '</em>',
		'#rows'          => 3,
		'#default_value' => $paper->getAbstr(),
	);

	// + + + + + + + + + + + + + + + + + + + + + + + +

	$form['submit_back'] = array(
		'#type'                    => 'submit',
		'#name'                    => 'submit_back',
		'#value'                   => iish_t('Back'),
		'#submit'                  => array('preregister_form_submit'),
		'#limit_validation_errors' => array(),
	);

	$form['submit'] = array(
		'#type'  => 'submit',
		'#name'  => 'submit',
		'#value' => iish_t('Save participant'),
	);

	// We can only remove a participant from a session if he/she has already been added to session
	if (isset($sessionParticipants[0])) {
		$form['submit_remove'] = array(
			'#type'                    => 'submit',
			'#name'                    => 'submit_remove',
			'#value'                   => iish_t('Remove participant from session'),
			'#submit'                  => array('preregister_form_submit'),
			'#limit_validation_errors' => array(),
			'#attributes'              => array('onclick' =>
	            'if (!confirm("' .
	            iish_t('Are you sure you want to remove this participant? ' .
		            '(The participant will only be removed from this session).') .
	            '")) { return false; }'),
		);
	}

	return $form;
}

/**
 * Implements hook_form_validate()
 */
function preregister_sessionparticipant_form_validate($form, &$form_state) {
	$email = trim($form_state['values']['addparticipantemail']);

	if (!valid_email_address($email)) {
		form_set_error('addparticipantemail', iish_t('The e-mail address appears to be invalid.'));
	}

	if (!ParticipantTypeApi::isCombinationOfTypesAllowed($form_state['values']['addparticipanttype'])) {
		form_set_error('addparticipanttype',
			ConferenceMisc::getCleanHTML(ParticipantTypeApi::getCombinationsNotAllowedText()));
	}

	if (ParticipantTypeApi::containsTypeWithPaper($form_state['values']['addparticipanttype'])) {
		if (strlen(trim($form_state['values']['addparticipantpapertitle'])) === 0) {
			form_set_error('addparticipantpapertitle', iish_t('Paper title is required with the selected type(s).'));
		}
		if (strlen(trim($form_state['values']['addparticipantpaperabstract'])) === 0) {
			form_set_error('addparticipantpaperabstract', iish_t('Paper abstract is required with the selected type(s).'));
		}
	}
}

/**
 * Implements hook_form_submit()
 */
function preregister_sessionparticipant_form_submit($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$preRegisterUser = $state->getUser();

	$data = $state->getFormData();
	$session = $data['session'];
	$user = $data['user'];
	$participant = $data['participant'];
	$paper = $data['paper'];
	$allToDelete = $data['session_participants'];

	// First check if the user with the given email does not exists already
	$email = strtolower(trim($form_state['values']['addparticipantemail']));
	$foundUser = CRUDApiMisc::getFirstWherePropertyEquals(new UserApi(), 'email', $email);
	if ($foundUser !== null) {
		$user = $foundUser;
		$participant =
			CRUDApiMisc::getFirstWherePropertyEquals(new ParticipantDateApi(), 'user_id', $foundUser->getId());
		$participant = ($participant !== null) ? $participant : new ParticipantDateApi();
	}

	$userCv = $user->getCv();
	$userCountry = $user->getCountryId();
	$cvRequired = ((SettingsApi::getSetting(SettingsApi::SHOW_CV) == 1) &&
		(SettingsApi::getSetting(SettingsApi::REQUIRED_CV) == 1) && empty($userCv));
	$countryRequired = empty($userCountry);

	// Then we save the user
	if (!$user->isUpdate() || ($user->getAddedById() == $preRegisterUser->getId()) ||
		($user->getId() == $preRegisterUser->getId())
	) {
		$user->setEmail($form_state['values']['addparticipantemail']);
		$user->setFirstName($form_state['values']['addparticipantfirstname']);
		$user->setLastName($form_state['values']['addparticipantlastname']);
		$user->setCountry($form_state['values']['addparticipantcountry']);

		if (SettingsApi::getSetting(SettingsApi::SHOW_CV) == 1) {
			$user->setCv($form_state['values']['addparticipantcv']);
		}

		$user->save();
	}
	// If a field is required, but turns out to be missing in the existing record, allow the user to add a value
	else if ($cvRequired || $countryRequired) {
		if ($cvRequired) {
			$user->setCv($form_state['values']['addparticipantcv']);
		}
		if ($countryRequired) {
			$user->setCountry($form_state['values']['addparticipantcountry']);
		}

		$user->save();
	}

	// Then save the participant
	if (!$participant->isUpdate() || ($participant->getAddedById() == $preRegisterUser->getId()) |
		($participant->getUserId() == $preRegisterUser->getId())
	) {
		if (SettingsApi::getSetting(SettingsApi::SHOW_STUDENT) == 1) {
			$participant->setStudent($form_state['values']['addparticipantstudent']);
		}
		$participant->setUser($user);

		$participant->save();
	}

	// Then save the paper
	if (ParticipantTypeApi::containsTypeWithPaper($form_state['values']['addparticipanttype'])) {
		$paper->setUser($user);
		$paper->setSession($session);
		$paper->setTitle($form_state['values']['addparticipantpapertitle']);
		$paper->setAbstr($form_state['values']['addparticipantpaperabstract']);

		$paper->save();
	}
	else {
		$paper->delete();
	}

	// Last the types
	foreach ($form_state['values']['addparticipanttype'] as $typeId => $type) {
		if ($typeId == $type) {
			$foundInstance = false;
			foreach ($allToDelete as $key => $instance) {
				if ($instance->getTypeId() == $typeId) {
					$foundInstance = true;
					unset($allToDelete[$key]);
					break;
				}
			}

			if (!$foundInstance) {
				$sessionParticipant = new SessionParticipantApi();
				$sessionParticipant->setSession($session);
				$sessionParticipant->setUser($user);
				$sessionParticipant->setType($typeId);
				$sessionParticipant->save();
			}
		}
	}

	foreach ($allToDelete as $instance) {
		$instance->delete();
	}

	// Now go back to the session form
	$state->setMultiPageData(array('session' => $session));

	return 'preregister_session_form';
}

/**
 * What is the previous page?
 */
function preregister_sessionparticipant_form_back($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$data = $state->getFormData();

	$session = $data['session'];
	$state->setMultiPageData(array('session' => $session));

	return 'preregister_session_form';
}

/**
 * Remove the session participant
 */
function preregister_sessionparticipant_form_remove($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$data = $state->getFormData();

	$session = $data['session'];
	$sessionParticipants = $data['session_participants'];

	foreach ($sessionParticipants as $sessionParticipant) {
		$sessionParticipant->delete();
	}

	// Now go back to the session page
	$state->setMultiPageData(array('session' => $session));

	return 'preregister_session_form';
}
