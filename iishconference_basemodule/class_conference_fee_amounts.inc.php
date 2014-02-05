<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_fee_amounts {
	private $fee_state_id = 0;

	/**
	 * TODOEXPLAIN
	 */
	public function __construct( $fee_state_id, $all = false ) {
		if ( $fee_state_id == '' ) {
          $fee_state_id = 0;
		}

		$this->fee_state_id = $fee_state_id;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function getFeeAmounts() {
		$arr = array();
		db_set_active( getSetting('db_connection') );

		$query =
			'SELECT * FROM fee_amounts
			WHERE fee_state_id = :feeStateId
			AND deleted=0 AND enabled=1
			ORDER BY end_date, nr_of_days_start, nr_of_days_end';

		$result = db_query($query, array(':feeStateId' => $this->fee_state_id));
		foreach ($result as $record) {
			$arr[] = new class_conference_fee_amount($record->fee_amount_id);
		}

		db_set_active();

		return $arr;
	}

    /**
     * TODOEXPLAIN
     */
    public function getFeeAmountsForDate($date = NULL) {
        if (!is_int($date)) {
            $date = time();
        }

        $arr = array();
        db_set_active( getSetting('db_connection') );

        $query =
				'SELECT * FROM fee_amounts
				WHERE fee_state_id = :feeStateId
				AND end_date >= :endDate
				AND deleted=0 AND enabled=1
				GROUP BY nr_of_days_start, nr_of_days_end
				ORDER BY end_date, nr_of_days_start, nr_of_days_end';

        $result = db_query($query, array(':feeStateId' => $this->fee_state_id, ':endDate' => date("Y-m-d", $date)));
        foreach ($result as $record) {
          $arr[] = new class_conference_fee_amount($record->fee_amount_id);
        }

        db_set_active();

        return $arr;
    }
}
