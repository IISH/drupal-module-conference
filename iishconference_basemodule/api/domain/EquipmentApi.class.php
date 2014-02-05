<?php

/**
 * Holds an equipment obtained from the API
 */
class EquipmentApi extends CRUDApiClient {
	protected $code;
	protected $equipment;
	protected $description;
	protected $imageUrl;

	public static function getListWithCriteria(array $properties, $showDrupalMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $showDrupalMessage);
	}

	/**
	 * Return the code of this equipment
	 *
	 * @return string The code of this equipment
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * Returns the description of this equipment
	 *
	 * @return string The description of this equipment
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Returns the name of this equipment
	 *
	 * @return string The name of this equipment
	 */
	public function getEquipment() {
		return $this->equipment;
	}

	/**
	 * Returns the URL of the image that belongs to this equipment
	 *
	 * @return string The URL of the image that belongs to this equipment
	 */
	public function getImageUrl() {
		return $this->imageUrl;
	}

	public function __toString() {
		return $this->getEquipment();
	}
} 