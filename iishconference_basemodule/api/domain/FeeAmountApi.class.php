<?php

/**
 * Holds a fee amount obtained from the API
 */
class FeeAmountApi extends CRUDApiClient {
	protected $feeState_id;
	protected $endDate;
	protected $numDaysStart;
	protected $numDaysEnd;
	protected $feeAmount;
	protected $substituteName;

	private $feeState;

	public static function getListWithCriteria(array $properties, $showDrupalMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $showDrupalMessage);
	}

	/**
	 * The final date this fee amount is valid
	 *
	 * @return int The final date as a Unix timestamp
	 */
	public function getEndDate() {
		return strtotime($this->endDate);
	}

	/**
	 * The fee amount
	 *
	 * @return float The fee amount
	 */
	public function getFeeAmount() {
		return $this->feeAmount;
	}

	/**
	 * The fee amount in a human friendly readable format
	 *
	 * @return string The fee amount
	 */
	public function getFeeAmountInFormat() {
		return ConferenceMisc::getReadableAmount($this->feeAmount);
	}

	/**
	 * The id of the fee state to which this amount belongs
	 *
	 * @return int The fee state id
	 */
	public function getFeeStateId() {
		return $this->feeState_id;
	}

	/**
	 * The fee state to which this amount belongs
	 *
	 * @return FeeStateAPI The fee state
	 */
	public function getFeeState() {
		if (!$this->feeState) {
			$this->feeState = CRUDApiMisc::getById(new FeeStateApi(), $this->feeState_id);
		}

		return $this->feeState;
	}

	/**
	 * Return the maximum number of days for which this fee amount is valid
	 *
	 * @return int The max number of days
	 */
	public function getNumDaysEnd() {
		return $this->numDaysEnd;
	}

	/**
	 * Return the minimum number of days for which this fee amount is valid
	 *
	 * @return int The min number of days
	 */
	public function getNumDaysStart() {
		return $this->numDaysStart;
	}

	/**
	 * Returns the substitute name (over the fee state name) if this fee amount is used
	 *
	 * @return string|null The substitute name, if it exists
	 */
	public function getSubstituteName() {
		return $this->substituteName;
	}

	public function __toString() {
		if ($this->numDaysStart == $this->numDaysEnd) {
			$days = $this->numDaysStart . ' ' . t('day');
		}
		else {
			$days = $this->numDaysStart . '-' . $this->numDaysEnd . ' ' . t('days');
		}

		$name = $this->getFeeState()->getName();
		if (!empty($this->substituteName)) {
			$name = $this->substituteName;
		}

		return $name . ' (' . $days . '): ' . $this->getFeeAmountInFormat();
	}
} 