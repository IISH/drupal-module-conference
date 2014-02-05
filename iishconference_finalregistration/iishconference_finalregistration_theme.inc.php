<?php
/**
 * @file
 * Provides the themes to call for the pages of the final registration procedure
 */

/**
 * Implements template_preprocess_hook()
 */
function template_preprocess_iishconference_finalregistration_main_form(&$variables) {
	if (variable_get('payment_show_days_session_planned')) {
		$sessions = SessionParticipantApi::getAllSessions(LoggedInUserDetails::getUser()->getSessionParticipantInfo());
		$variables['session-days'] = SessionApi::getAllPlannedDaysForSessions($sessions);
	}
	$variables['email-addresses'] = ConferenceMisc::getInfoBlock(1);
}

/**
 * Implements template_preprocess_hook()
 */
function template_preprocess_iishconference_finalregistration_overview_form(&$variables) {
	$variables['bank_transfer_open'] =
		(ConferenceMisc::getTimeFromDateArray(variable_get('date_close_bank_transfer')) >= strtotime('today'));
	$variables['email-addresses'] = ConferenceMisc::getInfoBlock(1);
}