<?php

require_once 'preregister_login.inc.php';
require_once 'preregister_personalinfo_edit.inc.php';
require_once 'preregister_personalinfo_preview.inc.php';
require_once 'preregister_typeofregistration.inc.php';
require_once 'preregister_registerpaper_edit.inc.php';
require_once 'preregister_registerpaper_preview.inc.php';
require_once 'preregister_chairdiscussantpool_edit.inc.php';
require_once 'preregister_chairdiscussantpool_preview.inc.php';
require_once 'preregister_completed.inc.php';
require_once 'preregister_session_list.inc.php';
require_once 'preregister_session_edit.inc.php';
require_once 'preregister_sessionparticipant.inc.php';
require_once 'preregister_sessionparticipant_remove.inc.php';
require_once 'preregister_session_remove.inc.php';

/**
 * Primary form builder for the pre registration
 * Implements hook_form()
 */
function preregister_form($form, &$form_state) {
	// Load ECA settings
	$ecaSettings = CachedConferenceApi::getSettings();
	$closesOn = $ecaSettings[SettingsApi::PREREGISTARTION_CLOSES_ON];
	$startsOn = $ecaSettings[SettingsApi::PREREGISTARTION_STARTS_ON];

	// Check if user is already registered for the current conference, if so, show message no changes possible
	if (LoggedInUserDetails::isLoggedIn() && LoggedInUserDetails::isAParticipant()) {
		$form['ct1'] = array(
			'#type'   => 'markup',
			'#markup' => '<span class="eca_warning">' .
				t('You are already pre-registered for the @codeYear conference. It is not allowed to modify online ' .
					'your data after your data has been checked by the conference organization. If you would like to ' .
					'make some changes please send an e-mail to @code. Please go to your !link to check the data.',
					array('@codeYear' => CachedConferenceApi::getEventDate()->getLongCodeAndYear(), '@code' => CachedConferenceApi::getEventDate()->getEvent()->getCode(),
					      '!link'     => l(t('personal page'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'personal-page'))) .
				'</span>',
		);
	}
	// Check if preregistration is closed
	else if (($closesOn !== null) && (strlen(trim($closesOn)) > 0) && (time() >= strtotime($closesOn))) {
		$form['ct1'] = array(
			'#type'   => 'markup',
			'#markup' =>
				'<span class="eca_warning">' .
				ConferenceMisc::getCleanHTML($ecaSettings[SettingsApi::PREREGISTARTION_CLOSES_ON_MESSAGE]) .
				'</span>',
		);

	}
	// Check if preregistration has started
	else if (($startsOn !== null) && (strlen(trim($startsOn)) > 0) && (time() < strtotime($startsOn))) {
		$form['ct1'] = array(
			'#type'   => 'markup',
			'#markup' =>
				'<span class="eca_warning">' .
				ConferenceMisc::getCleanHTML($ecaSettings[SettingsApi::PREREGISTARTION_STARTS_ON_MESSAGE]) .
				'</span>',
		);
	}
	// Initialize
	else {
		// TODO: Don't hang on to submitted data in form state input.
		if ($form_state['rebuild']) {
			$form_state['input'] = array();
		}

		$flow = new PreRegistrationFlow($form_state);

		$functionName = $flow->getCurrentStep() . '_form';
		if (function_exists($functionName)) {
			$form = $functionName($form, $form_state);
		}

		/*
		$user_id = getIdLoggedInUser();
		if ( $user_id == '' || $user_id == '0' ) {
			$loggedin = false;
			$firstpage = 'preregister_login_form';
		} else {
			$loggedin = true;
	// TODOLATER bij submit van een form onthou de naam van de huidige form
			$firstpage = 'preregister_personalinfo_edit_form';
		}*/

		/*if (empty($form_state['storage'])) {
			// No step has been set so start with the first.
			$form_state['storage'] = array(
				'step' => $firstpage,
			);
		}

		// Return the form for the current step.
		$function = $form_state['storage']['step'];

		if ($loggedin) {
			if (!isset($_SESSION['storage']['naw_downloaded'])) {
				// load NAW data
				loadData($user_id, $form_state);
				$_SESSION['storage']['naw_downloaded'] = '1';
			}
		}

		$form = $function($form, $form_state);*/
	}

	return $form;
}

/**
 * Primary validate handler for the pre registration
 * Implements hook_form_validate()
 */
function preregister_form_validate($form, &$form_state) {
	$flow = new PreRegistrationFlow($form_state);

	if ($flow->userMovesForward()) {
		$functionName = $flow->getCurrentStep() . '_form_validate';
		if (function_exists($functionName)) {
			$functionName($form, $form_state);
		}
	}
}

/**
 * Primary submit handler for the pre registration
 * Implements hook_form_submit()
 */
function preregister_form_submit($form, &$form_state) {
	$flow = new PreRegistrationFlow($form_state);

	if ($flow->userMovesForward()) {
		$functionName = $flow->getCurrentStep() . '_form_submit';
		if (function_exists($functionName)) {
			$functionName($form, $form_state);
		}
	}

	$flow->setNextStep();

	/*
	$values = $form_state['values'];
	if (isset($values['back']) && $values['op'] == $values['back']) {
		// Moving back in form.
		$step = $form_state['storage']['step'];
		// Call current step submit handler if it exists to unset step form data.
		if (function_exists($step . '_submit')) {
			$function = $step . '_submit';
			$function($form, $form_state);
		}
		// Remove the last saved step so we use it next.
		$last_step = array_pop($form_state['storage']['steps']);
		$form_state['storage']['step'] = $last_step;
	}
	else {
		// Record step.
		$step = $form_state['storage']['step'];
		$form_state['storage']['steps'][] = $step;
		// Call step submit handler if it exists.
		if (function_exists($step . '_submit')) {
			$function = $step . '_submit';
			$function($form, $form_state);
		}
	}*/
}

