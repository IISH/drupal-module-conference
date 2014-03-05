<?php
/**
 * @file
 * All functions for the second 'overview' stage of the main form of this module
 */

/**
 * The actual form builder for the final registration procedure overview
 *
 * @param array $form       The form description
 * @param array $form_state The form state
 *
 * @return array $form is returned
 */
function finalregistration_overview_form($form, &$form_state) {
	$form['back'] = array(
		'#type'  => 'submit',
		'#name'  => 'back',
		'#value' => t('Previous step'),
	);

	$form['confirm'] = array(
		'#type'  => 'submit',
		'#name'  => 'confirm',
		'#value' => t('Confirm'),
	);

	$form['payway'] = array(
		'#type'  => 'submit',
		'#name'  => 'payway',
		'#value' => t('Make payment with credit card or iDeal'),
	);

	$form['bank_transfer'] = array(
		'#type'  => 'submit',
		'#name'  => 'bank_transfer',
		'#value' => t('Make payment by bank transfer'),
	);

	return $form;
}

/**
 * The actual submit handler for the final registration procedure overview
 *
 * @param array $form       The form description
 * @param array $form_state The form state
 */
function finalregistration_overview_submit($form, $form_state) {
	$participant = LoggedInUserDetails::getParticipant();
	$user = LoggedInUserDetails::getUser();

	$feeAmount = $participant->getFeeAmount();
	$extras = $participant->getExtras();
	$totalAmount = $participant->getTotalAmout();

	$isPayWayTransaction = ($form_state['triggering_element']['#name'] !== 'bank_transfer');

	// Create the order, if successful, redirect user to payment page
	$createOrder = new PayWayMessage(array(
		'amount'        => intval($totalAmount * 100),
		'currency'      => 'EUR',
		'language'      => 'en_US',
		'cn'            => $user->getFullName(),
		'email'         => $user->getEmail(),
		'owneraddress'  => null,
		'ownerzip'      => null,
		'ownertown'     => $user->getCity(),
		'ownercty'      => ($user->getCountry() !== null) ? $user->getCountry()->getISOCode() : null,
		'ownertelno'    => $user->getPhone(),
		'com'           => ConferenceMisc::getPaymentDescription($feeAmount, $extras),
		'willpaybybank' => !$isPayWayTransaction,
		'userid'        => LoggedInUserDetails::getId(),
	));
	$order = $createOrder->send('createOrder');

	// If creating a new order is successful, redirect to PayWay or to bank transfer information?
	if (!empty($order) && $order->get('success')) {
		// Save order id
		$participant->setPaymentId($order->get('orderid'));

		if ($totalAmount == 0) {
			// No payment is necessary now, just confirm and send an email
			$sendEmailApi = new SendEmailApi();
			$sendEmailApi->sendPaymentAcceptedEmail($participant->getUserId(), $order->get('orderid'),
				ConferenceMisc::getReadableAmount($participant->getTotalAmout()),
				ConferenceMisc::getPaymentDescription($participant->getFeeAmount(), $participant->getExtras()));

			drupal_goto(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'final-registration/accept');
		}
		else if ($isPayWayTransaction) {
			$payment = new PayWayMessage(array('orderid' => $order->get('orderid')));
			$payment->send('payment');
		}
		else {
			$sendEmailApi = new SendEmailApi();
			$sendEmailApi->sendBankTransferEmail($participant->getUserId(), $order->get('orderid'),
				ConferenceMisc::getReadableAmount($participant->getTotalAmout()),
				ConferenceMisc::getPaymentDescription($participant->getFeeAmount(), $participant->getExtras()),
				$participant->getBankTransferFinalDate(time()));

			drupal_goto(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'final-registration/bank-transfer');
		}
	}
	else {
		drupal_set_message(t('Currently it is not possible to proceed to create a new order. Please try again later...'),
			'error');
	}
}
