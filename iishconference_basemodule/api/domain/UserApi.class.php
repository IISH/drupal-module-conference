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
	 * Set the address of this user
	 *
	 * @param string|null $address The address
	 */
	public function setAddress($address) {
		$address = (($address !== null) && strlen(trim($address)) > 0) ? trim($address) : null;

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
	 * Set the city of this user
	 *
	 * @param string|null $city The city
	 */
	public function setCity($city) {
		$city = (($city !== null) && strlen(trim($city)) > 0) ? trim($city) : null;

		$this->city = $city;
		$this->toSave['city'] = $city;
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
	 * Set the CV of this user
	 *
	 * @param string|null $cv The CV
	 */
	public function setCv($cv) {
		$cv = (($cv !== null) && strlen(trim($cv)) > 0) ? trim($cv) : null;

		$this->cv = $cv;
		$this->toSave['cv'] = $cv;
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
	 * Set the email of this user
	 *
	 * @param string|null $email The email address
	 */
	public function setEmail($email) {
		$email = (($email !== null) && strlen(trim($email)) > 0) ? trim($email) : null;

		$this->email = $email;
		$this->toSave['email'] = $email;
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
	 * Set the fax of this user
	 *
	 * @param string|null $fax The fax number
	 */
	public function setFax($fax) {
		$fax = (($fax !== null) && strlen(trim($fax)) > 0) ? trim($fax) : null;

		$this->fax = $fax;
		$this->toSave['fax'] = $fax;
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
	 * Set the gender of this user
	 *
	 * @param string|null $gender The gender
	 */
	public function setGender($gender) {
		$gender = (($gender !== null) && strlen(trim($gender)) > 0) ? trim($gender) : null;

		$this->gender = $gender;
		$this->toSave['gender'] = $gender;
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
	 * Set the mobile of this user
	 *
	 * @param string|null $mobile The mobile number
	 */
	public function setMobile($mobile) {
		$mobile = (($mobile !== null) && strlen(trim($mobile)) > 0) ? trim($mobile) : null;

		$this->mobile = $mobile;
		$this->toSave['mobile'] = $mobile;
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
	 * Set the phone of this user
	 *
	 * @param string|null $phone The phone number
	 */
	public function setPhone($phone) {
		$phone = (($phone !== null) && strlen(trim($phone)) > 0) ? trim($phone) : null;

		$this->phone = $phone;
		$this->toSave['phone'] = $phone;
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
	 * Set the title of this user
	 *
	 * @param string|null $title The title
	 */
	public function setTitle($title) {
		$title = (($title !== null) && strlen(trim($title)) > 0) ? trim($title) : null;

		$this->title = $title;
		$this->toSave['title'] = $title;
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
	 * Set the department of this user
	 *
	 * @param string|null $department The department
	 */
	public function setDepartment($department) {
		$department = (($department !== null) && strlen(trim($department)) > 0) ? trim($department) : null;

		$this->department = $department;
		$this->toSave['department'] = $department;
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
	 * Set the organisation of this user
	 *
	 * @param string|null $organisation The organisation
	 */
	public function setOrganisation($organisation) {
		$organisation = (($organisation !== null) && strlen(trim($organisation)) > 0) ? trim($organisation) : null;

		$this->organisation = $organisation;
		$this->toSave['organisation'] = $organisation;
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
	 * Set the country of this user
	 *
	 * @param int|CountryApi $country The country (id)
	 */
	public function setCountryId($country) {
		if ($country instanceof CountryApi) {
			$country = $country->getId();
		}

		$this->country = null;
		$this->country_id = $country;
		$this->toSave['country.id'] = $country;
	}

	/**
	 * Returns session participants information of this user
	 *
	 * @return SessionParticipantApi[] The session participant information
	 */
	public function getSessionParticipantInfo() {
		if (!$this->sessionParticipants) {
			$this->sessionParticipants =
				CRUDApiMisc::getAllWherePropertyEquals(new SessionParticipantApi(), 'user_id', $this->getId())
					->getResults();
		}

		return $this->sessionParticipants;
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
	 * Set the first name of this user
	 *
	 * @param string|null $firstName The first name
	 */
	public function setFirstName($firstName) {
		$firstName = (($firstName !== null) && strlen(trim($firstName)) > 0) ? trim($firstName) : null;

		$this->firstName = $firstName;
		$this->toSave['firstName'] = $firstName;
	}

	/**
	 * Returns the last name of this user
	 *
	 * @return string The last name
	 */
	public function getLastName() {
		return $this->lastName;
	}

	/**
	 * Set the last name of this user
	 *
	 * @param string|null $lastName The last name
	 */
	public function setLastName($lastName) {
		$lastName = (($lastName !== null) && strlen(trim($lastName)) > 0) ? trim($lastName) : null;

		$this->lastName = $lastName;
		$this->toSave['lastName'] = $lastName;
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

	public function save($showDrupalMessage = true) {
		$save = parent::save($showDrupalMessage);

		// Make sure to invalidate the cached user
		if ($save) {
			unset($_SESSION['conference']['user']);
		}

		return $save;
	}

	/**
	 * Compare two users, by last name, then by first name
	 *
	 * @param UserApi $instance Compare this instance with the given instance
	 *
	 * @return int &lt; 0 if <i>$instA</i> is less than
	 * <i>$instB</i>; &gt; 0 if <i>$instA</i>
	 * is greater than <i>$instB</i>, and 0 if they are
	 * equal.
	 */
	protected function compareWith($instance) {
		$lastNameCmp = strcmp(strtolower($this->getLastName()), strtolower($instance->getLastName()));
		if ($lastNameCmp === 0) {
			return strcmp(strtolower($this->getFirstName()), strtolower($instance->getFirstName()));
		}
		else {
			return $lastNameCmp;
		}
	}

	public function __toString() {
		return $this->getFullName();
	}
} 