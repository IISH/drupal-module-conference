<?php

/**
 * Toggles the opt-in of the logged-in user
 */
function iishconference_personalpage_optin_toggle() {
	$output = array('success' => false);

  if (LoggedInUserDetails::isLoggedIn()) {
    $user = LoggedInUserDetails::getUser();
    $user->setOptIn(!$user->getOptIn());

    $success = $user->save(false);
    $output['success'] = $success;
    $output['optin'] = $user->getOptIn();
  }

	drupal_json_output($output);
}