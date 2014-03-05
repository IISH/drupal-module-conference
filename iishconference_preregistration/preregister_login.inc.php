<?php

/**
 * Implements hook_form()
 */
function preregister_login_form($form, &$form_state) {
	$form['existing_users'] = array(
		'#type'  => 'fieldset',
		'#title' => t('Existing users'),
	);

	$form['existing_users']['help_text'] = array(
		'#type'   => 'markup',
		'#markup' => '<div class="bottommargin">' . t('Please enter your e-mail address and password.') . '</div>',
	);

	$form['existing_users']['email'] = array(
		'#type'      => 'textfield',
		'#title'     => 'E-mail',
		'#size'      => 20,
		'#maxlength' => 100,
	);

	$form['existing_users']['password'] = array(
		'#type'      => 'password',
		'#title'     => 'Password',
		'#size'      => 20,
		'#maxlength' => 50,
	);

	$form['existing_users']['submit_login'] = array(
		'#type'  => 'submit',
		'#value' => t('Log in'),
	);

	// lost password url
	$form['existing_users']['lost_password'] = array(
		'#type'   => 'markup',
		'#markup' =>
			'<div class="largertopmargin">' . l(t('Lost password'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'lost-password') .
			'</div>',
	);

	$form['new_users'] = array(
		'#type'  => 'fieldset',
		'#title' => t('New users'),
	);

	$form['new_users']['help_text'] = array(
		'#type'   => 'markup',
		'#markup' => '<div class="bottommargin">' . t('Please enter your e-mail address.') . '</div>',
	);

	$form['new_users']['email_newusers'] = array(
		'#type'      => 'textfield',
		'#title'     => 'E-mail',
		'#size'      => 20,
		'#maxlength' => 100,
	);

	$form['new_users']['submit_new'] = array(
		'#type'  => 'submit',
		'#value' => t('New user'),
	);

	$form['info_block'] = array(
		'#type'   => 'markup',
		'#markup' => ConferenceMisc::getInfoBlock(),
	);

	$form['comments_block'] = array(
		'#type'   => 'markup',
		'#markup' => '<div class="eca_warning">
			<br />
			<strong>' . t('Comments') . '</strong><br>
			<ol>
				<li>' .
			t('Please disable (or minimize the size of) the cache in your browser (Internet Explorer, Firefox, Chrome)') . '</li>
				<li>' .
			t('Use the back/next buttons in the form, do NOT use the browser back button') . '</li>
				<li>' .
			t('Prepare your abstract beforehand. Do NOT type your abstract in the form field, but COPY it into the form field') . '</li>
				<li>' .
			t('Please mail all errors to: !email',
				array('!email' => ConferenceMisc::encryptEmailAddress(SettingsApi::getSetting(SettingsApi::JIRA_EMAIL)))) . '</li>
			</ol>
		</div>',
	);

	return $form;
}

/**
 * Implements hook_form_validate()
 */
function preregister_login_form_validate($form, &$form_state) {
	// EXISTING USERS
	if ($form_state['clicked_button']['#value'] == $form_state['values']['submit_login']) {
		$email = trim($form_state['values']['email']);
		if (strlen($email) === 0) {
			form_set_error('email', 'E-mail field is required.');
		}
		else if (!valid_email_address($email)) {
			form_set_error('email', 'The e-mail address appears to be invalid.');
		}

		$password = trim($form_state['values']['password']);
		if (strlen($password) === 0) {
			form_set_error('password', 'Password field is required.');
		}
	}
	// NEW USERS
	else if ($form_state['clicked_button']['#value'] == $form_state['values']['submit_new']) {
		$email = trim($form_state['values']['email_newusers']);
		if (strlen($email) === 0) {
			form_set_error('email', 'E-mail field is required.');
		}
		else if (!valid_email_address($email)) {
			form_set_error('email', 'The e-mail address appears to be invalid.');
		}
	}
	else {
		die('ERROR 658412: Unknown button in login form');
	}
}

/**
 * Implements hook_form_submit()
 */
function preregister_login_form_submit($form, &$form_state) {
	$form_state['pre-registration']['is_existing_user'] = false;

	// EXISTING USERS
	if ($form_state['clicked_button']['#value'] == $form_state['values']['submit_button_next']) {
		$loginApi = new LoginApi();
		$userStatus = $loginApi->login($form_state['values']['email'], $form_state['values']['password']);

		if ($userStatus == LoggedInUserDetails::USER_STATUS_EXISTS) {
			$form_state['pre-registration']['is_existing_user'] = true;
			$form_state['pre-registration']['user_email'] = LoggedInUserDetails::getUser()->getEmail();
		}
		else {
			switch ($userStatus) {
				case LoggedInUserDetails::USER_STATUS_DISABLED:
					drupal_set_message(t("Account is disabled."), 'error');
					break;
				case LoggedInUserDetails::USER_STATUS_DELETED:
					drupal_set_message(t("Account is deleted"), 'error');
					break;
				default:
					drupal_set_message(t("Incorrect email / password combination."), 'error');
			}
		}
	}
	// NEW USERS
	else if ($form_state['clicked_button']['#value'] == $form_state['values']['submit_button_new']) {
		$email = trim($form_state['values']['email_newusers']);

		$user = CRUDApiMisc::getFirstWherePropertyEquals(new UserApi(), 'email', $email);
		if ($user !== null) {
			$ecaSettings = CachedConferenceApi::getSettings();
			$existingUserMessage = $ecaSettings[SettingsApi::EXISTING_USER_MESSAGE];

			drupal_set_message(t($existingUserMessage, array('@email' => $email)), 'error');
		}
		else {
			$form_state['pre-registration']['user_email'] = $email;
		}
	}
}
