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
		$participant = CRUDApiMisc::getFirstWherePropertyEquals(new ParticipantDateApi(), 'user_id',
			$paymentResponse->get('userid'));
		$orderId = $paymentResponse->get('orderid');
		$participant->setPaymentId($orderId);

		// Get the details of the order in question
		$orderDetails = new PayWayMessage(array('orderid' => $participant->getPaymentId()));
		$order = $orderDetails->send('orderDetails');

		// Send the participant an email that his/her payment has been accepted
		if (!empty($order)) {
			$creationDate = $order->getDateTime('createdat');

			// Obtain the order description
			$orderDescription = array();
			$orderDescription[] = '- ' . $participant->getFeeAmount($creationDate)->getDescriptionWithoutDays();

			foreach ($participant->getExtras() as $extra) {
				$orderDescription[] = '- ' . $extra;
			}

			if (SettingsApi::getSetting(SettingsApi::SHOW_ACCOMPANYING_PERSONS)) {
				$accompanyingPersons = $participant->getAccompanyingPersons();
				$feeAmountAccompanyingPersons = $participant->getFeeAmount($creationDate,
					FeeStateApi::getAccompanyingPersonFee())->getDescriptionWithoutDays();

				foreach ($accompanyingPersons as $accompanyingPerson) {
					$orderDescription[] = '- ' . $accompanyingPerson . ' ' . $feeAmountAccompanyingPersons;
				}
			}

			$sendEmailApi = new SendEmailApi();
			$sendEmailApi->sendPaymentAcceptedEmail(
				$participant->getUserId(),
				$orderId,
				ConferenceMisc::getReadableAmount($order->get('amount'), true),
				$order->get('com'),
				implode("\n", $orderDescription)
			);

			// Make sure that cancelled participants are confirmed again
			if ($participant->getStateId() == ParticipantStateApi::REMOVED_CANCELLED) {
				$participant->setState(ParticipantStateApi::PARTICIPANT);
			}

			$participant->save();
		}
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
