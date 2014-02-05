<?php

/**
 * API for obtaining the current program
 */
class ProgramApi {
	private $client;
	private static $apiName = 'program';

	public function __construct() {
		$this->client = new ConferenceApiClient();
	}

	/**
	 * Retrieves the program with the provided filters
	 *
	 * @param int|null    $dayId     Only return the program for the day with the specified id
	 * @param int|null    $timeId    Only return the program for the time slot with the specified id
	 * @param int|null    $networkId Only return the program for sessions in the network with the specified id
	 * @param int|null    $roomId    Only return the program for sessions in the room with the specified id
	 * @param string|null $terms     Only return the program for sessions that contain one or more of the specified terms
	 *                               (Terms are separated by a space)
	 *
	 * @return array|null Returns the program
	 */
	public function getProgram($dayId = null, $timeId = null, $networkId = null, $roomId = null, $terms = null) {
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
		if (!is_null($terms) && (strlen(trim($terms)) > 0)) {
			$params['terms'] = trim($terms);
		}

		return $this->client->get(self::$apiName, $params);
	}

	/**
	 * Retrieves the program for sessions in the network with the specified id
	 *
	 * @param int|null $networkId The network id to filter on
	 *
	 * @return array|null Returns the program
	 */
	public function getProgramForNetwork($networkId) {
		return $this->getProgram(null, null, $networkId);
	}

	/**
	 * Retrieves the program for the specified day and/or time slot
	 *
	 * @param int|null $dayId  The day id to filter on
	 * @param int|null $timeId The time slot id to filter on
	 *
	 * @return array|null Returns the program
	 */
	public function getProgramForDayAndTime($dayId, $timeId = null) {
		return $this->getProgram($dayId, $timeId);
	}

	/**
	 * Retrieves the program for sessions in the room with the specified id
	 *
	 * @param int|null $roomId The room id to filter on
	 *
	 * @return array|null Returns the program
	 */
	public function getProgramForRoom($roomId) {
		return $this->getProgram(null, null, null, $roomId);
	}

	/**
	 * Retrieves the program for sessions that contain one or more of the specified terms
	 *
	 * @param string|null $terms The terms, separated by a space
	 *
	 * @return array|null Returns the program
	 */
	public function getProgramForTerms($terms) {
		return $this->getProgram(null, null, null, null, $terms);
	}
} 