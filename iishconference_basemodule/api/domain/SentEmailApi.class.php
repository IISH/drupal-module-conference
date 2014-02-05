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
	 * @return mixed
	 */
	public function getBody() {
		return $this->body;
	}

	/**
	 * @return mixed
	 */
	public function getDateTimeCreated() {
		if ($this->dateTimeCreated === null) {
			return null;
		}
		else {
			return strtotime($this->dateTimeCreated);
		}
	}

	public function getDateTimeCreatedFormatted($format = "Y-m-d H:i:s") {
		if ($this->dateTimeCreated === null) {
			return null;
		}
		else {
			return date($format, $this->getDateTimeCreated());
		}
	}

	/**
	 * @return mixed
	 */
	public function getDateTimeSent() {
		if ($this->dateTimeSent === null) {
			return null;
		}
		else {
			return strtotime($this->dateTimeSent);
		}
	}

	public function getDateTimeSentFormatted($format = "Y-m-d H:i:s") {
		if ($this->dateTimeSent === null) {
			return null;
		}
		else {
			return date($format, $this->getDateTimeSent()) . " (CET)";
		}
	}

	/**
	 * @return mixed
	 */
	public function getDateTimesSentCopy() {
		return $this->dateTimesSentCopy;
	}

	public function getDateTimesSentCopyFormatted($format = "Y-m-d H:i:s") {
		if (!is_null($this->dateTimesSentCopy)) {
			$datesTimes = explode(';', $this->dateTimesSentCopy);

			$datesTimesNew = array();
			foreach ($datesTimes as $time) {
				$datesTimesNew[] = date($format, $time) . " (CET)";
			}

			return $datesTimesNew;
		}
		else {
			return null;
		}
	}

	/**
	 * @return mixed
	 */
	public function getFromEmail() {
		return $this->fromEmail;
	}

	/**
	 * @return mixed
	 */
	public function getFromName() {
		return $this->fromName;
	}

	/**
	 * @return mixed
	 */
	public function getNumTries() {
		return $this->numTries;
	}

	/**
	 * @return mixed
	 */
	public function getSendAsap() {
		return $this->sendAsap;
	}

	/**
	 * @return mixed
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * @return mixed
	 */
	public function getUserId() {
		return $this->user_id;
	}
} 