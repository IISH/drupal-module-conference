<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_email {
	private $template_id = 0;
	private $subject = '';
	private $body = '';
	private $sendername = '';
	private $senderemail = '';
	private $toemail = '';

	/**
	 * TODOEXPLAIN
	 */
	public function __construct( $template_id ) {
		$this->template_id = $template_id;

		$this->init();
	}

	/**
	 * TODOEXPLAIN
	 */
	private function init() {
		db_set_active( getSetting('db_connection') );

		$query = 'SELECT * FROM email_templates WHERE email_template_id=' . $this->template_id . ' AND deleted=0 AND enabled=1 ';

		$result = db_query($query);
		foreach ( $result as $record) {
			$this->subject = $record->subject;
			$this->body = $record->body;
			$this->sendername = $record->sender;
		}

		$this->senderemail = getSetting('email_fromemail');

		db_set_active();
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
	public function setSubject( $subject) {
		$this->subject = $subject;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function convertCodeToValue($code, $value) {
		$this->subject = str_replace($code, $value, $this->subject);
		$this->body = str_replace($code, $value, $this->body);
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
	public function getSenderName() {
		return $this->sendername;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getFromName() {
		return $this->getSenderName();
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getSenderEmail() {
		return $this->senderemail;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getFromEmail() {
		return $this->getSenderEmail();
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getToEmail() {
		return $this->toemail;
	}

	public function getCurrentTimeStamp() {
		return date("j F Y H:i:s") . " (Central European Time)";
	}
}
