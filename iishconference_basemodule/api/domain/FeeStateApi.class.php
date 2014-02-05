<?php

/**
 * Holds a fee state obtained from the API
 */
class FeeStateApi extends CRUDApiClient {
	protected $name;
	protected $isDefaultFee;
	protected $feeAmounts_id;

	public static function getListWithCriteria(array $properties, $showDrupalMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $showDrupalMessage);
	}

	/**
	 * Returns the name of the fee state
	 *
	 * @return string The fee state name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Returns the ids of all fee amounts belonging to this fee state
	 *
	 * @return int[] Returns fee amount ids
	 */
	public function getFeeAmountsId() {
		return $this->feeAmounts_id;
	}

	/**
	 * Returns whether this fee is the default fee
	 *
	 * @return bool Returns true if this is the default fee
	 */
	public function isDefaultFee() {
		return $this->isDefaultFee;
	}

	/**
	 * Returns the default fee state, if there is one
	 *
	 * @return FeeStateAPI|null The default fee state, if found
	 */
	public static function getDefaultFee() {
		return CRUDApiMisc::getFirstWherePropertyEquals(new FeeStateApi(), 'isDefaultFee', true);
	}

	public function __toString() {
		return $this->getName();
	}
} 