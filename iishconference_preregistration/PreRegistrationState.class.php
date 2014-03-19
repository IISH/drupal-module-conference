<?php

/**
 * Handles the state during the pre-registration process
 */
class PreRegistrationState {
	private $formState;

	public function __construct(array &$formState) {
		$this->formState = & $formState;
	}

	/**
	 * The next step/page the user should go to
	 *
	 * @param string $formName The internal function name of the form to call
	 */
	public function setNextStep($formName) {
		$this->formState['pre_registration']['step'] = $formName;

		// Make sure that during refreshes the user stays on the right step
		if (isset($this->formState['pre_registration']['form_build_id'])) {
			$this->formState['values']['form_build_id'] = $this->formState['pre_registration']['form_build_id'];
		}

		$this->formState['pre_registration']['form_build_id'] = $this->formState['values']['form_build_id'];
		$this->formState['rebuild'] = true;

		unset($this->formState['pre_registration']['data']);
	}

	/**
	 * Returns the internal function name of the form to call next
	 *
	 * @return string A function name
	 */
	public function getCurrentStep() {
		if (!isset($this->formState['pre_registration']['step'])) {
			if (LoggedInUserDetails::isLoggedIn()) {
				$this->formState['pre_registration']['step'] = 'preregister_personalinfo_form';
			}
			else {
				$this->formState['pre_registration']['step'] = 'preregister_login_form';
			}
		}

		return $this->formState['pre_registration']['step'];
	}

	/**
	 * Caches data only for a single step/page
	 *
	 * @param array $data The data to cache
	 */
	public function setFormData(array $data) {
		$this->formState['pre_registration']['data'] = serialize($data);
	}

	/**
	 * Caches data for multiple steps/pages
	 *
	 * @param array $data The data to cache
	 */
	public function setMultiPageData(array $data) {
		$this->formState['pre_registration']['multi_page_data'] = serialize($data);
	}

	/**
	 * Returns the cached data only for a single step/page
	 *
	 * @return array The cached data
	 */
	public function getFormData() {
		if (!isset($this->formState['pre_registration']['data'])) {
			return array();
		}

		return unserialize($this->formState['pre_registration']['data']);
	}

	/**
	 * Returns the cached data for multiple steps/pages
	 *
	 * @return array The cached data
	 */
	public function getMultiPageData() {
		if (!isset($this->formState['pre_registration']['multi_page_data'])) {
			return array();
		}

		return unserialize($this->formState['pre_registration']['multi_page_data']);
	}

	/**
	 * Sets the email of the user doing the pre-registration
	 *
	 * @param string $email The email address
	 */
	public function setEmail($email) {
		unset($this->formState['pre_registration']['user']);
		unset($this->formState['pre_registration']['participant']);

		$this->formState['pre_registration']['email'] = strtolower(trim($email));
	}

	/**
	 * Returns the user instance doing the pre-registration
	 *
	 * @return UserApi The user instance
	 */
	public function getUser() {
		if (!isset($this->formState['pre_registration']['user'])) {
			if (LoggedInUserDetails::isLoggedIn() && (LoggedInUserDetails::getUser() !== null)) {
				$this->formState['pre_registration']['user'] = serialize(LoggedInUserDetails::getUser());
			}
			else {
				$user = new UserApi();
				$user->setEmail($this->formState['pre_registration']['email']);

				$this->formState['pre_registration']['user'] = serialize($user);
			}
		}

		return unserialize($this->formState['pre_registration']['user']);
	}

	/**
	 * Returns the participant instance doing the pre-registration
	 *
	 * @return ParticipantDateApi The participant instance
	 */
	public function getParticipant() {
		if (!isset($this->formState['pre_registration']['participant'])) {
			if (LoggedInUserDetails::isLoggedIn() && (LoggedInUserDetails::getParticipant() !== null)) {
				$this->formState['pre_registration']['participant'] = serialize(LoggedInUserDetails::getParticipant());
			}
			else {
				$this->formState['pre_registration']['participant'] = serialize(new ParticipantDateApi());
			}
		}

		return unserialize($this->formState['pre_registration']['participant']);
	}

	/**
	 * Updates the user and participant instances that are cached during the pre-registration.
	 * Should only be used by the personalinfo form
	 *
	 * @param UserApi            $user        The user instance
	 * @param ParticipantDateApi $participant The participant instance
	 */
	public function updateUserAndParticipant($user, $participant) {
		$this->formState['pre_registration']['user'] = serialize($user);
		$this->formState['pre_registration']['participant'] = serialize($participant);
	}
} 