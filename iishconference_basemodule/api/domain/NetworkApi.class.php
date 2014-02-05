<?php

/**
 * Holds a network obtained from the API
 */
class NetworkApi extends CRUDApiClient {
	protected $name;
	protected $comment;
	protected $longDescription;
	protected $url;
	protected $email;
	protected $showOnline;
	protected $chairs_chair_id;

	private $chairs;

	public static function getListWithCriteria(array $properties, $showDrupalMessage = true) {
		// Make sure we only obtain networks that can be shown online
		$prop = new ApiCriteriaBuilder();
		$properties = array_merge($prop->eq('showOnline', true)->get(), $properties);

		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $showDrupalMessage);
	}

	/**
	 * For the given list of networks, filter out the networks in which the given user is not a chair
	 *
	 * @param NetworkApi[] $networks All the networks to filter on
	 * @param UserApi      $chair    The user/chair in question
	 *
	 * @return NetworkApi[] The networks of the given chair
	 */
	public static function getOnlyNetworksOfChair($networks, $chair) {
		foreach ($networks as $i => $network) {
			if (!array_search($chair->getId(), $network->chairs_chair_id)) {
				unset($networks[$i]);
			}
		}

		return array_values($networks);
	}

	/**
	 * Returns a list with the ids of all the chairs of this network
	 *
	 * @return int[] A list of chair ids
	 */
	public function getChairsId() {
		return $this->chairs_chair_id;
	}

	/**
	 * Returns the comment (short description) of this network
	 *
	 * @return string|null The comment
	 */
	public function getComment() {
		return $this->comment;
	}

	/**
	 * Returns the long description of this network
	 *
	 * @return string|null The long description
	 */
	public function getLongDescription() {
		return $this->longDescription;
	}

	/**
	 * Returns the email address for correspondence with this network
	 *
	 * @return string The email address for correspondence with this network
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Returns the URL for this network
	 *
	 * @return string The URL for this network
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * Returns a list with all the chairs of this network
	 *
	 * @return UserApi[] A list of chairs
	 */
	public function getChairs() {
		if (!$this->chairs) {
			$this->chairs = array();

			foreach ($this->chairs_chair_id as $id) {
				$prop = new ApiCriteriaBuilder();
				$this->chairs[] = UserApi::getListWithCriteria(
					$prop
						->eq('id', $id)
						->get()
				)->getFirstResult();
			}
		}

		return $this->chairs;
	}

	public function __toString() {
		return $this->getName();
	}

	/**
	 * Returns the name of this network
	 *
	 * @return string The name of this network
	 */
	public function getName() {
		return $this->name;
	}
} 