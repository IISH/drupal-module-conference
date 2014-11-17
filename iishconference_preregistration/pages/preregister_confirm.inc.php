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

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +
	// PERSONAL INFO

	$personalInfoContent = array(theme('iishconference_container_header', array('text' => iish_t('Personal Info'))));

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

	$addressContent = array(theme('iishconference_container_header', array('text' => iish_t('Address'))));

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

	$communicationContent =
		array(theme('iishconference_container_header', array('text' => iish_t('Communication Means'))));

	$communicationContent[] = theme('iishconference_container_field', array(
		'label' => 'Phone number',
		'value' => $user->getPhone()
	));
	$communicationContent[] = theme('iishconference_container_field', array(
		'label' => 'Mobile number',
		'value' => $user->getMobile()
	));

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// EXTRA'S

	$extrasContent = array();
	$extras = ExtraApi::getOnlyPreRegistration(CachedConferenceApi::getExtras());
	if (count($extras) > 0) {
		$extrasContent = array(theme('iishconference_container_header', array('text' => '')));

		$extrasParticipant = $participant->getExtrasOfPreRegistration();
		foreach ($extras as $extra) {
			$extrasContent[] = theme('iishconference_container_field', array(
				'label' => $extra->getDescription(),
				'value' => ConferenceMisc::getYesOrNo(array_search($extra, $extrasParticipant) !== false)
			));
		}
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// CHAIR / DISCUSSANT POOL

	$chairDiscussantContent = array();
	$allVolunteering = PreRegistrationUtils::getAllVolunteeringOfUser($state);

	if ($showChairDiscussantPool) {
		$chairVolunteering =
			ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering, VolunteeringApi::CHAIR);
		$discussantVolunteering =
			ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering, VolunteeringApi::DISCUSSANT);

		$chairDiscussantContent =
			array(theme('iishconference_container_header', array('text' => iish_t('Chair / discussant pool'))));

		$chairDiscussantContent[] = theme('iishconference_container_field', array(
			'label' => 'I would like to volunteer as Chair?',
			'value' => ConferenceMisc::getYesOrNo(count($chairVolunteering) > 0)
		));

		if (PreRegistrationUtils::showNetworks() && (count($chairVolunteering) > 0)) {
			$chairDiscussantContent[] = theme('iishconference_container_field', array(
				'label' => NetworkApi::getNetworkName(false),
				'value' => implode(', ', $chairVolunteering)
			));
		}

		$chairDiscussantContent[] = theme('iishconference_container_field', array(
			'label' => 'I would like to volunteer as Discussant?',
			'value' => ConferenceMisc::getYesOrNo(count($discussantVolunteering) > 0)
		));

		if (PreRegistrationUtils::showNetworks() && (count($discussantVolunteering) > 0)) {
			$chairDiscussantContent[] = theme('iishconference_container_field', array(
				'label' => NetworkApi::getNetworkName(false),
				'value' => implode(', ', $discussantVolunteering)
			));
		}
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// ENGLISH LANGUAGE COACH

	$englishCoachingContent = array();
	if ($showLanguageCoaching) {
		$coachVolunteering =
			ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering, VolunteeringApi::COACH);
		$pupilVolunteering =
			ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering, VolunteeringApi::PUPIL);

		$englishCoachingContent =
			array(theme('iishconference_container_header', array('text' => iish_t('English Language Coach'))));

		$englishCoachingContent[] = theme('iishconference_container_field', array(
			'label' => ConferenceMisc::getLanguageCoachPupil('coach'),
			'value' => ConferenceMisc::getYesOrNo(count($coachVolunteering) > 0)
		));

		if (PreRegistrationUtils::showNetworks() && (count($coachVolunteering) > 0)) {
			$englishCoachingContent[] = theme('iishconference_container_field', array(
				'label' => NetworkApi::getNetworkName(false),
				'value' => implode(', ', $coachVolunteering)
			));
		}

		$englishCoachingContent[] = theme('iishconference_container_field', array(
			'label' => ConferenceMisc::getLanguageCoachPupil('pupil'),
			'value' => ConferenceMisc::getYesOrNo(count($pupilVolunteering) > 0)
		));

		if (PreRegistrationUtils::showNetworks() && (count($pupilVolunteering) > 0)) {
			$englishCoachingContent[] = theme('iishconference_container_field', array(
				'label' => NetworkApi::getNetworkName(false),
				'value' => implode(', ', $pupilVolunteering)
			));
		}
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// PAPERS

	$papersContent = array();
	$papers = PreRegistrationUtils::getPapersOfUser($state);

	foreach ($papers as $i => $paper) {
		$paperContent = array(theme('iishconference_container_header', array('text' => iish_t('Paper @count of @total',
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

		if (!PreRegistrationUtils::useSessions()) {
			/*if (PreRegistrationUtils::showNetworks()) {
				$paperContent[] = theme('iishconference_container_field', array(
					'label' => 'Proposed network',
					'value' => $paper->getNetworkProposal()
				));
			}

			$paperContent[] = theme('iishconference_container_field', array(
				'label' => 'Proposed session',
				'value' => $paper->getSessionProposal()
			));*/
			$abc = 123; // TODO
		}
		else {
			$paperContent[] = theme('iishconference_container_field', array(
				'label' => 'Proposed session',
				'value' => $paper->getSession()
			));
		}

		if ((SettingsApi::getSetting(SettingsApi::SHOW_AWARD) == 1) && $participant->getStudent()) {
			$paperContent[] = theme('iishconference_container_field', array(
				'label'       => SettingsApi::getSetting(SettingsApi::AWARD_NAME) . '?',
				'value'       => ConferenceMisc::getYesOrNo($participant->getAward()),
				'valueIsHTML' => true
			));
		}

		if (SettingsApi::getSetting(SettingsApi::SHOW_EQUIPMENT) == 1) {
			$paperContent[] = theme('iishconference_container_field', array(
				'label' => 'Audio/visual equipment',
				'value' => implode(', ', $paper->getEquipment())
			));
			$paperContent[] = theme('iishconference_container_field', array(
				'label' => 'Extra audio/visual request',
				'value' => $paper->getEquipmentComment()
			));
		}

		$papersContent[] = $paperContent;
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// SESSIONS

	$sessionsContent = array();
	$sessionParticipants = PreRegistrationUtils::getSessionParticipantsAddedByUser($state);
	$sessions = SessionParticipantApi::getAllSessions($sessionParticipants);

	foreach ($sessions as $i => $session) {
		$networks = $session->getNetworks();

		$sessionParticipants = PreRegistrationUtils::getSessionParticipantsAddedByUserForSession($state, $session);
		$users = SessionParticipantApi::getAllUsers($sessionParticipants);

		// + + + + + + + + + + + + + + + + + + + + + + + +

		$sessionContent =
			array(theme('iishconference_container_header', array('text' => iish_t('Session @count of @total',
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

		if (PreRegistrationUtils::showNetworks()) {
			$sessionContent[] = theme('iishconference_container_field', array(
				'label' => NetworkApi::getNetworkName(),
				'value' => isset($networks[0]) ? $networks[0] : null
			));
		}

		foreach ($users as $user) {
			$participant =
				CRUDApiMisc::getFirstWherePropertyEquals(new ParticipantDateApi(), 'user_id', $user->getId());
			$roles = SessionParticipantApi::getAllTypesOfUserForSession(
				$sessionParticipants,
				$user->getId(),
				$session->getId()
			);
			$paper = PreRegistrationUtils::getPaperForSessionAndUser($state, $session, $user);

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
				'value' => implode(', ', $roles),
			));

			if ($paper->isUpdate()) {
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
	// SESSION PARTICIPANT TYPES

	$sessionParticipantTypesContent = array();
	$participantTypes = PreRegistrationUtils::getParticipantTypesForUser();

	foreach ($participantTypes as $participantType) {
		$sessionParticipants = PreRegistrationUtils::getSessionParticipantsOfUserWithType($state, $participantType);

		if (count($sessionParticipants) > 0) {
			$sessionParticipantTypeContent = array(theme('iishconference_container_header',
				array('text' => iish_t('@type in sessions', array('@type' => $participantType)))));

			$sessionParticipantTypeContent[] = theme('item_list', array(
				'title' => '',
				'type'  => 'ul',
				'items' => $sessions,
			));

			$sessionParticipantTypesContent[] = $sessionParticipantTypeContent;
		}
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +
	// GENERAL COMMENTS

	$generalComments = array();
	if ((SettingsApi::getSetting(SettingsApi::SHOW_GENERAL_COMMENTS) == 1) &&
		(strlen($participant->getExtraInfo()) > 0)
	) {
		$generalComments = array(theme('iishconference_container_header', array('text' => iish_t('General comments'))));

		$generalComments[] = theme('iishconference_container_field', array(
			'label' => '',
			'value' => $participant->getExtraInfo(),
		));
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +

	drupal_set_message(iish_t('Please check your data, scroll down, and confirm and finish your pre-registration.'),
		'warning');

	$confirm = theme('iishconference_container', array('fields' => $personalInfoContent));
	$confirm .= theme('iishconference_container', array('fields' => $addressContent));
	$confirm .= theme('iishconference_container', array('fields' => $communicationContent));

	if (count($extrasContent) > 0) {
		$confirm .= theme('iishconference_container', array('fields' => $extrasContent));
	}
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
	foreach ($sessionParticipantTypesContent as $sessionParticipantTypeContent) {
		$confirm .= theme('iishconference_container', array('fields' => $sessionParticipantTypeContent));
	}

	if (count($generalComments) > 0) {
		$confirm .= theme('iishconference_container', array('fields' => $generalComments));
	}

	// + + + + + + + + + + + + + + + + + + + + + + + +

	$form['confirm'] = array(
		'#type'   => 'markup',
		'#markup' => $confirm,
	);

	$form['submit_back_personalinfo'] = array(
		'#type'                    => 'submit',
		'#name'                    => 'submit_back_personalinfo',
		'#value'                   => iish_t('Back to personal info page'),
		'#submit'                  => array('preregister_form_submit'),
		'#limit_validation_errors' => array(),
	);

	$typeOfRegistrationPage = new PreRegistrationPage(PreRegistrationPage::TYPE_OF_REGISTRATION);
	$commentsPage = new PreRegistrationPage(PreRegistrationPage::COMMENTS);

	if ($typeOfRegistrationPage->isOpen()) {
		$form['submit_back_typeofregistration'] = array(
			'#type'                    => 'submit',
			'#name'                    => 'submit_back_typeofregistration',
			'#value'                   => iish_t('Back to type of registration page'),
			'#submit'                  => array('preregister_form_submit'),
			'#limit_validation_errors' => array(),
		);
	}

	if ($commentsPage->isOpen()) {
		$form['submit_back_comments'] = array(
			'#type'                    => 'submit',
			'#name'                    => 'submit_back_comments',
			'#value'                   => iish_t('Back to general comments page'),
			'#submit'                  => array('preregister_form_submit'),
			'#limit_validation_errors' => array(),
		);
	}

	$form['submit'] = array(
		'#type'  => 'submit',
		'#name'  => 'submit',
		'#value' => iish_t('Confirm and finish pre-registration'),
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

	$typeOfRegistrationPage = new PreRegistrationPage(PreRegistrationPage::TYPE_OF_REGISTRATION);
	$commentsPage = new PreRegistrationPage(PreRegistrationPage::COMMENTS);

	if (($submitName === 'submit_back_typeofregistration') && $typeOfRegistrationPage->isOpen()) {
		return 'preregister_typeofregistration_form';
	}
	else if (($submitName === 'submit_back_comments') && $commentsPage->isOpen()) {
		return 'preregister_comments_form';
	}
	else {
		return 'preregister_personalinfo_form';
	}
}