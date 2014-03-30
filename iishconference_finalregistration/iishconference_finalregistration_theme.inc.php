<?php
/**
 * @file
 * Provides the themes to call for the pages of the final registration procedure
 */

/**
 * Implements template_preprocess_hook()
 */
function template_preprocess_iishconference_finalregistration_main_form(&$variables) {
	if (SettingsApi::getSetting(SettingsApi::PAYMENT_SHOW_DAYS_SESSION_PLANNED) == 1) {
		$sessions = SessionParticipantApi::getAllSessions(LoggedInUserDetails::getUser()->getSessionParticipantInfo());
		$variables['session-days'] = SessionApi::getAllPlannedDaysForSessions($sessions);
	}
}

/**
 * Implements template_preprocess_hook()
 */
function template_preprocess_iishconference_finalregistration_overview_form(&$variables) {
	$participant = LoggedInUserDetails::getParticipant();

	$variables['fee-amount'] = $participant->getFeeAmount();
	$variables['extras'] = $participant->getExtras();
	$variables['total-amount'] = $participant->getTotalAmount();

	if (SettingsApi::getSetting(SettingsApi::SHOW_ACCOMPANYING_PERSONS)) {
		$variables['accompanying-persons'] = $participant->getAccompanyingPersons();
		$variables['fee-amount-accompanying-person'] = $participant->getFeeAmount(null, FeeStateApi::getAccompanyingPersonFee());
	}

	$variables['bank_transfer_open'] =
		(strtotime(SettingsApi::getSetting(SettingsApi::BANK_TRANSFER_CLOSES_ON)) >= strtotime('today'));
}