<?php

/**
 * Implements hook_form()
 */
function preregister_login_form($form, &$form_state) {
	$form['login'] = array(
		'#type' => 'fieldset',
	);

	$form['login']['help_text'] = array(
		'#type'   => 'markup',
		'#markup' => '<div class="bottommargin">' . iish_t('Please enter your e-mail address.') . '</div>',
	);

	$form['login']['email'] = array(
		'#type'      => 'textfield',
		'#title'     => 'E-mail',
		'#required'  => true,
		'#size'      => 20,
		'#maxlength' => 100,
	);

	$form['login']['submit'] = array(
		'#type'  => 'submit',
		'#name'  => 'submit',
		'#value' => iish_t('Next'),
	);

	// Privacy Statement
	if ( SettingsApi::getSetting(SettingsApi::SHOW_PRIVACY_STATEMENT_ON_REGISTRATION_PAGE) == '1'  ) {
		$form['privacystatement_block'] = array(
			'#type'   => 'markup',
			'#markup' => '<div class="iishconference_container"><div class="eca_privacy_statement">'
				. l( 'EuroSEAS ' . iish_t('Privacy Statement'), SettingsApi::getSetting(SettingsApi::URL_PRIVACY_STATEMENT) )
				. '</div></div>',
		);
	}

	$form['info_block'] = array(
		'#type'   => 'markup',
		'#markup' => ConferenceMisc::getInfoBlock(),
	);

	$form['comments_block'] = array(
		'#type'   => 'markup',
		'#markup' => '<div class="eca_warning">
			<br />
			<strong>' . iish_t('Comments') . '</strong>
			<br />
			<ol>
				<li>' .
			iish_t('Please disable (or minimize the size of) the cache in your browser (Internet Explorer, Firefox, Chrome)') . '</li>
				<li>' .
			iish_t('Use the back/next buttons in the form, do NOT use the browser back button') . '</li>
				<li>' .
			iish_t('Prepare your abstract beforehand. Do NOT type your abstract in the form field, but COPY it into the form field') . '</li>
			</ol>
		</div>',
	);

	return $form;
}

/**
 * Implements hook_form_validate()
 */
function preregister_login_form_validate($form, &$form_state) {
	$email = trim($form_state['values']['email']);

	if (!valid_email_address($email)) {
		form_set_error('email', iish_t('The e-mail address appears to be invalid.'));
	}
}

/**
 * Implements hook_form_submit()
 */
function preregister_login_form_submit($form, &$form_state) {
	$state = new PreRegistrationState($form_state);

	$email = strtolower(trim($form_state['values']['email']));
	$state->setEmail($email);
	$user = CRUDApiMisc::getFirstWherePropertyEquals(new UserApi(), 'email', $email);

	// If the user is not found, then this must be a new user, otherwise he/she must login with password first
	if ($user === null) {
		return PreRegistrationPage::PERSONAL_INFO;
	}

	return PreRegistrationPage::PASSWORD;
}
