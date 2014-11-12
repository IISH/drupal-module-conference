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

	$networks = CachedConferenceApi::getNetworks();
	$networkOptions = CRUDApiClient::getAsKeyValueArray($networks);

	$form['paper']['proposednetwork'] = array(
		'#type'          => 'select',
		'#title'         => iish_t('Proposed @network', array('@network' => NetworkApi::getNetworkName(true, true))),
		'#options'       => $networkOptions,
		'#size'          => 3,
		'#required'      => true,
		'#default_value' => $paper->getNetworkProposalId(),
	);

	if (SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) != 1) {
		$form['paper']['proposednetwork']['#access'] = false;
		$form['paper']['proposednetwork']['#default_value'] =
			SettingsApi::getSetting(SettingsApi::DEFAULT_NETWORK_ID);
	}

	$form['paper']['partofexistingsession'] = array(
		'#type'          => 'checkbox',
		'#title'         => iish_t('Is this part of an existing session?'),
		'#default_value' => (($paper->getSessionProposal() !== null) &&
				(strlen(trim($paper->getSessionProposal())) > 0)),
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

	if ((SettingsApi::getSetting(SettingsApi::SHOW_AWARD) == 1) && $participant->getStudent()) {
		$form['paper']['award'] = array(
			'#type'          => 'checkbox',
			'#title'         => iish_t('Would you like to participate in the "@awardName"?',
					array('@awardName' => SettingsApi::getSetting(SettingsApi::AWARD_NAME))) . '&nbsp; <em>(' .
				l(t('more about the award'), 'award', array('attributes' => array('target' => '_blank'))) . ')</em>',
			'#default_value' => $participant->getAward(),
		);
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// AUDIO VISUAL EQUIPMENT

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
	if ($form_state['values']['partofexistingsession']) {
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
	$paper->setNetworkProposal($form_state['values']['proposednetwork']);
	$paper->setSessionProposal($form_state['values']['proposedsession']);
	$paper->setEquipmentComment($form_state['values']['extraaudiovisual']);

	// Save equipment
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

	$paper->save();

	// Then save the participant
	if ((SettingsApi::getSetting(SettingsApi::SHOW_AWARD) == 1) && $participant->getStudent()) {
		$participant->setAward($form_state['values']['award']);
		$participant->save();
	}

	// Move back to the 'type of registration' page, clean cached data
	$state->setMultiPageData(array());

	return 'preregister_typeofregistration_form';
}

/**
 * What is the previous page?
 */
function preregister_paper_form_back($form, &$form_state) {
    $state = new PreRegistrationState($form_state);
    $state->setMultiPageData(array());

	return 'preregister_typeofregistration_form';
}

/**
 * Remove the paper
 */
function preregister_paper_form_remove($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$multiPageData = $state->getMultiPageData();

	$paper = $multiPageData['paper'];
	$paper->delete();

	$state->setMultiPageData(array());

	return 'preregister_typeofregistration_form';
}

