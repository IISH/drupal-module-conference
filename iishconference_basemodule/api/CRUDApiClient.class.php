<?php

require_once('ConferenceApiClient.class.php');
require_once('CRUDApiResults.class.php');

/**
 * The Conference API client for standard CRUD (Create, Read, Update and Delete) actions
 */
abstract class CRUDApiClient {
	private static $allowedMethods = array('eq', 'ne', 'gt', 'lt', 'ge', 'le');
	private static $otherProperties = array('sort', 'order', 'max', 'offset');

	private static $client;
	protected $id;
	protected $toSave = array();

	/**
	 * The id of this instance
	 *
	 * @return int The id of this instance
	 */
	public function getId() {
		return $this->id;
	}

	public function save($showDrupalMessage = true) {
		$apiName = substr(get_class($this), 0, -3);
		$this->toSave['id'] = $this->getId();

		if ($this->isUpdate()) {
			$apiResults =
				self::getClient()->post($apiName, $this->toSave, $showDrupalMessage);
		}
		else {
			$apiResults =
				self::getClient()->put($apiName, $this->toSave, $showDrupalMessage);
		}

		return is_array($apiResults) ? $apiResults['success'] : false;
	}

	/*public abstract function delete($showDrupalMessage=true);*/

	/**
	 * Returns a list with CRUDApiClient instances as a key value array with the id as the key
	 * and the string representation of the instance as a value
	 *
	 * @param CRUDApiClient[] $crudList A list with CRUDApiClient instances
	 *
	 * @return array An array with the id a s the key and the string representation of the instance as a value
	 */
	public static function getAsKeyValueArray(array $crudList) {
		$list = array();
		foreach ($crudList as $crudInstance) {
			$list[$crudInstance->getId()] = $crudInstance->__toString();
		}

		return $list;
	}

	/**
	 * Indicates whether this save is an update or an insert
	 *
	 * @return bool
	 */
	protected function isUpdate() {
		return ($this->getId() !== null);
	}

	/**
	 * Allows the user to get a list with instances of this class based on a list with criteria
	 *
	 * @param string $class             The name of the class from where the class is made, subclass should use __CLASS__ for this
	 * @param array  $properties        The criteria
	 * @param bool   $showDrupalMessage Whether to show a Drupal error message in case of failure
	 *
	 * @return CRUDApiResults|null
	 */
	protected static function getListWithCriteriaForClass($class, array $properties, $showDrupalMessage = true) {
		$apiName = substr($class, 0, -3);
		$apiResults =
			self::getClient()->get($apiName, self::transformCRUDParametersToApi($properties), $showDrupalMessage);

		$results = new CRUDApiResults($apiResults['totalSize']);
		foreach ($apiResults['results'] as $curInstance) {
			$results->addToResults(self::createNewInstance($class, $curInstance));
		}

		return $results;
	}

	protected static function transformCRUDParametersToApi(array $crudParameters) {
		$apiParameters = array();
		foreach ($crudParameters as $parameter) {
			$property = trim(str_replace('_', '.', $parameter['property']));
			$value = (is_null($parameter['value'])) ? 'null' : trim($parameter['value']);
			$method = trim($parameter['method']);

			if (in_array($method, self::$allowedMethods)) {
				$apiParameters[$property] = $value . '::' . $method;
			}
			else if (in_array($property, self::$otherProperties)) {
				$apiParameters[$property] = $value;
			}
		}

		return $apiParameters;
	}

	protected static function createNewInstance($class, $properties) {
		$instance = new $class();
		foreach ($properties as $property => $value) {
			$prop = str_replace('.', '_', $property);
			if (is_array($value)) {
				asort($value);
			}
			$instance->$prop = $value;
		}

		return $instance;
	}

	private static function getClient() {
		if (!self::$client) {
			self::$client = new ConferenceApiClient();
		}

		return self::$client;
	}
} 