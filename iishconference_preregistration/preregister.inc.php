<?php

require_once 'preregister_login.inc.php';
require_once 'preregister_password.inc.php';
require_once 'preregister_personalinfo.inc.php';
require_once 'preregister_typeofregistration.inc.php';
require_once 'preregister_paper.inc.php';
require_once 'preregister_session.inc.php';
require_once 'preregister_sessionparticipant.inc.php';
require_once 'preregister_confirm.inc.php';

/**
 * Primary form builder for the pre registration
 * Implements hook_form()
 */
function preregister_form($form, &$form_state) {
	$form['#attributes']['class'][] = 'iishconference_form';

	// Load ECA settings
	$closesOn = SettingsApi::getSetting(SettingsApi::PREREGISTRATION_CLOSES_ON);
	$startsOn = SettingsApi::getSetting(SettingsApi::PREREGISTRATION_STARTS_ON);

	// Check if user is already registered for the current conference, if so, show message no changes possible
	if (LoggedInUserDetails::isLoggedIn() && LoggedInUserDetails::isAParticipant()) {
		$form['ct1'] = array(
			'#type'   => 'markup',
			'#markup' => '<span class="eca_warning">' .
				t('You are already pre-registered for the @codeYear. It is not allowed to modify online ' .
					'your data after your data has been checked by the conference organization. If you would like to ' .
					'make some changes please send an e-mail to @code. Please go to your !link to check the data.',
					array('@codeYear' => CachedConferenceApi::getEventDate()->getLongNameAndYear(),
					      '@code'     => CachedConferenceApi::getEventDate()->getEvent()->getShortName(),
					      '!link'     => l(t('personal page'),
						      SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'personal-page'))) .
				'</span>',
		);

		return $form;
	}

	// Check if preregistration is closed
	if (($closesOn !== null) && (strlen(trim($closesOn)) > 0) && (time() >= strtotime($closesOn))) {
		$form['ct1'] = array(
			'#type'   => 'markup',
			'#markup' =>
				'<span class="eca_warning">' .
				ConferenceMisc::getCleanHTML(SettingsApi::getSetting(SettingsApi::PREREGISTRATION_CLOSES_ON_MESSAGE)) .
				'</span>',
		);

		return $form;
	}

	// Check if preregistration has started
	if (($startsOn !== null) && (strlen(trim($startsOn)) > 0) && (time() < strtotime($startsOn))) {
		$form['ct1'] = array(
			'#type'   => 'markup',
			'#markup' =>
				'<span class="eca_warning">' .
				ConferenceMisc::getCleanHTML(SettingsApi::getSetting(SettingsApi::PREREGISTRATION_STARTS_ON_MESSAGE)) .
				'</span>',
		);

		return $form;
	}

	$state = new PreRegistrationState($form_state);

	$functionName = $state->getCurrentStep();
	if (function_exists($functionName)) {
		$form = $functionName($form, $form_state);
	}

	return $form;
}

/**
 * Primary validate handler for the pre registration
 * Implements hook_form_validate()
 */
function preregister_form_validate($form, &$form_state) {
	$state = new PreRegistrationState($form_state);

	$functionName = $state->getCurrentStep() . '_validate';
	if (function_exists($functionName)) {
		$functionName($form, $form_state);
	}
}

/**
 * Primary submit handler for the pre registration
 * Implements hook_form_submit()
 */
function preregister_form_submit($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$nextStepName = $state->getCurrentStep();

	$userPressedPrevButton = strpos($form_state['triggering_element']['#name'], 'submit_back') === 0;
	$userPressedRemoveButton = strpos($form_state['triggering_element']['#name'], 'submit_remove') === 0;

	// Determine which function to call
	if ($userPressedPrevButton) {
		$functionName = $state->getCurrentStep() . '_back';
	}
	else if ($userPressedRemoveButton) {
		$functionName = $state->getCurrentStep() . '_remove';
	}
	else {
		$functionName = $state->getCurrentStep() . '_submit';
	}

	// Call the function if it exists, otherwise we will stay on the same step
	if (function_exists($functionName)) {
		$nextStepName = $functionName($form, $form_state);
	}

	$state->setNextStep($nextStepName);
}

