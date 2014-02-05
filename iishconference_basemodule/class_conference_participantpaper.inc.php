<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_participantpaper {
	private $paper_id = 0;
	private $user_id = 0;
	private $session_id = 0;
	private $network_proposal_id = 0;
	private $title = '';
	private $co_authors = '';
	private $abstract = '';
	private $deleted = 0;
	private $state;

	/**
	 * TODOEXPLAIN
	 */
	public function __construct( $user_id, $session_id, $network_proposal_id = 0, $all = false ) {
		$this->user_id = $user_id;
		$this->session_id = $session_id;
		$this->network_proposal_id = $network_proposal_id;

		$this->init($all);
	}

	/**
	 * TODOEXPLAIN
	 */
	private function init( $all = false ) {
		db_set_active( getSetting('db_connection') );

		if ( $this->network_proposal_id != 0 ) {
			$crit = ' network_proposal_id=' . $this->network_proposal_id . ' ';
		} else {
			$crit = ' session_id=' . $this->session_id . ' ';
		}
		$query = 'SELECT paper_id, title, co_authors, abstract, paper_state_id, deleted FROM papers WHERE user_id=' . $this->user_id . ' AND ' . $crit . ' AND deleted=0 AND enabled=1 ';

		$result = db_query($query);
		foreach ( $result as $record) {
			$this->paper_id = $record->paper_id;
			$this->title = $record->title;
			$this->co_authors = $record->co_authors;
			$this->abstract = $record->abstract;
			$this->state = new class_conference_paperstate($record->paper_state_id);
			$this->deleted = $record->deleted;
		}

		db_set_active();

	}

	/**
	 * TODOEXPLAIN
	 */
	public function getId() {
		return $this->paper_id;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getDeleted() {
		return $this->deleted;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getCoAuthors() {
		return $this->co_authors;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getState() {
		return $this->state;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getAbstract( $length = 0 ) {
		$ret = $this->abstract;
		if ( $length > 0 && strlen( $ret ) > $length ) {
			$ret = substr($ret, 0, $length) . "...";
		}

		return $ret;
	}
}
