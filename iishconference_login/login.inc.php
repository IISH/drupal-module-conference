<?php

/**
 * Implements hook_form
 */
function conference_login_form($form, &$form_state) {
	$ct = 0;

	$form['ct' . $ct++] = array(
		'#type'   => 'markup',
		'#markup' => '<div class="iishconference_container">',
	);

	$form['ct' . $ct++] = array(
		'#type'   => 'markup',
		'#markup' => '<div class="bottommargin">' . iish_t('Please enter your e-mail address and password.') . '</div>',
	);

	$default_email_value = '';
	if (strtolower($_SERVER['REQUEST_METHOD']) === "get") {
		if (isset($_SESSION["conference"]["user_email"])) {
			$default_email_value = trim($_SESSION["conference"]["user_email"]);
		}
	}

	$form['email'] = array(
		'#type'          => 'textfield',
		'#title'         => iish_t('E-mail'),
		'#size'          => 20,
		'#maxlength'     => 255,
		'#prefix'        => '<div class="container-inline bottommargin">',
		'#suffix'        => '</div>',
		'#default_value' => $default_email_value,
		'#required'      => true,
	);

	$form['password'] = array(
		'#type'      => 'password',
		'#title'     => iish_t('Password'),
		'#size'      => 20,
		'#maxlength' => 50,
		'#prefix'    => '<div class="container-inline bottommargin">',
		'#suffix'    => '</div>',
		'#required'  => true,
	);

	$form['submit_button_next'] = array(
		'#type'  => 'submit',
		'#value' => iish_t('Log in')
	);

	// lost password url
	$form['ct' . $ct++] = array(
		'#type'   => 'markup',
		'#markup' => '<div class="largertopmargin">' . l(iish_t('Lost password'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) .
				'lost-password') . '</div>',
	);

	// pre-registration url
	$form['ct' . $ct++] = array(
		'#type'   => 'markup',
		'#markup' => '<div class="largertopmargin">' . iish_t('If you don\'t have an account please go to !link.',
				array('!link' => l(iish_t('Pre-registration form'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'pre-registration'))) .
			'</div>',
	);

	$form['ct' . $ct++] = array(
		'#type'   => 'markup',
		'#markup' => '</div>',
	);

	$form['ct' . $ct++] = array(
		'#type'   => 'markup',
		'#markup' => ConferenceMisc::getInfoBlock(),
	);

	return $form;
}

/**
 * TODOEXPLAIN
 */
function conference_login_form_validate($form, &$form_state) {
	if (!valid_email_address(trim($form_state['values']['email']))) {
		form_set_error('email', iish_t('The e-mail address appears to be invalid.'));
	}
}

/**
 * TODOEXPLAIN
 */
function conference_login_form_submit($form, &$form_state) {
	$loginApi = new LoginApi();
	$user_status = $loginApi->login($form_state['values']['email'], $form_state['values']['password']);

	if ($user_status == LoggedInUserDetails::USER_STATUS_EXISTS) {
		drupal_goto(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'personal-page');
	}
	else {
		$form_state['rebuild'] = true;

		switch ($user_status) {
			case LoggedInUserDetails::USER_STATUS_DISABLED:
            case LoggedInUserDetails::USER_STATUS_EMAIL_DISCONTINUED:
				drupal_set_message(iish_t("Account is disabled."), 'error');
				break;
			case LoggedInUserDetails::USER_STATUS_DELETED:
				drupal_set_message(iish_t("Account is deleted"), 'error');
				break;
            case LoggedInUserDetails::USER_STATUS_PARTICIPANT_CANCELLED:
                drupal_set_message(iish_t("Account has been cancelled."), 'error');
                break;
            case LoggedInUserDetails::USER_STATUS_PARTICIPANT_DOUBLE_ENTRY:
                drupal_set_message(iish_t("Your account is disabled. ' . 
                '(Probably due to a double registration, please login with your other registration) ' . 
                'Please contact !email", array('!email' =>
                    ConferenceMisc::encryptEmailAddress(
                        SettingsApi::getSetting(SettingsApi::DEFAULT_ORGANISATION_EMAIL)))), 'error');
                break;
			default:
				drupal_set_message(iish_t("Incorrect email / password combination."), 'error');
		}
	}
}
