<?php

/**
 * Holds a user obtained from the API
 */
class UserApi extends CRUDApiClient {
	protected $email;
	protected $lastName;
	protected $firstName;
	protected $gender;
	protected $title;
	protected $address;
	protected $city;
	protected $country_id;
	protected $phone;
	protected $fax;
	protected $mobile;
	protected $organisation;
	protected $department;
	protected $cv;
	protected $extraInfo;
	protected $papers_id;
	protected $daysPresent_day_id;

	private $sessionParticipants;
	private $country;
	private $days;

	public static function getListWithCriteria(array $properties, $showDrupalMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $showDrupalMessage);
	}

	/**
	 * Allows the creation of a user via an array with details
	 *
	 * @param array $user An array with user details
	 *
	 * @return UserApi A user object
	 */
	public static function getUserFromArray(array $user) {
		return self::createNewInstance(__CLASS__, $user);
	}

	/**
	 * Returns the address of this user
	 *
	 * @return string|null The address
	 */
	public function getAddress() {
		return $this->address;
	}

	/**
	 * Stores the address of this user
	 *
	 * @param string $address The address of this user
	 */
	public function setAddress($address) {
		$this->address = $address;
		$this->toSave['address'] = $address;
	}

	/**
	 * Returns the city of this user
	 *
	 * @return string|null The city
	 */
	public function getCity() {
		return $this->city;
	}

	/**
	 * Returns the CV of this user
	 *
	 * @return string|null The CV
	 */
	public function getCv() {
		return $this->cv;
	}

	/**
	 * Returns the email address of this user
	 *
	 * @return string The email address
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * Returns extra information added by this user
	 *
	 * @return string|null Extra information added by this user
	 */
	public function getExtraInfo() {
		return $this->extraInfo;
	}

	/**
	 * Returns the fax of this user
	 *
	 * @return string|null The fex
	 */
	public function getFax() {
		return $this->fax;
	}

	/**
	 * Returns the gender of this user ('M' or 'F')
	 *
	 * @return string|null The gender
	 */
	public function getGender() {
		return $this->gender;
	}

	/**
	 * Returns the mobile number of this user
	 *
	 * @return string|null The mobile number of this user
	 */
	public function getMobile() {
		return $this->mobile;
	}

	/**
	 * Returns a list with ids of all papers of this user
	 *
	 * @return int[] Paper ids of this user
	 */
	public function getPapersId() {
		return $this->papers_id;
	}

	/**
	 * Returns the phone number of this user
	 *
	 * @return string|null The phone number
	 */
	public function getPhone() {
		return $this->phone;
	}

	/**
	 * Returns the title of this user (Dr., Prof. etc.)
	 *
	 * @return string|null The title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Returns the location details of the user (department, organisation and country)
	 *
	 * @return string A comma seperated string of department, organisation and country
	 */
	public function getLocationDetails() {
		$locations = array();

		if (($this->getDepartment() !== null) && (strlen($this->getDepartment()) > 0)) {
			$locations[] = $this->getDepartment();
		}
		if (($this->getOrganisation() !== null) && (strlen($this->getOrganisation()) > 0)) {
			$locations[] = $this->getOrganisation();
		}
		if ($this->getCountry() !== null) {
			$locations[] = $this->getCountry()->getNameEnglish();
		}

		return implode(', ', $locations);
	}

	/**
	 * Returns the department of this user
	 *
	 * @return string|null The department
	 */
	public function getDepartment() {
		return $this->department;
	}

	/**
	 * Returns the organisation of this user
	 *
	 * @return string|null The organisation of this user
	 */
	public function getOrganisation() {
		return $this->organisation;
	}

	/**
	 * Returns the country of this user
	 *
	 * @return CountryApi|null The country
	 */
	public function getCountry() {
		if (!$this->country) {
			$countries = CachedConferenceApi::getCountries();

			foreach ($countries as $country) {
				if ($country->getId() == $this->getCountryId()) {
					$this->country = $country;
					break;
				}
			}
		}

		return $this->country;
	}

	/**
	 * Returns the id of the country of this user
	 *
	 * @return int|null The country id
	 */
	public function getCountryId() {
		return $this->country_id;
	}

	/**
	 * Returns session participants information of this user
	 *
	 * @return SessionParticipantApi[] The session participant information
	 */
	public function getSessionParticipantInfo() {
		if (!$this->sessionParticipants) {
			$props = new ApiCriteriaBuilder();
			$this->sessionParticipants = SessionParticipantApi::getListWithCriteria(
				$props
					->eq('user_id', $this->getId())
					->get()
			)->getResults();
		}

		return $this->sessionParticipants;
	}

	public function __toString() {
		return $this->getFullName();
	}

	/**
	 * Returns the ids of the days this user is present
	 *
	 * @return int[] The day ids
	 */
	public function getDaysPresentDayId() {
		return $this->daysPresent_day_id;
	}

	/**
	 * Set the days for which this participant signed up to be present
	 *
	 * @param int[]|DayApi[] $days The days (or their ids) to add to this participant
	 */
	public function setDaysPresent($days) {
		$this->days = null;
		$this->daysPresent_day_id = array();

		foreach ($days as $day) {
			if ($day instanceof DayApi) {
				$this->daysPresent_day_id[] = $day->getId();
			}
			else if (is_int($day)) {
				$this->daysPresent_day_id[] = $day;
			}
		}

		$this->toSave['daysPresent.day.id'] = implode(';', $this->daysPresent_day_id);
	}

	/**
	 * Returns the days this user is present
	 *
	 * @return DayApi[] The days
	 */
	public function getDaysPresent() {
		if (!$this->days) {
			$this->days = array();
			foreach ($this->daysPresent_day_id as $dayId) {
				foreach (CachedConferenceApi::getDays() as $day) {
					if ($day->getId() === $dayId) {
						$this->days[] = $day;
					}
				}
			}
		}

		return $this->days;
	}

	/**
	 * Returns the full name of this user
	 *
	 * @return string The full name
	 */
	public function getFullName() {
		return trim($this->getFirstName()) . ' ' . trim($this->getLastName());
	}

	/**
	 * Returns the first name of this user
	 *
	 * @return string The first name
	 */
	public function getFirstName() {
		return $this->firstName;
	}

	/**
	 * Returns the last name of this user
	 *
	 * @return string The last name
	 */
	public function getLastName() {
		return $this->lastName;
	}

	public function save($showDrupalMessage = true) {
		parent::save($showDrupalMessage);

		// Make sure to invalidate the cached user
		unset($_SESSION['conference']['user']);
	}
} 