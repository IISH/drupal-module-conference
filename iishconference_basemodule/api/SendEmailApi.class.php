<?php

/**
 * API that allows emails to be send
 */
class SendEmailApi {
	private static $apiName = 'sendEmail';
	private $client;

	public function __construct() {
		$this->client = new ConferenceApiClient();
	}

	/**
	 * Sends an email that tells the user how to make a bank transfer
	 *
	 * @param int|UserApi $userId                The user (id) to whom the email is addressed
	 * @param string      $paymentNumber         The payment number
	 * @param string      $paymentAmount         The amount payed
	 * @param string      $paymentDescription    The payment description
	 * @param int         $bankTransferFinalDate The final date a bank transfer should have been made
	 *
	 * @return bool Returns whether the action was successful or not
	 */
	public function sendBankTransferEmail($userId, $paymentNumber, $paymentAmount, $paymentDescription,
	                                      $bankTransferFinalDate) {
		return $this->sendEmail(SettingsApi::BANK_TRANSFER_EMAIL_TEMPLATE_ID, $userId, array(
			'paymentNumber'         => $paymentNumber,
			'paymentAmount'         => $paymentAmount,
			'paymentDescription'    => $paymentDescription,
			'bankTransferFinalDate' => date('Y/m/d', $bankTransferFinalDate),
		));
	}

	/**
	 * Sends an email that informs the user his payment has been accepted
	 *
	 * @param int|UserApi $userId             The user (id) to whom the email is addressed
	 * @param string      $paymentNumber      The payment number
	 * @param string      $paymentAmount      The amount payed
	 * @param string      $paymentDescription The payment description
	 * @param string      $orderDescription   The order description
	 *
	 * @return bool Returns whether the action was successful or not
	 */
	public function sendPaymentAcceptedEmail($userId, $paymentNumber, $paymentAmount, $paymentDescription,
	                                         $orderDescription) {
		return $this->sendEmail(SettingsApi::PAYMENT_ACCEPTED_EMAIL_TEMPLATE_ID, $userId, array(
			'paymentNumber'      => $paymentNumber,
			'paymentAmount'      => $paymentAmount,
			'paymentDescription' => $paymentDescription,
			'orderDescription'   => $orderDescription,
		));
	}

	/**
	 * Sends an emails that details the pre registration he/she just finished
	 *
	 * @param int|UserApi $userId The user (id) to whom the email is addressed
	 *
	 * @return bool Returns whether the action was successful or not
	 */
	public function sendPreRegistrationFinishedEmail($userId) {
		return $this->sendEmail(SettingsApi::PRE_REGISTRATION_EMAIL_TEMPLATE_ID, $userId, array());
	}

	/**
	 * Allows emails to be send
	 *
	 * @param string      $settingPropertyName The name of the setting property that hols the specific email template to use
	 * @param int|UserApi $userId              The user (id) to whom the email is addressed
	 * @param array       $props               The properties to include in the email
	 *
	 * @return bool Returns whether the action was successful or not
	 */
	private function sendEmail($settingPropertyName, $userId, array $props) {
		if ($userId instanceof UserApi) {
			$userId = $userId->getId();
		}

		$response = $this->client->get(self::$apiName, array_merge($props, array(
			'settingPropertyName' => $settingPropertyName,
			'userId'              => $userId,
		)));

		return ($response !== null) ? $response['success'] : false;
	}
} 