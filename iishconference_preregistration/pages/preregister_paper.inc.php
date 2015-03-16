<?php

/**
 * Implements hook_form()
 */
function preregister_paper_form($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$participant = $state->getParticipant();

	$data = $state->getMultiPageData();
	$paper = $data['paper'];

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// PAPER

	$form['paper'] = array(
		'#type'  => 'fieldset',
		'#title' => iish_t('Register a paper'),
	);

	$form['paper']['papertitle'] = array(
		'#type'          => 'textfield',
		'#title'         => iish_t('Paper title'),
		'#required'      => true,
		'#size'          => 40,
		'#maxlength'     => 255,
		'#default_value' => $paper->getTitle(),
	);

	$form['paper']['paperabstract'] = array(
		'#type'          => 'textarea',
		'#title'         => iish_t('Abstract'),
		'#required'      => true,
		'#description'   => '<em>' . iish_t('(max. 500 words)') . '</em>',
		'#rows'          => 2,
		'#default_value' => $paper->getAbstr(),
	);

	$form['paper']['coauthors'] = array(
		'#type'          => 'textfield',
		'#title'         => iish_t('Co-authors'),
		'#size'          => 40,
		'#maxlength'     => 255,
		'#default_value' => $paper->getCoAuthors(),
	);

	if (PreRegistrationUtils::useSessions()) {
		$form['paper']['session'] = array(
			'#type'          => 'select',
			'#title'         => iish_t('Proposed session'),
			'#options'       => CachedConferenceApi::getSessionsKeyValue(),
			'#empty_option'  => '- ' . iish_t('Select a session') . ' -',
			'#default_value' => $paper->getSessionId(),
			'#attributes'    => array('class' => array('iishconference_new_line')),
		);
	}
	else {
		$form['paper']['proposednetwork'] = array(
			'#type'          => 'select',
			'#title'         => iish_t('Proposed network'),
			'#options'       => CRUDApiClient::getAsKeyValueArray(CachedConferenceApi::getNetworks()),
			'#size'          => 4,
			'#required'      => true,
			'#default_value' => $paper->getNetworkProposalId(),
		);

		PreRegistrationUtils::hideAndSetDefaultNetwork($form['paper']['proposednetwork']);

		$form['paper']['partofexistingsession'] = array(
			'#type'          => 'checkbox',
			'#title'         => iish_t('Is this part of an existing session?'),
			'#default_value' => (
					($paper->getSessionProposal() !== null) &&
					(strlen(trim($paper->getSessionProposal())) > 0)
				),
		);

		$form['paper']['proposedsession'] = array(
			'#type'          => 'textfield',
			'#title'         => iish_t('Proposed session'),
			'#size'          => 40,
			'#maxlength'     => 255,
			'#default_value' => $paper->getSessionProposal(),
			'#states'        => array(
				'visible' => array(
					':input[name="partofexistingsession"]' => array('checked' => true),
				),
			),
		);
	}

	if ((SettingsApi::getSetting(SettingsApi::SHOW_AWARD) == 1) && $participant->getStudent()) {
		$form['paper']['award'] = array(
			'#type'          => 'checkbox',
			'#title'         => iish_t('Would you like to participate in the "@awardName"?',
					array('@awardName' => SettingsApi::getSetting(SettingsApi::AWARD_NAME))) . '&nbsp; <em>(' .
				l(iish_t('more about the award'), 'award', array('attributes' => array('target' => '_blank')))
				. ')</em>',
			'#default_value' => $participant->getAward(),
		);
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// AUDIO VISUAL EQUIPMENT

	if (SettingsApi::getSetting(SettingsApi::SHOW_EQUIPMENT) == 1) {
		$equipment = CachedConferenceApi::getEquipment();

		$form['equipment'] = array(
			'#type'  => 'fieldset',
			'#title' => iish_t('Audio/visual equipment'),
		);

		if (is_array($equipment) && (count($equipment) > 0)) {
			$equipmentOptions = CRUDApiClient::getAsKeyValueArray($equipment);

			$form['equipment']['audiovisual'] = array(
				'#type'          => 'checkboxes',
				'#description'   => iish_t('Select the equipment you will need for your presentation.'),
				'#options'       => $equipmentOptions,
				'#default_value' => $paper->getEquipmentIds(),
			);
		}

		$form['equipment']['extraaudiovisual'] = array(
			'#type'          => 'textarea',
			'#title'         => iish_t('Extra audio/visual request'),
			'#description'   => iish_t('Every room has a beamer and powerpoint available.'),
			'#rows'          => 2,
			'#default_value' => $paper->getEquipmentComment(),
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

	$form['submit'] = array(
		'#type'  => 'submit',
		'#name'  => 'submit',
		'#value' => iish_t('Save paper'),
	);

	// We can only remove a paper if it has been persisted
	if ($paper->isUpdate()) {
		$form['submit_remove'] = array(
			'#type'                    => 'submit',
			'#name'                    => 'submit_remove',
			'#value'                   => iish_t('Remove paper'),
			'#submit'                  => array('preregister_form_submit'),
			'#limit_validation_errors' => array(),
			'#attributes'              => array('onclick' =>
				                                    'if (!confirm("' .
				                                    iish_t('Are you sure you want to remove this paper?') .
				                                    '")) { return false; }'),
		);
	}

	return $form;
}

/**
 * Implements hook_form_validate()
 */
function preregister_paper_form_validate($form, &$form_state) {
	if (!PreRegistrationUtils::useSessions() && $form_state['values']['partofexistingsession']) {
		if (strlen(trim($form_state['values']['proposedsession'])) === 0) {
			form_set_error('proposedsession',
				iish_t('Proposed session field is required if you check \'Is part of an existing session?\'.'));
		}
	}
}

/**
 * Implements hook_form_submit()
 */
function preregister_paper_form_submit($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$user = $state->getUser();
	$participant = $state->getParticipant();

	$data = $state->getMultiPageData();
	$paper = $data['paper'];

	// First save the paper
	$paper->setUser($user);
	$paper->setTitle($form_state['values']['papertitle']);
	$paper->setAbstr($form_state['values']['paperabstract']);
	$paper->setCoAuthors($form_state['values']['coauthors']);

	// Either save a session or save a network proposal
	$firstSessionId = $paper->getSessionId();
	if (PreRegistrationUtils::useSessions()) {
		$paper->setSession($form_state['values']['session']);
	}
	else {
		$paper->setNetworkProposal($form_state['values']['proposednetwork']); // TODO: QUESTION MARK ???
		$paper->setSessionProposal($form_state['values']['proposedsession']);
	}

	// Save equipment
	if (SettingsApi::getSetting(SettingsApi::SHOW_EQUIPMENT) == 1) {
		$allEquipment = CachedConferenceApi::getEquipment();
		if (is_array($allEquipment) && (count($allEquipment) > 0)) {
			$equipment = array();
			foreach ($allEquipment as $equipmentInstance) {
				$value = $form_state['values']['audiovisual'][$equipmentInstance->getId()];
				if ($equipmentInstance->getId() == $value) {
					$equipment[] = $equipmentInstance->getId();
				}
			}
			$paper->setEquipment($equipment);
		}

		$paper->setEquipmentComment($form_state['values']['extraaudiovisual']);
	}

	$paper->save();

	// Then save the participant
	if ((SettingsApi::getSetting(SettingsApi::SHOW_AWARD) == 1) && $participant->getStudent()) {
		$participant->setAward($form_state['values']['award']);
		$participant->save();
	}

	// If we can add a paper to a session, then also create a session participant registration
	if (PreRegistrationUtils::useSessions()) {
		// We changed the session, remove session registration from the first registration
		if (($paper->getSessionId() !== null) &&
			($firstSessionId !== null) &&
			($paper->getSessionId() != $firstSessionId)
		) {
			$prevSessionParticipant = PreRegistrationUtils::getSessionParticipantsOfUserWithSessionAndType(
				$state, $firstSessionId, ParticipantTypeApi::AUTHOR_ID
			);

			$prevSessionParticipant->delete();
		}

		$sessionParticipant = PreRegistrationUtils::getSessionParticipantsOfUserWithSessionAndType(
			$state, $paper->getSessionId(), ParticipantTypeApi::AUTHOR_ID
		);

		// We added a session, but have no session participant yet
		if (($paper->getSessionId() !== null) && ($sessionParticipant === null)) {
			$sessionParticipant = new SessionParticipantApi();
			$sessionParticipant->setUser($user);
			$sessionParticipant->setSession($paper->getSessionId());
			$sessionParticipant->setType(ParticipantTypeApi::AUTHOR_ID);
			$sessionParticipant->save();
		}

		// Or maybe we removed the session, but still have the session participant
		if (($paper->getSessionId() === null) && ($sessionParticipant !== null)) {
			$sessionParticipant->delete();
		}
	}

	// Move back to the 'type of registration' page, clean cached data
	$state->setMultiPageData(array());

	return PreRegistrationPage::TYPE_OF_REGISTRATION;
}

/**
 * What is the previous page?
 */
function preregister_paper_form_back($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$state->setMultiPageData(array());

	return PreRegistrationPage::TYPE_OF_REGISTRATION;
}

/**
 * Remove the paper
 */
function preregister_paper_form_remove($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$multiPageData = $state->getMultiPageData();

	$paper = $multiPageData['paper'];
	$paper->delete();

	// If we added the removed paper to a session, then we should also remove the session participant registration
	if (PreRegistrationUtils::useSessions() && ($paper->getSessionId() !== null)) {
		$sessionParticipant = PreRegistrationUtils::getSessionParticipantsOfUserWithSessionAndType(
			$state, $paper->getSessionId(), ParticipantTypeApi::AUTHOR_ID
		);

		if ($sessionParticipant !== null) {
			$sessionParticipant->delete();
		}
	}

	$state->setMultiPageData(array());

	return PreRegistrationPage::TYPE_OF_REGISTRATION;
}

