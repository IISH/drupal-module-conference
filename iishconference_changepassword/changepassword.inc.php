<?php

/**
 * Implements hook_form()
 */
function conference_changepassword_form($form, &$form_state) {
	if (!LoggedInUserDetails::isLoggedIn()) {
		// redirect to login page
		header('Location: ' .
			url(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())));
		die(iish_t('Go to !login page.',
			array('!login' => l(iish_t('login'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())))));
	}

	// show change password page
	$ct = 0;
	$form['ct' . $ct++] = array(
		'#type'   => 'markup',
		'#markup' => '<div><br />' . iish_t('Please enter twice your new password.') . '</div>',
	);

	$form['new_password'] = array(
		'#type'      => 'password',
		'#title'     => iish_t('New password'),
		'#size'      => 20,
		'#maxlength' => 50,
		'#required'  => true,
		'#prefix'    => '<div class="iishconference_container_inline">',
		'#suffix'    => '</div>',
	);

	$form['confirm_password'] = array(
		'#type'      => 'password',
		'#title'     => iish_t('Confirm new password'),
		'#size'      => 20,
		'#maxlength' => 50,
		'#required'  => true,
		'#prefix'    => '<div class="iishconference_container_inline">',
		'#suffix'    => '</div>',
	);

	$form['ct' . $ct++] = array(
		'#type'   => 'markup',
		'#markup' => '<div><br /></div>',
	);

	$form['submit'] = array(
		'#type'  => 'submit',
		'#value' => iish_t('Change'),
	);

	$form['ct' . $ct++] = array(
		'#type'   => 'markup',
		'#markup' => '<div><span class="eca_warning">' .
			iish_t('The new password must be at least 8 characters long and contain at least one lowercase character, ' .
				'one upper case character and one digit.') .
			'</span></div>',
	);

	return $form;
}

/**
 * Implements hook_form_validate()
 */
function conference_changepassword_form_validate($form, &$form_state) {
	$error_message = iish_t('The new password must be at least 8 characters long and contain at least ' .
		'one lowercase character, one upper case character and one digit.');

	// check length of new password
	if (strlen($form_state['values']['new_password']) < 8) {
		form_set_error('new_password', $error_message);
	}

	// check if the new passwords are equal
	if ($form_state['values']['new_password'] != $form_state['values']['confirm_password']) {
		form_set_error('confirm_password', iish_t('The confirm password is not equal to the new password.'));
	}

	// check if new passwords contain at least one lowercase, one uppercase, one digit
	if (!ChangePasswordApi::isPasswordValid($form_state['values']['new_password'])) {
		form_set_error('new_password', $error_message);
	}
}

/**
 * Implements hook_form_submit()
 */
function conference_changepassword_form_submit($form, &$form_state) {
	$changePasswordApi = new ChangePasswordApi();

	if ($changePasswordApi->changePassword(
		LoggedInUserDetails::getId(),
		$form_state['values']['new_password'],
		$form_state['values']['confirm_password'])
	) {
		drupal_set_message(iish_t('Password is successfully changed!'), 'status');
	}
	else {
		drupal_set_message(iish_t('We failed to change your password, please try again later.'), 'error');
	}
}
