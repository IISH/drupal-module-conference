<?php

/**
 * Holds a participant date obtained from the API
 */
class ParticipantDateApi extends CRUDApiClient {
	protected $user_id;
	protected $state_id;
	protected $feeState_id;
	protected $paymentId;
	protected $invitationLetter;
	protected $lowerFeeRequested;
	protected $lowerFeeText;
	protected $student;
	protected $award;
	protected $extras_id;
	protected $addedBy_id;

	private $state;
	private $user;
	private $extras;
	private $addedBy;

	public function __construct() {
		$this->setState(ParticipantStateApi::DID_NOT_FINISH_REGISTRATION);
		$this->setFeeState(FeeStateApi::NO_FEE_SELECTED);
	}

	public static function getListWithCriteria(array $properties, $printErrorMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $printErrorMessage);
	}

	/**
	 * Allows the creation of a participant via an array with details
	 *
	 * @param array $participant An array with participant details
	 *
	 * @return ParticipantDateApi A participant object
	 */
	public static function getParticipantDateFromArray(array $participant) {
		return self::createNewInstance(__CLASS__, $participant);
	}

	/**
	 * Did this participant sign up for the award?
	 *
	 * @return bool Whether this participant signed up for the award
	 */
	public function getAward() {
		return $this->award;
	}

	/**
	 * Sets whether this student participates in the award
	 *
	 * @param bool $award Whether this student participates in the award or not
	 */
	public function setAward($award) {
		$this->award = (bool) $award;
		$this->toSave['award'] = $this->award;
	}

	/**
	 * Returns the ids of all extras chosen by this participant
	 *
	 * @return int[] The ids of all extras chosen by this participant
	 */
	public function getExtrasId() {
		return $this->extras_id;
	}

	/**
	 * Returns the fee amounts suitable for this participant
	 *
	 * @param int|null $numDays     When specified, returns only the fee amounts for this number of days
	 * @param int|null $date        Returns only the fee amounts that are still valid from the given date.
	 *                              If no date is given, the current date is used
	 * @param bool     $oneDateOnly Whether to only return results with the same youngest date
	 *
	 * @return FeeAmountApi[] The fee amounts that match the criteria
	 */
	public function getFeeAmounts($numDays = null, $date = null, $oneDateOnly = true) {
		if ($date === null) {
			$date = time();
		}

		$props = new ApiCriteriaBuilder();
		$props
			->eq('feeState_id', $this->getFeeStateId())
			->ge('endDate', $date)
			->sort('endDate', 'asc');

		if (is_int($numDays)) {
			$props
				->le('numDaysStart', $numDays)
				->ge('numDaysEnd', $numDays);
		}

		$feeAmounts = FeeAmountApi::getListWithCriteria($props->get())->getResults();

		if ($oneDateOnly) {
			$firstDate = null;
			foreach ($feeAmounts as $key => $feeAmount) {
				if ($firstDate === null) {
					$firstDate = $feeAmount->getEndDate();
				}
				else if ($firstDate !== $feeAmount->getEndDate()) {
					unset($feeAmounts[$key]);
				}
			}
		}

		return array_values($feeAmounts);
	}

	/**
	 * The id of this participants fee state
	 *
	 * @return int The fee state id of this participant
	 */
	public function getFeeStateId() {
		if ($this->feeState_id == 0 || $this->feeState_id == null) {
			$feeState = FeeStateApi::getDefaultFee();
			if (!empty($feeState)) {
				$this->setFeeStateId($feeState->getId());
				$this->save();
			}
		}

		return $this->feeState_id;
	}

	/**
	 * Changes the fee state of this user
	 *
	 * @param FeeStateApi|int $feeStateId The new fee state (id)
	 */
	public function setFeeState($feeState) {
		if ($feeState instanceof FeeStateApi) {
			$feeState = $feeState->getId();
		}

		$this->feeState_id = $feeState;
		$this->toSave['feeState.id'] = $feeState;
	}

	public function save($printErrorMessage = true) {
		$save = parent::save($printErrorMessage);

		// Make sure to invalidate the cached participant
		if ($save && isset($_SESSION['conference']['participant'])) {
			unset($_SESSION['conference']['participant']);
		}
	}

	/**
	 * Did this participant request an invitation letter?
	 *
	 * @return bool Whether this participant requested an invitation letter
	 */
	public function getInvitationLetter() {
		return $this->invitationLetter;
	}

	/**
	 * Whether this participant has requested an invitation letter
	 *
	 * @param bool $invitationLetter If the participant has requested an invitation letter
	 */
	public function setInvitationLetter($invitationLetter) {
		$this->invitationLetter = (bool) $invitationLetter;
		$this->toSave['invitationLetter'] = $this->invitationLetter;
	}

	/**
	 * Did this participant request a lower fee?
	 *
	 * @return bool Whether this participant requested a lower fee
	 */
	public function getLowerFeeRequested() {
		return $this->lowerFeeRequested;
	}

	/**
	 * Sets whether this participant requested a lower fee
	 *
	 * @param bool $lowerFeeRequested whether this participant requested a lower fee or not
	 */
	public function setLowerFeeRequested($lowerFeeRequested) {
		$this->lowerFeeRequested = (bool) $lowerFeeRequested;
		$this->toSave['lowerFeeRequested'] = $this->lowerFeeRequested;
	}

	/**
	 * Returns extra information concerning the lower fee request
	 *
	 * @return string|null Extra information concerning the lower fee request
	 */
	public function getLowerFeeText() {
		return $this->lowerFeeText;
	}

	/**
	 * Returns the PayWay payment id
	 *
	 * @return int|null The payment id
	 */
	public function getPaymentId() {
		return $this->paymentId;
	}

	/**
	 * The payment id of this participant
	 *
	 * @param int $paymentId The payment id
	 */
	public function setPaymentId($paymentId) {
		$this->paymentId = $paymentId;
		$this->toSave['paymentId'] = $paymentId;
	}

	/**
	 * Returns the state id of this participant
	 *
	 * @return int The participant state id
	 */
	public function getStateId() {
		return $this->state_id;
	}

	/**
	 * Returns the state of this participant
	 *
	 * @return ParticipantStateApi The participant state
	 */
	public function getState() {
		if (!$this->state) {
			foreach (CachedConferenceApi::getParticipantStates() as $state) {
				if ($state->getId() == $this->state_id) {
					$this->state = $state;
					break;
				}
			}
		}

		return $this->state;
	}

	/**
	 * Changes the participant state of this participant
	 *
	 * @param int|ParticipantStateApi $state The new participant state (id)
	 */
	public function setState($state) {
		if ($state instanceof ParticipantStateApi) {
			$state = $state->getId();
		}

		$this->state = null;
		$this->state_id = $state;
		$this->toSave['state.id'] = $state;
	}

	/**
	 * Did this participant indicate to be a student?
	 *
	 * @return bool Whether this participant indicated to be a student
	 */
	public function getStudent() {
		return $this->student;
	}

	/**
	 * Sets whether this participant is a student
	 *
	 * @param bool $student Whether this is a student or not
	 */
	public function setStudent($student) {
		$this->student = (bool) $student;
		$this->toSave['student'] = $this->student;

		$this->setLowerFeeRequested($this->student);
	}

	/**
	 * Returns the user id of this participant
	 *
	 * @return int The user id
	 */
	public function getUserId() {
		return $this->user_id;
	}

	/**
	 * Compute the total amount the given participant has to pay for the days and extras chosen by him/her
	 *
	 * @return float The total amount to pay
	 */
	public function getTotalAmount() {
		$totalAmount = $this->getFeeAmount()->getFeeAmount();
		foreach ($this->getExtras() as $extra) {
			$totalAmount += $extra->getAmount();
		}

		return $totalAmount;
	}

	/**
	 * Returns the single fee amount to use for this participant
	 *
	 * @param int|null $date Returns the fee amount for the given date. If no date is given, the current date is used
	 *
	 * @return FeeAmountApi The fee amount
	 */
	public function getFeeAmount($date = null) {
		if ($date === null) {
			$date = time();
		}

		$props = new ApiCriteriaBuilder();

		return FeeAmountApi::getListWithCriteria(
			$props
				->eq('feeState_id', $this->getFeeStateId())
				->ge('endDate', $date)
				->le('numDaysStart', count($this->getUser()->getDaysPresentDayId()))
				->ge('numDaysEnd', count($this->getUser()->getDaysPresentDayId()))
				->sort('endDate', 'asc')
				->get()
		)->getFirstResult();
	}

	/**
	 * Returns the user of this participant
	 *
	 * @return UserApi The user
	 */
	public function getUser() {
		if (!$this->user) {
			if (LoggedInUserDetails::getId() === $this->user_id) {
				$this->user = LoggedInUserDetails::getUser();
			}
			else {
				$this->user = CRUDApiMisc::getById(new UserApi(), $this->user_id);
			}
		}

		return $this->user;
	}

	/**
	 * Sets the user of this participant
	 *
	 * @param int|UserApi $user The user (id) to set
	 */
	public function setUser($user) {
		if ($user instanceof UserApi) {
			$user = $user->getId();
		}

		$this->user = null;
		$this->user_id = $user;
		$this->toSave['user.id'] = $user;
	}

	/**
	 * Returns all extras chosen by this participant
	 *
	 * @return ExtraApi[] All extras chosen by this participant
	 */
	public function getExtras() {
		if (!$this->extras) {
			$this->extras = array();
			foreach ($this->extras_id as $extraId) {
				foreach (CachedConferenceApi::getExtras() as $extra) {
					if ($extra->getId() === $extraId) {
						$this->extras[] = $extra;
					}
				}
			}
		}

		return $this->extras;
	}

	/**
	 * Set the extras for which this participant signed up
	 *
	 * @param int[]|ExtraApi[] $extras The extras (or their ids) to add to this participant
	 */
	public function setExtras($extras) {
		$this->extras = null;
		$this->extras_id = array();

		foreach ($extras as $extra) {
			if ($extra instanceof ExtraApi) {
				$this->extras_id[] = $extra->getId();
			}
			else if (is_int($extra)) {
				$this->extras_id[] = $extra;
			}
		}

		$this->toSave['extras.id'] = implode(';', $this->extras_id);
	}

	/**
	 * Returns the final date for a bank transfer based on the date the bank transfer was created
	 *
	 * @param int $orderCreationDate The Unix timestamp the order was created
	 *
	 * @return int The Unix timestamp with the final date
	 */
	public function getBankTransferFinalDate($orderCreationDate) {
		$feeAmount = $this->getFeeAmount($orderCreationDate);
		$finalDate = $feeAmount->getEndDate();

		$closingDate = strtotime(SettingsApi::getSetting(SettingsApi::BANK_TRANSFER_CLOSES_ON));
		if ($finalDate > $closingDate) {
			$finalDate = $closingDate;
		}

		return $finalDate;
	}

	/**
	 * Returns the user that created this participant
	 *
	 * @return UserApi The user that created this participant
	 */
	public function getAddedBy() {
		if (!$this->addedBy && is_int($this->getAddedById())) {
			$this->addedBy = CRUDApiMisc::getById(new UserApi(), 'id', $this->getAddedById());
		}

		return $this->addedBy;
	}

	/**
	 * Set the user who added this participant
	 *
	 * @param int|UserApi $addedBy The user (id)
	 */
	public function setAddedBy($addedBy) {
		if ($addedBy instanceof UserApi) {
			$addedBy = $addedBy->getId();
		}

		$this->addedBy = null;
		$this->addedBy_id = $addedBy;
		$this->toSave['addedBy.id'] = $addedBy;
	}

	/**
	 * The user id of the user who created this participant
	 *
	 * @return int The user id of the user who created this participant
	 */
	public function getAddedById() {
		return $this->addedBy_id;
	}

	public function __toString() {
		return $this->getUser()->__toString();
	}
}

