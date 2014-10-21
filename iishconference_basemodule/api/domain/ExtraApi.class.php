<?php

/**
 * Holds an extra obtained from the API
 */
class ExtraApi extends CRUDApiClient {
	protected $title;
	protected $extra;
	protected $description;
	protected $secondDescription;
	protected $amount;
	protected $isFinalRegistration;
	protected $sortOrder;

	public static function getListWithCriteria(array $properties, $printErrorMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $printErrorMessage);
	}

	/**
	 * The amount for this extra
	 *
	 * @return float The amount
	 */
	public function getAmount() {
		return $this->amount;
	}

	/**
	 * The human friendly readable amount for this extra
	 *
	 * @return string The human friendly readable amount
	 */
	public function getAmountInFormat() {
		return ConferenceMisc::getReadableAmount($this->amount);
	}

	/**
	 * The description to place for the user
	 *
	 * @return string The description for the user
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * The name of this extra in the backend
	 *
	 * @return string The backend name
	 */
	public function getExtra() {
		return $this->extra;
	}

	/**
	 * The extended second description for the user
	 *
	 * @return string The extended second description for the user
	 */
	public function getSecondDescription() {
		return $this->secondDescription;
	}

	/**
	 * The title of this extra for the user
	 *
	 * @return string The title for the user
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Whether this extra is shown to the user during final registration rather than pre-registration
	 *
	 * @return bool Whether this extra is shown to the user during final registration
	 */
	public function isFinalRegistration() {
		return $this->isFinalRegistration;
	}

	/**
	 * The sort order
	 *
	 * @return int The sort order
	 */
	public function getSortOrder() {
		return $this->sortOrder;
	}

	/**
	 * Returns only the extras for the pre-registration
	 *
	 * @param ExtraApi[] $allExtras All of the extras
	 *
	 * @return ExtraApi[] Only the extras for the pre-registration
	 */
	public static function getOnlyPreRegistration(array $allExtras = array()) {
		$extras = array();
		foreach ($allExtras as $extra) {
			if (!$extra->isFinalRegistration()) {
				$extras[] = $extra;
			}
		}

		return $extras;
	}

	/**
	 * Returns only the extras for the final registration
	 *
	 * @param ExtraApi[] $allExtras All of the extras
	 *
	 * @return ExtraApi[] Only the extras for the final registration
	 */
	public static function getOnlyFinalRegistration(array $allExtras = array()) {
		$extras = array();
		foreach ($allExtras as $extra) {
			if ($extra->isFinalRegistration()) {
				$extras[] = $extra;
			}
		}

		return $extras;
	}

	/**
	 * An alternative to the __toString method with more extended details
	 *
	 * @return string String representation of this extra
	 */
	public function getExtendedString() {
		if ($this->amount > 0) {
			return $this->getExtra() . ': ' . $this->getDescription() . ' (' . $this->getAmountInFormat() . ')';
		}
		else {
			return $this->getExtra() . ': ' . $this->getDescription();
		}
	}

	public function __toString() {
		if ($this->amount > 0) {
			return $this->getTitle() . ': ' . $this->getAmountInFormat();
		}
		else {
			return $this->getTitle();
		}
	}
}