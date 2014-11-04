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
	protected $accompanyingPersons;
	protected $extras_id;
	protected $addedBy_id;

	private $user;
	private $state;
	private $extras;
	private $addedBy;
	private $feeState;
	private $participantVolunteering;

	public function __construct($new = true) {
		if ($new) {
			$this->setState(ParticipantStateApi::DID_NOT_FINISH_REGISTRATION);
			$this->setFeeState(FeeStateApi::NO_FEE_SELECTED);
		}
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
		return is_array($this->extras_id) ? $this->extras_id : array();
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
		return FeeAmountApi::getFeeAmounts($this->getFeeStateId(), $date, $numDays, $oneDateOnly);
	}

	/**
	 * The id of this participants fee state
	 *
	 * @return int The fee state id of this participant
	 */
	public function getFeeStateId() {
		return $this->getFeeState()->getId();
	}

	/**
	 * The participants fee state
	 *
	 * @return FeeStateApi The fee state of this participant
	 */
	public function getFeeState() {
		if ($this->feeState_id == FeeStateApi::NO_FEE_SELECTED || $this->feeState_id === null) {
			$feeState = FeeStateApi::getDefaultFee();
			if (!empty($feeState)) {
				$this->feeState = $feeState;
				$this->setFeeState($feeState);
				$this->save();
			}
		}

		if ($this->feeState === null) {
			$this->feeState = CRUDApiMisc::getById(new FeeStateApi(), $this->feeState_id);
		}

		return $this->feeState;
	}

	/**
	 * Changes the fee state of this user
	 *
	 * @param FeeStateApi|int $feeState The new fee state (id)
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
		if ($save) {
			LoggedInUserDetails::invalidateParticipant();
		}
	}

	/**
	 * Compute the total amount the given participant has to pay for
	 * - The days
	 * - The extras
	 * - The accompanying persons
	 *
	 * @return float The total amount to pay
	 */
	public function getTotalAmount() {
		$totalAmount = $this->getFeeAmount()->getFeeAmount();

		foreach ($this->getExtrasOfFinalRegistration() as $extra) {
			$totalAmount += $extra->getAmount();
		}

		if (SettingsApi::getSetting(SettingsApi::SHOW_ACCOMPANYING_PERSONS) == 1) {
			$feeAmountAccompanyingPerson = $this->getFeeAmount(null, FeeStateApi::getAccompanyingPersonFee());
			$totalAmount += (count($this->getAccompanyingPersons()) * $feeAmountAccompanyingPerson->getFeeAmount());
		}

		return $totalAmount;
	}

	/**
	 * Returns the single best fee amount to use for this participant
	 *
	 * @param int|null             $date     Returns the fee amount for the given date. If no date is given, the current date is used
	 * @param FeeStateApi|int|null $feeState The fee state to use. If no fee state is given, the participants fee state is used
	 *
	 * @return FeeAmountApi The fee amount
	 */
	public function getFeeAmount($date = null, $feeState = null) {
		$date = ($date === null) ? strtotime('today') : $date;

		$feeStateId = $this->getFeeStateId();
		if ($feeState !== null) {
			$feeStateId = ($feeState instanceof FeeStateApi) ? $feeState->getId() : $feeState;
		}

		$feeAmounts = FeeAmountApi::getFeeAmounts($feeStateId, $date, count($this->getUser()->getDaysPresentDayId()));

		return (isset($feeAmounts[0])) ? $feeAmounts[0] : null;
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
	 * Returns all extras chosen by this participant during pre-registration
	 *
	 * @return ExtraApi[] All extras chosen by this participant during pre-registration
	 */
	public function getExtrasOfPreRegistration() {
		return ExtraApi::getOnlyPreRegistration($this->getExtras());
	}

	/**
	 * Returns all extras chosen by this participant during final registration
	 *
	 * @return ExtraApi[] All extras chosen by this participant during final registration
	 */
	public function getExtrasOfFinalRegistration() {
		return ExtraApi::getOnlyFinalRegistration($this->getExtras());
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
	 * Returns the names of the accompanying persons
	 *
	 * @return string[] Names of the accompanying persons
	 */
	public function getAccompanyingPersons() {
		return is_array($this->accompanyingPersons) ? array_values($this->accompanyingPersons) : array();
	}

	/**
	 * Sets the names of the accompanying persons
	 *
	 * @param string[] $accompanyingPersons Names of the accompanying persons
	 */
	public function setAccompanyingPersons($accompanyingPersons) {
		$this->accompanyingPersons = $accompanyingPersons;
		$this->toSave['accompanyingPersons'] = json_encode($this->accompanyingPersons);
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

		$lastDate = strtotime(SettingsApi::getSetting(SettingsApi::BANK_TRANSFER_LASTDATE));
		if (!ConferenceMisc::isOpenForLastDate($lastDate, $finalDate)) {
			$finalDate = $lastDate;
		}

		return $finalDate;
	}

	/**
	 * Returns all volunteering chosen by this participant
	 *
	 * @return ParticipantVolunteeringApi[] All volunteering by this participant
	 */
	public function getParticipantVolunteering() {
		if (!$this->participantVolunteering) {
			$this->participantVolunteering = CRUDApiMisc::getAllWherePropertyEquals(new ParticipantVolunteeringApi(),
				'participantDate_id', $this->getId())->getResults();
		}

		return $this->participantVolunteering;
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

