<?php
/**
 * @file
 * Provides the themes to call for the pages of the final registration procedure
 */

/**
 * Implements template_preprocess_hook()
 */
function template_preprocess_iishconference_finalregistration_main_page_form(&$variables) {
	if (SettingsApi::getSetting(SettingsApi::SHOW_DAYS_SESSION_PLANNED) == 1) {
		$sessions = CombinedSessionParticipantApi::getAllSessions(LoggedInUserDetails::getUser()->getCombinedSessionParticipantInfo());
		$variables['session-days'] = SessionApi::getAllPlannedDaysForSessions($sessions);
	}

	if (SettingsApi::getSetting(SettingsApi::SHOW_DAYS) != 1) {
		$days = CachedConferenceApi::getDays();
		$feeAmounts = LoggedInUserDetails::getParticipant()->getFeeAmounts(count($days));
		$feeAmount = isset($feeAmounts[0]) ? $feeAmounts[0] : null;
		$variables['fee-amount-description'] = $feeAmount->getDescriptionWithoutDays();
	}
}

/**
 * Implements template_preprocess_hook()
 */
function template_preprocess_iishconference_finalregistration_overview_page_form(&$variables) {
	$participant = LoggedInUserDetails::getParticipant();

	$variables['fee-amount-description'] = $participant->getFeeAmount()->getDescriptionWithoutDays();
	$variables['extras'] = $participant->getExtrasOfFinalRegistration();
	$variables['total-amount'] = $participant->getTotalAmount();
	$variables['total-amount-pay-on-site'] = $participant->getTotalAmountPaymentOnSite();

	if (SettingsApi::getSetting(SettingsApi::SHOW_ACCOMPANYING_PERSONS)) {
		$feeAmountAccompanyingPerson = $participant->getFeeAmount(null, FeeStateApi::getAccompanyingPersonFee());
		$variables['accompanying-persons'] = $participant->getAccompanyingPersons();
		$variables['fee-amount-accompanying-person-description'] = $feeAmountAccompanyingPerson->getDescriptionWithoutDays();
	}

	$user = LoggedInUserDetails::getUser();

	$variables['days'] = $user->getDaysPresent();
	$variables['invitation-letter'] = $participant->getInvitationLetter();
	$variables['address'] = $user->getAddress();

	$bankTransferLastDate = strtotime(SettingsApi::getSetting(SettingsApi::BANK_TRANSFER_LASTDATE));
	$variables['bank_transfer_open'] = ConferenceMisc::isOpenForLastDate($bankTransferLastDate);

    $paymentOnSiteStartDate = strtotime(SettingsApi::getSetting(SettingsApi::PAYMENT_ON_SITE_STARTDATE));
    $variables['payment_on_site_open'] = ConferenceMisc::isOpenForStartDate($paymentOnSiteStartDate);
}