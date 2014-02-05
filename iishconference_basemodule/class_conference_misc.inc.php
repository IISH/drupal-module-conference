<?php 
/**
 * TODOEXPLAIN
 */
class class_conference_misc {

	/**
	 * TODOEXPLAIN
	 */
	public function __construct() {
	}

	/**
	 * TODOEXPLAIN
	 */
	public function protectSearchQuery( $crit ) {

		$crit = str_replace(array(':', ';', '<', '>', '\'', '"', '%', '(', ')', '{', '}', '[', ']'), ' ', $crit);

		$crit = trim($crit);

		while ( strpos($crit, '  ') !== false ) {
			$crit = str_replace('  ', ' ', $crit);
		}

		$crit = substr($crit, 0, 50);

		return $crit;
	}

	/**
	 * TODOEXPLAIN
	 */
	public function explodeSqlQuery( $fieldname, $crit, $quotes = '', $concat = ' AND ' ) {

		$crit = trim($crit);

		while ( strpos($crit, '  ') !== false ) {
			$crit = str_replace('  ', ' ', $crit);
		}

		if ( $crit != '' ) {
			$arrCrit = explode(' ', $crit);
		}

		$crit = '';
		$separator = '';
		foreach ( $arrCrit as $c ) {
			$crit .= $separator . $fieldname . " LIKE '%" . $c . "%' ";
			$separator = ' ' . $concat . ' ';
		}

		return $crit;
	}
}