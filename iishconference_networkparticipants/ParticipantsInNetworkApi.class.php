<?php

/**
 * API that returns all participants in a network
 */
class ParticipantsInNetworkApi {
	private static $apiName = 'participantsInNetwork';
	private $client;

	public function __construct() {
		$this->client = new ConferenceApiClient();
	}

	/**
	 * Returns all the accepted participants (last name, first name and email)
	 * that were in accepted sessions of the given network
	 *
	 * @param int|NetworkApi $networkId The network (id) in question
	 * @param bool           $excel     Whether to return an excel (xls) file
	 *
	 * @return mixed The result, or false in case of a failure
	 */
	public function getParticipantsForNetwork($networkId, $excel = false) {
		if ($networkId instanceof NetworkApi) {
			$networkId = $networkId->getId();
		}

		$response = $this->client->get(self::$apiName, array(
			'networkId' => $networkId,
			'excel'     => $excel,
			'networkName'   => iish_t('Network'),
			'firstName' => iish_t('First name'),
			'lastName'  => iish_t('Last name'),
			'email'     => iish_t('E-mail'),
      'organisation' => iish_t('Organisation'),
		));

		return (($response !== null) && $response['success']) ? $this->processResponse($response, $excel) : false;
	}

	/**
	 * Makes sure to properly return the results
	 *
	 * @param array $response The response obtained from the API
	 * @param bool  $excel    Whether we requested an excel file or not
	 *
	 * @return mixed The result
	 */
	private function processResponse($response, $excel) {
		if ($excel) {
			return base64_decode($response['xls']);
		}
		else {
			return $response['users'];
		}
	}
} 