<?php
/**
 * @file
 * Allows participants to browse through their emails
 */

/**
 * Requests the emails for the current page and displays them
 *
 * @return string The HTML for a table with the emails
 */
function conference_emails() {
	if (!LoggedInUserDetails::isLoggedIn()) {
		// redirect to login page
		header('Location: ' . url(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login', array('query' => drupal_get_destination())));
		die(t('Go to !login page.', array('!login' => l(t('login'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
			array('query' => drupal_get_destination())))));
	}

	$ecaSettings = CachedConferenceApi::getSettings();
	$maxTries = intval($ecaSettings[SettingsApi::EMAIL_MAX_NUM_TRIES]);

	$props = new ApiCriteriaBuilder();
	$emailsNotSent = SentEmailApi::getListWithCriteria(
		$props
			->eq('user_id', LoggedInUserDetails::getId())
			->eq('dateTimeSent', null)
			->sort('dateTimeCreated', 'desc')
			->get()
	)->getResults();

	$props = new ApiCriteriaBuilder();
	$emailsSent = SentEmailApi::getListWithCriteria(
		$props
			->eq('user_id', LoggedInUserDetails::getId())
			->ge('dateTimeSent', strtotime('-18 month'))
			->sort('dateTimeSent', 'desc')
			->get()
	)->getResults();

	// Now also sort on subject
	SentEmailApi::setSortOnCreated(true);
	CRUDApiClient::sort($emailsNotSent);

	SentEmailApi::setSortOnCreated(false);
	CRUDApiClient::sort($emailsSent);

	$rowsNotSent = array();
	foreach ($emailsNotSent as $email) {
		$rowsNotSent[] = array(
			array(
				'data' => l($email->getSubject(), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'emails/' . $email->getId())
			),
			array(
				'data' => (is_null($email->getDateTimeCreated())) ? null :
						date('j F Y H:i:s', $email->getDateTimeCreated())
			),
			array(
				'data' => ($email->getNumTries() >= $maxTries) ?
						'<font color="red">' . iish_t('Sending failed') . '</font>' : iish_t('Not sent yet')
			),
		);
	}

	$rowsSent = array();
	foreach ($emailsSent as $email) {
		$rowsSent[] = array(
			array(
				'data' => l($email->getSubject(), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'emails/' . $email->getId())
			),
			array(
				'data' => (is_null($email->getDateTimeSent())) ? null :
						date('j F Y H:i:s', $email->getDateTimeSent())
			),
		);
	}

	$tableNotSent = theme_table(
		array(
			"header"     => array(
				array('data' => iish_t('Email subject')),
				array('data' => iish_t('Date/time created')),
				array('data' => iish_t('Sending status')),
			),
			"rows"       => $rowsNotSent,
			"attributes" => array(),
			"sticky"     => true,
			"caption"    => null,
			"colgroups"  => array(),
			"empty"      => iish_t('No emails found!'),
		)
	);

	$tableSent = theme_table(
		array(
			"header"     => array(
				array('data' => iish_t('Email subject')),
				array('data' => iish_t('Date/time sent')),
			),
			"rows"       => $rowsSent,
			"attributes" => array(),
			"sticky"     => true,
			"caption"    => null,
			"colgroups"  => array(),
			"empty"      => iish_t('No emails found!'),
		)
	);

	$emailsPage = l(t('Go back to your personal page'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'personal-page') . '<br /><br />';
	$emailsPage .= theme('iishconference_container',
		array(
			'fields' => array(
				theme('iishconference_container_header',
					array('text' => iish_t('Emails that have not been sent to you yet'))),
				$tableNotSent
			)
		)
	);
	$emailsPage .= theme('iishconference_container',
		array(
			'fields' => array(
				theme('iishconference_container_header',
					array('text' => iish_t('Emails that have been sent to you'))),
				$tableSent
			)
		)
	);

	return $emailsPage;
}