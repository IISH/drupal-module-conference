<?php

class PreRegistrationFlow {
	private $formState;

	public function __construct(array &$formState) {
		$this->formState = $formState;
	}

	public function getCurrentStep() {
		return $this->formState['step'];
	}

	public function userMovesForward() {
		return $this->formState['moves_forward'];
	}

	public function setNextStep() {
		$nextStep = '';
		$back = false;

		// Make sure that during refreshes the user stays on the right step
		if (isset($this->formState['pre_registration_values']['form_build_id'])) {
			$this->formState['values']['form_build_id'] = $this->formState['pre_registration_values']['form_build_id'];
		}

		$this->formState['pre_registration_values']['form_build_id'] = $this->formState['values']['form_build_id'];
		$this->formState['step'] = $nextStep;
		$this->formState['moves_forward'] = $back;
		$this->formState['rebuild'] = true;
	}
} 