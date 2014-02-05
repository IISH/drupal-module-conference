<?php

/**
 * Implements hook_form()
 */
function conference_changeuser_form($form, &$form_state) {
	if (!LoggedInUserDetails::isLoggedIn()) {
		// if not logged in, redirect to login page
		Header('Location: /' . getSetting('pathForMenu') . 'login?backurl=' . urlencode($_SERVER["REQUEST_URI"]));
		die('Go to <a href="/' . getSetting('pathForMenu') . 'login?backurl=' . urlencode($_SERVER["REQUEST_URI"]) .
		    '">login</a> page.');
	}

	if (!LoggedInUserDetails::isCrew() && !LoggedInUserDetails::hasFullRights()) {
		drupal_set_message(t("Access denied.") . "<br />" . t("Current user ( @user ) is not a conference crew member.",
				array('@user' => LoggedInUserDetails::getUser())) . "<br />" . t("Please") . "<a href=\"/" .
		                   getSetting('pathForMenu') . 'login?backurl=' . urlencode($_SERVER["REQUEST_URI"]) . "\">" .
		                   t("log out and login") . "</a>" . t("as a crew member."), 'error');

		return '';
	}

	$ct = 0;

	// show change user page
	$form['ct' . $ct++] = array(
		'#type'   => 'markup',
		'#markup' => '<div><br />' . t('Please enter # or e-mail of user.') . '</div>',
	);

	$value = getShiftValue($_SERVER["REQUEST_URI"], 1 + getNumberOfDirectories(getSetting('pathForAdminMenu')));

	$form['user_id'] = array(
		'#type'          => 'textfield',
		'#title'         => 'User # or e-mail',
		'#size'          => 20,
		'#maxlength'     => 100,
		'#required'      => true,
		'#prefix'        => '<div class="container-inline">',
		'#suffix'        => '</div>',
		'#default_value' => $value
	);

	$form['ct' . $ct++] = array(
		'#type'   => 'markup',
		'#markup' => '<div><br /></div>',
	);

	$form['submit'] = array(
		'#type'  => 'submit',
		'#value' => 'Change'
	);

	return $form;
}

/**
 * Implements hook_form_submit()
 */
function conference_changeuser_form_submit($form, &$form_state) {
	$userInfoApi = new UserInfoApi();
	$userInfo = $userInfoApi->userInfo(trim($form_state['values']['user_id']));

	if ($userInfo) {
		if ($userInfo['isCrew'] || $userInfo['hasFullRights']) {
			form_set_error('user_id', t("You cannot change into a crew member."));
			$form_state['rebuild'] = true;
		}
		else {
			$userStatus = LoggedInUserDetails::setCurrentlyLoggedInWithResponse($userInfo);
			if ($userStatus == LoggedInUserDetails::USER_STATUS_EXISTS) {
				drupal_set_message(t("User changed."));

				// redirect to personal page
				$form_state['redirect'] = getSetting('pathForMenu') . 'personal-page';
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
						drupal_set_message(t("Incorrect email / id."), 'error');
				}

				$form_state['rebuild'] = true;
			}
		}
	}
	else {
		form_set_error('user_id', t("Cannot find user..."));
		$form['rebuild'] = true;
	}
}
