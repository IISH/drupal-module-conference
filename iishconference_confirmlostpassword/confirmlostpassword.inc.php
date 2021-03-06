<?php

/**
 * Implements hook_form()
 */
function conference_confirmlostpassword_form($form, &$form_state) {
	$ct = 0;
	$params = drupal_get_query_parameters();

	$default_id = isset($params['id']) ? EasyProtection::easyIntegerProtection($params['id']) : null;
	$default_code = isset($params['code']) ? EasyProtection::easyStringProtection($params['code']) : null;

	$codeCheckOkay = false;

	// auto submit
	if (($default_id !== null) && ($default_id !== '') && ($default_code !== null) && ($default_code !== '')) {
		if (strtolower($_SERVER['REQUEST_METHOD']) === "get") {
			$codeCheckOkay = conference_confirmlostpassword_set_message($default_id, $default_code);
		}
	}

	if (!$codeCheckOkay) {
		$form['ct' . $ct++] = array(
			'#type'   => 'markup',
			'#markup' => '<div class="bottommargin">' .
			             iish_t('Please enter the ID and CODE we have sent you via e-mail and click on Confirm.') .
			             '<br />' . iish_t('After the CODE is confirmed we will send you a new password.') .
			             '</div>',
		);

		$form['id'] = array(
			'#type'          => 'textfield',
			'#title'         => iish_t('ID'),
			'#size'          => 20,
			'#maxlength'     => 50,
			'#required'      => true,
			'#prefix'        => '<div class="container-inline bottommargin">',
			'#suffix'        => '</div>',
			'#default_value' => $default_id,
		);

		$form['code'] = array(
			'#type'          => 'textfield',
			'#title'         => iish_t('CODE'),
			'#size'          => 20,
			'#maxlength'     => 50,
			'#required'      => true,
			'#prefix'        => '<div class="container-inline bottommargin">',
			'#suffix'        => '</div>',
			'#default_value' => $default_code,
		);

		$form['submit'] = array(
			'#type'  => 'submit',
			'#value' => 'Confirm'
		);

	}

	return $form;
}

/**
 * Implements hook_form_validate
 */
function conference_confirmlostpassword_form_validate($form, &$form_state) {
	// regexp only integers
	if (EasyProtection::easyIntegerProtection($form_state['values']['id']) === null) {
		form_set_error('id', iish_t('The ID appears to be invalid.'));
	}
	// regexp only digits and characters
	if (EasyProtection::easyStringProtection($form_state['values']['code']) === '') {
		form_set_error('code', iish_t('The CODE appears to be invalid.'));
	}
}

/**
 * Implements hook_form_submit
 */
function conference_confirmlostpassword_form_submit($form, &$form_state) {
	$form_state['rebuild'] = true;

	$id = EasyProtection::easyIntegerProtection($form_state['values']['id']);
	$code = EasyProtection::easyStringProtection($form_state['values']['code']);

	conference_confirmlostpassword_set_message($id, $code);
}

/**
 * Sets the message for lost password code checking
 *
 * @param int    $id   The id of the user
 * @param string $code The code to check for this id
 *
 * @return bool Whether to return
 */
function conference_confirmlostpassword_set_message($id, $code) {
	$confirmLostPasswordApi = new ConfirmLostPasswordApi();
	$status = $confirmLostPasswordApi->confirmLostPassword($id, $code);
	$ret = false;

	switch ($status) {
		case ConfirmLostPasswordApi::ACCEPT:
			drupal_set_message(iish_t('We have sent you an e-mail with your new password.'), 'status');
			$ret = true;
			break;
		case ConfirmLostPasswordApi::PASSWORD_ALREADY_SENT:
			drupal_set_message(iish_t('We already sent you an email with your new password. Please check your email!'),
				'warning');
			$ret = true;
			break;
		case ConfirmLostPasswordApi::CODE_EXPIRED:
			drupal_set_message(iish_t('The CODE has been expired. Please request a new CODE.'), 'error');
			break;
		case ConfirmLostPasswordApi::ERROR:
			drupal_set_message(iish_t('We failed to send you an email with your new password. ' .
				'Please try again later. We are sorry for the inconvenience.'), 'error');
			break;
		default:
			drupal_set_message(iish_t('ID / CODE combination not found.'), 'error');
	}

	return $ret;
}

