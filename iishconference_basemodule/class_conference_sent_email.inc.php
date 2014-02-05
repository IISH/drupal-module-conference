<?php
/**
 * TODOEXPLAIN
 */
class class_conference_sent_email {
	private $sent_email_id = 0;
	private $user_id = 0;
	private $user;
	private $from_name;
	private $from_email;
	private $subject;
	private $body;
	private $date_time_sent;
	private $dates_times_sent_copy;

	/**
	 * TODOEXPLAIN
	 */
	public function __construct( $sent_email_id, $all = false ) {
		$this->sent_email_id = $sent_email_id;

		$this->init( $all );
	}

	/**
	 * TODOEXPLAIN
	 */
	private function init( $all = false ) {
		db_set_active( getSetting('db_connection') );

		$query = 'SELECT sent_email_id, user_id, from_name, from_email, subject, body, date_time_sent, dates_times_sent_copy FROM sent_emails WHERE sent_email_id=' . $this->sent_email_id . ' AND deleted=0 AND enabled=1 ';

		$result = db_query($query);
		foreach ($result as $record) {
			$this->sent_email_id = $record->sent_email_id;
			$this->user_id = $record->user_id;
			$this->user = new class_conference_user($this->user_id);
			$this->from_name = $record->from_name;
			$this->from_email = $record->from_email;
			$this->subject = $record->subject;
			$this->body = $record->body;
			$this->date_time_sent = $record->date_time_sent;
			$this->dates_times_sent_copy = $record->dates_times_sent_copy;
		}

		db_set_active();
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getBody() {
		return $this->body;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getDateTimeSent($format = "j F Y H:i:s") {
		if (!is_null($this->date_time_sent)) {
			$dateTimeSent = strtotime($this->date_time_sent);
			return date($format, $dateTimeSent) . " (CET)";
		}
		else {
			return null;
		}
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getDatesTimesSentCopy($format = "j F Y H:i:s") {
		if (!is_null($this->dates_times_sent_copy)) {
			$datesTimes = explode(';', $this->dates_times_sent_copy);

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
	 * TODOEXPLAIN
	 */
	public function getFromEmail() {
		return $this->from_email;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getFromName() {
		return $this->from_name;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getId() {
		return $this->sent_email_id;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getUserId() {
		return $this->user_id;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function resendEmail() {
		db_set_active(getSetting('db_connection'));

		if (is_null($this->date_time_sent)) {
			$query = "UPDATE sent_emails SET date_time_sent = :dateTime WHERE sent_email_id = :id";
			$result = db_query($query, array(':dateTime' => date('Y-m-d H:i:s', time()), ':id' => $this->getId()));
		}
		else {
			$copiesTimesSend = (is_null($this->dates_times_sent_copy)) ? array() : explode(';', $this->dates_times_sent_copy);
			$copiesTimesSend[] = time();

			$query = "UPDATE sent_emails SET dates_times_sent_copy = :times WHERE sent_email_id = :id";
			$result = db_query($query, array(':times' => implode(';', $copiesTimesSend), ':id' => $this->getId()));
		}

		db_set_active();
		sendEmail($this->getUser()->getEmail(), $this->getSubject(), $this->getBody(), getSetting('bcc_debug'));
	}
} 