<?php

/**
 * Creates the personal page
 */
function conference_personalpage_main() {
	if (!LoggedInUserDetails::isLoggedIn()) {
		// redirect to login page
		Header("Location: /" . getSetting('pathForMenu') . "login/?backurl=" . urlencode($_SERVER["REQUEST_URI"]));
		die('Go to <a href="/' . getSetting('pathForMenu') . 'login/?backurl=' . urlencode($_SERVER["REQUEST_URI"]) .
			'">login</a> page.');
	}

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +

	$userDetails = LoggedInUserDetails::getUser();
	$participantDateDetails = LoggedInUserDetails::getParticipant();

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +
	// PERSONAL INFO

	$personalInfoContent = array(theme('iishconference_container_header', array('text' => t('Personal Info'))));

	$personalInfoContent[] = theme('iishconference_container_field',
		array(
			'label' => 'First name',
			'value' => $userDetails->getFirstName()
		));
	$personalInfoContent[] = theme('iishconference_container_field',
		array(
			'label' => 'Last name',
			'value' => $userDetails->getLastName()
		));
	$personalInfoContent[] = theme('iishconference_container_field',
		array(
			'label' => 'Gender',
			'value' => ConferenceMisc::getGender($userDetails->getGender())
		));
	$personalInfoContent[] = theme('iishconference_container_field',
		array(
			'label' => 'Organisation',
			'value' => $userDetails->getOrganisation()
		));
	$personalInfoContent[] = theme('iishconference_container_field',
		array(
			'label' => 'Department',
			'value' => $userDetails->getDepartment()
		));
	$personalInfoContent[] = theme('iishconference_container_field',
		array(
			'label' => 'E-mail',
			'value' => $userDetails->getEmail()
		));

	if (LoggedInUserDetails::isAParticipant()) {
		$personalInfoContent[] = theme('iishconference_container_field',
			array(
				'label' => '(PhD) Student?',
				'value' => ConferenceMisc::getYesOrNo($participantDateDetails->getStudent())
			));
	}

	$personalInfoContent[] = theme('iishconference_container_field',
		array(
			'label' => 'City',
			'value' => $userDetails->getCity()
		));
	$personalInfoContent[] = theme('iishconference_container_field',
		array(
			'label' => 'Country',
			'value' => $userDetails->getCountry()->getNameEnglish()
		));
	$personalInfoContent[] = theme('iishconference_container_field',
		array(
			'label' => 'Phone number',
			'value' => $userDetails->getPhone()
		));
	$personalInfoContent[] = theme('iishconference_container_field',
		array(
			'label' => 'Mobile number',
			'value' => $userDetails->getMobile()
		));

	if (getSetting('show_cv') == 1) {
		$personalInfoContent[] = theme('iishconference_container_field',
			array(
				'label'          => 'Curriculum Vitae',
				'value'          => $userDetails->getCV(),
				'valueOnNewLine' => true
			));
	}

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +
	// REGISTERED AND/OR PAYED

	$registeredAndPayedContent = array();
	if (LoggedInUserDetails::isAParticipant()) {
		$registeredAndPayedContent[] = '<span class="eca_remark heavy">' .
			t('You have pre-registered for the @conference conference.',
				array('@conference' => getSetting('long_code_year'))) . '<br /></span>';

		$paymentInfo = t('Payment: none') . ' ' . t('(Final registration and payment has not started yet)');
		if (module_exists('iishconference_finalregistration')) {
			$paymentInfo = t('Payment: none') . ' ' . t('(!link)',
					array('!link' => l(t('Final registration and payment'),
						getSetting('pathForMenu') . 'final-registration')));

			if (!is_null($participantDateDetails->getPaymentId()) && ($participantDateDetails->getPaymentId() !== 0)) {
				$orderDetails = new PayWayMessage(array('orderid' => $participantDateDetails->getPaymentId()));
				$order = $orderDetails->send('orderDetails');

				if (!empty($order)) {
					if ($order->get('payed') == 1) {
						if ($order->get('willpaybybank')) {
							$paymentInfo = t('Payment: by bank transfer') . ' ' . t('(confirmed)');
						}
						else {
							$paymentInfo = t('Payment: by credit card/iDeal') . ' ' . t('(confirmed)');
						}
					}
					else if ($order->get('willpaybybank')) {
						$paymentInfo = t('Payment: by bank transfer') . ' ' . t('(not yet confirmed)');
					}
				}
				else {
					$paymentInfo = t('Payment information is currently unavailable');
				}
			}
		}

		$registeredAndPayedContent[] = $paymentInfo . '<br />';
	}
	else {
		$registeredAndPayedContent[] = '<span class="eca_warning">' .
			t('You are not registered for the @conference conference. Please go to !link.',
				array('@conference' => getSetting('long_code_year'), '!link' => l(t('Pre-registration form'),
					getSetting('pathForMenu') . 'pre-registration'))) . '</span>';
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

			foreach ($networks as $network) {
				$sessionContainer[] = theme('iishconference_container_field',
					array(
						'label' => 'Network name',
						'value' => $network->getName()
					));
				$sessionContainer[] = theme('iishconference_container_field',
					array(
						'label' => 'Network chairs',
						'value' => implode(', ', $network->getChairs())
					));
			}

			$sessionName = $session->getName() . ' <em>(' . $session->getState()->getDescription() . ')</em>';
			$sessionContainer[] = theme('iishconference_container_field',
				array(
					'label'       => 'Session name',
					'value'       => $sessionName,
					'valueIsHTML' => true
				));

			$plannedText = '<span class="eca_warning heavy">' . $planned->getDay()
					->getDayFormatted("l d F Y") . ' / ' . $planned->getDateTimePeriod() . ' / ' .
				$planned->getRoomName() . '</span>';
			$sessionContainer[] = theme('iishconference_container_field',
				array(
					'label'       => 'Session Date / Time / Room',
					'value'       => $plannedText,
					'valueIsHTML' => true
				));

			$submittedBy = (is_object($session->getAddedBy())) ? $session->getAddedBy()->getFullName() : null;
			$sessionContainer[] = theme('iishconference_container_field',
				array(
					'label' => 'Session submitted by',
					'value' => $submittedBy
				));

			$sessionContainer[] = theme('iishconference_container_field',
				array(
					'label' => 'Your function in session',
					'value' => implode(', ', $functionsInSession)
				));
			$sessionContainer[] = theme('iishconference_container_field',
				array(
					'label'          => 'Session abstract',
					'value'          => $session->getAbstr(),
					'valueOnNewLine' => true
				));

			// show paper info
			if (count($sessionPapers) > 0) {
				foreach ($sessionPapers as $paper) {
					$sessionContainer[] = '<br /><br />';
					conference_personalpage_paper($sessionContainer, $paper, $participantDateDetails);
				}
			}
			else {
				$sessionContainer[] = '<br /><br />';
				$sessionContainer[] = theme('iishconference_container_header', array('text' => t('Paper')));
				$sessionContainer[] = t('No paper.');
			}

			$sessionsContainers[] = $sessionContainer;
		}

		// END SESSIONS
		// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +
		// LOSSE PAPERS

		// show paper info
		$noSessionPapers = PaperApi::getPapersWithoutSession($papers);
		foreach ($noSessionPapers as $paper) {
			conference_personalpage_paper($papersContainer, $paper, $participantDateDetails);
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
			getSetting('volunteering_chair'));
		$networksAsDiscussant = ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering,
			getSetting('volunteering_discussant'));

		$chairDiscussantContainer =
			array(theme('iishconference_container_header', array('text' => t('Chair / Discussant pool'))));
		$chairDiscussantContainer[] = theme('iishconference_container_field',
			array(
				'label' => 'I would like to volunteer as Chair?',
				'value' => ConferenceMisc::getYesOrNo(count($networksAsChair) > 0)
			));

		if ((getSetting('show_network') == 1) && (count($networksAsChair) > 0)) {
			$chairDiscussantContainer[] = theme('iishconference_container_field',
				array(
					'label' => 'Network(s)',
					'value' => implode(', ', $networksAsChair)
				));
		}

		$chairDiscussantContainer[] = theme('iishconference_container_field',
			array(
				'label' => 'I would like to volunteer as Discussant?',
				'value' => ConferenceMisc::getYesOrNo(count($networksAsDiscussant) > 0)
			));

		if ((getSetting('show_network') == 1) && (count($networksAsDiscussant) > 0)) {
			$chairDiscussantContainer[] = theme('iishconference_container_field',
				array(
					'label' => 'Network(s)',
					'value' => implode(', ', $networksAsDiscussant)
				));
		}

		$volunteeringContainers[] = $chairDiscussantContainer;

		// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +
		// ENGLISH LANGUAGE / COACH POOL

		if (getSetting('show_languagecoachpupil') == 1) {
			$networksAsCoach = ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering,
				getSetting('volunteering_languagecoach'));
			$networksAsPupil = ParticipantVolunteeringApi::getAllNetworksForVolunteering($allVolunteering,
				getSetting('volunteering_languagepupil'));

			$languageFound = false;
			$languageContainer =
				array(theme('iishconference_container_header', array('text' => t('English Language Coach'))));

			if (count($networksAsCoach) > 0) {
				$languageFound = true;
				$languageContainer[] = theme('iishconference_container_field',
					array(
						'label' => ConferenceMisc::getLanguageCoachPupil('coach')
					));
				$languageContainer[] = theme('iishconference_container_field',
					array(
						'label' => 'Network(s)',
						'value' => implode(', ', $networksAsCoach)
					));
			}

			if (count($networksAsPupil) > 0) {
				$languageFound = true;
				$languageContainer[] =
					theme('iishconference_container_field',
						array('label' => ConferenceMisc::getLanguageCoachPupil('pupil')));
				$languageContainer[] = theme('iishconference_container_field',
					array(
						'label' => 'Network(s)',
						'value' => implode(', ', $networksAsPupil)
					));
			}

			if ($languageFound) {
				$languageContainer[] =
					theme('iishconference_container_field',
						array('label' => ConferenceMisc::getLanguageCoachPupil((''))));
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
			'&bull; ' . l(t('Pre-registration form'), getSetting('pathForMenu') . 'pre-registration') . '<br />';
	}
	if (module_exists('iishconference_changepassword')) {
		$linksContainer[] =
			'&bull; ' . l(t('Change password'), getSetting('pathForMenu') . 'change-password') . '<br />';
	}
	if (module_exists('iishconference_finalregistration')) {
		$linksContainer[] = '&bull; ' .
			l(t('Final registration and payment'), getSetting('pathForMenu') . 'final-registration') . '<br />';
	}
	if (module_exists('iishconference_emails')) {
		$linksContainer[] =
			'&bull; ' . l(t('List of e-mails sent to you'), getSetting('pathForMenu') . 'emails') . '<br />';
	}
	if (module_exists('iishconference_logout')) {
		$linksContainer[] = '&bull; ' . l(t('Logout'), getSetting('pathForMenu') . 'logout') . '<br />';
	}
	// check if live or crew or network chair or chair or organizer
	if (module_exists('iishconference_program') &&
		(getSetting('onlineprogram_live') == 1 ||
			LoggedInUserDetails::hasFullRights() ||
			LoggedInUserDetails::isNetworkChair() ||
			LoggedInUserDetails::isChair() ||
			LoggedInUserDetails::isOrganiser())
	) {
		$linksContainer[] =
			'&bull; ' . l(getSetting('onlineprogram_header'), getSetting('pathForMenu') . 'program') . '<br />';
	}

	// + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + +
	// NETWORK CHAIR LINKS

	$linksNetworkContainer =
		array(theme('iishconference_container_header', array('text' => t('Links for Network Chairs'))));

	if (LoggedInUserDetails::hasFullRights() || LoggedInUserDetails::isNetworkChair()) {
		if (module_exists('iishconference_networksforchairs')) {
			$linksNetworkContainer[] = '&bull; ' . l(t('Networks, Sessions & Participants (and papers)'),
					getSetting('pathForMenu') . 'networksforchairs') . '<br />';
		}
		if (module_exists('iishconference_networkparticipants')) {
			$linksNetworkContainer[] = '&bull; ' .
				l(t('Networks and their Participants'), getSetting('pathForMenu') . 'networkparticipants') . '<br />';
		}
		if (module_exists('iishconference_networkvolunteers')) {
			$linksNetworkContainer[] = '&bull; ' . l(t('Networks and their Volunteers (Chair/Discussant)'),
					getSetting('pathForMenu') . 'networkvolunteers') . '<br />';
		}
		if (module_exists('iishconference_proposednetworkparticipants')) {
			$linksNetworkContainer[] = '&bull; ' . l(t('Participants and their proposed networks'),
					getSetting('pathForMenu') . 'proposednetworkparticipants') . '<br />';
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
			if (count($sessionContainer) > 0) {
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
	$container[] = theme('iishconference_container_header', array('text' => t('Paper')));

	$container[] = theme('iishconference_container_field', array('label' => 'Title', 'value' => $paper->getTitle()));
	$container[] = theme('iishconference_container_field',
		array(
			'label' => 'Paper state',
			'value' => $paper->getState()->getDescription()
		));
	$container[] = theme('iishconference_container_field',
		array(
			'label'          => 'Abstract',
			'value'          => $paper->getAbstr(),
			'valueOnNewLine' => true
		));
	$container[] = theme('iishconference_container_field',
		array(
			'label' => 'Co-author(s)',
			'value' => $paper->getCoAuthors()
		));

	if ((getSetting('show_award') == 1) && $participant->getStudent()) {
		$awardText = ConferenceMisc::getYesOrNo($participant->getAward());
		$awardText .= '&nbsp; <em>(' . l(t('more about the award'), 'award') . ')</em>';
		$container[] = theme('iishconference_container_field',
			array(
				'label'       => getSetting('award_name') . ' award?',
				'value'       => $awardText,
				'valueIsHTML' => true
			));
	}

	$container[] = theme('iishconference_container_field',
		array(
			'label' => 'Audio/visual equipment',
			'value' => implode(', ', $paper->getEquipment())
		));
	$container[] = theme('iishconference_container_field',
		array(
			'label' => 'Extra audio/visual request',
			'value' => $paper->getEquipmentComment()
		));

	$container[] =
		'<br /><b> ' .
		l(t('Upload paper'), getSetting('pathForMenu') . 'personal-page/upload-paper/' . $paper->getId()) . '</b>';
}