<?php

/**
 * Implements hook_form()
 */
function preregister_paper_form($form, &$form_state) {
	$flow = new PreRegistrationFlow($form_state);
	$participant = $flow->getParticipant();

	$data = $flow->getMultiPageData();
	$paper = $data['paper'];

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// PAPER

	$form['paper'] = array(
		'#type'  => 'fieldset',
		'#title' => t('Register a paper'),
	);

	/* $form['ct'.$ct++] = array(
		'#type' => 'markup',
		'#markup' => '<span class="iishconference_container_header">Paper info</span>',
		'#prefix' => '<div class="iishconference_container_inline">',
		'#suffix' => '</div>',
		);*/

	$form['paper']['papertitle'] = array(
		'#type'          => 'textfield',
		'#title'         => t('Paper title'),
		'#required'      => true,
		'#size'          => 40,
		'#maxlength'     => 255,
		//	'#prefix' => '<div class="iishconference_container_inline">',
		//	'#suffix' => '</div>',
		'#default_value' => $paper->getTitle(),
	);

	$form['paper']['paperabstract'] = array(
		'#type'          => 'textarea',
		'#title'         => t('Abstract'),
		'#required'      => true,
		'#description'   => '<em>' . t('(max. 500 words)') . '</em>',
		'#rows'          => 2,
		'#default_value' => $paper->getAbstr(),
	);

	$form['paper']['coauthors'] = array(
		'#type'          => 'textfield',
		'#title'         => t('Co-authors'),
		'#size'          => 40,
		'#maxlength'     => 255,
		//	'#prefix'        => '<div class="container-inline bottommargin">',
		//	'#suffix'        => '</div>',
		'#default_value' => $paper->getCoAuthors(),
	);

	$networks = CachedConferenceApi::getNetworks();
	$networkOptions = CRUDApiClient::getAsKeyValueArray($networks);

	$form['paper']['proposednetwork'] = array(
		'#type'          => 'select',
		'#title'         => t('Proposed network'),
		'#options'       => $networkOptions,
		//	'#prefix'        => '<div class="iishconference_container_inline">',
		//	'#suffix'        => '</div>',
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
		'#title'         => t('Is this part of an existing session?'),
		'#default_value' => (($paper->getSessionProposal() !== null) &&
				(strlen(trim($paper->getSessionProposal())) > 0)),
	);

	$form['paper']['proposedsession'] = array(
		'#type'          => 'textfield',
		'#title'         => t('Proposed session'),
		'#size'          => 40,
		'#maxlength'     => 255,
		//'#prefix'        => '<div id="textfields"><div class="iishconference_container_inline">',
		//'#suffix'        => '</div></div>',
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
			'#title'         => t('Would you like to participant in the "@awardName"?',
					array('@awardName' => SettingsApi::getSetting(SettingsApi::AWARD_NAME))) . '&nbsp; <em>(' .
				l(t('more about the award'), '/award', array('attributes' => array('target' => '_blank'))) . ')</em>',
			'#default_value' => $participant->getAward(),
		);
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// AUDIO VISUAL EQUIPMENT

	$equipment = CachedConferenceApi::getEquipment();
	$equipmentOptions = CRUDApiClient::getAsKeyValueArray($equipment);

	$form['equipment'] = array(
		'#type'  => 'fieldset',
		'#title' => t('Audio/visual equipment'),
	);

	$form['equipment']['audiovisual'] = array(
		'#type'          => 'checkboxes',
		'#description'   => t('Select the equipment you will need for your presentation.'),
		'#options'       => $equipmentOptions,
		'#default_value' => $paper->getEquipmentIds(),
	);

	$form['equipment']['extraaudiovisual'] = array(
		'#type'          => 'textarea',
		'#title'         => t('Extra audio/visual request'),
		'#rows'          => 2,
		'#default_value' => $paper->getEquipmentComment(),
	);

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
		'#value' => t('Save paper'),
	);

	// We can only remove a paper if it has been persisted
	if ($paper->isUpdate()) {
		$form['submit_remove'] = array(
			'#type'                    => 'submit',
			'#name'                    => 'submit_remove',
			'#value'                   => t('Remove paper'),
			'#submit'                  => array('preregister_form_submit'),
			'#limit_validation_errors' => array(),
			'#attributes'              => array('onclick' =>
				                                    'if (!confirm("' .
				                                    t('Are you sure you want to remove this paper?') .
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
				t('Proposed session field is required if you check \'Is part of an existing session?\'.'));
		}
	}
}

/**
 * Implements hook_form_submit()
 */
function preregister_paper_form_submit($form, &$form_state) {
	$flow = new PreRegistrationFlow($form_state);
	$user = $flow->getUser();
	$participant = $flow->getParticipant();

	$data = $flow->getMultiPageData();
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
	$equipment = array();
	foreach (CachedConferenceApi::getEquipment() as $equipmentInstance) {
		$value = $form_state['values']['audiovisual'][$equipmentInstance->getId()];
		if ($equipmentInstance->getId() == $value) {
			$equipment[] = $equipmentInstance->getId();
		}
	}
	$paper->setEquipment($equipment);

	$paper->save();

	// Then save the participant
	if ((SettingsApi::getSetting(SettingsApi::SHOW_AWARD) == 1) && $participant->getStudent()) {
		$participant->setAward($form_state['values']['award']);
		$participant->save();
	}

	// Move back to the 'type of registration' page, clean cached data
	$flow->setMultiPageData(array());

	return 'preregister_typeofregistration_form';
}

/**
 * What is the previous page?
 */
function preregister_paper_form_back($form, &$form_state) {
	return 'preregister_typeofregistration_form';
}

/**
 * Remove the paper
 */
function preregister_paper_form_remove($form, &$form_state) {
	$flow = new PreRegistrationFlow($form_state);
	$multiPageData = $flow->getMultiPageData();

	$paper = $multiPageData['paper'];
	$paper->delete();

	$flow->setMultiPageData(array());

	return 'preregister_typeofregistration_form';
}

