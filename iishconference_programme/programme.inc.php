<?php

/**
 * Prints the programme
 *
 * @param string $yearCode The event date for which to print the programme
 *
 * @return string The HTML for the programme
 */
function iishconference_programme($yearCode = null) {
	$eventDate = iishconference_programme_get_event_date($yearCode);
	if ($eventDate === null) {
		drupal_set_message(iish_t('No programme available for the given year!'), 'error');
		drupal_goto(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'programme');

		return;
	}

	// If the programme of the last event date is still closed, show message
	if ($eventDate->isLastDate() && !ConferenceMisc::mayLoggedInUserSeeProgramme()) {
		drupal_set_message(iish_t('The programme is not yet available.'), 'warning');

		return '';
	}

	// If the programme of the last event date is still under construction AND there is an under construction message, show message
	$underConstruction = SettingsApi::getSetting(SettingsApi::ONLINE_PROGRAM_UNDER_CONSTRUCTION);
	if ($eventDate->isLastDate() && ($underConstruction != '')) {
		drupal_set_message($underConstruction, 'warning');
	}

	// Make sure the settings are already cached, before changing the year code
	CachedConferenceApi::getSettings();
	ConferenceApiClient::setYearCode($eventDate->getYearCodeURL());
	$queryParameters = drupal_get_query_parameters();

	// Obtain all necessary query parameters
	$dayId = isset($queryParameters['day']) ? EasyProtection::easyIntegerProtection($queryParameters['day']) : null;
	$timeId = isset($queryParameters['time']) ?
		EasyProtection::easyIntegerProtection($queryParameters['time']) : null;
	$roomId = isset($queryParameters['room']) ? EasyProtection::easyIntegerProtection($queryParameters['room']) : null;
	$networkId =
		isset($queryParameters['network']) ? EasyProtection::easyIntegerProtection($queryParameters['network']) : null;
	$sessionId =
		isset($queryParameters['session']) ? EasyProtection::easyIntegerProtection($queryParameters['session']) : null;
    $participantId = (isset($queryParameters['favorites']) && LoggedInUserDetails::isLoggedIn())
        ? LoggedInUserDetails::getParticipant()->getId() : null;
    $textsearch =
        isset($queryParameters['textsearch']) ? EasyProtection::easyStringProtection($queryParameters['textsearch']) :
            null;

	// Make sure the query parameters representing ids are integers and empty strings are null
	$dayId = (is_int($dayId)) ? $dayId : null; // An id of 0 is allowed, means all days
	$timeId = (is_int($timeId) && ($timeId !== 0)) ? $timeId : null;
	$roomId = (is_int($roomId) && ($roomId !== 0)) ? $roomId : null;
	$networkId = (is_int($networkId) && ($networkId !== 0)) ? $networkId : null;
	$sessionId = (is_int($sessionId) && ($sessionId !== 0)) ? $sessionId : null;
	$textsearch = (!is_null($textsearch) && (strlen($textsearch) > 0)) ? urldecode($textsearch) : null;

	$props = new ApiCriteriaBuilder();
	$days = DayApi::getListWithCriteria($props->get())->getResults();
	$networks = NetworkApi::getListWithCriteria($props->get())->getResults();
	$rooms = RoomApi::getListWithCriteria($props->get())->getResults();
	$dateTimes = SessionDateTimeApi::getListWithCriteria($props->get())->getResults();
	$types = ParticipantTypeApi::getListWithCriteria($props->get())->getResults();

	// Make sure we filter out co-authors and types with papers and types configured to be hidden
	$alwaysHide = SettingsApi::getSetting(SettingsApi::HIDE_ALWAYS_IN_ONLINE_PROGRAMME);
	$typesToHide = SettingsApi::getArrayOfValues($alwaysHide);
	foreach ($types as $i => $type) {
		if (($type->getId() == ParticipantTypeApi::CO_AUTHOR_ID) ||
			$type->getWithPaper() ||
			(array_search($type->getId(), $typesToHide) !== false)
		) {
			unset($types[$i]);
		}
	}
	$types = array_values($types);

	// What time slot is showing?
	$showing = '';
	$showingTimeSlot = iish_t('all days');

	// if network id, room id or text search is not empty, then show all days
	if (!is_null($networkId) || !is_null($roomId) || !is_null($participantId) || !is_null($textsearch)) {
		$dayId = 0; // all days
		$timeId = null;

		if (!is_null($networkId)) {
			foreach ($networks as $network) {
				if ($network->getId() === $networkId) {
					$showing = $network->getName();
				}
			}

			$roomId = null;
            $participantId = null;
			$textsearch = null;
		}
		else if (!is_null($roomId)) {
			foreach ($rooms as $room) {
				if ($room->getId() === $roomId) {
					$showing = iish_t('room') . ' ' . $room->getRoomNumber();
				}
			}

			$networkId = null;
            $participantId = null;
			$textsearch = null;
		}
        else if (!is_null($participantId)) {
            $showing = iish_t('Favourite sessions');
            $showingTimeSlot = '';

            $networkId = null;
            $roomId = null;
            $textsearch = null;
        }
        else if (!is_null($textsearch)) {
            $showing = iish_t('text search') . ': ' . $textsearch;

            $networkId = null;
            $roomId = null;
            $participantId = null;
        }
	}
	else {
		$showing = iish_t('all days');
		$showingTimeSlot = '';
	}

	// if dayId is empty, only first date, else all dates
	if (is_null($dayId)) {
		$dayId = $days[0]->getId(); // find first date
		$showing = $days[0]->getDayFormatted("l j F Y");
		$showingTimeSlot = iish_t('entire day');
	}
	else if ($dayId === 0) {
		$dayId = null;
	}
	else {
		foreach ($days as $day) {
			if ($day->getId() === $dayId) {
				$showing = $day->getDayFormatted("l j F Y");
				$showingTimeSlot = iish_t('entire day');
			}
		}
	}

	if (!is_null($timeId)) {
		foreach ($dateTimes as $dateTime) {
			if ($dateTime->getId() === $timeId) {
				$showing .= ' ' . $dateTime->getPeriod(true);
				$showingTimeSlot = iish_t('single time slot');
			}
		}
	}

	$curShowing = iish_t('Showing') . ': ' . $showing;
	$curShowing .= (strlen($showingTimeSlot) > 0) ? ' (' . $showingTimeSlot . ')' : '';

	// Create the query part for the back URL
	if (!is_null($textsearch)) {
		$backUrl = '?textsearch=' . urlencode($textsearch);
	}
    else if (!is_null($participantId)) {
        $backUrl = '?favorites=yes';
    }
	else if (!is_null($roomId)) {
		$backUrl = '?room=' . $roomId;
	}
	else if (!is_null($networkId)) {
		$backUrl = '?network=' . $networkId;
	}
	else {
		$backUrl = "?day=" . $dayId . "&time=" . $timeId;
	}

	$form = drupal_get_form('iishconference_programme_form', $networks, $networkId, $textsearch);

	$highlight = new Highlighter(explode(' ', $textsearch));
	$highlight->setOpeningTag('<span class="highlight">');
	$highlight->setClosingTag('</span>');

    $programmeApi = new ProgrammeApi();
    $programme = null;

    if (is_null($sessionId)) {
        $programme = $programmeApi->getProgramme($dayId, $timeId, $networkId, $roomId, $participantId, $textsearch);
    }
    else {
        $programme = $programmeApi->getProgrammeForSession($sessionId);
    }

	$paperDownloadLinkStart = variable_get('conference_base_url') . variable_get('conference_event_code') . '/' .
		variable_get('conference_date_code') . '/' . 'userApi/downloadPaper/';

	$downloadPaperLastDate = strtotime(SettingsApi::getSetting(SettingsApi::DOWNLOAD_PAPER_LASTDATE));
	$downloadPaperIsOpen = ConferenceMisc::isOpenForLastDate($downloadPaperLastDate);

    $participant = LoggedInUserDetails::getParticipant();
    $favoriteSessions = ($participant !== null) ? $participant->getFavoriteSessionsId() : array();

	return theme('iishconference_programme', array(
		'eventDate'              => $eventDate,
		'form'                   => $form,
		'days'                   => $days,
		'date-times'             => $dateTimes,
		'types'                  => $types,
		'programme'              => $programme,
		'back-url-query'         => $backUrl,
		'highlight'              => $highlight,
		'networkId'              => $networkId,
		'roomId'                 => $roomId,
		'sessionId'              => $sessionId,
		'textsearch'             => $textsearch,
		'curShowing'             => $curShowing,
		'downloadPaperIsOpen'    => $downloadPaperIsOpen,
		'paperDownloadLinkStart' => $paperDownloadLinkStart,
        'favoriteSessions'       => $favoriteSessions,
	));
}

/**
 * TODOEXPLAIN
 */
function iishconference_programme_form($form, &$form_state, $networks, $networkId, $textsearch) {
	$form['#method'] = 'get';
	$form['#token'] = false;
	$form['#after_build'] = array('iishconference_programme_unset_default_form_elements');

	// create a list of select options
	// also add empty option
	$titleTextSearch = iish_t('Search on name');
	if (SettingsApi::getSetting(SettingsApi::SHOW_NETWORK) == 1) {
		$titleTextSearch = iish_t('or search on name');

		$selectListOfNetworks = array();
		$selectListOfNetworks[0] = '';
		foreach ($networks as $network) {
			$selectListOfNetworks[$network->getId()] = $network->getName();
		}

		$form['network'] = array(
			'#type'          => 'select',
			'#title'         => iish_t('Browse networks') . ': ',
			'#size'          => 1,
			'#default_value' => is_null($networkId) ? 0 : $networkId,
			'#options'       => $selectListOfNetworks,
		);
	}

	$form['textsearch'] = array(
		'#type'          => 'textfield',
		'#title'         => $titleTextSearch,
		'#size'          => 20,
		'#maxlength'     => 50,
		'#default_value' => is_null($textsearch) ? '' : $textsearch,
	);

	$form['btnSubmit'] = array(
		'#type'  => 'submit',
		'#value' => iish_t('Go'),
	);

	return $form;
}

/**
 * TODOEXPLAIN
 */
function iishconference_programme_unset_default_form_elements($form) {
	unset($form['#build_id'], $form['form_build_id'], $form['form_id'], $form['btnSubmit']['#name']);

	return $form;
}

/**
 * Returns the event date that belongs to the year code, if given
 *
 * @param string|null $yearCode The year code
 */
function iishconference_programme_get_event_date($yearCode) {
	$eventDate = null;
	if (!empty($yearCode)) {
		foreach (CachedConferenceApi::getEventDates() as $date) {
			if (strtolower($date->getYearCodeURL()) == strtolower(trim($yearCode))) {
				$eventDate = $date;
			}
		}
	}
	else {
		$eventDate = CachedConferenceApi::getEventDate();
	}

	return $eventDate;
}
