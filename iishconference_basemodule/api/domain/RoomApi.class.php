<?php

/**
 * Holds a room obtained from the API
 */
class RoomApi extends CRUDApiClient {
	protected $roomName;
	protected $roomNumber;
	protected $noOfSeats;
	protected $comment;

	public static function getListWithCriteria(array $properties, $showDrupalMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $showDrupalMessage);
	}

	/**
	 * @return mixed
	 */
	public function getComment() {
		return $this->comment;
	}

	/**
	 * @return mixed
	 */
	public function getNoOfSeats() {
		return $this->noOfSeats;
	}

	/**
	 * @return mixed
	 */
	public function getRoomName() {
		return $this->roomName;
	}

	/**
	 * @return mixed
	 */
	public function getRoomNumber() {
		return $this->roomNumber;
	}

	public function __toString() {
		return $this->roomNumber . ': ' . $this->roomName;
	}
} 