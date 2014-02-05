<?php

/**
 * Implements hook_form
 */
function conference_login_form($form, &$form_state) {
$ct = 0;

	$form['ct' . $ct++] = array(
		'#type'   => 'markup',
		'#markup' => '<div class="preregister_fullwidth">',
	);

	$form['ct' . $ct++] = array(
		'#type'   => 'markup',
		'#markup' => '<div class="bottommargin">' . t('Please enter your e-mail address and password.') . '</div>',
	);

	$default_email_value = '';
	if (strtolower($_SERVER['REQUEST_METHOD']) === "get") {
		if (isset($_SESSION["conference"]["user_email"])) {
			$default_email_value = trim($_SESSION["conference"]["user_email"]);
		}
	}

	$form['email'] = array(
		'#type'          => 'textfield',
		'#title'         => 'E-mail',
		'#size'          => 20,
		'#maxlength'     => 255,
		'#prefix'        => '<div class="container-inline bottommargin">',
		'#suffix'        => '</div>',
		'#default_value' => $default_email_value,
		'#required'      => true,
	);

	$form['password'] = array(
		'#type'      => 'password',
		'#title'     => 'Password',
		'#size'      => 20,
		'#maxlength' => 50,
		'#prefix'    => '<div class="container-inline bottommargin">',
		'#suffix'    => '</div>',
		'#required'  => true,
	);

	$form['submit_button_next'] = array(
		'#type'  => 'submit',
		'#value' => 'Log in'
	);

	// lost password url
	$form['ct' . $ct++] = array(
		'#type'   => 'markup',
		'#markup' => '<div class="largertopmargin">' . l(t('Lost password'), getSetting('pathForMenu') .
				'lost-password') . '</div>',
	);

	// pre-registration url
	$form['ct' . $ct++] = array(
		'#type'   => 'markup',
		'#markup' => '<div class="largertopmargin">' . t('If you don\'t have an account please go to !link.',
				array('!link' => l(t('Pre-registration form'), getSetting('pathForMenu') . 'pre-registration'))) .
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
		form_set_error('email', t('The e-mail address appears to be invalid.'));
	}
}

/**
 * TODOEXPLAIN
 */
function conference_login_form_submit($form, &$form_state) {
	$loginApi = new LoginApi();
	$user_status = $loginApi->login($form_state['values']['email'], $form_state['values']['password']);

	if ($user_status == LoggedInUserDetails::USER_STATUS_EXISTS) {
		if (!isset($_GET["backurl"])) {
			$_GET["backurl"] = '';
		}

		if (!isset($_GET["p"])) {
			$_GET["p"] = '';
		}

		$nextpage = '';

		switch (strtolower(trim($_GET["p"]))) {
			case 'change-password':
				$nextpage = '/' . getSetting('pathForMenu') . getSetting('urlchangepassword');
				break;
			default:
				$nextpage = trim($_GET["backurl"]);
				if ($nextpage != '') {
					$nextpage = urldecode($nextpage);
					$nextpage = protectBackUrl($nextpage);
				}
		}

		if ($nextpage == '') {
			$nextpage = '/' . getSetting('pathForMenu') . getSetting('urlpersonalpage');
		}

		// redirect to personal page
		$form_state['redirect'] = array(
			$nextpage,
		);

	}
	else {
		$form_state['rebuild'] = true;

		switch ($user_status) {
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
