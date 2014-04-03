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

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

	$userDetails = LoggedInUserDetails::getUser();
	$participantDateDetails = LoggedInUserDetails::getParticipant();

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +
	// PERSONAL INFO

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
			'value'          => $userDetails->getCv(),
			'valueOnNewLine' => true
		));
	}

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +
	// REGISTERED AND/OR PAYED

	$registeredAndPayedContent = array();
	if (LoggedInUserDetails::isAParticipant()) {
		$registeredAndPayedContent[] = '<span class="eca_remark heavy">' .
			t('You have pre-registered for the @conference.',
				array('@conference' => CachedConferenceApi::getEventDate()->getLongNameAndYear())) . '</span>';

		$registeredAndPayedContent[] = '<br />';

		$registeredAndPayedContent[] = theme('iishconference_container_field', array(
			'label' => 'Currently selected fee',
			'value' => $participantDateDetails->getFeeState()
		));

		$paymentMethod = t('Payment: none');
		$paymentStatus = t('(Final registration and payment has not started yet)');

		if (module_exists('iishconference_finalregistration')) {
			$paymentStatus = t('(!link)', array('!link' => l(t('Final registration and payment'),
				SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'final-registration')));

			if (!is_null($participantDateDetails->getPaymentId()) && ($participantDateDetails->getPaymentId() !== 0)) {
				$orderDetails = new PayWayMessage(array('orderid' => $participantDateDetails->getPaymentId()));
				$order = $orderDetails->send('orderDetails');

				if (!empty($order)) {
					if ($order->get('willpaybybank')) {
						$paymentMethod = t('Payment: by bank transfer');
					}
					else {
						$paymentMethod = t('Payment: online payment');
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
	else {
		$registeredAndPayedContent[] = '<span class="eca_warning">' .
			t('You are not registered for the @conference. Please go to the !link.',
				array('@conference' => CachedConferenceApi::getEventDate()->getLongNameAndYear(),
				      '!link'       => l(t('pre-registration form'),
					      SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'pre-registration'))) . '</span>';
	}

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +
	// SESSIONS

	$sessionsContainers = array();
	$papersContainer = array();
	if (LoggedInUserDetails::isAParticipant()) {
		$sessions = SessionParticipantApi::getAllSessions($userDetails->getSessionParticipantInfo());
		$papers =
			CRUDApiMisc::getAllWherePropertyEquals(new PaperApi(), 'user_id', $userDetails->getId())->getResults();

		foreach ($sessions as $i => $session) {
			$header = t('Session @count of @total', array('@count' => $i + 1, '@total' => count($sessions)));
			$sessionContainer = array(theme('iishconference_container_header', array('text' => $header)));

			$networks = $session->getNetworks();
			$planned =
				CRUDApiMisc::getFirstWherePropertyEquals(new SessionRoomDateTimeApi(), 'session_id', $session->getId());
			$functionsInSession = SessionParticipantApi::getAllTypesOfUserForSession(
				$userDetails->getSessionParticipantInfo(),
				$userDetails->getId(),
				$session->getId()
			);
			$sessionPapers = PaperApi::getPapersWithSession($papers, $session->getId());

			if (SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) {
				foreach ($networks as $network) {
					$sessionContainer[] = theme('iishconference_container_field', array(
						'label' => t('@network name', array('@network' => NetworkApi::getNetworkName())),
						'value' => $network->getName()
					));
					$sessionContainer[] = theme('iishconference_container_field', array(
						'label' => t('Chairs of this @network',
							array('@network' => NetworkApi::getNetworkName(true, true))),
						'value' => implode(', ', $network->getChairs())
					));
					$sessionContainer[] = '<br />';
				}
			}

			$sessionName = $session->getName() . ' <em>(' . $session->getState()->getDescription() . ')</em>';
			$sessionContainer[] = theme('iishconference_container_field', array(
				'label'       => 'Session name',
				'value'       => $sessionName,
				'valueIsHTML' => true
			));

			if ($planned !== null) {
				$plannedText = '<span class="eca_warning heavy">' . $planned->getDay()
						->getDayFormatted("l d F Y") . ' / ' . $planned->getDateTimePeriod() . ' / ' .
					$planned->getRoomName() . '</span>';
				$sessionContainer[] = theme('iishconference_container_field', array(
					'label'       => 'Session Date / Time / Room',
					'value'       => $plannedText,
					'valueIsHTML' => true
				));
			}

			$submittedBy = (is_object($session->getAddedBy())) ? $session->getAddedBy()->getFullName() : null;
			$sessionContainer[] = theme('iishconference_container_field', array(
				'label' => 'Session submitted by',
				'value' => $submittedBy
			));

			$sessionContainer[] = theme('iishconference_container_field', array(
				'label' => 'Your function in session',
				'value' => implode(', ', $functionsInSession)
			));
			$sessionContainer[] = theme('iishconference_container_field', array(
				'label'          => 'Session abstract',
				'value'          => $session->getAbstr(),
				'valueOnNewLine' => true
			));

			// show paper info
			if (count($sessionPapers) > 0) {
				foreach ($sessionPapers as $paper) {
					$sessionContainer[] = '<br />';
					$sessionContainer[] = theme('iishconference_container_header', array('text' => t('Paper')));
					conference_personalpage_paper($sessionContainer, $paper, $participantDateDetails);
				}
			}
			else {
				$sessionContainer[] = '<br />';
				$sessionContainer[] = theme('iishconference_container_header', array('text' => t('Paper')));
				$sessionContainer[] = t('No paper.');
			}

			$sessionsContainers[] = $sessionContainer;
		}

		// END SESSIONS
		// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +
		// PAPERS WITHOUT SESSION

		// show paper info
		$noSessionPapers = PaperApi::getPapersWithoutSession($papers);
		foreach ($noSessionPapers as $i => $paper) {
			$paperContainer = array(theme('iishconference_container_header',
				array('text' => t('Paper  @count of @total',
					array('@count' => $i + 1, '@total' => count($noSessionPapers))))));
			conference_personalpage_paper($paperContainer, $paper, $participantDateDetails);
			$papersContainer[] = $paperContainer;
		}
	}

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +
	// ALL VOLUNTEERING OF THIS PARTICIPANT

	$volunteeringContainers = array();
	if (LoggedInUserDetails::isAParticipant()) {
		$allVolunteering =
			CRUDApiMisc::getAllWherePropertyEquals(new ParticipantVolunteeringApi(),
				'participantDate_id',
				$participantDateDetails->getId())->getResults();

		// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +
		// CHAIR / DISCUSSANT POOL

		$networksAsChair = ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering,
			VolunteeringApi::CHAIR);
		$networksAsDiscussant = ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering,
			VolunteeringApi::DISCUSSANT);

		CRUDApiClient::sort($networksAsChair);
		CRUDApiClient::sort($networksAsDiscussant);

		$chairDiscussantContainer =
			array(theme('iishconference_container_header', array('text' => t('Chair / Discussant pool'))));
		$chairDiscussantContainer[] = theme('iishconference_container_field', array(
			'label' => 'I would like to volunteer as Chair?',
			'value' => ConferenceMisc::getYesOrNo(count($networksAsChair) > 0)
		));

		if ((SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) && (count($networksAsChair) > 0)) {
			$chairDiscussantContainer[] = theme('iishconference_container_field', array(
				'label' => NetworkApi::getNetworkName(false),
				'value' => implode(', ', $networksAsChair)
			));
		}

		$chairDiscussantContainer[] = theme('iishconference_container_field', array(
			'label' => 'I would like to volunteer as Discussant?',
			'value' => ConferenceMisc::getYesOrNo(count($networksAsDiscussant) > 0)
		));

		if ((SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) && (count($networksAsDiscussant) > 0)) {
			$chairDiscussantContainer[] = theme('iishconference_container_field', array(
				'label' => NetworkApi::getNetworkName(false),
				'value' => implode(', ', $networksAsDiscussant)
			));
		}

		$volunteeringContainers[] = $chairDiscussantContainer;

		// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +
		// ENGLISH LANGUAGE / COACH POOL

		if (SettingsApi::getSetting(SettingsApi::SHOW_LANGUAGE_COACH_PUPIL) == 1) {
			$networksAsCoach = ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering,
				VolunteeringApi::COACH);
			$networksAsPupil = ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering,
				VolunteeringApi::PUPIL);

			CRUDApiClient::sort($networksAsCoach);
			CRUDApiClient::sort($networksAsPupil);

			$languageFound = false;
			$languageContainer =
				array(theme('iishconference_container_header', array('text' => t('English Language Coach'))));

			if (count($networksAsCoach) > 0) {
				$languageFound = true;
				$languageContainer[] = theme('iishconference_container_field', array(
					'label' => t('I would like to be an English Language Coach in the following @networks:',
						array('@networks' => NetworkApi::getNetworkName(false, true))),
					'value' => implode(', ', $networksAsCoach),
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
						$list[] =
							'<strong>' . $network->getName() . '</strong>: <em>' .
							t('No language coaches found in this @network!',
								array('@network' => NetworkApi::getNetworkName(true, true))) . '</em>';
					}
				}

				$languageContainer[] = theme('iishconference_container_field', array(
					'label'          => t('I need some help from one of the following English Language Coaches in each chosen @network:',
						array('@network' => NetworkApi::getNetworkName(true, true))),
					'value'          => str_replace("\n", '', theme('item_list', array('items' => $list))),
					'valueIsHTML'    => true,
					'valueOnNewLine' => true
				));
			}

			if (!$languageFound) {
				$languageContainer[] = theme('iishconference_container_field',
					array('label' => ConferenceMisc::getLanguageCoachPupil('')));
			}

			$volunteeringContainers[] = $languageContainer;
		}
	}

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +
	// LINKS

	$linksContainer = array(theme('iishconference_container_header', array('text' => t('Links'))));

	// show pre registration link if not registered or participant state is 'not finished' or 'new participant'
	if (module_exists('iishconference_preregistration') &&
		(is_null($participantDateDetails) ||
			$participantDateDetails->getStateId() === ParticipantStateApi::DID_NOT_FINISH_REGISTRATION ||
			$participantDateDetails->getStateId() === ParticipantStateApi::NEW_PARTICIPANT)
	) {
		$linksContainer[] =
			'&bull; ' . l(t('Pre-registration form'),
				SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'pre-registration') . '<br />';
	}
	if (module_exists('iishconference_changepassword')) {
		$linksContainer[] =
			'&bull; ' .
			l(t('Change password'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'change-password') . '<br />';
	}
	if (module_exists('iishconference_finalregistration')) {
		$linksContainer[] = '&bull; ' .
			l(t('Final registration and payment'),
				SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'final-registration') . '<br />';
	}
	if (module_exists('iishconference_emails')) {
		$linksContainer[] =
			'&bull; ' . l(t('List of e-mails sent to you'),
				SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'emails') . '<br />';
	}
	if (module_exists('iishconference_logout')) {
		$linksContainer[] =
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
		$linksContainer[] =
			'&bull; ' . l(SettingsApi::getSetting(SettingsApi::ONLINE_PROGRAM_HEADER),
				SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'program') . '<br />';
	}

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +
	// NETWORK CHAIR LINKS

	$linksNetworkContainer =
		array(theme('iishconference_container_header',
			array('text' => t('Links for chairs of a @network',
				array('@network' => NetworkApi::getNetworkName(true, true))))));

	if (LoggedInUserDetails::hasFullRights() || LoggedInUserDetails::isNetworkChair()) {
		if (module_exists('iishconference_networksforchairs')) {
			$linksNetworkContainer[] = '&bull; ' . l(t('@networks, Sessions & Participants (and papers)',
						array('@networks' => NetworkApi::getNetworkName(false))),
					SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . NetworkApi::getNetworkName(false, true) .
					'forchairs') . '<br />';
		}
		if (module_exists('iishconference_networkparticipants')) {
			$linksNetworkContainer[] = '&bull; ' .
				l(t('@networks and their Participants', array('@networks' => NetworkApi::getNetworkName(false))),
					SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . NetworkApi::getNetworkName(true, true) .
					'participants') . '<br />';
		}
		if (module_exists('iishconference_networkvolunteers')) {
			$linksNetworkContainer[] = '&bull; ' . l(t('@networks and their Volunteers (Chair/Discussant)',
						array('@networks' => NetworkApi::getNetworkName(false))),
					SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . NetworkApi::getNetworkName(true, true) .
					'volunteers') . '<br />';
		}
		if (module_exists('iishconference_proposednetworkparticipants')) {
			$linksNetworkContainer[] = '&bull; ' . l(t('Participants and their proposed @networks',
						array('@networks' => NetworkApi::getNetworkName(false, true))),
					SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'proposed' .
					NetworkApi::getNetworkName(true, true) . 'participants') . '<br />';
		}
		if (module_exists('iishconference_electionadvisory')) {
			$linksNetworkContainer[] = '&bull; ' . l(t('Election \'Advisory board\''),
					SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'election-advisory-board') . '<br />';
		}
	}

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

	$personalPage = theme('iishconference_container', array('fields' => $personalInfoContent));
	$personalPage .= theme('iishconference_container', array('fields' => $registeredAndPayedContent));

	if (LoggedInUserDetails::isAParticipant()) {
		foreach ($sessionsContainers as $sessionContainer) {
			if (count($sessionContainer) > 0) {
				$personalPage .= theme('iishconference_container', array('fields' => $sessionContainer));
			}
		}
		foreach ($papersContainer as $paperContainer) {
			if (count($paperContainer) > 0) {
				$personalPage .= theme('iishconference_container', array('fields' => $paperContainer));
			}
		}
		foreach ($volunteeringContainers as $volunteeringContainer) {
			$personalPage .= theme('iishconference_container', array('fields' => $volunteeringContainer));
		}
	}

	$personalPage .= theme('iishconference_container', array('fields' => $linksContainer));
	if (LoggedInUserDetails::hasFullRights() || LoggedInUserDetails::isNetworkChair()) {
		$personalPage .= theme('iishconference_container', array('fields' => $linksNetworkContainer));
	}

	return $personalPage;
}

/**
 * Adds paper information to an information container
 *
 * @param array              $container   The container to add paper information into
 * @param PaperApi           $paper       The paper in question
 * @param ParticipantDateApi $participant The participant of this paper
 */
function conference_personalpage_paper(&$container, $paper, $participant) {
	$container[] = theme('iishconference_container_field', array(
			'label' => 'Title',
			'value' => $paper->getTitle())
	);
	$container[] = theme('iishconference_container_field', array(
		'label' => 'Paper state',
		'value' => $paper->getState()->getDescription()
	));
	$container[] = theme('iishconference_container_field', array(
		'label'          => 'Abstract',
		'value'          => $paper->getAbstr(),
		'valueOnNewLine' => true
	));
	$container[] = theme('iishconference_container_field', array(
		'label' => 'Co-author(s)',
		'value' => $paper->getCoAuthors()
	));

	if ((SettingsApi::getSetting(SettingsApi::SHOW_AWARD) == 1) && $participant->getStudent()) {
		$awardText = ConferenceMisc::getYesOrNo($participant->getAward());
		$awardText .= '&nbsp; <em>(' . l(t('more about the award'), 'award') . ')</em>';
		$container[] = theme('iishconference_container_field', array(
			'label'       => SettingsApi::getSetting(SettingsApi::AWARD_NAME) . '?',
			'value'       => $awardText,
			'valueIsHTML' => true
		));
	}

	$container[] = theme('iishconference_container_field', array(
		'label' => 'Audio/visual equipment',
		'value' => implode(', ', $paper->getEquipment())
	));
	$container[] = theme('iishconference_container_field', array(
		'label' => 'Extra audio/visual request',
		'value' => $paper->getEquipmentComment()
	));

	$container[] = '<br /><span class="heavy"> ' .
		l(t('Upload paper'), SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'personal-page/upload-paper/' .
			$paper->getId()) . '</span>';
}