<?php

/**
 * Holds a participant volunteering obtained from the API
 */
class ParticipantVolunteeringApi extends CRUDApiClient {
	protected $participantDate_id;
	protected $volunteering_id;
	protected $network_id;
	protected $volunteering;
	protected $network;

	private $volunteeringInstance;
	private $networkInstance;

	public static function getListWithCriteria(array $properties, $showDrupalMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $showDrupalMessage);
	}

	/**
	 * Returns the network for which the participant volunteerd
	 *
	 * @return NetworkApi The network
	 */
	public function getNetwork() {
		if (!$this->networkInstance) {
			$this->networkInstance = $this->createNewInstance('NetworkApi', $this->network);
		}

		return $this->networkInstance;
	}

	/**
	 * Returns the network id for which the participant volunteerd
	 *
	 * @return int The network id
	 */
	public function getNetworkId() {
		return $this->network_id;
	}

	/**
	 * Returns the participant date id of the participant that volunteerd
	 *
	 * @return int The participant id
	 */
	public function getParticipantDateId() {
		return $this->participantDate_id;
	}

	/**
	 * The type of volunteering this participant signed up for
	 *
	 * @return VolunteeringApi[] The volunteering type
	 */
	public function getVolunteering() {
		if (!$this->volunteeringInstance) {
			$this->volunteeringInstance = $this->createNewInstance('VolunteeringApi', $this->volunteering);
		}

		return $this->volunteeringInstance;
	}

	/**
	 * The type of volunteering id this participant signed up for
	 *
	 * @return int The volunteering id
	 */
	public function getVolunteeringId() {
		return $this->volunteering_id;
	}

	/**
	 * Filter out the list with participant volunteerings based on a certain volunteering type
	 *
	 * @param ParticipantVolunteering[] $participantVolunteerings The list to filter
	 * @param int                       $volunteeringId           The volunteering id to filter on
	 *
	 * @return ParticipantVolunteering[] The filtered list with participant volunteerings
	 */
	public static function getAllNetworksForVolunteering($participantVolunteerings, $volunteeringId) {
		$networks = array();
		foreach ($participantVolunteerings as $participantVolunteering) {
			if ($participantVolunteering->getVolunteeringId() == $volunteeringId) {
				$networks[] = $participantVolunteering->getNetwork();
			}
		}

		return $networks;
	}
}