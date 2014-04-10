<?php
/**
 * @file
 * Handles the callback states from PayWay after an payment attempt has been made
 */

/**
 * Called when a payment was accepted
 *
 * @return string The message for the user
 */
function iishconference_finalregistration_accept() {
	$paymentResponse = new PayWayMessage(drupal_get_query_parameters());

	// 'POST' indicates that it is a one time response after the payment has been made, in our case, to send an email
	if ($paymentResponse->isSignValid() && $paymentResponse->get('POST')) {
		$userId = $paymentResponse->get('userid');
		$orderId = $paymentResponse->get('orderid');

		$participant = CRUDApiMisc::getFirstWherePropertyEquals(new ParticipantDateApi(), 'user_id', $userId);
		$participant->setPaymentId($orderId);

		// Make sure that cancelled participants are confirmed again
		if ($participant->getStateId() == ParticipantStateApi::REMOVED_CANCELLED) {
			$participant->setState(ParticipantStateApi::PARTICIPANT);
		}

		$participant->save();

		// Also make sure the CMS side is aware of the update of this order
		$refreshOrderApi = new RefreshOrderApi();
		$refreshOrderApi->refreshOrder($orderId);

		// Send an email to inform the user his payment has been accepted
		$sendEmailApi = new SendEmailApi();
		$sendEmailApi->sendPaymentAcceptedEmail($userId, $orderId);
	}

	return t('Thank you. The procedure has been completed successfully!') . '<br />' .
		t('Within a few minutes you will receive an email from us confirming your \'final registration and payment\' ' .
		'and you will receive a second email from the payment provider confirming your payment.');
}

/**
 * Called when a payment was declined
 *
 * @return string The message for the user
 */
function iishconference_finalregistration_decline() {
	return t('Unfortunately, your payment has been declined. Please try to finish your final registration ' .
		'at a later moment or try a different payment method.');
}

/**
 * Called when a payment result is uncertain
 *
 * @return string The message for the user
 */
function iishconference_finalregistration_exception() {
	return t('Unfortunately, your payment result is uncertain at the moment.') . '<br />' .
	t('Please contact !email to request information on your payment transaction.',
		array('!email' => ConferenceMisc::encryptEmailAddress(
				SettingsApi::getSetting(SettingsApi::DEFAULT_ORGANISATION_EMAIL))));
}
