<?php

/**
 * API that returns all participants in a session
 */
class ParticipantsInSessionApi {
	private static $apiName = 'participantsInSession';
	private $client;

	public function __construct() {
		$this->client = new ConferenceApiClient();
	}

	/**
	 * Returns all the participants for a session in a network along with the user, paper and type details.
	 * If a non existing session id is given, the participants not in a session,
	 * but proposing for the given network are returned instead
	 *
	 * @param int|NetworkApi      $networkId The network in question
	 * @param int|SessionApi|null $sessionId The session in question
	 *
	 * @return array|bool The results, an array with the UserApi, PaperApi and ParticipantTypeApi,
	 * or false in case of a failure
	 */
	public function getParticipantsForSession($networkId, $sessionId) {
		if ($networkId instanceof NetworkApi) {
			$networkId = $networkId->getId();
		}

		if ($sessionId instanceof SessionApi) {
			$sessionId = $sessionId->getId();
		}
		else if ($sessionId === null) {
			$sessionId = -1;
		}

//		drupal_set_message('apiName: ' . self::$apiName, 'error');
//		drupal_set_message('networkId: ' . $networkId, 'error');
//		drupal_set_message('sessionId: ' . $sessionId, 'error');

		$response = $this->client->get(self::$apiName, array(
			'networkId' => $networkId,
			'sessionId' => $sessionId
		));

		return ($response !== null) ? $this->processResponse($response) : false;
	}

	/**
	 * Makes sure to properly return the results
	 *
	 * @param array $response The response obtained from the API
	 *
	 * @return array The results, an array with the UserApi, the PaperApi and the ParticipantTypeApi
	 */
	private function processResponse($response) {
		$results = array();
		foreach ($response as $participantInfo) {
			$user = UserApi::getUserFromArray($participantInfo[0]);
			$paper = ($participantInfo[1] === null) ? null : PaperApi::getPaperFromArray($participantInfo[1]);
			$type = (isset($participantInfo[2]) && ($participantInfo[2] !== null)) ? ParticipantTypeApi::getParticipantTypeFromArray($participantInfo[2]) : null;

			$results[] = array('user' => $user, 'paper' => $paper, 'type' => $type);
		}

		return $results;
	}
} 