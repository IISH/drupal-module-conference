<?php

module_load_include('module', 'filter');

/**
 * Miscellaneous conference functions that are often used
 */
class ConferenceMisc {

	/**
	 * Return a human friendly text 'yes' or 'no' based on the given boolean value
	 *
	 * @param bool $booleanValue When 'true', 'yes' is returned, else 'no' is returned
	 *
	 * @return string Either 'yes' or 'no'
	 */
	public static function getYesOrNo($booleanValue) {
		return $booleanValue ? t('yes') : t('no');
	}

	/**
	 * Returns the human-friendly text for a gender
	 *
	 * @param string $genderKey The gender key
	 *
	 * @return string The human-friendly text
	 */
	public static function getGender($genderKey) {
		$genders = self::getGenders();

		return $genders[$genderKey];
	}

	/**
	 * Returns the human-friendly texts for genders
	 *
	 * @return array The human-friendly texts for genders
	 */
	public static function getGenders() {
		return array(
			''  => '',
			'M' => t('Male'),
			'F' => t('Female'),
		);
	}

	/**
	 * Returns the human-friendly text for a language coach/pupil volunteering
	 *
	 * @param string $langCoachPupilKey The language coach/pupil volunteering key
	 *
	 * @return string The human-friendly text for a language coach/pupil volunteering
	 */
	public static function getLanguageCoachPupil($langCoachPupilKey) {
		$langCoachPupils = self::getLanguageCoachPupils();

		return $langCoachPupils[$langCoachPupilKey];
	}

	/**
	 * Returns the human-friendly texts for language coach/pupil volunteering
	 *
	 * @return array The human-friendly texts for language coach/pupil volunteering
	 */
	public static function getLanguageCoachPupils() {
		return array(
			''      => t('not applicable'),
			'coach' => t('I would like to be an English Language Coach'),
			'pupil' => t('I need some help from an English Language Coach'),
		);
	}

	/**
	 * Returns clean HTML to be used for output:
	 * - The given text is trimmed
	 * - If the text is empty, a '-' is displayed instead
	 * - Web pages, emails and other URLs are translated to links
	 * - If the text is HTML ($isHTML), the HTML is filtered
	 * - If the text is plain text ($isHTML), all found HTML is escaped
	 * - New lines (/n) are translated to HTML breaks (<br />)
	 *
	 * @param string $text   The text to clean
	 * @param bool   $isHTML Whether the given text includes HTML
	 *
	 * @return string Returns clean HTML
	 */
	public static function getCleanHTML($text, $isHTML = false) {
		$filter = new stdClass();
		$filter->callback = '_filter_url';
		$filter->settings = array('filter_url_length' => 300);

		$text = (($text === null) || (strlen(trim($text)) === 0)) ? '-' : trim($text);
		$text = ($isHTML) ? filter_xss_admin($text) : check_plain($text);

		return _filter_url(nl2br($text), $filter);
	}

	/**
	 * Returns the filesize in a human friendly format
	 *
	 * @param int|null $filesize The filesize in bytes
	 *
	 * @return string The filesize in bytes, KB or MB
	 */
	public static function getReadableFileSize($filesize) {
		if (is_null($filesize) || ($filesize == 0)) {
			return "0 bytes";
		}

		if ($filesize / 1024 > 1) {
			if ($filesize / 1048576 > 1) {
				return round($filesize / 1048576, 2) . ' MB';
			}

			return round($filesize / 1024, 2) . ' KB';
		}

		return $filesize . ' bytes';
	}

	/**
	 * A wrapper around preg_match that simply returns whether the string matches the pattern or not
	 *
	 * @param string $value   The string to match
	 * @param string $pattern The pattern the value should have
	 *
	 * @return bool Whether the value matches the pattern or not
	 */
	public static function regexpValue($value, $pattern = '/^[0-9]+$/') {
		$ret = true;

		if ($value !== '') {
			if ($pattern !== '') {
				if (preg_match($pattern, $value) === 0) {
					$ret = false;
				}
			}
		}

		return $ret;
	}

	/**
	 * Returns the amount in a human friendly readable format
	 *
	 * @param int|float $amount        The amount
	 * @param bool      $amountInCents Whether the amount is given in cents, yes or no
	 *
	 * @return string The human friendly format
	 */
	public static function getReadableAmount($amount, $amountInCents = false) {
		if ($amountInCents) {
			$amount = $amount / 100;
		}

		return number_format($amount, 2) . ' EUR';
	}

	/**
	 * Returns the block that informs the user how and who to contact for information
	 *
	 * @param int $emptyRows The number of empty rows before the block appears
	 *
	 * @return string The HTML generating an info block
	 */
	public static function getInfoBlock($emptyRows = 2) {
		return
			str_repeat('<br />', $emptyRows) . '<div class="eca_warning">' .
				t('For general questions and errors please contact: ') .
				self::encryptEmailAddress(SettingsApi::getSetting(SettingsApi::DEFAULT_ORGANISATION_EMAIL)) .
			'</div>';
	}

	/**
	 * Protects again email harvesting by cutting up the email address into pieces
	 *
	 * @param string $email The email address in question
	 * @param string $label The label of the email address
	 *
	 * @return string The HTML/Javascript to place in the document
	 */
	public static function encryptEmailAddress($email, $label = '') {
		$email = trim($email);

		if ($label != '') {
			$ret = "<script language=\"javascript\" type=\"text/javascript\">
<!--
var w = \"::NAME::\";
var h1 = \"::DOMAIN1::\";
var h2 = \"::DOMAIN2::\";
var l = \"::LABEL::\";
document.write('<a hr'+'ef=\"'+'mai'+'lto:'+w+'@'+h1+'.'+h2+'\">'+l+'<\/a>');
//-->
</script>";
		}
		else {
			$ret = "<script language=\"javascript\" type=\"text/javascript\">
<!--
var w = \"::NAME::\";
var h1 = \"::DOMAIN1::\";
var h2 = \"::DOMAIN2::\";
document.write('<a hr'+'ef=\"'+'mai'+'lto:'+w+'@'+h1+'.'+h2+'\">'+w+'@'+h1+'.'+h2+'<\/a>');
//-->
</script>";
		}

		// divide @
		$arr = explode('@', $email);
		$preAt = $arr[0];
		$postAt = $arr[1];

		// divide first dot
		$pos = strpos($postAt, '.');
		$domain1 = substr($postAt, 0, $pos);
		$domain2 = substr($postAt, -(strlen($postAt) - $pos - 1));

		// place in template
		$ret = str_replace('::NAME::', $preAt, $ret);
		$ret = str_replace('::DOMAIN1::', $domain1, $ret);
		$ret = str_replace('::DOMAIN2::', $domain2, $ret);
		$ret = str_replace('::LABEL::', $label, $ret);

		return $ret;
	}

	/**
	 * Create a single line enumeration
	 *
	 * @param array  $items        The items to enumerate
	 * @param string $seperator    The default seperator to use
	 * @param string $seperatorEnd The last seperator to use, 'and' by default
	 *
	 * @return string A single line enumeration
	 */
	public static function getEnumSingleLine(array $items, $seperator = ', ', $seperatorEnd = null) {
		if ($seperatorEnd === null) {
			$seperatorEnd = ' ' . t('and') . ' ';
		}

		$line = '';
		$items = array_values($items);
		foreach ($items as $i => $item) {
			if ($i > 0) {
				$line .= ($i < (count($items) - 1)) ? $seperator : $seperatorEnd;
			}
			$line .= $item;
		}

		return $line;
	}

	/**
	 * Only returns the first part of a long piece of text, with (...) indicating that the original is longer.
	 * If nothing was cut, the (...) will not appear
	 *
	 * @param string $text          The text to cut
	 * @param int    $numberOfWords The number of allowed words from the start
	 *
	 * @return string The first part of the text
	 */
	public static function getFirstPartOfText($text, $numberOfWords = 50) {
		$newText = implode(' ', array_slice(explode(' ', $text), 0, $numberOfWords));

		return (strlen($newText) < strlen($text)) ? $newText . ' ...' : $newText;
	}
} 