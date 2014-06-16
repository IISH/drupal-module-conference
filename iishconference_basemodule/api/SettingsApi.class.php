<?php

/**
 * API that returns settings set in the CMS
 */
class SettingsApi {
	const ACCOMPANYING_PERSON_DESCRIPTION = 'accompanying_person_description';
	const ALLOWED_PAPER_EXTENSIONS = 'allowed_paper_extensions';
	const AUTHOR_REGISTRATION_CLOSES_ON = 'author_registration_closes_on';
	const AWARD_NAME = 'award_name';
	const BANK_TRANSFER_CLOSES_ON = 'bank_transfer_closes_on';
	const BANK_TRANSFER_INFO = 'bank_transfer_info';
	const COUNTRY_ID = 'country_id';
	const DEFAULT_EQUIPMENT_TEXT = 'default_equipment_text';
	const DEFAULT_NETWORK_ID = 'default_network_id';
	const DEFAULT_ORGANISATION_EMAIL = 'default_organisation_email';
	const EMAIL_MAX_NUM_TRIES = 'email_max_num_tries';
	const FINAL_REGISTRATION_CLOSES_ON = 'final_registration_closes_on';
	const FINAL_REGISTRATION_INTRO_TEXT = 'final_registration_intro_text';
	const MAX_PAPERS_PER_PERSON_PER_SESSION = 'max_papers_per_person_per_session';
	const MAX_UPLOAD_SIZE_PAPER = 'max_upload_size_paper';
	const NETWORK_NAME_PLURAL = 'network_name_plural';
	const NETWORK_NAME_SINGULAR = 'network_name_singular';
	const NUM_CANDIDATE_VOTES_ADVISORY_BOARD = 'num_candidate_votes_advisory_board';
	const ONLINE_PROGRAM_HEADER = 'online_program_header';
	const ONLINE_PROGRAM_UNDER_CONSTRUCTION = 'online_program_under_construction';
	const ORGANIZER_REGISTRATION_CLOSES_ON = 'organizer_registration_closes_on';
	const PATH_FOR_ADMIN_MENU = 'path_for_admin_menu';
	const PATH_FOR_MENU = 'path_for_menu';
	const PREREGISTRATION_CLOSES_ON = 'preregistration_closes_on';
	const PREREGISTRATION_CLOSES_ON_MESSAGE = 'preregistration_closes_on_message';
	const PREREGISTRATION_STARTS_ON = 'preregistration_starts_on';
	const PREREGISTRATION_STARTS_ON_MESSAGE = 'preregistration_starts_on_message';
	const SPECTATOR_NAME = 'spectator_name';

	// Show / hide
	const SHOW_ACCOMPANYING_PERSONS = 'show_accompanying_persons';
	const SHOW_AUTHOR_REGISTRATION = 'show_author_registration';
	const SHOW_AWARD = 'show_award';
	const SHOW_CHAIR_DISCUSSANT_POOL = 'show_chair_discussant_pool';
	const SHOW_CV = 'show_cv';
	const SHOW_DAYS = 'show_days';
	const SHOW_DAYS_SESSION_PLANNED = 'show_days_session_planned';
	const SHOW_LANGUAGE_COACH_PUPIL = 'show_language_coach_pupil';
	const SHOW_NETWORK = 'show_network';
	const SHOW_ORGANIZER_REGISTRATION = 'show_organizer_registration';
	const SHOW_PROGRAMME_ONLINE = 'show_programme_online';
	const SHOW_STUDENT = 'show_student';

	// Required fields
	const REQUIRED_CV = 'required_cv';

	// Email templates
	const BANK_TRANSFER_EMAIL_TEMPLATE_ID = 'bank_transfer_email_template_id';
	const PAYMENT_ACCEPTED_EMAIL_TEMPLATE_ID = 'payment_accepted_email_template_id';
	const PRE_REGISTRATION_EMAIL_TEMPLATE_ID = 'pre_registration_email_template_id';

	// PayWay
	const PAYWAY_ADDRESS = 'payway_address';
	const PAYWAY_PASSPHRASE_IN = 'payway_passphrase_in';
	const PAYWAY_PASSPHRASE_OUT = 'payway_passphrase_out';
	const PAYWAY_PROJECT = 'payway_project';

	private $client;
	private static $apiName = 'settings';
	private static $cachedSettings;

	public function __construct() {
		$this->client = new ConferenceApiClient();
	}

	/**
	 * Returns an array with the CMS settings where the keys hold the property
	 *
	 * @param bool $printErrorMessage Whether an error message should be printed on failure
	 *
	 * @return array|null The settings array or null in case of a failure
	 */
	public function settings($printErrorMessage) {
		return $this->client->get(self::$apiName, array(), $printErrorMessage);
	}

	/**
	 * Recommended use for obtaining the value for a certain setting.
	 * The settings array is obtained from the cache and the value for the given property is returned (if it exists)
	 *
	 * @param string $property The name of the property
	 *
	 * @return mixed The value set for this property for this event, or null if not found
	 */
	public static function getSetting($property) {
		if (!is_array(self::$cachedSettings)) {
			self::$cachedSettings = CachedConferenceApi::getSettings();
		}

		if (is_array(self::$cachedSettings) && isset(self::$cachedSettings[$property])) {
			return self::$cachedSettings[$property];
		}
		else {
			return null;
		}
	}
} 