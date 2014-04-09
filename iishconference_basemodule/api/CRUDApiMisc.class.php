<?php

/**
 * Miscellaneous CRUD API functions that are often used
 */
class CRUDApiMisc {

	/**
	 * Returns the instance of the given object with the given id
	 *
	 * @param CRUDApiClient $obj               The object to return an instance of with the given id
	 * @param int           $id                The id to look for
	 * @param bool          $printErrorMessage Whether an error message should be printed on failure
	 *
	 * @return CRUDApiClient|null The instance or null if not found
	 */
	public static function getById($obj, $id, $printErrorMessage = true) {
		return self::getFirstWherePropertyEquals($obj, 'id', $id, $printErrorMessage);
	}

	/**
	 * Returns the first instance of the given object where the given property equals the given id
	 *
	 * @param CRUDApiClient $obj               The object to return the first instance of
	 * @param string        $property          The property in question
	 * @param mixed         $value             The value the given property should have
	 * @param bool          $printErrorMessage Whether an error message should be printed on failure
	 *
	 * @return CRUDApiClient|null The instance or null if not found
	 */
	public static function getFirstWherePropertyEquals($obj, $property, $value, $printErrorMessage = true) {
		return self::getAllWherePropertyEquals($obj, $property, $value, $printErrorMessage)->getFirstResult();
	}

	/**
	 * Returns all matching instances of the given object where the given property equals the given id
	 *
	 * @param CRUDApiClient $obj               The object to return instances of
	 * @param string        $property          The property in question
	 * @param mixed         $value             The value the given property should have
	 * @param bool          $printErrorMessage Whether an error message should be printed on failure
	 *
	 * @return CRUDApiResults The results
	 */
	public static function getAllWherePropertyEquals($obj, $property, $value, $printErrorMessage = true) {
		$props = new ApiCriteriaBuilder();

		return $obj->getListWithCriteria(
			$props
				->eq($property, $value)
				->get(),
			$printErrorMessage
		);
	}
} 