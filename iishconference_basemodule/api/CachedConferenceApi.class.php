<?php

/**
 * Holds all methods to cache data obtained via the API and obtain the data again from the cache
 */
class CachedConferenceApi {
	private static $nameEventDateCache = 'iishconference_eventdate';
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
		try {
			self::setEventDate(false);
			self::setNetworks(false);
			self::setCountries(false);
			self::setDays(false);
			self::setSessionDateTimes(false);
			self::setParticipantTypes(false);
			self::setParticipantStates(false);
			self::setSessionStates(false);
			self::setPaperStates(false);
			self::setEquipment(false);
			self::setRooms(false);
			self::setExtras(false);
			self::setVolunteering(false);
			self::setSettings(false);
		}
		catch (Exception $exception) {
			watchdog_exception('conference api', $exception,
				'Failure communicating with the Conference API during cron job.');
		}
	}

	public static function setEventDate($printErrorMessage = true) {
		$eventDate = EventDateApi::getCurrent($printErrorMessage);
		cache_set(self::$nameEventDateCache, $eventDate, 'cache', CACHE_PERMANENT);

		return $eventDate;
	}

	public static function setNetworks($printErrorMessage = true) {
		$prop = new ApiCriteriaBuilder();
		$results = NetworkApi::getListWithCriteria($prop->get(), $printErrorMessage);
		if ($networks = $results->getResults()) {
			foreach ($networks as $network) {
				$network->getChairs($printErrorMessage);
			}
			cache_set(self::$nameNetworksCache, $networks, 'cache', CACHE_PERMANENT);

			return $networks;
		}

		return null;
	}

	public static function setCountries($printErrorMessage = true) {
		return self::set(self::$nameCountriesCache, 'CountryApi', $printErrorMessage);
	}

	private static function set($cacheName, $apiClassName, $printErrorMessage = true) {
		$prop = new ApiCriteriaBuilder();
		$rm = new ReflectionMethod($apiClassName, 'getListWithCriteria');
		$results = $rm->invoke(null, $prop->get(), $printErrorMessage);
		if ($results != null) {
			cache_set($cacheName, $results->getResults(), 'cache', CACHE_PERMANENT);

			return $results->getResults();
		}

		return null;
	}

	public static function setDays($printErrorMessage = true) {
		return self::set(self::$nameDaysCache, 'DayApi', $printErrorMessage);
	}

	public static function setSessionDateTimes($printErrorMessage = true) {
		return self::set(self::$nameSessionDateTimesCache, 'SessionDateTimeApi', $printErrorMessage);
	}

	public static function setParticipantTypes($printErrorMessage = true) {
		return self::set(self::$nameParticipantTypesCache, 'ParticipantTypeApi', $printErrorMessage);
	}

	public static function setParticipantStates($printErrorMessage = true) {
		return self::set(self::$nameParticipantStatesCache, 'ParticipantStateApi', $printErrorMessage);
	}

	public static function setSessionStates($printErrorMessage = true) {
		return self::set(self::$nameSessionStatesCache, 'SessionStateApi', $printErrorMessage);
	}

	public static function setPaperStates($printErrorMessage = true) {
		return self::set(self::$namePaperStatesCache, 'PaperStateApi', $printErrorMessage);
	}

	public static function setEquipment($printErrorMessage = true) {
		return self::set(self::$nameEquipmentCache, 'EquipmentApi', $printErrorMessage);
	}

	public static function setRooms($printErrorMessage = true) {
		return self::set(self::$nameRoomsCache, 'RoomApi', $printErrorMessage);
	}

	public static function setExtras($printErrorMessage = true) {
		return self::set(self::$nameExtrasCache, 'ExtraApi', $printErrorMessage);
	}

	public static function setVolunteering($printErrorMessage = true) {
		return self::set(self::$nameVolunteeringCache, 'VolunteeringApi', $printErrorMessage);
	}

	public static function setSettings($printErrorMessage = true) {
		$settingsApi = new SettingsApi();
		$settings = $settingsApi->settings($printErrorMessage);
		cache_set(self::$nameSettingsCache, $settings, 'cache', CACHE_PERMANENT);

		return $settings;
	}

	public static function getEventDate($printErrorMessage = true) {
		if ($result = cache_get(self::$nameEventDateCache, 'cache')) {
			return $result->data;
		}
		else {
			return self::setEventDate($printErrorMessage);
		}
	}

	public static function getRooms($printErrorMessage = true) {
		return self::get(self::$nameRoomsCache, 'RoomApi', $printErrorMessage);
	}

	private static function get($cacheName, $apiClassName, $printErrorMessage = true) {
		if ($result = cache_get($cacheName, 'cache')) {
			return $result->data;
		}
		else {
			return self::set($cacheName, $apiClassName, $printErrorMessage);
		}
	}

	public static function getNetworks($printErrorMessage = true) {
		if ($result = cache_get(self::$nameNetworksCache, 'cache')) {
			return $result->data;
		}
		else {
			return self::setNetworks($printErrorMessage);
		}
	}

	public static function getCountries($printErrorMessage = true) {
		return self::get(self::$nameCountriesCache, 'CountryApi', $printErrorMessage);
	}

	public static function getDays($printErrorMessage = true) {
		return self::get(self::$nameDaysCache, 'DayApi', $printErrorMessage);
	}

	public static function getExtras($printErrorMessage = true) {
		return self::get(self::$nameExtrasCache, 'ExtraApi', $printErrorMessage);
	}

	public static function getSessionDateTimes($printErrorMessage = true) {
		return self::get(self::$nameSessionDateTimesCache, 'SessionDateTimeApi', $printErrorMessage);
	}

	public static function getParticipantTypes($printErrorMessage = true) {
		return self::get(self::$nameParticipantTypesCache, 'ParticipantTypeApi', $printErrorMessage);
	}

	public static function getParticipantStates($printErrorMessage = true) {
		return self::get(self::$nameParticipantStatesCache, 'ParticipantStateApi', $printErrorMessage);
	}

	public static function getSessionStates($printErrorMessage = true) {
		return self::get(self::$nameSessionStatesCache, 'SessionStateApi', $printErrorMessage);
	}

	public static function getPaperStates($printErrorMessage = true) {
		return self::get(self::$namePaperStatesCache, 'PaperStateApi', $printErrorMessage);
	}

	public static function getEquipment($printErrorMessage = true) {
		return self::get(self::$nameEquipmentCache, 'EquipmentApi', $printErrorMessage);
	}

	public static function getVolunteering($printErrorMessage = true) {
		return self::get(self::$nameVolunteeringCache, 'VolunteeringApi', $printErrorMessage);
	}

	public static function getSettings($printErrorMessage = true) {
		if ($result = cache_get(self::$nameSettingsCache, 'cache')) {
			return $result->data;
		}
		else {
			return self::setSettings($printErrorMessage);
		}
	}
}