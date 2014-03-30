<?php

/**
 * Implements hook_form()
 */
function preregister_confirm_form($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$user = $state->getUser();
	$participant = $state->getParticipant();

	$showChairDiscussantPool = (SettingsApi::getSetting(SettingsApi::SHOW_CHAIR_DISCUSSANT_POOL) == 1);
	$showLanguageCoaching = (SettingsApi::getSetting(SettingsApi::SHOW_LANGUAGE_COACH_PUPIL) == 1);

	$allVolunteering = array();
	if ($showChairDiscussantPool || $showLanguageCoaching) {
		$allVolunteering =
			CRUDApiMisc::getAllWherePropertyEquals(new ParticipantVolunteeringApi(), 'participantDate_id',
				$participant->getId())->getResults();
	}

	$showPapers = SettingsApi::getSetting(SettingsApi::SHOW_AUTHOR_REGISTRATION);
	$showSessions = SettingsApi::getSetting(SettingsApi::SHOW_ORGANIZER_REGISTRATION);

	$papers = array();
	if ($showPapers) {
		$props = new ApiCriteriaBuilder();
		$papers = PaperApi::getListWithCriteria(
			$props
				->eq('user_id', $user->getId())
				->eq('addedBy_id', $user->getId())
				->get()
		)->getResults();
	}

	$sessions = array();
	if ($showSessions) {
		$sessions =
			CRUDApiMisc::getAllWherePropertyEquals(new SessionApi(), 'addedBy_id', $user->getId())->getResults();
	}

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +
	// PERSONAL INFO

	$personalInfoContent = array(theme('iishconference_container_header', array('text' => t('Personal Info'))));

	$personalInfoContent[] = theme('iishconference_container_field', array(
		'label' => 'First name',
		'value' => $user->getFirstName()
	));
	$personalInfoContent[] = theme('iishconference_container_field', array(
		'label' => 'Last name',
		'value' => $user->getLastName()
	));
	$personalInfoContent[] = theme('iishconference_container_field', array(
		'label' => 'Gender',
		'value' => ConferenceMisc::getGender($user->getGender())
	));
	$personalInfoContent[] = theme('iishconference_container_field', array(
		'label' => 'Organisation',
		'value' => $user->getOrganisation()
	));
	$personalInfoContent[] = theme('iishconference_container_field', array(
		'label' => 'Department',
		'value' => $user->getDepartment()
	));
	$personalInfoContent[] = theme('iishconference_container_field', array(
		'label' => 'E-mail',
		'value' => $user->getEmail()
	));

	if (SettingsApi::getSetting(SettingsApi::SHOW_STUDENT) == 1) {
		$personalInfoContent[] = theme('iishconference_container_field', array(
			'label' => '(PhD) Student?',
			'value' => ConferenceMisc::getYesOrNo($participant->getStudent())
		));
	}

	if (SettingsApi::getSetting(SettingsApi::SHOW_CV) == 1) {
		$personalInfoContent[] = theme('iishconference_container_field', array(
			'label'          => 'Curriculum Vitae',
			'value'          => $user->getCv(),
			'valueOnNewLine' => true
		));
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// ADDRESS

	$addressContent = array(theme('iishconference_container_header', array('text' => t('Address'))));

	$addressContent[] = theme('iishconference_container_field', array(
		'label' => 'City',
		'value' => $user->getCity()
	));
	$addressContent[] = theme('iishconference_container_field', array(
		'label' => 'Country',
		'value' => $user->getCountry()->getNameEnglish()
	));

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// COMMUNICATION MEANS

	$communicationContent = array(theme('iishconference_container_header', array('text' => t('Communication Means'))));

	$communicationContent[] = theme('iishconference_container_field', array(
		'label' => 'Phone number',
		'value' => $user->getPhone()
	));
	$communicationContent[] = theme('iishconference_container_field', array(
		'label' => 'Mobile number',
		'value' => $user->getMobile()
	));

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// CHAIR / DISCUSSANT POOL

	$chairDiscussantContent = array();
	if ($showChairDiscussantPool) {
		$chairVolunteering =
			ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering, VolunteeringApi::CHAIR);
		$discussantVolunteering =
			ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering, VolunteeringApi::DISCUSSANT);

		$chairDiscussantContent =
			array(theme('iishconference_container_header', array('text' => t('Chair / discussant pool'))));

		$chairDiscussantContent[] = theme('iishconference_container_field', array(
			'label' => 'I would like to volunteer as Chair?',
			'value' => ConferenceMisc::getYesOrNo(count($chairVolunteering) > 0)
		));

		if ((SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) && (count($chairVolunteering) > 0)) {
			$chairDiscussantContent[] = theme('iishconference_container_field', array(
				'label' => NetworkApi::getNetworkName(false),
				'value' => implode(', ', $chairVolunteering)
			));
		}

		$chairDiscussantContent[] = theme('iishconference_container_field', array(
			'label' => 'I would like to volunteer as Discussant?',
			'value' => ConferenceMisc::getYesOrNo(count($discussantVolunteering) > 0)
		));

		if ((SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) && (count($discussantVolunteering) > 0)) {
			$chairDiscussantContent[] = theme('iishconference_container_field', array(
				'label' => NetworkApi::getNetworkName(false),
				'value' => implode(', ', $discussantVolunteering)
			));
		}
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// ENGLISH LANGUAGE COACH

	$englishCoachingContent = array();
	if ($showChairDiscussantPool) {
		$coachVolunteering =
			ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering, VolunteeringApi::COACH);
		$pupilVolunteering =
			ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering, VolunteeringApi::PUPIL);

		$englishCoachingContent =
			array(theme('iishconference_container_header', array('text' => t('English Language Coach'))));

		$englishCoachingContent[] = theme('iishconference_container_field', array(
			'label' => ConferenceMisc::getLanguageCoachPupil('coach'),
			'value' => ConferenceMisc::getYesOrNo(count($coachVolunteering) > 0)
		));

		if ((SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) && (count($coachVolunteering) > 0)) {
			$englishCoachingContent[] = theme('iishconference_container_field', array(
				'label' => NetworkApi::getNetworkName(false),
				'value' => implode(', ', $coachVolunteering)
			));
		}

		$englishCoachingContent[] = theme('iishconference_container_field', array(
			'label' => ConferenceMisc::getLanguageCoachPupil('pupil'),
			'value' => ConferenceMisc::getYesOrNo(count($pupilVolunteering) > 0)
		));

		if ((SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) && (count($pupilVolunteering) > 0)) {
			$englishCoachingContent[] = theme('iishconference_container_field', array(
				'label' => NetworkApi::getNetworkName(false),
				'value' => implode(', ', $pupilVolunteering)
			));
		}
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// PAPERS

	$papersContent = array();
	foreach ($papers as $i => $paper) {
		$paperContent = array(theme('iishconference_container_header', array('text' => t('Paper @count of @total',
			array('@count' => $i + 1, '@total' => count($papers))))));

		$paperContent[] = theme('iishconference_container_field', array(
			'label' => 'Title',
			'value' => $paper->getTitle()
		));
		$paperContent[] = theme('iishconference_container_field', array(
			'label'          => 'Abstract',
			'value'          => $paper->getAbstr(),
			'valueOnNewLine' => true
		));
		$paperContent[] = theme('iishconference_container_field', array(
			'label' => 'Co-author(s)',
			'value' => $paper->getCoAuthors()
		));

		if ((SettingsApi::getSetting(SettingsApi::SHOW_AWARD) == 1) && $participant->getStudent()) {
			$paperContent[] = theme('iishconference_container_field', array(
				'label'       => SettingsApi::getSetting(SettingsApi::AWARD_NAME) . '?',
				'value'       => ConferenceMisc::getYesOrNo($participant->getAward()),
				'valueIsHTML' => true
			));
		}

		$paperContent[] = theme('iishconference_container_field', array(
			'label' => 'Audio/visual equipment',
			'value' => implode(', ', $paper->getEquipment())
		));
		$paperContent[] = theme('iishconference_container_field', array(
			'label' => 'Extra audio/visual request',
			'value' => $paper->getEquipmentComment()
		));

		$papersContent[] = $paperContent;
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// SESSIONS

	$sessionsContent = array();
	foreach ($sessions as $i => $session) {
		$networks = $session->getNetworks();

		$sessionParticipants =
			CRUDApiMisc::getAllWherePropertyEquals(new SessionParticipantApi(), 'session_id', $session->getId())
				->getResults();
		$sessionPapers =
			CRUDApiMisc::getAllWherePropertyEquals(new PaperApi(), 'session_id', $session->getId())->getResults();

		$users = SessionParticipantApi::getAllUsers($sessionParticipants);

		// + + + + + + + + + + + + + + + + + + + + + + + +

		$sessionContent = array(theme('iishconference_container_header', array('text' => t('Session @count of @total',
			array('@count' => $i + 1, '@total' => count($sessions))))));

		$sessionContent[] = theme('iishconference_container_field', array(
			'label' => 'Session name',
			'value' => $session->getName()
		));

		$sessionContent[] = theme('iishconference_container_field', array(
			'label'          => 'Abstract',
			'value'          => $session->getAbstr(),
			'valueOnNewLine' => true
		));

		$sessionContent[] = theme('iishconference_container_field', array(
			'label' => NetworkApi::getNetworkName(),
			'value' => isset($networks[0]) ? $networks[0] : null
		));

		foreach ($users as $user) {
			$participant =
				CRUDApiMisc::getFirstWherePropertyEquals(new ParticipantDateApi(), 'user_id', $user->getId());
			$paper = PaperApi::getPapersOfUser($sessionPapers, $user->getId());
			$paper = isset($paper[0]) ? $paper[0] : null;
			$types = SessionParticipantApi::getAllTypesOfUserForSession($sessionParticipants, $user->getId(),
				$session->getId());

			$sessionContent[] = '<br />';
			$sessionContent[] = theme('iishconference_container_field', array(
				'label' => 'E-mail',
				'value' => $user->getEmail()
			));
			$sessionContent[] = theme('iishconference_container_field', array(
				'label' => 'First name',
				'value' => $user->getFirstName()
			));
			$sessionContent[] = theme('iishconference_container_field', array(
				'label' => 'Last name',
				'value' => $user->getLastName()
			));

			if (SettingsApi::getSetting(SettingsApi::SHOW_STUDENT) == 1) {
				$sessionContent[] = theme('iishconference_container_field', array(
					'label' => '(PhD) Student?',
					'value' => ConferenceMisc::getYesOrNo($participant->getStudent())
				));
			}

			if (SettingsApi::getSetting(SettingsApi::SHOW_CV) == 1) {
				$sessionContent[] = theme('iishconference_container_field', array(
					'label'          => 'Curriculum Vitae',
					'value'          => $user->getCv(),
					'valueOnNewLine' => true
				));
			}

			$sessionContent[] = theme('iishconference_container_field', array(
				'label' => 'Country',
				'value' => $user->getCountry()->getNameEnglish()
			));
			$sessionContent[] = theme('iishconference_container_field', array(
				'label' => 'Type(s)',
				'value' => implode(', ', $types),
			));

			if ($paper !== null) {
				$sessionContent[] = theme('iishconference_container_field', array(
					'label' => 'Paper title',
					'value' => $paper->getTitle()
				));
				$sessionContent[] = theme('iishconference_container_field', array(
					'label'          => 'Paper abstract',
					'value'          => $paper->getAbstr(),
					'valueOnNewLine' => true
				));
			}
		}

		$sessionsContent[] = $sessionContent;
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +

	$confirm = theme('iishconference_container', array('fields' => $personalInfoContent));
	$confirm .= theme('iishconference_container', array('fields' => $addressContent));
	$confirm .= theme('iishconference_container', array('fields' => $communicationContent));

	if (count($chairDiscussantContent) > 0) {
		$confirm .= theme('iishconference_container', array('fields' => $chairDiscussantContent));
	}
	if (count($englishCoachingContent) > 0) {
		$confirm .= theme('iishconference_container', array('fields' => $englishCoachingContent));
	}

	foreach ($papersContent as $paperContent) {
		$confirm .= theme('iishconference_container', array('fields' => $paperContent));
	}
	foreach ($sessionsContent as $sessionContent) {
		$confirm .= theme('iishconference_container', array('fields' => $sessionContent));
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +

	$form['confirm'] = array(
		'#type'   => 'markup',
		'#markup' => $confirm,
	);

	$form['submit_back_personalinfo'] = array(
		'#type'                    => 'submit',
		'#name'                    => 'submit_back_personalinfo',
		'#value'                   => t('Back to personal info page'),
		'#submit'                  => array('preregister_form_submit'),
		'#limit_validation_errors' => array(),
	);

	// Allow the user to move to the 'type of registration' page if either author
	// or organizer registration had been / is possible
	$showAuthor = SettingsApi::getSetting(SettingsApi::SHOW_AUTHOR_REGISTRATION);
	$showOrganizer = SettingsApi::getSetting(SettingsApi::SHOW_ORGANIZER_REGISTRATION);

	if (($showAuthor == 1) || ($showOrganizer == 1)) {
		$form['submit_back_typeofregistration'] = array(
			'#type'                    => 'submit',
			'#name'                    => 'submit_back_typeofregistration',
			'#value'                   => t('Back to previous step'),
			'#submit'                  => array('preregister_form_submit'),
			'#limit_validation_errors' => array(),
		);
	}

	$form['submit'] = array(
		'#type'  => 'submit',
		'#name'  => 'submit',
		'#value' => t('Confirm and finish pre-registration'),
	);

	return $form;
}

/**
 * Implements hook_form_submit()
 */
function preregister_confirm_form_submit($form, &$form_state) {
	$state = new PreRegistrationState($form_state);
	$user = $state->getUser();

	$participant = $state->getParticipant();
	$participant->setState(ParticipantStateApi::NEW_PARTICIPANT);
	$participant->save();

	// Also set the state of all session participants we added to 0
	$sessionParticipants =
		CRUDApiMisc::getAllWherePropertyEquals(new SessionParticipantApi(), 'addedBy_id', $user->getId())->getResults();
	$users = SessionParticipantApi::getAllUsers($sessionParticipants);
	foreach ($users as $addedUser) {
		$participant =
			CRUDApiMisc::getFirstWherePropertyEquals(new ParticipantDateApi(), 'user_id', $addedUser->getId());
		if ($participant->getStateId() == ParticipantStateApi::DID_NOT_FINISH_REGISTRATION) {
			$participant->setState(ParticipantStateApi::NEW_PARTICIPANT);
			$participant->save();
		}
	}

	$sendEmailApi = new SendEmailApi();
	$sendEmailApi->sendPreRegistrationFinishedEmail($state->getUser());

	drupal_goto(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'pre-registration/completed');
}

/**
 * What is the previous page?
 */
function preregister_confirm_form_back($form, &$form_state) {
	// Now find out if to which step we have to go to
	$submitName = $form_state['triggering_element']['#name'];

	// Move to the 'type of registration' page if either author or organizer registration had been / is possible
	$showAuthor = SettingsApi::getSetting(SettingsApi::SHOW_AUTHOR_REGISTRATION);
	$showOrganizer = SettingsApi::getSetting(SettingsApi::SHOW_ORGANIZER_REGISTRATION);

	if (($submitName === 'submit_back_typeofregistration') && (($showAuthor == 1) || ($showOrganizer == 1))) {
		return 'preregister_typeofregistration_form';
	}
	else {
		return 'preregister_personalinfo_form';
	}
}