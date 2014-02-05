<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_fee_amount {
	private $id = 0;
    private $end_date;
    private $nr_of_days_start;
    private $nr_of_days_end;
    private $fee_amount;
	 private $substitute_name;
    private $oFeeState;

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

		$query = 'SELECT * FROM fee_amounts WHERE fee_amount_id=' . $id . ' AND deleted=0 AND enabled=1 ';

		$result = db_query($query);
		foreach ( $result as $record) {
            $this->id = $id;

            $arr = explode('-', $record->end_date);
            $day = $arr[2];
            $month = $arr[1];
            $year = $arr[0];
            $this->end_date = mktime(0,0,0, $month, $day, $year);

            $this->nr_of_days_start = $record->nr_of_days_start;
            $this->nr_of_days_end = $record->nr_of_days_end;
            $this->fee_amount = $record->fee_amount;
				$this->substitute_name = $record->substitute_name;
            $this->oFeeState = new class_conference_fee_state($record->fee_state_id);
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
	public function getEndDate($format = "Y-m-d") {
		return date($format, $this->end_date);
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getEndDateAsTime() {
		return $this->end_date;
	}

    /**
     * TODOEXPLAIN
     */
    public function getNrOfDaysStart() {
        return $this->nr_of_days_start;
    }

  /**
   * TODOEXPLAIN
   */
    public function getNrOfDaysEnd() {
        return $this->nr_of_days_end;
    }

    /**
     * TODOEXPLAIN
     */
    public function getAmount() {
        return $this->fee_amount;
    }

    /**
     * TODOEXPLAIN
     */
    public function getAmountInFormat() {
        return getReadableAmount($this->fee_amount);
    }

    public static function getFeeAmountForParticipant($oParticipant) {
      db_set_active(getSetting('db_connection'));

      $nrDays = db_select('participant_day')
        ->condition('user_id', $oParticipant->getId())
        ->condition('date_id', getSetting('date_id'))
        ->countQuery()
        ->execute()
        ->fetchField();

      $oFeeState = $oParticipant->getFeeState();

    $query = 'SELECT fee_amount_id
            FROM fee_amounts
            WHERE fee_state_id = :feeStateId
            AND end_date >= :endDate
            AND nr_of_days_start <= :nrDays
            AND nr_of_days_end >= :nrDays
            AND deleted=0 AND enabled=1';

        $feeAmount = db_query($query, array(':feeStateId' => $oFeeState->getId(), ':endDate' => date("Y-m-d", time()), ':nrDays' => $nrDays))
            ->fetchAssoc();

      $reObj = new class_conference_fee_amount($feeAmount['fee_amount_id']);
      db_set_active();

      return $reObj;
    }

    /**
     * TODOEXPLAIN
     */
    public function __toString() {
		 $days = '';
		 if ($this->nr_of_days_start == $this->nr_of_days_end) {
			 $days = $this->nr_of_days_start . t(' day');
		 }
		 else {
			 $days = $this->nr_of_days_start . '-' . $this->nr_of_days_end . t(' days');
		 }

		 $name = $this->oFeeState->getName();
		 if (!empty($this->substitute_name)) {
			 $name = $this->substitute_name;
		 }

		 return $name . ' (' .  $days . '): ' . $this->getAmountInFormat();
    }
}