<?php

/**
 * Implements hook_form()
 */
function conference_changeuser_form($form, &$form_state, $value) {
	if (!LoggedInUserDetails::isLoggedIn()) {
		// redirect to login page
		header('Location: ' . url(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login', array('query' => drupal_get_destination())));
		die(iish_t('Go to !login page.', array('!login' => l(iish_t('login'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
			array('query' => drupal_get_destination())))));
	}

	if (!LoggedInUserDetails::isCrew() && !LoggedInUserDetails::hasFullRights()) {
		drupal_set_message(iish_t('Access denied.') . '<br />' .
			iish_t('Current user ( @user ) is not a conference crew member.',
				array('@user' => LoggedInUserDetails::getUser())) . '<br />' .
			iish_t('Please !login as a crew member.',
				array('!login' => l(iish_t('log out and login'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
					array('query' => drupal_get_destination())))), 'error');

		return '';
	}

	$ct = 0;

	// show change user page
	$form['ct' . $ct++] = array(
		'#type'   => 'markup',
		'#markup' => '<div><br />' . iish_t('Please enter # or e-mail of user.') . '</div>',
	);

	$form['user_id'] = array(
		'#type'          => 'textfield',
		'#title'         => 'User # or e-mail',
		'#size'          => 20,
		'#maxlength'     => 100,
		'#required'      => true,
		'#prefix'        => '<div class="iishconference_container_inline">',
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
		if ($userInfo['isCrew']) {
			form_set_error('user_id', iish_t('You cannot change into a crew member.'));
			$form_state['rebuild'] = true;
		}
		else if ($userInfo['hasFullRights']) {
			form_set_error('user_id', iish_t('You cannot change into an administrator.'));
			$form_state['rebuild'] = true;
		}
		else {
			$userStatus = LoggedInUserDetails::setCurrentlyLoggedInWithResponse($userInfo);
			if ($userStatus == LoggedInUserDetails::USER_STATUS_EXISTS) {
				drupal_set_message(iish_t("User changed."));

				// redirect to personal page
				$form_state['redirect'] = SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'personal-page';
			}
			else {
				switch ($userStatus) {
					case LoggedInUserDetails::USER_STATUS_DISABLED:
						drupal_set_message(iish_t("Account is disabled."), 'error');
						break;
					case LoggedInUserDetails::USER_STATUS_DELETED:
						drupal_set_message(iish_t("Account is deleted"), 'error');
						break;
					default:
						drupal_set_message(iish_t("Incorrect email / id."), 'error');
				}

				$form_state['rebuild'] = true;
			}
		}
	}
	else {
		form_set_error('user_id', iish_t("Cannot find user..."));
		$form['rebuild'] = true;
	}
}
