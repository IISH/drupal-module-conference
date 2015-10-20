<?php

/**
 * API for obtaining the current programme
 */
class ProgrammeApi {
	private $client;
	private static $apiName = 'programme';

	public function __construct() {
		$this->client = new ConferenceApiClient();
	}

	/**
	 * Retrieves the programme with the provided filters
	 *
	 * @param int|null    $dayId     Only return the programme for the day with the specified id
	 * @param int|null    $timeId    Only return the programme for the time slot with the specified id
	 * @param int|null    $networkId Only return the programme for sessions in the network with the specified id
	 * @param int|null    $roomId    Only return the programme for sessions in the room with the specified id
	 * @param int|null    $sessionId Only return the programme for a particular session with the specified id
	 * @param string|null $terms     Only return the programme for sessions that contain one or more of the specified terms
	 *                               (Terms are separated by a space)
	 *
	 * @return array|null Returns the programme
	 */
	public function getProgramme($dayId = null, $timeId = null, $networkId = null, $roomId = null, $sessionId = null,
								 $terms = null) {
		$params = array();
		if (is_int($dayId)) {
			$params['dayId'] = $dayId;
		}
		if (is_int($timeId)) {
			$params['timeId'] = $timeId;
		}
		if (is_int($networkId)) {
			$params['networkId'] = $networkId;
		}
		if (is_int($roomId)) {
			$params['roomId'] = $roomId;
		}
		if (is_int($sessionId)) {
			$params['sessionId'] = $sessionId;
		}
		if (!is_null($terms) && (strlen(trim($terms)) > 0)) {
			$params['terms'] = trim($terms);
		}

		return $this->client->get(self::$apiName, $params);
	}

	/**
	 * Retrieves the programme for sessions in the network with the specified id
	 *
	 * @param int|null $networkId The network id to filter on
	 *
	 * @return array|null Returns the programme
	 */
	public function getProgrammeForNetwork($networkId) {
		return $this->getProgramme(null, null, $networkId);
	}

	/**
	 * Retrieves the programme for the specified day and/or time slot
	 *
	 * @param int|null $dayId  The day id to filter on
	 * @param int|null $timeId The time slot id to filter on
	 *
	 * @return array|null Returns the programme
	 */
	public function getProgrammeForDayAndTime($dayId, $timeId = null) {
		return $this->getProgramme($dayId, $timeId);
	}

	/**
	 * Retrieves the programme for sessions in the room with the specified id
	 *
	 * @param int|null $roomId The room id to filter on
	 *
	 * @return array|null Returns the programme
	 */
	public function getProgrammeForRoom($roomId) {
		return $this->getProgramme(null, null, null, $roomId);
	}

	/**
	 * Retrieves the programme for a particular session with the specified id
	 *
	 * @param int|null $sessionId The session id to filter on
	 *
	 * @return array|null Returns the programme
	 */
	public function getProgrammeForSession($sessionId) {
		return $this->getProgramme(null, null, null, null, $sessionId);
	}

	/**
	 * Retrieves the programme for sessions that contain one or more of the specified terms
	 *
	 * @param string|null $terms The terms, separated by a space
	 *
	 * @return array|null Returns the programme
	 */
	public function getProgrammeForTerms($terms) {
		return $this->getProgramme(null, null, null, null, null, $terms);
	}
} 