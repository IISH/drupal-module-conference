<?php

/**
 * Holds a keyword obtained from the API
 */
class KeywordApi extends CRUDApiClient {
	protected $keyword;

	public static function getListWithCriteria(array $properties, $printErrorMessage = true) {
		return parent::getListWithCriteriaForClass(__CLASS__, $properties, $printErrorMessage);
	}

	/**
	 * Return the keyword
	 *
	 * @return string The keyword
	 */
	public function getKeyword() {
		return $this->keyword;
	}

	public function __toString() {
		return $this->getKeyword();
	}
} 