<?php

/**
 * API that returns settings set in the CMS
 */
class SettingsApi {
	const MAX_PAPERS_PER_PERSON_PER_SESSION = 'max_papers_per_person_per_session';
	const SHOW_PROGRAMME_ONLINE = 'show_programme_online';
	const DEFAULT_ORGANISATION_EMAIL = 'default_organisation_email';
	const BANK_TRANSFER_INFO = 'bank_transfer_info';
	const MAX_UPLOAD_SIZE_PAPER = 'max_upload_size_paper';
	const ALLOWED_PAPER_EXTENSIONS = 'allowed_paper_extensions';
	const EMAIL_MAX_NUM_TRIES = 'email_max_num_tries';
	const NUM_CANDIDATE_VOTES_ADVISORY_BOARD = 'num_candidate_votes_advisory_board';

	// Email templates
	const BANK_TRANSFER_EMAIL_TEMPLATE_ID = 'bank_transfer_email_template_id';
	const PAYMENT_ACCEPTED_EMAIL_TEMPLATE_ID = 'payment_accepted_email_template_id';

	private $client;
	private static $apiName = 'settings';

	public function __construct() {
		$this->client = new ConferenceApiClient();
	}

	/**
	 * Returns an array with the CMS settings where the keys hold the property
	 *
	 * @return array|null The settings array or null in case of a failure
	 */
	public function settings() {
		return $this->client->get(self::$apiName, array());
	}
} 