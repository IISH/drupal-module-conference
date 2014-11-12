<?php

/**
 * Implements hook_form
 */
function conference_logout_form($form, &$form_state) {
	$ct = 0;
	$form['ct' . $ct++] = array(
		'#type'   => 'markup',
		'#markup' => '<div class="bottommargin">' . iish_t('Are you sure you want to log out?') . '</div>',
	);

	$form['submit'] = array(
		'#type'  => 'submit',
		'#value' => iish_t('Logout'),
	);

	return $form;
}

/**
 * Implements hook_form_submit
 */
function conference_logout_form_submit($form, &$form_state) {
	$_SESSION["conference"]["user_id"] = null;
	$_SESSION["conference"]["user_email"] = null;

	$_SESSION["conference"]["login_default_email_existingusers"] = null;
	$_SESSION["conference"]["login_default_email_newusers"] = null;

	$_SESSION["conference"]["hasFullRights"] = null;
	$_SESSION["conference"]["isNetworkChair"] = null;
	$_SESSION["conference"]["isChair"] = null;
	$_SESSION["conference"]["isOrganiser"] = null;
	$_SESSION["conference"]["isCrew"] = null;

	$_SESSION["conference"]["user"] = null;
	$_SESSION["conference"]["participant"] = null;

	unset($_SESSION['storage']);

	// redirect to the login page
	$form_state['redirect'] = array(
		'/' . SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
	);
}
