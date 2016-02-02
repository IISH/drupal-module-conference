<?php

/**
 * Adds a session to the favorites list of the logged in participant
 *
 * @param SessionApi $session The session to add
 */
function iishconference_programme_add_session($session) {
	$output = array('success' => false);

	if ($session !== null) {
		$output['session'] = $session->getId();

		if (LoggedInUserDetails::isLoggedIn()) {
			$participant = LoggedInUserDetails::getParticipant();

			$sessionIds = $participant->getFavoriteSessionsId();
			$sessionIds[] = $session->getId();
			$participant->setFavoriteSessionsId($sessionIds);

			$success = $participant->save(false);
			$output['success'] = $success;
		}
	}

	drupal_json_output($output);
}

/**
 * Removes a session from the favorites list of the logged in participant
 *
 * @param SessionApi $session The session to remove
 */
function iishconference_programme_remove_session($session) {
	$output = array('success' => false);

	if ($session !== null) {
		$output['session'] = $session->getId();

		if (LoggedInUserDetails::isLoggedIn()) {
			$participant = LoggedInUserDetails::getParticipant();

			$sessionIds = $participant->getFavoriteSessionsId();
			$sessionIds = array_diff($sessionIds, array($session->getId()));
			$participant->setFavoriteSessionsId($sessionIds);

			$success = $participant->save(false);
			$output['success'] = $success;
		}
	}

	drupal_json_output($output);
}