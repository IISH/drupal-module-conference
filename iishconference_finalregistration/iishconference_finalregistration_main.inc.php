<?php
/**
 * @file
 * Describes the main purpose of this module, a form for users to finalize their registration and make a payment
 */

require_once 'iishconference_finalregistration_main_page.inc.php';
require_once 'iishconference_finalregistration_overview_page.inc.php';

/**
 * Implements hook_form()
 */
function iishconference_finalregistration_main_form($form, &$form_state) {
	// Make sure we always start at the main stage
	if (!isset($form_state['stage'])) {
		$form_state['stage'] = 'main';
	}

	if (!LoggedInUserDetails::isLoggedIn()) {
		// redirect to login page
		header('Location: ' . url(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())));
		die(t('Go to !login page.',
			array('!login' => l(t('login'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())))));
	}

	if (!LoggedInUserDetails::isAParticipant()) {
		drupal_set_message(t('You are not registered for the @conference conference. Please go to !link.',
				array('@conference' => CachedConferenceApi::getEventDate()->getLongNameAndYear(),
				      '!link'       => l(t('pre-registration form'),
					      SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'pre-registration'))),
			'warning');

		return '';
	}

	if (strtotime(SettingsApi::getSetting(SettingsApi::FINAL_REGISTRATION_CLOSES_ON)) < strtotime('today')) {
		drupal_set_message(t('The final registration is closed.'), 'warning');

		return '';
	}

	// Get fee amount information
	$participant = LoggedInUserDetails::getParticipant();
	$feeAmounts = $participant->getFeeAmounts();

	if (count($feeAmounts) === 0) {
		drupal_set_message(t('Something is wrong with your fee, please contact !email.',
				array('!email' => ConferenceMisc::encryptEmailAddress(
						SettingsApi::getSetting(SettingsApi::DEFAULT_ORGANISATION_EMAIL)))),
			'error');

		return '';
	}

	if (SettingsApi::getSetting(SettingsApi::SHOW_ACCOMPANYING_PERSONS) == 1) {
		$accompanyingPersonsFeeState = FeeStateApi::getAccompanyingPersonFee();

		if (($accompanyingPersonsFeeState === null) || (count($accompanyingPersonsFeeState->getFeeAmounts()) === 0)) {
			drupal_set_message(t('Something is wrong with your fee, please contact !email .',
				array('!email' => ConferenceMisc::encryptEmailAddress(
						SettingsApi::getSetting(SettingsApi::DEFAULT_ORGANISATION_EMAIL)))), 'error');

			return '';
		}
	}

	if ($participant->getPaymentId()) {
		$orderDetails = new PayWayMessage(array('orderid' => $participant->getPaymentId()));
		$order = $orderDetails->send('orderDetails');

		if (!empty($order)) {
			if ($order->get('payed') == 1) {
				drupal_set_message(t('You already finished the final registration for the @conference.') .
					'<br />' .
					t('If you have questions please contact the secretariat at !email .',
						array('@conference' => CachedConferenceApi::getEventDate()->getLongNameAndYear(),
						      '!email'      => ConferenceMisc::encryptEmailAddress(
								      SettingsApi::getSetting(SettingsApi::DEFAULT_ORGANISATION_EMAIL)))));

				return '';
			}
			else if ($order->get('willpaybybank')) {
				$form['will-pay-by-bank'] = array(
					'#type'   => 'markup',
					'#markup' =>
						'<span class="eca_warning">' .
						t('You chose to finish your final registration by bank transfer.') . '<br />' .
						t('!link for the bank transfer information.', array('!link' => l(t('Click here'),
							SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) .
							'final-registration/bank-transfer'))) .
						'<br />' . t('Please continue if you want to choose a different payment method.') .
						'</span>',
				);
			}
		}
		else {
			drupal_set_message(t('Currently it is not possible to proceed with the final registration. ' .
				'Please try again later...'), 'error');

			return '';
		}
	}

	return getFormForCurrentStage($form, $form_state);
}

/**
 * Returns the form based on the current stage of the user
 *
 * @param array $form       The form description
 * @param array $form_state The form state
 *
 * @return array $form is returned
 */
function getFormForCurrentStage($form, &$form_state) {
	switch ($form_state['stage']) {
		case 'overview':
			$form['#theme'] = 'iishconference_finalregistration_overview_page_form';

			return finalregistration_overview_form($form, $form_state);
			break;
		case 'main':
		default:
			$form['#theme'] = 'iishconference_finalregistration_main_page_form';

			return finalregistration_main_form($form, $form_state);
	}
}

/**
 * Implements hook_form_validate()
 */
function iishconference_finalregistration_main_form_validate($form, &$form_state) {
	// Currently, only the main page has a validation function
	switch ($form_state['stage']) {
		case 'main':
		default:
			finalregistration_main_validate($form, $form_state);
	}
}

/**
 * Implements hook_form_submit()
 */
function iishconference_finalregistration_main_form_submit($form, &$form_state) {
	// Handle form rebuilding if moving between stages and calling submit handlers for each page
	switch ($form_state['stage']) {
		case 'overview':
			if ($form_state['triggering_element']['#name'] == 'back') {
				$form_state['stage'] = 'main';
				$form_state['rebuild'] = true;
			}
			else {
				finalregistration_overview_submit($form, $form_state);
			}
			break;
		case 'main':
			if ($form_state['triggering_element']['#name'] == 'next') {
				finalregistration_main_submit($form, $form_state);
				$form_state['stage'] = 'overview';
				$form_state['rebuild'] = true;
			}
			break;
	}
}
