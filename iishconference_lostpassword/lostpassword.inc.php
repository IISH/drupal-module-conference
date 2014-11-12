<?php

/**
 * Implements hook_form()
 */
function conference_lostpassword_form($form, &$form_state) {
	$ct = 0;
	$form['ct' . $ct++] = array(
		'#type'   => 'markup',
		'#markup' => '<div>' . iish_t('Please enter your e-mail address.') . '<br />' .
		             iish_t('We will send you a new link you can use to confirm your email.') . '<br />' .
		             iish_t('After confirmation you will receive a new password.') . '<br /><br /></div>',
	);

	$form['email'] = array(
		'#type'      => 'textfield',
		'#title'     => 'E-mail',
		'#size'      => 20,
		'#maxlength' => 100,
		'#required'  => true,
		'#prefix'    => '<div class="container-inline bottommargin">',
		'#suffix'    => '</div>',
	);

	$form['submit'] = array(
		'#type'  => 'submit',
		'#value' => 'Send'
	);

	return $form;
}

/**
 * Implements hook_form_validate
 */
function conference_lostpassword_form_validate($form, &$form_state) {
	$email = strtolower(trim($form_state['values']['email']));

	if (!valid_email_address($email)) {
		form_set_error('email', iish_t('The email address appears to be invalid.'));
	}
}

/**
 * Implements hook_form_submit
 */
function conference_lostpassword_form_submit($form, &$form_state) {
	$email = strtolower(trim($form_state['values']['email']));
	$lostPasswordApi = new LostPasswordApi();
	$status = $lostPasswordApi->lostPassword($email);

	if (is_int($status)) {
		switch ($status) {
			case LostPasswordApi::USER_STATUS_EXISTS:
				drupal_set_message(t("We have received your request for a new password.") . "<br>" .
				                   iish_t("We have sent you an e-mail you have to confirm before we will send you a new password."),
				                   'status');
				break;
			case LostPasswordApi::USER_STATUS_DISABLED:
				drupal_set_message(t("Account is disabled."), 'error');
				break;
			case LostPasswordApi::USER_STATUS_DELETED:
				drupal_set_message(t("Account is blocked."), 'error');
				break;
			default:
				drupal_set_message(t("We could not find this e-mail address."), 'error');
		}
	}
}
