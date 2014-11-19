<?php

/**
 * Implements hook_form()
 */
function preregister_comments_form($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$participant = $state->getParticipant();

	// + + + + + + + + + + + + + + + + + + + + + + + +

	$form['comments'] = array(
		'#type'  => 'fieldset',
		'#title' => iish_t('General comments'),
	);

	$form['comments']['comment'] = array(
		'#type'          => 'textarea',
		'#title'         => '',
		'#rows'          => 10,
		'#default_value' => $participant->getExtraInfo(),
	);

	// + + + + + + + + + + + + + + + + + + + + + + + +

	$form['submit_back'] = array(
		'#type'                    => 'submit',
		'#name'                    => 'submit_back',
		'#value'                   => iish_t('Back to previous step'),
		'#submit'                  => array('preregister_form_submit'),
		'#limit_validation_errors' => array(),
	);

	$form['submit'] = array(
		'#type'  => 'submit',
		'#name'  => 'submit',
		'#value' => iish_t('Next to confirmation page'),
	);

	return $form;
}

/**
 * Implements hook_form_submit()
 */
function preregister_comments_form_submit($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$participant = $state->getParticipant();

	$participant->setExtraInfo($form_state['values']['comment']);
	$participant->save();

	return 'preregister_confirm_form';
}

/**
 * What is the previous page?
 */
function preregister_comments_form_back($form, &$form_state) {
	// Move to the 'type of registration' page if either author or organizer registration had been / is possible
	$showAuthor = SettingsApi::getSetting(SettingsApi::SHOW_AUTHOR_REGISTRATION);
	$showOrganizer = SettingsApi::getSetting(SettingsApi::SHOW_ORGANIZER_REGISTRATION);
	$types = SettingsApi::getSetting(SettingsApi::SHOW_SESSION_PARTICIPANT_TYPES_REGISTRATION);
	$typesToShow = SettingsApi::getArrayOfValues($types);

	if (($showAuthor == 1) || ($showOrganizer == 1) || (count($typesToShow) > 0)) {
		return 'preregister_typeofregistration_form';
	}
	else {
		return 'preregister_personalinfo_form';
	}
}
