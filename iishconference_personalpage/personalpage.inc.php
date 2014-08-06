<?php

/**
 * Creates the personal page
 */
function conference_personalpage_main() {
	if (!LoggedInUserDetails::isLoggedIn()) {
		// redirect to login page
		header('Location: ' . url(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())));
		die(t('Go to !login page.',
			array('!login' => l(t('login'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())))));
	}

	$userDetails = LoggedInUserDetails::getUser();
	$participantDateDetails = LoggedInUserDetails::getParticipant();

	$personlPage = conference_personalpage_create_personal_info($userDetails, $participantDateDetails);
	$personlPage .= conference_personalpage_create_registration_info($userDetails, $participantDateDetails);
	$personlPage .= conference_personalpage_create_sessions_info($userDetails, $participantDateDetails);
	$personlPage .= conference_personalpage_create_papers_info($userDetails, $participantDateDetails);
	$personlPage .= conference_personalpage_create_chair_discussant_info($participantDateDetails);
	$personlPage .= conference_personalpage_create_language_info($participantDateDetails);
	$personlPage .= conference_personalpage_create_links($participantDateDetails);
	$personlPage .= conference_personalpage_create_links_network($participantDateDetails);

	return $personlPage;
}

/**
 * Creates the personal info container for the personal page
 *
 * @param UserApi                 $userDetails            The user in question
 * @param ParticipantDateApi|null $participantDateDetails The user in question participant details, if registered
 *
 * @return string The personal info container in HTML
 */
function conference_personalpage_create_personal_info($userDetails, $participantDateDetails) {
	$personalInfoContent = array(theme('iishconference_container_header', array('text' => t('Personal Info'))));

	$personalInfoContent[] = theme('iishconference_container_field', array(
		'label' => 'First name',
		'value' => $userDetails->getFirstName()
	));
	$personalInfoContent[] = theme('iishconference_container_field', array(
		'label' => 'Last name',
		'value' => $userDetails->getLastName()
	));
	$personalInfoContent[] = theme('iishconference_container_field', array(
		'label' => 'Gender',
		'value' => ConferenceMisc::getGender($userDetails->getGender())
	));
	$personalInfoContent[] = theme('iishconference_container_field', array(
		'label' => 'Organisation',
		'value' => $userDetails->getOrganisation()
	));
	$personalInfoContent[] = theme('iishconference_container_field', array(
		'label' => 'Department',
		'value' => $userDetails->getDepartment()
	));
	$personalInfoContent[] = theme('iishconference_container_field', array(
		'label' => 'E-mail',
		'value' => $userDetails->getEmail()
	));

	if (LoggedInUserDetails::isAParticipant() && (SettingsApi::getSetting(SettingsApi::SHOW_STUDENT) == 1)) {
		$personalInfoContent[] = theme('iishconference_container_field', array(
			'label' => '(PhD) Student?',
			'value' => ConferenceMisc::getYesOrNo($participantDateDetails->getStudent())
		));
	}

	$personalInfoContent[] = theme('iishconference_container_field', array(
		'label' => 'City',
		'value' => $userDetails->getCity()
	));
	$personalInfoContent[] = theme('iishconference_container_field', array(
		'label' => 'Country',
		'value' => $userDetails->getCountry()->getNameEnglish()
	));
	$personalInfoContent[] = theme('iishconference_container_field', array(
		'label' => 'Phone number',
		'value' => $userDetails->getPhone()
	));
	$personalInfoContent[] = theme('iishconference_container_field', array(
		'label' => 'Mobile number',
		'value' => $userDetails->getMobile()
	));

	if (SettingsApi::getSetting(SettingsApi::SHOW_CV) == 1) {
		$personalInfoContent[] = theme('iishconference_container_field', array(
			'label'          => 'Curriculum Vitae',
			'value'          => ConferenceMisc::getHTMLForLongText($userDetails->getCv()),
			'valueIsHTML'    => true,
			'valueOnNewLine' => true
		));
	}

	return theme('iishconference_container', array('fields' => $personalInfoContent));
}

/**
 * Creates the registration info content for the personal page
 *
 * @param UserApi                 $userDetails            The user in question
 * @param ParticipantDateApi|null $participantDateDetails The user in question participant details, if registered
 *
 * @return string The registration info container in HTML
 */
function conference_personalpage_create_registration_info($userDetails, $participantDateDetails) {
	$registeredAndPayedContent = array();

	if (LoggedInUserDetails::isAParticipant()) {
		$registeredAndPayedContent[] = '<span class="eca_remark heavy">' .
			t('You have pre-registered for the @conference',
				array('@conference' => CachedConferenceApi::getEventDate()->getLongNameAndYear())) . '</span>';

		$registeredAndPayedContent[] = '<br />';

		conference_personalpage_create_payment_status($registeredAndPayedContent, $participantDateDetails);

		$registeredAndPayedContent[] = '<br /><br />';

		$registeredAndPayedContent[] = theme('iishconference_container_field', array(
			'label' => 'Currently selected fee',
			'value' => $participantDateDetails->getFeeState()
		));

		$days = $userDetails->getDaysPresent();
		if ((count($days) > 0) && (SettingsApi::getSetting(SettingsApi::SHOW_DAYS) == 1)) {
			$registeredAndPayedContent[] = theme('iishconference_container_field', array(
				'label'          => 'I will be present on the following days',
				'value'          => theme('item_list', array('items' => $days)),
				'valueOnNewLine' => true,
				'valueIsHTML'    => true,
			));
		}

		$extrasIds = $participantDateDetails->getExtrasId();
		foreach (CachedConferenceApi::getExtras() as $extra) {
			$userHasRegistered = (array_search($extra->getId(), $extrasIds) !== false);
			$registeredAndPayedContent[] = theme('iishconference_container_field', array(
				'label' => $extra->getTitle(),
				'value' => ConferenceMisc::getYesOrNo($userHasRegistered)
			));
		}

		if (SettingsApi::getSetting(SettingsApi::SHOW_ACCOMPANYING_PERSONS) == 1) {
			$accompanyingPersons = $participantDateDetails->getAccompanyingPersons();
			$registeredAndPayedContent[] = theme('iishconference_container_field', array(
				'label' => 'Accompanying person(s)',
				'value' => (count($accompanyingPersons) > 0) ? ConferenceMisc::getEnumSingleLine($accompanyingPersons) :
						t('No accompanying person')
			));
		}
	}
	else {
		$registeredAndPayedContent[] = '<span class="eca_warning">' .
			t('You are not registered for the @conference. Please go to the !link.',
				array('@conference' => CachedConferenceApi::getEventDate()->getLongNameAndYear(),
				      '!link'       => l(t('pre-registration form'),
					      SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'pre-registration'))) . '</span>';
	}

	return theme('iishconference_container', array('fields' => $registeredAndPayedContent));
}

/**
 * Creates the payment status field by calling the PayWay API
 *
 * @param array              $registeredAndPayedContent The content array to which to add the payment status field
 * @param ParticipantDateApi $participantDateDetails    The participant of whom to check the payment status
 */
function conference_personalpage_create_payment_status(array &$registeredAndPayedContent, $participantDateDetails) {
	$paymentMethod = t('Payment: none');
	$paymentStatus = t('(Final registration and payment has not started yet)');

	if (module_exists('iishconference_finalregistration')) {
		$paymentStatus = t('(!link)', array('!link' => l(t('Final registration and payment'),
			SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'final-registration')));

		if (!is_null($participantDateDetails->getPaymentId()) && ($participantDateDetails->getPaymentId() !== 0)) {
			$orderDetails = new PayWayMessage(array('orderid' => $participantDateDetails->getPaymentId()));
			$order = $orderDetails->send('orderDetails');

			if (!empty($order)) {
				switch ($order->get('paymentmethod')) {
					case 0:
						$paymentMethod = t('Payment: online payment');
						break;
					case 1:
						$paymentMethod = t('Payment: bank transfer');
						break;
					case 2:
						$paymentMethod = t('Payment: cash');
						break;
					default:
						$paymentMethod = t('Payment unknown');
				}

				switch ($order->get('payed')) {
					case 0:
						$paymentStatus = t('(not yet confirmed)');
						break;
					case 1:
						$paymentStatus = t('(confirmed)');
						break;
					case 2:
					case 3:
						$paymentStatus = t('(refunded)');
						break;
					default:
						$paymentStatus = t('(status unknown)');
				}
			}
			else {
				$paymentMethod = t('Payment information is currently unavailable');
				$paymentStatus = '';
			}
		}
	}

	$registeredAndPayedContent[] = '<span>' . trim($paymentMethod . ' ' . $paymentStatus) . '</span>';
}

/**
 * Creates the sessions containers for the personal page
 *
 * @param UserApi                 $userDetails            The user in question
 * @param ParticipantDateApi|null $participantDateDetails The user in question participant details, if registered
 *
 * @return string The sessions containers in HTML
 */
function conference_personalpage_create_sessions_info($userDetails, $participantDateDetails) {
	$sessionsContainers = '';

	if (LoggedInUserDetails::isAParticipant()) {
		$papers = $userDetails->getPapers();
		$sessions = SessionParticipantApi::getAllSessions($userDetails->getSessionParticipantInfo());

		foreach ($sessions as $i => $session) {
			$sessionPapers = PaperApi::getPapersWithSession($papers, $session->getId());

			$header = t('Session @count of @total', array('@count' => $i + 1, '@total' => count($sessions)));
			$sessionContent = array(theme('iishconference_container_header', array('text' => $header)));

			conference_personalpage_create_session_info($userDetails, $participantDateDetails, $sessionContent,
				$sessionPapers, $session);

			$sessionsContainers .= theme('iishconference_container', array('fields' => $sessionContent));
		}
	}

	return $sessionsContainers;
}

/**
 * Creates the papers containers for the personal page
 *
 * @param UserApi                 $userDetails            The user in question
 * @param ParticipantDateApi|null $participantDateDetails The user in question participant details, if registered
 *
 * @return string The papers containers in HTML
 */
function conference_personalpage_create_papers_info($userDetails, $participantDateDetails) {
	$papersContainers = '';

	if (LoggedInUserDetails::isAParticipant()) {
		$papers = $userDetails->getPapers();
		$noSessionPapers = PaperApi::getPapersWithoutSession($papers);

		foreach ($noSessionPapers as $i => $paper) {
			$header = t('Paper  @count of @total', array('@count' => $i + 1, '@total' => count($noSessionPapers)));
			$paperContent = array(theme('iishconference_container_header', array('text' => $header)));

			conference_personalpage_create_paper_info($paperContent, $paper, $participantDateDetails);

			$papersContainers .= theme('iishconference_container', array('fields' => $paperContent));
		}
	}

	return $papersContainers;
}

/**
 * Adds session info to a session ccntent holder
 *
 * @param array      $sessionContent The session content holder to add info to
 * @param PaperApi[] $sessionPapers  The papers in this session
 * @param SessionApi $session        The session in question
 */

/**
 * Adds session info to a session ccntent holder
 *
 * @param UserApi            $userDetails            The user in this session
 * @param ParticipantDateApi $participantDateDetails The participant in this session
 * @param array              $sessionContent         The session content holder to add info to
 * @param PaperApi[]         $sessionPapers          The papers in this session
 * @param SessionApi         $session                The session in question
 */
function conference_personalpage_create_session_info($userDetails, $participantDateDetails, array &$sessionContent,
                                                     $sessionPapers, $session) {
	if (SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) {
		$networks = $session->getNetworks();
		foreach ($networks as $network) {
			$sessionContent[] = theme('iishconference_container_field', array(
				'label' => t('@network name', array('@network' => NetworkApi::getNetworkName())),
				'value' => $network->getName()
			));
			$sessionContent[] = theme('iishconference_container_field', array(
				'label' => t('Chairs of this @network',
					array('@network' => NetworkApi::getNetworkName(true, true))),
				'value' => implode(', ', $network->getChairs())
			));
			$sessionContent[] = '<br />';
		}
	}

	$sessionName = $session->getName() . ' <em>(' . $session->getState()->getDescription() . ')</em>';
	$sessionContent[] = theme('iishconference_container_field', array(
		'label'       => 'Session name',
		'value'       => $sessionName,
		'valueIsHTML' => true
	));

	$planned = CRUDApiMisc::getFirstWherePropertyEquals(new SessionRoomDateTimeApi(), 'session_id', $session->getId());
	if ($planned !== null) {
		$plannedText = '<span class="eca_warning heavy">' . $planned->getDay()
				->getDayFormatted("l d F Y") . ' / ' . $planned->getDateTimePeriod() . ' / ' .
			$planned->getRoomName() . '</span>';
		$sessionContent[] = theme('iishconference_container_field', array(
			'label'       => 'Session Date / Time / Room',
			'value'       => $plannedText,
			'valueIsHTML' => true
		));
	}

	$submittedBy = (is_object($session->getAddedBy())) ? $session->getAddedBy()->getFullName() : null;
	$sessionContent[] = theme('iishconference_container_field', array(
		'label' => 'Session submitted by',
		'value' => $submittedBy
	));

	$functionsInSession = SessionParticipantApi::getAllTypesOfUserForSession(
		$userDetails->getSessionParticipantInfo(),
		$userDetails->getId(),
		$session->getId()
	);

	$sessionContent[] = theme('iishconference_container_field', array(
		'label' => 'Your function in session',
		'value' => implode(', ', $functionsInSession)
	));
	$sessionContent[] = theme('iishconference_container_field', array(
		'label'          => 'Session abstract',
		'value'          => ConferenceMisc::getHTMLForLongText($session->getAbstr()),
		'valueIsHTML'    => true,
		'valueOnNewLine' => true
	));

	if (count($sessionPapers) > 0) {
		foreach ($sessionPapers as $paper) {
			$sessionContent[] = '<br />';
			$sessionContent[] = theme('iishconference_container_header', array('text' => t('Paper')));

			conference_personalpage_create_paper_info($sessionContent, $paper, $participantDateDetails);
		}
	}
	else {
		$sessionContent[] = '<br />';
		$sessionContent[] = theme('iishconference_container_header', array('text' => t('Paper')));
		$sessionContent[] = t('No paper.');
	}
}

/**
 * Adds paper info to a paper content holder
 *
 * @param array              $paperContent The paper content holder to add info to
 * @param PaperApi           $paper        The paper in question
 * @param ParticipantDateApi $participant  The participant of this paper
 */
function conference_personalpage_create_paper_info(array &$paperContent, $paper, $participant) {
	$paperContent[] = theme('iishconference_container_field', array(
			'label' => 'Title',
			'value' => $paper->getTitle())
	);
	$paperContent[] = theme('iishconference_container_field', array(
		'label' => 'Paper state',
		'value' => $paper->getState()->getDescription()
	));
	$paperContent[] = theme('iishconference_container_field', array(
		'label'          => 'Abstract',
		'value'          => ConferenceMisc::getHTMLForLongText($paper->getAbstr()),
		'valueIsHTML'    => true,
		'valueOnNewLine' => true
	));
	$paperContent[] = theme('iishconference_container_field', array(
		'label' => 'Co-author(s)',
		'value' => $paper->getCoAuthors()
	));

	if ((SettingsApi::getSetting(SettingsApi::SHOW_AWARD) == 1) && $participant->getStudent()) {
		$awardText = ConferenceMisc::getYesOrNo($participant->getAward());
		$awardText .= '&nbsp; <em>(' . l(t('more about the award'), 'award') . ')</em>';
		$paperContent[] = theme('iishconference_container_field', array(
			'label'       => SettingsApi::getSetting(SettingsApi::AWARD_NAME) . '?',
			'value'       => $awardText,
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

	if ($paper->getFileName() == null) {
		$paperLinkText = t('Upload paper');
	}
	else {
		$paperLinkText = t('Uploaded paper:') . ' ' . $paper->getFileName();
	}

	$paperContent[] = '<br /><span class="heavy"> ' .
		l($paperLinkText, SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'personal-page/upload-paper/' .
			$paper->getId()) . '</span>';
}

/**
 * Creates the chair/discussant volunteering content for the personal page
 *
 * @param ParticipantDateApi|null $participantDateDetails The user in question participant details, if registered
 *
 * @return string The chair/discussant volunteering container in HTML
 */
function conference_personalpage_create_chair_discussant_info($participantDateDetails) {
	$showChairDiscussant = (SettingsApi::getSetting(SettingsApi::SHOW_CHAIR_DISCUSSANT_POOL) == 1);

	if (LoggedInUserDetails::isAParticipant() && $showChairDiscussant) {
		$chairDiscussantContent = array();
		$allVolunteering = $participantDateDetails->getParticipantVolunteering();

		$networksAsChair = ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering,
			VolunteeringApi::CHAIR);
		$networksAsDiscussant = ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering,
			VolunteeringApi::DISCUSSANT);

		CRUDApiClient::sort($networksAsChair);
		CRUDApiClient::sort($networksAsDiscussant);

		$chairDiscussantContent[] =
			theme('iishconference_container_header', array('text' => t('Chair / Discussant pool')));

		$chairDiscussantContent[] = theme('iishconference_container_field', array(
			'label' => 'I would like to volunteer as Chair?',
			'value' => ConferenceMisc::getYesOrNo(count($networksAsChair) > 0)
		));

		if ((SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) && (count($networksAsChair) > 0)) {
			$chairDiscussantContent[] = theme('iishconference_container_field', array(
				'label' => NetworkApi::getNetworkName(false),
				'value' => implode(', ', $networksAsChair)
			));
		}

		$chairDiscussantContent[] = theme('iishconference_container_field', array(
			'label' => 'I would like to volunteer as Discussant?',
			'value' => ConferenceMisc::getYesOrNo(count($networksAsDiscussant) > 0)
		));

		if ((SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) && (count($networksAsDiscussant) > 0)) {
			$chairDiscussantContent[] = theme('iishconference_container_field', array(
				'label' => NetworkApi::getNetworkName(false),
				'value' => implode(', ', $networksAsDiscussant)
			));
		}

		return theme('iishconference_container', array('fields' => $chairDiscussantContent));
	}

	return '';
}

/**
 * Creates the language coach/pupil volunteering content for the personal page
 *
 * @param ParticipantDateApi|null $participantDateDetails The user in question participant details, if registered
 *
 * @return string The language coach/pupil volunteering container in HTML
 */
function conference_personalpage_create_language_info($participantDateDetails) {
	$showLanguage = (SettingsApi::getSetting(SettingsApi::SHOW_LANGUAGE_COACH_PUPIL) == 1);

	if (LoggedInUserDetails::isAParticipant() && $showLanguage) {
		$languageContent = array();
		$allVolunteering = $participantDateDetails->getParticipantVolunteering();

		$networksAsCoach = ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering,
			VolunteeringApi::COACH);
		$networksAsPupil = ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering,
			VolunteeringApi::PUPIL);

		CRUDApiClient::sort($networksAsCoach);
		CRUDApiClient::sort($networksAsPupil);

		$languageFound = false;
		$languageContent =
			array(theme('iishconference_container_header', array('text' => t('English Language Coach'))));

		if ((SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) && (count($networksAsCoach) > 0)) {
			$languageFound = true;
			$languageContent[] = theme('iishconference_container_field', array(
				'label' => t('I would like to be an English Language Coach in the following @networks',
					array('@networks' => NetworkApi::getNetworkName(false, true))),
				'value' => implode(', ', $networksAsCoach),
			));
		}
		else if (count($networksAsCoach) > 0) {
			$languageFound = true;
			$languageContent[] = theme('iishconference_container_field', array(
				'label' => t('I would like to be an English Language Coach'),
				'value' => ConferenceMisc::getYesOrNo(true),
			));
		}

		if (count($networksAsPupil) > 0) {
			$languageFound = true;
			$networksAndUsers = ParticipantVolunteeringApi::getAllUsersWithTypeForNetworks(
				VolunteeringApi::COACH, $networksAsPupil);

			$list = array();
			foreach ($networksAsPupil as $network) {
				CRUDApiClient::sort($networksAndUsers[$network->getId()]);

				$emailList = array();
				if (is_array($networksAndUsers[$network->getId()])) {
					foreach ($networksAndUsers[$network->getId()] as $user) {
						$emailList[] =
							l($user->getFullName(), 'mailto:' . $user->getEmail(), array('absolute' => true));
					}
				}

				if (count($emailList) > 0) {
					if (SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) {
						$list[] = '<strong>' . $network->getName() . '</strong>: ' .
							ConferenceMisc::getEnumSingleLine($emailList);
					}
					else {
						$list[] = ConferenceMisc::getEnumSingleLine($emailList);
					}
				}
				else {
					if (SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) {
						$list[] = '<strong>' . $network->getName() . '</strong>: <em>' .
							t('No language coaches found in this @network!',
								array('@network' => NetworkApi::getNetworkName(true, true))) . '</em>';
					}
					else {
						$list[] = '<em>' . t('No language coaches found!') . '</em>';
					}
				}
			}

			if (SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) {
				$languageCoachLabel = t('I need some help from one of the following English Language Coaches ' .
					'in each chosen @network', array('@network' => NetworkApi::getNetworkName(true, true)));
			}
			else {
				$languageCoachLabel = t('I need some help from one of the following English Language Coaches');
			}

			$languageContent[] = theme('iishconference_container_field', array(
				'label'          => $languageCoachLabel,
				'value'          => str_replace("\n", '', theme('item_list', array('items' => $list))),
				'valueIsHTML'    => true,
				'valueOnNewLine' => true
			));
		}

		if (!$languageFound) {
			$languageContent[] = '<em>' . ConferenceMisc::getLanguageCoachPupil('') . '</em>';
		}

		return theme('iishconference_container', array('fields' => $languageContent));
	}

	return '';
}

/**
 * Creates the links for the personal page
 *
 * @param ParticipantDateApi|null $participantDateDetails The user in question participant details, if registered
 *
 * @return string The links container in HTML
 */
function conference_personalpage_create_links($participantDateDetails) {
	$linksContent = array(theme('iishconference_container_header', array('text' => t('Links'))));

	// show pre registration link if not registered or participant state is 'not finished' or 'new participant'
	if (module_exists('iishconference_preregistration') &&
		(is_null($participantDateDetails) ||
			$participantDateDetails->getStateId() === ParticipantStateApi::DID_NOT_FINISH_REGISTRATION ||
			$participantDateDetails->getStateId() === ParticipantStateApi::NEW_PARTICIPANT)
	) {
		$linksContent[] =
			'&bull; ' . l(t('Pre-registration form'),
				SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'pre-registration') . '<br />';
	}
	if (module_exists('iishconference_changepassword')) {
		$linksContent[] =
			'&bull; ' .
			l(t('Change password'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'change-password') . '<br />';
	}
	if (module_exists('iishconference_finalregistration')) {
		$linksContent[] = '&bull; ' .
			l(t('Final registration and payment'),
				SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'final-registration') . '<br />';
	}
	if (module_exists('iishconference_emails')) {
		$linksContent[] =
			'&bull; ' . l(t('List of e-mails sent to you'),
				SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'emails') . '<br />';
	}
	if (module_exists('iishconference_logout')) {
		$linksContent[] =
			'&bull; ' . l(t('Logout'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'logout') . '<br />';
	}
	// check if live or crew or network chair or chair or organizer
	if (module_exists('iishconference_program') && (
			(SettingsApi::getSetting(SettingsApi::SHOW_PROGRAMME_ONLINE) == 1) ||
			LoggedInUserDetails::hasFullRights() ||
			LoggedInUserDetails::isNetworkChair() ||
			LoggedInUserDetails::isChair() ||
			LoggedInUserDetails::isOrganiser())
	) {
		$programHeader = SettingsApi::getSetting(SettingsApi::ONLINE_PROGRAM_HEADER);
		if (($programHeader === null) || (strlen(trim($programHeader)) === 0)) {
			$programHeader = t('Preliminary Program');
		}

		$linksContent[] =
			'&bull; ' . l($programHeader, SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'program') . '<br />';
	}

	return theme('iishconference_container', array('fields' => $linksContent));
}

/**
 * Creates the network links for the personal page
 *
 * @param ParticipantDateApi|null $participantDateDetails The user in question participant details, if registered
 *
 * @return string The network links container in HTML
 */
function conference_personalpage_create_links_network($participantDateDetails) {
	if (LoggedInUserDetails::hasFullRights() || LoggedInUserDetails::isNetworkChair()) {
		$linksNetworkContent =
			array(theme('iishconference_container_header',
				array('text' => t('Links for chairs of a @network',
					array('@network' => NetworkApi::getNetworkName(true, true))))));

		if (module_exists('iishconference_networksforchairs')) {
			$linksNetworkContent[] = '&bull; ' . l(t('@networks, Sessions & Participants (and papers)',
						array('@networks' => NetworkApi::getNetworkName(false))),
					SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . NetworkApi::getNetworkName(false, true) .
					'forchairs') . '<br />';
		}
		if (module_exists('iishconference_networkparticipants')) {
			$linksNetworkContent[] = '&bull; ' .
				l(t('@networks and their Participants', array('@networks' => NetworkApi::getNetworkName(false))),
					SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . NetworkApi::getNetworkName(true, true) .
					'participants') . '<br />';
		}
		if (module_exists('iishconference_networkvolunteers')) {
			$linksNetworkContent[] = '&bull; ' . l(t('@networks and their Volunteers (Chair/Discussant)',
						array('@networks' => NetworkApi::getNetworkName(false))),
					SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . NetworkApi::getNetworkName(true, true) .
					'volunteers') . '<br />';
		}
		if (module_exists('iishconference_proposednetworkparticipants')) {
			$linksNetworkContent[] = '&bull; ' . l(t('Participants and their proposed @networks',
						array('@networks' => NetworkApi::getNetworkName(false, true))),
					SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'proposed' .
					NetworkApi::getNetworkName(true, true) . 'participants') . '<br />';
		}
		if (module_exists('iishconference_electionadvisory')) {
			$linksNetworkContent[] = '&bull; ' . l(t('Election \'Advisory board\''),
					SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'election-advisory-board') . '<br />';
		}

		return theme('iishconference_container', array('fields' => $linksNetworkContent));
	}

	return '';
}
