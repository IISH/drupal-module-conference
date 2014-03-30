<?php

/**
 * Implements hook_form()
 */
function preregister_password_form($form, &$form_state) {
	$state = new PreRegistrationState($form_state);

	$form['login_with_password'] = array(
		'#type' => 'fieldset',
	);

	$form['login_with_password']['help_text'] = array(
		'#type'   => 'markup',
		'#markup' => '<div class="bottommargin">' . t('Please enter your password.') . '</div>',
	);

	$form['login_with_password']['email'] = array(
		'#type'          => 'textfield',
		'#title'         => 'E-mail',
		'#size'          => 20,
		'#maxlength'     => 100,
		'#default_value' => $state->getEmail(),
		'#attributes'    => array('readonly' => 'readonly', 'class' => array('readonly-text')),
	);

	$form['login_with_password']['password'] = array(
		'#type'      => 'password',
		'#title'     => 'Password',
		'#required'  => true,
		'#size'      => 20,
		'#maxlength' => 50,
	);

	$form['login_with_password']['login'] = array(
		'#type'  => 'submit',
		'#name'  => 'login',
		'#value' => t('Next'),
	);

	// Lost password URL
	$form['login_with_password']['lost_password'] = array(
		'#type'   => 'markup',
		'#markup' =>
			'<div class="largertopmargin">' .
			l(t('Lost password'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'lost-password') .
			'</div>',
	);

	return $form;
}

/**
 * Implements hook_form_submit()
 */
function preregister_password_form_submit($form, &$form_state) {
	$state = new PreRegistrationState($form_state);

	$loginApi = new LoginApi();
	$userStatus = $loginApi->login($state->getEmail(), $form_state['values']['password']);

	if ($userStatus == LoggedInUserDetails::USER_STATUS_EXISTS) {
		return 'preregister_personalinfo_form';
	}
	else {
		switch ($userStatus) {
			case LoggedInUserDetails::USER_STATUS_DISABLED:
			case LoggedInUserDetails::USER_STATUS_DELETED:
				drupal_set_message(t('The account with the given email address is disabled.'), 'error');
				break;
			case LoggedInUserDetails::USER_STATUS_DOES_NOT_EXISTS:
				drupal_set_message(t('Incorrect email / password combination.'), 'error');
		}

		return 'preregister_password_form';
	}
}
