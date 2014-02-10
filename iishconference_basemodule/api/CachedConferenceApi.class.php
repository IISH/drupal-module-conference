<?php

/**
 * Holds all methods to cache data obtained via the API and obtain the data again from the cache
 */
class CachedConferenceApi {
	private static $nameNetworksCache = 'iishconference_networks';
	private static $nameCountriesCache = 'iishconference_countries';
	private static $nameDaysCache = 'iishconference_days';
	private static $nameSessionDateTimesCache = 'iishconference_session_date_times';
	private static $nameParticipantTypesCache = 'iishconference_participant_types';
	private static $nameParticipantStatesCache = 'iishconference_participant_states';
	private static $nameSessionStatesCache = 'iishconference_session_states';
	private static $namePaperStatesCache = 'iishconference_paper_states';
	private static $nameEquipmentCache = 'iishconference_equipment';
	private static $nameRoomsCache = 'iishconference_rooms';
	private static $nameExtrasCache = 'iishconference_extras';
	private static $nameVolunteeringCache = 'iishconference_volunteering';
	private static $nameSettingsCache = 'iishconference_settings';

	/**
	 * Updates all caches
	 */
	public static function updateAll() {
		self::setNetworks();
		self::setCountries();
		self::setDays();
		self::setSessionDateTimes();
		self::setParticipantTypes();
		self::setParticipantStates();
		self::setSessionStates();
		self::setPaperStates();
		self::setEquipment();
		self::setRooms();
		self::setExtras();
		self::setVolunteering();
		self::setSettings();
	}

	public static function setNetworks() {
		$prop = new ApiCriteriaBuilder();
		$results = NetworkApi::getListWithCriteria($prop->get());
		if ($networks = $results->getResults()) {
			foreach ($networks as $network) {
				$network->getChairs();
			}
			cache_set(self::$nameNetworksCache, $networks, 'cache', CACHE_PERMANENT);

			return $networks;
		}

		return null;
	}

	public static function setCountries() {
		return self::set(self::$nameCountriesCache, 'CountryApi');
	}

	public static function setDays() {
		return self::set(self::$nameDaysCache, 'DayApi');
	}

	public static function setExtras() {
		return self::set(self::$nameExtrasCache, 'ExtraApi');
	}

	public static function setSessionDateTimes() {
		return self::set(self::$nameSessionDateTimesCache, 'SessionDateTimeApi');
	}

	public static function setParticipantTypes() {
		return self::set(self::$nameParticipantTypesCache, 'ParticipantTypeApi');
	}

	public static function setParticipantStates() {
		return self::set(self::$nameParticipantStatesCache, 'ParticipantStateApi');
	}

	public static function setSessionStates() {
		return self::set(self::$nameSessionStatesCache, 'SessionStateApi');
	}

	public static function setPaperStates() {
		return self::set(self::$namePaperStatesCache, 'PaperStateApi');
	}

	public static function setEquipment() {
		return self::set(self::$nameEquipmentCache, 'EquipmentApi');
	}

	public static function setVolunteering() {
		return self::set(self::$nameVolunteeringCache, 'VolunteeringApi');
	}

	public static function setRooms() {
		return self::set(self::$nameRoomsCache, 'RoomApi');
	}

	public static function getRooms() {
		return self::get(self::$nameRoomsCache, 'RoomApi');
	}

	public static function getNetworks() {
		if ($result = cache_get(self::$nameNetworksCache, 'cache')) {
			return $result->data;
		}
		else {
			return self::setNetworks();
		}
	}

	public static function getCountries() {
		return self::get(self::$nameCountriesCache, 'CountryApi');
	}

	public static function getDays() {
		return self::get(self::$nameDaysCache, 'DayApi');
	}

	public static function getExtras() {
		return self::get(self::$nameExtrasCache, 'ExtraAPI');
	}

	public static function getSessionDateTimes() {
		return self::get(self::$nameSessionDateTimesCache, 'SessionDateTimeApi');
	}

	public static function getParticipantTypes() {
		return self::get(self::$nameParticipantTypesCache, 'ParticipantTypeApi');
	}

	public static function getParticipantStates() {
		return self::get(self::$nameParticipantStatesCache, 'ParticipantStateApi');
	}

	public static function getSessionStates() {
		return self::get(self::$nameSessionStatesCache, 'SessionStateApi');
	}

	public static function getPaperStates() {
		return self::get(self::$namePaperStatesCache, 'PaperStateApi');
	}

	public static function getEquipment() {
		return self::get(self::$nameEquipmentCache, 'EquipmentApi');
	}

	public static function getVolunteering() {
		return self::get(self::$nameVolunteeringCache, 'VolunteeringApi');
	}

	public static function getSettings() {
		if ($result = cache_get(self::$nameSettingsCache, 'cache')) {
			return $result->data;
		}
		else {
			return self::setSettings();
		}
	}

	public static function setSettings() {
		$settingsApi = new SettingsApi();
		$settings = $settingsApi->settings();
		cache_set(self::$nameSettingsCache, $settings, 'cache', CACHE_PERMANENT);

		return $settings;
	}

	private static function set($cacheName, $apiClassName) {
		$prop = new ApiCriteriaBuilder();
		$rm = new ReflectionMethod($apiClassName, 'getListWithCriteria');
		$results = $rm->invoke(null, $prop->get());
		if ($results != null) {
			cache_set($cacheName, $results->getResults(), 'cache', CACHE_PERMANENT);

			return $results->getResults();
		}

		return null;
	}

	private static function get($cacheName, $apiClassName) {
		if ($result = cache_get($cacheName, 'cache')) {
			return $result->data;
		}
		else {
			return self::set($cacheName, $apiClassName);
		}
	}
} 