<?php

/**
 * Implements hook_form()
 */
function preregister_personalinfo_form($form, &$form_state) {
	/**
	 * TODO:
	 * <style>
	 * <!--
	 * #edit-email {
	 * border: 0px;
	 * }
	 * // -->
	 * </style>
	 */

	$flow = new PreRegistrationFlow($form_state);
	$user = $flow->getUser();
	$participant = $flow->getParticipant();

	$showChairDiscussantPool = (SettingsApi::getSetting(SettingsApi::SHOW_CHAIR_DISCUSSANT_POOL) == 1);
	$showLanguageCoaching = (SettingsApi::getSetting(SettingsApi::SHOW_LANGUAGE_COACH_PUPIL) == 1);

	$allVolunteering = array();
	$networkOptions = array();
	if ($showChairDiscussantPool || $showLanguageCoaching) {
		$allVolunteering =
			CRUDApiMisc::getAllWherePropertyEquals(new ParticipantVolunteeringApi(), 'participantDate_id',
				$participant->getId())->getResults();
		$networks = CachedConferenceApi::getNetworks();
		$networkOptions = CRUDApiClient::getAsKeyValueArray($networks);
	}

	$flow->setFormData(array('volunteering' => $allVolunteering));

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// PERSONAL INFO

	$form['personal_info'] = array(
		'#type'  => 'fieldset',
		'#title' => t('Personal info'),
	);

	$form['personal_info']['firstname'] = array(
		'#type'          => 'textfield',
		'#title'         => t('First name'),
		'#size'          => 40,
		'#maxlength'     => 255,
		'#required'      => true,
		'#default_value' => $user->getFirstName(),
	);

	$form['personal_info']['lastname'] = array(
		'#type'          => 'textfield',
		'#title'         => t('Last name'),
		'#size'          => 40,
		'#maxlength'     => 255,
		'#required'      => true,
		'#default_value' => $user->getLastName(),
	);

	$form['personal_info']['gender'] = array(
		'#title'         => t('Gender'),
		'#type'          => 'select',
		'#options'       => ConferenceMisc::getGenders(),
		'#default_value' => $user->getGender(),
	);

	$form['personal_info']['organisation'] = array(
		'#type'          => 'textfield',
		'#title'         => t('Organisation'),
		'#size'          => 40,
		'#maxlength'     => 255,
		'#required'      => true,
		'#default_value' => $user->getOrganisation(),
	);

	$form['personal_info']['department'] = array(
		'#type'          => 'textfield',
		'#title'         => t('Department'),
		'#size'          => 40,
		'#maxlength'     => 255,
		'#required'      => true,
		'#default_value' => $user->getDepartment(),
	);

	$form['personal_info']['email'] = array(
		'#type'          => 'textfield',
		'#title'         => t('E-mail'),
		'#size'          => 40,
		'#maxlength'     => 100,
		'#default_value' => $user->getEmail(),
		'#attributes'    => array('readonly' => 'readonly'),
	);

	if (SettingsApi::getSetting(SettingsApi::SHOW_STUDENT) == 1) {
		$form['personal_info']['student'] = array(
			'#type'          => 'checkbox',
			'#title'         => t('Please check if you are a (PhD) student'),
			'#default_value' => $participant->getStudent(),
		);
	}

	if (SettingsApi::getSetting(SettingsApi::SHOW_CV) == 1) {
		$form['personal_info']['cv'] = array(
			'#type'          => 'textarea',
			'#title'         => t('Curriculum Vitae'),
			'#description'   => '<em>' . t('(max. 200 words)') . '</em>',
			'#rows'          => 2,
			'#default_value' => $user->getCv(),
		);
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// ADDRESS

	$form['address'] = array(
		'#type'  => 'fieldset',
		'#title' => t('Address'),
	);

	$form['address']['city'] = array(
		'#type'          => 'textfield',
		'#title'         => t('City'),
		'#size'          => 40,
		'#maxlength'     => 255,
		'#required'      => true,
		'#default_value' => $user->getCity(),
	);

	$form['address']['country'] = array(
		'#type'          => 'select',
		'#title'         => t('Country'),
		'#options'       => CRUDApiClient::getAsKeyValueArray(CachedConferenceApi::getCountries()),
		'#required'      => true,
		'#default_value' => $user->getCountryId(),
	);

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// COMMUNICATION MEANS

	$form['communication_means'] = array(
		'#type'  => 'fieldset',
		'#title' => t('Communication Means'),
	);

	$form['communication_means']['phone'] = array(
		'#type'          => 'textfield',
		'#title'         => t('Phone number'),
		'#size'          => 40,
		'#maxlength'     => 100,
		'#default_value' => $user->getPhone(),
	);

	$form['communication_means']['mobile'] = array(
		'#type'          => 'textfield',
		'#title'         => t('Mobile number'),
		'#size'          => 40,
		'#maxlength'     => 100,
		'#default_value' => $user->getMobile(),
	);

	$form['communication_means']['extra_info'] = array(
		'#type'   => 'markup',
		'#markup' => '<span class="extra_info">' .
			t('Please enter international numbers (including country prefix etc.)') .
			'</span>',
	);

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// CHAIR / DISCUSSANT POOL

	if ($showChairDiscussantPool) {
		$chairVolunteering =
			ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering, VolunteeringApi::CHAIR);
		$discussantVolunteering =
			ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering, VolunteeringApi::DISCUSSANT);

		$chairOptions = array_keys(CRUDApiClient::getAsKeyValueArray($chairVolunteering));
		$discussantOptions = array_keys(CRUDApiClient::getAsKeyValueArray($discussantVolunteering));

		$form['chair_discussant_pool'] = array(
			'#type'  => 'fieldset',
			'#title' => t('Chair / discussant pool'),
		);

		$form['chair_discussant_pool']['volunteerchair'] = array(
			'#type'          => 'checkbox',
			'#title'         => t('I would like to volunteer as Chair'),
			//'#prefix'        => '<div class='container-inline'><span style='vertical-align:top;'>',
			'#default_value' => count($chairOptions) > 0,
		);

		$form['chair_discussant_pool']['volunteerchair_networks'] = array(
			'#type'          => 'select',
			'#options'       => $networkOptions,
			//'#prefix'        => '</span>',
			//'#suffix'        => '</div>',
			'#multiple'      => true,
			'#size'          => 3,
			'#description'   => '<i>' . t('Use CTRL key to select multiple @networks.',
					array('@networks' => NetworkApi::getNetworkName(false, true))) . '</i>',
			'#states'        => array(
				'visible' => array(
					':input[name="volunteerchair"]' => array('checked' => true),
				),
			),
			'#default_value' => $chairOptions,
		);

		if (SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) != 1) {
			$networkId = SettingsApi::getSetting(SettingsApi::DEFAULT_NETWORK_ID);
			$form['chair_discussant_pool']['volunteerchair_networks']['#access'] = false;
			$form['chair_discussant_pool']['volunteerchair_networks']['#default_value'] =
				array($networkId => $networkId);
		}

		$form['chair_discussant_pool']['volunteerdiscussant'] = array(
			'#type'          => 'checkbox',
			'#title'         => t('I would like to volunteer as Discussant'),
			//	'#prefix'        => '<div class='container-inline'><span style='vertical-align:top;'>',
			'#default_value' => count($discussantOptions) > 0,
		);

		$form['chair_discussant_pool']['volunteerdiscussant_networks'] = array(
			'#type'          => 'select',
			'#options'       => $networkOptions,
			//'#prefix'        => '</span>',
			//'#suffix'        => '</div>',
			'#multiple'      => true,
			'#size'          => 3,
			'#description'   => '<i>' . t('Use CTRL key to select multiple @networks.',
					array('@networks' => NetworkApi::getNetworkName(false, true))) . '</i>',
			'#states'        => array(
				'visible' => array(
					':input[name="volunteerdiscussant"]' => array('checked' => true),
				),
			),
			'#default_value' => $discussantOptions,
		);

		if (SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) != 1) {
			$networkId = SettingsApi::getSetting(SettingsApi::DEFAULT_NETWORK_ID);
			$form['chair_discussant_pool']['volunteerdiscussant_networks']['#access'] = false;
			$form['chair_discussant_pool']['volunteerdiscussant_networks']['#default_value'] =
				array($networkId => $networkId);
		}
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

		$form['english_language_coach'] = array(
			'#type'  => 'fieldset',
			'#title' => t('English Language Coach'),
		);

		$defaultValue = '';
		if (count($coachOptions) > 0) {
			$defaultValue = 'coach';
		}
		else if (count($pupilOptions) > 0) {
			$defaultValue = 'pupil';
		}

		$form['english_language_coach']['coachpupil'] = array(
			'#type'          => 'radios',
			'#options'       => ConferenceMisc::getLanguageCoachPupils(),
			'#default_value' => $defaultValue,
			//'#prefix'        => '<div class='container-inline' style='float:left;width:46%;'>',
			//'#suffix'        => '</div>',
		);

		$form['english_language_coach']['coachpupil_networks'] = array(
			'#type'          => 'select',
			'#options'       => $networkOptions,
			'#multiple'      => true,
			'#size'          => 3,
			//'#prefix'        => '<div class='container-inline'>',
			//'#suffix'        => '</div>',
			'#description'   => '<i>' . t('Use CTRL key to select multiple @networks.',
					array('@networks' => NetworkApi::getNetworkName(false, true))) . '</i>',
			'#states'        => array(
				'visible' => array(
					array(':input[name="coachpupil"]' => array('value' => 'coach')),
					'or',
					array(':input[name="coachpupil"]' => array('value' => 'pupil')),
				)
			),
			'#default_value' => (count($coachOptions) > 0) ? $coachOptions : $pupilOptions,
		);

		if (SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) != 1) {
			$networkId = SettingsApi::getSetting(SettingsApi::DEFAULT_NETWORK_ID);
			$form['english_language_coach']['coachpupil_networks']['#access'] = false;
			$form['english_language_coach']['coachpupil_networks']['#default_value'] = array($networkId => $networkId);
		}
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +

	$form['submit'] = array(
		'#type'  => 'submit',
		'#name'  => 'submit',
		'#value' => t('Next'),
	);

	return $form;
}

/**
 * Implements hook_form_validate()
 */
function preregister_personalinfo_form_validate($form, &$form_state) {
	// controle als men chair aanklikt ook echt een netwerk wordt geselecteerd
	if ($form_state['values']['volunteerchair']) {
		if (count($form_state['values']['volunteerchair_networks']) === 0) {
			form_set_error('volunteerchair',
				t('Please select a @network or uncheck the field \'I would like to volunteer as Chair\'.'),
				array('@network' => NetworkApi::getNetworkName(true, false)));
		}
	}

	// controle als men discussant aanklikt ook echt een netwerk wordt geselecteerd
	if ($form_state['values']['volunteerdiscussant']) {
		if (count($form_state['values']['volunteerdiscussant_networks']) == 0) {
			form_set_error('volunteerdiscussant',
				t('Please select a @network or uncheck the field \'I would like to volunteer as Discussant\'.'),
				array('@network' => NetworkApi::getNetworkName(true, false)));
		}
	}

	// controle als men language aanklikt ook echt een netwerk wordt geselecteerd
	if (in_array($form_state['values']['coachpupil'], array('coach', 'pupil'))) {
		if (count($form_state['values']['coachpupil_networks']) == 0) {
			form_set_error('coachpupil',
				t('Please select a @network or select \'not applicable\' at English language coach.'),
				array('@network' => NetworkApi::getNetworkName(true, false)));
		}
	}
}

/**
 * Implements hook_form_submit()
 */
function preregister_personalinfo_form_submit($form, &$form_state) {
	$flow = new PreRegistrationFlow($form_state);
	$user = $flow->getUser();
	$participant = $flow->getParticipant();

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

	$participant->save();

	// Make sure the correct changes are also cached correctly
	$flow->updateUserAndParticipant($user, $participant);
	LoggedInUserDetails::setCurrentlyLoggedIn($user);

	// Then the volunteering options (chair / discussant / language coach / language pupil)
	$data = $flow->getFormData();
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

	// Move to the 'type of registration' page if either author or organizer registration had been / is possible
	$showAuthor = SettingsApi::getSetting(SettingsApi::SHOW_AUTHOR_REGISTRATION);
	$showOrganizer = SettingsApi::getSetting(SettingsApi::SHOW_ORGANIZER_REGISTRATION);

	if (($showAuthor == 1) || ($showOrganizer == 1)) {
		return 'preregister_typeofregistration_form';
	}
	else {
		return 'preregister_confirm_form';
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


