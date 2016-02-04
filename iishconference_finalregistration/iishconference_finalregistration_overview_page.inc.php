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

	if (SettingsApi::getSetting(SettingsApi::BANK_TRANSFER_ALLOWED) == 1) {
		$form['bank_transfer'] = array(
			'#type'  => 'submit',
			'#name'  => 'bank_transfer',
			'#value' => iish_t('Make payment by bank transfer'),
		);
	}

    $form['on_site'] = array(
        '#type'  => 'submit',
        '#name'  => 'on_site',
        '#value' => iish_t('Pay on site'),
    );

	if (strlen(trim(SettingsApi::getSetting(SettingsApi::GENERAL_TERMS_CONDITIONS_LINK))) > 0) {
		$link = SettingsApi::getSetting(SettingsApi::GENERAL_TERMS_CONDITIONS_LINK);

		$form['terms_and_conditions'] = array(
			'#title'         =>
				iish_t('Check the box to accept the') . ' ' .
				'<a href="' . $link . '" target="_blank">' . iish_t('General terms and conditions') . '</a>.',
			'#type'          => 'checkbox',
			'#default_value' => false,
		);
	}

	return $form;
}

/**
 * The actual validate handler for the final registration procedure overview
 *
 * @param array $form       The form description
 * @param array $form_state The form state
 */
function finalregistration_overview_validate($form, &$form_state) {
	if ((strlen(trim(SettingsApi::getSetting(SettingsApi::GENERAL_TERMS_CONDITIONS_LINK))) > 0) &&
		($form_state['values']['terms_and_conditions'] !== 1)
	) {
		form_set_error('terms_and_conditions', iish_t('You have to accept the general terms and conditions.'));
	}
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

    $paymentMethod = PayWayMessage::ORDER_OGONE_PAYMENT;
    if ((SettingsApi::getSetting(SettingsApi::BANK_TRANSFER_ALLOWED) == 1) &&
        ($form_state['triggering_element']['#name'] === 'bank_transfer')) {
        $paymentMethod = PayWayMessage::ORDER_BANK_PAYMENT;
    }
    if ($form_state['triggering_element']['#name'] === 'on_site') {
        $paymentMethod = PayWayMessage::ORDER_CASH_PAYMENT;
    }

    $totalAmount = ($paymentMethod === PayWayMessage::ORDER_CASH_PAYMENT)
        ? $participant->getTotalAmountPaymentOnSite()
        : $participant->getTotalAmount();

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
		'paymentmethod' => $paymentMethod,
		'userid'        => LoggedInUserDetails::getId(),
	));
	$order = $createOrder->send('createOrder');

	// If creating a new order is successful, redirect to PayWay or to bank transfer information or just succeed?
	if (!empty($order) && $order->get('success')) {
		$orderId = $order->get('orderid');

		// Save order id
		$participant->setPaymentId($orderId);
		$participant->save();

		// Also make sure the CMS has a copy of the order
		$refreshOrderApi = new RefreshOrderApi();
		$refreshOrderApi->refreshOrder($orderId);

		// If no payment is necessary now, just confirm and send an email
		if (($totalAmount == 0) || ($paymentMethod === PayWayMessage::ORDER_CASH_PAYMENT)) {
            if ($totalAmount == 0) {
                $sendEmailApi = new SendEmailApi();
                $sendEmailApi->sendPaymentAcceptedEmail($participant->getUserId(), $orderId);
            }

			if ($paymentMethod === PayWayMessage::ORDER_CASH_PAYMENT) {
				$sendEmailApi = new SendEmailApi();
				$sendEmailApi->sendPaymentOnSiteEmail($participant->getUserId(), $orderId);
			}

			drupal_goto(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'final-registration/accept');
		}
		else if ($paymentMethod === PayWayMessage::ORDER_OGONE_PAYMENT) {
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
