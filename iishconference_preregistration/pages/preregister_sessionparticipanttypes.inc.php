<?php

/**
 * Implements hook_form()
 */
function preregister_sessionparticipanttypes_form($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$formData = array();

	// + + + + + + + + + + + + + + + + + + + + + + + +

	foreach (PreRegistrationUtils::getParticipantTypesForUser() as $participantType) {
		$form['type_' . $participantType->getId()] = array(
			'#type'  => 'fieldset',
			'#title' => iish_t('I would like to be a @type in the sessions',
				array('@type' => strtolower($participantType))),
		);

		$storedSessionTypes = PreRegistrationUtils::getSessionParticipantsOfUserWithType($state, $participantType);
		$formData['sessions_' . $participantType->getId()] = $storedSessionTypes;

		$form['type_' . $participantType->getId()]['session_' . $participantType->getId()] = array(
			'#type'          => 'select',
			'#title'         => '',
			'#options'       => CachedConferenceApi::getSessionsKeyValue(),
			'#size'          => 12,
			'#multiple'      => true,
			'#default_value' => SessionParticipantApi::getForMethod($storedSessionTypes, 'getSessionId'),
			'#attributes'    => array('class' => array('iishconference_new_line')),
			'#description'   => '<i>' . iish_t('Use CTRL key to select multiple sessions.') . '</i>',
		);
	}

	$state->setFormData($formData);

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
		'#value' => iish_t('Save'),
	);

	return $form;
}

/**
 * Implements hook_form_submit()
 */
function preregister_sessionparticipanttypes_form_submit($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$user = $state->getUser();
	$data = $state->getFormData();

	$sessions = CachedConferenceApi::getSessionsKeyValue();

	foreach (PreRegistrationUtils::getParticipantTypesForUser() as $participantType) {
		$allToDelete = $data['sessions_' . $participantType->getId()];
		$sessionIdsForType = $form_state['values']['session_' . $participantType->getId()];

		foreach ($sessionIdsForType as $sessionId) {
			$foundInstance = false;

			foreach ($allToDelete as $key => $instance) {
				if ($instance->getSessionId() == $sessionId) {
					$foundInstance = true;
					unset($allToDelete[$key]);
					break;
				}
			}

			if (!$foundInstance && array_key_exists($sessionId, $sessions)) {
				$sessionParticipant = new SessionParticipantApi();
				$sessionParticipant->setUser($user);
				$sessionParticipant->setAddedBy($user);
				$sessionParticipant->setSession($sessionId);
				$sessionParticipant->setType($participantType);
				$sessionParticipant->save();
			}
		}

		// Delete all previously saved session participant choices that were not chosen again
		foreach ($allToDelete as $instance) {
			$instance->delete();
		}
	}

	return PreRegistrationPage::TYPE_OF_REGISTRATION;
}

/**
 * What is the previous page?
 */
function preregister_sessionparticipanttypes_form_back($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$state->setMultiPageData(array());

	return PreRegistrationPage::TYPE_OF_REGISTRATION;
}

