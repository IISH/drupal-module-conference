<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_participantsession {
	private $paper_id = 0;
	private $user_id = 0;
	private $session_id = 0;
	private $network_proposal_id = 0;
	private $title = '';
	private $co_authors = '';
	private $abstract = '';
	private $author;
	private $oState;
	private $session;
	private $deleted = 0;

	/**
	 * TODOEXPLAIN
	 */
	public function __construct( $user_id, $session_id, $network_proposal_id = 0, $all = false ) {
		$this->user_id = $user_id;
		$this->session_id = $session_id;
		$this->network_proposal_id = $network_proposal_id;

		$this->init();
	}

	/**
	 * TODOEXPLAIN
	 */
	private function init() {
		db_set_active( getSetting('db_connection') );

		if ( $this->network_proposal_id != 0 && $session_id = -1 ) { // ALL NETWORK PROPOSALS
			$crit = ' network_proposal_id=' . $this->network_proposal_id . ' ';
		} elseif ( $this->network_proposal_id != 0 ) { // NETWORK PROPOSALS WHEN NO SESSION ENTERED
			$crit = ' network_proposal_id=' . $this->network_proposal_id . ' AND session_id IS NULL ';
		} else { // SESSION
			$crit = ' session_id=' . $this->session_id . ' ';
		}
		$query = 'SELECT paper_id, title, co_authors, abstract, user_id, paper_state_id, deleted, session_id FROM papers WHERE user_id=' . $this->user_id . ' AND ' . $crit . ' ';
//echo $query . '+ <br><br>';

		$result = db_query($query);
		foreach ( $result as $record) {
			$this->paper_id = $record->paper_id;
			$this->author = new class_conference_user($record->user_id, true);
//echo $record->title . ' +<br>';
			$this->title = $record->title;
			$this->co_authors = $record->co_authors;
			$this->abstract = $record->abstract;
			$this->oState = new class_conference_paperstate($record->paper_state_id);
			$this->deleted = $record->deleted;
			if ( $record->session_id == null ) {
				$this->session = null;
			} else {
				$this->session = new class_conference_session( $record->session_id );
			}
			$oParticipantType = new class_conference_participanttype($record->user_id, $this->session_id, true);
//			$this->arrFunctions = $oParticipantType->getFunctions();
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
	public function getAuthor() {
		return $this->author;
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
	public function getTitle() {
		return $this->title;
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
		return $this->oState;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getSession() {
		return $this->session;
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
