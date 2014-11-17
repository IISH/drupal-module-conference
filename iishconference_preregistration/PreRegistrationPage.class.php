<?php

/**
 * Holds utility methods concerning a page of the pre registration
 */
class PreRegistrationPage {
	const LOGIN = 'preregister_login_form';
	const PASSWORD = 'preregister_password_form';
	const PERSONAL_INFO = 'preregister_personalinfo_form';
	const TYPE_OF_REGISTRATION = 'preregister_typeofregistration_form';
	const PAPER = 'preregister_paper_form';
	const SESSION = 'preregister_session_form';
	const SESSION_PARTICIPANT = 'preregister_sessionparticipant_form';
	const SESSION_PARTICIPANT_TYPES = 'preregister_sessionparticipanttypes_form';
	const COMMENTS = 'preregister_comments_form';
	const CONFIRM = 'preregister_confirm_form';

	private $pageName;

	public function __construct($pageName) {
		$this->pageName = $pageName;
	}

	/**
	 * The name of this page
	 *
	 * @return string Returns the name of this page
	 */
	public function getPageName() {
		return $this->pageName;
	}

	/**
	 * Indicates whether this page is open
	 *
	 * @return bool Returns true if this page is open
	 */
	public function isOpen() {
		switch ($this->pageName) {
			case self::TYPE_OF_REGISTRATION:
				return self::isTypeOfRegistrationOpen();
				break;
			case self::COMMENTS:
				return self::isCommentsOpen();
				break;
			default:
				return true;
		}
	}

	/**
	 * Indicates whether the type of registration page is open
	 *
	 * @return bool Returns true if the type of registration page is open
	 */
	private static function isTypeOfRegistrationOpen() {
		$showAuthor = SettingsApi::getSetting(SettingsApi::SHOW_AUTHOR_REGISTRATION);
		$showOrganizer = SettingsApi::getSetting(SettingsApi::SHOW_ORGANIZER_REGISTRATION);
		$types = SettingsApi::getSetting(SettingsApi::SHOW_SESSION_PARTICIPANT_TYPES_REGISTRATION);
		$typesToShow = SettingsApi::getArrayOfValues($types);

		return (($showAuthor == 1) || ($showOrganizer == 1) || (count($typesToShow) > 0));
	}

	/**
	 * Indicates whether the general comments page is open
	 *
	 * @return bool Returns true if the general comments is open
	 */
	private static function isCommentsOpen() {
		return (SettingsApi::getSetting(SettingsApi::SHOW_GENERAL_COMMENTS) == 1);
	}
} 