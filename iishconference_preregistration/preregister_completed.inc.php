<?php

/**
 * Indication for the user that he/she finished the pre-registration
 */
function preregister_completed() {
	return theme('iishconference_container',
		array('fields' => array(
			'<span class="eca_remark heavy">' .
				t('You are now pre-registered for the @conference conference.',
					array('@conference' => CachedConferenceApi::getEventDate()->getLongCodeAndYear())) .
				'<br />' .
				t('In a few minutes you will receive by e-mail a copy of your pre-registration.') .
			'</span>',

			'<br /><br />',

			'<span class="eca_warning heavy">' .
				t('It is not possible to modify your pre-registration anymore.') .
				'<br />' .
				t('If you would like to modify your registration please send an email to !email.',
					array('!email' =>
						      ConferenceMisc::encryptEmailAddress(SettingsApi::getSetting(SettingsApi::DEFAULT_ORGANISATION_EMAIL)))) .
			'</span>',

			'<br /><br />',

			'<span class="eca_remark heavy">' .
				t('Go to your !link.',
					array('!link' => l(t('personal page'),
						SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'personal-page'))) .
			'</span>',
		)
	));
}