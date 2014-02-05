<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_paper {
	private $paper_id = 0;
	private $user_id = 0;
	private $session_id = 0;
	private $network_proposal_id = 0;
	private $title = '';
	private $co_authors = '';
	private $abstract = '';
	private $deleted = 0;
	private $state;
	private $equipment_comment = '';
	private $oSession = null;
	private $filesize = null;
	private $filename;

	/**
	 * TODOEXPLAIN
	 */
	public function __construct( $paper_id, $all = false ) {
		$this->paper_id = $paper_id;

		$this->init( $all );
	}

	/**
	 * TODOEXPLAIN
	 */
	private function init( $all = false ) {
		db_set_active( getSetting('db_connection') );

		$query = 'SELECT paper_id, user_id, session_id, title, co_authors, abstract, paper_state_id, equipment_comment, network_proposal_id, filesize, filename FROM papers WHERE paper_id=' . $this->paper_id . ' AND deleted=0 AND enabled=1 ';

		$result = db_query($query);
		foreach ( $result as $record) {
			$this->paper_id = $record->paper_id;
			$this->user_id = $record->user_id;
			$this->session_id = $record->session_id;
			$this->oSession = new class_conference_session( $record->session_id, true );
			$this->title = $record->title;
			$this->co_authors = $record->co_authors;
			$this->abstract = $record->abstract;
			$this->state = new class_conference_paperstate($record->paper_state_id);
			$this->equipment_comment = $record->equipment_comment;
			$this->network_proposal_id = $record->network_proposal_id;
			$this->filesize = $record->filesize;
			$this->filename = $record->filename;
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
	public function getUserId() {
		return $this->user_id;
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
	public function getSessionId() {
		return $this->session_id;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getSession() {
		return $this->oSession;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getAuthor() {
		return new class_conference_user( $this->user_id );
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
	public function getEquipmentComment() {
		return $this->equipment_comment;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getEquipment( $ifresultempty = '' ) {
		$ret = '';
		$separator = '';

		db_set_active( getSetting('db_connection') );

		$query = 'SELECT equipment FROM paper_equipment INNER JOIN equipment ON paper_equipment.equipment_id=equipment.equipment_id WHERE paper_equipment.paper_id=' . $this->paper_id . ' AND equipment.deleted=0 AND equipment.enabled=1 ';

//echo $query;

		$result = db_query($query);
		foreach ( $result as $record) {
			$ret .= $separator . $record->equipment . ": yes";

			$separator = ', ';
		}

		db_set_active();

		if ( $ret == '' ) {
			$ret = $ifresultempty;
		}

		return $ret;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getProposedNetwork() {
		return $this->network_proposal_id;
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

	/**
	 * TODOEXPLAIN
	 */
	public function getPaperInfoForEmail() {
		$ret = '';

		$ret .= "Paper title: " . $this->getTitle() . " \n";
		$ret .= "Paper state: " . $this->getState()->getDescription() . " \n";
		$ret .= "Co-author(s): " . ifEmpty($this->getCoAuthors(), '-') . " \n";
		$ret .= "Session: " . ifEmpty($this->getSession()->getName(), '-') . " \n";

//		$networks = $this->getSession()->getNetworks();
		$oNetwork = new class_conference_network( $this->getProposedNetwork() );
		$ret .= "Proposed network: " . $oNetwork->getNetworkName() . " \n";
		$ret .= "Network chair(s): ";
		$chairs = $oNetwork->getChairs();
		$separator = '';
		foreach ( $chairs as $chair ) {
			$ret .= $separator . $chair->getFirstname() . " " . $chair->getLastname() . " ( " . $chair->getEmail() . " )";
			$separator = ', ';
		}
		$ret .= " \n \n";

		$ret .= "Abstract: \n" . $this->getAbstract() . " \n \n";
		$ret .= "Audio/visual equipment \n";
		$ret .= $this->getEquipment("Beamer: no") . " \n";
		$ret .= "Extra audio/visual request: " . ifEmpty($this->getEquipmentComment(), '-') . " \n";

		return $ret;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getFileSize() {
		return $this->filesize;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getFileName() {
		return $this->filename;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function setMailPaperState( $value ) {
		db_set_active( getSetting('db_connection') );

		$query = "UPDATE papers SET mail_paper_state=" . $value . " WHERE paper_id=" . $this->getId();
//echo $query . ' +<br><br>';
		$result = db_query($query);

		db_set_active();
	}

	/**
	 * TODOEXPLAIN
	 */
	public function deleteFile() {
		db_set_active(getSetting('db_connection'));

		db_update('papers')
			->fields(array(
				'file' => null,
				'filesize' => null,
				'filename' => null,
				'content_type' => null,
			))
			->condition('paper_id', $this->getId())
			->execute();

		db_set_active();
	}

	/**
	 * TODOEXPLAIN
	 */
	public function storeFile($oFile) {
		db_set_active(getSetting('db_connection'));

		db_update('papers')
			->fields(array(
				'file' => file_get_contents($oFile->uri),
				'filesize' => $oFile->filesize,
				'filename' => $oFile->filename,
				'content_type' => $oFile->filemime,
			))
			->condition('paper_id', $this->getId())
			->execute();

		db_set_active();
	}

	/**
	 * Makes the current request download the paper from the database
	 */
	public function makeDownload() {
		db_set_active(getSetting('db_connection'));

		$query =
			'SELECT file, filename, filesize, content_type
			FROM papers
			WHERE paper_id = :paperId';
		$record = db_query($query, array(':paperId' => $this->paper_id))->fetchAssoc();

		drupal_add_http_header('Content-Type', $record['content_type']);
		drupal_add_http_header('Content-Length', $record['filesize']);
		drupal_add_http_header('Content-Disposition', 'attachment; filename=' .
			str_replace(' ', '-', getSetting('code_year')) . '-' . $this->user_id . '-' . $record['filename']);

		echo $record['file'];

		db_set_active();
		drupal_exit();
	}
}