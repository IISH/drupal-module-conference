<?php

/**
 * Indication for the user that he/she finished the pre-registration
 */
function preregister_completed() {
	$fields = array(
		'<span class="eca_remark heavy">' .
		iish_t('You are now pre-registered for the @conference conference.',
			array('@conference' => CachedConferenceApi::getEventDate()->getLongNameAndYear())) .
		'<br />' .
		iish_t('In a few minutes you will receive by e-mail a copy of your pre-registration.') .
		'</span>',

		'<br /><br />',

		'<span class="eca_warning heavy">' .
		iish_t('It is not possible to modify your pre-registration anymore.') .
		'<br />' .
		iish_t('If you would like to modify your registration please send an email to !email.',
			array('!email' =>
				      ConferenceMisc::encryptEmailAddress(SettingsApi::getSetting(SettingsApi::DEFAULT_ORGANISATION_EMAIL)))) .
		'</span>',

		'<br /><br />',

		'<span class="eca_remark heavy">' .
		iish_t('Go to your !link.',
			array('!link' => l(t('personal page'),
				SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'personal-page'))) .
		'</span>'
	);

	$finalRegistrationLastDate = strtotime(SettingsApi::getSetting(SettingsApi::FINAL_REGISTRATION_LASTDATE));
	$isFinalRegistrationOpen = ConferenceMisc::isOpenForLastDate($finalRegistrationLastDate);
	if (module_exists('iishconference_finalregistration') && $isFinalRegistrationOpen) {
		$fields[] = '<br /><br />';
		$fields[] = '<span class="eca_remark heavy">' . iish_t('You have just pre-registered. Please go now to !link.',
				array('!link' => l(t('final registration and payment'),
					SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'final-registration'))) . '</span>';
	}

	return theme('iishconference_container', array('fields' => $fields));
}