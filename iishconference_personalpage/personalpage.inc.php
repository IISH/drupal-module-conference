<?php

/**
 * Creates the personal page
 */
function conference_personalpage_main() {
	if (!LoggedInUserDetails::isLoggedIn()) {
		// redirect to login page
		header('Location: ' . url(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
				array('query' => drupal_get_destination())));
		die(iish_t('Go to !login page.',
			array('!login' => l(iish_t('login'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
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
	$personalInfoContent = array(theme('iishconference_container_header', array('text' => iish_t('Personal Info'))));

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
			iish_t('You have pre-registered for the @conference',
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
						iish_t('No accompanying person')
			));
		}
	}
	else if (LoggedInUserDetails::isAParticipantWithoutConfirmation()) {
		$registeredAndPayedContent[] = '<span class="eca_warning">' .
			iish_t('You have not finished the pre-registration for the @conference. Please go to the !link.',
				array('@conference' => CachedConferenceApi::getEventDate()->getLongNameAndYear(),
				      '!link'       => l(iish_t('pre-registration form'),
					      SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'pre-registration'))) . '</span>';

		// TODO Should we only allow payments of finished pre-registrations? if so remove next 2 lines
		$registeredAndPayedContent[] = '<br />';
		conference_personalpage_create_payment_status($registeredAndPayedContent, $participantDateDetails);
	}
	else {
		$registeredAndPayedContent[] = '<span class="eca_warning">' .
			iish_t('You are not registered for the @conference. Please go to the !link.',
				array('@conference' => CachedConferenceApi::getEventDate()->getLongNameAndYear(),
				      '!link'       => l(iish_t('pre-registration form'),
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
	$paymentMethod = iish_t('Payment: none');
	$paymentStatus = iish_t('(Final registration and payment has not started yet)');
	$extraMessage = '';
	$amount = '';

	if (module_exists('iishconference_finalregistration')) {
		$paymentStatus = iish_t('(Please go to !link)', array('!link' => l(iish_t('Final registration and payment'),
			SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'final-registration')));

		if (!is_null($participantDateDetails->getPaymentId()) && ($participantDateDetails->getPaymentId() !== 0)) {
			$orderDetails = new PayWayMessage(array('orderid' => $participantDateDetails->getPaymentId()));
			$order = $orderDetails->send('orderDetails');

			if (!empty($order)) {
				switch ($order->get('paymentmethod')) {
					case PayWayMessage::ORDER_OGONE_PAYMENT:
						$paymentMethod = iish_t('Payment: online payment');
						break;
					case PayWayMessage::ORDER_BANK_PAYMENT:
						$paymentMethod = iish_t('Payment: bank transfer');
						break;
					case PayWayMessage::ORDER_CASH_PAYMENT:
						$paymentMethod = iish_t('Payment: on site');
						break;
					default:
						$paymentMethod = iish_t('Payment unknown');
				}

				switch ($order->get('payed')) {
					case PayWayMessage::ORDER_NOT_PAYED:
						$paymentStatus = iish_t('(your payment has not yet been confirmed)');

						switch ($order->get('paymentmethod')) {
							case PayWayMessage::ORDER_BANK_PAYMENT:
								$extraMessage = iish_t('<br>When we receive your bank payment we will confirm your payment.<br />If you have completed your bank payment and it is still not visible, please contact the conference secretariat.<br />You can also still pay online !link', array('!link' => l(iish_t('Final registration and payment'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'final-registration')));
								break;
							case PayWayMessage::ORDER_CASH_PAYMENT:
								$extraMessage = iish_t('<br>Your payment will be confirmed when you pay the fee at the conference.<br />You can still decide to pay online !link', array('!link' => l(iish_t('Final registration and payment'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'final-registration')));
								break;
						}

						break;
					case PayWayMessage::ORDER_PAYED:
						$paymentStatus = iish_t('(your payment has been confirmed)');
						break;
					case PayWayMessage::ORDER_REFUND_OGONE:
					case PayWayMessage::ORDER_REFUND_BANK:
						$paymentStatus = iish_t('(your payment has been refunded)');
						break;
					default:
						$paymentStatus = iish_t('(status of your payment is unknown)');
						$extraMessage = iish_t('<br>If you have completed your payment please contact the conference secretariat<br />else please try again !link', array('!link' => l(iish_t('Final registration and payment'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'final-registration')));
				}

				$amount = iish_t('<br>Amount: ') . number_format($order->get('amount') / 100) . ' EUR';
				$amount .= iish_t('<br>Order id: ') . $order->get('orderid');
			}
			else {
				$paymentMethod = iish_t('Payment information is currently unavailable');
				$paymentStatus = '';
			}
		}
	}

	$registeredAndPayedContent[] = '<span>' . trim($paymentMethod . ' ' . $paymentStatus) . $amount . $extraMessage . '</span>';
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

			$header = iish_t('Session @count of @total', array('@count' => $i + 1, '@total' => count($sessions)));
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
			$header = iish_t('Paper  @count of @total', array('@count' => $i + 1, '@total' => count($noSessionPapers)));
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
				'label' => 'Network name',
				'value' => $network->getName()
			));
			$sessionContent[] = theme('iishconference_container_field', array(
				'label' => 'Chairs of this network',
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

		// show session end time or only start time?
		$sessionTime = $planned->getDateTimePeriod();
		if ( SettingsApi::getSetting(SettingsApi::SHOW_SESSION_ENDTIME_IN_PP) == '0'  ) {
			$sessionTime = explode('-', $sessionTime);
			$sessionTime = $sessionTime[0];
		}

		$plannedText = '<span class="eca_warning heavy">' . $planned->getDay()
				->getDayFormatted("l d F Y") . ' / ' . $sessionTime . ' / ' .
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
			$sessionContent[] = theme('iishconference_container_header', array('text' => iish_t('Paper')));

			conference_personalpage_create_paper_info($sessionContent, $paper, $participantDateDetails);
		}
	}
	else {
		$sessionContent[] = '<br />';
		$sessionContent[] = theme('iishconference_container_header', array('text' => iish_t('Paper')));
		$sessionContent[] = iish_t('No paper.');
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
		$awardText .= '&nbsp; <em>(' . l(iish_t('more about the award'), 'award') . ')</em>';
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

	$paperContent[] = '<br />';

	$uploadPaperUrl = SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) .
		'personal-page/upload-paper/' . $paper->getId();
	if ($paper->getFileName() == null) {
		$paperContent[] = '<span class="heavy"> ' . l(iish_t('Upload paper'), $uploadPaperUrl) . '</span>';
	}
	else {
		$paperContent[] = theme('iishconference_container_field', array(
			'label'       => 'Uploaded paper',
			'value'       => l($paper->getFileName(), $paper->getDownloadURL()) .
								'&nbsp; <em>(' . l(iish_t('Edit uploaded paper'), $uploadPaperUrl) . ')</em>',
			'valueIsHTML' => true
		));
	}
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
			theme('iishconference_container_header', array('text' => iish_t('Chair / Discussant pool')));

		$chairDiscussantContent[] = theme('iishconference_container_field', array(
			'label' => 'I would like to volunteer as Chair?',
			'value' => ConferenceMisc::getYesOrNo(count($networksAsChair) > 0)
		));

		if ((SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) && (count($networksAsChair) > 0)) {
			$chairDiscussantContent[] = theme('iishconference_container_field', array(
				'label' => 'Networks',
				'value' => implode(', ', $networksAsChair)
			));
		}

		$chairDiscussantContent[] = theme('iishconference_container_field', array(
			'label' => 'I would like to volunteer as Discussant?',
			'value' => ConferenceMisc::getYesOrNo(count($networksAsDiscussant) > 0)
		));

		if ((SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) && (count($networksAsDiscussant) > 0)) {
			$chairDiscussantContent[] = theme('iishconference_container_field', array(
				'label' => 'Networks',
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
			array(theme('iishconference_container_header', array('text' => iish_t('English Language Coach'))));

		if ((SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) && (count($networksAsCoach) > 0)) {
			$languageFound = true;
			$languageContent[] = theme('iishconference_container_field', array(
				'label' => iish_t('I would like to be an English Language Coach in the following networks'),
				'value' => implode(', ', $networksAsCoach),
			));
		}
		else if (count($networksAsCoach) > 0) {
			$languageFound = true;
			$languageContent[] = theme('iishconference_container_field', array(
				'label' => iish_t('I would like to be an English Language Coach'),
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
							iish_t('No language coaches found in this network!') . '</em>';
					}
					else {
						$list[] = '<em>' . iish_t('No language coaches found!') . '</em>';
					}
				}
			}

			if (SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) {
				$languageCoachLabel = iish_t('I need some help from one of the following English Language Coaches ' .
					'in each chosen network');
			}
			else {
				$languageCoachLabel = iish_t('I need some help from one of the following English Language Coaches');
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
	$linksContent = array('<a name="links"></a>' . theme('iishconference_container_header', array('text' => iish_t('Links'))));

	// show pre registration link if not registered or participant state is 'not finished' or 'new participant'
	if (module_exists('iishconference_preregistration') &&
		(is_null($participantDateDetails) ||
			$participantDateDetails->getStateId() === ParticipantStateApi::DID_NOT_FINISH_REGISTRATION ||
			$participantDateDetails->getStateId() === ParticipantStateApi::NEW_PARTICIPANT)
	) {
		$linksContent[] =
			'&bull; ' . l(iish_t('Pre-registration form'),
				SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'pre-registration') . '<br />';
	}
	if (module_exists('iishconference_changepassword')) {
		$linksContent[] =
			'&bull; ' .
			l(iish_t('Change password'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'change-password') . '<br />';
	}
	if (module_exists('iishconference_finalregistration')) {
		$linksContent[] = '&bull; ' .
			l(iish_t('Final registration and payment'),
				SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'final-registration') . '<br />';
	}
	if (module_exists('iishconference_emails')) {
		$linksContent[] =
			'&bull; ' . l(iish_t('List of e-mails sent to you'),
				SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'emails') . '<br />';
	}
	if (module_exists('iishconference_logout')) {
		$linksContent[] =
			'&bull; ' . l(iish_t('Logout'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'logout') . '<br />';
	}
	// check if live or crew or network chair or chair or organizer
	if (module_exists('iishconference_programme') && ConferenceMisc::mayLoggedInUserSeeProgramme()) {
		$linksContent[] =
			'&bull; ' . l(iish_t('Preliminary Programme'),
				SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'programme') . '<br />';
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
			array('<a name="nclinks"></a>' . theme('iishconference_container_header', array('text' => iish_t('Links for network chairs'))));

		// names and email addresses
		if (module_exists('iishconference_networkparticipants')) {
			$linksNetworkContent[] = '&bull; ' . l(iish_t('Participant names and e-mail addresses'),
					SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . NetworkApi::getNetworkName(true, true) .
					'participants') . ' (xls)<br />';
		}
		// session paper proposals
		if (module_exists('iishconference_networksforchairs')) {
			$linksNetworkContent[] = '&bull; ' . l(iish_t('Participants and their papers'),
					SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . NetworkApi::getNetworkName(false, true) .
					'forchairs') . '<br />';
		}
		// session paper proposals xls (new and accepted participants)
		if (module_exists('iishconference_networksessionpapersxls')) {
			$linksNetworkContent[] = '&bull; ' . l(iish_t('Participants and their session paper proposals (new and accepted participants)'),
					SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . NetworkApi::getNetworkName(true, true) .
					'sessionpapersxls') . ' (xls)<br />';
		}
		// session paper proposals xls (only accepted participants)
		if (module_exists('iishconference_networksessionpapersacceptedxls')) {
			$linksNetworkContent[] = '&bull; ' . l(iish_t('Participants and their session paper proposals (only accepted participants)'),
					SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . NetworkApi::getNetworkName(true, true) .
					'sessionpapersacceptedxls') . ' (xls)<br />';
		}
		// individual paper proposals
//		if (module_exists('iishconference_proposednetworkparticipants')) {
//			$linksNetworkContent[] = '&bull; ' . l(iish_t('Participants and their individual paper proposals'),
//					SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'proposed' .
//					NetworkApi::getNetworkName(true, true) . 'participants') . '<br />';
//		}
		// individual paper proposals xls
		if (module_exists('iishconference_networkindividualpapersxls')) {
			$linksNetworkContent[] = '&bull; ' . l(iish_t('Participants and their individual paper proposals'),
					SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . NetworkApi::getNetworkName(true, true) .
					'individualpapersxls') . ' (xls)<br />';
		}
		// volunteers
		if (module_exists('iishconference_networkvolunteers')) {
			$linksNetworkContent[] = '&bull; ' . l(iish_t('Volunteers (Chair/Discussant)'),
					SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . NetworkApi::getNetworkName(true, true) .
					'volunteers') . '<br />';
		}
		// election advisory
		if (module_exists('iishconference_electionadvisory')) {
			$linksNetworkContent[] = '&bull; ' . l(iish_t('Election \'Advisory board\''),
					SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'election-advisory-board') . '<br />';
		}

		return theme('iishconference_container', array('fields' => $linksNetworkContent));
	}

	return '';
}
