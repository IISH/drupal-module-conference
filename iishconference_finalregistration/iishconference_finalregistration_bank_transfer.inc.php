<?php
/**
 * @file
 * This page gives the user information about a payment by bank transfer
 */

/**
 * Gives the user information about their bank transfer order
 *
 * @return string Bank transfer information
 */
function iishconference_finalregistration_bank_transfer() {
	if (!LoggedInUserDetails::isLoggedIn()) {
		// redirect to login page
		header('Location: ' . url(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())));
		die(iish_t('Go to !login page.',
			array('!login' => l(iish_t('login'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())))));
	}

	if (LoggedInUserDetails::isAParticipant() && LoggedInUserDetails::getParticipant()->getPaymentId()) {
		$participant = LoggedInUserDetails::getParticipant();
		$orderDetails = new PayWayMessage(array('orderid' => $participant->getPaymentId()));
		$order = $orderDetails->send('orderDetails');

		if (!empty($order)) {
			if ($order->get('payed') == 1) {
				drupal_set_message(iish_t('You have already completed your final registration and payment.'), 'status');

				return '';
			}
			else if ($order->get('willpaybybank')) {
				$bankTransferInfo = SettingsApi::getSetting(SettingsApi::BANK_TRANSFER_INFO);
				$amount = ConferenceMisc::getReadableAmount($order->get('amount'), true);
				$finalDate =
					date('l j F Y', $participant->getBankTransferFinalDate($order->getDateTime('createdat')));
				$fullName = LoggedInUserDetails::getUser()->getFullName();

				$bankTransferInfo = str_replace('[PaymentNumber]',      $order->get('orderid'), $bankTransferInfo);
				$bankTransferInfo = str_replace('[PaymentAmount]',      $amount,                $bankTransferInfo);
				$bankTransferInfo = str_replace('[PaymentDescription]', $order->get('com'),     $bankTransferInfo);
				$bankTransferInfo = str_replace('[PaymentFinalDate]',   $finalDate,             $bankTransferInfo);
				$bankTransferInfo = str_replace('[NameParticipant]',    $fullName,              $bankTransferInfo);

				return ConferenceMisc::getCleanHTML($bankTransferInfo);
			}
			else {
				drupal_set_message(iish_t('You have chosen another payment method. !link to change your payment method.',
						array('!link' => l(iish_t('Click here'),
							SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'final-registration'))),
					'error');

				return '';
			}
		}
		else {
			drupal_set_message(iish_t('Currently it is not possible to obtain your payment information. ' .
					'Please try again later...'),
				'error');

			return '';
		}
	}
	else {
		drupal_set_message(iish_t('You have not finished the final registration. !link.',
			array('!link' => l(iish_t('Click here'),
				SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'final-registration'))), 'error');

		return '';
	}
}