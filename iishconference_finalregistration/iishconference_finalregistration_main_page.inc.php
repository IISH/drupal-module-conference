<?php
/**
 * @file
 * All functions for the first 'main' stage of the main form of this module
 */

/**
 * The actual form builder for the final registration procedure
 *
 * @param array $form       The form description
 * @param array $form_state The form state
 *
 * @return array $form is returned
 */
function finalregistration_main_form($form, &$form_state) {
	$participant = LoggedInUserDetails::getParticipant();
	$user = LoggedInUserDetails::getUser();

	$feeAmounts = $participant->getFeeAmounts();
	$days = CRUDApiClient::getAsKeyValueArray(CachedConferenceApi::getDays());

	$form['days_present'] = array(
		'#title'         => t('Days present'),
		'#type'          => 'checkboxes',
		'#description'   => t('Please select the days you will be present.') . ' ' . implode(', ', $feeAmounts) . '.',
		'#options'       => $days,
		'#default_value' => $user->getDaysPresentDayId(),
		'#required'      => true,
	);

	// Only show invitation letter to participants in a not exempted country and participating in at least one session
	$countryEvent = CountryApi::getCountryOfEvent();
	$userCountry = $user->getCountry();
	$isCountryExempt = (($userCountry !== null) && ($countryEvent !== null) &&
		in_array($userCountry->getId(), $countryEvent->getExemptCountriesId()));

	$isSessionParticipant = (count($user->getSessionParticipantInfo()) > 0);

	if (!$isCountryExempt && $isSessionParticipant) {
		$form['invitation_letter'] = array(
			'#title'         => t('Invitation letter'),
			'#type'          => 'checkbox',
			'#description'   => t('Please check if you will need an invitation letter.'),
			'#default_value' => $participant->getInvitationLetter(),
		);

		$form['address'] = array(
			'#title'         => t('Address'),
			'#type'          => 'textarea',
			'#description'   => t('Please enter the full address to which we have to send the invitation letter. ' .
				'This includes your name, address, zipcode and country.'),
			'#default_value' => $user->getAddress(),
			'#states'        => array(
				'visible' => array(
					'input[name="invitation_letter"]' => array('checked' => true),
				),
			),
		);
	}

	$extras = CachedConferenceApi::getExtras();
	foreach ($extras as $extra) {
		$form['extras_' . $extra->getExtra()] = array(
			'#title'         => $extra->getTitle(),
			'#type'          => 'checkboxes',
			'#description'   => $extra->getSecondDescription(),
			'#options'       => array($extra->getId() => $extra->getDescription()),
			'#default_value' => $participant->getExtrasId(),
		);
	}

	$form['next'] = array(
		'#type'  => 'submit',
		'#name'  => 'next',
		'#value' => t('Next step'),
	);

	return $form;
}

/**
 * The actual validator for the final registration procedure
 *
 * @param array $form       The form description
 * @param array $form_state The form state
 */
function finalregistration_main_validate($form, &$form_state) {
	// Make sure the values exists, if the user chooses to go back one step
	if (array_key_exists('invitation_letter', $form_state['values'])) {
		if (($form_state['values']['invitation_letter'] === 1) &&
			(strlen(trim($form_state['values']['address'])) === 0)) {
			form_set_error('address', t('Please enter your address, so we can send the invitation letter to you.'));
		}
	}
}

/**
 * The actual submit handler for the final registration procedure
 *
 * @param array $form       The form description
 * @param array $form_state The form state
 */
function finalregistration_main_submit($form, &$form_state) {
	$participant = LoggedInUserDetails::getParticipant();
	$user = LoggedInUserDetails::getUser();

	// Save days
	$days = array();
	foreach ($form_state['values']['days_present'] as $dayId => $day) {
		if ($dayId == $day) {
			$days[] = $dayId;
		}
	}
	$user->setDaysPresent($days);

	// Save invitation letter info
	if (array_key_exists('invitation_letter', $form_state['values'])) {
		$participant->setInvitationLetter($form_state['values']['invitation_letter']);
		$user->setAddress($form_state['values']['address']);
	}

	// Save extras
	$extras = array();
	foreach (CachedConferenceApi::getExtras() as $extra) {
		$value = $form_state['values']['extras_' . $extra->getExtra()][$extra->getId()];
		if ($extra->getId() == $value) {
			$extras[] = $extra->getId();
		}
	}
	$participant->setExtras($extras);

	$user->save();
	$participant->save();
}