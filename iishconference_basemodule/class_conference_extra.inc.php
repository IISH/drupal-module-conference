<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_extra {
	private $id = 0;
	private $title;
	private $extra;
	private $description;
	private $description_2nd;
	private $amount = 0.00;
   private $max_seats = 0;

	/**
	 * TODOEXPLAIN
	 */
	public function __construct( $id, $all = false ) {
		if ( $id == '' ) {
			$id = 0;
		}

		$this->init( $id, $all );
	}

	/**
	 * TODOEXPLAIN
	 */
	private function init( $id, $all = false ) {
		db_set_active( getSetting('db_connection') );

		$query = 'SELECT * FROM extras WHERE extra_id=' . $id . ' AND deleted=0 AND enabled=1 ';
		$result = db_query($query);

		foreach ($result as $record) {
			$this->id = $id;
			$this->title = $record->title;
			$this->extra = $record->extra;
			$this->description = $record->description;
			$this->description_2nd = $record->description_2nd;
			$this->amount = $record->amount;
			$this->max_seats = $record->max_seats;
		}

		db_set_active();
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getId() {
		return $this->id;
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
	public function getExtra() {
		return $this->extra;
	}

    /**
     * TODOEXPLAIN
     */
    public function getDescription() {
	  	return $this->description;
    }

	/**
	 * TODOEXPLAIN
	 */
	public function getSecondDescription() {
		return $this->description_2nd;
	}

    /**
     * TODOEXPLAIN
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * TODOEXPLAIN
     */
    public function getAmountInFormat() {
        return getReadableAmount($this->amount);
    }

    /**
     * TODOEXPLAIN
     */
    public function getMaxSeats() {
        return $this->max_seats;
    }

    public static function getExtrasForParticipant($oParticipant) {
      db_set_active(getSetting('db_connection'));

      $query = db_select('extras', 'e');
      $query->innerJoin('participant_date_extra', 'pde', 'e.extra_id = pde.extra_id');
      $result = $query
        ->fields('e', array('extra_id'))
        ->condition('pde.participant_date_id', $oParticipant->getParticipantDateId())
        ->execute();

      $arr = array();
      foreach ($result as $record) {
        $oExtra = new class_conference_extra($record->extra_id);
        $arr[] = $oExtra;
      }

      db_set_active();

      return $arr;
    }

	public function getExtendedString() {
		if ($this->getAmount() > 0) {
			return $this->getExtra() . ': ' . $this->getDescription() . ' (' . $this->getAmountInFormat() . ')';
		}
		else {
			return $this->getExtra() . ': ' . $this->getDescription();
		}
	}

	public function __toString() {
      if ($this->getAmount() > 0) {
			return $this->getTitle() . ': ' . $this->getAmountInFormat();
		}
		else {
			return $this->getTitle();
		}
 	}
}