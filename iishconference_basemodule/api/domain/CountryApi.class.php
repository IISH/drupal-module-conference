<?php

/**
 * Holds a country obtained from the API
 */
class CountryApi extends CRUDApiClient {
	protected $tld;
	protected $isoCode;
	protected $nameEnglish;
	protected $nameDutch;
	protected $exemptCountries_id;

	public static function getListWithCriteria(array $properties, $printErrorMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $printErrorMessage);
	}

	/**
	 * Returns the country where the current event takes place
	 *
	 * @return CountryApi|null The country where the event takes place or null if not found
	 */
	public static function getCountryOfEvent() {
		$countryId = SettingsApi::getSetting(SettingsApi::COUNTRY_ID);
		$countries = CachedConferenceApi::getCountries();

		foreach ($countries as $country) {
			if ($country->getId() == $countryId) {
				return $country;
			}
		}

		return null;
	}

	/**
	 * Returns the top level domain of this country
	 *
	 * @return string The top level domain
	 */
	public function getTld() {
		return $this->tld;
	}

	/**
	 * Returns the ISO code of this country
	 *
	 * @return string The ISO code
	 */
	public function getIsoCode() {
		return $this->isoCode;
	}

	/**
	 * The Dutch name of this country
	 *
	 * @return string The name in Dutch
	 */
	public function getNameDutch() {
		return $this->nameDutch;
	}

	/**
	 * The English name of this country
	 *
	 * @return string The name in English
	 */
	public function getNameEnglish() {
		return $this->nameEnglish;
	}

	/**
	 * Returns the ids of all countries that are exempted for invitation letter requests
	 *
	 * @return int[] All excempted countries ids
	 */
	public function getExemptCountriesId() {
		return $this->exemptCountries_id;
	}

	public function __toString() {
		return $this->getNameEnglish();
	}
} 