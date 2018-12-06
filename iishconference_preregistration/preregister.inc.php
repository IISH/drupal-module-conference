<?php

require_once 'pages/preregister_login.inc.php';
require_once 'pages/preregister_password.inc.php';
require_once 'pages/preregister_personalinfo.inc.php';
require_once 'pages/preregister_typeofregistration.inc.php';
require_once 'pages/preregister_paper.inc.php';
require_once 'pages/preregister_session.inc.php';
require_once 'pages/preregister_sessionparticipant.inc.php';
require_once 'pages/preregister_sessionparticipanttypes.inc.php';
require_once 'pages/preregister_comments.inc.php';
require_once 'pages/preregister_confirm.inc.php';

/**
 * Primary form builder for the pre registration
 * Implements hook_form()
 */
function preregister_form($form, &$form_state) {
	$form['#attributes']['class'][] = 'iishconference_form';

	// Load ECA settings
	$closesOn = SettingsApi::getSetting(SettingsApi::PREREGISTRATION_LASTDATE);
	$startsOn = SettingsApi::getSetting(SettingsApi::PREREGISTRATION_STARTDATE);

	// Check if user is already registered for the current conference, if so, show message no changes possible
	if (LoggedInUserDetails::isLoggedIn() && LoggedInUserDetails::isAParticipant()) {
		$form['ct1'] = array(
			'#type'   => 'markup',
			'#markup' => '<span class="eca_warning">' .
				iish_t('You are already pre-registered for the @codeYear. It is not possible to modify your pre-registration online ' .
					' after your data has been checked by the conference organization. If you would like to ' .
					'make some changes please send an e-mail to @code. Please go to your !link to check the data.',
					array('@codeYear' => CachedConferenceApi::getEventDate()->getLongNameAndYear(),
					      '@code'     => CachedConferenceApi::getEventDate()->getEvent()->getShortName(),
					      '!link'     => l(iish_t('personal page'),
						      SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'personal-page'))) .
				'</span>',
		);

		// show 'go to final registration' link
		$finalRegistrationLastDate = strtotime(SettingsApi::getSetting(SettingsApi::FINAL_REGISTRATION_LASTDATE));
		$isFinalRegistrationOpen = ConferenceMisc::isOpenForLastDate($finalRegistrationLastDate);
		if (module_exists('iishconference_finalregistration') && $isFinalRegistrationOpen) {

			$form['ct2'] = array(
				'#type'   => 'markup',
				'#markup' => '<br /><br />' .
					 '<span class="eca_remark heavy">' .
					 iish_t('Please go to !link.',
					    array('!link' => l(iish_t('final registration and payment'),
					        SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'final-registration'))) .
					 '</span>'
			);
		}

		return $form;
	}

	// Check if preregistration is closed
	if (($closesOn !== null) && (strlen(trim($closesOn)) > 0) && (!ConferenceMisc::isOpenForLastDate(strtotime($closesOn)))) {
		$form['ct1'] = array(
			'#type'   => 'markup',
			'#markup' =>
				'<span class="eca_warning">' .
				ConferenceMisc::getCleanHTML(iish_t('Please note it is no longer possible to pre-register online. ' .
					'If you wish to register as listener, you can do so at the conference desk. ' .
					'If you have been in touch with the network chairs or session organizers about a paper proposal ' .
					'and still have to pre-register, please contact the secretariat. ' .
					'It is still possible to do the Final Registration and Payment. ' .
					'If you haven\'t payed the conference fee, please do it as soon as possible.',
					array('!email' => ConferenceMisc::encryptEmailAddress(
							SettingsApi::getSetting(SettingsApi::DEFAULT_ORGANISATION_EMAIL))))) .
				'</span>',
		);

		return $form;
	}

	// Check if preregistration has started
	if (($startsOn !== null) && (strlen(trim($startsOn)) > 0) && (!ConferenceMisc::isOpenForStartDate(strtotime($startsOn)))) {
		$form['ct1'] = array(
			'#type'   => 'markup',
			'#markup' =>
				'<span class="eca_warning">' .
				ConferenceMisc::getCleanHTML(iish_t('The pre-registration for this conference has not started yet.')) .
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

