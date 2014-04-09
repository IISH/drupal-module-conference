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

	// Start with the days
	if (SettingsApi::getSetting(SettingsApi::SHOW_DAYS) == 1) {
		$form['days_present'] = array(
			'#title'         => t('Days present'),
			'#type'          => 'checkboxes',
			'#description'   => t('Please select the days you will be present.') . ' <span class="heavy">' .
				FeeAmountApi::getFeeAmountsDescription($feeAmounts) . '</span>.',
			'#options'       => $days,
			'#default_value' => $user->getDaysPresentDayId(),
			'#required'      => true,
		);
	}

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

	// Any extras from which the participant can choose?
	$extras = CachedConferenceApi::getExtras();
	foreach ($extras as $extra) {
		$description = $extra->getSecondDescription();
		if ($extra->getAmount() > 0) {
			$description .= ' <span class="heavy">' . $extra->getAmountInFormat() . '</span>.';
		}

		$form['extras_' . $extra->getId()] = array(
			'#title'         => $extra->getTitle(),
			'#type'          => 'checkboxes',
			'#description'   => trim($description),
			'#options'       => array($extra->getId() => $extra->getDescription()),
			'#default_value' => $participant->getExtrasId(),
		);
	}

	// Only add accompanying persons if accepted
	if (SettingsApi::getSetting(SettingsApi::SHOW_ACCOMPANYING_PERSONS) == 1) {
		$accompanyingPersons = $participant->getAccompanyingPersons();
		$accompanyingPersonFeeState = FeeStateApi::getAccompanyingPersonFee();
		$accompanyingPersonFees = $accompanyingPersonFeeState->getFeeAmounts();

		// Always show add least one text field for participants to enter an accompanying person
		if (!isset($form_state['num_persons'])) {
			$form_state['num_persons'] = max(1, count($accompanyingPersons));
		}

		$form['accompanying_persons'] = array(
			'#type'   => 'container',
			'#prefix' => '<div id="accompanying-persons-wrapper">',
			'#suffix' => '</div>',
		);

		$title = t('Accompanying persons');
		$description = SettingsApi::getSetting(SettingsApi::ACCOMPANYING_PERSON_DESCRIPTION);
		$description .= ' <span class="heavy">' .
			FeeAmountApi::getFeeAmountsDescription($accompanyingPersonFees) . '</span>.';
		$form['accompanying_persons']['person']['#tree'] = true;

		// Display all accompanying persons previously stored, unless the user deliberately removed some
		foreach ($accompanyingPersons as $i => $accompanyingPerson) {
			if ($i <= ($form_state['num_persons'] - 1)) {
				$form['accompanying_persons']['person'][$i] = array(
					'#type'          => 'textfield',
					'#size'          => 40,
					'#maxlength'     => 100,
					'#default_value' => $accompanyingPerson,
					'#title'         => ($i === 0) ? $title : null,
					'#description'   => ($i === ($form_state['num_persons'] - 1)) ? trim($description) : null,
				);
			}
		}

		// Now display all additional empty text fields to enter accompanying persons, as many as requested by the user
		for ($i = count($accompanyingPersons); $i < $form_state['num_persons']; $i++) {
			$form['accompanying_persons']['person'][$i] = array(
				'#type'        => 'textfield',
				'#size'        => 40,
				'#maxlength'   => 100,
				'#title'       => ($i === 0) ? $title : null,
				'#description' => ($i === ($form_state['num_persons'] - 1)) ? trim($description) : null,
			);
		}

		$form['accompanying_persons']['add_person'] = array(
			'#type'                    => 'submit',
			'#name'                    => 'add_person',
			'#value'                   => t('Add one more person'),
			'#submit'                  => array('finalregistration_add_person'),
			'#limit_validation_errors' => array(),
			'#ajax'                    => array(
				'callback' => 'finalregistration_callback',
				'wrapper'  => 'accompanying-persons-wrapper',
				'progress' => array(
					'type'    => 'throbber',
					'message' => t('Please wait...'),
				),
			),
		);

		// Always display add least one text field to enter accompanying persons
		if ($form_state['num_persons'] > 1) {
			$form['accompanying_persons']['remove_person'] = array(
				'#type'                    => 'submit',
				'#name'                    => 'remove_person',
				'#value'                   => t('Remove the last person'),
				'#submit'                  => array('finalregistration_remove_person'),
				'#limit_validation_errors' => array(),
				'#ajax'                    => array(
					'callback' => 'finalregistration_callback',
					'wrapper'  => 'accompanying-persons-wrapper',
					'progress' => array(
						'type'    => 'throbber',
						'message' => t('Please wait...'),
					),
				),
			);
		}
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
			(strlen(trim($form_state['values']['address'])) === 0)
		) {
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
	if (SettingsApi::getSetting(SettingsApi::SHOW_DAYS) == 1) {
		$days = array();
		foreach ($form_state['values']['days_present'] as $dayId => $day) {
			if ($dayId == $day) {
				$days[] = $dayId;
			}
		}
		$user->setDaysPresent($days);
	}
	else {
		$days = CachedConferenceApi::getDays();
		$user->setDaysPresent(CRUDApiClient::getIds($days));
	}

	// Save invitation letter info
	if (array_key_exists('invitation_letter', $form_state['values'])) {
		$participant->setInvitationLetter($form_state['values']['invitation_letter']);
		$user->setAddress($form_state['values']['address']);
	}

	// Save extras
	$extras = array();
	foreach (CachedConferenceApi::getExtras() as $extra) {
		$value = $form_state['values']['extras_' . $extra->getId()][$extra->getId()];
		if ($extra->getId() == $value) {
			$extras[] = $extra->getId();
		}
	}
	$participant->setExtras($extras);

	// Save accompanying person(s) into the database
	if (SettingsApi::getSetting(SettingsApi::SHOW_ACCOMPANYING_PERSONS) == 1) {
		$accompanyingPersons = array();
		foreach ($form_state['values']['person'] as $accompanyingPerson) {
			$accompanyingPerson = trim($accompanyingPerson);
			if (strlen($accompanyingPerson) > 0) {
				$accompanyingPersons[] = $accompanyingPerson;
			}
		}
		$participant->setAccompanyingPersons($accompanyingPersons);

		// Reset the number of additional persons in form state
		unset($form_state['num_persons']);
	}

	$user->save();
	$participant->save();
}

function finalregistration_add_person($form, &$form_state) {
	$form_state['num_persons']++;
	$form_state['rebuild'] = true;
}

function finalregistration_remove_person($form, &$form_state) {
	$form_state['num_persons']--;
	$form_state['rebuild'] = true;
}

function finalregistration_callback($form, &$form_state) {
	return $form['accompanying_persons'];
}