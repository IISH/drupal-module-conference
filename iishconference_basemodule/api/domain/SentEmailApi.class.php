<?php

/**
 * Holds a sent email obtained from the API
 */
class SentEmailApi extends CRUDApiClient {
	protected $user_id;
	protected $fromName;
	protected $fromEmail;
	protected $subject;
	protected $body;
	protected $dateTimeCreated;
	protected $dateTimeSent;
	protected $dateTimesSentCopy;
	protected $sendAsap;
	protected $numTries;

	public static function getListWithCriteria(array $properties, $showDrupalMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $showDrupalMessage);
	}

	/**
	 * Returns the email body
	 *
	 * @return string The email body
	 */
	public function getBody() {
		return $this->body;
	}

	/**
	 * Returns a Unix timestamp of the date/time this email was created
	 *
	 * @return int|null The Unix timestamp this email was created
	 */
	public function getDateTimeCreated() {
		if ($this->dateTimeCreated === null) {
			return null;
		}
		else {
			return strtotime($this->dateTimeCreated);
		}
	}

	/**
	 * Returns the date/time this email was created with the given format
	 *
	 * @param string $format The date/time format
	 *
	 * @return string|null The date/time according to the format
	 */
	public function getDateTimeCreatedFormatted($format = 'Y-m-d H:i:s') {
		if ($this->dateTimeCreated === null) {
			return null;
		}
		else {
			return date($format, $this->getDateTimeCreated());
		}
	}

	/**
	 * Returns a Unix timestamp of the date/time this email was successfully sent
	 *
	 * @return int|null The Unix timestamp this email was successfully sent
	 */
	public function getDateTimeSent() {
		if ($this->dateTimeSent === null) {
			return null;
		}
		else {
			return strtotime($this->dateTimeSent);
		}
	}

	/**
	 * Returns the date/time this email was successfully sent with the given format
	 *
	 * @param string $format The date/time format
	 *
	 * @return string|null The date/time according to the format
	 */
	public function getDateTimeSentFormatted($format = 'Y-m-d H:i:s') {
		if ($this->dateTimeSent === null) {
			return null;
		}
		else {
			return date($format, $this->getDateTimeSent()) . ' (CET)';
		}
	}

	/**
	 * Returns Unix timestamps of the dates/times copies of this email were successfully sent
	 *
	 * @return int[]|null The Unix timestamps copies of this email were successfully sent
	 */
	public function getDateTimesSentCopy() {
		if ($this->dateTimesSentCopy === null) {
			return null;
		}

		$unixTimes = array();
		$datesTimes = explode(';', $this->dateTimesSentCopy);
		foreach ($datesTimes as $dateTime) {
			$unixTimes[] = strtotime($dateTime);
		}

		return $unixTimes;
	}

	/**
	 * Returns the dates/times copies of this email were successfully sent with the given format
	 *
	 * @param string $format The date/time format
	 *
	 * @return string[]|null The dates/times according to the format
	 */
	public function getDateTimesSentCopyFormatted($format = 'Y-m-d H:i:s') {
		if ($this->dateTimesSentCopy === null) {
			return null;
		}

		$datesTimesNew = array();
		$datesTimes = explode(';', $this->dateTimesSentCopy);
		foreach ($datesTimes as $time) {
			if (strlen(trim($time)) > 0) {
				$datesTimesNew[] = date($format, $time) . ' (CET)';
			}
		}

		return $datesTimesNew;
	}

	/**
	 * Returns the email address of the person who send this email
	 *
	 * @return string The email address of the person who send this email
	 */
	public function getFromEmail() {
		return $this->fromEmail;
	}

	/**
	 * Returns the name of the person who send this email
	 *
	 * @return string The name of the person who send this email
	 */
	public function getFromName() {
		return $this->fromName;
	}

	/**
	 * Returns how many times the CMS has tried to sent this email
	 *
	 * @return int The number of tries
	 */
	public function getNumTries() {
		return $this->numTries;
	}

	/**
	 * Returns whether this email has to be send as soon as possible
	 *
	 * @return bool Whether this email has to be send as soon as possible
	 */
	public function getSendAsap() {
		return $this->sendAsap;
	}

	/**
	 * Returns the subject of this email
	 *
	 * @return string The subject of this email
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * Returns the id of the user to whom this email is addressed
	 *
	 * @return int The id of the user to whom this email is addressed
	 */
	public function getUserId() {
		return $this->user_id;
	}

	public function __toString() {
		return $this->getSubject();
	}
} 