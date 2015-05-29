<?php

/**
 * Implements hook_form()
 */
function preregister_personalinfo_form($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$user = $state->getUser();
	$participant = $state->getParticipant();

	$showChairDiscussantPool = (SettingsApi::getSetting(SettingsApi::SHOW_CHAIR_DISCUSSANT_POOL) == 1);
	$showLanguageCoaching = (SettingsApi::getSetting(SettingsApi::SHOW_LANGUAGE_COACH_PUPIL) == 1);

	$allVolunteering = PreRegistrationUtils::getAllVolunteeringOfUser($state);
	$networkOptions = CRUDApiClient::getAsKeyValueArray(CachedConferenceApi::getNetworks());

	$state->setFormData(array('volunteering' => $allVolunteering));

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// PERSONAL INFO

	$form['personal_info'] = array(
		'#type'  => 'fieldset',
		'#title' => iish_t('Personal info'),
	);

	$form['personal_info']['firstname'] = array(
		'#type'          => 'textfield',
		'#title'         => iish_t('First name'),
		'#size'          => 40,
		'#maxlength'     => 255,
		'#required'      => true,
		'#default_value' => $user->getFirstName(),
	);

	$form['personal_info']['lastname'] = array(
		'#type'          => 'textfield',
		'#title'         => iish_t('Last name'),
		'#size'          => 40,
		'#maxlength'     => 255,
		'#required'      => true,
		'#default_value' => $user->getLastName(),
	);

	$form['personal_info']['gender'] = array(
		'#title'         => iish_t('Gender'),
		'#type'          => 'select',
		'#options'       => ConferenceMisc::getGenders(),
		'#default_value' => $user->getGender(),
	);

	$form['personal_info']['organisation'] = array(
		'#type'          => 'textfield',
		'#title'         => iish_t('Organisation'),
		'#size'          => 40,
		'#maxlength'     => 255,
		'#required'      => true,
		'#default_value' => $user->getOrganisation(),
	);

	$form['personal_info']['department'] = array(
		'#type'          => 'textfield',
		'#title'         => iish_t('Department'),
		'#size'          => 40,
		'#maxlength'     => 255,
		'#required'      => true,
		'#default_value' => $user->getDepartment(),
	);

	$form['personal_info']['email'] = array(
		'#type'          => 'textfield',
		'#title'         => iish_t('E-mail'),
		'#size'          => 40,
		'#maxlength'     => 100,
		'#default_value' => $user->getEmail(),
		'#attributes'    => array('readonly' => 'readonly', 'class' => array('readonly-text')),
	);

	if (SettingsApi::getSetting(SettingsApi::SHOW_STUDENT) == 1) {
		$form['personal_info']['student'] = array(
			'#type'          => 'checkbox',
			'#title'         => iish_t('Please check if you are a (PhD) student'),
			'#default_value' => $participant->getStudent(),
		);
	}

	if (SettingsApi::getSetting(SettingsApi::SHOW_CV) == 1) {
		$form['personal_info']['cv'] = array(
			'#type'          => 'textarea',
			'#title'         => iish_t('Curriculum Vitae'),
			'#description'   => '<em>' . iish_t('(optional, max. 200 words)') . '</em>',
			'#rows'          => 2,
			'#required'      => SettingsApi::getSetting(SettingsApi::REQUIRED_CV) == 1,
			'#default_value' => $user->getCv(),
		);
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// ADDRESS

	$form['address'] = array(
		'#type'  => 'fieldset',
		'#title' => iish_t('Address'),
	);

	$form['address']['city'] = array(
		'#type'          => 'textfield',
		'#title'         => iish_t('City'),
		'#size'          => 40,
		'#maxlength'     => 255,
		'#required'      => true,
		'#default_value' => $user->getCity(),
	);

	$form['address']['country'] = array(
		'#type'          => 'select',
		'#title'         => iish_t('Country'),
		'#options'       => CRUDApiClient::getAsKeyValueArray(CachedConferenceApi::getCountries()),
		'#required'      => true,
		'#default_value' => $user->getCountryId(),
	);

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// COMMUNICATION MEANS

	$form['communication_means'] = array(
		'#type'  => 'fieldset',
		'#title' => iish_t('Communication Means'),
	);

	$form['communication_means']['phone'] = array(
		'#type'          => 'textfield',
		'#title'         => iish_t('Phone number'),
		'#size'          => 40,
		'#maxlength'     => 100,
		'#default_value' => $user->getPhone(),
	);

	$form['communication_means']['mobile'] = array(
		'#type'          => 'textfield',
		'#title'         => iish_t('Mobile number'),
		'#size'          => 40,
		'#maxlength'     => 100,
		'#default_value' => $user->getMobile(),
	);

	$form['communication_means']['extra_info'] = array(
		'#type'   => 'markup',
		'#markup' => '<span class="extra_info">' .
			iish_t('Please enter international numbers (including country prefix etc.)') .
			'</span>',
	);

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// EXTRA'S

	$extras = ExtraApi::getOnlyPreRegistration(CachedConferenceApi::getExtras());
	if (count($extras) > 0) {
		$form['extras'] = array(
			'#type'  => 'fieldset',
			'#title' => '',
		);

		foreach ($extras as $extra) {
			$form['extras']['extras_' . $extra->getId()] = array(
				'#title'         => $extra->getTitle(),
				'#type'          => 'checkboxes',
				'#description'   => $extra->getSecondDescription(),
				'#options'       => array($extra->getId() => $extra->getDescription()),
				'#default_value' => $participant->getExtrasId(),
			);
		}
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// CHAIR / DISCUSSANT POOL

	if ($showChairDiscussantPool) {
		$chairVolunteering =
			ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering, VolunteeringApi::CHAIR);
		$chairOptions = array_keys(CRUDApiClient::getAsKeyValueArray($chairVolunteering));

		$form['chair_discussant_pool'] = array(
			'#type'  => 'fieldset',
			'#title' => iish_t('Chair / discussant pool'),
		);

		$form['chair_discussant_pool']['volunteerchair'] = array(
			'#type'          => 'checkbox',
			'#title'         => iish_t('I would like to volunteer as Chair'),
			'#default_value' => count($chairOptions) > 0,
		);

		$form['chair_discussant_pool']['volunteerchair_networks'] = array(
			'#type'          => 'select',
			'#options'       => CRUDApiClient::getAsKeyValueArray(CachedConferenceApi::getNetworks()),
			'#multiple'      => true,
			'#size'          => 4,
			'#description'   => '<i>' . iish_t('Use CTRL key to select multiple networks.') . '</i>',
			'#states'        => array(
				'visible' => array(
					':input[name="volunteerchair"]' => array('checked' => true),
				),
			),
			'#default_value' => $chairOptions,
		);

		PreRegistrationUtils::hideAndSetDefaultNetwork($form['chair_discussant_pool']['volunteerchair_networks']);

		// + + + + + + + + + + + + + + + + + + + + + + + +

		$discussantVolunteering =
			ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering, VolunteeringApi::DISCUSSANT);
		$discussantOptions = array_keys(CRUDApiClient::getAsKeyValueArray($discussantVolunteering));

		$form['chair_discussant_pool']['volunteerdiscussant'] = array(
			'#type'          => 'checkbox',
			'#title'         => iish_t('I would like to volunteer as Discussant'),
			'#default_value' => count($discussantOptions) > 0,
		);

		$form['chair_discussant_pool']['volunteerdiscussant_networks'] = array(
			'#type'          => 'select',
			'#options'       => CRUDApiClient::getAsKeyValueArray(CachedConferenceApi::getNetworks()),
			'#multiple'      => true,
			'#size'          => 4,
			'#description'   => '<i>' . iish_t('Use CTRL key to select multiple networks.') . '</i>',
			'#states'        => array(
				'visible' => array(
					':input[name="volunteerdiscussant"]' => array('checked' => true),
				),
			),
			'#default_value' => $discussantOptions,
		);

		PreRegistrationUtils::hideAndSetDefaultNetwork($form['chair_discussant_pool']['volunteerdiscussant_networks']);
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// ENGLISH LANGUAGE COACH

	if ($showLanguageCoaching) {
		$coachVolunteering =
			ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering, VolunteeringApi::COACH);
		$pupilVolunteering =
			ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering, VolunteeringApi::PUPIL);

		$coachOptions = array_keys(CRUDApiClient::getAsKeyValueArray($coachVolunteering));
		$pupilOptions = array_keys(CRUDApiClient::getAsKeyValueArray($pupilVolunteering));

		$defaultValue = '';
		if (count($coachOptions) > 0) {
			$defaultValue = 'coach';
		}
		else if (count($pupilOptions) > 0) {
			$defaultValue = 'pupil';
		}

		$form['english_language_coach'] = array(
			'#type'  => 'fieldset',
			'#title' => iish_t('English Language Coach'),
		);

		$form['english_language_coach']['coachpupil'] = array(
			'#type'          => 'radios',
			'#options'       => ConferenceMisc::getLanguageCoachPupils(),
			'#default_value' => $defaultValue,
		);

		$form['english_language_coach']['coachpupil_networks'] = array(
			'#type'          => 'select',
			'#options'       => $networkOptions,
			'#multiple'      => true,
			'#size'          => 4,
			'#description'   => '<i>' . iish_t('Use CTRL key to select multiple networks.') . '</i>',
			'#states'        => array(
				'visible' => array(
					array(':input[name="coachpupil"]' => array('value' => 'coach')),
					'or',
					array(':input[name="coachpupil"]' => array('value' => 'pupil')),
				)
			),
			'#default_value' => (count($coachOptions) > 0) ? $coachOptions : $pupilOptions,
		);

		PreRegistrationUtils::hideAndSetDefaultNetwork($form['english_language_coach']['coachpupil_networks']);
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +

	$form['submit'] = array(
		'#type'  => 'submit',
		'#name'  => 'submit',
		'#value' => iish_t('Next'),
	);

	return $form;
}

/**
 * Implements hook_form_validate()
 */
function preregister_personalinfo_form_validate($form, &$form_state) {
	if (SettingsApi::getSetting(SettingsApi::SHOW_CHAIR_DISCUSSANT_POOL) == 1) {
		// Make sure that when a chair is checked, a network is chosen as well
		if ($form_state['values']['volunteerchair']) {
			if (count($form_state['values']['volunteerchair_networks']) === 0) {
				form_set_error('volunteerchair',
					iish_t('Please select a network or uncheck the field \'I would like to volunteer as Chair\'.')
				);
			}
		}

		// Make sure that when a discussant is checked, a network is chosen as well
		if ($form_state['values']['volunteerdiscussant']) {
			if (count($form_state['values']['volunteerdiscussant_networks']) === 0) {
				form_set_error('volunteerdiscussant',
					iish_t('Please select a network or uncheck the field \'I would like to volunteer as Discussant\'.')
				);
			}
		}
	}

	if (SettingsApi::getSetting(SettingsApi::SHOW_LANGUAGE_COACH_PUPIL) == 1) {
		// Make sure that when a language coach or pupil is checked, a network is chosen as well
		if (in_array($form_state['values']['coachpupil'], array('coach', 'pupil'))) {
			if (count($form_state['values']['coachpupil_networks']) === 0) {
				form_set_error('coachpupil',
					iish_t('Please select a network or select \'not applicable\' at English language coach.')
				);
			}
		}
	}
}

/**
 * Implements hook_form_submit()
 */
function preregister_personalinfo_form_submit($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$user = $state->getUser();
	$participant = $state->getParticipant();

	// First save the user
	$user->setEmail($form_state['values']['email']);
	$user->setFirstName($form_state['values']['firstname']);
	$user->setLastName($form_state['values']['lastname']);
	$user->setGender($form_state['values']['gender']);
	$user->setOrganisation($form_state['values']['organisation']);
	$user->setDepartment($form_state['values']['department']);
	$user->setCity($form_state['values']['city']);
	$user->setCountry($form_state['values']['country']);
	$user->setPhone($form_state['values']['phone']);
	$user->setMobile($form_state['values']['mobile']);

	if (SettingsApi::getSetting(SettingsApi::SHOW_CV) == 1) {
		$user->setCv($form_state['values']['cv']);
	}

	$user->save();

	// Then save the participant
	if (SettingsApi::getSetting(SettingsApi::SHOW_STUDENT) == 1) {
		$participant->setStudent($form_state['values']['student']);
	}
	$participant->setUser($user);

	// Don't forget the extras for this participant
	$extras = array();
	foreach (ExtraApi::getOnlyPreRegistration(CachedConferenceApi::getExtras()) as $extra) {
		$value = $form_state['values']['extras_' . $extra->getId()][$extra->getId()];
		if ($extra->getId() == $value) {
			$extras[] = $extra->getId();
		}
	}
	$participant->setExtras($extras);

	$participant->save();
	LoggedInUserDetails::setCurrentlyLoggedIn($user);

	// Then the volunteering options (chair / discussant / language coach / language pupil)
	$data = $state->getFormData();
	$allToDelete = $data['volunteering'];

	if (SettingsApi::getSetting(SettingsApi::SHOW_CHAIR_DISCUSSANT_POOL) == 1) {
		if ($form_state['values']['volunteerchair']) {
			preregister_personalinfo_save_volunteering($participant, VolunteeringApi::CHAIR,
				$form_state['values']['volunteerchair_networks'], $allToDelete);
		}
		if ($form_state['values']['volunteerdiscussant']) {
			preregister_personalinfo_save_volunteering($participant, VolunteeringApi::DISCUSSANT,
				$form_state['values']['volunteerdiscussant_networks'], $allToDelete);
		}
	}

	if (SettingsApi::getSetting(SettingsApi::SHOW_LANGUAGE_COACH_PUPIL) == 1) {
		if ($form_state['values']['coachpupil'] == 'coach') {
			preregister_personalinfo_save_volunteering($participant, VolunteeringApi::COACH,
				$form_state['values']['coachpupil_networks'], $allToDelete);
		}
		if ($form_state['values']['coachpupil'] == 'pupil') {
			preregister_personalinfo_save_volunteering($participant, VolunteeringApi::PUPIL,
				$form_state['values']['coachpupil_networks'], $allToDelete);
		}
	}

	// Delete all previously saved volunteering choices that were not chosen again
	foreach ($allToDelete as $instance) {
		$instance->delete();
	}

	// Find out which page to go to next
	$typeOfRegistrationPage = new PreRegistrationPage(PreRegistrationPage::TYPE_OF_REGISTRATION);
	$commentsPage = new PreRegistrationPage(PreRegistrationPage::COMMENTS);

	if ($typeOfRegistrationPage->isOpen()) {
		return PreRegistrationPage::TYPE_OF_REGISTRATION;
	}
	else if ($commentsPage->isOpen()) {
		return PreRegistrationPage::COMMENTS;
	}
	else {
		return PreRegistrationPage::CONFIRM;
	}
}

/**
 * Look up which networks were chosen by the participant for the selected volunteering type.
 * If the network was chosen before, remove the instance from the list 'to be removed'.
 * If the network was not chosen before, create a new instance and save it.
 *
 * @param ParticipantDateApi|int       $participant    The participant in question
 * @param int                          $volunteeringId The volunteering type id
 * @param int[]                        $networkValues  The chosen network ids
 * @param ParticipantVolunteeringApi[] $allToDelete    The ParticipantVolunteeringApi previously saved
 */
function preregister_personalinfo_save_volunteering($participant, $volunteeringId, array $networkValues,
                                                    array &$allToDelete) {
	foreach ($networkValues as $networkId => $network) {
		if ($networkId == $network) {
			$foundInstance = false;
			foreach ($allToDelete as $key => $instance) {
				if (($instance->getVolunteeringId() == $volunteeringId) && ($instance->getNetworkId() == $networkId)) {
					$foundInstance = true;
					unset($allToDelete[$key]);
					break;
				}
			}

			if (!$foundInstance) {
				$participantVolunteering = new ParticipantVolunteeringApi();
				$participantVolunteering->setParticipantDate($participant);
				$participantVolunteering->setVolunteering($volunteeringId);
				$participantVolunteering->setNetwork($networkId);
				$participantVolunteering->save();
			}
		}
	}
}


