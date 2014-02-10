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
	 * Returns comments about this room
	 *
	 * @return string The comments about this room
	 */
	public function getComment() {
		return $this->comment;
	}

	/**
	 * Returns the number of available seats in this room
	 *
	 * @return int The number of seats
	 */
	public function getNoOfSeats() {
		return $this->noOfSeats;
	}

	/**
	 * Returns the name of the room
	 *
	 * @return string The name of the room
	 */
	public function getRoomName() {
		return $this->roomName;
	}

	/**
	 * Returns the room number
	 *
	 * @return string The room number
	 */
	public function getRoomNumber() {
		return $this->roomNumber;
	}

	public function __toString() {
		return $this->roomNumber . ': ' . $this->roomName;
	}
} 