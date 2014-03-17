<?php
/**
 * @file
 * Provides the page where a participant can upload or remove a file for his/her paper
 */

define('UPLOAD_PAPER_ERROR_NONE', 0);
define('UPLOAD_PAPER_ERROR_ID_NOT_FOUND', 1);
define('UPLOAD_PAPER_ERROR_USER_NOT_ALLOWED', 2);
define('UPLOAD_PAPER_ERROR_EMPTY_FILE', 3);
define('UPLOAD_PAPER_ERROR_LARGE_FILE', 4);
define('UPLOAD_PAPER_ERROR_EXT_NOT_ALLOWED', 5);
define('UPLOAD_PAPER_ERROR_OTHER', 6);

/**
 * Allows participants to upload paper files through the CMS API
 *
 * @param PaperApi|null $paper The paper of which a file will be uploaded
 *
 * @return string The HTML for participants to upload their paper file
 */
function conference_upload_paper($paper) {
	if (!LoggedInUserDetails::isLoggedIn()) {
		// redirect to login page
		header('Location: ' . url(SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login', array('query' => drupal_get_destination())));
		die(t('Go to !login page.', array('!login' => l(t('login'), '/' . SettingsApi::getSetting(SettingsApi::PATH_FOR_MENU) . 'login',
			array('query' => drupal_get_destination())))));
	}

	if ($paper === null) {
		drupal_set_message(t('Unfortunately, this paper does not seem to exist.'), 'error');

		return '';
	}
	else if ($paper->getUserId() !== LoggedInUserDetails::getId()) {
		drupal_set_message(t('You are only allowed to upload a paper for your own papers.'), 'error');

		return '';
	}
	else {
		$accessTokenApi = new AccessTokenApi();
		$token = $accessTokenApi->accessToken(LoggedInUserDetails::getId());

		$url = variable_get('conference_base_url') . variable_get('conference_event_code') . '/' .
			variable_get('conference_date_code') . '/' . 'userApi/uploadPaper?access_token=' . $token;

		$paperDownloadLink = variable_get('conference_base_url') . variable_get('conference_event_code') . '/' .
			variable_get('conference_date_code') . '/' . 'userApi/downloadPaper/' . $paper->getId();

		$ecaSettings = CachedConferenceApi::getSettings();
		$allowedExtensions = $ecaSettings[SettingsApi::ALLOWED_PAPER_EXTENSIONS];
		$maxSize = $ecaSettings[SettingsApi::MAX_UPLOAD_SIZE_PAPER];
		$form = drupal_get_form('conference_upload_paper_form', $paper);

		$params = drupal_get_query_parameters();
		if (isset($params['e'])) {
			switch ($params['e']) {
				case UPLOAD_PAPER_ERROR_NONE:
					drupal_set_message(t('Your paper has been successfully uploaded!'), 'status');
					break;
				case UPLOAD_PAPER_ERROR_ID_NOT_FOUND:
					drupal_set_message(t('Your paper could not be found!'), 'error');
					break;
				case UPLOAD_PAPER_ERROR_USER_NOT_ALLOWED:
					drupal_set_message(t('You are not allowed to upload your paper!'), 'error');
					break;
				case UPLOAD_PAPER_ERROR_EMPTY_FILE:
					drupal_set_message(t('You have not uploaded a file!'), 'error');
					break;
				case UPLOAD_PAPER_ERROR_LARGE_FILE:
					drupal_set_message(t('The file you uploaded is too large! The maximum size is @maxSize!',
						array('@maxSize' => ConferenceMisc::getReadableFileSize($maxSize))), 'error');
					break;
				case UPLOAD_PAPER_ERROR_EXT_NOT_ALLOWED:
					drupal_set_message(t('You can only upload files with the following extensions: @extensions',
						array('@extensions' => $allowedExtensions)), 'error');
					break;
				case UPLOAD_PAPER_ERROR_OTHER:
				default:
					drupal_set_message(t('An undefined error has occurred!'), 'error');
			}
		}

		return theme('conference_upload_paper', array(
			'paper'             => $paper,
			'actionUrl'         => $url,
			'paperDownloadLink' => $paperDownloadLink,
			'maxSize'           => ConferenceMisc::getReadableFileSize($maxSize),
			'extensions'        => $allowedExtensions,
			'form'              => $form,
		));
	}
}

/**
 * Implements hook_form()
 */
function conference_upload_paper_form($form, &$form_state) {
	$form['remove-paper'] = array(
		'#type'       => 'submit',
		'#name'       => 'remove-paper',
		'#value'      => t('Remove uploaded paper'),
		'#attributes' => array('onclick' =>
			                       'if (!confirm("' . t('Are you sure you want to remove the uploaded paper?') .
			                       '")) { return false; }'),
	);

	return $form;
}

/**
 * Implements hook_submit()
 */
function conference_upload_paper_form_submit($form, &$form_state) {
	$paper = $form_state['build_info']['args'][0];

	$removePaperApi = new RemovePaperApi();
	if ($removePaperApi->removePaper($paper)) {
		drupal_set_message(t('Your paper has been successfully removed!'), 'status');
	}
}