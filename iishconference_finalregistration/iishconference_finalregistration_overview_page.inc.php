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
		'#value' => iish_t('Previous step'),
	);

	$form['confirm'] = array(
		'#type'  => 'submit',
		'#name'  => 'confirm',
		'#value' => iish_t('Confirm'),
	);

	$form['payway'] = array(
		'#type'  => 'submit',
		'#name'  => 'payway',
		'#value' => iish_t('Make online payment'),
	);

	$form['bank_transfer'] = array(
		'#type'  => 'submit',
		'#name'  => 'bank_transfer',
		'#value' => iish_t('Make payment by bank transfer'),
	);

	return $form;
}

/**
 * The actual submit handler for the final registration procedure overview
 *
 * @param array $form       The form description
 * @param array $form_state The form state
 */
function finalregistration_overview_submit($form, &$form_state) {
	$participant = LoggedInUserDetails::getParticipant();
	$user = LoggedInUserDetails::getUser();

	$totalAmount = $participant->getTotalAmount();
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
		'com'           => CachedConferenceApi::getEventDate() . ' ' . iish_t('payment'),
		'paymentmethod' => ($isPayWayTransaction) ? PayWayMessage::ORDER_OGONE_PAYMENT : PayWayMessage::ORDER_BANK_PAYMENT,
		'userid'        => LoggedInUserDetails::getId(),
	));
	$order = $createOrder->send('createOrder');

	// If creating a new order is successful, redirect to PayWay or to bank transfer information?
	if (!empty($order) && $order->get('success')) {
		$orderId = $order->get('orderid');

		// Save order id
		$participant->setPaymentId($orderId);
		$participant->save();

		// Also make sure the CMS has a copy of the order
		$refreshOrderApi = new RefreshOrderApi();
		$refreshOrderApi->refreshOrder($orderId);

		// If no payment is necessary now, just confirm and send an email
		if ($totalAmount == 0) {
			$sendEmailApi = new SendEmailApi();
			$sendEmailApi->sendPaymentAcceptedEmail($participant->getUserId(), $orderId);

			drupal_goto(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'final-registration/accept');
		}
		else if ($isPayWayTransaction) {
			$payment = new PayWayMessage(array('orderid' => $orderId));
			$payment->send('payment');
		}
		else {
			$sendEmailApi = new SendEmailApi();
			$sendEmailApi->sendBankTransferEmail($participant->getUserId(), $orderId);

			drupal_goto(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'final-registration/bank-transfer');
		}
	}
	else {
		drupal_set_message(iish_t('Currently it is not possible to proceed to create a new order. Please try again later...'),
			'error');
	}
}
