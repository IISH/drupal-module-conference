<?php
/**
 * @file
 * Allows a participant to read an email and resend the email
 */

/**
 * The main page for viewing the details of an email message and resending them
 *
 * @param int $emailId The id of the email in question
 *
 * @return string The HTML page
 */
function conference_email_main($emailId) {
	if (!LoggedInUserDetails::isLoggedIn()) {
		// redirect to login page
		Header("Location: /" . getSetting('pathForMenu') . "login/?backurl=" . urlencode($_SERVER["REQUEST_URI"]));
		die('Go to <a href="/' . getSetting('pathForMenu') . 'login/?backurl=' . urlencode($_SERVER["REQUEST_URI"]) .
		    '">login</a> page.');
	}

	$emailId = EasyProtection::easyIntegerProtection($emailId);
	$email = CRUDApiMisc::getById(new SentEmailApi(), $emailId);

	if ($email === null) {
		drupal_set_message(t('Unfortunately, this email does not seem to exist.'), 'error');

		return '';
	}
	if ($email->getUserId() !== LoggedInUserDetails::getId()) {
		drupal_set_message(t('You are only allowed to see emails sent to you.'), 'error');

		return '';
	}

	$form = drupal_get_form('conference_email_form', $emailId);

	$emailPage = theme('iishconference_container_inline', array(
		'inline' => array(
			l(t('Go back to your emails'), getSetting('pathForMenu') . 'emails'),
			'&nbsp;',
			l(t('Go back to your personal page'), getSetting('pathForMenu') . 'personal-page'),
			'<br /><br />',
		)
	));

	$emailPage .= theme('iishconference_container', array(
		'fields' => array(
			theme('iishconference_container_field', array(
				'label' => 'Original email sent on',
				'value' => (!is_null($email->getDateTimeSent()) ?
						$email->getDateTimeSentFormatted("j F Y H:i:s") :
						t('Not sent yet'))
			)),
			theme('iishconference_container_field', array(
				'label' => 'Copies of this email sent on',
				'value' => (!is_null($email->getDateTimesSentCopy()) ?
						implode(', ', $email->getDateTimesSentCopyFormatted("j F Y H:i:s")) :
						t('No copies sent yet'))
			)),
			'<br />',
			theme('iishconference_container_field', array(
				'label' => 'Email from',
				'value' => $email->getFromName() . ' ( ' . $email->getFromEmail() . ' )'
			)),
			theme('iishconference_container_field', array(
				'label' => 'Email subject',
				'value' => $email->getSubject()
			)),
			'<br />',
			theme('iishconference_container_field', array(
				'label'          => 'Email message',
				'value'          => $email->getBody(),
				'valueOnNewLine' => true
			)),
			'<br /><br />',
			drupal_render($form),
		)
	));

	return $emailPage;
}

/**
 * Sets up the form part of resending emails
 *
 * @param array $form       The form description
 * @param array $form_state The form state
 *
 * @return array The form
 */
function conference_email_form($form, &$form_state) {
	$form['resend'] = array(
		'#type'  => 'submit',
		'#name'  => 'resend',
		'#value' => t('(Re)send email now'),
	);

	return $form;
}

/**
 * Resend the selected email
 *
 * @param array $form       The form description
 * @param array $form_state The form state
 */
function conference_email_form_submit($form, &$form_state) {
	$emailId = $form_state['build_info']['args'][0];
	$resendEmailApi = new ResendEmailApi();
	if ($resendEmailApi->resendEmail($emailId)) {
		drupal_set_message(t('Your request for this email has been received and the email has just been sent to you. ' .
		                     'It can take a while before you will actually receive the email.'), 'status');
	}
}